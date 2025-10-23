<?php
/**
 * AJAX Handlers - Dashboard Gestore
 * Delete documenti, utenti, reset password, ecc.
 */

if (!defined('ABSPATH')) exit;

// ============================================
// LOAD FORM (Wrapper per rendering form ACF)
// ============================================

add_action('wp_ajax_gestore_load_form', 'meridiana_ajax_load_form');

function meridiana_ajax_load_form() {
    // Security: Nonce check
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_rest')) {
        wp_send_json_error(['message' => 'Nonce non valido'], 403);
    }

    // Security: Capability check
    if (!current_user_can('manage_platform') && !current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permessi insufficienti'], 403);
    }

    // Validate: post_type / action_type / post_id
    $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';
    $action_type = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : 'new';
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

    if (!in_array($post_type, ['documenti', 'utenti', 'comunicazioni'])) {
        wp_send_json_error(['message' => 'Tipo form non valido'], 400);
    }

    // Load functions if not already loaded
    if (!function_exists('meridiana_render_documento_form')) {
        require_once MERIDIANA_CHILD_DIR . '/includes/gestore-acf-forms.php';
    }

    // Render form
    $form_html = '';
    $document_cpt = isset($_POST['cpt']) ? sanitize_text_field($_POST['cpt']) : '';

    if ($post_type === 'documenti') {
        if (!in_array($document_cpt, ['protocollo', 'modulo'], true)) {
            $document_cpt = 'protocollo';
        }
        $form_html = meridiana_render_documento_form($action_type, $post_id > 0 ? $post_id : null, $document_cpt);
    } elseif ($post_type === 'utenti') {
        $form_html = meridiana_render_user_form($action_type, $post_id > 0 ? $post_id : null);
    } elseif ($post_type === 'comunicazioni') {
        $form_html = meridiana_render_comunicazione_form($action_type, $post_id > 0 ? $post_id : null);
    }

    if (!$form_html) {
        wp_send_json_error(['message' => 'Errore caricamento form'], 500);
    }

    wp_send_json_success([
        'form_html' => $form_html,
        'post_type' => $post_type,
        'action_type' => $action_type,
        'document_cpt' => $document_cpt,
    ]);
}

// ============================================
// SAVE FORM (Wrapper dispatcher)
// ============================================

add_action('wp_ajax_gestore_save_form', 'meridiana_ajax_save_form');

function meridiana_ajax_save_form() {
    // Security: Nonce check
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_rest')) {
        wp_send_json_error(['message' => 'Nonce non valido'], 403);
    }

    // Security: Capability check
    if (!current_user_can('manage_platform') && !current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permessi insufficienti'], 403);
    }

    $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';

    // Load functions if not already loaded
    if (!function_exists('meridiana_ajax_save_documento')) {
        require_once MERIDIANA_CHILD_DIR . '/includes/gestore-acf-forms.php';
    }

    if ($post_type === 'documenti') {
        meridiana_ajax_save_documento();
    } elseif ($post_type === 'utenti') {
        meridiana_ajax_save_user();
    } elseif ($post_type === 'comunicazioni') {
        meridiana_ajax_save_comunicazione();
    } else {
        wp_send_json_error(['message' => 'Tipo form non valido'], 400);
    }
}

// ============================================
// DELETE DOCUMENTO (Protocollo/Modulo)
// ============================================

add_action('wp_ajax_gestore_delete_documento', 'meridiana_ajax_delete_documento');
add_action('wp_ajax_gestore_delete_comunicazione', 'meridiana_ajax_delete_comunicazione');



function meridiana_ajax_delete_comunicazione() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_rest')) {
        wp_send_json_error(['message' => __('Nonce non valido', 'meridiana-child')], 403);
    }

    if (!current_user_can('manage_platform') && !current_user_can('delete_posts')) {
        wp_send_json_error(['message' => __('Permessi insufficienti', 'meridiana-child')], 403);
    }

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    if (!$post_id) {
        wp_send_json_error(['message' => __('ID comunicazione non valido', 'meridiana-child')], 400);
    }

    $post_type = get_post_type($post_id);
    if ($post_type !== 'post') {
        wp_send_json_error(['message' => __('Tipo contenuto non consentito', 'meridiana-child')], 400);
    }

    $deleted = wp_delete_post($post_id, true);

    if (!$deleted) {
        wp_send_json_error(['message' => __('Errore durante l\'eliminazione', 'meridiana-child')], 500);
    }

    wp_send_json_success([
        'message' => __('Comunicazione eliminata con successo', 'meridiana-child'),
        'post_id' => $post_id,
    ]);
}





function meridiana_ajax_delete_documento() {
    // Security: Nonce check
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_rest')) {
        wp_send_json_error(['message' => 'Nonce non valido'], 403);
    }

    // Security: Capability check
    if (!current_user_can('manage_platform') && !current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permessi insufficienti'], 403);
    }

    // Validate: Post ID
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    if (!$post_id) {
        wp_send_json_error(['message' => 'ID documento non valido'], 400);
    }

    // Validate: Post type
    $post_type = get_post_type($post_id);
    if (!in_array($post_type, ['protocollo', 'modulo'])) {
        wp_send_json_error(['message' => 'Tipo documento non consentito'], 400);
    }

    // Archive old PDF if exists (opzionale in questa fase)
    // TODO: Implementare archiving in fase successiva

    // Hard delete
    $deleted = wp_delete_post($post_id, true);

    if (!$deleted) {
        wp_send_json_error(['message' => 'Errore durante l\'eliminazione'], 500);
    }

    wp_send_json_success([
        'message' => 'Documento eliminato con successo',
        'post_id' => $post_id,
    ]);
}

// ============================================
// DELETE UTENTE
// ============================================

add_action('wp_ajax_gestore_delete_user', 'meridiana_ajax_delete_user');

function meridiana_ajax_delete_user() {
    // Security: Nonce check
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_rest')) {
        wp_send_json_error(['message' => 'Nonce non valido'], 403);
    }

    // Security: Capability check
    if (!current_user_can('delete_users')) {
        wp_send_json_error(['message' => 'Permessi insufficienti'], 403);
    }

    // Validate: User ID
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    if (!$user_id) {
        wp_send_json_error(['message' => 'ID utente non valido'], 400);
    }

    // Safety: Non permettere di eliminare se stesso
    if ($user_id === get_current_user_id()) {
        wp_send_json_error(['message' => 'Non puoi eliminare il tuo account'], 400);
    }

    // Hard delete user
    $deleted = wp_delete_user($user_id);

    if (!$deleted) {
        wp_send_json_error(['message' => 'Errore durante l\'eliminazione dell\'utente'], 500);
    }

    wp_send_json_success([
        'message' => 'Utente eliminato con successo',
        'user_id' => $user_id,
    ]);
}

// ============================================
// RESET PASSWORD UTENTE
// ============================================

add_action('wp_ajax_gestore_reset_password', 'meridiana_ajax_reset_password');

function meridiana_ajax_reset_password() {
    // Security: Nonce check
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_rest')) {
        wp_send_json_error(['message' => 'Nonce non valido'], 403);
    }

    // Security: Capability check
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Solo admin può resettare password'], 403);
    }

    // Validate: User ID
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    if (!$user_id) {
        wp_send_json_error(['message' => 'ID utente non valido'], 400);
    }

    $user = get_user_by('id', $user_id);
    if (!$user) {
        wp_send_json_error(['message' => 'Utente non trovato'], 404);
    }

    // Generate reset token e link
    $reset_key = wp_generate_password(20, false);
    update_user_meta($user_id, '_reset_password_key', $reset_key);
    update_user_meta($user_id, '_reset_password_time', time());

    $reset_link = add_query_arg([
        'action' => 'rp',
        'key' => $reset_key,
        'login' => rawurlencode($user->user_login),
    ], wp_login_url());

    // Invia email (via Brevo o wp_mail)
    $to = $user->user_email;
    $subject = 'Reimposta password - ' . get_bloginfo('name');
    $message = sprintf(
        "Ciao %s,\n\nLa tua password è stata resettata dall'amministratore.\n\nClicca qui per impostare una nuova password:\n%s\n\nLink valido per 24 ore.",
        $user->first_name ?: $user->user_login,
        $reset_link
    );

    $sent = wp_mail($to, $subject, $message);

    if (!$sent) {
        wp_send_json_error(['message' => 'Errore invio email'], 500);
    }

    wp_send_json_success([
        'message' => 'Email di reset inviata a ' . $to,
        'user_id' => $user_id,
    ]);
}

