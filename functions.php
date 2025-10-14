<?php
/**
 * Meridiana Child Theme Functions
 * 
 * Parent Theme: Blocksy (Free)
 * Piattaforma Formazione Cooperativa La Meridiana
 * 
 * Questo file orchestr tutti i componenti del child theme.
 * Include enqueue di stili/script, caricamento moduli PHP, 
 * configurazione API REST e hook WordPress custom.
 */

// Previeni accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

// Definisci costanti utili
define('MERIDIANA_CHILD_VERSION', '1.0.0');
define('MERIDIANA_CHILD_DIR', get_stylesheet_directory());
define('MERIDIANA_CHILD_URI', get_stylesheet_directory_uri());

/**
 * =====================================================================
 * ENQUEUE STYLES & SCRIPTS
 * =====================================================================
 */

function meridiana_enqueue_styles() {
    // Enqueue parent theme (Blocksy)
    wp_enqueue_style(
        'blocksy-parent-style',
        get_template_directory_uri() . '/style.css',
        array(),
        wp_get_theme('blocksy')->get('Version')
    );
    
    // Enqueue child theme compiled CSS
    $css_file = MERIDIANA_CHILD_DIR . '/assets/css/dist/main.min.css';
    $css_version = file_exists($css_file) ? filemtime($css_file) : MERIDIANA_CHILD_VERSION;
    
    wp_enqueue_style(
        'meridiana-child-style',
        MERIDIANA_CHILD_URI . '/assets/css/dist/main.min.css',
        array('blocksy-parent-style'),
        $css_version
    );
}
add_action('wp_enqueue_scripts', 'meridiana_enqueue_styles');

function meridiana_enqueue_scripts() {
    // Enqueue Alpine.js (lightweight reactive framework - 15kb)
    wp_enqueue_script(
        'alpinejs',
        'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js',
        array(),
        '3.13.0',
        true
    );
    
    // Defer Alpine.js
    add_filter('script_loader_tag', 'meridiana_defer_alpinejs', 10, 2);
    
    // Enqueue child theme JS
    $js_file = MERIDIANA_CHILD_DIR . '/assets/js/dist/main.min.js';
    $js_version = file_exists($js_file) ? filemtime($js_file) : MERIDIANA_CHILD_VERSION;
    
    wp_enqueue_script(
        'meridiana-child-scripts',
        MERIDIANA_CHILD_URI . '/assets/js/dist/main.min.js',
        array('alpinejs'),
        $js_version,
        true
    );
    
    // Localize script per REST API e variabili globali
    wp_localize_script('meridiana-child-scripts', 'meridiana', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'resturl' => rest_url('piattaforma/v1/'),
        'nonce' => wp_create_nonce('wp_rest'),
        'userId' => get_current_user_id(),
        'isAdmin' => current_user_can('manage_options'),
        'isGestore' => current_user_can('gestore_piattaforma'),
    ));
}
add_action('wp_enqueue_scripts', 'meridiana_enqueue_scripts');

// Defer Alpine.js per performance
function meridiana_defer_alpinejs($tag, $handle) {
    if ('alpinejs' === $handle) {
        return str_replace(' src', ' defer src', $tag);
    }
    return $tag;
}

/**
 * =====================================================================
 * INCLUDE FILES - Moduli PHP organizzati
 * =====================================================================
 */

// Custom Post Types (gestiti tramite ACF Pro UI - no file PHP necessario)
// require_once MERIDIANA_CHILD_DIR . '/includes/cpt-register.php';

// Taxonomies (gestite tramite ACF Pro UI - no file PHP necessario)
// require_once MERIDIANA_CHILD_DIR . '/includes/taxonomies.php';

// Configurazione ACF (JSON sync + helper functions)
require_once MERIDIANA_CHILD_DIR . '/includes/acf-config.php';

// ACF Frontend Forms per Gestore Piattaforma
// require_once MERIDIANA_CHILD_DIR . '/includes/acf-forms.php';

// User Roles & Capabilities custom
require_once MERIDIANA_CHILD_DIR . '/includes/user-roles.php';

// Membership logic (forza login globale)
require_once MERIDIANA_CHILD_DIR . '/includes/membership.php';
require_once MERIDIANA_CHILD_DIR . '/includes/design-system-demo.php';
// require_once MERIDIANA_CHILD_DIR . '/includes/analytics.php';

// Notifiche (OneSignal + Brevo)
// require_once MERIDIANA_CHILD_DIR . '/includes/notifications.php';

// File management system
// require_once MERIDIANA_CHILD_DIR . '/includes/file-management.php';

// Helper functions
require_once MERIDIANA_CHILD_DIR . '/includes/helpers.php';

// Security hardening
require_once MERIDIANA_CHILD_DIR . '/includes/security.php';

/**
 * =====================================================================
 * API ENDPOINTS - REST API custom
 * =====================================================================
 */

// Registrazione endpoints
// require_once MERIDIANA_CHILD_DIR . '/api/rest-endpoints.php';

// Analytics API
// require_once MERIDIANA_CHILD_DIR . '/api/analytics-api.php';

// Notifications API
// require_once MERIDIANA_CHILD_DIR . '/api/notifications-api.php';

/**
 * =====================================================================
 * BLOCKSY CUSTOMIZATION
 * =====================================================================
 */

// Rimuovi features Blocksy non necessarie
add_action('after_setup_theme', 'meridiana_remove_blocksy_features');
function meridiana_remove_blocksy_features() {
    // Remove support per features che non servono (alleggerisce il tema)
    remove_theme_support('blocksy-post-formats');
    // Sidebar non necessaria per questa piattaforma
    remove_theme_support('blocksy-sidebar-widgets');
}

// Custom container width
add_filter('blocksy:general:container-width', function($width) {
    return 1400; // Max-width container custom
});

// Disabilita header Blocksy default (userai custom navigation)
// add_action('wp', function() {
//     if (!is_admin()) {
//         remove_action('blocksy:header:render', 'blocksy_output_header');
//         add_action('blocksy:header:render', 'meridiana_custom_header');
//     }
// });

/**
 * =====================================================================
 * THEME SUPPORT & FEATURES
 * =====================================================================
 */

add_action('after_setup_theme', 'meridiana_theme_setup');
function meridiana_theme_setup() {
    // Supporto per i18n (traduzioni future)
    load_child_theme_textdomain('meridiana-child', MERIDIANA_CHILD_DIR . '/languages');
    
    // HTML5 support
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script'
    ));
    
    // Post thumbnails (già in Blocksy ma confermiamo)
    add_theme_support('post-thumbnails');
    
    // Custom logo
    add_theme_support('custom-logo', array(
        'height'      => 100,
        'width'       => 400,
        'flex-height' => true,
        'flex-width'  => true,
    ));
    
    // Title tag
    add_theme_support('title-tag');
    
    // Responsive embeds
    add_theme_support('responsive-embeds');
    
    // Editor styles (se serve Gutenberg backend)
    // add_theme_support('editor-styles');
}

/**
 * =====================================================================
 * NAVIGATION MENUS
 * =====================================================================
 */

// Registra menu locations
register_nav_menus(array(
    'primary-mobile' => __('Mobile Bottom Navigation', 'meridiana-child'),
    'primary-desktop' => __('Desktop Top Navigation', 'meridiana-child'),
    'mobile-menu' => __('Mobile Menu Overlay', 'meridiana-child'),
));

/**
 * =====================================================================
 * CUSTOM FUNCTIONS
 * =====================================================================
 */

/**
 * Ottieni classe CSS per navigazione attiva
 * 
 * @param string $page_slug Slug della pagina
 * @return string Classe 'active' se pagina corrente, stringa vuota altrimenti
 */
function get_current_nav_class($page_slug) {
    global $post;
    
    $current_page = '';
    
    if (is_front_page()) {
        $current_page = 'home';
    } elseif (is_post_type_archive('protocollo') || is_singular('protocollo') || is_post_type_archive('modulo') || is_singular('modulo')) {
        $current_page = 'documentazione';
    } elseif (is_post_type_archive('sfwd-courses') || is_singular('sfwd-courses') || is_singular('sfwd-lessons') || is_singular('sfwd-topic')) {
        $current_page = 'corsi';
    } elseif (is_page('organigramma') || is_singular('organigramma')) {
        $current_page = 'organigramma';
    } elseif (is_page('convenzioni') || is_singular('convenzione')) {
        $current_page = 'convenzioni';
    } elseif (is_page('salute-benessere') || is_singular('salute_benessere')) {
        $current_page = 'salute-benessere';
    } elseif (is_page('analytics')) {
        $current_page = 'analytics';
    }
    
    return $current_page === $page_slug ? 'active' : '';
}

/**
 * Debug helper (solo per sviluppo)
 * Rimuovere in produzione
 */
if (WP_DEBUG && WP_DEBUG_DISPLAY) {
    function meridiana_debug($data, $label = '') {
        echo '<pre style="background: #f5f5f5; padding: 10px; border: 1px solid #ccc; margin: 10px 0;">';
        if ($label) echo '<strong>' . esc_html($label) . ':</strong><br>';
        print_r($data);
        echo '</pre>';
    }
}

/**
 * =====================================================================
 * CLEANUP & OPTIMIZATION
 * =====================================================================
 */

// Rimuovi versione WordPress dai CSS/JS (sicurezza)
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

// Disabilita emoji script (non necessario, alleggerisce pagina)
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

// Disabilita embed.js di WordPress (non serve per questa piattaforma)
function meridiana_disable_embeds_init() {
    remove_action('rest_api_init', 'wp_oembed_register_route');
    add_filter('embed_oembed_discover', '__return_false');
    remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    remove_action('wp_head', 'wp_oembed_add_host_js');
}
add_action('init', 'meridiana_disable_embeds_init', 9999);

/**
 * =====================================================================
 * ACTIVATION HOOKS
 * =====================================================================
 */

// Azioni da eseguire all'attivazione del tema
function meridiana_theme_activation() {
    // Flush rewrite rules per CPT
    flush_rewrite_rules();
    
    // Crea ruoli utente custom (solo se non esistono)
    // Vedi includes/user-roles.php
}
add_action('after_switch_theme', 'meridiana_theme_activation');

/**
 * =====================================================================
 * NOTE PER SVILUPPO
 * =====================================================================
 * 
 * 1. I CPT e le taxonomies vanno create tramite ACF Pro UI
 * 2. I Custom Fields vanno configurati tramite ACF Pro UI
 * 3. Le form frontend useranno acf_form() - vedi includes/acf-forms.php
 * 4. Per compilare SCSS: npm run watch (in locale)
 * 5. Per build production: npm run build
 * 
 * COMANDI NPM:
 * npm install              -> Installa dependencies
 * npm run dev              -> Watch mode (SCSS + JS)
 * npm run build            -> Build production CSS
 * npm run js:build         -> Build production JS
 * 
 * =====================================================================
 */



