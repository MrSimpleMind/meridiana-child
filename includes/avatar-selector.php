<?php
/**
 * Avatar Selector System
 * Gestisce la selezione di avatar predefiniti dagli utenti
 * Supporta file con spazi nei nomi (vengono URL-encoded)
 */

if (!defined('ABSPATH')) exit;

/**
 * Mostra l'avatar HTML dell'utente (selezionato o predefinito)
 * 
 * @param int $user_id ID dell'utente
 * @param string $size Classe di grandezza: small, medium, large
 * @return string HTML dell'avatar
 */
function meridiana_display_user_avatar($user_id = null, $size = 'medium') {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return '<div class="user-avatar user-avatar--' . $size . '"><i data-lucide="user"></i></div>';
    }
    
    // Cerca l'avatar selezionato nel database
    $selected_avatar = get_user_meta($user_id, 'selected_avatar', true);
    
    // DEBUG: Log del valore recuperato
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[Avatar Debug] User ' . $user_id . ': selected_avatar = ' . var_export($selected_avatar, true));
    }
    
    if ($selected_avatar) {
        // Verifica che il file esista
        $avatar_dir = MERIDIANA_CHILD_DIR . '/assets/images/avatar';
        $avatar_path = $avatar_dir . '/' . $selected_avatar;
        
        if (file_exists($avatar_path)) {
            // Avatar selezionato dall'utente (filename reale)
            // Usa rawurlencode per gestire spazi e caratteri speciali nell'URL
            $avatar_url = MERIDIANA_CHILD_URI . '/assets/images/avatar/' . rawurlencode($selected_avatar);
            $size_class = $size === 'small' ? '40px' : ($size === 'large' ? '80px' : '56px');
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[Avatar Debug] ✓ File trovato, URL: ' . $avatar_url);
            }
            
            return '<img src="' . esc_url($avatar_url) . '" alt="Avatar" class="user-avatar user-avatar--' . esc_attr($size) . '" style="width: ' . $size_class . '; height: ' . $size_class . '; object-fit: cover; border-radius: 50%; display: block;">';
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[Avatar Debug] ✗ File non trovato: ' . $avatar_path);
            }
        }
    }
    
    // Fallback: icona predefinita se non ha avatar
    return '<div class="user-avatar user-avatar--' . $size . '"><i data-lucide="user"></i></div>';
}

/**
 * DEBUG: Test function - Mostra lo stato dell'avatar salvato
 * Accedi a: yoursite.com/?meridiana_avatar_debug=1 (se loggato)
 */
function meridiana_avatar_debug_test() {
    if (!isset($_GET['meridiana_avatar_debug']) || !is_user_logged_in()) {
        return;
    }
    
    $user_id = get_current_user_id();
    $selected_avatar = get_user_meta($user_id, 'selected_avatar', true);
    $avatar_dir = MERIDIANA_CHILD_DIR . '/assets/images/avatar';
    
    echo '<div style="background: #f0f0f0; padding: 20px; margin: 20px; border: 2px solid #333; border-radius: 8px; font-family: monospace;">';
    echo '<h3 style="margin-top: 0;">🔍 Avatar Debug Info</h3>';
    echo '<p><strong>User ID:</strong> ' . esc_html($user_id) . '</p>';
    echo '<p><strong>Selected Avatar (DB):</strong> ' . esc_html($selected_avatar ?: 'NONE') . '</p>';
    
    if ($selected_avatar) {
        $avatar_path = $avatar_dir . '/' . $selected_avatar;
        echo '<p><strong>File Path:</strong> ' . esc_html($avatar_path) . '</p>';
        echo '<p><strong>File Exists:</strong> ' . (file_exists($avatar_path) ? '✓ YES' : '✗ NO') . '</p>';
        
        if (file_exists($avatar_path)) {
            $url = MERIDIANA_CHILD_URI . '/assets/images/avatar/' . rawurlencode($selected_avatar);
            echo '<p><strong>URL:</strong> ' . esc_html($url) . '</p>';
            echo '<p><strong>Preview:</strong></p>';
            echo '<img src="' . esc_url($url) . '" alt="Avatar" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid #333;">';
        }
    } else {
        echo '<p style="color: #d32f2f;"><strong>❌ Nessun avatar selezionato</strong></p>';
    }
    
    echo '<p style="margin-bottom: 0;"><strong>Available Avatars:</strong></p>';
    echo '<ul style="max-height: 300px; overflow-y: auto;">';
    if (is_dir($avatar_dir)) {
        $files = scandir($avatar_dir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && preg_match('/\.(jpg|jpeg|png|gif)$/i', $file)) {
                $is_selected = ($file === $selected_avatar) ? ' ✓' : '';
                echo '<li>' . esc_html($file) . $is_selected . '</li>';
            }
        }
    }
    echo '</ul>';
    echo '</div>';
}
add_action('wp_footer', 'meridiana_avatar_debug_test');

/**
 * Ottiene la lista di avatar disponibili dalla cartella assets/images/avatar/
 * 
 * @return array Array di avatar con 'name' e 'url'
 */
function meridiana_get_avatar_list() {
    $avatar_dir = MERIDIANA_CHILD_DIR . '/assets/images/avatar';
    $avatars = array();
    
    if (!is_dir($avatar_dir)) {
        return $avatars;
    }
    
    $files = scandir($avatar_dir);
    
    foreach ($files as $file) {
        // Salta . e .. e file non immagine
        if ($file === '.' || $file === '..' || !preg_match('/\.(jpg|jpeg|png|gif)$/i', $file)) {
            continue;
        }
        
        $basename = pathinfo($file, PATHINFO_FILENAME);
        $label = ucfirst(str_replace(array('_', '-'), ' ', $basename));
        
        $avatars[] = array(
            'filename' => $file,  // Nome file reale con estensione
            'url' => MERIDIANA_CHILD_URI . '/assets/images/avatar/' . rawurlencode($file),  // URL safe
            'label' => $label
        );
    }
    
    // Ordina alfabeticamente per label
    usort($avatars, function($a, $b) {
        return strcmp($a['label'], $b['label']);
    });
    
    return $avatars;
}

/**
 * Ottiene l'avatar selezionato dall'utente
 * 
 * @param int $user_id ID dell'utente
 * @return array|false Array con 'url', 'filename' e 'label' oppure false
 */
function meridiana_get_user_selected_avatar($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return false;
    }
    
    $avatar_filename = get_user_meta($user_id, 'selected_avatar', true);
    
    if (!$avatar_filename) {
        return false;
    }
    
    $basename = pathinfo($avatar_filename, PATHINFO_FILENAME);
    $label = ucfirst(str_replace(array('_', '-'), ' ', $basename));
    $avatar_url = MERIDIANA_CHILD_URI . '/assets/images/avatar/' . rawurlencode($avatar_filename);
    
    return array(
        'filename' => $avatar_filename,
        'url' => $avatar_url,
        'label' => $label
    );
}

/**
 * Salva l'avatar selezionato per l'utente
 * 
 * @param int $user_id ID dell'utente
 * @param string $avatar_filename Nome del file avatar
 * @return bool True se salvato correttamente
 */
function meridiana_save_user_avatar($user_id, $avatar_filename) {
    if (empty($avatar_filename)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[Avatar Debug] Tentativo di salvare avatar vuoto');
        }
        return false;
    }
    
    // Verifica che il file esista prima di salvare
    $avatar_dir = MERIDIANA_CHILD_DIR . '/assets/images/avatar';
    $avatar_path = $avatar_dir . '/' . $avatar_filename;
    
    if (!file_exists($avatar_path)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[Avatar Debug] File non trovato: ' . $avatar_path);
        }
        return false;
    }
    
    // Salva il filename nel database (anche con spazi)
    $result = update_user_meta($user_id, 'selected_avatar', $avatar_filename);
    
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[Avatar Debug] Avatar salvato per user ' . $user_id . ': ' . $avatar_filename . ' - Result: ' . ($result ? 'OK' : 'FAILED'));
    }
    
    return (bool)$result;
}

/**
 * Mostra il selettore HTML per gli avatar
 * 
 * @param int $user_id ID dell'utente
 * @return string HTML del selettore
 */
function meridiana_render_avatar_selector_html($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return '';
    }
    
    $avatars = meridiana_get_avatar_list();
    $selected = meridiana_get_user_selected_avatar($user_id);
    $selected_filename = $selected ? $selected['filename'] : '';
    
    ob_start();
    ?>
    <div class="avatar-selector">
        <h3 class="avatar-selector__title">Scegli il tuo avatar</h3>
        
        <div class="avatar-selector__grid">
            <?php foreach ($avatars as $avatar) : ?>
                <div class="avatar-selector__item">
                    <input 
                        type="radio" 
                        id="avatar-<?php echo esc_attr($avatar['filename']); ?>" 
                        name="user_avatar" 
                        value="<?php echo esc_attr($avatar['filename']); ?>"
                        class="avatar-selector__radio"
                        <?php checked($selected_filename, $avatar['filename']); ?>
                        data-avatar-url="<?php echo esc_attr($avatar['url']); ?>"
                    >
                    <label for="avatar-<?php echo esc_attr($avatar['filename']); ?>" class="avatar-selector__label">
                        <img 
                            src="<?php echo esc_url($avatar['url']); ?>" 
                            alt="<?php echo esc_attr($avatar['label']); ?>"
                            class="avatar-selector__image"
                        >
                        <span class="avatar-selector__name"><?php echo esc_html($avatar['label']); ?></span>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
