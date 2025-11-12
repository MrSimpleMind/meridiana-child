<?php
/**
 * Internal Notification System
 * Crea notifiche interne quando vengono pubblicati contenuti
 * Le notifiche appaiono nella campanella header
 *
 * @package Meridiana Child
 */

if (!defined('ABSPATH')) exit;

/**
 * CPT che generano notifiche interne
 * Esclusi: utenti, organigramma
 */
function meridiana_get_notification_post_types() {
    return [
        'post',                    // Comunicazioni (articoli WP)
        'protocollo',              // Protocolli
        'modulo',                  // Moduli
        'convenzione',             // Convenzioni
        'salute-e-benessere-l',    // Salute e Benessere
        'sfwd-courses',            // Corsi LearnDash
    ];
}

/**
 * Hook transition_post_status: crea notifica solo al PRIMO publish
 *
 * Performance:
 * - Esegue una sola volta (non ad ogni update)
 * - Batch insert recipients (una query per N utenti)
 * - Check duplicati
 *
 * @param string $new_status Nuovo stato del post
 * @param string $old_status Vecchio stato del post
 * @param WP_Post $post Oggetto post
 */
function meridiana_create_internal_notification_on_publish($new_status, $old_status, $post) {
    // Transizione verso publish (non update)
    if ($new_status !== 'publish' || $old_status === 'publish') {
        return;
    }

    // Verifica CPT supportato
    if (!in_array($post->post_type, meridiana_get_notification_post_types(), true)) {
        return;
    }

    // Skip autosave/revision
    if (wp_is_post_revision($post->ID) || wp_is_post_autosave($post->ID)) {
        return;
    }

    // Crea notifica
    meridiana_insert_internal_notification([
        'post_id' => $post->ID,
        'post_type' => $post->post_type,
        'title' => meridiana_get_notification_title($post),
        'message' => meridiana_get_notification_message($post),
        'sender_id' => $post->post_author,
    ]);
}
add_action('transition_post_status', 'meridiana_create_internal_notification_on_publish', 10, 3);

/**
 * Genera titolo notifica basato su post type
 *
 * @param WP_Post $post Post object
 * @return string Titolo notifica (max 100 chars)
 */
function meridiana_get_notification_title($post) {
    $post_type_obj = get_post_type_object($post->post_type);
    $type_label = $post_type_obj ? $post_type_obj->labels->singular_name : ucfirst($post->post_type);

    // Limita titolo a 100 caratteri per performance DB
    $title = sprintf('Nuovo %s: %s', $type_label, $post->post_title);
    return mb_substr($title, 0, 100);
}

/**
 * Genera messaggio notifica (excerpt del contenuto)
 *
 * @param WP_Post $post Post object
 * @return string Messaggio notifica (max 200 chars)
 */
function meridiana_get_notification_message($post) {
    $content = wp_strip_all_tags($post->post_content);
    $excerpt = wp_trim_words($content, 15, '...');

    // Limita a 200 caratteri
    return mb_substr($excerpt ?: 'Clicca per visualizzare', 0, 200);
}

/**
 * Insert notifica nel DB con BATCH INSERT per performance
 *
 * Performance:
 * - Check duplicati prima di insert
 * - Batch insert recipients (una query per tutti gli utenti)
 * - Invalida cache dopo insert
 *
 * @param array $args {
 *     @type int    $post_id      ID del post che ha generato la notifica
 *     @type string $post_type    Tipo di post
 *     @type int    $sender_id    ID autore
 *     @type string $title        Titolo notifica
 *     @type string $message      Messaggio notifica
 * }
 * @return int|false Notification ID o false se errore
 */
function meridiana_insert_internal_notification($args) {
    global $wpdb;

    $defaults = [
        'post_id' => 0,
        'post_type' => '',
        'sender_id' => get_current_user_id(),
        'title' => '',
        'message' => '',
    ];

    $args = wp_parse_args($args, $defaults);

    // Validazione
    if (empty($args['post_id']) || empty($args['title'])) {
        error_log('[Meridiana Notifications] Errore: post_id o title mancanti');
        return false;
    }

    $table_notifications = $wpdb->prefix . 'meridiana_notifications';
    $table_recipients = $wpdb->prefix . 'meridiana_notification_recipients';

    // PERFORMANCE: Check se notifica già esiste per questo post (previene duplicati)
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$table_notifications} WHERE post_id = %d AND post_type = %s LIMIT 1",
        $args['post_id'],
        $args['post_type']
    ));

    if ($exists) {
        error_log('[Meridiana Notifications] Notifica già esistente per post_id=' . $args['post_id']);
        return false;
    }

    // 1. INSERT notifica principale
    $inserted = $wpdb->insert($table_notifications, [
        'post_id' => absint($args['post_id']),
        'post_type' => sanitize_text_field($args['post_type']),
        'sender_id' => absint($args['sender_id']),
        'title' => sanitize_text_field($args['title']),
        'message' => sanitize_text_field($args['message']),
        'notification_type' => 'internal',
        'segmentation_type' => 'all_subscribers',
        'send_email' => 0,
        'created_at' => current_time('mysql'),
        'published_at' => current_time('mysql'),
    ], [
        '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s'
    ]);

    if (!$inserted) {
        error_log('[Meridiana Notifications] Errore insert: ' . $wpdb->last_error);
        return false;
    }

    $notification_id = $wpdb->insert_id;

    // 2. PERFORMANCE: Recupera TUTTI gli utenti (admin, gestore, subscriber)
    // Le notifiche interne appaiono a tutti
    $user_ids = get_users([
        'role__in' => ['administrator', 'gestore_piattaforma', 'subscriber'],
        'fields' => 'ID',
        'number' => -1,
    ]);

    if (empty($user_ids)) {
        error_log('[Meridiana Notifications] Nessun utente trovato');
        return $notification_id;
    }

    // 3. PERFORMANCE: BATCH INSERT recipients in una query
    $values = [];
    $placeholders = [];

    foreach ($user_ids as $user_id) {
        $values[] = $notification_id;
        $values[] = $user_id;
        $placeholders[] = '(%d, %d, NULL, 0, NOW())';
    }

    // Query unica con tutti i recipients
    $query = "INSERT INTO {$table_recipients}
              (notification_id, user_id, read_at, email_sent, created_at)
              VALUES " . implode(', ', $placeholders);

    $result = $wpdb->query($wpdb->prepare($query, $values));

    if ($result === false) {
        error_log('[Meridiana Notifications] Errore insert recipients: ' . $wpdb->last_error);
    }

    // Log successo
    error_log(sprintf(
        '[Meridiana Notifications] ✅ Creata notifica ID=%d per post_id=%d (%s) | Recipients=%d',
        $notification_id,
        $args['post_id'],
        $args['post_type'],
        count($user_ids)
    ));

    // PERFORMANCE: Invalida cache (tutte le chiavi utenti)
    wp_cache_flush_group('meridiana_notifications');

    return $notification_id;
}
