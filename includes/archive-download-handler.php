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
