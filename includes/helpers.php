<?php
/**
 * Helper Functions
 * 
 * Funzioni di utilità riutilizzabili in tutta la piattaforma
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Formatta data in italiano
 * 
 * @param string $date Data in formato MySQL (Y-m-d H:i:s)
 * @param string $format Formato output (default: 'd/m/Y')
 * @return string Data formattata
 */
function meridiana_format_date($date, $format = 'd/m/Y') {
    if (empty($date)) {
        return '';
    }
    
    $timestamp = strtotime($date);
    return date_i18n($format, $timestamp);
}

/**
 * Formatta data relativa (es: "2 giorni fa")
 * 
 * @param string $date Data in formato MySQL
 * @return string Data relativa
 */
function meridiana_relative_date($date) {
    if (empty($date)) {
        return '';
    }
    
    $timestamp = strtotime($date);
    return human_time_diff($timestamp, current_time('timestamp')) . ' fa';
}

/**
 * Ottieni icona Lucide per tipologia documento
 * 
 * @param string $type Tipo documento (protocollo, modulo, etc)
 * @return string Nome icona Lucide
 */
function get_document_icon($type) {
    $icons = array(
        'protocollo' => 'file-text',
        'modulo' => 'file',
        'convenzione' => 'tag',
        'comunicazione' => 'bell',
        'corso' => 'graduation-cap',
        'salute_benessere' => 'heart',
    );
    
    return isset($icons[$type]) ? $icons[$type] : 'file';
}

/**
 * Ottieni classe colore badge per stato
 * 
 * @param string $status Stato (attivo, scaduto, sospeso, etc)
 * @return string Classe CSS
 */
function get_status_badge_class($status) {
    $classes = array(
        'attivo' => 'badge-success',
        'completato' => 'badge-success',
        'scaduto' => 'badge-error',
        'sospeso' => 'badge-warning',
        'in_corso' => 'badge-info',
        'non_iniziato' => 'badge-secondary',
    );
    
    return isset($classes[$status]) ? $classes[$status] : 'badge-secondary';
}

/**
 * Tronca testo a N caratteri mantenendo parole intere
 * 
 * @param string $text Testo da troncare
 * @param int $length Lunghezza massima
 * @param string $suffix Suffisso (default: ...)
 * @return string Testo troncato
 */
function truncate_text($text, $length = 150, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    $text = wp_strip_all_tags($text);
    $text = substr($text, 0, $length);
    $text = substr($text, 0, strrpos($text, ' '));
    
    return $text . $suffix;
}

/**
 * Sanitizza nome file per upload
 * 
 * @param string $filename Nome file
 * @return string Nome file sanitizzato
 */
function sanitize_filename_upload($filename) {
    // Rimuovi estensione
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $name = pathinfo($filename, PATHINFO_FILENAME);
    
    // Sanitizza nome
    $name = sanitize_title($name);
    
    // Rimuovi caratteri speciali
    $name = preg_replace('/[^a-z0-9\-_]/', '', strtolower($name));
    
    // Aggiungi timestamp per unicità
    $name .= '-' . time();
    
    return $name . '.' . $ext;
}

/**
 * Genera excerpt personalizzato da contenuto
 * 
 * @param string $content Contenuto completo
 * @param int $length Lunghezza excerpt
 * @return string Excerpt
 */
function get_custom_excerpt($content, $length = 200) {
    $content = strip_shortcodes($content);
    $content = wp_strip_all_tags($content);
    
    return truncate_text($content, $length);
}

/**
 * Verifica se è mobile
 * 
 * @return bool
 */
function is_mobile_device() {
    return wp_is_mobile();
}

/**
 * Ottieni URL avatar utente (con fallback)
 * 
 * @param int $user_id User ID
 * @param int $size Dimensione avatar
 * @return string URL avatar
 */
function get_user_avatar_url($user_id, $size = 96) {
    $avatar_url = get_avatar_url($user_id, array('size' => $size));
    
    if (!$avatar_url) {
        // Fallback a default avatar
        $avatar_url = MERIDIANA_CHILD_URI . '/assets/images/default-avatar.png';
    }
    
    return $avatar_url;
}

/**
 * Ottieni nome completo utente
 * 
 * @param int $user_id User ID
 * @return string Nome completo
 */
function get_user_full_name($user_id) {
    $user = get_userdata($user_id);
    
    if (!$user) {
        return '';
    }
    
    $first_name = $user->first_name;
    $last_name = $user->last_name;
    
    if ($first_name && $last_name) {
        return $first_name . ' ' . $last_name;
    }
    
    // Fallback a display_name
    return $user->display_name;
}

/**
 * Verifica se PDF è embedded (non scaricabile)
 * 
 * @param string $post_type Tipo di post
 * @return bool
 */
function is_pdf_embedded($post_type) {
    // Protocolli sono embedded, moduli scaricabili
    return $post_type === 'protocollo';
}

/**
 * Ottieni etichetta taxonomy user-friendly
 * 
 * @param string $term_slug Slug term
 * @param string $taxonomy Nome taxonomy
 * @return string Etichetta term
 */
function get_term_label($term_slug, $taxonomy) {
    $term = get_term_by('slug', $term_slug, $taxonomy);
    
    if (!$term || is_wp_error($term)) {
        return $term_slug;
    }
    
    return $term->name;
}

/**
 * Genera nonce per sicurezza form
 * 
 * @param string $action Action name
 * @return string Nonce field HTML
 */
function meridiana_nonce_field($action = 'meridiana_action') {
    return wp_nonce_field($action, 'meridiana_nonce', true, false);
}

/**
 * Verifica nonce
 * 
 * @param string $action Action name
 * @return bool
 */
function meridiana_verify_nonce($action = 'meridiana_action') {
    if (!isset($_POST['meridiana_nonce'])) {
        return false;
    }
    
    return wp_verify_nonce($_POST['meridiana_nonce'], $action);
}

/**
 * Genera colore random per avatar placeholder
 * 
 * @param string $string Stringa seed (es: nome utente)
 * @return string Hex color
 */
function generate_avatar_color($string) {
    $colors = array(
        '#B91C1C', // Rosso brand
        '#0066CC', // Blu
        '#10B981', // Verde
        '#F59E0B', // Giallo
        '#8B5CF6', // Viola
        '#06B6D4', // Cyan
    );
    
    $index = abs(crc32($string)) % count($colors);
    return $colors[$index];
}

/**
 * Formatta dimensione file (KB, MB, GB)
 * 
 * @param int $bytes Dimensione in bytes
 * @param int $decimals Decimali
 * @return string Dimensione formattata
 */
function format_file_size($bytes, $decimals = 2) {
    $size = array('B', 'KB', 'MB', 'GB', 'TB');
    $factor = floor((strlen($bytes) - 1) / 3);
    
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . @$size[$factor];
}

/**
 * Ottieni ruolo utente user-friendly
 * 
 * @param int $user_id User ID
 * @return string Ruolo tradotto
 */
function get_user_role_label($user_id) {
    $user = get_userdata($user_id);
    
    if (!$user) {
        return '';
    }
    
    $roles = array(
        'administrator' => 'Amministratore',
        'gestore_piattaforma' => 'Gestore Piattaforma',
        'subscriber' => 'Utente Standard',
    );
    
    $role = $user->roles[0] ?? '';
    
    return isset($roles[$role]) ? $roles[$role] : ucfirst($role);
}

/**
 * Crea messaggio di notifica HTML
 * 
 * @param string $message Messaggio
 * @param string $type Tipo (success, error, warning, info)
 * @return string HTML notifica
 */
function create_notice($message, $type = 'info') {
    $classes = array(
        'success' => 'notice-success',
        'error' => 'notice-error',
        'warning' => 'notice-warning',
        'info' => 'notice-info',
    );
    
    $class = isset($classes[$type]) ? $classes[$type] : $classes['info'];
    
    return sprintf(
        '<div class="notice %s"><p>%s</p></div>',
        esc_attr($class),
        esc_html($message)
    );
}

/**
 * Log custom per debug
 * Solo in modalità WP_DEBUG
 * 
 * @param mixed $data Dati da loggare
 * @param string $label Etichetta
 */
function meridiana_log($data, $label = '') {
    if (!WP_DEBUG) {
        return;
    }
    
    $log_file = WP_CONTENT_DIR . '/meridiana-debug.log';
    $timestamp = date('Y-m-d H:i:s');
    
    $log_entry = "[$timestamp]";
    if ($label) {
        $log_entry .= " $label:";
    }
    $log_entry .= "\n" . print_r($data, true) . "\n\n";
    
    error_log($log_entry, 3, $log_file);
}

/**
 * Verifica se utente ha completato corso
 * 
 * @param int $user_id User ID
 * @param int $course_id Course ID
 * @return bool
 */
function user_completed_course($user_id, $course_id) {
    // LearnDash function
    if (function_exists('learndash_course_completed')) {
        return learndash_course_completed($user_id, $course_id);
    }
    
    return false;
}

/**
 * Ottieni progresso corso utente (%)
 * 
 * @param int $user_id User ID
 * @param int $course_id Course ID
 * @return int Percentuale 0-100
 */
function get_course_progress($user_id, $course_id) {
    // LearnDash function
    if (function_exists('learndash_course_progress')) {
        $progress = learndash_course_progress(array(
            'user_id' => $user_id,
            'course_id' => $course_id,
            'array' => true
        ));
        
        return isset($progress['percentage']) ? intval($progress['percentage']) : 0;
    }
    
    return 0;
}
