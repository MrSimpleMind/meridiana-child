<?php
// api/analytics-api.php

function registra_analytics_endpoints() {
    register_rest_route('piattaforma/v1', '/track-view', array(
        'methods' => 'POST',
        'callback' => 'api_track_view',
        'permission_callback' => 'is_user_logged_in',
    ));
    
    register_rest_route('piattaforma/v1', '/analytics/document/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'api_get_document_analytics',
        'permission_callback' => function() {
            return current_user_can('view_analytics');
        },
    ));
}
add_action('rest_api_init', 'registra_analytics_endpoints');

function api_track_view($request) {
    global $wpdb;

    $document_id = intval($request->get_param('document_id'));
    $duration = intval($request->get_param('duration')); // Secondi
    $document_type = get_post_type($document_id);
    $user_id = get_current_user_id();

    // Validate document exists
    if (!$document_id || !in_array($document_type, ['protocollo', 'modulo'])) {
        return new WP_Error('invalid_document', 'Documento non valido', array('status' => 400));
    }

    // Recupera il profilo professionale dell'utente AL MOMENTO DELLA VISUALIZZAZIONE
    $user_profile = get_user_meta($user_id, 'profilo_professionale', true) ?: null;

    // Insert view
    $result = $wpdb->insert(
        $wpdb->prefix . 'document_views',
        array(
            'user_id' => $user_id,
            'document_id' => $document_id,
            'document_type' => $document_type,
            'user_profile' => $user_profile,
            'view_timestamp' => current_time('mysql'),
            'view_duration' => $duration,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        ),
        array('%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s')
    );

    if ($result === false) {
        return new WP_Error('db_error', 'Errore database', array('status' => 500));
    }

    return rest_ensure_response(array(
        'success' => true,
        'view_id' => $wpdb->insert_id,
    ));
}
