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
    $user_id = intval($request->get_param('user_id'));

    if (!$user_id) {
        return new WP_Error('invalid_user', 'Invalid user ID', array('status' => 400));
    }

    // ========================================
    // MOCK DATA - Replace with real LearnDash queries later
    // ========================================

    $user = get_user_by('id', $user_id);
    $user_name = $user ? $user->display_name : 'Utente';

    $data = array(
        'in_progress' => array(
            array(
                'id' => 1,
                'title' => 'Introduzione ai Protocolli Aziendali',
                'description' => 'Corso fondamentale per comprendere i protocolli operativi della struttura.',
                'url' => home_url('/course/intro-protocolli/'),
                'progress' => 65,
                'lessons_total' => 8,
                'lessons_done' => 5,
                'featured_image' => 'https://via.placeholder.com/400x250/3b82f6/ffffff?text=Protocolli',
                'is_enrolled' => true,
                'status' => 'in-progress',
            ),
            array(
                'id' => 2,
                'title' => 'Sicurezza e Igiene sul Lavoro',
                'description' => 'Formazione obbligatoria su norme di sicurezza e igiene.',
                'url' => home_url('/course/sicurezza-igiene/'),
                'progress' => 40,
                'lessons_total' => 6,
                'lessons_done' => 2,
                'featured_image' => 'https://via.placeholder.com/400x250/f59e0b/ffffff?text=Sicurezza',
                'is_enrolled' => true,
                'status' => 'in-progress',
            ),
        ),
        'completed' => array(
            array(
                'id' => 3,
                'title' => 'Comunicazione Efficace in Equipe',
                'description' => 'Come migliorare la comunicazione tra i membri del team.',
                'url' => home_url('/course/comunicazione-equipe/'),
                'progress' => 100,
                'lessons_total' => 5,
                'lessons_done' => 5,
                'featured_image' => 'https://via.placeholder.com/400x250/22c55e/ffffff?text=Comunicazione',
                'is_enrolled' => true,
                'completed_date' => '15/09/2025',
                'status' => 'completed',
            ),
            array(
                'id' => 4,
                'title' => 'Gestione delle Emergenze',
                'description' => 'Procedure di gestione e risposta alle emergenze.',
                'url' => home_url('/course/emergenze/'),
                'progress' => 100,
                'lessons_total' => 7,
                'lessons_done' => 7,
                'featured_image' => 'https://via.placeholder.com/400x250/ef4444/ffffff?text=Emergenze',
                'is_enrolled' => true,
                'completed_date' => '22/08/2025',
                'status' => 'completed',
            ),
        ),
        'optional' => array(
            array(
                'id' => 5,
                'title' => 'Excel Avanzato per Gestionali',
                'description' => 'Approfondimento su Excel per gestione dati e reporting.',
                'url' => home_url('/course/excel-avanzato/'),
                'progress' => 0,
                'lessons_total' => 4,
                'lessons_done' => 0,
                'featured_image' => 'https://via.placeholder.com/400x250/10b981/ffffff?text=Excel',
                'enrolled_count' => 34,
                'is_enrolled' => false,
                'status' => 'optional',
            ),
            array(
                'id' => 6,
                'title' => 'Public Speaking e Presentazioni',
                'description' => 'Tecniche per parlare in pubblico e creare presentazioni efficaci.',
                'url' => home_url('/course/public-speaking/'),
                'progress' => 0,
                'lessons_total' => 6,
                'lessons_done' => 0,
                'featured_image' => 'https://via.placeholder.com/400x250/8b5cf6/ffffff?text=Speaking',
                'enrolled_count' => 22,
                'is_enrolled' => false,
                'status' => 'optional',
            ),
            array(
                'id' => 7,
                'title' => 'Problem Solving Avanzato',
                'description' => 'Metodologie e strumenti per risolvere problemi complessi.',
                'url' => home_url('/course/problem-solving/'),
                'progress' => 0,
                'lessons_total' => 5,
                'lessons_done' => 0,
                'featured_image' => 'https://via.placeholder.com/400x250/06b6d4/ffffff?text=Problem',
                'enrolled_count' => 18,
                'is_enrolled' => false,
                'status' => 'optional',
            ),
        ),
        'certificates' => array(
            array(
                'id' => 'cert_1',
                'courseName' => 'Comunicazione Efficace in Equipe',
                'issuedDate' => '15/09/2025',
                'expiryDate' => null,
            ),
            array(
                'id' => 'cert_2',
                'courseName' => 'Gestione delle Emergenze',
                'issuedDate' => '22/08/2025',
                'expiryDate' => '22/08/2027',
            ),
        ),
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
    $user_id = intval($request->get_param('user_id'));
    $course_id = intval($request->get_param('course_id'));

    if (!$user_id || !$course_id) {
        return new WP_Error('invalid_params', 'Missing user_id or course_id', array('status' => 400));
    }

    // ========================================
    // MOCK: Simula iscrizione riuscita
    // Future: ld_update_user_course_access() e learndash_user_add_course_access()
    // ========================================

    $response = array(
        'success' => true,
        'message' => 'Iscritto al corso con successo',
        'user_id' => $user_id,
        'course_id' => $course_id,
        'enrolled_date' => current_time('mysql'),
    );

    return rest_ensure_response($response);
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
 * Marca lezione come visualizzata per utente
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

    // ========================================
    // MOCK: Simula tracciamento
    // Future: learndash_mark_lesson_complete() + tracking
    // ========================================

    $response = array(
        'success' => true,
        'message' => 'Lezione marcata come visualizzata',
        'lesson_id' => $lesson_id,
        'user_id' => $user_id,
        'duration' => $duration,
        'marked_at' => current_time('mysql'),
    );

    return rest_ensure_response($response);
}
