<?php
/**
 * User Roles & Capabilities
 * 
 * Definisce i ruoli custom per la piattaforma:
 * - Gestore Piattaforma (frontend-only admin)
 * - Utente Standard (subscriber con capabilities custom)
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Crea ruoli custom all'attivazione del tema
 * Viene chiamato da functions.php -> after_switch_theme
 */
function meridiana_create_custom_roles() {
    
    // =========================================================================
    // GESTORE PIATTAFORMA
    // =========================================================================
    // Può gestire contenuti via frontend, vedere analytics, gestire utenti
    // NO accesso al backend WordPress
    
    $gestore_capabilities = array(
        // Lettura base
        'read' => true,
        
        // Gestione contenuti frontend (via ACF forms)
        'edit_posts' => false,          // No backend
        'delete_posts' => false,        // No backend
        'publish_posts' => false,       // No backend
        
        // Custom capabilities per frontend
        'gestione_frontend' => true,    // Accesso form frontend
        'view_analytics' => true,       // Visualizza analytics
        'manage_platform_users' => true, // CRUD utenti
        
        // LearnDash
        'view_all_courses' => true,
    );
    
    // Rimuovi ruolo se esiste (per update)
    remove_role('gestore_piattaforma');
    
    // Crea ruolo
    add_role(
        'gestore_piattaforma',
        __('Gestore Piattaforma', 'meridiana-child'),
        $gestore_capabilities
    );
    
    // =========================================================================
    // UTENTE STANDARD
    // =========================================================================
    // Subscriber WordPress modificato con capabilities custom
    
    // Ottieni subscriber default
    $subscriber_role = get_role('subscriber');
    
    if ($subscriber_role) {
        // Aggiungi capabilities custom
        $subscriber_role->add_cap('view_documenti');
        $subscriber_role->add_cap('download_moduli');
        $subscriber_role->add_cap('view_organigramma');
        $subscriber_role->add_cap('view_convenzioni');
        $subscriber_role->add_cap('view_comunicazioni');
        
        // LearnDash
        $subscriber_role->add_cap('access_courses');
        $subscriber_role->add_cap('download_certificates');
    }
}

// Hook per creazione ruoli
add_action('after_switch_theme', 'meridiana_create_custom_roles');

/**
 * Previeni accesso backend per Gestore Piattaforma
 * Reindirizza al frontend
 */
function meridiana_restrict_admin_access() {
    // Non bloccare AJAX requests
    if (defined('DOING_AJAX') && DOING_AJAX) {
        return;
    }
    
    // Permetti solo a admin veri
    if (is_admin() && !current_user_can('administrator')) {
        wp_redirect(home_url());
        exit;
    }
}
add_action('admin_init', 'meridiana_restrict_admin_access');

/**
 * Nascondi admin bar per non-admin
 */
function meridiana_hide_admin_bar() {
    if (!current_user_can('administrator')) {
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'meridiana_hide_admin_bar');

/**
 * Helper: Verifica se utente è Gestore Piattaforma
 * 
 * @param int $user_id User ID (default: current user)
 * @return bool
 */
function is_gestore_piattaforma($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    $user = get_userdata($user_id);
    return $user && in_array('gestore_piattaforma', (array) $user->roles);
}

/**
 * Helper: Verifica se utente può gestire contenuti frontend
 * 
 * @return bool
 */
function can_manage_frontend() {
    return current_user_can('gestione_frontend') || current_user_can('administrator');
}

/**
 * Helper: Verifica se utente può vedere analytics
 * 
 * @return bool
 */
function can_view_analytics() {
    return current_user_can('view_analytics') || current_user_can('administrator');
}
