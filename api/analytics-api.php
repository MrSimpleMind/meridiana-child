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
    // Recupera l'UDO dell'utente AL MOMENTO DELLA VISUALIZZAZIONE
    $user_udo = get_user_meta($user_id, 'udo_riferimento', true) ?: null;

    // Recupera il timestamp di ultima modifica del documento (document_version)
    $document_post = get_post($document_id);
    $document_version = ($document_post && $document_post->post_modified_gmt !== '0000-00-00 00:00:00') ? $document_post->post_modified_gmt : current_time('mysql', true);

    $table_name = $wpdb->prefix . 'document_views';

    // Verifica se esiste già una visualizzazione unica per questo utente, documento e versione
    $existing_view = $wpdb->get_row($wpdb->prepare(
        "SELECT id, view_duration FROM $table_name WHERE user_id = %d AND document_id = %d AND document_version = %s",
        $user_id,
        $document_id,
        $document_version
    ));

    if ($existing_view) {
        // Se la visualizzazione esiste già, non fare nulla (come da requisito RF-1)
        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Visualizzazione unica già registrata',
            'view_id' => $existing_view->id,
        ));
    }

    // Inserisci nuova visualizzazione
    $result = $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'document_id' => $document_id,
            'document_type' => $document_type,
            'user_profile' => $user_profile,
            'user_udo' => $user_udo,
            'document_version' => $document_version,
            'view_timestamp' => current_time('mysql'),
            'view_duration' => $duration,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        ),
        array('%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s')
    );

    if ($result === false) {
        return new WP_Error('db_error', 'Errore database', array('status' => 500));
    }

    return rest_ensure_response(array(
        'success' => true,
        'view_id' => $wpdb->insert_id,
    ));
}

/**
 * Endpoint REST API: Griglia Protocolli × Profili Professionali
 * GET /wp-json/piattaforma/v1/analytics/protocol-grid
 *
 * Restituisce una matrice di visualizzazioni uniche per combinazione:
 * (protocollo × profilo professionale) con conteggi e percentuali
 */
function api_get_protocol_grid($request) {
    global $wpdb;

    // Verifica permessi: gestore_piattaforma O admin
    if (!current_user_can('view_analytics') && !current_user_can('manage_options')) {
        return new WP_Error('forbidden', 'Permessi insufficienti', array('status' => 403));
    }

    $table_name = $wpdb->prefix . 'document_views';

    // 1. Query: Prendi TUTTI i protocolli pubblicati (ordinati per titolo)
    $all_protocols = $wpdb->get_results("
        SELECT ID, post_title
        FROM {$wpdb->posts}
        WHERE post_type = 'protocollo'
            AND post_status = 'publish'
        ORDER BY post_title ASC
    ");

    if (!is_array($all_protocols)) {
        $all_protocols = array();
    }

    // 2. Query: Prendi TUTTI i profili professionali unici dal sistema
    // (da usermeta, non solo da chi ha visualizzazioni)
    $profile_totals = $wpdb->get_results("
        SELECT DISTINCT
            um.meta_value as profile_name,
            COUNT(DISTINCT um.user_id) as total_users
        FROM {$wpdb->usermeta} um
        WHERE um.meta_key = 'profilo_professionale'
            AND um.meta_value IS NOT NULL
            AND um.meta_value != ''
        GROUP BY um.meta_value
        ORDER BY um.meta_value ASC
    ");

    // Crea una mappa veloce dei totali per profilo
    $profile_totals_map = array();
    if (is_array($profile_totals)) {
        foreach ($profile_totals as $total) {
            $profile_totals_map[$total->profile_name] = intval($total->total_users);
        }
    }

    // Estrai nomi profili ordinati
    $all_profiles = array_keys($profile_totals_map);
    sort($all_profiles);

    // 3. Query: Prendi TUTTE le visualizzazioni aggregate per (protocollo, profilo)
    $views_data = $wpdb->get_results("
        SELECT
            dv.document_id,
            dv.user_profile,
            COUNT(DISTINCT dv.user_id) as unique_users
        FROM {$table_name} dv
        WHERE dv.document_type = 'protocollo'
            AND dv.document_id IS NOT NULL
        GROUP BY dv.document_id, dv.user_profile
    ");

    // Crea una mappa veloce: [doc_id][profile] = unique_users
    $views_map = array();
    if (is_array($views_data)) {
        foreach ($views_data as $view) {
            $doc_id = intval($view->document_id);
            $profile = $view->user_profile ?: 'Non specificato';
            $unique_users = intval($view->unique_users);

            if (!isset($views_map[$doc_id])) {
                $views_map[$doc_id] = array();
            }
            $views_map[$doc_id][$profile] = $unique_users;
        }
    }

    // 4. Costruisci la griglia completa
    $grid_structure = array();

    foreach ($all_protocols as $protocol) {
        $doc_id = intval($protocol->ID);
        $doc_title = $protocol->post_title;

        $protocol_row = array(
            'document_id' => $doc_id,
            'document_title' => $doc_title,
            'profiles' => array(),
        );

        // Per ogni profilo possibile, aggiungi i dati (o 0 se non ha visualizzazioni)
        foreach ($all_profiles as $profile_name) {
            $unique_users = isset($views_map[$doc_id][$profile_name]) ? $views_map[$doc_id][$profile_name] : 0;
            $total_profile_users = $profile_totals_map[$profile_name];
            $percentage = $total_profile_users > 0 ? round(($unique_users / $total_profile_users) * 100, 1) : 0;

            $protocol_row['profiles'][$profile_name] = array(
                'profile_name' => $profile_name,
                'unique_users' => $unique_users,
                'total_users' => $total_profile_users,
                'percentage' => $percentage,
            );
        }

        $grid_structure[] = $protocol_row;
    }

    // 5. Prepara le intestazioni delle colonne
    $profile_headers = array_map(function($profile) use ($profile_totals_map) {
        return array(
            'name' => $profile,
            'total_users' => $profile_totals_map[$profile],
        );
    }, $all_profiles);

    return rest_ensure_response(array(
        'success' => true,
        'data' => array(
            'protocols' => $grid_structure,
            'profile_headers' => $profile_headers,
            'total_protocols' => count($all_protocols),
            'total_profiles' => count($all_profiles),
            'timestamp' => current_time('mysql'),
        ),
    ));
}

// Registra l'endpoint nella funzione principale
add_action('rest_api_init', function() {
    register_rest_route('piattaforma/v1', '/analytics/protocol-grid', array(
        'methods' => 'GET',
        'callback' => 'api_get_protocol_grid',
        'permission_callback' => function() {
            // Consenti sia gestore_piattaforma che admin
            return current_user_can('view_analytics') || current_user_can('manage_options');
        },
    ));
});
