<?php
/**
 * LearnDash REST API Endpoints
 *
 * Endpoints per integrazione corsi LearnDash con frontend
 * Attualmente: dati MOCK per testing UI
 * Future: integrazione con funzioni LearnDash reali
 *
 * @package Meridiana Child
 */

if (!defined('ABSPATH')) exit;

/**
 * Registra tutti gli endpoint LearnDash
 */
function meridiana_register_learndash_endpoints() {

    // GET /wp-json/learnDash/v1/user/{id}/courses
    register_rest_route('learnDash/v1', '/user/(?P<user_id>\d+)/courses', array(
        'methods' => 'GET',
        'callback' => 'meridiana_get_user_courses',
        'permission_callback' => function($request) {
            $user_id = $request->get_param('user_id');
            // Utente può vedere i propri dati, admin vede tutti
            return get_current_user_id() == $user_id || current_user_can('manage_options');
        },
    ));

    // POST /wp-json/learnDash/v1/user/{id}/courses/{courseId}/enroll
    register_rest_route('learnDash/v1', '/user/(?P<user_id>\d+)/courses/(?P<course_id>\d+)/enroll', array(
        'methods' => 'POST',
        'callback' => 'meridiana_enroll_user_in_course',
        'permission_callback' => function($request) {
            $user_id = $request->get_param('user_id');
            return is_user_logged_in() && (get_current_user_id() == $user_id || current_user_can('manage_options'));
        },
    ));

    // GET /wp-json/learnDash/v1/courses/{id}/certificate
    register_rest_route('learnDash/v1', '/courses/(?P<course_id>\d+)/certificate', array(
        'methods' => 'GET',
        'callback' => 'meridiana_get_course_certificate',
        'permission_callback' => function() {
            return is_user_logged_in();
        },
    ));

    // POST /wp-json/learnDash/v1/lessons/{id}/mark-viewed
    register_rest_route('learnDash/v1', '/lessons/(?P<lesson_id>\d+)/mark-viewed', array(
        'methods' => 'POST',
        'callback' => 'meridiana_mark_lesson_viewed',
        'permission_callback' => function() {
            return is_user_logged_in();
        },
    ));
}

add_action('rest_api_init', 'meridiana_register_learndash_endpoints');

/**
 * GET /wp-json/learnDash/v1/user/{id}/courses
 *
 * Ritorna lista corsi per utente con stato (in-progress, completed, optional, certificates)
 *
 * @return WP_REST_Response
 */
function meridiana_get_user_courses($request) {
    global $wpdb;
    $user_id = intval($request->get_param('user_id'));

    if (!$user_id) {
        return new WP_Error('invalid_user', 'Invalid user ID', array('status' => 400));
    }

    // ========================================
    // REAL LEARNDASH DATA - Direct database queries
    // ========================================

    $courses = array();
    $completed = array();
    $certificates = array();

    // Get all courses from database
    $all_courses = get_posts(array(
        'post_type'      => 'sfwd-courses',
        'numberposts'    => -1,
        'post_status'    => 'publish',
    ));

    if (empty($all_courses)) {
        // No courses found
        $data = array(
            'courses'      => array(),
            'completed'    => array(),
            'certificates' => array(),
        );
        return rest_ensure_response($data);
    }

    foreach ($all_courses as $course) {
        $course_id = $course->ID;

        // Fetch course data
        $course_data = array(
            'id'          => $course_id,
            'title'       => $course->post_title,
            'description' => wp_trim_words($course->post_content, 20, '...'),
            'url'         => get_permalink($course_id),
            'featured_image' => wp_get_attachment_url(get_post_thumbnail_id($course_id)) ?: 'https://via.placeholder.com/400x250/3b82f6/ffffff?text=' . urlencode($course->post_title),
        );

        // Check if user is enrolled (via user meta - our custom enrollment system)
        $enrolled_meta = get_user_meta($user_id, '_enrolled_course_' . $course_id, true);
        $is_enrolled = !empty($enrolled_meta);

        if ($is_enrolled) {
            // User is enrolled - Get progress from lesson completion
            $lessons = get_posts(array(
                'post_type'      => 'sfwd-lessons',
                'numberposts'    => -1,
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
                'meta_key'       => 'course_id',
                'meta_value'     => $course_id,
                'fields'         => 'ids',
            ));

            $lessons_count = count($lessons);
            $lessons_completed = 0;

            // Count completed lessons using user_meta
            if ($lessons_count > 0) {
                foreach ($lessons as $lesson_id) {
                    // Check if lesson is marked complete in user meta
                    $lesson_completed = get_user_meta($user_id, '_completed_lesson_' . $lesson_id, true);
                    if (!empty($lesson_completed)) {
                        $lessons_completed++;
                    }
                }
                $progress_percent = round(($lessons_completed / $lessons_count) * 100);
            } else {
                $progress_percent = 0;
            }

            $course_data['progress'] = $progress_percent;
            $course_data['lessons_total'] = $lessons_count;
            $course_data['lessons_done'] = $lessons_completed;
            $course_data['is_enrolled'] = true;

            // Separate by completion status
            if ($progress_percent === 100) {
                $course_data['completedDate'] = date('d/m/Y', current_time('timestamp'));
                $course_data['completed_date'] = $course_data['completedDate'];
                $completed[] = $course_data;

                // Add certificate
                $has_certificate = get_post_meta($course_id, 'course_certificate', true);
                if ($has_certificate) {
                    $cert_data = array(
                        'id'         => 'cert_' . $course_id,
                        'courseName' => $course->post_title,
                        'issuedDate' => date('d/m/Y', current_time('timestamp')),
                        'expiryDate' => null,
                    );

                    $expiration = get_post_meta($course_id, 'course_certificate_expiration', true);
                    if ($expiration) {
                        $expiry_timestamp = current_time('timestamp') + ($expiration * 86400);
                        $cert_data['expiryDate'] = date('d/m/Y', $expiry_timestamp);
                    }

                    $certificates[] = $cert_data;
                }
            } else {
                // In progress (0-99%)
                $courses[] = $course_data;
            }
        } else {
            // User not enrolled - Show in courses list with enrollment button
            $course_data['progress'] = 0;
            $course_data['is_enrolled'] = false;
            $courses[] = $course_data;
        }
    }

    $data = array(
        'courses'      => $courses,
        'completed'    => $completed,
        'certificates' => $certificates,
    );

    return rest_ensure_response($data);
}

/**
 * POST /wp-json/learnDash/v1/user/{id}/courses/{courseId}/enroll
 *
 * Iscrivi utente a corso
 *
 * @return WP_REST_Response
 */
function meridiana_enroll_user_in_course($request) {
    try {
        $user_id = intval($request->get_param('user_id'));
        $course_id = intval($request->get_param('course_id'));

        if (!$user_id || !$course_id) {
            return new WP_Error('invalid_params', 'Missing user_id or course_id', array('status' => 400));
        }

        // Verify course exists
        $course = get_post($course_id);
        if (!$course || $course->post_type !== 'sfwd-courses') {
            return new WP_Error('invalid_course', 'Course does not exist', array('status' => 400));
        }

        // Check if user exists
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return new WP_Error('invalid_user', 'User does not exist', array('status' => 400));
        }

        // Check if already enrolled (via user meta)
        $enrolled_meta = get_user_meta($user_id, '_enrolled_course_' . $course_id, true);
        if (!empty($enrolled_meta)) {
            return new WP_Error('already_enrolled', 'User is already enrolled in this course', array('status' => 400));
        }

        // Enroll user - store in user meta
        update_user_meta($user_id, '_enrolled_course_' . $course_id, current_time('timestamp'));

        $response = array(
            'success' => true,
            'message' => 'Iscritto al corso con successo',
            'user_id' => $user_id,
            'course_id' => $course_id,
            'enrolled_date' => current_time('mysql'),
        );

        return rest_ensure_response($response);

    } catch (Exception $e) {
        error_log('LearnDash Enrollment Exception: ' . $e->getMessage());
        return new WP_Error('enrollment_exception', 'Exception: ' . $e->getMessage(), array('status' => 500));
    }
}

/**
 * GET /wp-json/learnDash/v1/courses/{id}/certificate
 *
 * Ritorna URL certificato per download
 *
 * @return WP_REST_Response
 */
function meridiana_get_course_certificate($request) {
    $course_id = intval($request->get_param('course_id'));
    $user_id = get_current_user_id();

    if (!$course_id) {
        return new WP_Error('invalid_course', 'Invalid course ID', array('status' => 400));
    }

    // ========================================
    // MOCK: Ritorna URL certificato finto
    // Future: Genera PDF reale o ritorna URL da LearnDash
    // ========================================

    $response = array(
        'success' => true,
        'download_url' => home_url("/certificates/certificate-{$course_id}-user-{$user_id}.pdf"),
        'course_id' => $course_id,
        'user_id' => $user_id,
        'filename' => "certificato-corso-{$course_id}.pdf",
    );

    return rest_ensure_response($response);
}

/**
 * POST /wp-json/learnDash/v1/lessons/{id}/mark-viewed
 *
 * Marca lezione come visualizzata per utente - SALVA nel database
 *
 * @return WP_REST_Response
 */
function meridiana_mark_lesson_viewed($request) {
    $lesson_id = intval($request->get_param('lesson_id'));
    $user_id = get_current_user_id();
    $duration = intval($request->get_param('duration') ?: 0);

    if (!$lesson_id) {
        return new WP_Error('invalid_lesson', 'Invalid lesson ID', array('status' => 400));
    }

    if (!$user_id) {
        return new WP_Error('invalid_user', 'User not logged in', array('status' => 401));
    }

    // Verify lesson exists
    $lesson = get_post($lesson_id);
    if (!$lesson || $lesson->post_type !== 'sfwd-lessons') {
        return new WP_Error('invalid_lesson_post', 'Lesson does not exist', array('status' => 400));
    }

    // ========================================
    // SAVE: Salva il completamento della lezione nel database
    // ========================================

    // Save lesson completion using user meta
    update_user_meta($user_id, '_completed_lesson_' . $lesson_id, current_time('timestamp'));

    $response = array(
        'success' => true,
        'message' => 'Lezione marcata come completata',
        'lesson_id' => $lesson_id,
        'user_id' => $user_id,
        'duration' => $duration,
        'marked_at' => current_time('mysql'),
    );

    return rest_ensure_response($response);
}
