<?php
/**
 * LearnDash Helpers
 *
 * Wrapper functions around native LearnDash functions
 * Provides simplified interface for getting user courses, progress, and enrollment data
 *
 * @package Meridiana_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get user dashboard data (enrolled courses, completed courses, certificates)
 *
 * @param int $user_id User ID
 * @return array Dashboard data with 'courses', 'completed', 'certificates'
 */
function meridiana_get_user_dashboard( $user_id ) {
	$user_id = absint( $user_id );

	if ( ! $user_id ) {
		return [
			'courses'    => [],
			'completed'  => [],
			'certificates' => [],
		];
	}

	$dashboard = [
		'courses'      => [],
		'completed'    => [],
		'certificates' => [],
	];

	// Get all courses enrolled by user
	$user_courses = learndash_get_user_courses( [ 'user_id' => $user_id ] );

	if ( ! empty( $user_courses ) ) {
		foreach ( $user_courses as $course_id => $course_data ) {
			// Get course progress
			$progress = learndash_get_course_progress( [
				'user_id'   => $user_id,
				'course_id' => $course_id,
				'array'     => true,
			] );

			$course_info = [
				'id'               => $course_id,
				'title'            => get_the_title( $course_id ),
				'description'      => wp_trim_words( get_the_excerpt( $course_id ), 20 ),
				'url'              => get_the_permalink( $course_id ),
				'featured_image'   => get_the_post_thumbnail_url( $course_id, 'medium' ),
				'progress'         => isset( $progress['percentage'] ) ? (int) $progress['percentage'] : 0,
				'lessons_total'    => isset( $progress['total'] ) ? (int) $progress['total'] : 0,
				'lessons_done'     => isset( $progress['completed'] ) ? (int) $progress['completed'] : 0,
				'is_enrolled'      => true,
				'completed_date'   => null,
			];

			// Check if course is completed
			if ( $course_info['progress'] >= 100 ) {
				// Get completion date from LearnDash meta
				$completion_meta = get_user_meta( $user_id, 'course_completed_' . $course_id, true );
				$course_info['completed_date'] = ! empty( $completion_meta ) ? $completion_meta : date( 'Y-m-d' );
				$dashboard['completed'][] = $course_info;
			} else {
				$dashboard['courses'][] = $course_info;
			}

			// Get certificates (when implemented)
			// $certificate = learndash_get_course_certificate( [ 'course_id' => $course_id ] );
			// if ( ! empty( $certificate ) ) {
			//     $dashboard['certificates'][] = $certificate;
			// }
		}
	}

	return $dashboard;
}

/**
 * Check if user is enrolled in course
 *
 * @param int $user_id User ID
 * @param int $course_id Course ID
 * @return bool True if enrolled, false otherwise
 */
function meridiana_user_is_enrolled( $user_id, $course_id ) {
	$user_id   = absint( $user_id );
	$course_id = absint( $course_id );

	if ( ! $user_id || ! $course_id ) {
		return false;
	}

	return sfwd_lms_has_access( $course_id, $user_id );
}

/**
 * Enroll user in course (using native LearnDash)
 *
 * @param int $user_id User ID
 * @param int $course_id Course ID
 * @return bool|WP_Error True on success, WP_Error on failure
 */
function meridiana_enroll_user( $user_id, $course_id ) {
	$user_id   = absint( $user_id );
	$course_id = absint( $course_id );

	if ( ! $user_id || ! $course_id ) {
		return new WP_Error( 'invalid_params', 'Invalid user or course ID' );
	}

	// Check if user already enrolled
	if ( meridiana_user_is_enrolled( $user_id, $course_id ) ) {
		return new WP_Error( 'already_enrolled', 'User already enrolled in this course' );
	}

	// Enroll using LearnDash native function
	$enrollment = ld_update_user_course_access( $user_id, $course_id, false );

	if ( ! $enrollment ) {
		return new WP_Error( 'enrollment_failed', 'Failed to enroll user in course' );
	}

	return true;
}

/**
 * Unenroll user from course
 *
 * @param int $user_id User ID
 * @param int $course_id Course ID
 * @return bool|WP_Error True on success, WP_Error on failure
 */
function meridiana_unenroll_user( $user_id, $course_id ) {
	$user_id   = absint( $user_id );
	$course_id = absint( $course_id );

	if ( ! $user_id || ! $course_id ) {
		return new WP_Error( 'invalid_params', 'Invalid user or course ID' );
	}

	// Unenroll using LearnDash native function (third param true = remove)
	$unenrollment = ld_update_user_course_access( $user_id, $course_id, true );

	if ( ! $unenrollment ) {
		return new WP_Error( 'unenrollment_failed', 'Failed to unenroll user from course' );
	}

	return true;
}

/**
 * Get user's enrolled course IDs
 *
 * @param int $user_id User ID
 * @return array Array of course IDs
 */
function meridiana_get_user_enrolled_course_ids( $user_id ) {
	$user_id = absint( $user_id );

	if ( ! $user_id ) {
		return [];
	}

	$courses = learndash_get_user_courses( [ 'user_id' => $user_id ] );

	if ( empty( $courses ) ) {
		return [];
	}

	return array_keys( $courses );
}

/**
 * Get course progress for user
 *
 * @param int $user_id User ID
 * @param int $course_id Course ID
 * @return array Progress data with 'percentage', 'total', 'completed'
 */
function meridiana_get_user_course_progress( $user_id, $course_id ) {
	$user_id   = absint( $user_id );
	$course_id = absint( $course_id );

	if ( ! $user_id || ! $course_id ) {
		return [
			'percentage' => 0,
			'total'      => 0,
			'completed'  => 0,
		];
	}

	$progress = learndash_get_course_progress( [
		'user_id'   => $user_id,
		'course_id' => $course_id,
		'array'     => true,
	] );

	return [
		'percentage' => isset( $progress['percentage'] ) ? (int) $progress['percentage'] : 0,
		'total'      => isset( $progress['total'] ) ? (int) $progress['total'] : 0,
		'completed'  => isset( $progress['completed'] ) ? (int) $progress['completed'] : 0,
	];
}

/**
 * Get all lessons for a course
 *
 * @param int $course_id Course ID
 * @return array Array of lesson posts
 */
function meridiana_get_course_lessons( $course_id ) {
	$course_id = absint( $course_id );

	if ( ! $course_id ) {
		return [];
	}

	// LearnDash stores course structure in postmeta
	$lessons = get_posts( [
		'post_type'      => 'sfwd-lessons',
		'posts_per_page' => -1,
		'meta_key'       => 'course_id',
		'meta_value'     => $course_id,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	] );

	return $lessons;
}

/**
 * Get all quizzes for a lesson
 *
 * @param int $lesson_id Lesson ID
 * @return array Array of quiz posts
 */
function meridiana_get_lesson_quizzes( $lesson_id ) {
	$lesson_id = absint( $lesson_id );

	if ( ! $lesson_id ) {
		return [];
	}

	// Quizzes are children of lessons (post_parent)
	$quizzes = get_posts( [
		'post_type'      => 'sfwd-quiz',
		'posts_per_page' => -1,
		'post_parent'    => $lesson_id,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	] );

	return $quizzes;
}

/**
 * Check if lesson is completed by user
 *
 * @param int $user_id User ID
 * @param int $lesson_id Lesson ID
 * @return bool True if completed, false otherwise
 */
function meridiana_lesson_is_completed( $user_id, $lesson_id ) {
	$user_id   = absint( $user_id );
	$lesson_id = absint( $lesson_id );

	if ( ! $user_id || ! $lesson_id ) {
		return false;
	}

	// LearnDash stores lesson completion in user meta
	$completed = get_user_meta( $user_id, 'lesson_' . $lesson_id, true );

	return ! empty( $completed );
}

/**
 * Mark lesson as completed for user
 *
 * @param int $user_id User ID
 * @param int $lesson_id Lesson ID
 * @return bool True on success
 */
function meridiana_mark_lesson_complete( $user_id, $lesson_id ) {
	$user_id   = absint( $user_id );
	$lesson_id = absint( $lesson_id );

	if ( ! $user_id || ! $lesson_id ) {
		return false;
	}

	// Mark as complete in LearnDash format
	update_user_meta( $user_id, 'lesson_' . $lesson_id, current_time( 'timestamp' ) );

	// Also trigger LearnDash hook if available
	do_action( 'learndash_lesson_completed', $lesson_id, $user_id );

	return true;
}

/**
 * Check if quiz is completed by user
 *
 * @param int $user_id User ID
 * @param int $quiz_id Quiz ID
 * @return bool True if completed, false otherwise
 */
function meridiana_quiz_is_completed( $user_id, $quiz_id ) {
	$user_id = absint( $user_id );
	$quiz_id = absint( $quiz_id );

	if ( ! $user_id || ! $quiz_id ) {
		return false;
	}

	// LearnDash stores quiz completion in user meta
	$completed = get_user_meta( $user_id, 'quiz_' . $quiz_id, true );

	return ! empty( $completed );
}

/**
 * Mark quiz as completed for user
 *
 * @param int $user_id User ID
 * @param int $quiz_id Quiz ID
 * @param array $quiz_data Quiz submission data
 * @return bool True on success
 */
function meridiana_mark_quiz_complete( $user_id, $quiz_id, $quiz_data = [] ) {
	$user_id = absint( $user_id );
	$quiz_id = absint( $quiz_id );

	if ( ! $user_id || ! $quiz_id ) {
		return false;
	}

	// Mark as complete in LearnDash format
	update_user_meta( $user_id, 'quiz_' . $quiz_id, current_time( 'timestamp' ) );

	// Store quiz submission data if provided
	if ( ! empty( $quiz_data ) ) {
		update_user_meta( $user_id, 'quiz_' . $quiz_id . '_submission', $quiz_data );
	}

	// Trigger LearnDash hook if available
	do_action( 'learndash_quiz_completed', $quiz_id, $user_id, $quiz_data );

	return true;
}

/**
 * Reset course progress for user (keep enrollment)
 *
 * @param int $user_id User ID
 * @param int $course_id Course ID
 * @return bool True on success
 */
function meridiana_reset_course_progress( $user_id, $course_id ) {
	$user_id   = absint( $user_id );
	$course_id = absint( $course_id );

	if ( ! $user_id || ! $course_id ) {
		return false;
	}

	// Get all lessons in course
	$lessons = meridiana_get_course_lessons( $course_id );

	foreach ( $lessons as $lesson ) {
		// Delete lesson completion meta
		delete_user_meta( $user_id, 'lesson_' . $lesson->ID );

		// Get quizzes for this lesson and delete completion
		$quizzes = meridiana_get_lesson_quizzes( $lesson->ID );
		foreach ( $quizzes as $quiz ) {
			delete_user_meta( $user_id, 'quiz_' . $quiz->ID );
			delete_user_meta( $user_id, 'quiz_' . $quiz->ID . '_submission' );
		}
	}

	// Delete course completion meta if exists
	delete_user_meta( $user_id, 'course_completed_' . $course_id );

	return true;
}

/**
 * Get all available courses (for admin/manager)
 *
 * @return array Array of course posts
 */
function meridiana_get_all_courses() {
	$courses = get_posts( [
		'post_type'      => 'sfwd-courses',
		'posts_per_page' => -1,
		'orderby'        => 'post_title',
		'order'          => 'ASC',
		'post_status'    => 'publish',
	] );

	return $courses;
}
