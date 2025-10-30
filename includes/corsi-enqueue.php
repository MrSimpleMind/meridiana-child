<?php
/**
 * Enqueue: LearnDash Courses JavaScript & Styles
 * Carica assets per le pagine dei corsi (dashboard + single course)
 *
 * @package Meridiana Child
 */

if (!defined('ABSPATH')) exit;

/**
 * Verifica se siamo su una pagina relativa ai corsi
 */
function meridiana_is_courses_page() {
    // Check per page-corsi.php (courses dashboard)
    if (is_page('corsi') || is_page_template('page-corsi.php')) {
        return true;
    }

    // Check per single course (sfwd-courses post type)
    if (is_singular('sfwd-courses')) {
        return true;
    }

    // Check per archive courses
    if (is_post_type_archive('sfwd-courses')) {
        return true;
    }

    return false;
}

/**
 * Verifica se siamo sulla dashboard corsi (page-corsi.php)
 */
function meridiana_is_courses_dashboard() {
    return is_page('corsi') || is_page_template('page-corsi.php');
}

/**
 * Enqueue CSS e JavaScript per le pagine dei corsi
 */
function meridiana_enqueue_courses_assets() {
    if (!meridiana_is_courses_page()) {
        return;
    }

    // Verificare che l'utente sia loggato
    if (!is_user_logged_in()) {
        return;
    }

    // ========================================
    // ENQUEUE STYLES
    // ========================================

    // Il CSS dei corsi è incluso nel main.css compilato da SCSS
    // Se vuoi uno file separato, decommentare e assicurarsi che il CSS sia compilato:
    /*
    $courses_css_path = MERIDIANA_CHILD_DIR . '/assets/css/dist/corsi.css';
    $courses_css_version = file_exists($courses_css_path) ? filemtime($courses_css_path) : MERIDIANA_CHILD_VERSION;

    wp_enqueue_style(
        'meridiana-courses-styles',
        MERIDIANA_CHILD_URI . '/assets/css/dist/corsi.css',
        array('meridiana-child-style'),
        $courses_css_version
    );
    */

    // ========================================
    // ENQUEUE SCRIPTS - DASHBOARD ONLY
    // ========================================

    if (meridiana_is_courses_dashboard()) {
        // Enqueue Alpine.js component per la dashboard
        wp_enqueue_script(
            'meridiana-corsi-dashboard',
            MERIDIANA_CHILD_URI . '/assets/js/src/corsi-dashboard.js',
            array(), // Nessuna dipendenza - Alpine.js carica da functions.php
            MERIDIANA_CHILD_VERSION,
            true
        );

        // Localizza lo script con dati richiesti
        wp_localize_script(
            'meridiana-corsi-dashboard',
            'meridianaCourses',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'restUrl' => rest_url('learnDash/v1/'),
                'nonce' => wp_create_nonce('wp_rest'),
                'userId' => get_current_user_id(),
                'currentUser' => array(
                    'id' => get_current_user_id(),
                    'name' => wp_get_current_user()->display_name,
                    'email' => wp_get_current_user()->user_email,
                ),
            )
        );
    }

    // ========================================
    // ENQUEUE SCRIPTS - SINGLE COURSE
    // ========================================

    if (is_singular('sfwd-courses')) {
        // Enqueue Alpine.js per interazioni del singolo corso
        wp_enqueue_script(
            'meridiana-corsi-single',
            MERIDIANA_CHILD_URI . '/assets/js/src/corsi-dashboard.js',
            array(), // Nessuna dipendenza
            MERIDIANA_CHILD_VERSION,
            true
        );

        // Localizza lo script
        wp_localize_script(
            'meridiana-corsi-single',
            'meridianaCourses',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'restUrl' => rest_url('learnDash/v1/'),
                'nonce' => wp_create_nonce('wp_rest'),
                'userId' => get_current_user_id(),
                'courseId' => get_the_ID(),
            )
        );

        // Enqueue course tracking script (optional - se vuoi trackare visualizzazioni del corso)
        if (function_exists('meridiana_enqueue_tracking')) {
            wp_enqueue_script(
                'meridiana-course-tracker',
                MERIDIANA_CHILD_URI . '/assets/js/src/tracking.js',
                array(),
                MERIDIANA_CHILD_VERSION,
                true
            );
        }
    }
}

add_action('wp_enqueue_scripts', 'meridiana_enqueue_courses_assets', 20);

/**
 * Aggiungi body classes per le pagine dei corsi
 * Utile per styling condizionato via CSS
 */
function meridiana_courses_body_classes($classes) {
    if (meridiana_is_courses_page()) {
        $classes[] = 'page-courses';
    }

    if (meridiana_is_courses_dashboard()) {
        $classes[] = 'page-courses-dashboard';
    }

    if (is_singular('sfwd-courses')) {
        $classes[] = 'single-course';
    }

    if (is_post_type_archive('sfwd-courses')) {
        $classes[] = 'archive-courses';
    }

    return $classes;
}

add_filter('body_class', 'meridiana_courses_body_classes');
