<?php
/**
 * Avatar System - Predefined Icons
 * Sistema di avatar predefiniti con icone Lucide
 */

if (!defined('ABSPATH')) exit;

/**
 * Array avatar predefiniti
 * 
 * @return array Lista avatar con icona e colore sfondo
 */
function meridiana_get_avatar_options() {
    return [
        'user-blue' => [
            'icon' => 'user',
            'bg_color' => '#3B82F6', // Blue
            'label' => 'Profilo Standard (Blu)'
        ],
        'briefcase-green' => [
            'icon' => 'briefcase',
            'bg_color' => '#10B981', // Green
            'label' => 'Professionale (Verde)'
        ],
        'user-check-purple' => [
            'icon' => 'user-check',
            'bg_color' => '#8B5CF6', // Purple
            'label' => 'Certificato (Viola)'
        ],
        'shield-orange' => [
            'icon' => 'shield',
            'bg_color' => '#F59E0B', // Orange
            'label' => 'Sicurezza (Arancione)'
        ],
        'heart-pink' => [
            'icon' => 'heart',
            'bg_color' => '#EC4899', // Pink
            'label' => 'Assistenza (Rosa)'
        ],
        'users-teal' => [
            'icon' => 'users',
            'bg_color' => '#14B8A6', // Teal
            'label' => 'Team (Turchese)'
        ],
    ];
}

/**
 * Get user avatar (custom o predefinito)
 * 
 * @param int $user_id User ID
 * @param string $size Avatar size (small|medium|large)
 * @return string HTML avatar
 */
function meridiana_get_user_avatar($user_id = null, $size = 'medium') {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    // Dimensioni
    $sizes = [
        'small' => 32,
        'medium' => 48,
        'large' => 64,
    ];
    
    $px = $sizes[$size] ?? 48;
    $icon_size = round($px * 0.5); // Icona = 50% del container
    
    // Check se ha avatar custom uploadato
    $custom_avatar_id = get_user_meta($user_id, 'custom_avatar', true);
    if ($custom_avatar_id) {
        $avatar_url = wp_get_attachment_image_url($custom_avatar_id, 'thumbnail');
        if ($avatar_url) {
            return sprintf(
                '<div class="user-avatar user-avatar--%s" style="width: %dpx; height: %dpx;">
                    <img src="%s" alt="Avatar" style="width: 100%%; height: 100%%; object-fit: cover; border-radius: 50%%;">
                </div>',
                esc_attr($size),
                $px,
                $px,
                esc_url($avatar_url)
            );
        }
    }
    
    // Altrimenti usa avatar predefinito
    $avatar_key = get_user_meta($user_id, 'predefined_avatar', true);
    if (!$avatar_key) {
        $avatar_key = 'user-blue'; // Default
    }
    
    $avatars = meridiana_get_avatar_options();
    $avatar = $avatars[$avatar_key] ?? $avatars['user-blue'];
    
    return sprintf(
        '<div class="user-avatar user-avatar--%s" style="width: %dpx; height: %dpx; background-color: %s;">
            <i data-lucide="%s" style="width: %dpx; height: %dpx; color: white;"></i>
        </div>',
        esc_attr($size),
        $px,
        $px,
        esc_attr($avatar['bg_color']),
        esc_attr($avatar['icon']),
        $icon_size,
        $icon_size
    );
}

/**
 * Update user avatar (AJAX handler già presente, questo è solo helper)
 * 
 * @param int $user_id User ID
 * @param string $avatar_key Avatar key oppure 'custom' per upload
 * @param int $attachment_id Attachment ID se custom
 * @return bool Success
 */
function meridiana_update_user_avatar($user_id, $avatar_key, $attachment_id = null) {
    if ($avatar_key === 'custom' && $attachment_id) {
        update_user_meta($user_id, 'custom_avatar', $attachment_id);
        delete_user_meta($user_id, 'predefined_avatar');
        return true;
    }
    
    $avatars = meridiana_get_avatar_options();
    if (isset($avatars[$avatar_key])) {
        update_user_meta($user_id, 'predefined_avatar', $avatar_key);
        delete_user_meta($user_id, 'custom_avatar');
        return true;
    }
    
    return false;
}

/**
 * Render avatar selector for forms
 * 
 * @param int $user_id User ID
 * @return string HTML avatar selector
 */
function meridiana_render_avatar_selector($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    $current_avatar = get_user_meta($user_id, 'predefined_avatar', true) ?: 'user-blue';
    $avatars = meridiana_get_avatar_options();
    
    ob_start();
    ?>
    <div class="avatar-selector">
        <label class="form-label">Scegli il tuo avatar</label>
        <div class="avatar-selector__grid">
            <?php foreach ($avatars as $key => $avatar): ?>
            <label class="avatar-selector__option">
                <input 
                    type="radio" 
                    name="predefined_avatar" 
                    value="<?php echo esc_attr($key); ?>"
                    <?php checked($current_avatar, $key); ?>
                >
                <div class="avatar-selector__preview" style="background-color: <?php echo esc_attr($avatar['bg_color']); ?>;">
                    <i data-lucide="<?php echo esc_attr($avatar['icon']); ?>"></i>
                </div>
                <span class="avatar-selector__label"><?php echo esc_html($avatar['label']); ?></span>
            </label>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
