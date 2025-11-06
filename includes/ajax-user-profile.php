<?php
/**
 * AJAX Handler - User Profile Update
 * 
 * LOGICA DI SICUREZZA (NUOVA):
 * 1. Avatar ONLY: Salva SENZA password richiesta (azione leggera)
 * 2. Dati personali + password: Richiede password attuale (azione critica)
 */

// ============================================================================
// HANDLER 1: UPDATE AVATAR ONLY (SENZA PASSWORD)
// ============================================================================

add_action('wp_ajax_update_user_avatar_only', 'handle_update_user_avatar_only');

function handle_update_user_avatar_only() {
    // Verifica nonce
    if (!isset($_POST['avatar_nonce']) || !wp_verify_nonce($_POST['avatar_nonce'], 'update_user_profile')) {
        wp_send_json_error('Nonce non valido.');
        return;
    }
    
    // Verifica utente loggato
    if (!is_user_logged_in()) {
        wp_send_json_error('Devi essere loggato.');
        return;
    }
    
    $user_id = get_current_user_id();
    
    // Verifica che avatar sia fornito
    if (!isset($_POST['user_avatar']) || empty($_POST['user_avatar'])) {
        wp_send_json_error('Avatar non fornito.');
        return;
    }
    
    $avatar_file = sanitize_file_name($_POST['user_avatar']);
    
    // Salva avatar usando la funzione robusta
    $avatar_result = meridiana_save_user_avatar_robust($user_id, $avatar_file);
    
    if (!$avatar_result['success']) {
        wp_send_json_error('Avatar: ' . $avatar_result['message']);
        return;
    }
    
    error_log('[Avatar Only] ✓ Avatar salvato per user ' . $user_id . ': ' . $avatar_file);
    wp_send_json_success('Avatar salvato con successo!');
}

// ============================================================================
// HANDLER 2: UPDATE PROFILE (DATI PERSONALI + PASSWORD OBBLIGATORIA)
// ============================================================================

add_action('wp_ajax_update_user_profile', 'handle_update_user_profile');

function handle_update_user_profile() {
    // Rate limiting (max 20 richieste all'ora)
    $rate_limit_check = meridiana_check_ajax_rate_limit('update_user_profile', 20, HOUR_IN_SECONDS);
    if (is_wp_error($rate_limit_check)) {
        wp_send_json_error($rate_limit_check->get_error_message());
        return;
    }

    // Verifica nonce
    if (!isset($_POST['profile_nonce']) || !wp_verify_nonce($_POST['profile_nonce'], 'update_user_profile')) {
        wp_send_json_error('Nonce non valido. Ricarica la pagina e riprova.');
        return;
    }

    // Verifica utente loggato
    if (!is_user_logged_in()) {
        wp_send_json_error('Devi essere loggato per aggiornare il profilo.');
        return;
    }
    
    $user_id = get_current_user_id();
    $current_user = wp_get_current_user();
    
    // **STEP 1: VERIFICA PASSWORD ATTUALE (OBBLIGATORIA)** ⚠️
    $confirm_password_required = $_POST['confirm_password_required'] ?? '';
    
    if (empty($confirm_password_required)) {
        wp_send_json_error('⚠️ Per salvare le modifiche, devi inserire la tua password attuale.');
        return;
    }
    
    // Verifica che la password sia corretta
    if (!wp_check_password($confirm_password_required, $current_user->user_pass, $user_id)) {
        error_log('[Profile Update] ✗ Password non corretta per user ' . $user_id);
        wp_send_json_error('❌ Password attuale non corretta. Riprovare.');
        return;
    }
    
    // Password corretta - procedi con aggiornamento
    error_log('[Profile Update] ✓ Password verificata per user ' . $user_id);
    
    // **STEP 2: SANITIZZA E VALIDA INPUT**
    $first_name = sanitize_text_field($_POST['first_name'] ?? '');
    $last_name = sanitize_text_field($_POST['last_name'] ?? '');
    $user_phone = sanitize_text_field($_POST['user_phone'] ?? '');
    $codice_fiscale = strtoupper(sanitize_text_field($_POST['codice_fiscale'] ?? ''));
    
    // Validazione campi obbligatori
    if (empty($first_name) || empty($last_name)) {
        wp_send_json_error('Nome e cognome sono obbligatori.');
        return;
    }
    
    // Validazione Codice Fiscale (se fornito)
    if (!empty($codice_fiscale)) {
        if (!preg_match('/^[A-Z0-9]{16}$/i', $codice_fiscale)) {
            wp_send_json_error('❌ Codice Fiscale non valido. Deve contenere 16 caratteri alfanumerici.');
            return;
        }
    }
    
    // **STEP 3: AGGIORNA DATI UTENTE**
    $user_data = array(
        'ID' => $user_id,
        'first_name' => $first_name,
        'last_name' => $last_name,
    );
    
    // **STEP 4: GESTIONE CAMBIO PASSWORD**
    $new_password = $_POST['new_password'] ?? '';
    $confirm_new_password = $_POST['confirm_new_password'] ?? '';
    
    if (!empty($new_password) || !empty($confirm_new_password)) {
        // Verifica corrispondenza nuove password
        if ($new_password !== $confirm_new_password) {
            wp_send_json_error('❌ Le nuove password non corrispondono.');
            return;
        }
        
        // Valida lunghezza password
        if (strlen($new_password) < 8) {
            wp_send_json_error('❌ La password deve essere di almeno 8 caratteri.');
            return;
        }
        
        // Aggiungi nuova password all'update
        $user_data['user_pass'] = $new_password;
        error_log('[Profile Update] ✓ Password modificata per user ' . $user_id);
    }
    
    // Aggiorna utente WordPress
    $result = wp_update_user($user_data);
    
    if (is_wp_error($result)) {
        wp_send_json_error('Errore durante l\'aggiornamento: ' . $result->get_error_message());
        return;
    }
    
    // **STEP 5: AGGIORNA USER META**
    if (!empty($user_phone)) {
        update_user_meta($user_id, 'user_phone', $user_phone);
    }
    
    if (!empty($codice_fiscale)) {
        update_user_meta($user_id, 'codice_fiscale', $codice_fiscale);
        error_log('[Profile Update] ✓ Codice Fiscale aggiornato per user ' . $user_id . ': ' . $codice_fiscale);
    }
    
    error_log('[Profile Update] ✓ Profilo completamente aggiornato per user ' . $user_id);
    wp_send_json_success('✅ Profilo aggiornato con successo!');
}

/**
 * Handle avatar upload
 */
function handle_avatar_upload($user_id, $file) {
    // Verifica tipo file
    $allowed_types = array('image/jpeg', 'image/jpg', 'image/png');
    $file_type = $file['type'];
    
    if (!in_array($file_type, $allowed_types)) {
        return new WP_Error('invalid_type', 'Tipo di file non valido. Usa JPG o PNG.');
    }
    
    // Verifica dimensione (max 2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        return new WP_Error('file_too_large', 'Il file è troppo grande. Max 2MB.');
    }
    
    // Setup WordPress upload
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    
    // Upload file
    $upload_overrides = array('test_form' => false);
    $uploaded_file = wp_handle_upload($file, $upload_overrides);
    
    if (isset($uploaded_file['error'])) {
        return new WP_Error('upload_error', $uploaded_file['error']);
    }
    
    // Crea attachment
    $attachment = array(
        'post_mime_type' => $uploaded_file['type'],
        'post_title' => 'Avatar - User ' . $user_id,
        'post_content' => '',
        'post_status' => 'inherit'
    );
    
    $attach_id = wp_insert_attachment($attachment, $uploaded_file['file']);
    
    if (is_wp_error($attach_id)) {
        return $attach_id;
    }
    
    // Genera metadata e thumbnails
    $attach_data = wp_generate_attachment_metadata($attach_id, $uploaded_file['file']);
    wp_update_attachment_metadata($attach_id, $attach_data);
    
    // Associa avatar all'utente (usa simple local avatars o altro plugin se disponibile)
    // Per WordPress standard, salva l'ID come meta
    update_user_meta($user_id, 'custom_avatar', $attach_id);
    
    return $attach_id;
}

/**
 * Get custom avatar if exists
 * Usare questo per mostrare l'avatar custom invece di quello di Gravatar
 */
function get_custom_user_avatar($user_id, $size = 96) {
    $avatar_id = get_user_meta($user_id, 'custom_avatar', true);
    
    if ($avatar_id) {
        $avatar_url = wp_get_attachment_image_url($avatar_id, array($size, $size));
        if ($avatar_url) {
            return $avatar_url;
        }
    }
    
    // Fallback a Gravatar
    return get_avatar_url($user_id, array('size' => $size));
}

// Hook per usare avatar custom nel get_avatar
// TEMPORANEAMENTE DISABILITATO - CAUSA LOOP INFINITO
// add_filter('get_avatar_url', 'use_custom_avatar_url', 10, 3);

function use_custom_avatar_url($url, $id_or_email, $args) {
    // Ottieni user ID
    if (is_numeric($id_or_email)) {
        $user_id = (int) $id_or_email;
    } elseif (is_object($id_or_email) && isset($id_or_email->user_id)) {
        $user_id = (int) $id_or_email->user_id;
    } elseif (is_object($id_or_email) && isset($id_or_email->ID)) {
        $user_id = (int) $id_or_email->ID;
    } else {
        return $url;
    }
    
    // Cerca avatar custom
    $custom_avatar = get_custom_user_avatar($user_id, $args['size']);
    
    return $custom_avatar ? $custom_avatar : $url;
}
