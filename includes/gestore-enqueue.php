<?php
/**
 * Enqueue: Gestore Dashboard JavaScript
 */

function meridiana_enqueue_gestore_dashboard() {
    if (is_page('dashboard-gestore') && is_user_logged_in() && (current_user_can('manage_platform') || current_user_can('manage_options'))) {
        wp_enqueue_script(
            'meridiana-gestore-dashboard',
            MERIDIANA_CHILD_URI . '/assets/js/src/gestore-dashboard.js',
            array('alpinejs', 'meridiana-child-scripts'),
            MERIDIANA_CHILD_VERSION,
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'meridiana_enqueue_gestore_dashboard', 15);
