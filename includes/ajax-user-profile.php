<?php
/**
 * AJAX Handler - User Profile Update
 * Gestisce l'aggiornamento del profilo utente da frontend
 */

// Hook AJAX per utenti loggati
add_action('wp_ajax_update_user_profile', 'handle_update_user_profile');

function handle_update_user_profile() {
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
    
    // Sanitizza input
    $first_name = sanitize_text_field($_POST['first_name'] ?? '');
    $last_name = sanitize_text_field($_POST['last_name'] ?? '');
    $user_phone = sanitize_text_field($_POST['user_phone'] ?? '');
    
    // Validazione campi obbligatori
    if (empty($first_name) || empty($last_name)) {
        wp_send_json_error('Nome e cognome sono obbligatori.');
        return;
    }
    
    // Aggiorna dati utente
    $user_data = array(
        'ID' => $user_id,
        'first_name' => $first_name,
        'last_name' => $last_name,
    );
    
    // Gestione cambio password
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (!empty($new_password) || !empty($confirm_password)) {
        // Verifica password attuale
        if (empty($current_password)) {
            wp_send_json_error('Inserisci la password attuale per cambiarla.');
            return;
        }
        
        if (!wp_check_password($current_password, $current_user->user_pass, $user_id)) {
            wp_send_json_error('Password attuale non corretta.');
            return;
        }
        
        // Verifica corrispondenza nuove password
        if ($new_password !== $confirm_password) {
            wp_send_json_error('Le nuove password non corrispondono.');
            return;
        }
        
        // Valida lunghezza password
        if (strlen($new_password) < 8) {
            wp_send_json_error('La password deve essere di almeno 8 caratteri.');
            return;
        }
        
        // Aggiungi nuova password all'update
        $user_data['user_pass'] = $new_password;
    }
    
    // Aggiorna utente
    $result = wp_update_user($user_data);
    
    if (is_wp_error($result)) {
        wp_send_json_error('Errore durante l\'aggiornamento: ' . $result->get_error_message());
        return;
    }
    
    // Aggiorna user meta (telefono)
    if (!empty($user_phone)) {
        update_user_meta($user_id, 'user_phone', $user_phone);
    }
    
    // Gestione avatar predefinito
    if (isset($_POST['predefined_avatar'])) {
        $avatar_key = sanitize_text_field($_POST['predefined_avatar']);
        $avatar_updated = meridiana_update_user_avatar($user_id, $avatar_key);
        
        if (!$avatar_updated) {
            wp_send_json_error('Profilo aggiornato ma errore nel salvataggio dell\'avatar.');
            return;
        }
    }
    
    wp_send_json_success('Profilo aggiornato con successo!');
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
