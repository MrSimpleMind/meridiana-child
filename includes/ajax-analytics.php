<?php
/**
 * AJAX Handlers: Analytics
 * 
 * - Ricerca utenti
 * - Carica visualizzazioni utente
 * - Carica statistiche protocolli
 */

if (!defined('ABSPATH')) exit;

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
        $udo = get_field('udo_riferimento', 'user_' . $user->ID);
        $udo_term = $udo ? get_term($udo) : null;
        
        $results[] = array(
            'ID' => $user->ID,
            'display_name' => $user->display_name,
            'user_email' => $user->user_email,
            'udo' => $udo_term ? $udo_term->name : null,
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
                $udo = get_field('udo_riferimento', 'user_' . $user->ID);
                $udo_term = $udo ? get_term($udo) : null;
                
                $results[] = array(
                    'ID' => $user->ID,
                    'display_name' => $user->display_name,
                    'user_email' => $user->user_email,
                    'udo' => $udo_term ? $udo_term->name : null,
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
