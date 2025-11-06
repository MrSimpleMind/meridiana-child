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

    // Verifica se esiste già usando INFORMATION_SCHEMA (più sicuro)
    $table_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s",
        $table_name
    ));

    if ($table_exists) {
        return; // Già esiste
    }
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT NOT NULL,
        document_id BIGINT NOT NULL,
        document_type VARCHAR(50) NOT NULL,
        user_profile VARCHAR(100) DEFAULT NULL COMMENT 'Profilo professionale al momento della visualizzazione',
        user_udo VARCHAR(100) DEFAULT NULL COMMENT 'Unità di Offerta al momento della visualizzazione',
        document_version DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Timestamp di ultima modifica del documento al momento della visualizzazione',
        view_timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        view_duration INT DEFAULT NULL COMMENT 'Secondi',
        ip_address VARCHAR(45),
        user_agent VARCHAR(255),
        UNIQUE KEY unique_view_idx (user_id, document_id, document_version),
        INDEX timestamp_idx (view_timestamp),
        INDEX document_idx (document_id, document_type),
        INDEX profile_idx (user_profile),
        INDEX udo_idx (user_udo)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Aggiungi la colonna user_profile se non esiste (per compatibilità con versioni precedenti)
    $column_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s AND COLUMN_NAME = %s",
        $table_name,
        'user_profile'
    ));
    if (!$column_exists) {
        $wpdb->query($wpdb->prepare(
            "ALTER TABLE %i ADD COLUMN user_profile VARCHAR(100) DEFAULT NULL AFTER document_type",
            $table_name
        ));
        $wpdb->query($wpdb->prepare(
            "ALTER TABLE %i ADD INDEX profile_idx (user_profile)",
            $table_name
        ));
    }

    // Aggiungi la colonna user_udo se non esiste
    $column_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s AND COLUMN_NAME = %s",
        $table_name,
        'user_udo'
    ));
    if (!$column_exists) {
        $wpdb->query($wpdb->prepare(
            "ALTER TABLE %i ADD COLUMN user_udo VARCHAR(100) DEFAULT NULL AFTER user_profile",
            $table_name
        ));
        $wpdb->query($wpdb->prepare(
            "ALTER TABLE %i ADD INDEX udo_idx (user_udo)",
            $table_name
        ));
    }

    // Aggiungi la colonna document_version se non esiste
    $column_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s AND COLUMN_NAME = %s",
        $table_name,
        'document_version'
    ));
    if (!$column_exists) {
        $wpdb->query($wpdb->prepare(
            "ALTER TABLE %i ADD COLUMN document_version DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER document_type",
            $table_name
        ));
    }

    // Rimuovi il vecchio indice user_doc_idx se esiste
    $index_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s AND INDEX_NAME = %s",
        $table_name,
        'user_doc_idx'
    ));
    if ($index_exists) {
        $wpdb->query($wpdb->prepare(
            "ALTER TABLE %i DROP INDEX user_doc_idx",
            $table_name
        ));
    }

    // Aggiungi l'indice UNIQUE unique_view_idx se non esiste
    $index_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s AND INDEX_NAME = %s",
        $table_name,
        'unique_view_idx'
    ));
    if (!$index_exists) {
        $wpdb->query($wpdb->prepare(
            "ALTER TABLE %i ADD UNIQUE KEY unique_view_idx (user_id, document_id, document_version)",
            $table_name
        ));
    }
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

    // Ottimizzazione: Fetch all user meta in una singola query per evitare N+1
    global $wpdb;
    $user_ids = array_map('intval', $users);

    if (!empty($user_ids)) {
        $user_ids_str = implode(',', $user_ids);
        $user_stati = $wpdb->get_results($wpdb->prepare(
            "SELECT user_id, meta_value
             FROM {$wpdb->usermeta}
             WHERE meta_key = %s
               AND user_id IN ($user_ids_str)",
            'stato_utente'
        ), OBJECT_K);

        foreach ($users as $user_id) {
            $stato = isset($user_stati[$user_id]) ? $user_stati[$user_id]->meta_value : '';

            if ($stato === 'attivo') {
                $totals['attivi']++;
            } elseif ($stato === 'sospeso') {
                $totals['sospesi']++;
            } elseif ($stato === 'licenziato') {
                $totals['licenziati']++;
            }
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

    // Ottimizzazione: Fetch all post meta in una singola query per evitare N+1
    if (!empty($protocolli)) {
        global $wpdb;
        $post_ids = array_map('intval', $protocolli);
        $post_ids_str = implode(',', $post_ids);

        $ats_values = $wpdb->get_results($wpdb->prepare(
            "SELECT post_id, meta_value
             FROM {$wpdb->postmeta}
             WHERE meta_key = %s
               AND post_id IN ($post_ids_str)",
            'pianificazione_ats'
        ), OBJECT_K);

        foreach ($protocolli as $post_id) {
            $is_ats = isset($ats_values[$post_id]) ? $ats_values[$post_id]->meta_value : false;
            if ($is_ats) {
                $ats_count++;
            }
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
    
    // Build query with proper parameter binding
    $where_conditions = array('document_id = %d');
    $where_values = array($document_id);

    if ($args['date_from']) {
        $where_conditions[] = 'view_timestamp >= %s';
        $where_values[] = $args['date_from'];
    }

    if ($args['date_to']) {
        $where_conditions[] = 'view_timestamp <= %s';
        $where_values[] = $args['date_to'];
    }

    $where_clause = implode(' AND ', $where_conditions);

    if ($args['unique']) {
        $sql = "SELECT COUNT(DISTINCT user_id, document_version) as count
                FROM {$wpdb->prefix}document_views
                WHERE {$where_clause}";
    } else {
        $sql = "SELECT COUNT(*) as count
                FROM {$wpdb->prefix}document_views
                WHERE {$where_clause}";
    }

    $result = $wpdb->get_var($wpdb->prepare($sql, ...$where_values));
    return intval($result);
}

/**
 * Utenti che hanno visualizzato un documento (visualizzazioni uniche per versione)
 */
function meridiana_get_unique_document_views_by_user($document_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'document_views';

    $sql = "SELECT
                dv.user_id,
                u.display_name,
                u.user_email,
                dv.user_profile,
                dv.document_version,
                MAX(dv.view_timestamp) as last_view,
                COUNT(*) as view_count
            FROM $table dv
            LEFT JOIN {$wpdb->users} u ON dv.user_id = u.ID
            WHERE dv.document_id = %d
            GROUP BY dv.user_id, dv.document_version
            ORDER BY last_view DESC";

    return $wpdb->get_results($wpdb->prepare($sql, $document_id));
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
    
    // Get user details with user_profile - use proper prepare with placeholders
    $placeholders = implode(',', array_fill(0, count($not_viewed), '%d'));
    $sql = "SELECT u.ID, u.display_name, u.user_email, um.meta_value as user_profile
            FROM {$wpdb->users} u
            LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id AND um.meta_key = 'profilo_professionale'
            WHERE u.ID IN ($placeholders)";

    return $wpdb->get_results($wpdb->prepare($sql, ...$not_viewed));
}

/**
 * Visualizzazioni per documento (per grafico)
 */
function meridiana_get_views_per_document_type() {
    global $wpdb;

    $sql = "SELECT
                document_type,
                COUNT(*) as view_count,
                COUNT(DISTINCT user_id, document_version) as unique_users
            FROM {$wpdb->prefix}document_views
            GROUP BY document_type
            ORDER BY view_count DESC";

    return $wpdb->get_results($sql);
}

/**
 * Visualizzazioni per profilo professionale (Protocolli/Moduli)
 */
function meridiana_get_views_by_professional_profile($document_type) {
    global $wpdb;

    if (!in_array($document_type, array('protocollo', 'modulo'), true)) {
        return array();
    }

    $table_views = $wpdb->prefix . 'document_views';

    // Priorità: profilo salvato > profilo attuale dell'utente > 'Non specificato'
    // COALESCE legge prima user_profile (salvato), poi fallback a usermeta (attuale), poi default
    $sql = "SELECT
                COALESCE(dv.user_profile, um.meta_value, 'Non specificato') as profilo_professionale,
                COALESCE(dv.user_udo, um_udo.meta_value, 'Non specificato') as user_udo,
                COUNT(DISTINCT dv.user_id) as unique_users,
                COUNT(DISTINCT dv.document_id) as unique_documents,
                dv.document_version
            FROM $table_views dv
            LEFT JOIN {$wpdb->users} u ON dv.user_id = u.ID
            LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id AND um.meta_key = 'profilo_professionale'
            LEFT JOIN {$wpdb->usermeta} um_udo ON u.ID = um_udo.user_id AND um_udo.meta_key = 'udo_riferimento'
            WHERE dv.document_type = %s
            GROUP BY dv.document_id, dv.document_version, profilo_professionale, user_udo
            ORDER BY unique_users DESC";

    $results = $wpdb->get_results($wpdb->prepare($sql, $document_type));

    if (!is_array($results)) {
        return array();
    }

    return $results;
}

/**
 * Documenti visualizzati da un utente specifico
 */
function meridiana_get_user_viewed_documents($user_id, $args = array()) {
    global $wpdb;

    $defaults = array(
        'limit' => 50,
        'post_types' => array('protocollo', 'modulo'),
    );

    $args = wp_parse_args($args, $defaults);

    if (!$user_id) {
        return array();
    }

    $post_types = array_filter((array) $args['post_types']);
    if (empty($post_types)) {
        $post_types = array('protocollo', 'modulo');
    }

    $post_types = array_map('esc_sql', $post_types);
    $types_placeholder = "'" . implode("','", $post_types) . "'";

    $limit = intval($args['limit']);
    if ($limit <= 0) {
        $limit = 50;
    }

    $table = $wpdb->prefix . 'document_views';
    $sql = "SELECT dv.document_id, dv.document_version, p.post_title, p.post_type, MAX(dv.view_timestamp) AS last_view, COUNT(*) AS view_count, SUM(COALESCE(dv.view_duration, 0)) AS total_duration
            FROM $table dv
            LEFT JOIN {$wpdb->posts} p ON dv.document_id = p.ID
            WHERE dv.user_id = %d
              AND p.ID IS NOT NULL
              AND p.post_status = 'publish'
              AND p.post_type IN ($types_placeholder)
            GROUP BY dv.document_id, dv.document_version
            ORDER BY last_view DESC
            LIMIT %d";

    return $wpdb->get_results($wpdb->prepare($sql, $user_id, $limit));
}

/**
 * Ricerca documenti (Protocollo/Modulo)
 */
function meridiana_search_documents($query, $args = array()) {
    $defaults = array(
        'limit' => 10,
        'post_type' => array('protocollo', 'modulo'),
    );

    $args = wp_parse_args($args, $defaults);
    $post_types = (array) $args['post_type'];

    $search_args = array(
        'post_type' => $post_types,
        'posts_per_page' => intval($args['limit']),
        's' => $query,
        'post_status' => 'publish',
        'orderby' => 'modified',
        'order' => 'DESC',
        'no_found_rows' => true,
        // Ottimizzazione: Rimosso 'fields' => 'ids' per evitare N+1 query con get_post()
    );

    $results = array();
    $documents = new WP_Query($search_args);

    if ($documents->have_posts()) {
        foreach ($documents->posts as $post) {
            // Ottimizzazione: $post è già l'oggetto completo, non serve get_post()
            if (!$post) {
                continue;
            }

            $results[] = array(
                'ID' => $post->ID,
                'post_title' => $post->post_title,
                'post_type' => $post->post_type,
                'modified_at' => get_post_modified_time('Y-m-d H:i:s', true, $post),
            );
        }
        wp_reset_postdata();
    }

    return $results;
}

/**
 * Dettaglio visualizzazioni documento (chi ha visto / non ha visto)
 */
function meridiana_get_document_view_details($document_id, $args = array()) {
    $defaults = array(
        'non_viewers_limit' => 200,
    );

    $args = wp_parse_args($args, $defaults);

    if (!$document_id) {
        return array(
            'viewers' => array(),
            'non_viewers' => array(),
            'non_viewers_count' => 0,
        );
    }

    $viewers = meridiana_get_unique_document_views_by_user($document_id);
    $non_viewers = meridiana_get_users_who_not_viewed($document_id);

    $non_viewers_count = is_array($non_viewers) ? count($non_viewers) : 0;
    $limited_non_viewers = array();

    if (!empty($non_viewers)) {
        $limited_non_viewers = array_slice($non_viewers, 0, intval($args['non_viewers_limit']));
    }

    return array(
        'viewers' => $viewers,
        'non_viewers' => $limited_non_viewers,
        'non_viewers_count' => $non_viewers_count,
    );
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
