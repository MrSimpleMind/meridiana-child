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
