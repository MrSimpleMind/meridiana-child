<?php
/**
 * Database Setup - Crea tabelle analytics
 *
 * @package Meridiana Child
 */

if (!defined('ABSPATH')) exit;

/**
 * Crea la tabella document_views se non esiste
 */
function meridiana_create_analytics_tables() {
    global $wpdb;

    // Esegui una sola volta
    if (get_option('meridiana_analytics_tables_created')) {
        return;
    }

    $table_name = $wpdb->prefix . 'document_views';
    $charset_collate = $wpdb->get_charset_collate();

    // SQL per creare la tabella
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        document_id BIGINT UNSIGNED NOT NULL,
        document_type VARCHAR(50) NOT NULL,
        user_profile VARCHAR(255),
        view_timestamp DATETIME NOT NULL,
        view_duration INT UNSIGNED DEFAULT 0,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_user_id (user_id),
        KEY idx_document_id (document_id),
        KEY idx_timestamp (view_timestamp),
        KEY idx_document_type (document_type)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Marca come creato
    update_option('meridiana_analytics_tables_created', true);
}
add_action('init', 'meridiana_create_analytics_tables');

/**
 * Crea le tabelle per il sistema di notifiche frontend
 */
function meridiana_create_notification_tables() {
    global $wpdb;

    // Esegui una sola volta
    if (get_option('meridiana_notification_tables_created')) {
        return;
    }

    $charset_collate = $wpdb->get_charset_collate();

    // Tabella principale per le notifiche
    $notifications_table = $wpdb->prefix . 'meridiana_notifications';
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

    // Tabella per i destinatari delle notifiche
    $recipients_table = $wpdb->prefix . 'meridiana_notification_recipients';
    $sql_recipients = "CREATE TABLE IF NOT EXISTS $recipients_table (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        notification_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        read_at DATETIME,
        email_sent TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_notification_id (notification_id),
        KEY idx_user_id (user_id),
        KEY idx_read_at (read_at),
        FOREIGN KEY (notification_id) REFERENCES $notifications_table(id) ON DELETE CASCADE
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_notifications);
    dbDelta($sql_recipients);

    // Marca come creato
    update_option('meridiana_notification_tables_created', true);
}
add_action('init', 'meridiana_create_notification_tables');
