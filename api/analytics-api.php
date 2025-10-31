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

    error_log('[api_get_protocol_grid] START - Caricamento matrice protocolli × profili');

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

    // 2. Definisci TUTTI i profili professionali disponibili (da ACF field choices)
    // Usa le KEY come sono salvate nel DB, con mapping ai LABEL per visualizzazione
    $all_profiles_keys = array(
        'addetto_manutenzione',
        'asa_oss',
        'assistente_sociale',
        'coordinatore',
        'educatore',
        'fkt',
        'impiegato_amministrativo',
        'infermiere',
        'logopedista',
        'medico',
        'psicologa',
        'receptionista',
        'terapista_occupazionale',
        'volontari'
    );

    // Mapping da KEY a LABEL per visualizzazione
    $profile_key_to_label = array(
        'addetto_manutenzione' => 'Addetto Manutenzione',
        'asa_oss' => 'ASA/OSS',
        'assistente_sociale' => 'Assistente Sociale',
        'coordinatore' => 'Coordinatore Unità di Offerta',
        'educatore' => 'Educatore',
        'fkt' => 'FKT',
        'impiegato_amministrativo' => 'Impiegato Amministrativo',
        'infermiere' => 'Infermiere',
        'logopedista' => 'Logopedista',
        'medico' => 'Medico',
        'psicologa' => 'Psicologa',
        'receptionista' => 'Receptionista',
        'terapista_occupazionale' => 'Terapista Occupazionale',
        'volontari' => 'Volontari'
    );

    $all_profiles = $all_profiles_keys;

    // Query: Conta quanti utenti hanno assegnato ogni profilo (anche 0)
    $profile_counts = $wpdb->get_results("
        SELECT
            meta_value as profile_name,
            COUNT(DISTINCT user_id) as total_users
        FROM {$wpdb->usermeta}
        WHERE meta_key = 'profilo_professionale'
            AND meta_value IS NOT NULL
            AND meta_value != ''
        GROUP BY meta_value
    ");

    // Crea una mappa veloce dei conteggi effettivi
    $profile_counts_map = array();
    if (is_array($profile_counts)) {
        foreach ($profile_counts as $profile) {
            $profile_counts_map[$profile->profile_name] = intval($profile->total_users);
        }
    }

    // Costruisci mappa completa con TUTTI i 14 profili (anche con 0 utenti)
    $profile_totals_map = array();
    foreach ($all_profiles as $profile_name) {
        $profile_totals_map[$profile_name] = isset($profile_counts_map[$profile_name]) ? $profile_counts_map[$profile_name] : 0;
    }

    // 3. Query: Prendi gli utenti che hanno visto ogni protocollo
    // Ogni utente una sola volta per documento (indipendentemente da quante volte ha visto)
    // NOTA: Non filtriamo per versione attuale perché potrebbe escludere visualizzazioni
    // se il documento è stato modificato dopo la visualizzazione
    $views_users = $wpdb->get_results("
        SELECT DISTINCT
            dv.document_id,
            dv.user_id
        FROM {$table_name} dv
        INNER JOIN {$wpdb->posts} p ON dv.document_id = p.ID
        WHERE dv.document_type = 'protocollo'
            AND dv.document_id IS NOT NULL
    ");

    error_log('[api_get_protocol_grid] Views found: ' . ($views_users ? count($views_users) : 0));

    // Aggreghiamo per profilo ATTUALE di ogni utente
    $views_data = array();
    $profile_users_by_doc = array(); // [doc_id][profile] = [user_ids]

    if (!empty($views_users)) {
        foreach ($views_users as $view) {
            $doc_id = intval($view->document_id);
            $user_id = intval($view->user_id);

            // Recupera profilo ATTUALE dell'utente
            $profile_key = get_user_meta($user_id, 'profilo_professionale', true) ?: 'Non specificato';

            if (!isset($profile_users_by_doc[$doc_id])) {
                $profile_users_by_doc[$doc_id] = array();
            }
            if (!isset($profile_users_by_doc[$doc_id][$profile_key])) {
                $profile_users_by_doc[$doc_id][$profile_key] = array();
            }

            // Aggiungi utente se non già presente (evita duplicati se ha visto più volte)
            if (!in_array($user_id, $profile_users_by_doc[$doc_id][$profile_key])) {
                $profile_users_by_doc[$doc_id][$profile_key][] = $user_id;
            }
        }

        // Converti in formato per la query
        foreach ($profile_users_by_doc as $doc_id => $profiles) {
            foreach ($profiles as $profile_key => $users) {
                $views_data[] = (object) array(
                    'document_id' => $doc_id,
                    'user_profile' => $profile_key,
                    'unique_users' => count($users)
                );
            }
        }
    }

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
        foreach ($all_profiles as $profile_key) {
            $unique_users = isset($views_map[$doc_id][$profile_key]) ? $views_map[$doc_id][$profile_key] : 0;
            $total_profile_users = $profile_totals_map[$profile_key];
            $percentage = $total_profile_users > 0 ? round(($unique_users / $total_profile_users) * 100, 1) : 0;

            // Converti la KEY al LABEL per visualizzazione
            $profile_label = isset($profile_key_to_label[$profile_key]) ? $profile_key_to_label[$profile_key] : $profile_key;

            $protocol_row['profiles'][$profile_label] = array(
                'profile_name' => $profile_label,
                'unique_users' => $unique_users,
                'total_users' => $total_profile_users,
                'percentage' => $percentage,
            );
        }

        $grid_structure[] = $protocol_row;
    }

    // 5. Prepara le intestazioni delle colonne (converti da KEY a LABEL)
    $profile_headers = array_map(function($profile_key) use ($profile_totals_map, $profile_key_to_label) {
        $profile_label = isset($profile_key_to_label[$profile_key]) ? $profile_key_to_label[$profile_key] : $profile_key;
        return array(
            'name' => $profile_label,
            'total_users' => $profile_totals_map[$profile_key],
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
