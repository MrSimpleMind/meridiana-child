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
 * Personalizza login page
 * Logo, colori, stile
 */
function meridiana_login_logo() {
    ?>
    <style type="text/css">
        #login h1 a, .login h1 a {
            background-image: url(<?php echo MERIDIANA_CHILD_URI; ?>/assets/images/logo.svg);
            height: 80px;
            width: 320px;
            background-size: contain;
            background-repeat: no-repeat;
            padding-bottom: 30px;
        }
        
        .login form {
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        
        .wp-core-ui .button-primary {
            background-color: #B91C1C;
            border-color: #991B1B;
            text-shadow: none;
            box-shadow: none;
        }
        
        .wp-core-ui .button-primary:hover,
        .wp-core-ui .button-primary:focus {
            background-color: #991B1B;
            border-color: #7F1D1D;
        }
        
        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #B91C1C;
            box-shadow: 0 0 0 1px #B91C1C;
        }
    </style>
    <?php
}
add_action('login_enqueue_scripts', 'meridiana_login_logo');

/**
 * Cambia URL logo login page
 */
function meridiana_login_logo_url() {
    return home_url();
}
add_filter('login_headerurl', 'meridiana_login_logo_url');

/**
 * Cambia title logo login page
 */
function meridiana_login_logo_url_title() {
    return 'Cooperativa La Meridiana - Piattaforma Formazione';
}
add_filter('login_headertext', 'meridiana_login_logo_url_title');

/**
 * Messaggio custom nella login page
 */
function meridiana_login_message($message) {
    // Solo se non ci sono già messaggi di errore
    if (empty($message)) {
        $message = '<p class="message">Accedi con le tue credenziali fornite dalla Cooperativa.</p>';
    }
    return $message;
}
add_filter('login_message', 'meridiana_login_message');

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
