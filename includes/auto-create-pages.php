<?php
/**
 * Auto-Create Dashboard Gestore Page
 */

function meridiana_create_dashboard_gestore_page() {
    $dashboard_page = get_page_by_path('dashboard-gestore');
    if ($dashboard_page) return;
    
    $page_data = array(
        'post_type'    => 'page',
        'post_title'   => 'Dashboard Gestore',
        'post_name'    => 'dashboard-gestore',
        'post_content' => '',
        'post_status'  => 'publish',
        'post_author'  => 1,
        'menu_order'   => 0,
    );
    
    $page_id = wp_insert_post($page_data);
    if (!is_wp_error($page_id)) {
        error_log('[Meridiana] Dashboard Gestore page created: ' . $page_id);
    }
}

add_action('after_switch_theme', 'meridiana_create_dashboard_gestore_page');
add_action('wp_loaded', function() {
    static $executed = false;
    if (!$executed) {
        meridiana_create_dashboard_gestore_page();
        $executed = true;
    }
}, 999);
