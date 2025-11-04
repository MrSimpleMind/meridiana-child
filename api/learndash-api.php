<?php
/**
 * LearnDash REST API Endpoints
 *
 * Simplified REST API wrapper around native LearnDash functions
 * Uses learndash-helpers.php for all course/enrollment/progress data
 *
 * @package Meridiana Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registra tutti gli endpoint LearnDash
 */
function meridiana_register_learndash_endpoints() {

	// GET /wp-json/learnDash/v1/user/{id}/courses
	register_rest_route( 'learnDash/v1', '/user/(?P<user_id>\d+)/courses', [
		'methods'             => 'GET',
		'callback'            => 'meridiana_get_user_courses',
		'permission_callback' => function( $request ) {
			$user_id = $request->get_param( 'user_id' );
			return get_current_user_id() == $user_id || current_user_can( 'manage_options' );
		},
	] );

	// POST /wp-json/learnDash/v1/user/{id}/courses/{courseId}/enroll
	register_rest_route( 'learnDash/v1', '/user/(?P<user_id>\d+)/courses/(?P<course_id>\d+)/enroll', [
		'methods'             => 'POST',
		'callback'            => 'meridiana_enroll_user_in_course',
		'permission_callback' => function( $request ) {
			$user_id = $request->get_param( 'user_id' );
			return is_user_logged_in() && ( get_current_user_id() == $user_id || current_user_can( 'manage_options' ) );
		},
	] );

	// POST /wp-json/learnDash/v1/user/{id}/courses/{courseId}/reset
	register_rest_route( 'learnDash/v1', '/user/(?P<user_id>\d+)/courses/(?P<course_id>\d+)/reset', [
		'methods'             => 'POST',
		'callback'            => 'meridiana_reset_course_progress',
		'permission_callback' => function( $request ) {
			$user_id = $request->get_param( 'user_id' );
			return is_user_logged_in() && ( get_current_user_id() == $user_id || current_user_can( 'manage_options' ) );
		},
	] );

	// GET /wp-json/learnDash/v1/courses/{id}/certificate
	register_rest_route( 'learnDash/v1', '/courses/(?P<course_id>\d+)/certificate', [
		'methods'             => 'GET',
		'callback'            => 'meridiana_get_course_certificate',
		'permission_callback' => function() {
			return is_user_logged_in();
		},
	] );

	// POST /wp-json/learnDash/v1/lessons/{id}/mark-viewed
	register_rest_route( 'learnDash/v1', '/lessons/(?P<lesson_id>\d+)/mark-viewed', [
		'methods'             => 'POST',
		'callback'            => 'meridiana_mark_lesson_viewed',
		'permission_callback' => function() {
			return is_user_logged_in();
		},
	] );

	// POST /wp-json/learnDash/v1/quizzes/{id}/submit
	register_rest_route( 'learnDash/v1', '/quizzes/(?P<quiz_id>\d+)/submit', [
		'methods'             => 'POST',
		'callback'            => 'meridiana_submit_quiz',
		'permission_callback' => function() {
			return is_user_logged_in();
		},
	] );
}

add_action( 'rest_api_init', 'meridiana_register_learndash_endpoints' );

/**
 * GET /wp-json/learnDash/v1/user/{id}/courses
 *
 * Returns courses for user using native LearnDash functions
 * Separates into: enrolled courses | completed courses | certificates
 *
 * @param WP_REST_Request $request REST request object
 * @return WP_REST_Response
 */
function meridiana_get_user_courses( $request ) {
	$user_id = absint( $request->get_param( 'user_id' ) );

	if ( ! $user_id ) {
		return new WP_Error( 'invalid_user', 'Invalid user ID', [ 'status' => 400 ] );
	}

	// Use helper function to get dashboard data
	$dashboard = meridiana_get_user_dashboard( $user_id );

	return rest_ensure_response( [
		'courses'      => $dashboard['courses'],
		'completed'    => $dashboard['completed'],
		'certificates' => $dashboard['certificates'],
	] );
}

/**
 * POST /wp-json/learnDash/v1/user/{id}/courses/{courseId}/enroll
 *
 * Enroll user in course using native LearnDash
 *
 * @param WP_REST_Request $request REST request object
 * @return WP_REST_Response
 */
function meridiana_enroll_user_in_course( $request ) {
	try {
		$user_id   = absint( $request->get_param( 'user_id' ) );
		$course_id = absint( $request->get_param( 'course_id' ) );

		if ( ! $user_id || ! $course_id ) {
			return new WP_Error( 'invalid_params', 'Missing user_id or course_id', [ 'status' => 400 ] );
		}

		// Verify course exists
		$course = get_post( $course_id );
		if ( ! $course || 'sfwd-courses' !== $course->post_type ) {
			return new WP_Error( 'invalid_course', 'Course does not exist', [ 'status' => 400 ] );
		}

		// Check if user exists
		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			return new WP_Error( 'invalid_user', 'User does not exist', [ 'status' => 400 ] );
		}

		// Enroll using helper function
		$enrollment = meridiana_enroll_user( $user_id, $course_id );

		if ( is_wp_error( $enrollment ) ) {
			return $enrollment;
		}

		return rest_ensure_response( [
			'success'        => true,
			'message'        => 'Iscritto al corso con successo',
			'user_id'        => $user_id,
			'course_id'      => $course_id,
			'enrolled_date'  => current_time( 'mysql' ),
		] );

	} catch ( Exception $e ) {
		error_log( 'LearnDash Enrollment Exception: ' . $e->getMessage() );
		return new WP_Error( 'enrollment_exception', 'Exception: ' . $e->getMessage(), [ 'status' => 500 ] );
	}
}

/**
 * POST /wp-json/learnDash/v1/user/{id}/courses/{courseId}/reset
 *
 * Reset course progress while maintaining enrollment
 *
 * @param WP_REST_Request $request REST request object
 * @return WP_REST_Response
 */
function meridiana_reset_course_progress( $request ) {
	try {
		$user_id   = absint( $request->get_param( 'user_id' ) );
		$course_id = absint( $request->get_param( 'course_id' ) );

		if ( ! $user_id || ! $course_id ) {
			return new WP_Error( 'invalid_params', 'Missing user_id or course_id', [ 'status' => 400 ] );
		}

		// Verify course exists
		$course = get_post( $course_id );
		if ( ! $course || 'sfwd-courses' !== $course->post_type ) {
			return new WP_Error( 'invalid_course', 'Course does not exist', [ 'status' => 400 ] );
		}

		// Check if user exists
		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			return new WP_Error( 'invalid_user', 'User does not exist', [ 'status' => 400 ] );
		}

		// Check if user is enrolled
		if ( ! meridiana_user_is_enrolled( $user_id, $course_id ) ) {
			return new WP_Error( 'not_enrolled', 'User is not enrolled in this course', [ 'status' => 400 ] );
		}

		// Reset using helper function
		meridiana_reset_course_progress( $user_id, $course_id );

		// Count lessons and quizzes for response
		$lessons = meridiana_get_course_lessons( $course_id );
		$quizzes = get_posts( [
			'post_type'      => 'sfwd-quiz',
			'posts_per_page' => -1,
			'meta_key'       => 'course_id',
			'meta_value'     => $course_id,
			'fields'         => 'ids',
		] );

		return rest_ensure_response( [
			'success'              => true,
			'message'              => 'Progresso del corso azzerato con successo',
			'user_id'              => $user_id,
			'course_id'            => $course_id,
			'lessons_cleared'      => count( $lessons ),
			'quizzes_cleared'      => count( $quizzes ),
			'enrollment_preserved' => true,
			'reset_date'           => current_time( 'mysql' ),
		] );

	} catch ( Exception $e ) {
		error_log( 'LearnDash Course Reset Exception: ' . $e->getMessage() );
		return new WP_Error( 'reset_exception', 'Exception: ' . $e->getMessage(), [ 'status' => 500 ] );
	}
}

/**
 * GET /wp-json/learnDash/v1/courses/{id}/certificate
 *
 * Get certificate for completed course (currently MOCK)
 * TODO: Implement real certificate generation
 *
 * @param WP_REST_Request $request REST request object
 * @return WP_REST_Response
 */
function meridiana_get_course_certificate( $request ) {
	$course_id = absint( $request->get_param( 'course_id' ) );
	$user_id   = get_current_user_id();

	if ( ! $course_id ) {
		return new WP_Error( 'invalid_course', 'Invalid course ID', [ 'status' => 400 ] );
	}

	// TODO: Implement real certificate generation
	// For now, return mock URL

	return rest_ensure_response( [
		'success'      => true,
		'download_url' => home_url( "/certificates/certificate-{$course_id}-user-{$user_id}.pdf" ),
		'course_id'    => $course_id,
		'user_id'      => $user_id,
		'filename'     => "certificato-corso-{$course_id}.pdf",
	] );
}

/**
 * POST /wp-json/learnDash/v1/lessons/{id}/mark-viewed
 *
 * Mark lesson as completed for user
 *
 * @param WP_REST_Request $request REST request object
 * @return WP_REST_Response
 */
function meridiana_mark_lesson_viewed( $request ) {
	$lesson_id = absint( $request->get_param( 'lesson_id' ) );
	$user_id   = get_current_user_id();
	$duration  = absint( $request->get_param( 'duration' ) ?: 0 );

	if ( ! $lesson_id ) {
		return new WP_Error( 'invalid_lesson', 'Invalid lesson ID', [ 'status' => 400 ] );
	}

	if ( ! $user_id ) {
		return new WP_Error( 'invalid_user', 'User not logged in', [ 'status' => 401 ] );
	}

	// Verify lesson exists
	$lesson = get_post( $lesson_id );
	if ( ! $lesson || 'sfwd-lessons' !== $lesson->post_type ) {
		return new WP_Error( 'invalid_lesson_post', 'Lesson does not exist', [ 'status' => 400 ] );
	}

	// Mark lesson complete using helper
	meridiana_mark_lesson_complete( $user_id, $lesson_id );

	return rest_ensure_response( [
		'success'   => true,
		'message'   => 'Lezione marcata come completata',
		'lesson_id' => $lesson_id,
		'user_id'   => $user_id,
		'duration'  => $duration,
		'marked_at' => current_time( 'mysql' ),
	] );
}

/**
 * POST /wp-json/learnDash/v1/quizzes/{id}/submit
 *
 * Submit quiz answers
 *
 * @param WP_REST_Request $request REST request object
 * @return WP_REST_Response
 */
function meridiana_submit_quiz( $request ) {
	$quiz_id = absint( $request->get_param( 'quiz_id' ) );
	$user_id = get_current_user_id();
	$answers = $request->get_json_params();

	if ( ! $quiz_id ) {
		return new WP_Error( 'invalid_quiz', 'Invalid quiz ID', [ 'status' => 400 ] );
	}

	if ( ! $user_id ) {
		return new WP_Error( 'invalid_user', 'User not logged in', [ 'status' => 401 ] );
	}

	// Verify quiz exists
	$quiz = get_post( $quiz_id );
	if ( ! $quiz || 'sfwd-quiz' !== $quiz->post_type ) {
		return new WP_Error( 'invalid_quiz_post', 'Quiz does not exist', [ 'status' => 400 ] );
	}

	// Prepare submission data
	$submission_data = [
		'quiz_id'      => $quiz_id,
		'user_id'      => $user_id,
		'answers'      => isset( $answers['answers'] ) ? $answers['answers'] : [],
		'submitted_at' => current_time( 'timestamp' ),
	];

	// Mark quiz complete using helper
	meridiana_mark_quiz_complete( $user_id, $quiz_id, $submission_data );

	return rest_ensure_response( [
		'success'      => true,
		'message'      => 'Quiz completato con successo',
		'quiz_id'      => $quiz_id,
		'user_id'      => $user_id,
		'submitted_at' => current_time( 'mysql' ),
		'results'      => [
			'score'      => 0, // TODO: Calculate actual score based on answers
			'percentage' => 0, // TODO: Calculate percentage
		],
	] );
}
