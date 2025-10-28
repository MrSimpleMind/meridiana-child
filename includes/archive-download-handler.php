<?php
/**
 * Archive Download Handler
 * Gestisce il download sicuro di file archiviati
 *
 * AJAX Endpoint: /wp-admin/admin-ajax.php?action=meridiana_download_archive
 * Parameters:
 *   - post_id (int): ID del documento
 *   - archive_num (int): Numero archivio (1, 2, 3, etc)
 *   - nonce (string): Nonce security token
 */

if (!defined('ABSPATH')) exit;

// ============================================
// DOWNLOAD ARCHIVE AJAX HANDLER
// ============================================

add_action('wp_ajax_meridiana_download_archive', 'meridiana_handle_archive_download');
add_action('wp_ajax_nopriv_meridiana_download_archive', 'meridiana_handle_archive_download_nopriv');

/**
 * Handler per download file archiviato
 * Accessibile solo agli utenti loggati con permessi
 */
function meridiana_handle_archive_download() {
    // Security: Verify nonce
    $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
    $archive_num = isset($_GET['archive_num']) ? intval($_GET['archive_num']) : 0;
    $nonce = isset($_GET['nonce']) ? sanitize_text_field($_GET['nonce']) : '';

    if (!$post_id || !$archive_num) {
        wp_die(__('Parametri non validi', 'meridiana-child'), 400);
    }

    // Verify nonce with context
    if (!wp_verify_nonce($nonce, 'meridiana_archive_download_' . $post_id)) {
        wp_die(__('Nonce non valido o scaduto', 'meridiana-child'), 403);
    }

    // Security: Capability check
    if (!current_user_can('manage_platform') && !current_user_can('manage_options')) {
        wp_die(__('Permessi insufficienti', 'meridiana-child'), 403);
    }

    // Validate post exists and is a document type
    $post = get_post($post_id);
    if (!$post || !in_array($post->post_type, ['protocollo', 'modulo'])) {
        wp_die(__('Documento non trovato', 'meridiana-child'), 404);
    }

    // Get archive metadata
    $archive_metadata = get_post_meta($post_id, '_archive_' . $archive_num, true);
    if (!$archive_metadata || !is_array($archive_metadata)) {
        wp_die(__('Archivio non trovato', 'meridiana-child'), 404);
    }

    // Get file path
    $archived_path = $archive_metadata['archived_file_path'] ?? '';
    if (!$archived_path || !file_exists($archived_path)) {
        wp_die(__('File archiviato non trovato nel filesystem', 'meridiana-child'), 404);
    }

    // Security: Validate path is within uploads directory (no directory traversal)
    $upload_dir = wp_upload_dir();
    $upload_base = trailingslashit($upload_dir['basedir']);
    $real_path = realpath($archived_path);

    if (!$real_path || strpos($real_path, $upload_base) !== 0) {
        error_log("Meridiana: Tentativo accesso file fuori da upload dir: $archived_path");
        wp_die(__('Accesso negato', 'meridiana-child'), 403);
    }

    // Get original filename for download
    $original_filename = $archive_metadata['original_filename'] ?? basename($archived_path);
    $original_filename = sanitize_file_name($original_filename);

    // Serve file
    meridiana_serve_file_download($real_path, $original_filename);
}

/**
 * Handler per utenti non loggati (nopriv)
 */
function meridiana_handle_archive_download_nopriv() {
    wp_die(__('Accesso negato. Effettua il login.', 'meridiana-child'), 403);
}


// ============================================
// FILE SERVING FUNCTION
// ============================================

/**
 * Serve un file per il download con headers appropriati
 *
 * @param string $file_path - Path assoluto del file
 * @param string $filename - Nome da usare nel download
 */
function meridiana_serve_file_download($file_path, $filename) {
    // Validate file
    if (!is_file($file_path) || !is_readable($file_path)) {
        wp_die(__('File non leggibile', 'meridiana-child'), 403);
    }

    // Get file size
    $file_size = filesize($file_path);
    if ($file_size === false) {
        wp_die(__('Errore durante la lettura del file', 'meridiana-child'), 500);
    }

    // Determine MIME type
    $mime_type = meridiana_get_file_mime_type($file_path);

    // Set headers
    header('Content-Type: ' . $mime_type, true);
    header('Content-Disposition: attachment; filename="' . $filename . '"', true);
    header('Content-Length: ' . $file_size, true);
    header('Cache-Control: no-cache, no-store, must-revalidate', true);
    header('Pragma: no-cache', true);
    header('Expires: 0', true);

    // Disable WordPress and output the file
    readfile($file_path);
    exit();
}


// ============================================
// UTILITY: GET MIME TYPE
// ============================================

/**
 * Determina il MIME type di un file
 *
 * @param string $file_path - Path del file
 * @return string - MIME type
 */
function meridiana_get_file_mime_type($file_path) {
    $file_ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

    $mime_types = [
        'pdf' => 'application/pdf',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'zip' => 'application/zip',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    return $mime_types[$file_ext] ?? 'application/octet-stream';
}


// ============================================
// VIEW ARCHIVE HANDLER (INLINE - NOT ATTACHMENT)
// ============================================

add_action('wp_ajax_meridiana_view_archive', 'meridiana_handle_archive_view');
add_action('wp_ajax_nopriv_meridiana_view_archive', 'meridiana_handle_archive_view_nopriv');

/**
 * Handler per visualizzare file archiviato inline nel browser
 * Identico a download ma con Content-Disposition: inline
 */
function meridiana_handle_archive_view() {
    // Security & Validation: Identico a download
    $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
    $archive_num = isset($_GET['archive_num']) ? intval($_GET['archive_num']) : 0;
    $nonce = isset($_GET['nonce']) ? sanitize_text_field($_GET['nonce']) : '';

    if (!$post_id || !$archive_num) {
        wp_die(__('Parametri non validi', 'meridiana-child'), 400);
    }

    if (!wp_verify_nonce($nonce, 'meridiana_archive_view_' . $post_id)) {
        wp_die(__('Nonce non valido o scaduto', 'meridiana-child'), 403);
    }

    if (!current_user_can('manage_platform') && !current_user_can('manage_options')) {
        wp_die(__('Permessi insufficienti', 'meridiana-child'), 403);
    }

    $post = get_post($post_id);
    if (!$post || !in_array($post->post_type, ['protocollo', 'modulo'])) {
        wp_die(__('Documento non trovato', 'meridiana-child'), 404);
    }

    $archive_metadata = get_post_meta($post_id, '_archive_' . $archive_num, true);
    if (!$archive_metadata || !is_array($archive_metadata)) {
        wp_die(__('Archivio non trovato', 'meridiana-child'), 404);
    }

    $archived_path = $archive_metadata['archived_file_path'] ?? '';
    if (!$archived_path || !file_exists($archived_path)) {
        wp_die(__('File archiviato non trovato nel filesystem', 'meridiana-child'), 404);
    }

    // Path validation
    $upload_dir = wp_upload_dir();
    $upload_base = trailingslashit($upload_dir['basedir']);
    $real_path = realpath($archived_path);

    if (!$real_path || strpos($real_path, $upload_base) !== 0) {
        error_log("Meridiana: Tentativo accesso file fuori da upload dir (view): $archived_path");
        wp_die(__('Accesso negato', 'meridiana-child'), 403);
    }

    $original_filename = $archive_metadata['original_filename'] ?? basename($archived_path);
    $original_filename = sanitize_file_name($original_filename);

    // Serve file inline (not attachment)
    meridiana_serve_file_view($real_path, $original_filename);
}

function meridiana_handle_archive_view_nopriv() {
    wp_die(__('Accesso negato. Effettua il login.', 'meridiana-child'), 403);
}

/**
 * Serve file per visualizzazione inline nel browser
 * Identico a download ma con Content-Disposition: inline
 *
 * @param string $file_path - Path assoluto del file
 * @param string $filename - Nome del file
 */
function meridiana_serve_file_view($file_path, $filename) {
    if (!is_file($file_path) || !is_readable($file_path)) {
        wp_die(__('File non leggibile', 'meridiana-child'), 403);
    }

    $file_size = filesize($file_path);
    if ($file_size === false) {
        wp_die(__('Errore durante la lettura del file', 'meridiana-child'), 500);
    }

    $mime_type = meridiana_get_file_mime_type($file_path);

    // Headers per inline view (non attachment)
    header('Content-Type: ' . $mime_type, true);
    header('Content-Disposition: inline; filename="' . $filename . '"', true);
    header('Content-Length: ' . $file_size, true);
    header('Cache-Control: no-cache, no-store, must-revalidate', true);
    header('Pragma: no-cache', true);
    header('Expires: 0', true);

    readfile($file_path);
    exit();
}


// ============================================
// RESTORE ARCHIVE HANDLER (AJAX POST)
// ============================================

add_action('wp_ajax_meridiana_restore_archive', 'meridiana_handle_archive_restore');

/**
 * Handler per ripristinare un file archiviato
 * Archivia il corrente e rimpiazza con l'archiviato
 *
 * Parametri via GET (link diretto da template)
 * - post_id: ID del documento
 * - archive_num: Numero dell'archivio da ripristinare
 * - nonce: Nonce security token
 */
function meridiana_handle_archive_restore() {
    // Leggi parametri da GET (come nel link dal template)
    $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
    $archive_num = isset($_GET['archive_num']) ? intval($_GET['archive_num']) : 0;
    $nonce = isset($_GET['nonce']) ? sanitize_text_field($_GET['nonce']) : '';

    if (!$post_id || !$archive_num) {
        wp_send_json_error(['message' => __('Parametri non validi', 'meridiana-child')], 400);
    }

    // Security: Verify nonce con il contesto corretto
    if (!wp_verify_nonce($nonce, 'meridiana_restore_archive_' . $post_id)) {
        wp_send_json_error(['message' => __('Nonce non valido o scaduto', 'meridiana-child')], 403);
    }

    // Security: Capability check - solo admin/editor del documento
    if (!current_user_can('edit_post', $post_id) && !current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Permessi insufficienti', 'meridiana-child')], 403);
    }

    // Validate: post exists and is document type
    $post = get_post($post_id);
    if (!$post || !in_array($post->post_type, ['protocollo', 'modulo'])) {
        wp_send_json_error(['message' => __('Documento non trovato', 'meridiana-child')], 404);
    }

    // Get archive metadata
    $archive_metadata = get_post_meta($post_id, '_archive_' . $archive_num, true);
    if (!$archive_metadata || !is_array($archive_metadata)) {
        wp_send_json_error(['message' => __('Archivio non trovato', 'meridiana-child')], 404);
    }

    // Call restore function (from meridiana-archive-system.php)
    if (!function_exists('meridiana_restore_archive_file')) {
        wp_send_json_error(['message' => __('Funzione restore non disponibile', 'meridiana-child')], 500);
    }

    $result = meridiana_restore_archive_file($post_id, $archive_num);
    if (!$result || (is_array($result) && isset($result['success']) && !$result['success'])) {
        error_log("Meridiana Restore Error: " . print_r($result, true));
        wp_send_json_error(['message' => __('Errore durante il ripristino', 'meridiana-child')], 500);
    }

    // Success response
    wp_send_json_success([
        'message' => __('File ripristinato con successo', 'meridiana-child'),
        'post_id' => $post_id,
        'redirect' => add_query_arg([
            'post' => $post_id,
            'action' => 'edit',
            'restored' => 'true',
        ], admin_url('post.php')),
    ]);
}
