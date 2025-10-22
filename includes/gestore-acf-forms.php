<?php
/**
 * Gestore ACF Forms - Dashboard Gestore
 * Rendering manuale dei field (senza acf_form per evitare errori)
 */

if (!defined('ABSPATH')) exit;

// ============================================
// FETCH FORM - AJAX HANDLER
// ============================================

add_action('wp_ajax_gestore_fetch_form', 'meridiana_ajax_fetch_form');

function meridiana_ajax_fetch_form() {
    // Security: Nonce check
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_rest')) {
        wp_send_json_error(['message' => 'Nonce non valido'], 403);
    }

    // Security: Capability check
    if (!current_user_can('manage_platform') && !current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permessi insufficienti'], 403);
    }

    // Validate: type e action
    $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
    $action = isset($_POST['action_form']) ? sanitize_text_field($_POST['action_form']) : 'new';
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : null;
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;

    if (!in_array($type, ['documenti', 'utenti'])) {
        wp_send_json_error(['message' => 'Tipo form non valido'], 400);
    }

    // Render form based on type
    $form_html = '';
    
    if ($type === 'documenti') {
        $form_html = meridiana_render_documento_form($action, $post_id);
    } elseif ($type === 'utenti') {
        $form_html = meridiana_render_user_form($action, $user_id);
    }

    if (!$form_html) {
        wp_send_json_error(['message' => 'Errore rendering form'], 500);
    }

    wp_send_json_success([
        'html' => $form_html,
        'type' => $type,
        'action' => $action,
    ]);
}

// ============================================
// RENDER DOCUMENTO FORM (Protocollo/Modulo)
// ============================================

function meridiana_render_documento_form($action = 'new', $post_id = null) {
    if (!function_exists('acf_form')) {
        return null;
    }

    // Determine post type from context
    // Se edit, leggi il type dal post esistente
    // Se new, default a 'protocollo' (l'utente può cambiarla nel form)
    $post_type = 'protocollo';
    
    if ($action === 'edit' && $post_id) {
        $post_type = get_post_type($post_id);
        if (!in_array($post_type, ['protocollo', 'modulo'])) {
            return null;
        }
    }

    // Determine field group based on post type
    $field_group = $post_type === 'protocollo' ? 'group_protocollo' : 'group_modulo';

    // Build ACF form args
    $form_args = [
        'id' => 'gestore_form_' . $post_type,
        'post_id' => $action === 'new' ? 'new_post' : $post_id,
        'field_groups' => [$field_group],
        'post_title' => true,
        'post_content' => false,
        'form' => true,
        'submit_value' => $action === 'new' ? 'Pubblica Documento' : 'Aggiorna Documento',
        'updated_message' => $action === 'new' ? 'Documento creato con successo' : 'Documento aggiornato con successo',
        'return' => add_query_arg(['action' => 'success'], $_SERVER['REQUEST_URI']),
    ];

    // Per new post, specifica il post type
    if ($action === 'new') {
        $form_args['new_post'] = [
            'post_type' => $post_type,
            'post_status' => 'publish',
        ];
    }

    // Render form
    ob_start();
    acf_form($form_args);
    $form_html = ob_get_clean();

    return $form_html;
}

// ============================================
// RENDER USER FORM
// ============================================

function meridiana_render_user_form($action = 'new', $user_id = null) {
    if (!function_exists('acf_form')) {
        return null;
    }

    // Validate user ID for edit
    if ($action === 'edit' && $user_id) {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return null;
        }
    }

    // Build ACF form args
    $form_args = [
        'id' => 'gestore_form_user',
        'post_id' => $action === 'new' ? 'user_new' : 'user_' . $user_id,
        'field_groups' => ['group_user_fields'],
        'form' => true,
        'submit_value' => $action === 'new' ? 'Crea Utente' : 'Aggiorna Utente',
        'updated_message' => $action === 'new' ? 'Utente creato con successo' : 'Utente aggiornato con successo',
        'return' => add_query_arg(['action' => 'success'], $_SERVER['REQUEST_URI']),
    ];

    // Render form
    ob_start();
    acf_form($form_args);
    $form_html = ob_get_clean();

    // Aggiungere manualmente i field standard WP (email, password, nome, cognome)
    // ACF non li include di default, devo renderli a parte
    $user_fields_html = meridiana_render_user_standard_fields($action, $user_id);

    return $user_fields_html . $form_html;
}

// ============================================
// RENDER USER STANDARD FIELDS (email, password, nome, cognome)
// ============================================

function meridiana_render_user_standard_fields($action = 'new', $user_id = null) {
    $user_data = [];
    
    if ($action === 'edit' && $user_id) {
        $user = get_user_by('id', $user_id);
        $user_data = [
            'user_login' => $user->user_login,
            'user_email' => $user->user_email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
        ];
    }

    ob_start();
    ?>
    <div class="acf-form-fields">
        <!-- Username (new) / Display only (edit) -->
        <div class="acf-field acf-field-text">
            <div class="acf-label">
                <label for="user_login">Username <span class="required">*</span></label>
            </div>
            <div class="acf-input">
                <?php if ($action === 'new'): ?>
                    <input type="text" id="user_login" name="user_login" value="" required />
                <?php else: ?>
                    <input type="text" id="user_login" name="user_login" value="<?php echo esc_attr($user_data['user_login']); ?>" readonly />
                <?php endif; ?>
            </div>
        </div>

        <!-- Email -->
        <div class="acf-field acf-field-email">
            <div class="acf-label">
                <label for="user_email">Email <span class="required">*</span></label>
            </div>
            <div class="acf-input">
                <input type="email" id="user_email" name="user_email" value="<?php echo esc_attr($user_data['user_email'] ?? ''); ?>" required />
            </div>
        </div>

        <!-- Password (new only) -->
        <?php if ($action === 'new'): ?>
        <div class="acf-field acf-field-password">
            <div class="acf-label">
                <label for="user_pass">Password <span class="required">*</span></label>
            </div>
            <div class="acf-input">
                <input type="password" id="user_pass" name="user_pass" value="" required />
            </div>
        </div>
        <?php endif; ?>

        <!-- First Name -->
        <div class="acf-field acf-field-text">
            <div class="acf-label">
                <label for="first_name">Nome</label>
            </div>
            <div class="acf-input">
                <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($user_data['first_name'] ?? ''); ?>" />
            </div>
        </div>

        <!-- Last Name -->
        <div class="acf-field acf-field-text">
            <div class="acf-label">
                <label for="last_name">Cognome</label>
            </div>
            <div class="acf-input">
                <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($user_data['last_name'] ?? ''); ?>" />
            </div>
        </div>
    </div>

    <hr />

    <?php
    return ob_get_clean();
}

// ============================================
// SAVE FORM - AJAX HANDLER (Documenti)
// ============================================

add_action('wp_ajax_gestore_save_documento', 'meridiana_ajax_save_documento');

function meridiana_ajax_save_documento() {
    // Security: Nonce check
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_rest')) {
        wp_send_json_error(['message' => 'Nonce non valido'], 403);
    }

    // Security: Capability check
    if (!current_user_can('manage_platform') && !current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permessi insufficienti'], 403);
    }

    // Validate: post_type
    $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';
    if (!in_array($post_type, ['protocollo', 'modulo'])) {
        wp_send_json_error(['message' => 'Tipo documento non valido'], 400);
    }

    // Validate: title
    $title = isset($_POST['post_title']) ? sanitize_text_field($_POST['post_title']) : '';
    if (empty($title)) {
        wp_send_json_error(['message' => 'Titolo obbligatorio'], 400);
    }

    // Determine if new or edit
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    
    if ($post_id === 0) {
        // NEW post
        $post_data = [
            'post_title' => $title,
            'post_type' => $post_type,
            'post_status' => 'publish',
            'post_content' => '',
        ];
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            wp_send_json_error(['message' => 'Errore creazione documento: ' . $post_id->get_error_message()], 500);
        }
    } else {
        // EDIT post
        $existing_post = get_post($post_id);
        if (!$existing_post || !in_array($existing_post->post_type, ['protocollo', 'modulo'])) {
            wp_send_json_error(['message' => 'Documento non trovato'], 404);
        }

        wp_update_post([
            'ID' => $post_id,
            'post_title' => $title,
        ]);
    }

    // Save ACF fields (if any file uploads, etc.)
    if (isset($_POST['acf'])) {
        foreach ($_POST['acf'] as $field_key => $field_value) {
            update_field($field_key, $field_value, $post_id);
        }
    }

    // Save taxonomies
    $post_type === 'protocollo' 
        ? meridiana_save_documento_taxonomies($post_id, 'protocollo')
        : meridiana_save_documento_taxonomies($post_id, 'modulo');

    wp_send_json_success([
        'message' => 'Documento salvato con successo',
        'post_id' => $post_id,
        'redirect' => add_query_arg(['action' => 'success'], $_SERVER['REQUEST_URI']),
    ]);
}

// ============================================
// SAVE DOCUMENTO TAXONOMIES
// ============================================

function meridiana_save_documento_taxonomies($post_id, $post_type = 'protocollo') {
    // unita-offerta (both protocollo and modulo)
    if (isset($_POST['tax_unita-offerta'])) {
        $terms = is_array($_POST['tax_unita-offerta']) 
            ? array_map('intval', $_POST['tax_unita-offerta'])
            : [intval($_POST['tax_unita-offerta'])];
        
        wp_set_post_terms($post_id, $terms, 'unita-offerta');
    }

    // profilo-professionale (both protocollo and modulo)
    if (isset($_POST['tax_profilo-professionale'])) {
        $terms = is_array($_POST['tax_profilo-professionale'])
            ? array_map('intval', $_POST['tax_profilo-professionale'])
            : [intval($_POST['tax_profilo-professionale'])];
        
        wp_set_post_terms($post_id, $terms, 'profilo-professionale');
    }

    // area-competenza (only modulo)
    if ($post_type === 'modulo' && isset($_POST['tax_area-competenza'])) {
        $terms = is_array($_POST['tax_area-competenza'])
            ? array_map('intval', $_POST['tax_area-competenza'])
            : [intval($_POST['tax_area-competenza'])];
        
        wp_set_post_terms($post_id, $terms, 'area-competenza');
    }
}

// ============================================
// SAVE FORM - AJAX HANDLER (Utenti)
// ============================================

add_action('wp_ajax_gestore_save_user', 'meridiana_ajax_save_user');

function meridiana_ajax_save_user() {
    // Security: Nonce check
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_rest')) {
        wp_send_json_error(['message' => 'Nonce non valido'], 403);
    }

    // Security: Capability check
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Solo admin può gestire utenti'], 403);
    }

    // Validate: user_email
    $user_email = isset($_POST['user_email']) ? sanitize_email($_POST['user_email']) : '';
    if (empty($user_email) || !is_email($user_email)) {
        wp_send_json_error(['message' => 'Email non valida'], 400);
    }

    // Determine if new or edit
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

    if ($user_id === 0) {
        // NEW user
        $user_login = isset($_POST['user_login']) ? sanitize_user($_POST['user_login']) : '';
        $user_pass = isset($_POST['user_pass']) ? $_POST['user_pass'] : '';

        if (empty($user_login)) {
            wp_send_json_error(['message' => 'Username obbligatorio'], 400);
        }

        if (empty($user_pass)) {
            wp_send_json_error(['message' => 'Password obbligatoria'], 400);
        }

        // Check username not exists
        if (username_exists($user_login)) {
            wp_send_json_error(['message' => 'Username già in uso'], 400);
        }

        // Create user
        $user_id = wp_create_user($user_login, $user_pass, $user_email);

        if (is_wp_error($user_id)) {
            wp_send_json_error(['message' => 'Errore creazione utente: ' . $user_id->get_error_message()], 500);
        }

        // Set default role
        $user = new WP_User($user_id);
        $user->set_role('subscriber');

    } else {
        // EDIT user - update email only
        $user_exists = get_user_by('id', $user_id);
        if (!$user_exists) {
            wp_send_json_error(['message' => 'Utente non trovato'], 404);
        }

        wp_update_user([
            'ID' => $user_id,
            'user_email' => $user_email,
        ]);
    }

    // Update standard fields (first_name, last_name)
    $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';

    if (!empty($first_name) || !empty($last_name)) {
        wp_update_user([
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
        ]);
    }

    // Save ACF custom user fields
    if (isset($_POST['acf'])) {
        foreach ($_POST['acf'] as $field_key => $field_value) {
            update_field($field_key, $field_value, 'user_' . $user_id);
        }
    }

    wp_send_json_success([
        'message' => 'Utente salvato con successo',
        'user_id' => $user_id,
        'redirect' => add_query_arg(['action' => 'success'], $_SERVER['REQUEST_URI']),
    ]);
}
