<?php
/**
 * Notification System Debugging & Verification
 * Questo file aiuta a diagnosticare problemi con il sistema di notifiche
 *
 * Accedi via: /wp-json/meridiana/v1/debug/notifications
 *
 * @package Meridiana Child
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registra il route di debug
 */
add_action('rest_api_init', function() {
    register_rest_route('meridiana/v1', '/debug/notifications', [
        'methods' => 'GET',
        'callback' => 'meridiana_debug_notifications',
        'permission_callback' => function(WP_REST_Request $request) {
            // Consenti solo agli admin
            return current_user_can('manage_options');
        }
    ]);

    register_rest_route('meridiana/v1', '/debug/database', [
        'methods' => 'GET',
        'callback' => 'meridiana_debug_database',
        'permission_callback' => function(WP_REST_Request $request) {
            return current_user_can('manage_options');
        }
    ]);
});

/**
 * DEBUG: Mostra informazioni sulle notifiche nel database
 */
function meridiana_debug_notifications(WP_REST_Request $request) {
    global $wpdb;

    $current_user = wp_get_current_user();

    // Informazioni generali
    $info = [
        'user_id' => $current_user->ID,
        'user_login' => $current_user->user_login,
        'current_time' => current_time('mysql'),
        'database' => DB_NAME,
        'tables_exist' => false,
        'tables_data' => [],
        'user_notifications' => [],
    ];

    $table_notifications = $wpdb->prefix . 'meridiana_notifications';
    $table_recipients = $wpdb->prefix . 'meridiana_notification_recipients';

    // Verifica che le tabelle esistono
    $tables_result = $wpdb->get_results(
        "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = %s",
        DB_NAME
    );

    $table_names = wp_list_pluck($tables_result, 'TABLE_NAME');
    $info['tables_exist'] = in_array($table_notifications, $table_names) && in_array($table_recipients, $table_names);

    if (!$info['tables_exist']) {
        return rest_ensure_response([
            'success' => false,
            'error' => 'Tabelle di notifica non trovate',
            'info' => $info,
        ]);
    }

    // Conta i record nelle tabelle
    $total_notifications = intval($wpdb->get_var("SELECT COUNT(*) FROM {$table_notifications}"));
    $total_recipients = intval($wpdb->get_var("SELECT COUNT(*) FROM {$table_recipients}"));
    $unread_for_user = intval($wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_recipients} WHERE user_id = %d AND read_at IS NULL",
            $current_user->ID
        )
    ));

    $info['tables_data'] = [
        'notifications_total' => $total_notifications,
        'recipients_total' => $total_recipients,
        'unread_for_current_user' => $unread_for_user,
    ];

    // Recupera ultime 5 notifiche per l'utente
    $notifications = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT
                n.id,
                n.post_id,
                n.post_type,
                n.title,
                n.message,
                n.created_at,
                n.published_at,
                r.notification_id,
                r.read_at,
                r.user_id,
                p.post_title,
                p.post_type as actual_post_type,
                p.guid as post_link
            FROM {$table_notifications} n
            INNER JOIN {$table_recipients} r ON n.id = r.notification_id
            LEFT JOIN {$wpdb->posts} p ON n.post_id = p.ID
            WHERE r.user_id = %d
            ORDER BY n.created_at DESC
            LIMIT 5",
            $current_user->ID
        ),
        ARRAY_A
    );

    // Formatta le notifiche come farebbe l'API
    $formatted_notifications = [];
    foreach ($notifications as $notif) {
        $formatted_notifications[] = [
            'notification_id' => intval($notif['id']),
            'post_id' => intval($notif['post_id']),
            'post_type' => $notif['post_type'],
            'title' => sanitize_text_field($notif['title']),
            'message' => sanitize_text_field($notif['message']),
            'created_at' => $notif['created_at'],
            'read_at' => $notif['read_at'],
            'is_read' => !empty($notif['read_at']),
            'post_link' => esc_url($notif['post_link'] ?? ''),
            // Debug info
            'raw_title' => $notif['title'],
            'raw_created_at' => $notif['created_at'],
        ];
    }

    $info['user_notifications'] = $formatted_notifications;

    return rest_ensure_response([
        'success' => true,
        'info' => $info,
        'raw_query' => $notifications,
    ]);
}

/**
 * DEBUG: Informazioni sulla struttura del database
 */
function meridiana_debug_database(WP_REST_Request $request) {
    global $wpdb;

    if (!current_user_can('manage_options')) {
        return new WP_Error('not_allowed', 'Non autorizzato');
    }

    $table_notifications = $wpdb->prefix . 'meridiana_notifications';
    $table_recipients = $wpdb->prefix . 'meridiana_notification_recipients';

    $info = [
        'notifications_table' => [
            'name' => $table_notifications,
            'columns' => [],
            'exists' => false,
        ],
        'recipients_table' => [
            'name' => $table_recipients,
            'columns' => [],
            'exists' => false,
        ],
    ];

    // Verifica tabella notifiche
    $notif_columns = $wpdb->get_results("DESCRIBE {$table_notifications}", ARRAY_A);
    if (!empty($notif_columns)) {
        $info['notifications_table']['exists'] = true;
        $info['notifications_table']['columns'] = $notif_columns;
    }

    // Verifica tabella recipients
    $recip_columns = $wpdb->get_results("DESCRIBE {$table_recipients}", ARRAY_A);
    if (!empty($recip_columns)) {
        $info['recipients_table']['exists'] = true;
        $info['recipients_table']['columns'] = $recip_columns;
    }

    return rest_ensure_response([
        'success' => true,
        'database_info' => $info,
    ]);
}
