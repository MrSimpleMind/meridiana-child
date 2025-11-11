<?php
/**
 * Frontend Notification Settings
 * Sistema di notifiche decentralizzato: ogni contenuto gestisce le proprie notifiche
 *
 * @package Meridiana Child
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Renderizza la sezione notifiche nel form frontend
 */
function meridiana_render_notification_section($post_type, $post_id = 0) {
    $send_notification = false;
    $segmentation_type = 'all';
    $selected_profili = [];
    $selected_udos = [];
    $send_email = false;

    // Se è un edit, carica i dati salvati
    if ($post_id) {
        $send_notification = get_post_meta($post_id, '_notification_enabled', true);
        $segmentation_type = get_post_meta($post_id, '_notification_segmentation_type', true) ?: 'all';
        $selected_profili = get_post_meta($post_id, '_notification_profili', true) ?: [];
        $selected_udos = get_post_meta($post_id, '_notification_udos', true) ?: [];
        $send_email = get_post_meta($post_id, '_notification_send_email', true);
    }

    // Recupera i termini disponibili
    $profili_terms = get_terms([
        'taxonomy' => 'profili_professionali',
        'hide_empty' => false,
    ]);
    $udos_terms = get_terms([
        'taxonomy' => 'unita_offerta',
        'hide_empty' => false,
    ]);
    ?>
    <div class="acf-form-fields" style="border-top: 2px solid #e5e7eb; margin-top: 20px; padding-top: 20px;">
        <h3 style="margin-top: 0; font-size: 16px; color: #1f2937;">Impostazioni Notifiche</h3>

        <!-- Abilita notifiche -->
        <div class="acf-field acf-field-true_false">
            <div class="acf-label">
                <label for="notification_enabled">
                    <span style="display: inline-flex; align-items: center;">
                        <input type="checkbox" id="notification_enabled" name="notification_enabled" value="1" <?php checked($send_notification, 1); ?> />
                        <span style="margin-left: 8px;">Invia notifiche quando pubblico questo contenuto</span>
                    </span>
                </label>
            </div>
        </div>

        <!-- Sezione destinatari (visibile solo se notifiche abilitate) -->
        <div id="notification-recipients-section" style="display: <?php echo $send_notification ? 'block' : 'none'; ?>; margin-top: 15px;">

            <!-- Tipo segmentazione -->
            <div class="acf-field acf-field-select">
                <div class="acf-label">
                    <label for="notification_segmentation">Invia a: <span class="required">*</span></label>
                </div>
                <div class="acf-input">
                    <select id="notification_segmentation" name="notification_segmentation" class="notification-segmentation-select">
                        <option value="all" <?php selected($segmentation_type, 'all'); ?>>Tutti gli utenti</option>
                        <option value="profili" <?php selected($segmentation_type, 'profili'); ?>>Profili professionali specifici</option>
                        <option value="udos" <?php selected($segmentation_type, 'udos'); ?>>Unità Offerta specifiche</option>
                        <option value="profili_and_udos" <?php selected($segmentation_type, 'profili_and_udos'); ?>>Profili + UDO (combinazione)</option>
                    </select>
                </div>
            </div>

            <!-- Seleziona Profili (visibile per profili e profili_and_udos) -->
            <div id="notification-profili-section" style="display: <?php echo in_array($segmentation_type, ['profili', 'profili_and_udos']) ? 'block' : 'none'; ?>; margin-top: 15px;">
                <div class="acf-field acf-field-checkbox">
                    <div class="acf-label">
                        <label>Profili Professionali:</label>
                    </div>
                    <div class="acf-input">
                        <div style="max-height: 200px; overflow-y: auto; border: 1px solid #d1d5db; border-radius: 4px; padding: 10px;">
                            <?php if (!is_wp_error($profili_terms) && !empty($profili_terms)) : ?>
                                <?php foreach ($profili_terms as $term) : ?>
                                    <label style="display: flex; align-items: center; margin-bottom: 8px; cursor: pointer;">
                                        <input type="checkbox" name="notification_profili[]" value="<?php echo esc_attr($term->term_id); ?>"
                                            <?php checked(in_array($term->term_id, $selected_profili)); ?> />
                                        <span style="margin-left: 8px;"><?php echo esc_html($term->name); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <p style="color: #999;">Nessun profilo disponibile</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seleziona UDO (visibile per udos e profili_and_udos) -->
            <div id="notification-udos-section" style="display: <?php echo in_array($segmentation_type, ['udos', 'profili_and_udos']) ? 'block' : 'none'; ?>; margin-top: 15px;">
                <div class="acf-field acf-field-checkbox">
                    <div class="acf-label">
                        <label>Unità Offerta:</label>
                    </div>
                    <div class="acf-input">
                        <div style="max-height: 200px; overflow-y: auto; border: 1px solid #d1d5db; border-radius: 4px; padding: 10px;">
                            <?php if (!is_wp_error($udos_terms) && !empty($udos_terms)) : ?>
                                <?php foreach ($udos_terms as $term) : ?>
                                    <label style="display: flex; align-items: center; margin-bottom: 8px; cursor: pointer;">
                                        <input type="checkbox" name="notification_udos[]" value="<?php echo esc_attr($term->term_id); ?>"
                                            <?php checked(in_array($term->term_id, $selected_udos)); ?> />
                                        <span style="margin-left: 8px;"><?php echo esc_html($term->name); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <p style="color: #999;">Nessuna UDO disponibile</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Opzione invio email -->
            <div class="acf-field acf-field-true_false" style="margin-top: 15px;">
                <div class="acf-label">
                    <label for="notification_send_email">
                        <span style="display: inline-flex; align-items: center;">
                            <input type="checkbox" id="notification_send_email" name="notification_send_email" value="1" <?php checked($send_email, 1); ?> />
                            <span style="margin-left: 8px;">Invia anche un'email ai destinatari</span>
                        </span>
                    </label>
                </div>
            </div>

        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const enableCheckbox = document.getElementById('notification_enabled');
        const recipientsSection = document.getElementById('notification-recipients-section');
        const segmentationSelect = document.getElementById('notification_segmentation');
        const profiliSection = document.getElementById('notification-profili-section');
        const udosSection = document.getElementById('notification-udos-section');

        if (enableCheckbox) {
            enableCheckbox.addEventListener('change', function() {
                recipientsSection.style.display = this.checked ? 'block' : 'none';
            });
        }

        if (segmentationSelect) {
            segmentationSelect.addEventListener('change', function() {
                const value = this.value;
                profiliSection.style.display = ['profili', 'profili_and_udos'].includes(value) ? 'block' : 'none';
                udosSection.style.display = ['udos', 'profili_and_udos'].includes(value) ? 'block' : 'none';
            });
        }
    });
    </script>
    <?php
}

/**
 * Salva le impostazioni notifiche quando viene pubblicato/aggiornato un post
 */
function meridiana_save_notification_settings($post_id) {
    // Verifica che sia una chiamata AJAX legittima
    if (!isset($_POST['post_type']) || !isset($_POST['notification_enabled'])) {
        return;
    }

    // Salva le impostazioni
    $send_notification = isset($_POST['notification_enabled']) ? 1 : 0;
    update_post_meta($post_id, '_notification_enabled', $send_notification);

    if ($send_notification) {
        $segmentation_type = isset($_POST['notification_segmentation']) ? sanitize_text_field($_POST['notification_segmentation']) : 'all';
        update_post_meta($post_id, '_notification_segmentation_type', $segmentation_type);

        // Salva profili selezionati
        $profili = isset($_POST['notification_profili']) ? array_map('intval', $_POST['notification_profili']) : [];
        update_post_meta($post_id, '_notification_profili', $profili);

        // Salva UDO selezionati
        $udos = isset($_POST['notification_udos']) ? array_map('intval', $_POST['notification_udos']) : [];
        update_post_meta($post_id, '_notification_udos', $udos);

        // Salva opzione email
        $send_email = isset($_POST['notification_send_email']) ? 1 : 0;
        update_post_meta($post_id, '_notification_send_email', $send_email);
    }
}

/**
 * Recupera gli user_ids in base alla segmentazione
 *
 * @param string $segmentation_type Tipo di segmentazione (all, profili, udos, profili_and_udos, post_terms)
 * @param array $selected_profili IDs dei profili selezionati
 * @param array $selected_udos IDs delle UDO selezionate
 * @param int $post_id ID del post (per segmentazione post_terms)
 */
function meridiana_get_notification_recipients($segmentation_type, $selected_profili = [], $selected_udos = [], $post_id = 0) {
    $users = [];

    switch ($segmentation_type) {
        case 'all':
            // Tutti gli utenti loggati (non admin)
            $all_users = get_users([
                'exclude' => [1], // Esclude admin
                'role__not_in' => ['administrator']
            ]);
            $users = wp_list_pluck($all_users, 'ID');
            break;

        case 'profili':
            // Solo utenti che hanno uno dei profili selezionati
            if (!empty($selected_profili)) {
                $users = meridiana_get_users_by_terms($selected_profili, 'profilo-professionale', 'IN');
            }
            break;

        case 'udos':
            // Solo utenti che hanno una delle UDO selezionate
            if (!empty($selected_udos)) {
                $users = meridiana_get_users_by_terms($selected_udos, 'unita-offerta', 'IN');
            }
            break;

        case 'profili_and_udos':
            // Utenti che hanno ALMENO UN profilo E ALMENO UN'UDO dai selezionati (OR logic)
            $users_profili = !empty($selected_profili) ? meridiana_get_users_by_terms($selected_profili, 'profilo-professionale', 'IN') : [];
            $users_udos = !empty($selected_udos) ? meridiana_get_users_by_terms($selected_udos, 'unita-offerta', 'IN') : [];
            $users = array_unique(array_merge($users_profili, $users_udos));
            break;

        case 'post_terms':
            // Usa le tassonomie assegnate al POST (per Protocollo/Modulo)
            if ($post_id) {
                $post = get_post($post_id);
                if ($post) {
                    // Recupera profili e UDO dal post
                    $post_profili = wp_get_post_terms($post_id, 'profilo-professionale', ['fields' => 'ids']);
                    $post_udos = wp_get_post_terms($post_id, 'unita-offerta', ['fields' => 'ids']);

                    if (!empty($post_profili) || !empty($post_udos)) {
                        // Combina profili e UDO del post per trovare utenti
                        $users_profili = !empty($post_profili) ? meridiana_get_users_by_terms($post_profili, 'profilo-professionale', 'IN') : [];
                        $users_udos = !empty($post_udos) ? meridiana_get_users_by_terms($post_udos, 'unita-offerta', 'IN') : [];
                        $users = array_unique(array_merge($users_profili, $users_udos));
                    }
                }
            }
            break;
    }

    return array_unique(array_filter($users));
}

/**
 * Helper: Recupera gli user_ids che hanno una delle tassonomie selezionate
 */
function meridiana_get_users_by_terms($term_ids = [], $taxonomy = '', $compare = 'IN') {
    if (empty($term_ids) || empty($taxonomy)) {
        return [];
    }

    $term_ids = array_filter(array_map('intval', $term_ids));
    if (empty($term_ids)) {
        return [];
    }

    // Query gli utenti che hanno uno dei termini
    $user_args = [
        'fields' => 'ID',
        'exclude' => [1], // Esclude admin
        'role__not_in' => ['administrator']
    ];

    // Usa il meta della relazione user-taxonomy se disponibile
    // Oppure query diretta dalle relazioni
    $users_query = new WP_User_Query($user_args);
    $all_users = $users_query->get_results();

    $users = [];
    foreach ($all_users as $user_id) {
        // Verifica se l'utente ha uno dei termini nella tassonomia
        $user_terms = wp_get_object_terms($user_id, $taxonomy, ['fields' => 'ids']);
        if (!is_wp_error($user_terms)) {
            $user_term_ids = array_map('intval', $user_terms);
            if (array_intersect($user_term_ids, $term_ids)) {
                $users[] = $user_id;
            }
        }
    }

    return array_unique($users);
}

/**
 * Crea un record di notifica nel database
 */
function meridiana_create_notification_record($post_id, $user_ids, $send_email = false) {
    global $wpdb;

    $post = get_post($post_id);
    if (!$post) {
        return false;
    }

    $current_user = wp_get_current_user();
    $segmentation_type = get_post_meta($post_id, '_notification_segmentation_type', true) ?: 'all';

    // Inserisci la notifica principale
    $notification_data = [
        'post_id' => $post_id,
        'post_type' => $post->post_type,
        'sender_id' => $current_user->ID,
        'title' => $post->post_title,
        'message' => wp_trim_words($post->post_excerpt ?: $post->post_content, 20),
        'notification_type' => 'push',
        'segmentation_type' => $segmentation_type,
        'send_email' => $send_email ? 1 : 0,
        'created_at' => current_time('mysql'),
        'published_at' => current_time('mysql'),
    ];

    $inserted = $wpdb->insert(
        $wpdb->prefix . 'meridiana_notifications',
        $notification_data,
        ['%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s']
    );

    if (!$inserted) {
        error_log('Errore inserimento notifica: ' . $wpdb->last_error);
        return false;
    }

    $notification_id = $wpdb->insert_id;

    // Inserisci i destinatari
    foreach ($user_ids as $user_id) {
        $wpdb->insert(
            $wpdb->prefix . 'meridiana_notification_recipients',
            [
                'notification_id' => $notification_id,
                'user_id' => $user_id,
                'email_sent' => 0,
            ],
            ['%d', '%d', '%d']
        );
    }

    return $notification_id;
}

/**
 * Hook per inviare notifiche al publish di un contenuto
 * NOTA: Questo hook è DISABILITATO perché la form del gestore usa meridiana_handle_document_notification()
 * Questo sarebbe usato solo se le notifiche venissero salvate tramite ACF field (non dalla form del gestore)
 * Usa SEMPRE i field ACF per la segmentazione (notification_profili e notification_udos)
 * Non usa le tassonomie del post, ma i Profili/UDO degli UTENTI selezionati dall'admin
 */
// DISABILITATO - usa meridiana_handle_document_notification() dalla form del gestore
/*
add_action('meridiana_after_document_save', function($post_id, $cpt) {
    $post = get_post($post_id);
    if (!$post) {
        return;
    }

    // Leggi il field ACF "notification_abilita"
    $send_notification = get_field('notification_abilita', $post_id);

    if (!$send_notification) {
        return;
    }

    // Per TUTTI i post types: usa i field ACF notification_profili e notification_udos
    $selected_profili = get_field('notification_profili', $post_id) ?: [];
    $selected_udos = get_field('notification_udos', $post_id) ?: [];

    if (empty($selected_profili) && empty($selected_udos)) {
        error_log('[Meridiana Notifications] No recipients selected for ' . $post->post_type . ' ID: ' . $post_id);
        return;
    }

    // Ottieni gli utenti che hanno uno dei Profili/UDO selezionati (OR logic)
    $segmentation_type = 'profili_and_udos'; // OR: utenti con almeno uno tra i profili/UDO
    $user_ids = meridiana_get_notification_recipients($segmentation_type, $selected_profili, $selected_udos, $post_id);

    if (empty($user_ids)) {
        error_log('[Meridiana Notifications] No users found with selected profiles/UDOs for ' . $post->post_type . ' ID: ' . $post_id);
        return;
    }

    // Leggi opzione email
    $send_email = get_field('notification_send_email', $post_id) ?: false;

    // Crea il record di notifica
    $notification_id = meridiana_create_notification_record($post_id, $user_ids, $send_email);

    if (!$notification_id) {
        error_log('[Meridiana Notifications] Failed to create notification record for post ID: ' . $post_id);
        return;
    }

    error_log('[Meridiana Notifications] Notification created. ID: ' . $notification_id . ', Post ID: ' . $post_id . ', Recipients: ' . count($user_ids));

    // Invia OneSignal (se configurato)
    do_action('meridiana_send_push_notification', $notification_id, $post_id, $user_ids);

    // Invia Email (se configurato)
    if ($send_email) {
        do_action('meridiana_send_email_notification', $notification_id, $post_id, $user_ids);
    }
}, 10, 2);
*/

/**
 * Invia notifiche push via OneSignal
 */
add_action('meridiana_send_push_notification', function($notification_id, $post_id, $user_ids) {
    global $wpdb;

    // Verifica che OneSignal sia configurato
    $app_id = get_field('meridiana_onesignal_app_id', 'option');
    $rest_key = get_field('meridiana_onesignal_rest_api_key', 'option');

    if (!$app_id || !$rest_key) {
        error_log('[Meridiana Notification] OneSignal non configurato');
        return;
    }

    $post = get_post($post_id);
    if (!$post) {
        return;
    }

    // Prepara i dati per OneSignal
    $post_type_obj = get_post_type_object($post->post_type);
    $post_type_label = $post_type_obj ? $post_type_obj->labels->singular_name : $post->post_type;

    $title = $post_type_label . ': ' . $post->post_title;
    $message = wp_trim_words($post->post_excerpt ?: $post->post_content, 20);

    $payload = [
        'app_id' => $app_id,
        'include_external_user_ids' => array_map('strval', $user_ids),
        'headings' => ['en' => $title],
        'contents' => ['en' => $message],
        'big_picture' => '',
        'ios_attachments' => ['image' => ''],
        'data' => [
            'post_id' => $post_id,
            'post_type' => $post->post_type,
            'notification_id' => $notification_id,
            'site_url' => home_url()
        ]
    ];

    // Invia a OneSignal
    $response = wp_remote_post('https://onesignal.com/api/v1/notifications', [
        'method' => 'POST',
        'blocking' => false,
        'sslverify' => true,
        'timeout' => 5,
        'headers' => [
            'Content-Type' => 'application/json; charset=utf-8',
            'Authorization' => 'Basic ' . $rest_key
        ],
        'body' => wp_json_encode($payload)
    ]);

    if (is_wp_error($response)) {
        // Silenzio gli errori in produzione, solo log per debug
        return;
    }

    // Log solo se WP_DEBUG è attivo
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[Meridiana Notification] OneSignal inviato. ID: ' . $notification_id);
    }
}, 10, 3);

/**
 * Invia notifiche via Email
 */
add_action('meridiana_send_email_notification', function($notification_id, $post_id, $user_ids) {
    global $wpdb;

    $post = get_post($post_id);
    if (!$post) {
        return;
    }

    $post_type_obj = get_post_type_object($post->post_type);
    $post_type_label = $post_type_obj ? $post_type_obj->labels->singular_name : $post->post_type;

    // Prepara il contenuto dell'email
    $subject = 'Nuovo ' . $post_type_label . ': ' . $post->post_title;
    $message = wp_trim_words($post->post_excerpt ?: $post->post_content, 50);
    $post_url = get_permalink($post_id);

    // Template HTML semplice
    $email_body = "
    <h2>$post_type_label: {$post->post_title}</h2>
    <p>$message</p>
    <p><a href='$post_url'>Visualizza il contenuto completo</a></p>
    <hr>
    <p style='color: #999; font-size: 12px;'>Questo è un notifica automatica dalla piattaforma.</p>
    ";

    // Invia email a ogni utente
    foreach ($user_ids as $user_id) {
        $user = get_user_by('ID', $user_id);
        if (!$user || !$user->user_email) {
            continue;
        }

        // Prepara gli header dell'email
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        // Invia l'email
        $sent = wp_mail(
            $user->user_email,
            $subject,
            $email_body,
            $headers
        );

        // Aggiorna il record nel database
        if ($sent) {
            $wpdb->update(
                $wpdb->prefix . 'meridiana_notification_recipients',
                ['email_sent' => 1],
                [
                    'notification_id' => $notification_id,
                    'user_id' => $user_id
                ],
                ['%d'],
                ['%d', '%d']
            );
        }
    }

    // Log solo se WP_DEBUG è attivo
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[Meridiana Notification] Email inviate. ID: ' . $notification_id);
    }
}, 10, 3);
