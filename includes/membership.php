<?php
/**
 * Membership Logic
 * 
 * Sistema di membership custom:
 * - Tutto il sito chiuso dietro login
 * - Unica pagina pubblica: login page
 * - No auto-registrazione
 * - Utenti creati solo da admin/gestore
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Forza login globale su tutto il sito
 * Reindirizza a wp-login.php se non autenticato
 */
function meridiana_force_login() {
    // Escludi pagine che devono restare pubbliche
    $public_pages = array(
        'wp-login.php',
        'wp-register.php',
        'wp-cron.php',
    );
    
    // Escludi AJAX e REST API
    if (defined('DOING_AJAX') && DOING_AJAX) {
        return;
    }
    
    if (defined('REST_REQUEST') && REST_REQUEST) {
        return;
    }
    
    // Ottieni pagina corrente
    $current_page = basename($_SERVER['PHP_SELF']);
    
    // Se non loggato e non in pagina pubblica, redirect a login
    if (!is_user_logged_in() && !in_array($current_page, $public_pages)) {
        // Salva URL richiesto per redirect dopo login
        $redirect_to = $_SERVER['REQUEST_URI'];
        
        // Redirect a login page
        wp_redirect(wp_login_url($redirect_to));
        exit;
    }
}
add_action('template_redirect', 'meridiana_force_login');

/**
 * Disabilita registrazione utenti pubblica
 * Solo admin/gestore possono creare utenti
 */
add_filter('pre_option_users_can_register', '__return_zero');

/**
 * Nascondi link "Registrati" dalla login page
 */
function meridiana_remove_register_link() {
    return null;
}
add_filter('register', 'meridiana_remove_register_link');

/**
 * Redirect dopo login
 * - Admin → backend
 * - Gestore Piattaforma → home frontend
 * - Utenti standard → home frontend
 */
function meridiana_login_redirect($redirect_to, $request, $user) {
    // Se errore, mantieni default
    if (is_wp_error($user)) {
        return $redirect_to;
    }
    
    // Admin → backend
    if (user_can($user, 'administrator')) {
        return admin_url();
    }
    
    // Tutti gli altri → home frontend
    return home_url();
}
add_filter('login_redirect', 'meridiana_login_redirect', 10, 3);

/**
 * Redirect dopo logout
 * Tutti → login page (non home, perché home è protetta)
 */
function meridiana_logout_redirect() {
    wp_redirect(wp_login_url());
    exit;
}
add_action('wp_logout', 'meridiana_logout_redirect');

/**
 * Redirect /wp-login.php to /login/ (custom split-layout page)
 * Mantiene i parametri GET come redirect_to
 */
function meridiana_redirect_wp_login_to_custom_page() {
    // Only on wp-login.php
    if (strpos($_SERVER['REQUEST_URI'], '/wp-login.php') === false) {
        return;
    }

    // Build the redirect URL with all query parameters
    $login_page_url = home_url('/login/');

    if (!empty($_SERVER['QUERY_STRING'])) {
        $login_page_url = home_url('/login/?' . $_SERVER['QUERY_STRING']);
    }

    // Redirect with 302
    wp_redirect($login_page_url, 302);
    exit;
}
add_action('login_init', 'meridiana_redirect_wp_login_to_custom_page', 1);

/**
 * Helper: Verifica se l'utente ha accesso al contenuto
 * 
 * @param int $user_id User ID
 * @param string $content_type Tipo di contenuto (protocollo, modulo, corso, etc)
 * @return bool
 */
function user_has_access($user_id, $content_type = '') {
    // Admin ha sempre accesso
    if (user_can($user_id, 'administrator')) {
        return true;
    }
    
    // Gestore ha sempre accesso
    if (is_gestore_piattaforma($user_id)) {
        return true;
    }
    
    // Verifica stato utente (solo utenti attivi)
    $stato_utente = get_user_meta($user_id, 'stato_utente', true);
    
    if ($stato_utente !== 'attivo' && $stato_utente !== '') {
        return false;
    }
    
    // Se arrivo qui, utente standard attivo ha accesso base
    return true;
}

/**
 * Nascondi contenuti per utenti sospesi/licenziati
 */
function meridiana_check_user_status() {
    if (!is_user_logged_in()) {
        return;
    }
    
    $user_id = get_current_user_id();
    $stato_utente = get_user_meta($user_id, 'stato_utente', true);
    
    // Se sospeso o licenziato, logout e messaggio
    if (in_array($stato_utente, array('sospeso', 'licenziato'))) {
        wp_logout();
        
        wp_redirect(add_query_arg(
            'account_disabled',
            '1',
            wp_login_url()
        ));
        exit;
    }
}
add_action('template_redirect', 'meridiana_check_user_status');

/**
 * Messaggio per account disabilitati
 */
function meridiana_disabled_account_message($message) {
    if (isset($_GET['account_disabled'])) {
        $message = '<p class="message error">Il tuo account è stato disabilitato. Contatta l\'amministratore per maggiori informazioni.</p>';
    }
    return $message;
}
add_filter('login_message', 'meridiana_disabled_account_message');
