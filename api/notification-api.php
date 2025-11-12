<?php
/**
 * REST API Endpoints - Notifiche
 * Gestisce il recupero e l'aggiornamento dello stato delle notifiche
 *
 * @package Meridiana Child
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Assicurati che le tabelle di notifica siano create
 */
function meridiana_ensure_notification_tables() {
    global $wpdb;

    $notifications_table = $wpdb->prefix . 'meridiana_notifications';
    $recipients_table = $wpdb->prefix . 'meridiana_notification_recipients';

    // Se la tabella già esiste, non fare nulla
    if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $notifications_table))) {
        return; // Tabella esiste già
    }

    error_log('[NotificationAPI] Creating notification tables...');

    $charset_collate = $wpdb->get_charset_collate();

    // Tabella principale notifiche
    $sql_notifications = "CREATE TABLE IF NOT EXISTS $notifications_table (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        post_id BIGINT UNSIGNED NOT NULL,
        post_type VARCHAR(50) NOT NULL,
        sender_id BIGINT UNSIGNED NOT NULL,
        title VARCHAR(255) NOT NULL,
        message LONGTEXT,
        notification_type VARCHAR(50) DEFAULT 'push',
        segmentation_type VARCHAR(50) NOT NULL,
        send_email TINYINT(1) DEFAULT 0,
        created_at DATETIME NOT NULL,
        published_at DATETIME,
        KEY idx_post_id (post_id),
        KEY idx_sender_id (sender_id),
        KEY idx_created_at (created_at),
        KEY idx_post_type (post_type),
        KEY idx_notification_type (notification_type)
    ) $charset_collate;";

    // Tabella destinatari
    $sql_recipients = "CREATE TABLE IF NOT EXISTS $recipients_table (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        notification_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        read_at DATETIME,
        email_sent TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_notification_id (notification_id),
        KEY idx_user_id (user_id),
        KEY idx_read_at (read_at)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql_notifications);
    dbDelta($sql_recipients);

    error_log('[NotificationAPI] Notification tables created successfully');
}

/**
 * Registra i REST routes per le notifiche
 */
add_action('rest_api_init', function() {
    // Nota: il check delle tabelle è già fatto in db-setup.php
    // Non ripetere qui per evitare rallentamenti
    register_rest_route('meridiana/v1', '/notifications', [
        'methods' => 'GET',
        'callback' => 'meridiana_get_user_notifications',
        'permission_callback' => 'meridiana_check_notification_permission',
        'args' => [
            'read' => [
                'type' => 'boolean',
                'description' => 'Filtra per notifiche lette/non lette',
                'required' => false,
            ],
            'limit' => [
                'type' => 'integer',
                'description' => 'Numero di notifiche da recuperare',
                'default' => 20,
                'required' => false,
            ],
        ]
    ]);

    register_rest_route('meridiana/v1', '/notifications/read', [
        'methods' => 'POST',
        'callback' => 'meridiana_mark_notifications_read',
        'permission_callback' => 'meridiana_check_notification_permission',
        'args' => [
            'notification_ids' => [
                'type' => 'array',
                'items' => ['type' => 'integer'],
                'description' => 'Array di notification IDs da marcare come letti',
                'required' => true,
            ]
        ]
    ]);

    register_rest_route('meridiana/v1', '/notifications/count-unread', [
        'methods' => 'GET',
        'callback' => 'meridiana_get_unread_notifications_count',
        'permission_callback' => 'meridiana_check_notification_permission',
    ]);
});

/**
 * Verifica i permessi per l'accesso alle notifiche
 */
function meridiana_check_notification_permission(WP_REST_Request $request) {
    // Deve essere loggato
    if (!is_user_logged_in()) {
        return false;
    }

    // Verifica il nonce
    $nonce = $request->get_header('X-WP-Nonce');
    if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
        return false;
    }

    return true;
}

/**
 * GET /wp-json/meridiana/v1/notifications
 * Recupera le notifiche dell'utente corrente
 * OTTIMIZZATO: Query ridotta + caching
 */
function meridiana_get_user_notifications(WP_REST_Request $request) {
    global $wpdb;

    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $read = $request->get_param('read');
    $limit = intval($request->get_param('limit')) ?: 10; // Ridotto da 20 a 10 per velocità
    $offset = intval($request->get_param('offset')) ?: 0;

    if (!$user_id) {
        return new WP_Error('not_logged_in', 'Non loggato', ['status' => 401]);
    }

    // OTTIMIZZAZIONE: Controlla cache prima
    $cache_key = 'meridiana_notif_' . $user_id . '_' . md5($read . $limit . $offset);
    $cached = wp_cache_get($cache_key);
    if (false !== $cached) {
        error_log('[NotificationAPI] Returning cached notifications for user ' . $user_id);
        return rest_ensure_response($cached);
    }

    $table_notifications = $wpdb->prefix . 'meridiana_notifications';
    $table_recipients = $wpdb->prefix . 'meridiana_notification_recipients';

    // OTTIMIZZAZIONE: Query semplificata
    // Seleziona SOLO dalla tabella recipients per prima
    // Poi fetcha i dati delle notifiche con una query separata
    $recipient_query = "
        SELECT DISTINCT r.notification_id, r.read_at
        FROM {$table_recipients} r
        INNER JOIN {$table_notifications} n ON r.notification_id = n.id
        WHERE r.user_id = %d
    ";

    $params = [$user_id];

    // Filtra per lette/non lette se richiesto
    if ($read !== null && is_bool($read)) {
        if ($read) {
            $recipient_query .= " AND r.read_at IS NOT NULL";
        } else {
            $recipient_query .= " AND r.read_at IS NULL";
        }
    }

    // IMPORTANTE: Ordina per created_at DESC (notifiche PIÙ NUOVE per prime, non più vecchie)
    $recipient_query .= " ORDER BY n.created_at DESC LIMIT %d OFFSET %d";
    $params[] = $limit;
    $params[] = $offset;

    error_log('[NotificationAPI] Executing notification query for user ' . $user_id);
    $start_time = microtime(true);

    $recipient_results = $wpdb->get_results($wpdb->prepare($recipient_query, $params), ARRAY_A);

    $elapsed = microtime(true) - $start_time;
    error_log('[NotificationAPI] Query completed in ' . round($elapsed * 1000, 2) . 'ms');

    if (is_wp_error($recipient_results) || empty($recipient_results)) {
        return rest_ensure_response([
            'success' => true,
            'notifications' => [],
            'count' => 0,
        ]);
    }

    // Estrai gli ID delle notifiche
    $notification_ids = array_column($recipient_results, 'notification_id');

    // Seconda query: Fetcha i dati completi delle notifiche
    $notifications_query = "
        SELECT
            n.id,
            n.post_id,
            n.post_type,
            n.title,
            n.message,
            n.created_at,
            n.published_at
        FROM {$table_notifications} n
        WHERE n.id IN (" . implode(',', array_map('intval', $notification_ids)) . ")
        ORDER BY n.created_at DESC
    ";

    $notifications = $wpdb->get_results($notifications_query, ARRAY_A);

    // Mappa i dati della notifica con read_at
    $read_map = array_column($recipient_results, 'read_at', 'notification_id');

    // Formatta le notifiche per la risposta
    $formatted = [];
    foreach ($notifications as $notif) {
        $notif_id = intval($notif['id']);
        $formatted[] = [
            'notification_id' => $notif_id,
            'post_id' => intval($notif['post_id']),
            'post_type' => $notif['post_type'],
            'title' => sanitize_text_field($notif['title']),
            'message' => sanitize_text_field($notif['message']),
            'created_at' => $notif['created_at'],
            'read_at' => $read_map[$notif_id] ?? null,
            'is_read' => !empty($read_map[$notif_id]),
            'post_link' => '', // Rimosso LEFT JOIN per velocità - aggiunto solo se necessario
        ];
    }

    $response = [
        'success' => true,
        'notifications' => $formatted,
        'count' => count($formatted),
    ];

    // Cache per 2 minuti
    wp_cache_set($cache_key, $response, '', 120);

    return rest_ensure_response($response);
}

/**
 * POST /wp-json/meridiana/v1/notifications/read
 * Marca le notifiche come lette
 */
function meridiana_mark_notifications_read(WP_REST_Request $request) {
    global $wpdb;

    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $notification_ids = $request->get_param('notification_ids');

    if (!$user_id) {
        return new WP_Error('not_logged_in', 'Non loggato', ['status' => 401]);
    }

    if (!is_array($notification_ids) || empty($notification_ids)) {
        return new WP_Error('invalid_ids', 'IDs delle notifiche non validi', ['status' => 400]);
    }

    // Sanitizza gli IDs
    $notification_ids = array_map('intval', $notification_ids);

    $table_recipients = $wpdb->prefix . 'meridiana_notification_recipients';
    $current_time = current_time('mysql');

    // Aggiorna solo le notifiche dell'utente corrente
    $updated = 0;
    foreach ($notification_ids as $notif_id) {
        $result = $wpdb->update(
            $table_recipients,
            ['read_at' => $current_time],
            [
                'notification_id' => $notif_id,
                'user_id' => $user_id,
                'read_at' => null,  // Solo se non già letto
            ],
            ['%s'],
            ['%d', '%d', NULL]
        );

        if ($result > 0) {
            $updated += $result;
        }
    }

    return rest_ensure_response([
        'success' => true,
        'message' => sprintf('Aggiornate %d notifiche', $updated),
        'updated_count' => $updated,
    ]);
}

/**
 * GET /wp-json/meridiana/v1/notifications/count-unread
 * Conta le notifiche non lette dell'utente
 */
function meridiana_get_unread_notifications_count(WP_REST_Request $request) {
    global $wpdb;

    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    if (!$user_id) {
        return new WP_Error('not_logged_in', 'Non loggato', ['status' => 401]);
    }

    $table_recipients = $wpdb->prefix . 'meridiana_notification_recipients';

    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table_recipients} WHERE user_id = %d AND read_at IS NULL",
        $user_id
    ));

    return rest_ensure_response([
        'success' => true,
        'unread_count' => intval($count ?: 0),
    ]);
}
