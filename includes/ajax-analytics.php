<?php
/**
 * AJAX Handlers: Analytics
 * 
 * - Ricerca utenti
 * - Carica visualizzazioni utente
 * - Carica statistiche protocolli
 */

if (!defined('ABSPATH')) exit;

function meridiana_user_can_view_analytics() {
    return current_user_can('gestore_piattaforma') || current_user_can('manage_options');
}

/**
 * AJAX: Ricerca Utenti
 * Action: meridiana_analytics_search_users
 */
function meridiana_ajax_analytics_search_users() {
    // Verifica nonce e capabilities
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_rest')) {
        wp_send_json_error('Nonce verification failed');
    }
    
    if (!current_user_can('list_users')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    // Sanitizza query
    $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
    
    if (strlen($query) < 2) {
        wp_send_json_success(array());
    }
    
    // Query utenti - ricerca per display_name e email
    $users = get_users(array(
        'search' => '*' . esc_attr($query) . '*',
        'search_columns' => array('display_name', 'user_email'),
        'number' => 15, // Max 15 risultati
        'fields' => array('ID', 'display_name', 'user_email'),
    ));
    
    // Enrichisci con metadata
    $results = array();
    foreach ($users as $user) {
        // Recupera il valore UDO (è una stringa key, non un term ID)
        $udo_label = get_field('field_udo_riferimento_user', 'user_' . $user->ID);
        
        // Se la funzione ACF non è disponibile o il campo è vuoto, fallback a get_user_meta
        if (!$udo_label) {
            $udo_label = get_user_meta($user->ID, 'udo_riferimento', true);
        }
        
        // Se il valore è ancora una chiave (es. 'ambulatori'), mappalo alla label
        if ($udo_label && !in_array($udo_label, array('Ambulatori', 'AP', 'CDI', 'Cure Domiciliari', 'Hospice', 'Paese', 'R20', 'RSA', 'RSA Aperta', 'RSD'))) {
            $default_udo_choices = array(
                'ambulatori' => 'Ambulatori',
                'ap' => 'AP',
                'cdi' => 'CDI',
                'cure_domiciliari' => 'Cure Domiciliari',
                'hospice' => 'Hospice',
                'paese' => 'Paese',
                'r20' => 'R20',
                'rsa' => 'RSA',
                'rsa_aperta' => 'RSA Aperta',
                'rsd' => 'RSD',
            );
            $udo_label = isset($default_udo_choices[$udo_label]) ? $default_udo_choices[$udo_label] : $udo_label;
        }
        
        $results[] = array(
            'ID' => $user->ID,
            'display_name' => $user->display_name,
            'user_email' => $user->user_email,
            'udo' => $udo_label,
        );
    }
    
    wp_send_json_success($results);
}
add_action('wp_ajax_meridiana_analytics_search_users', 'meridiana_ajax_analytics_search_users');

/**
 * AJAX: Carica documenti visualizzati da utente
 * Action: meridiana_analytics_user_viewed_documents
 * 
 * GET /wp-admin/admin-ajax.php?action=meridiana_analytics_user_viewed_documents&user_id=123
 */
function meridiana_ajax_analytics_user_viewed_documents() {
    // Verifica permissions
    if (!current_user_can('view_analytics')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    
    if (!$user_id) {
        wp_send_json_error('Invalid user ID');
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'document_views';
    
    // Query documenti visualizzati
    $viewed = $wpdb->get_results($wpdb->prepare(
        "SELECT DISTINCT 
            dv.document_id,
            p.post_title,
            p.post_type,
            MAX(dv.view_timestamp) as last_view,
            COUNT(*) as view_count
        FROM $table dv
        LEFT JOIN {$wpdb->posts} p ON dv.document_id = p.ID
        WHERE dv.user_id = %d
        GROUP BY dv.document_id
        ORDER BY last_view DESC",
        $user_id
    ));
    
    wp_send_json_success(array(
        'viewed' => $viewed,
    ));
}
add_action('wp_ajax_meridiana_analytics_user_viewed_documents', 'meridiana_ajax_analytics_user_viewed_documents');

/**
 * REST API: Ricerca Utenti (alternativa)
 * GET /wp-json/piattaforma/v1/search-users?q=maria
 */
function register_analytics_rest_routes() {
    register_rest_route('piattaforma/v1', '/search-users', array(
        'methods' => 'GET',
        'callback' => function($request) {
            $query = $request->get_param('q');
            
            if (!$query || strlen($query) < 2) {
                return rest_ensure_response(array());
            }
            
            $users = get_users(array(
                'search' => '*' . esc_attr($query) . '*',
                'search_columns' => array('display_name', 'user_email'),
                'number' => 15,
                'fields' => array('ID', 'display_name', 'user_email'),
            ));
            
            $results = array();
            foreach ($users as $user) {
                            // Recupera il valore UDO (è una stringa key, non un term ID)
                            $udo_label = get_field('field_udo_riferimento_user', 'user_' . $user->ID);
                            
                            // Se la funzione ACF non è disponibile o il campo è vuoto, fallback a get_user_meta
                            if (!$udo_label) {
                                $udo_label = get_user_meta($user->ID, 'udo_riferimento', true);
                            }
                            
                            // Se il valore è ancora una chiave (es. 'ambulatori'), mappalo alla label
                            if ($udo_label && !in_array($udo_label, array('Ambulatori', 'AP', 'CDI', 'Cure Domiciliari', 'Hospice', 'Paese', 'R20', 'RSA', 'RSA Aperta', 'RSD'))) {
                                $default_udo_choices = array(
                                    'ambulatori' => 'Ambulatori',
                                    'ap' => 'AP',
                                    'cdi' => 'CDI',
                                    'cure_domiciliari' => 'Cure Domiciliari',
                                    'hospice' => 'Hospice',
                                    'paese' => 'Paese',
                                    'r20' => 'R20',
                                    'rsa' => 'RSA',
                                    'rsa_aperta' => 'RSA Aperta',
                                    'rsd' => 'RSD',
                                );
                                $udo_label = isset($default_udo_choices[$udo_label]) ? $default_udo_choices[$udo_label] : $udo_label;
                            }                
                $results[] = array(
                    'ID' => $user->ID,
                    'display_name' => $user->display_name,
                    'user_email' => $user->user_email,
                    'udo' => $udo_label,
                );
            }
            
            return rest_ensure_response($results);
        },
        'permission_callback' => function() {
            return current_user_can('list_users');
        },
    ));
}
add_action('rest_api_init', 'register_analytics_rest_routes');

/**
 * AJAX: Get Global Statistics
 * Action: meridiana_analytics_get_global_stats
 */
function meridiana_ajax_get_global_stats() {
    check_ajax_referer('wp_rest', 'nonce');

    if (!meridiana_user_can_view_analytics()) {
        wp_send_json_error(array('message' => 'Permessi insufficienti.'));
    }

    $stats = array(
        'total_users' => count_users()['total_users'],
        'total_protocols' => wp_count_posts('protocollo')->publish,
        'total_modules' => wp_count_posts('modulo')->publish,
        'total_convenzioni' => wp_count_posts('convenzione')->publish,
        'total_salute_benessere' => wp_count_posts('salute-e-benessere-l')->publish,
        'total_comunicazioni' => wp_count_posts('post')->publish,
        'total_ats_protocols' => meridiana_get_stats_protocolli_ats(),
    );

    wp_send_json_success($stats);
}
add_action('wp_ajax_meridiana_analytics_get_global_stats', 'meridiana_ajax_get_global_stats');

/**
 * AJAX: Get Users Breakdown by Professional Profile
 * Action: meridiana_analytics_get_users_by_profile
 *
 * Restituisce:
 * - Conteggio utenti per profilo professionale (con label)
 * - Conteggio totale per stato (attivo, sospeso, licenziato)
 */
function meridiana_ajax_get_users_by_profile() {
    check_ajax_referer('wp_rest', 'nonce');

    if (!meridiana_user_can_view_analytics()) {
        wp_send_json_error(array('message' => 'Permessi insufficienti.'));
    }

    global $wpdb;

    // Mappa delle label dei profili (da ACF)
    $profile_labels = array(
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
        'volontari' => 'Volontari',
    );

    // Query 1: Conta utenti per profilo professionale
    $users_by_profile = $wpdb->get_results("
        SELECT
            um.meta_value as profile_key,
            COUNT(DISTINCT um.user_id) as user_count
        FROM {$wpdb->usermeta} um
        WHERE um.meta_key = 'profilo_professionale'
            AND um.meta_value IS NOT NULL
            AND um.meta_value != ''
        GROUP BY um.meta_value
        ORDER BY user_count DESC
    ");

    if (!is_array($users_by_profile)) {
        $users_by_profile = array();
    }

    // Formatta i dati per il chart (con label)
    $profiles_breakdown = array();
    foreach ($users_by_profile as $item) {
        $label = isset($profile_labels[$item->profile_key]) ? $profile_labels[$item->profile_key] : $item->profile_key;
        $profiles_breakdown[] = array(
            'key' => $item->profile_key,
            'label' => $label,
            'count' => intval($item->user_count),
        );
    }

    // Query 2: Conta utenti per stato
    $users_by_status = $wpdb->get_results("
        SELECT
            COALESCE(um.meta_value, 'attivo') as status,
            COUNT(DISTINCT um.user_id) as user_count
        FROM {$wpdb->users} u
        LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id AND um.meta_key = 'stato_utente'
        WHERE u.user_login NOT IN ('admin')
        GROUP BY COALESCE(um.meta_value, 'attivo')
    ");

    $status_breakdown = array(
        'attivo' => 0,
        'sospeso' => 0,
        'licenziato' => 0,
    );

    if (is_array($users_by_status)) {
        foreach ($users_by_status as $item) {
            if (isset($status_breakdown[$item->status])) {
                $status_breakdown[$item->status] = intval($item->user_count);
            }
        }
    }

    wp_send_json_success(array(
        'profiles' => $profiles_breakdown,
        'status' => $status_breakdown,
    ));
}
add_action('wp_ajax_meridiana_analytics_get_users_by_profile', 'meridiana_ajax_get_users_by_profile');

/**
 * AJAX: Track Document View
 * Action: meridiana_track_document_view
 */
function meridiana_ajax_track_document_view() {
    // Verify nonce and user login
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_rest')) {
        wp_send_json_error('Nonce verification failed');
    }

    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }

    $user_id = get_current_user_id();
    $document_id = isset($_POST['document_id']) ? intval($_POST['document_id']) : 0;
    $document_type = isset($_POST['document_type']) ? sanitize_text_field(wp_unslash($_POST['document_type'])) : 'unknown';
    $view_duration = isset($_POST['duration']) ? intval($_POST['duration']) : 0;

    if (!$document_id) {
        wp_send_json_error('Invalid document ID');
    }

    // Recupera il profilo professionale dell'utente AL MOMENTO DELLA VISUALIZZAZIONE
    $user_profile = get_user_meta($user_id, 'profilo_professionale', true) ?: null;
    // Recupera l'UDO dell'utente AL MOMENTO DELLA VISUALIZZAZIONE
    $user_udo = get_user_meta($user_id, 'udo_riferimento', true) ?: null;

    // Recupera il timestamp di ultima modifica del documento (document_version)
    $document_post = get_post($document_id);
    $document_version = ($document_post && $document_post->post_modified_gmt !== '0000-00-00 00:00:00') ? $document_post->post_modified_gmt : current_time('mysql', true);

    global $wpdb;
    $table_name = $wpdb->prefix . 'document_views';

    // Verifica se esiste già una visualizzazione unica per questo utente, documento e versione
    $existing_view = $wpdb->get_row($wpdb->prepare(
        "SELECT id FROM $table_name WHERE user_id = %d AND document_id = %d AND document_version = %s",
        $user_id,
        $document_id,
        $document_version
    ));

    if ($existing_view) {
        // Se la visualizzazione unica esiste già, non fare nulla
        wp_send_json_success('Visualizzazione unica già registrata.');
    }

    // Inserisci nuova visualizzazione
    $inserted = $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'document_id' => $document_id,
            'document_type' => $document_type,
            'user_profile' => $user_profile,
            'user_udo' => $user_udo,
            'document_version' => $document_version,
            'view_timestamp' => current_time('mysql'),
            'view_duration' => $view_duration,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        ),
        array('%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s')
    );

    if ($inserted) {
        wp_send_json_success('Document view tracked.');
    } else {
        wp_send_json_error('Failed to track document view.');
    }
}
add_action('wp_ajax_meridiana_track_document_view', 'meridiana_ajax_track_document_view');


/**
 * AJAX: Get Content Distribution
 * Action: meridiana_analytics_get_content_distribution
 */
function meridiana_ajax_get_content_distribution() {
    check_ajax_referer('wp_rest', 'nonce');

    if (!meridiana_user_can_view_analytics()) {
        wp_send_json_error(array('message' => 'Permessi insufficienti.'));
    }

    $distribution = meridiana_get_views_per_document_type();

    wp_send_json_success($distribution);
}
add_action('wp_ajax_meridiana_analytics_get_content_distribution', 'meridiana_ajax_get_content_distribution');

/**
 * AJAX: Documenti visualizzati da un utente
 */
function meridiana_ajax_analytics_get_user_views() {
    check_ajax_referer('wp_rest', 'nonce');

    if (!meridiana_user_can_view_analytics()) {
        wp_send_json_error(array('message' => 'Permessi insufficienti.'));
    }

    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

    if (!$user_id) {
        wp_send_json_error(array('message' => 'Utente non valido.'));
    }

    $views = meridiana_get_user_viewed_documents($user_id);

    wp_send_json_success(array('views' => $views));
}
add_action('wp_ajax_meridiana_analytics_get_user_views', 'meridiana_ajax_analytics_get_user_views');

/**
 * AJAX: Ricerca documenti monitorati
 */
function meridiana_ajax_analytics_search_documents() {
    check_ajax_referer('wp_rest', 'nonce');

    if (!meridiana_user_can_view_analytics()) {
        wp_send_json_error(array('message' => 'Permessi insufficienti.'));
    }

    $query = isset($_POST['query']) ? sanitize_text_field(wp_unslash($_POST['query'])) : '';
    $post_type = isset($_POST['post_type']) ? sanitize_key(wp_unslash($_POST['post_type'])) : 'all';

    if (strlen($query) < 2) {
        wp_send_json_success(array());
    }

    $allowed_types = array('protocollo', 'modulo');
    $types = ($post_type !== 'all' && in_array($post_type, $allowed_types, true)) ? array($post_type) : $allowed_types;

    $documents = meridiana_search_documents($query, array(
        'limit' => 10,
        'post_type' => $types,
    ));

    wp_send_json_success($documents);
}
add_action('wp_ajax_meridiana_analytics_search_documents', 'meridiana_ajax_analytics_search_documents');

/**
 * AJAX: Insight documento (chi ha visto / non ha visto)
 */
function meridiana_ajax_analytics_get_document_insights() {
    check_ajax_referer('wp_rest', 'nonce');

    if (!meridiana_user_can_view_analytics()) {
        wp_send_json_error(array('message' => 'Permessi insufficienti.'));
    }

    $document_id = isset($_POST['document_id']) ? intval($_POST['document_id']) : 0;
    $post = $document_id ? get_post($document_id) : null;

    if (!$post || !in_array($post->post_type, array('protocollo', 'modulo'), true)) {
        wp_send_json_error(array('message' => 'Documento non valido.'));
    }

    $details = meridiana_get_document_view_details($document_id);

    wp_send_json_success(array(
        'document' => array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'type' => $post->post_type,
        ),
        'viewers' => $details['viewers'],
        'non_viewers' => $details['non_viewers'],
        'non_viewers_count' => $details['non_viewers_count'],
    ));
}
add_action('wp_ajax_meridiana_analytics_get_document_insights', 'meridiana_ajax_analytics_get_document_insights');

/**
 * AJAX: Visualizzazioni per profilo professionale (Protocolli)
 * Action: meridiana_analytics_get_views_by_profile_protocols
 */
function meridiana_ajax_analytics_get_views_by_profile_protocols() {
    try {
        check_ajax_referer('wp_rest', 'nonce');

        if (!meridiana_user_can_view_analytics()) {
            wp_send_json_error(array('message' => 'Permessi insufficienti.'));
        }

        $data = meridiana_get_views_by_professional_profile('protocollo');

        wp_send_json_success($data);
    } catch (Exception $e) {
        error_log('Errore meridiana_ajax_analytics_get_views_by_profile_protocols: ' . $e->getMessage());
        wp_send_json_error(array('message' => $e->getMessage()));
    }
}
add_action('wp_ajax_meridiana_analytics_get_views_by_profile_protocols', 'meridiana_ajax_analytics_get_views_by_profile_protocols');

/**
 * AJAX: Visualizzazioni per profilo professionale (Moduli)
 * Action: meridiana_analytics_get_views_by_profile_modules
 */
function meridiana_ajax_analytics_get_views_by_profile_modules() {
    try {
        check_ajax_referer('wp_rest', 'nonce');

        if (!meridiana_user_can_view_analytics()) {
            wp_send_json_error(array('message' => 'Permessi insufficienti.'));
        }

        $data = meridiana_get_views_by_professional_profile('modulo');

        wp_send_json_success($data);
    } catch (Exception $e) {
        error_log('Errore meridiana_ajax_analytics_get_views_by_profile_modules: ' . $e->getMessage());
        wp_send_json_error(array('message' => $e->getMessage()));
    }
}
add_action('wp_ajax_meridiana_analytics_get_views_by_profile_modules', 'meridiana_ajax_analytics_get_views_by_profile_modules');

/**
 * AJAX: Get all professional profiles from ACF field choices
 * Action: meridiana_analytics_get_all_professional_profiles
 */
function meridiana_ajax_analytics_get_all_professional_profiles() {
    try {
        check_ajax_referer('wp_rest', 'nonce');

        if (!meridiana_user_can_view_analytics()) {
            wp_send_json_error(array('message' => 'Permessi insufficienti.'));
        }

        // Profili professionali disponibili nel sistema
        // Corrispondono ai label del campo ACF 'profilo_professionale'
        $profiles = array(
            'Addetto Manutenzione',
            'ASA/OSS',
            'Assistente Sociale',
            'Coordinatore Unità di Offerta',
            'Educatore',
            'FKT',
            'Impiegato Amministrativo',
            'Infermiere',
            'Logopedista',
            'Medico',
            'Psicologa',
            'Receptionista',
            'Terapista Occupazionale',
            'Volontari'
        );

        $udos = array(
            'Ambulatori',
            'AP',
            'CDI',
            'Cure Domiciliari',
            'Hospice',
            'Paese',
            'R20',
            'RSA',
            'RSA Aperta',
            'RSD'
        );

        sort($profiles);
        sort($udos);

        wp_send_json_success(array(
            'profiles' => $profiles,
            'udos' => $udos,
        ));
    } catch (Exception $e) {
        error_log('Errore meridiana_ajax_analytics_get_all_professional_profiles: ' . $e->getMessage());
        wp_send_json_error(array('message' => $e->getMessage()));
    }
}
add_action('wp_ajax_meridiana_analytics_get_all_professional_profiles', 'meridiana_ajax_analytics_get_all_professional_profiles');
