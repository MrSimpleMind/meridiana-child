<?php
/**
 * Enqueue: Gestore Dashboard JavaScript
 * 
 * IMPORTANTE: gestore-dashboard.js DEVE caricare PRIMA di Alpine.js
 * altrimenti Alpine non troverà il component gestoreDashboard() registrato
 */

function meridiana_enqueue_gestore_dashboard() {
    if (is_page('dashboard-gestore') && is_user_logged_in() && (current_user_can('manage_platform') || current_user_can('manage_options'))) {
        // Carica gestore-dashboard.js PRIMA, in head, senza dipendenze da Alpine
        wp_enqueue_script(
            'meridiana-gestore-dashboard',
            MERIDIANA_CHILD_URI . '/assets/js/src/gestore-dashboard.js',
            array(), // NO dipendenze
            MERIDIANA_CHILD_VERSION,
            false // in head
        );
    }
}
// Priorità alta (20) per caricare PRIMA della priorità standard (10)
add_action('wp_enqueue_scripts', 'meridiana_enqueue_gestore_dashboard', 20);
