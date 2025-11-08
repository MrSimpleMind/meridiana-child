<?php
/**
 * Meridiana Child Theme Functions
 * 
 * Parent Theme: Blocksy (Free)
 * Piattaforma Formazione Cooperativa La Meridiana
 */

if (!defined('ABSPATH')) exit;

if (!defined('WP_MEMORY_LIMIT')) {
    define('WP_MEMORY_LIMIT', '256M');
}

define('MERIDIANA_CHILD_VERSION', '1.0.1');
define('MERIDIANA_CHILD_DIR', get_stylesheet_directory());
define('MERIDIANA_CHILD_URI', get_stylesheet_directory_uri());
// Database setup
require_once MERIDIANA_CHILD_DIR . '/includes/db-setup.php';

// LearnDash helpers
require_once MERIDIANA_CHILD_DIR . '/includes/learndash-helpers.php';

// Admin test data generator (development only)
if (is_admin()) {
    require_once MERIDIANA_CHILD_DIR . '/inc/admin-test-data-page.php';
}

/**
 * ENQUEUE STYLES & SCRIPTS
 */

function meridiana_enqueue_styles() {
    wp_enqueue_style(
        'blocksy-parent-style',
        get_template_directory_uri() . '/style.css',
        array(),
        wp_get_theme('blocksy')->get('Version')
    );
    
    // Force cache bust on CSS file
    $css_file_path = MERIDIANA_CHILD_DIR . '/assets/css/dist/main.css';
    $css_version = file_exists($css_file_path) ? filemtime($css_file_path) : MERIDIANA_CHILD_VERSION;
    
    wp_enqueue_style(
        'meridiana-child-style',
        MERIDIANA_CHILD_URI . '/assets/css/dist/main.css',
        array('blocksy-parent-style'),
        $css_version
    );
    
    // CSS comunicazioni integrato in main.css via SCSS
}
add_action('wp_enqueue_scripts', 'meridiana_enqueue_styles');

/**
 * Returns true when the current request should load the analytics assets.
 *
 * WordPress may load page-analitiche.php directly via its slug, so
 * is_page_template('page-analitiche.php') can be false unless the template is
 * explicitly assigned in the admin. We fall back to checking common slugs to
 * avoid missing the enqueue.
 */
function meridiana_is_analytics_page() {
    if (!is_page()) {
        return false;
    }

    $matches_template = is_page_template('page-analitiche.php');

    $queried_object = get_queried_object();
    $analytics_slugs = array('analitiche', 'analytics');

    $matches_slug = ($queried_object instanceof WP_Post)
        ? in_array($queried_object->post_name, $analytics_slugs, true)
        : false;

    $is_analytics_page = $matches_template || $matches_slug;

    return (bool) apply_filters('meridiana_is_analytics_page', $is_analytics_page, $queried_object);
}

function meridiana_get_analytics_document_options() {
    $cached = wp_cache_get('meridiana_analytics_documents', 'meridiana');
    if ($cached !== false) {
        return $cached;
    }

    $documents = get_posts(array(
        'post_type' => array('protocollo', 'modulo'),
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'no_found_rows' => true,
    ));

    $formatted = array();

    foreach ($documents as $doc) {
        $formatted[] = array(
            'ID' => $doc->ID,
            'post_title' => get_the_title($doc),
            'post_type' => $doc->post_type,
            'modified_at' => get_post_modified_time('Y-m-d H:i:s', true, $doc),
        );
    }

    wp_cache_set('meridiana_analytics_documents', $formatted, 'meridiana', HOUR_IN_SECONDS);

    return $formatted;
}

function meridiana_enqueue_scripts() {
    error_log('meridiana_enqueue_scripts called');

    
    add_filter('script_loader_tag', 'meridiana_defer_alpinejs', 10, 2);
    
    // Alpine inizializza DOPO che gestore-dashboard.js è caricato
    
    $js_file = MERIDIANA_CHILD_DIR . '/assets/js/dist/main.min.js';
    $js_version = file_exists($js_file) ? md5_file($js_file) : MERIDIANA_CHILD_VERSION;
    
    wp_enqueue_script(
        'meridiana-child-scripts',
        MERIDIANA_CHILD_URI . '/assets/js/dist/main.min.js',
        array(), // Ora nessuna dipendenza da alpinejs
        $js_version,
        true
    );
    
    if (is_user_logged_in()) {
        wp_enqueue_script(
            'meridiana-avatar-persistence',
            MERIDIANA_CHILD_URI . '/assets/js/avatar-persistence.js',
            array(),
            MERIDIANA_CHILD_VERSION,
            true
        );
        
        wp_localize_script(
            'meridiana-avatar-persistence',
            'meridianaAvatarData',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('meridiana_avatar_save'),
            )
        );
    }
    
    wp_localize_script('meridiana-child-scripts', 'meridiana', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'resturl' => rest_url('piattaforma/v1/'),
        'nonce' => wp_create_nonce('wp_rest'),
        'userId' => get_current_user_id(),
        'isAdmin' => current_user_can('manage_options'),
        'isGestore' => current_user_can('gestore_piattaforma'),
    ));
    
    wp_enqueue_script(
        'meridiana-comunicazioni-filter',
        MERIDIANA_CHILD_URI . '/assets/js/comunicazioni-filter.js',
        array('meridiana-child-scripts'),
        MERIDIANA_CHILD_VERSION,
        true
    );
    
    wp_enqueue_script(
        'meridiana-archive-articoli',
        MERIDIANA_CHILD_URI . '/assets/js/src/archive-articoli.js',
        array('meridiana-child-scripts'),
        MERIDIANA_CHILD_VERSION,
        true
    );

    // Enqueue Analitiche scripts only on the Analitiche page
    if (meridiana_is_analytics_page()) {
        // Enqueue Analitiche custom script (Chart.js is now bundled via Webpack)
        $analitiche_file = MERIDIANA_CHILD_DIR . '/assets/js/dist/analitiche.min.js';
        $analitiche_version = file_exists($analitiche_file) ? md5_file($analitiche_file) : MERIDIANA_CHILD_VERSION;

        wp_enqueue_script(
            'meridiana-analitiche-scripts',
            MERIDIANA_CHILD_URI . '/assets/js/dist/analitiche.min.js',
            array(), // No dependencies - Chart.js is bundled
            $analitiche_version, // Use file hash for cache busting
            true
        );

        wp_localize_script(
            'meridiana-analitiche-scripts',
            'meridianaAnalyticsData',
            array(
                'documents' => meridiana_get_analytics_document_options(),
            )
        );
    }
}
add_action('wp_enqueue_scripts', 'meridiana_enqueue_scripts');

function meridiana_defer_alpinejs($tag, $handle) {
    if ('alpinejs' === $handle) {
        // NON usare defer su Alpine perché deve attendere gestore-dashboard.js
        // Lascia il loading syncronous
        return $tag;
    }
    return $tag;
}

/**
 * INLINE STYLES - MIGRATED TO SCSS ✅
 *
 * Migration completed: 2025-01-08
 * - .bottom-nav-overlay → layout/_navigation.scss
 * - .convenzione-card → pages/_home.scss
 * - .salute-card → pages/_home.scss
 * - .no-content → pages/_home.scss
 * - Single salute styles → pages/_single-salute-benessere.scss
 *
 * This function is now DISABLED. All styles moved to SCSS design system.
 */

// REMOVED: meridiana_add_inline_styles() - All styles migrated to SCSS
// This function was causing PHP parse errors due to HTML/CSS content within PHP comment blocks
// add_action('wp_head', 'meridiana_add_inline_styles', 99); // DISABLED - Migrated to SCSS

/**
 * INCLUDE FILES
 */

require_once MERIDIANA_CHILD_DIR . '/includes/analytics.php';
require_once MERIDIANA_CHILD_DIR . '/includes/acf-config.php';
require_once MERIDIANA_CHILD_DIR . '/includes/taxonomies.php';
require_once MERIDIANA_CHILD_DIR . '/includes/user-roles.php';
require_once MERIDIANA_CHILD_DIR . '/includes/membership.php';
require_once MERIDIANA_CHILD_DIR . '/includes/design-system-demo.php';
require_once MERIDIANA_CHILD_DIR . '/includes/ajax-user-profile.php';
require_once MERIDIANA_CHILD_DIR . '/includes/avatar-system.php';
require_once MERIDIANA_CHILD_DIR . '/includes/avatar-selector.php';
require_once MERIDIANA_CHILD_DIR . '/includes/avatar-persistence.php';
require_once MERIDIANA_CHILD_DIR . '/includes/breadcrumb-navigation.php';
require_once MERIDIANA_CHILD_DIR . '/includes/comunicazioni-filter.php';
require_once MERIDIANA_CHILD_DIR . '/includes/helpers.php';
require_once MERIDIANA_CHILD_DIR . '/includes/security.php';
require_once MERIDIANA_CHILD_DIR . '/includes/gestore-enqueue.php';
require_once MERIDIANA_CHILD_DIR . '/includes/corsi-enqueue.php';
require_once MERIDIANA_CHILD_DIR . '/includes/auto-create-pages.php';
require_once MERIDIANA_CHILD_DIR . '/includes/ajax-gestore-handlers.php';
require_once MERIDIANA_CHILD_DIR . '/includes/meridiana-archive-system.php';
require_once MERIDIANA_CHILD_DIR . '/includes/archive-download-handler.php';
require_once MERIDIANA_CHILD_DIR . '/includes/ajax-analytics.php';
require_once MERIDIANA_CHILD_DIR . '/includes/gestore-acf-forms.php';
require_once MERIDIANA_CHILD_DIR . '/includes/acf-media-fix.php';
require_once MERIDIANA_CHILD_DIR . '/includes/acf-label-fix.php';
require_once MERIDIANA_CHILD_DIR . '/includes/notification-system.php';
require_once MERIDIANA_CHILD_DIR . '/includes/notification-frontend.php';
require_once MERIDIANA_CHILD_DIR . '/api/analytics-api.php';
require_once MERIDIANA_CHILD_DIR . '/api/learndash-api.php';

/**
 * TEMPLATE ROUTING
 * 
 * 1. Single Modulo & Protocollo → single-documento.php
 *    Reindirizza i CPT 'modulo' e 'protocollo' al template unificato
 * 
 * 2. Archive Unificato → archive.php
 *    Reindirizza TUTTI gli archive (convenzione, salute-e-benessere-l, post)
 *    al template unificato archive.php con condizionali get_post_type()
 */
add_filter('template_include', function($template) {
    // SINGLE: Modulo & Protocollo
    if (is_singular()) {
        $post_type = get_post_type();
        if ($post_type === 'modulo' || $post_type === 'protocollo') {
            $single_documento = MERIDIANA_CHILD_DIR . '/single-documento.php';
            if (file_exists($single_documento)) {
                return $single_documento;
            }
        }
        return $template;
    }
    
    // ARCHIVE: Tutti i CPT
    if (is_archive()) {
        $post_type = get_post_type();
        
        // Reindirizza TUTTI gli archive a archive.php
        // (convenzione, salute-e-benessere-l, post, etc.)
        $archive_template = MERIDIANA_CHILD_DIR . '/archive.php';
        if (file_exists($archive_template)) {
            return $archive_template;
        }
    }
    
    return $template;
}, 99);

/**
 * THEME SETUP
 */

add_action('after_setup_theme', 'meridiana_remove_blocksy_features');
function meridiana_remove_blocksy_features() {
    remove_theme_support('blocksy-post-formats');
    remove_theme_support('blocksy-sidebar-widgets');
}

add_filter('blocksy:general:container-width', function($width) {
    return 1400;
});

add_action('after_setup_theme', 'meridiana_theme_setup');
function meridiana_theme_setup() {
    load_child_theme_textdomain('meridiana-child', MERIDIANA_CHILD_DIR . '/languages');
    
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script'
    ));
    
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('responsive-embeds');
    
    add_filter('use_block_editor_for_post_type', '__return_false');
}

register_nav_menus(array(
    'primary-mobile' => __('Mobile Bottom Navigation', 'meridiana-child'),
    'primary-desktop' => __('Desktop Top Navigation', 'meridiana-child'),
    'mobile-menu' => __('Mobile Menu Overlay', 'meridiana-child'),
));

function get_current_nav_class($page_slug) {
    $current_page = '';
    
    if (is_front_page()) $current_page = 'home';
    elseif (is_post_type_archive('protocollo') || is_post_type_archive('modulo')) $current_page = 'documentazione';
    elseif (is_post_type_archive('sfwd-courses')) $current_page = 'corsi';
    elseif (is_page('contatti')) $current_page = 'organigramma';
    elseif (is_page('convenzioni')) $current_page = 'convenzioni';
    elseif (is_page('analytics')) $current_page = 'analytics';
    
    return $current_page === $page_slug ? 'active' : '';
}

function meridiana_remove_version_strings($src) {
    global $wp_version;
    parse_str(parse_url($src, PHP_URL_QUERY), $query);
    if (!empty($query['ver']) && $query['ver'] === $wp_version) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}
add_filter('style_loader_src', 'meridiana_remove_version_strings', 9999);
add_filter('script_loader_src', 'meridiana_remove_version_strings', 9999);

remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

function meridiana_disable_embeds_init() {
    remove_action('rest_api_init', 'wp_oembed_register_route');
    add_filter('embed_oembed_discover', '__return_false');
    remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    remove_action('wp_head', 'wp_oembed_add_host_js');
}
add_action('init', 'meridiana_disable_embeds_init', 9999);

function meridiana_theme_activation() {
    flush_rewrite_rules();
    meridiana_create_document_views_table();
}
add_action('after_switch_theme', 'meridiana_theme_activation');

// Temporaneo: Esegui l'aggiornamento del DB una volta per le installazioni esistenti
add_action('init', 'meridiana_run_db_update_once');
function meridiana_run_db_update_once() {
    if (get_option('meridiana_analytics_db_updated') !== '1') {
        meridiana_create_analytics_table();
        update_option('meridiana_analytics_db_updated', '1');
    }
}

/**
 * LEARNDASH INTEGRATION: Mark course as completed when final quiz is passed
 *
 * When user completes the final quiz (Quizzo) with a passing score,
 * mark the course as completed in user meta
 *
 * Hook: learndash_quiz_completed passes ($quiz_id, $user_obj)
 */
add_action('learndash_quiz_completed', 'meridiana_mark_course_complete_on_quiz', 10, 2);
function meridiana_mark_course_complete_on_quiz($quiz_id, $user_obj) {
    // Extract user ID from user object
    if (is_object($user_obj) && isset($user_obj->ID)) {
        $user_id = $user_obj->ID;
    } else {
        return; // Invalid user object
    }

    // Get the quiz post
    $quiz = get_post($quiz_id);
    if (!$quiz || $quiz->post_type !== 'sfwd-quiz') {
        return;
    }

    // Check if this is the final quiz (Quizzo) by slug
    if ($quiz->post_name !== 'quizzo') {
        return;
    }

    // Get the course ID this quiz belongs to
    // The quiz should have course_id in meta
    $course_id = get_post_meta($quiz_id, 'course_id', true);

    // If no course_id found, try to find by post_parent relationship or search
    if (!$course_id) {
        // Search for courses that have this quiz
        $courses = get_posts([
            'post_type' => 'sfwd-courses',
            'numberposts' => 1,
            'meta_query' => [
                [
                    'key' => '_sfwd-course_options',
                    'compare' => 'EXISTS'
                ]
            ]
        ]);

        if (!empty($courses)) {
            $course_id = $courses[0]->ID;
        }
    }

    // If we found a course, mark it as completed
    if ($course_id) {
        // Mark the entire course as completed
        update_user_meta($user_id, '_completed_course_' . $course_id, current_time('timestamp'));

        error_log('LearnDash: Course ' . $course_id . ' marked as completed for user ' . $user_id . ' after passing quiz ' . $quiz_id);
    }
}



