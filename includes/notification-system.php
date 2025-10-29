<?php
/**
 * Notification System Core
 * Sistema event-driven per gestire notifiche push OneSignal
 * Tutto configurabile via ACF - questo file legge la configurazione e invia notifiche
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class: MeridianaNotificationSystem
 * Gestisce trigger, template, segmentazione e invio notifiche
 */
class MeridianaNotificationSystem {

    private static $triggers = array();
    private static $initialized = false;

    /**
     * Carica i trigger dalla configurazione ACF
     * Override il nome campo se necessario nel secondo parametro
     */
    public static function init($field_name = 'notification_triggers', $option_name = 'option') {
        if (self::$initialized) {
            return;
        }

        // Carica trigger da ACF (di default dalla option page)
        $notification_triggers = get_field($field_name, $option_name);

        if ($notification_triggers && is_array($notification_triggers)) {
            foreach ($notification_triggers as $trigger_config) {
                self::register_trigger_from_acf($trigger_config);
            }
        }

        // Attacca i hook dynamicamente
        self::attach_hooks();

        self::$initialized = true;
    }

    /**
     * Registra un trigger dalla configurazione ACF
     */
    private static function register_trigger_from_acf($config) {
        if (empty($config['trigger_id']) || empty($config['trigger_post_type'])) {
            return;
        }

        $post_type = $config['trigger_post_type'];
        $trigger_id = $config['trigger_id'];

        self::$triggers[$trigger_id] = array(
            'id' => $trigger_id,
            'post_type' => $post_type,
            'hook' => 'publish_' . $post_type,
            'title_template' => $config['trigger_title_template'] ?? 'Nuovo {{post_type}}',
            'message_template' => $config['trigger_message_template'] ?? '{{title}}',
            'icon_emoji' => $config['trigger_icon_emoji'] ?? '📬',
            'segmentation_rule_id' => $config['trigger_segmentation_rule'] ?? 0,
            'enabled' => isset($config['trigger_enabled']) ? (bool) $config['trigger_enabled'] : true
        );
    }

    /**
     * Attacca gli hook WordPress per i trigger
     */
    private static function attach_hooks() {
        foreach (self::$triggers as $trigger_id => $trigger) {
            if ($trigger['enabled']) {
                add_action($trigger['hook'], function($post_id) use ($trigger_id) {
                    self::trigger_notification($trigger_id, $post_id);
                }, 10, 1);
            }
        }
    }

    /**
     * Ottieni tutti i trigger
     */
    public static function get_triggers() {
        self::init();
        return self::$triggers;
    }

    /**
     * Ottieni un trigger specifico
     */
    public static function get_trigger($trigger_id) {
        self::init();
        return self::$triggers[$trigger_id] ?? null;
    }

    /**
     * TRIGGER: Invia notifica basata su trigger
     */
    public static function trigger_notification($trigger_id, $post_id) {
        $trigger = self::get_trigger($trigger_id);

        if (!$trigger || !$trigger['enabled']) {
            return false;
        }

        // Prepara titolo e messaggio
        $title = self::parse_template($trigger['title_template'], $post_id);
        $message = self::parse_template($trigger['message_template'], $post_id);

        // Ottieni destinatari via segmentazione
        $user_ids = self::get_segmented_users($trigger['segmentation_rule_id'], $post_id);

        if (empty($user_ids)) {
            return false;
        }

        // Invia via OneSignal
        return self::send_notification(
            $user_ids,
            $title,
            $message,
            $post_id,
            $trigger['post_type'],
            $trigger['icon_emoji']
        );
    }

    /**
     * Parse template con placeholder
     * Placeholder: {{post_type}}, {{title}}, {{author}}, {{date}}, {{excerpt}}
     */
    private static function parse_template($template, $post_id) {
        $post = get_post($post_id);

        if (!$post) {
            return $template;
        }

        $post_type_obj = get_post_type_object($post->post_type);
        $post_type_label = $post_type_obj ? $post_type_obj->labels->singular_name : $post->post_type;

        $replacements = array(
            '{{post_type}}' => $post_type_label,
            '{{title}}' => $post->post_title,
            '{{author}}' => get_the_author_meta('display_name', $post->post_author),
            '{{date}}' => get_the_date('d/m/Y', $post_id),
            '{{excerpt}}' => wp_trim_words($post->post_excerpt ?: $post->post_content, 20)
        );

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Ottieni user IDs per segmentazione
     * Legge dal repeater notification_segmentazioni nella options page
     */
    private static function get_segmented_users($segmentation_title, $post_id = 0) {
        if (!$segmentation_title) {
            return array();
        }

        // Leggi tutte le segmentazioni dal repeater
        $segmentazioni = get_field('notification_segmentazioni', 'option');

        if (!$segmentazioni || !is_array($segmentazioni)) {
            return array();
        }

        // Cerca la segmentazione con il titolo matching
        $rule = null;
        foreach ($segmentazioni as $segmentazione) {
            if ($segmentazione['segmentation_title'] === $segmentation_title) {
                $rule = $segmentazione;
                break;
            }
        }

        if (!$rule) {
            return array();
        }

        $rule_type = $rule['segmentation_rule_type'];
        $user_ids = array();

        switch ($rule_type) {

            case 'all_subscribers':
                $users = get_users(array('role' => 'subscriber'));
                $user_ids = wp_list_pluck($users, 'ID');
                break;

            case 'by_profilo':
                $profilo_id = $rule['segmentation_profilo'] ?? null;
                if ($profilo_id) {
                    $users = get_users(array(
                        'meta_query' => array(
                            array(
                                'key' => 'profilo_professionale',
                                'value' => $profilo_id,
                                'compare' => 'LIKE'
                            )
                        )
                    ));
                    $user_ids = wp_list_pluck($users, 'ID');
                }
                break;

            case 'by_udo':
                $udo_id = $rule['segmentation_udo'] ?? null;
                if ($udo_id) {
                    $users = get_users(array(
                        'meta_query' => array(
                            array(
                                'key' => 'udo_riferimento',
                                'value' => $udo_id,
                                'compare' => 'LIKE'
                            )
                        )
                    ));
                    $user_ids = wp_list_pluck($users, 'ID');
                }
                break;

            case 'by_stato':
                $stato = $rule['segmentation_stato'] ?? null;
                if ($stato) {
                    $users = get_users(array(
                        'meta_query' => array(
                            array(
                                'key' => 'stato_utente',
                                'value' => $stato
                            )
                        )
                    ));
                    $user_ids = wp_list_pluck($users, 'ID');
                }
                break;

            case 'by_profilo_and_udo':
                $profilo_id = $rule['segmentation_profilo'] ?? null;
                $udo_id = $rule['segmentation_udo'] ?? null;

                if ($profilo_id && $udo_id) {
                    $users = get_users(array(
                        'meta_query' => array(
                            'relation' => 'AND',
                            array(
                                'key' => 'profilo_professionale',
                                'value' => $profilo_id,
                                'compare' => 'LIKE'
                            ),
                            array(
                                'key' => 'udo_riferimento',
                                'value' => $udo_id,
                                'compare' => 'LIKE'
                            )
                        )
                    ));
                    $user_ids = wp_list_pluck($users, 'ID');
                }
                break;

            case 'custom_query':
                $query_class = $rule['segmentation_custom_query_class'] ?? null;
                if ($query_class && class_exists($query_class)) {
                    if (method_exists($query_class, 'get_target_users')) {
                        $user_ids = $query_class::get_target_users($post_id);
                    }
                }
                break;
        }

        return array_unique(array_filter($user_ids));
    }

    /**
     * Invia notifica via OneSignal REST API
     */
    private static function send_notification($user_ids, $title, $message, $post_id, $post_type, $icon = '📬') {
        if (empty($user_ids)) {
            return false;
        }

        // Carica credenziali da ACF
        $app_id = get_field('meridiana_onesignal_app_id', 'option');
        $rest_key = get_field('meridiana_onesignal_rest_api_key', 'option');

        if (!$app_id || !$rest_key) {
            error_log('[Meridiana Notification] OneSignal non configurato');
            return false;
        }

        // Prepara i dati per OneSignal
        $payload = array(
            'app_id' => $app_id,
            'include_external_user_ids' => array_map('strval', $user_ids),
            'headings' => array('en' => $title),
            'contents' => array('en' => $message),
            'big_picture' => '',
            'ios_attachments' => array('image' => ''),
            'data' => array(
                'post_id' => $post_id,
                'post_type' => $post_type,
                'site_url' => home_url()
            )
        );

        // Invia a OneSignal
        $response = wp_remote_post('https://onesignal.com/api/v1/notifications', array(
            'method' => 'POST',
            'blocking' => false,
            'sslverify' => true,
            'timeout' => 5,
            'headers' => array(
                'Content-Type' => 'application/json; charset=utf-8',
                'Authorization' => 'Basic ' . $rest_key
            ),
            'body' => wp_json_encode($payload)
        ));

        if (is_wp_error($response)) {
            error_log('[Meridiana Notification] Errore invio: ' . $response->get_error_message());
            return false;
        }

        error_log('[Meridiana Notification] Notifica inviata a ' . count($user_ids) . ' utenti');
        return true;
    }

    /**
     * Invia notifica di test
     */
    public static function send_test_notification($trigger_id) {
        $trigger = self::get_trigger($trigger_id);

        if (!$trigger) {
            return false;
        }

        // Carica credenziali
        $app_id = get_field('meridiana_onesignal_app_id', 'option');
        $rest_key = get_field('meridiana_onesignal_rest_api_key', 'option');

        if (!$app_id || !$rest_key) {
            return false;
        }

        $current_user = wp_get_current_user();

        $payload = array(
            'app_id' => $app_id,
            'include_external_user_ids' => array((string) $current_user->ID),
            'headings' => array('en' => '🧪 Test: ' . $trigger['title_template']),
            'contents' => array('en' => 'Questo è un test della notifica'),
            'data' => array(
                'test' => true,
                'trigger_id' => $trigger_id
            )
        );

        $response = wp_remote_post('https://onesignal.com/api/v1/notifications', array(
            'method' => 'POST',
            'blocking' => false,
            'sslverify' => true,
            'timeout' => 5,
            'headers' => array(
                'Content-Type' => 'application/json; charset=utf-8',
                'Authorization' => 'Basic ' . $rest_key
            ),
            'body' => wp_json_encode($payload)
        ));

        return !is_wp_error($response);
    }
}

/**
 * Inizializza il sistema al caricamento
 */
add_action('wp_loaded', array('MeridianaNotificationSystem', 'init'));
