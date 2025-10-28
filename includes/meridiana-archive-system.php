<?php
/**
 * File Archiving & Cleanup System
 * Gestisce l'archiviazione automatica di file sostituiti e la pulizia su hard delete
 *
 * FLOW:
 * 1. Quando un documento viene editato e il PDF viene sostituito:
 *    → Vecchio PDF viene archiviato in /wp-content/uploads/archived-files/
 *    → Metadata del vecchio file viene salvato in postmeta
 *
 * 2. Quando un documento viene hard deleted:
 *    → Tutti i file archiviati associati vengono eliminati
 *    → Metadata viene pulito
 */

if (!defined('ABSPATH')) exit;

// ============================================
// SETUP DIRECTORY PER ARCHIVIAZIONE
// ============================================

function meridiana_ensure_archive_directory() {
    $upload_dir = wp_upload_dir();
    $basedir = wp_normalize_path($upload_dir['basedir']); // Normalize immediately
    $archive_path = trailingslashit($basedir) . 'archived-files';

    // Crea directory se non esiste
    if (!is_dir($archive_path)) {
        wp_mkdir_p($archive_path);
    }

    // Crea .htaccess per bloccare accesso diretto
    $htaccess_file = $archive_path . '/.htaccess';
    if (!file_exists($htaccess_file)) {
        $htaccess_content = "# Archived files - not directly accessible\n";
        $htaccess_content .= "deny from all\n";
        file_put_contents($htaccess_file, $htaccess_content);
    }

    // Crea index.php per sicurezza
    $index_file = $archive_path . '/index.php';
    if (!file_exists($index_file)) {
        file_put_contents($index_file, "<?php // Archived files directory\n");
    }

    return $archive_path;
}

// Chiama al theme load
add_action('wp_loaded', 'meridiana_ensure_archive_directory');


// ============================================
// FUNZIONE: ARCHIVIO FILE SOSTITUITO
// ============================================

/**
 * Archivia un file quando viene sostituito
 *
 * @param int    $post_id - ID del documento (protocollo/modulo)
 * @param int    $old_attachment_id - ID del vecchio allegato (opzionale, sarà determinato se non fornito)
 * @param string $context - Contexto dell'archiving ('edit_document', 'user_action', etc)
 * @return bool|array - True se archivio riuscito, false altrimenti, o array con dettagli
 */
function meridiana_archive_replaced_document($post_id, $old_attachment_id = 0, $context = 'edit_document') {
    // Validazione
    $post_id = intval($post_id);
    if (!$post_id) {
        return false;
    }

    $post = get_post($post_id);
    if (!$post || !in_array($post->post_type, ['protocollo', 'modulo'])) {
        return false;
    }

    // Se non fornito attachment ID, cerca il file dal postmeta
    if (!$old_attachment_id) {
        $old_attachment_id = intval(get_post_meta($post_id, '_documento_pdf', true));
    }

    if (!$old_attachment_id) {
        return false; // Nessun file precedente da archiviare
    }

    // Ottieni i dati dell'allegato
    $attachment = get_post($old_attachment_id);
    if (!$attachment || $attachment->post_type !== 'attachment') {
        return false;
    }

    $attachment_path = get_attached_file($old_attachment_id);
    if (!$attachment_path) {
        return false;
    }

    // Normalize path immediately for Windows compatibility
    $attachment_path = wp_normalize_path($attachment_path);

    if (!file_exists($attachment_path)) {
        return false;
    }

    // Prepara directory archivio
    $archive_dir = meridiana_ensure_archive_directory();

    // Genera nome file archiviato con timestamp
    $file_info = pathinfo($attachment_path);
    $timestamp = time();
    $date_formatted = date('Y-m-d_H-i-s', $timestamp);
    $archived_filename = sanitize_file_name(
        $file_info['filename'] . '_archived_' . $date_formatted . '.' . $file_info['extension']
    );

    $archived_path = trailingslashit($archive_dir) . $archived_filename;
    $archived_path = wp_normalize_path($archived_path); // Normalize path for Windows compatibility

    // Copia il file in archivio
    if (!copy($attachment_path, $archived_path)) {
        error_log("Meridiana: Impossibile archiviare file $attachment_path");
        return false;
    }

    // Salva metadata di archivio nel postmeta
    $archive_metadata = array(
        'original_attachment_id' => $old_attachment_id,
        'original_filename' => $attachment->post_title ?: basename($attachment_path),
        'original_file_path' => wp_normalize_path($attachment_path),
        'archived_filename' => $archived_filename,
        'archived_file_path' => $archived_path,
        'archived_timestamp' => $timestamp,
        'archived_date_formatted' => $date_formatted,
        'archived_by_user_id' => get_current_user_id(),
        'archived_by_user_name' => wp_get_current_user()->user_login,
        'context' => $context,
        'document_post_id' => $post_id,
        'document_post_title' => $post->post_title,
    );

    // Memorizza l'archivio nel postmeta come array serializzato
    // Usiamo una progressione numerica per supportare multipli archivi
    $archive_count = intval(get_post_meta($post_id, '_archive_count', true)) ?: 0;
    $archive_count++;

    update_post_meta($post_id, '_archive_' . $archive_count, $archive_metadata);
    update_post_meta($post_id, '_archive_count', $archive_count);

    // Log l'azione
    error_log("Meridiana: Documento {$post_id} - File archiviato: {$archived_filename}");

    return array(
        'success' => true,
        'archived_path' => $archived_path,
        'archived_filename' => $archived_filename,
        'original_attachment_id' => $old_attachment_id,
        'archive_metadata' => $archive_metadata,
    );
}


// ============================================
// FUNZIONE: CLEANUP FILES ARCHIVIATI
// ============================================

/**
 * Elimina tutti i file archiviati associati a un documento quando viene hard-deleted
 *
 * @param int $post_id - ID del documento da eliminare
 * @return bool - True se cleanup riuscito
 */
function meridiana_cleanup_deleted_document($post_id) {
    $post_id = intval($post_id);
    if (!$post_id) {
        return false;
    }

    // Leggi tutti gli archivi associati al post
    $archive_count = intval(get_post_meta($post_id, '_archive_count', true)) ?: 0;

    $deleted_count = 0;
    for ($i = 1; $i <= $archive_count; $i++) {
        $archive_metadata = get_post_meta($post_id, '_archive_' . $i, true);

        if (!$archive_metadata || !is_array($archive_metadata)) {
            continue;
        }

        $archived_path = $archive_metadata['archived_file_path'] ?? '';

        // Elimina il file fisico se esiste
        if ($archived_path && file_exists($archived_path)) {
            if (unlink($archived_path)) {
                $deleted_count++;
                error_log("Meridiana: File archiviato eliminato: {$archived_path}");
            } else {
                error_log("Meridiana: ERRORE - Impossibile eliminare file archiviato: {$archived_path}");
            }
        }

        // Elimina il postmeta
        delete_post_meta($post_id, '_archive_' . $i);
    }

    // Elimina il contatore di archivi
    if ($deleted_count > 0) {
        delete_post_meta($post_id, '_archive_count');
        error_log("Meridiana: Documento {$post_id} - {$deleted_count} file archiviati eliminati");
    }

    return true;
}


// ============================================
// HOOK: AUTO-ARCHIVING SU EDIT DOCUMENTO
// ============================================

/**
 * Hook che cattura quando un documento viene salvato e il PDF cambia
 * Viene chiamato dall'AJAX handler gestore-acf-forms.php
 */
function meridiana_on_document_file_change($post_id, $old_attachment_id, $new_attachment_id, $context = 'edit_document') {
    // Solo per protocollo e modulo
    $post_type = get_post_type($post_id);
    if (!in_array($post_type, ['protocollo', 'modulo'])) {
        return;
    }

    // Se il file è stato effettivamente cambiato
    if ($old_attachment_id && $old_attachment_id != $new_attachment_id) {
        meridiana_archive_replaced_document($post_id, $old_attachment_id, $context);
    }
}

// Aggiungiamo un hook personalizzato che verrà chiamato dalla form save
add_action('meridiana_document_file_changed', 'meridiana_on_document_file_change', 10, 4);


// ============================================
// HOOK: CLEANUP SU HARD DELETE DOCUMENTO
// ============================================

/**
 * Hook che cattura la hard deletion di un post
 * Viene triggerato da wp_delete_post con force_delete=true
 */
add_action('delete_post', function($post_id) {
    // Ottieni il post prima che sia completamente eliminato
    $post = get_post($post_id);

    // Solo per protocollo e modulo
    if ($post && in_array($post->post_type, ['protocollo', 'modulo'])) {
        meridiana_cleanup_deleted_document($post_id);
    }
}, 10, 1);


// ============================================
// UTILITY: LISTA ARCHIVI PER UN DOCUMENTO
// ============================================

/**
 * Restituisce la lista di tutti i file archiviati per un documento
 *
 * @param int $post_id - ID del documento
 * @return array - Array di archive metadata
 */
function meridiana_get_document_archives($post_id) {
    $post_id = intval($post_id);
    if (!$post_id) {
        return [];
    }

    $archive_count = intval(get_post_meta($post_id, '_archive_count', true)) ?: 0;
    $archives = [];

    for ($i = 1; $i <= $archive_count; $i++) {
        $archive_metadata = get_post_meta($post_id, '_archive_' . $i, true);
        if ($archive_metadata && is_array($archive_metadata)) {
            $archives[] = array_merge(['archive_number' => $i], $archive_metadata);
        }
    }

    return $archives;
}


// ============================================
// UTILITY: RESTORE FILE DA ARCHIVIO
// ============================================

/**
 * Opzionale: Ripristina un file archiviato (per future implementazioni)
 *
 * @param int    $post_id - ID del documento
 * @param int    $archive_number - Numero archivio da ripristinare
 * @return bool - True se ripristino riuscito
 */
function meridiana_restore_archived_file($post_id, $archive_number) {
    $post_id = intval($post_id);
    $archive_number = intval($archive_number);

    if (!$post_id || !$archive_number) {
        return false;
    }

    $archive_metadata = get_post_meta($post_id, '_archive_' . $archive_number, true);
    if (!$archive_metadata || !is_array($archive_metadata)) {
        return false;
    }

    $archived_path = $archive_metadata['archived_file_path'] ?? '';
    if (!$archived_path || !file_exists($archived_path)) {
        return false;
    }

    // Qui potremmo implementare la logica di ripristino
    // Per ora è solo uno skeleton per future estensioni

    return true;
}


// ============================================
// CRON JOB: PULIZIA ARCHIVI VECCHI (OPZIONALE)
// ============================================

/**
 * Opzionale: Pulizia automatica archivi più vecchi di X giorni
 * Può essere integrata come cron job in seguito
 *
 * @param int $older_than_days - Elimina file più vecchi di N giorni (default 90)
 * @return int - Numero di file eliminati
 */
function meridiana_cleanup_old_archives($older_than_days = 90) {
    $archive_dir = meridiana_ensure_archive_directory();
    $cutoff_time = time() - ($older_than_days * 24 * 60 * 60);
    $deleted_count = 0;

    // Scansiona i file nella directory di archivio
    $files = glob(trailingslashit($archive_dir) . '*.pdf');

    foreach ($files as $file) {
        if (is_file($file) && filemtime($file) < $cutoff_time) {
            if (unlink($file)) {
                $deleted_count++;
                error_log("Meridiana: File archivio vecchio eliminato: $file");
            }
        }
    }

    return $deleted_count;
}

// ============================================
// HELPER FUNCTIONS: FORMATTING + UTILITIES
// ============================================

/**
 * Calcola i giorni rimanenti prima dell'eliminazione automatica
 *
 * @param int $archived_timestamp - Timestamp unix di archiving
 * @param int $deletion_days - Giorni prima dell'eliminazione (default 30)
 * @return int - Giorni rimanenti
 */
function meridiana_get_days_until_deletion($archived_timestamp, $deletion_days = 30) {
    $timestamp = intval($archived_timestamp);
    if (!$timestamp) {
        return 0;
    }

    $deletion_time = $timestamp + ($deletion_days * 24 * 60 * 60);
    $days_remaining = ceil(($deletion_time - time()) / (24 * 60 * 60));

    return max(0, intval($days_remaining));
}


/**
 * Formatta il timestamp di archiving in formato leggibile
 *
 * @param int $timestamp - Unix timestamp
 * @return string - Formato: "27 Ott 2025, 14:30"
 */
function meridiana_format_archive_date($timestamp) {
    $timestamp = intval($timestamp);
    if (!$timestamp) {
        return '';
    }

    // Locale-aware formatting
    return date_i18n(_x('j M Y, H:i', 'archive date format', 'meridiana-child'), $timestamp);
}


/**
 * Genera URL di download sicuro per file archiviato
 *
 * @param int $post_id - ID del documento
 * @param int $archive_number - Numero archivio (1, 2, 3, etc)
 * @return string - URL completo con nonce
 */
function meridiana_get_archive_download_url($post_id, $archive_number) {
    $post_id = intval($post_id);
    $archive_number = intval($archive_number);

    if (!$post_id || !$archive_number) {
        return '';
    }

    // Crea nonce valido per 1 ora
    $nonce = wp_create_nonce('meridiana_archive_download_' . $post_id);

    return add_query_arg([
        'action' => 'meridiana_download_archive',
        'post_id' => $post_id,
        'archive_num' => $archive_number,
        'nonce' => $nonce,
    ], admin_url('admin-ajax.php'));
}


/**
 * Genera URL per ripristinare un file archiviato
 *
 * @param int $post_id - ID del documento
 * @param int $archive_number - Numero archivio da ripristinare
 * @return string - URL completo con nonce
 */
function meridiana_get_archive_restore_url($post_id, $archive_number) {
    $post_id = intval($post_id);
    $archive_number = intval($archive_number);

    if (!$post_id || !$archive_number) {
        return '';
    }

    // Check permissions - solo admin o editor del documento
    if (!current_user_can('edit_post', $post_id)) {
        return '';
    }

    // Crea nonce valido
    $nonce = wp_create_nonce('meridiana_restore_archive_' . $post_id);

    return add_query_arg([
        'action' => 'meridiana_restore_archive',
        'post_id' => $post_id,
        'archive_num' => $archive_number,
        'nonce' => $nonce,
    ], admin_url('admin-ajax.php'));
}


/**
 * Valida se un archivio è ancora disponibile (non ancora eliminato)
 *
 * @param int $post_id - ID del documento
 * @param int $archive_number - Numero archivio
 * @return bool - True se archivio esiste e file fisico presente
 */
function meridiana_archive_exists($post_id, $archive_number) {
    $post_id = intval($post_id);
    $archive_number = intval($archive_number);

    if (!$post_id || !$archive_number) {
        return false;
    }

    $archive_metadata = get_post_meta($post_id, '_archive_' . $archive_number, true);
    if (!$archive_metadata || !is_array($archive_metadata)) {
        return false;
    }

    $archived_path = $archive_metadata['archived_file_path'] ?? '';
    if (!$archived_path || !file_exists($archived_path)) {
        return false;
    }

    return true;
}


/**
 * Genera URL di visualizzazione inline per file archiviato
 * File apre nel browser, non scarica
 *
 * @param int $post_id - ID del documento
 * @param int $archive_number - Numero archivio
 * @return string - URL completo con nonce
 */
function meridiana_get_archive_view_url($post_id, $archive_number) {
    $post_id = intval($post_id);
    $archive_number = intval($archive_number);

    if (!$post_id || !$archive_number) {
        return '';
    }

    $nonce = wp_create_nonce('meridiana_archive_view_' . $post_id);

    return add_query_arg([
        'action' => 'meridiana_view_archive',
        'post_id' => $post_id,
        'archive_num' => $archive_number,
        'nonce' => $nonce,
    ], admin_url('admin-ajax.php'));
}


/**
 * Ripristina un file archiviato come file corrente
 * Archivia il file corrente PRIMA di rimpiazzarlo
 *
 * @param int $post_id - ID del documento
 * @param int $archive_number - Numero archivio da ripristinare
 * @return bool|array - True se successo, array con dettagli, false se errore
 */
function meridiana_restore_archive_file($post_id, $archive_number) {
    $post_id = intval($post_id);
    $archive_number = intval($archive_number);

    error_log("Meridiana: Restore START - post_id=$post_id, archive_number=$archive_number");

    if (!$post_id || !$archive_number) {
        error_log("Meridiana: Restore FAIL - Invalid post_id or archive_number");
        return false;
    }

    // Validate post
    $post = get_post($post_id);
    if (!$post) {
        error_log("Meridiana: Restore FAIL - Post not found: $post_id");
        return false;
    }
    if (!in_array($post->post_type, ['protocollo', 'modulo'])) {
        error_log("Meridiana: Restore FAIL - Invalid post_type: {$post->post_type}");
        return false;
    }

    // Get archive metadata
    $archive_metadata = get_post_meta($post_id, '_archive_' . $archive_number, true);
    if (!$archive_metadata) {
        error_log("Meridiana: Restore FAIL - Archive metadata not found for archive #$archive_number");
        return false;
    }
    if (!is_array($archive_metadata)) {
        error_log("Meridiana: Restore FAIL - Archive metadata is not an array");
        return false;
    }

    $archived_path = $archive_metadata['archived_file_path'] ?? '';
    if (!$archived_path) {
        error_log("Meridiana: Restore FAIL - archived_file_path is empty in metadata");
        return false;
    }

    // Normalize path immediately after reading from metadata (for backwards compatibility with old archives)
    $archived_path = wp_normalize_path($archived_path);
    error_log("Meridiana: Restore - Raw archived_path from metadata: " . ($archive_metadata['archived_file_path'] ?? 'EMPTY'));
    error_log("Meridiana: Restore - Normalized archived_path: $archived_path");

    if (!file_exists($archived_path)) {
        error_log("Meridiana: Restore FAIL - Archived file does not exist: $archived_path");
        error_log("Meridiana: Restore - Trying to find file with glob fallback...");

        // Fallback: Try to find the file using glob
        $archive_dir = dirname($archived_path);
        $archive_dir = wp_normalize_path($archive_dir); // Normalize directory path too
        $filename_pattern = basename($archived_path);

        error_log("Meridiana: Restore - Glob search in: $archive_dir with pattern: $filename_pattern");

        if (is_dir($archive_dir)) {
            $found_files = glob($archive_dir . '/*' . $filename_pattern);
            if (!empty($found_files)) {
                $archived_path = $found_files[0]; // Use first match
                error_log("Meridiana: Restore - Found file via glob: $archived_path");
            } else {
                error_log("Meridiana: Restore FAIL - File not found even with glob in: $archive_dir");
                return false;
            }
        } else {
            error_log("Meridiana: Restore FAIL - Archive directory does not exist: $archive_dir");
            return false;
        }
    }

    error_log("Meridiana: Restore - Archive metadata OK, file exists at: $archived_path");

    // Get current file attachment ID
    $pdf_field_key = $post->post_type === 'protocollo' ? 'field_pdf_protocollo' : 'field_pdf_modulo';
    $current_attachment_id = intval(get_field($pdf_field_key, $post_id)) ?: 0;
    error_log("Meridiana: Restore - Current PDF field: $pdf_field_key, attachment_id: $current_attachment_id");

    // STEP 1: Archive current file if it exists
    if ($current_attachment_id) {
        error_log("Meridiana: Restore - Archiving current file (attachment_id: $current_attachment_id)");
        meridiana_archive_replaced_document($post_id, $current_attachment_id, 'restore_action');
    }

    // STEP 2: Copy archived file to uploads directory and create attachment
    $archived_filename = $archive_metadata['archived_filename'] ?? basename($archived_path);
    $original_filename = $archive_metadata['original_filename'] ?? $archived_filename;

    error_log("Meridiana: Restore - Original filename: $original_filename");

    // Get uploads directory
    $upload_dir = wp_upload_dir();
    if (is_wp_error($upload_dir)) {
        error_log("Meridiana: Restore FAIL - Error getting uploads directory: " . $upload_dir->get_error_message());
        return false;
    }
    $uploads_base = trailingslashit($upload_dir['basedir']);

    error_log("Meridiana: Restore - Uploads base: $uploads_base");

    // Generate unique filename for restored file in uploads (not in archived-files)
    $restored_filename = wp_unique_filename($uploads_base, $original_filename);
    $restored_path = $uploads_base . $restored_filename;
    $restored_path = wp_normalize_path($restored_path); // Normalize for Windows

    error_log("Meridiana: Restore - Restored path: $restored_path");

    // Copy archived file back to uploads
    if (!copy($archived_path, $restored_path)) {
        error_log("Meridiana: Restore FAIL - Cannot copy file from $archived_path to $restored_path");
        return false;
    }

    error_log("Meridiana: Restore - File copied successfully");

    // Create attachment post
    $attachment_data = [
        'post_title' => $original_filename,
        'post_content' => '',
        'post_status' => 'inherit',
        'post_type' => 'attachment',
        'post_mime_type' => 'application/pdf',
    ];

    $attachment_id = wp_insert_attachment($attachment_data);
    if (is_wp_error($attachment_id)) {
        wp_delete_file($restored_path); // Cleanup on error
        error_log("Meridiana: Restore FAIL - Error creating attachment: " . $attachment_id->get_error_message());
        return false;
    }

    error_log("Meridiana: Restore - Attachment created, ID: $attachment_id");

    // Register attachment file path
    $attach_file_result = update_attached_file($attachment_id, $restored_path);
    error_log("Meridiana: Restore - update_attached_file result: " . ($attach_file_result ? 'true' : 'false'));

    // Generate attachment metadata
    $metadata = wp_generate_attachment_metadata($attachment_id, $restored_path);
    error_log("Meridiana: Restore - Metadata generated: " . print_r($metadata, true));

    $meta_update = wp_update_attachment_metadata($attachment_id, $metadata);
    error_log("Meridiana: Restore - update_attachment_metadata result: " . ($meta_update ? 'true' : 'false'));

    // STEP 3: Update ACF field with new attachment ID
    if (!function_exists('update_field')) {
        wp_delete_post($attachment_id, true); // Cleanup on error
        error_log("Meridiana: Restore FAIL - update_field function not available");
        return false;
    }

    $updated = update_field($pdf_field_key, $attachment_id, $post_id);
    error_log("Meridiana: Restore - update_field($pdf_field_key) result: " . ($updated ? 'true' : 'false'));

    if (!$updated) {
        wp_delete_post($attachment_id, true); // Cleanup on error
        error_log("Meridiana: Restore FAIL - Error updating ACF field");
        return false;
    }

    error_log("Meridiana: File ripristinato - Documento {$post_id}, Archivio {$archive_number}");

    return [
        'success' => true,
        'post_id' => $post_id,
        'new_attachment_id' => $attachment_id,
        'restored_filename' => $original_filename,
    ];
}


// ============================================
// CRON JOB: PULIZIA ARCHIVI VECCHI (ATTIVO)
// ============================================

/**
 * Cron hook per cleanup automatico archivi
 * Eseguito giornalmente
 */
function meridiana_cron_cleanup_old_archives() {
    // Default: 30 giorni
    meridiana_cleanup_old_archives(30);
}

// Registra il cron job
add_action('meridiana_cleanup_archives_cron', 'meridiana_cron_cleanup_old_archives');

// Schedula il cron se non già schedulato
if (!wp_next_scheduled('meridiana_cleanup_archives_cron')) {
    wp_schedule_event(time(), 'daily', 'meridiana_cleanup_archives_cron');
}
