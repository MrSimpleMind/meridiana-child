<?php
/**
 * Security Hardening
 * 
 * Misure di sicurezza aggiuntive per proteggere la piattaforma
 * Complementari a Defender Pro (WPmuDEV)
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rimuovi versione WordPress da header
 * Previene information disclosure
 */
remove_action('wp_head', 'wp_generator');
add_filter('the_generator', '__return_empty_string');

/**
 * Disabilita XML-RPC se non necessario
 * Riduce superficie di attacco
 */
add_filter('xmlrpc_enabled', '__return_false');

/**
 * Rimuovi RSD link da header
 */
remove_action('wp_head', 'rsd_link');

/**
 * Rimuovi Windows Live Writer manifest
 */
remove_action('wp_head', 'wlwmanifest_link');

/**
 * Rimuovi shortlink da header
 */
remove_action('wp_head', 'wp_shortlink_wp_head');

/**
 * Disabilita REST API per utenti non autenticati
 * Eccetto endpoint pubblici necessari
 */
function meridiana_restrict_rest_api($result) {
    if (!is_user_logged_in()) {
        return new WP_Error(
            'rest_unauthorized',
            __('Accesso REST API riservato ad utenti autenticati.', 'meridiana-child'),
            array('status' => 401)
        );
    }
    
    return $result;
}
// add_filter('rest_authentication_errors', 'meridiana_restrict_rest_api');
// NOTA: Commentato per permettere API pubbliche se necessarie

/**
 * Security Headers
 * Aggiungi header di sicurezza HTTP
 */
function meridiana_security_headers() {
    // X-Content-Type-Options
    header('X-Content-Type-Options: nosniff');
    
    // X-Frame-Options (previene clickjacking)
    header('X-Frame-Options: SAMEORIGIN');
    
    // X-XSS-Protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy (base - customizza secondo necessità)
    // header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline';");
    
    // Strict-Transport-Security (HSTS) - Solo se HTTPS
    if (is_ssl()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}
add_action('send_headers', 'meridiana_security_headers');

/**
 * Previeni enumerazione utenti via REST API
 */
function meridiana_disable_user_endpoints($endpoints) {
    if (isset($endpoints['/wp/v2/users'])) {
        unset($endpoints['/wp/v2/users']);
    }
    
    if (isset($endpoints['/wp/v2/users/(?P<id>[\d]+)'])) {
        unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    }
    
    return $endpoints;
}
add_filter('rest_endpoints', 'meridiana_disable_user_endpoints');

/**
 * Previeni enumerazione utenti via URL
 * Blocca ?author=1 queries
 */
function meridiana_prevent_user_enumeration() {
    if (is_admin()) {
        return;
    }
    
    // Blocca query author
    if (isset($_REQUEST['author']) && !empty($_REQUEST['author'])) {
        wp_die('Forbidden', 'Forbidden', array('response' => 403));
    }
}
add_action('init', 'meridiana_prevent_user_enumeration');

/**
 * Sanitizza tutti gli input POST
 * Layer di sicurezza aggiuntivo
 */
function meridiana_sanitize_post_data() {
    if (!empty($_POST)) {
        foreach ($_POST as $key => $value) {
            if (is_array($value)) {
                $_POST[$key] = array_map('sanitize_text_field', $value);
            } else {
                $_POST[$key] = sanitize_text_field($value);
            }
        }
    }
}
// add_action('init', 'meridiana_sanitize_post_data', 1);
// NOTA: Commentato perché potrebbe interferire con editor WYSIWYG
// Usare sanitize_text_field() manualmente su singoli input

/**
 * Limita tentativi di login
 * Complementare a Defender Pro
 */
function meridiana_limit_login_attempts() {
    $max_attempts = 5;
    $lockout_duration = 15 * 60; // 15 minuti
    
    $ip = $_SERVER['REMOTE_ADDR'];
    $transient_key = 'login_attempts_' . md5($ip);
    
    $attempts = get_transient($transient_key);
    
    if ($attempts !== false && $attempts >= $max_attempts) {
        $remaining = get_option('_transient_timeout_' . $transient_key) - time();
        $minutes = ceil($remaining / 60);
        
        wp_die(
            sprintf(
                __('Troppi tentativi di login falliti. Riprova tra %d minuti.', 'meridiana-child'),
                $minutes
            ),
            __('Accesso bloccato', 'meridiana-child'),
            array('response' => 403)
        );
    }
}
add_action('wp_login_failed', 'meridiana_track_failed_login');

function meridiana_track_failed_login($username) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $transient_key = 'login_attempts_' . md5($ip);
    $lockout_duration = 15 * 60;
    
    $attempts = get_transient($transient_key);
    
    if ($attempts === false) {
        $attempts = 1;
    } else {
        $attempts++;
    }
    
    set_transient($transient_key, $attempts, $lockout_duration);
}

add_action('wp_login', 'meridiana_reset_login_attempts', 10, 2);

function meridiana_reset_login_attempts($username, $user) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $transient_key = 'login_attempts_' . md5($ip);
    
    delete_transient($transient_key);
}

/**
 * Disabilita file editing da dashboard
 * Previene modifiche al codice da backend
 */
if (!defined('DISALLOW_FILE_EDIT')) {
    define('DISALLOW_FILE_EDIT', true);
}

/**
 * Forza HTTPS su admin e login
 * WPmuDEV già gestisce SSL, ma confermiamo
 */
if (!defined('FORCE_SSL_ADMIN')) {
    // define('FORCE_SSL_ADMIN', true);
    // NOTA: Abilitare solo se HTTPS configurato
}

/**
 * Valida tipi file upload
 * Previene upload file pericolosi
 */
function meridiana_validate_file_upload($file) {
    $allowed_types = array(
        'pdf',
        'jpg',
        'jpeg',
        'png',
        'gif',
        'doc',
        'docx',
        'xls',
        'xlsx',
    );
    
    $file_type = wp_check_filetype($file['name']);
    $ext = $file_type['ext'];
    
    if (!in_array(strtolower($ext), $allowed_types)) {
        $file['error'] = sprintf(
            __('Tipo di file non permesso. Tipi consentiti: %s', 'meridiana-child'),
            implode(', ', $allowed_types)
        );
    }
    
    return $file;
}
add_filter('wp_handle_upload_prefilter', 'meridiana_validate_file_upload');

/**
 * Nascondi errori di login dettagliati
 * Previene information disclosure
 */
function meridiana_generic_login_error() {
    return __('Credenziali non valide. Riprova.', 'meridiana-child');
}
add_filter('login_errors', 'meridiana_generic_login_error');

/**
 * Disabilita plugin/theme editor per non-admin
 */
function meridiana_disable_editor_for_non_admin() {
    if (!current_user_can('administrator')) {
        define('DISALLOW_FILE_MODS', true);
    }
}
add_action('init', 'meridiana_disable_editor_for_non_admin');

/**
 * Log attività sospette (integra con Defender Pro logs)
 * 
 * @param string $event Tipo evento
 * @param mixed $data Dati evento
 */
function meridiana_log_security_event($event, $data = array()) {
    if (!WP_DEBUG) {
        return;
    }
    
    $log_entry = array(
        'timestamp' => current_time('mysql'),
        'event' => $event,
        'user_id' => get_current_user_id(),
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'data' => $data,
    );
    
    // Log su file
    meridiana_log($log_entry, 'SECURITY EVENT');
    
    // Opzionale: invia alert email per eventi critici
    // if (in_array($event, array('brute_force', 'unauthorized_access'))) {
    //     wp_mail(get_option('admin_email'), 'Security Alert', print_r($log_entry, true));
    // }
}

/**
 * Previeni hotlinking immagini (opzionale)
 * Se vuoi proteggere immagini da siti esterni
 */
/*
function meridiana_prevent_hotlinking() {
    if (!is_admin()) {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $site_url = home_url();
        
        if (!empty($referer) && strpos($referer, $site_url) === false) {
            // Hotlink detected
            status_header(403);
            die('Hotlinking not allowed');
        }
    }
}
add_action('init', 'meridiana_prevent_hotlinking');
*/

/**
 * Rate limiting su REST API custom endpoints
 * Previene abuso API
 */
function meridiana_rate_limit_rest_api($result, $server, $request) {
    // Solo su endpoint custom
    $route = $request->get_route();
    
    if (strpos($route, '/piattaforma/v1/') === false) {
        return $result;
    }
    
    $user_id = get_current_user_id();
    $ip = $_SERVER['REMOTE_ADDR'];
    $key = 'rest_rate_limit_' . ($user_id ?: md5($ip));
    
    $max_requests = 100; // Richieste per ora
    $period = HOUR_IN_SECONDS;
    
    $count = get_transient($key);
    
    if ($count === false) {
        set_transient($key, 1, $period);
    } else {
        if ($count >= $max_requests) {
            return new WP_Error(
                'rest_rate_limit',
                __('Troppi richieste. Riprova più tardi.', 'meridiana-child'),
                array('status' => 429)
            );
        }
        
        set_transient($key, $count + 1, $period);
    }
    
    return $result;
}
// add_filter('rest_pre_dispatch', 'meridiana_rate_limit_rest_api', 10, 3);
// NOTA: Abilitare se noti abusi API

/**
 * Verifica permessi prima di modifiche critiche
 * Helper per verificare capabilities in operazioni sensibili
 * 
 * @param string $capability Capability richiesta
 * @param string $error_message Messaggio errore
 * @return bool|WP_Error
 */
function verify_user_permission($capability, $error_message = '') {
    if (!current_user_can($capability)) {
        if (empty($error_message)) {
            $error_message = __('Non hai i permessi per questa operazione.', 'meridiana-child');
        }
        
        return new WP_Error('insufficient_permissions', $error_message, array('status' => 403));
    }
    
    return true;
}

/**
 * Previeni SQL injection su query custom
 * Reminder: usare sempre $wpdb->prepare()
 */
// Esempio di query sicura:
// global $wpdb;
// $results = $wpdb->get_results($wpdb->prepare(
//     "SELECT * FROM {$wpdb->prefix}custom_table WHERE user_id = %d AND status = %s",
//     $user_id,
//     $status
// ));
