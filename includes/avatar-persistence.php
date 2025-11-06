<?php
/**
 * ========================================
 * AVATAR PERSISTENCE SYSTEM - CORE FUNCTIONS
 * ========================================
 * 
 * OBIETTIVO: Rendere persistente la scelta dell'avatar
 * - Salvataggio nel database via AJAX
 * - Validazione backend robusta
 * - Visualizzazione dinamica dell'avatar salvato
 * - Sistema debug integrato
 * 
 * DIPENDENZE: avatar-selector.php, ajax-user-profile.php
 */

if (!defined('ABSPATH')) exit;

/**
 * =====================================================
 * 1. SALVATAGGIO - Persistenza avatar con validazione
 * =====================================================
 */

/**
 * Salva l'avatar selezionato con validazione ROBUSTA
 * 
 * @param int $user_id ID dell'utente
 * @param string $avatar_filename Nome del file avatar
 * @return array Array con 'success' e 'message'
 */
function meridiana_save_user_avatar_robust($user_id, $avatar_filename) {
    // 1. Validazione input
    if (empty($avatar_filename) || !is_numeric($user_id)) {
        return array(
            'success' => false,
            'message' => 'Parametri non validi'
        );
    }
    
    // 2. Sanitizzazione: trim + stripslashes
    $avatar_filename = trim(stripslashes($avatar_filename));
    
    // 3. Regex: validazione nome file
    // Permessi: caratteri alfanumerici, spazi, trattini, underscore, punto, parentesi
    if (!preg_match('/^[\w\s\-\.()]+\.(jpg|jpeg|png|gif)$/i', $avatar_filename)) {
        error_log('[Avatar Persistence] ✗ Nome file non valido: ' . $avatar_filename);
        return array(
            'success' => false,
            'message' => 'Nome file non valido'
        );
    }
    
    // 4. Protezione Path Traversal
    $avatar_dir = MERIDIANA_CHILD_DIR . '/assets/images/avatar';
    $avatar_path = realpath($avatar_dir . '/' . $avatar_filename);
    $avatar_dir_real = realpath($avatar_dir);
    
    // Se realpath fallisce o il percorso esce dalla cartella -> blocca
    if (!$avatar_path || !$avatar_dir_real || strpos($avatar_path, $avatar_dir_real) !== 0) {
        error_log('[Avatar Persistence] ✗ Path Traversal Attack Detected: ' . $avatar_filename);
        return array(
            'success' => false,
            'message' => 'File non valido'
        );
    }
    
    // 5. Verifica fisica che il file esista
    if (!file_exists($avatar_path)) {
        error_log('[Avatar Persistence] ✗ File non trovato: ' . $avatar_path);
        return array(
            'success' => false,
            'message' => 'File avatar non trovato'
        );
    }
    
    // 6. Verifica che sia un'immagine valida usando wp_check_filetype()
    $filetype = wp_check_filetype($avatar_path);
    $mime = $filetype['type'];
    $allowed_mimes = array('image/jpeg', 'image/png', 'image/gif');

    if (!$mime || !in_array($mime, $allowed_mimes)) {
        error_log('[Avatar Persistence] ✗ MIME type non valido: ' . $mime);
        return array(
            'success' => false,
            'message' => 'Tipo di file non valido'
        );
    }
    
    // 7. Salva nel database user meta
    $result = update_user_meta((int)$user_id, 'selected_avatar', $avatar_filename);
    
    // Log successo
    error_log('[Avatar Persistence] ✓ Avatar salvato - User ID: ' . $user_id . ' | Filename: ' . $avatar_filename);
    
    return array(
        'success' => true,
        'message' => 'Avatar salvato con successo',
        'avatar_url' => MERIDIANA_CHILD_URI . '/assets/images/avatar/' . rawurlencode($avatar_filename),
        'filename' => $avatar_filename
    );
}

/**
 * Recupera l'avatar corrente dell'utente (PERSISTENTE)
 * 
 * @param int $user_id ID dell'utente
 * @return array|null Array con dati avatar o null se non salvato
 */
function meridiana_get_user_avatar_persistent($user_id) {
    if (!$user_id || !is_numeric($user_id)) {
        return null;
    }
    
    // Recupera il filename dal database
    $avatar_filename = get_user_meta((int)$user_id, 'selected_avatar', true);
    
    if (!$avatar_filename) {
        return null;
    }
    
    // Verifica che il file esista fisicamente
    $avatar_dir = MERIDIANA_CHILD_DIR . '/assets/images/avatar';
    $avatar_path = $avatar_dir . '/' . $avatar_filename;
    
    if (!file_exists($avatar_path)) {
        error_log('[Avatar Persistence] ✗ File fisico non trovato per user ' . $user_id . ': ' . $avatar_path);
        // Rimuovi il salvataggio corrotto dal database
        delete_user_meta((int)$user_id, 'selected_avatar');
        return null;
    }
    
    // Tutto ok: ritorna dati avatar
    return array(
        'filename' => $avatar_filename,
        'url' => MERIDIANA_CHILD_URI . '/assets/images/avatar/' . rawurlencode($avatar_filename),
        'label' => ucfirst(str_replace(array('_', '-'), ' ', pathinfo($avatar_filename, PATHINFO_FILENAME)))
    );
}


/**
 * =====================================================
 * 2. VISUALIZZAZIONE - Mostra avatar persistente
 * =====================================================
 */

/**
 * Mostra l'avatar HTML dell'utente (CON FALLBACK)
 * Versione robusta che verifica l'esistenza del file
 * 
 * @param int $user_id ID dell'utente (default: utente corrente)
 * @param string $size Classe grandezza: small (40px), medium (56px), large (80px)
 * @return string HTML dell'avatar
 */
function meridiana_display_user_avatar_persistent($user_id = null, $size = 'medium') {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return '<div class="user-avatar user-avatar--' . esc_attr($size) . '" title="Nessun utente"><i data-lucide="user"></i></div>';
    }
    
    // Recupera avatar persistente
    $avatar = meridiana_get_user_avatar_persistent($user_id);
    
    if ($avatar) {
        // Mappa dimensioni
        $size_px = $size === 'small' ? '40px' : ($size === 'large' ? '80px' : '56px');
        
        return '<img src="' . esc_url($avatar['url']) . '" alt="' . esc_attr($avatar['label']) . '" class="user-avatar user-avatar--' . esc_attr($size) . '" style="width: ' . esc_attr($size_px) . '; height: ' . esc_attr($size_px) . '; object-fit: cover; border-radius: 50%; display: block;" loading="lazy">';
    }
    
    // Fallback: icona Lucide predefinita
    return '<div class="user-avatar user-avatar--' . esc_attr($size) . '" title="Avatar non configurato"><i data-lucide="user"></i></div>';
}

/**
 * Alias per compatibilità (usa nome persistente)
 * DISABILITATO - Evita conflitto con avatar-selector.php
 */
// function meridiana_display_user_avatar($user_id = null, $size = 'medium') {
//     return meridiana_display_user_avatar_persistent($user_id, $size);
// }


/**
 * =====================================================
 * 3. AJAX HANDLERS - Salvataggio via AJAX
 * =====================================================
 */

/**
 * Endpoint AJAX dedicato per salvare avatar
 * Action: save_user_avatar
 * Metodo: POST
 * Parameters: avatar (filename), nonce
 */
function handle_save_user_avatar_ajax() {
    // 0. Rate limiting (max 30 richieste all'ora)
    $rate_limit_check = meridiana_check_ajax_rate_limit('save_user_avatar', 30, HOUR_IN_SECONDS);
    if (is_wp_error($rate_limit_check)) {
        wp_send_json_error($rate_limit_check->get_error_message());
        return;
    }

    // 1. Verifica nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'meridiana_avatar_save')) {
        wp_send_json_error('Nonce non valido. Ricarica la pagina e riprova.');
        return;
    }

    // 2. Verifica utente loggato
    if (!is_user_logged_in()) {
        wp_send_json_error('Devi essere loggato per cambiare avatar.');
        return;
    }
    
    // 3. Recupera user ID e avatar
    $user_id = get_current_user_id();
    $avatar_filename = $_POST['avatar'] ?? '';
    
    if (empty($avatar_filename)) {
        wp_send_json_error('Seleziona un avatar.');
        return;
    }
    
    // 4. Salva con validazione robusta
    $result = meridiana_save_user_avatar_robust($user_id, $avatar_filename);
    
    if ($result['success']) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error($result['message']);
    }
}
add_action('wp_ajax_save_user_avatar', 'handle_save_user_avatar_ajax');


/**
 * =====================================================
 * 4. DEBUG & DIAGNOSTICA
 * =====================================================
 */

/**
 * Debug page integrata: mostra stato persistenza avatar
 * Accesso: yoursite.com/?meridiana_avatar_debug=1 (se loggato)
 * 
 * Mostra:
 * - Avatar attuale (se salvato)
 * - Lista avatar disponibili
 * - Opzione ripristino (reset)
 */
function meridiana_avatar_debug_persistent() {
    if (!isset($_GET['meridiana_avatar_debug']) || !is_user_logged_in()) {
        return;
    }
    
    // Gestisci reset se richiesto
    if (isset($_GET['reset']) && $_GET['reset'] === '1') {
        if (wp_verify_nonce($_GET['_wpnonce'] ?? '', 'avatar_reset')) {
            $user_id = get_current_user_id();
            delete_user_meta($user_id, 'selected_avatar');
            echo '<div style="background: #4caf50; color: white; padding: 12px 20px; border-radius: 4px; margin: 20px; font-weight: bold;">✅ Avatar rimosso. Ricarica la pagina.</div>';
            return;
        }
    }
    
    $user_id = get_current_user_id();
    $avatar = meridiana_get_user_avatar_persistent($user_id);
    $available = meridiana_get_avatar_list();
    
    // HTML debug panel
    $debug_html = '<div style="background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%); padding: 20px; margin: 20px; border: 3px solid #333; border-radius: 12px; font-family: monospace; max-width: 700px;">';
    
    $debug_html .= '<h3 style="margin-top: 0; color: #333; font-size: 18px;">🔍 Avatar Persistence Debug Panel</h3>';
    
    // Informazioni base
    $debug_html .= '<p><strong>User ID:</strong> ' . esc_html($user_id) . '</p>';
    
    if ($avatar) {
        $debug_html .= '<p><strong>Status:</strong> <span style="background: #4caf50; color: white; padding: 2px 8px; border-radius: 3px;">✅ AVATAR PERSISTENTE</span></p>';
    } else {
        $debug_html .= '<p><strong>Status:</strong> <span style="background: #ff9800; color: white; padding: 2px 8px; border-radius: 3px;">⚠️ NESSUN AVATAR</span></p>';
    }
    
    // Avatar attuale
    if ($avatar) {
        $debug_html .= '<hr style="border: none; border-top: 2px solid #ccc; margin: 15px 0;">';
        $debug_html .= '<h4 style="margin-top: 0;">✓ Avatar Attuale</h4>';
        $debug_html .= '<p><strong>Filename:</strong> ' . esc_html($avatar['filename']) . '</p>';
        $debug_html .= '<p><strong>Label:</strong> ' . esc_html($avatar['label']) . '</p>';
        $debug_html .= '<p><strong>URL:</strong> <code style="background: #fff; padding: 4px 8px; border-radius: 3px; font-size: 11px;">' . esc_html($avatar['url']) . '</code></p>';
        $debug_html .= '<p><strong>Preview:</strong></p>';
        $debug_html .= '<img src="' . esc_url($avatar['url']) . '" alt="Avatar Preview" style="width: 120px; height: 120px; border-radius: 50%; border: 3px solid #333; object-fit: cover;">';
    } else {
        $debug_html .= '<hr style="border: none; border-top: 2px solid #ccc; margin: 15px 0;">';
        $debug_html .= '<p style="color: #d32f2f; background: #ffebee; padding: 10px; border-radius: 4px;"><strong>❌ Nessun avatar salvato nel database</strong></p>';
    }
    
    // Avatar disponibili
    $debug_html .= '<hr style="border: none; border-top: 2px solid #ccc; margin: 15px 0;">';
    $debug_html .= '<h4 style="margin-top: 0;">📋 Avatar Disponibili (' . count($available) . ' totali)</h4>';
    $debug_html .= '<div style="background: white; border: 1px solid #ccc; border-radius: 4px; max-height: 250px; overflow-y: auto;">';
    $debug_html .= '<ul style="list-style: none; padding: 0; margin: 0;">';
    
    foreach ($available as $avail) {
        $is_selected = $avatar && $avail['filename'] === $avatar['filename'];
        $mark = $is_selected ? ' <strong style="color: #4caf50;">✓ SELEZIONATO</strong>' : '';
        $bg = $is_selected ? 'background: #e8f5e9;' : '';
        $debug_html .= '<li style="padding: 8px 12px; border-bottom: 1px solid #eee; ' . $bg . '">' . esc_html($avail['filename']) . $mark . '</li>';
    }
    
    $debug_html .= '</ul>';
    $debug_html .= '</div>';
    
    // Azioni
    $debug_html .= '<hr style="border: none; border-top: 2px solid #ccc; margin: 15px 0;">';
    $debug_html .= '<p>';
    $reset_nonce = wp_create_nonce('avatar_reset');
    $debug_html .= '<a href="?meridiana_avatar_debug=1&reset=1&_wpnonce=' . esc_attr($reset_nonce) . '" style="background: #f44336; color: white; padding: 10px 16px; border-radius: 4px; text-decoration: none; display: inline-block; font-weight: bold; margin-right: 10px;">🔄 Ripristina (rimuovi avatar)</a>';
    $debug_html .= '<a href="?" style="background: #666; color: white; padding: 10px 16px; border-radius: 4px; text-decoration: none; display: inline-block; font-weight: bold;">✕ Chiudi Debug</a>';
    $debug_html .= '</p>';
    
    $debug_html .= '<hr style="border: none; border-top: 1px solid #ccc; margin: 15px 0;">';
    $debug_html .= '<p style="font-size: 12px; color: #666; margin: 0;"><em>Debug attivo. Per disabilitare rimuovi ?meridiana_avatar_debug=1 dall\'URL.</em></p>';
    
    $debug_html .= '</div>';
    
    echo $debug_html;
}
add_action('wp_footer', 'meridiana_avatar_debug_persistent');


/**
 * =====================================================
 * 5. HOOKS & INTEGRATION
 * =====================================================
 */

/**
 * Pulizia avatar quando un utente viene eliminato
 */
function cleanup_avatar_on_user_delete($user_id) {
    delete_user_meta($user_id, 'selected_avatar');
}
add_action('delete_user', 'cleanup_avatar_on_user_delete');

/**
 * Export avatar data su user export (GDPR)
 */
function export_user_avatar_data($user_data, $user) {
    $avatar = meridiana_get_user_avatar_persistent($user->ID);
    
    if ($avatar) {
        $user_data['avatar_filename'] = $avatar['filename'];
        $user_data['avatar_label'] = $avatar['label'];
    }
    
    return $user_data;
}
add_filter('wp_privacy_personal_data_exporters', function($exporters) {
    $exporters['meridiana-avatar'] = array(
        'exporter_friendly_name' => 'Avatar Profilo',
        'callback' => 'export_user_avatar_for_gdpr'
    );
    return $exporters;
});

function export_user_avatar_for_gdpr($email_address, $page = 1) {
    $user = get_user_by('email', $email_address);

    if (!$user) {
        return array(
            'data' => array(),
            'done' => true
        );
    }

    $avatar = meridiana_get_user_avatar_persistent($user->ID);

    $data_to_export = array();
    $item_data = array();

    if ($avatar) {
        $item_data[] = array(
            'name' => __('Avatar Filename', 'meridiana-child'),
            'value' => $avatar['filename']
        );
        $item_data[] = array(
            'name' => __('Avatar Label', 'meridiana-child'),
            'value' => $avatar['label']
        );

        $data_to_export[] = array(
            'group_id' => 'meridiana-avatar',
            'group_label' => __('Avatar Profilo', 'meridiana-child'),
            'item_id' => 'avatar-' . $user->ID,
            'data' => $item_data
        );
    }

    return array(
        'data' => $data_to_export,
        'done' => true
    );
}

/**
 * Cancella avatar data su user delete request (GDPR)
 */
add_filter('wp_privacy_personal_data_erasers', function($erasers) {
    $erasers['meridiana-avatar'] = array(
        'eraser_friendly_name' => 'Avatar Profilo',
        'callback' => 'erase_user_avatar_for_gdpr'
    );
    return $erasers;
});

function erase_user_avatar_for_gdpr($email_address, $page = 1) {
    $user = get_user_by('email', $email_address);
    
    if (!$user) {
        return array(
            'items_removed' => false,
            'items_retained' => false,
            'messages' => array(),
            'done' => true
        );
    }
    
    delete_user_meta($user->ID, 'selected_avatar');
    
    return array(
        'items_removed' => true,
        'items_retained' => false,
        'messages' => array('Avatar rimosso'),
        'done' => true
    );
}
