<?php
/**
 * Analytics Module
 * 
 * - Creazione tabella custom wp_document_views
 * - Funzioni query per statistiche
 * - REST API endpoints per tracking
 */

if (!defined('ABSPATH')) exit;

/**
 * CREAZIONE TABELLA CUSTOM - Hook su theme activation
 */

function meridiana_create_analytics_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'document_views';
    $charset_collate = $wpdb->get_charset_collate();
    
    // Verifica se esiste già
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
        return; // Già esiste
    }
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT NOT NULL,
        document_id BIGINT NOT NULL,
        document_type VARCHAR(50) NOT NULL,
        view_timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        view_duration INT DEFAULT NULL COMMENT 'Secondi',
        ip_address VARCHAR(45),
        user_agent VARCHAR(255),
        INDEX user_doc_idx (user_id, document_id),
        INDEX timestamp_idx (view_timestamp),
        INDEX document_idx (document_id, document_type)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Hook su activation del tema
add_action('after_switch_theme', 'meridiana_create_analytics_table');

// Esegui anche su init se ancora non creata
add_action('wp_loaded', function() {
    static $done = false;
    if (!$done) {
        meridiana_create_analytics_table();
        $done = true;
    }
});

/**
 * FUNZIONI QUERY STATISTICHE
 */

/**
 * Conta totali per categorie
 */
function meridiana_get_stats_utenti() {
    $totals = array(
        'attivi' => 0,
        'sospesi' => 0,
        'licenziati' => 0,
    );
    
    $users = get_users(array(
        'number' => -1,
        'fields' => 'ID',
    ));
    
    foreach ($users as $user_id) {
        $stato = get_field('stato_utente', 'user_' . $user_id);
        
        if ($stato === 'attivo') {
            $totals['attivi']++;
        } elseif ($stato === 'sospeso') {
            $totals['sospesi']++;
        } elseif ($stato === 'licenziato') {
            $totals['licenziati']++;
        }
    }
    
    return $totals;
}

/**
 * Conta contenuti per CPT
 */
function meridiana_get_stats_contenuti() {
    $cpt_list = array(
        'protocollo' => 'Protocolli',
        'modulo' => 'Moduli',
        'convenzione' => 'Convenzioni',
        'salute-e-benessere-l' => 'Salute & Benessere',
        'post' => 'Comunicazioni',
    );
    
    $stats = array();
    
    foreach ($cpt_list as $cpt => $label) {
        $count = wp_count_posts($cpt);
        $stats[$cpt] = array(
            'label' => $label,
            'count' => isset($count->publish) ? $count->publish : 0,
        );
    }
    
    return $stats;
}

/**
 * Conta protocolli ATS
 */
function meridiana_get_stats_protocolli_ats() {
    $args = array(
        'post_type' => 'protocollo',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'fields' => 'ids',
    );
    
    $protocolli = get_posts($args);
    $ats_count = 0;
    
    foreach ($protocolli as $post_id) {
        $is_ats = get_field('pianificazione_ats', $post_id);
        if ($is_ats) {
            $ats_count++;
        }
    }
    
    return $ats_count;
}

/**
 * Visualizzazioni totali per documento
 */
function meridiana_get_document_views($document_id, $args = array()) {
    global $wpdb;
    
    $defaults = array(
        'unique' => false,
        'date_from' => null,
        'date_to' => null,
    );
    
    $args = wp_parse_args($args, $defaults);
    $table = $wpdb->prefix . 'document_views';
    
    if ($args['unique']) {
        $sql = "SELECT COUNT(DISTINCT user_id) as count 
                FROM $table 
                WHERE document_id = %d";
    } else {
        $sql = "SELECT COUNT(*) as count 
                FROM $table 
                WHERE document_id = %d";
    }
    
    if ($args['date_from']) {
        $sql .= $wpdb->prepare(" AND view_timestamp >= %s", $args['date_from']);
    }
    
    if ($args['date_to']) {
        $sql .= $wpdb->prepare(" AND view_timestamp <= %s", $args['date_to']);
    }
    
    $result = $wpdb->get_var($wpdb->prepare($sql, $document_id));
    return intval($result);
}

/**
 * Utenti che hanno visualizzato un documento
 */
function meridiana_get_users_who_viewed($document_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'document_views';
    
    $sql = "SELECT DISTINCT 
                dv.user_id,
                u.display_name,
                u.user_email,
                MAX(dv.view_timestamp) as last_view,
                COUNT(*) as view_count
            FROM $table dv
            LEFT JOIN {$wpdb->users} u ON dv.user_id = u.ID
            WHERE dv.document_id = %d
            GROUP BY dv.user_id
            ORDER BY last_view DESC";
    
    return $wpdb->get_results($wpdb->prepare($sql, $document_id));
}

/**
 * Utenti che NON hanno visualizzato un documento (solo attivi)
 */
function meridiana_get_users_who_not_viewed($document_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'document_views';
    
    // Get all active users
    $active_users = get_users(array(
        'number' => -1,
        'fields' => 'ID',
        'meta_query' => array(
            array(
                'key' => 'stato_utente',
                'value' => 'attivo',
                'compare' => '=',
            ),
        ),
    ));
    
    // Get users who viewed
    $viewed_users = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT user_id FROM $table WHERE document_id = %d",
        $document_id
    ));
    
    // Difference
    $not_viewed = array_diff($active_users, $viewed_users);
    
    if (empty($not_viewed)) {
        return array();
    }
    
    // Get user details
    $placeholders = implode(',', array_map('intval', $not_viewed));
    $sql = "SELECT ID, display_name, user_email FROM {$wpdb->users} WHERE ID IN ($placeholders)";
    
    return $wpdb->get_results($sql);
}

/**
 * Visualizzazioni per documento (per grafico)
 */
function meridiana_get_views_per_document_type() {
    global $wpdb;
    $table = $wpdb->prefix . 'document_views';
    
    $sql = "SELECT 
                document_type,
                COUNT(*) as view_count,
                COUNT(DISTINCT user_id) as unique_users
            FROM $table
            GROUP BY document_type
            ORDER BY view_count DESC";
    
    return $wpdb->get_results($sql);
}

/**
 * Cache wrapper per query pesanti
 */
function meridiana_get_cached_stat($cache_key, $callback, $expiration = 1 * HOUR_IN_SECONDS) {
    $transient_key = 'meridiana_stat_' . $cache_key;
    $cached = get_transient($transient_key);
    
    if ($cached !== false) {
        return $cached;
    }
    
    $data = call_user_func($callback);
    set_transient($transient_key, $data, $expiration);
    
    return $data;
}

/**
 * Clear cache utility
 */
function meridiana_clear_analytics_cache() {
    delete_transient('meridiana_stat_utenti');
    delete_transient('meridiana_stat_contenuti');
    delete_transient('meridiana_stat_protocolli_ats');
}

// Clear cache quando nuovo post/user
add_action('save_post_protocollo', 'meridiana_clear_analytics_cache');
add_action('save_post_modulo', 'meridiana_clear_analytics_cache');
add_action('save_post_convenzione', 'meridiana_clear_analytics_cache');
add_action('save_post_salute-e-benessere-l', 'meridiana_clear_analytics_cache');
add_action('save_post', 'meridiana_clear_analytics_cache');
add_action('user_register', 'meridiana_clear_analytics_cache');
