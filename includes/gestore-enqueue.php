<?php
/**
 * Enqueue: Gestore Dashboard JavaScript
 */

function meridiana_enqueue_gestore_dashboard() {
    if (is_page('dashboard-gestore') && is_user_logged_in() && (current_user_can('manage_platform') || current_user_can('manage_options'))) {
        // Enqueue Select2 for multi-select fields
        wp_enqueue_style('select2-css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', array(), '4.0.13');
        wp_enqueue_script('select2-js', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), '4.0.13', true);

        // Enqueue Select2 Italian translation
        wp_enqueue_script('select2-it', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/it.js', array('select2-js'), '4.0.13', true);

        // Carica gestore-dashboard.js SENZA dipendenze
        // (Alpine.js carica DOPO da functions.php con dipendenza a questo script)
        wp_enqueue_script(
            'meridiana-gestore-dashboard',
            MERIDIANA_CHILD_URI . '/assets/js/src/gestore-dashboard.js',
            array(), // NO dipendenze - evita infinite loop!
            MERIDIANA_CHILD_VERSION,
            true
        );

        // Localize script con dati richiesti
        wp_localize_script('meridiana-gestore-dashboard', 'meridiana', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_rest'),
            'userId' => get_current_user_id(),
            'isAdmin' => current_user_can('manage_options'),
            'isGestore' => current_user_can('manage_platform'),
        ));
    }
}
add_action('wp_enqueue_scripts', 'meridiana_enqueue_gestore_dashboard', 20);
