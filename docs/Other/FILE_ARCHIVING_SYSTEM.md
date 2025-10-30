# 📦 File Archiving & Automatic Cleanup System

**Implementazione completata**: 28 Ottobre 2025

## Overview

Sistema automatico di archiviazione e pulizia per documenti (Protocolli e Moduli) nella Dashboard Gestore.

### Obiettivi
- ✅ Archiviazione automatica di file PDF quando vengono sostituiti
- ✅ Cleanup automatico su hard delete documento
- ✅ Tracking metadata (original name, replacement date, deleter)
- ✅ Storage sicuro in `/wp-content/uploads/archived-files/`

---

## Architecture

### 1. Core Module: `includes/meridiana-archive-system.php`

Contiene tutte le funzioni di archiving e cleanup:

#### Funzioni Principali:

**`meridiana_ensure_archive_directory()`**
- Crea la directory `/wp-content/uploads/archived-files/` se non esiste
- Aggiunge `.htaccess` per bloccare accesso diretto ai file
- Aggiunge `index.php` per sicurezza

**`meridiana_archive_replaced_document($post_id, $old_attachment_id, $context)`**
- Archivia un file PDF quando viene sostituito
- **Parameters**:
  - `$post_id` (int) - ID del documento (protocollo/modulo)
  - `$old_attachment_id` (int) - ID dell'allegato vecchio da archiviare
  - `$context` (string) - Contesto ('edit_document', 'user_action', etc)
- **Return**: Array con dettagli archivio o false
- **Metadata salvato**: Serializzato in postmeta come `_archive_[N]`

**`meridiana_cleanup_deleted_document($post_id)`**
- Elimina tutti i file archiviati quando un documento viene hard-deleted
- Pulisce il postmeta associato
- **Parameters**: `$post_id` (int)
- **Return**: bool

**`meridiana_get_document_archives($post_id)`**
- Restituisce lista di tutti gli archivi per un documento
- Utile per future implementazioni (restore, audit log)

---

## Flow di Archiviazione

### Scenario 1: Documento Edit - File PDF Sostituito

```
1. Gestore fa click "Edit" documento in dashboard
2. Form carica il vecchio PDF attuale
3. Gestore seleziona NUOVO PDF dal media library
4. Submit form → AJAX request
   ↓
5. meridiana_ajax_save_documento() (gestore-acf-forms.php:2121)
   ↓
6. meridiana_save_documento_acf_fields() (gestore-acf-forms.php:2225)
   - Cattura il vecchio PDF ID: `$old_pdf_value`
   - Compara con nuovo PDF ID: `$pdf_value`
   - Se diversi: chiama meridiana_archive_replaced_document()
   ↓
7. meridiana_archive_replaced_document()
   - Copia il vecchio PDF in: /wp-content/uploads/archived-files/oldname_archived_2025-10-28_14-30-45.pdf
   - Salva metadata in postmeta:
     * _archive_1: { original_filename, archived_filename, timestamp, user_id, context, ... }
     * _archive_count: 1
   ↓
8. ACF field aggiornato con nuovo PDF
9. Response success al frontend
10. Redirect dashboard
```

### Scenario 2: Documento Delete - Hard Delete + Archivi Cleanup

```
1. Gestore fa click "Delete" documento in dashboard
2. Confirm dialog mostrato
3. Submit → AJAX request
   ↓
4. meridiana_ajax_delete_documento() (ajax-gestore-handlers.php:220)
   - Verifica permessi
   - Valida post_id e post_type
   ↓
5. meridiana_cleanup_deleted_document($post_id) (esplicito)
   - Legge _archive_count postmeta
   - Per ogni archivio (_archive_1, _archive_2, ...):
     * Cancella il file fisico dalla directory archived-files/
     * Cancella il postmeta _archive_[N]
   - Cancella _archive_count
   ↓
6. wp_delete_post($post_id, true) - Hard delete
   ↓
7. Hook delete_post attivato (meridiana-archive-system.php:164)
   - Ridondanza: chiama meridiana_cleanup_deleted_document() di nuovo
   - Non causa problemi perché i metadati sono già stati puliti
   ↓
8. Response success al frontend
9. Redirect dashboard
```

---

## File Structure

### Directory Archivi

```
/wp-content/uploads/archived-files/
├── .htaccess                              (blocca accesso diretto)
├── index.php                              (sicurezza)
├── protocollo_sostenibilita_archived_2025-10-28_14-30-45.pdf
├── modulo_checklist_archived_2025-10-28_15-45-12.pdf
└── ...
```

### Metadata Structure

Ogni documento che ha archivi contiene nel postmeta:

```php
// Numero di archivi
get_post_meta($post_id, '_archive_count', true); // int: 2

// Archive #1 - Metadata serializzato
get_post_meta($post_id, '_archive_1', true);
// Array:
// [
//     'archive_number' => 1,
//     'original_attachment_id' => 123,
//     'original_filename' => 'protocollo.pdf',
//     'original_file_path' => '/home/.../uploads/2025/10/protocollo.pdf',
//     'archived_filename' => 'protocollo_archived_2025-10-28_14-30-45.pdf',
//     'archived_file_path' => '/home/.../uploads/archived-files/protocollo_archived_2025-10-28_14-30-45.pdf',
//     'archived_timestamp' => 1729425045,
//     'archived_date_formatted' => '2025-10-28 14:30:45',
//     'archived_by_user_id' => 2,
//     'archived_by_user_name' => 'matteo',
//     'context' => 'edit_document',
//     'document_post_id' => 456,
//     'document_post_title' => 'Protocollo Sostenibilità'
// ]

// Archive #2 - Secondo archivio (se il documento è stato editato più volte)
get_post_meta($post_id, '_archive_2', true);
// Array: [...]
```

---

## Files Modificati

### 1. `includes/meridiana-archive-system.php` (NUOVO)
- 350 linee di codice
- Funzioni core: archive, cleanup, utility

### 2. `functions.php`
**Change**: Aggiunto include
```php
require_once MERIDIANA_CHILD_DIR . '/includes/meridiana-archive-system.php';
```

### 3. `includes/gestore-acf-forms.php` (MODIFICATO)
**Function**: `meridiana_save_documento_acf_fields()` (linea 2225)

**Before**:
```php
// Archive old PDF if exists (opzionale in questa fase)
// TODO: Implementare archiving in fase successiva
```

**After**:
```php
// Cattura il vecchio PDF prima di aggiornare
$old_pdf_value = intval(get_field($pdf_field_key, $post_id)) ?: 0;

// ... get new PDF value ...

// Se il PDF è stato cambiato (edit) o rimosso, archivia il vecchio file
if ($old_pdf_value && $old_pdf_value !== $pdf_value && function_exists('meridiana_archive_replaced_document')) {
    meridiana_archive_replaced_document($post_id, $old_pdf_value, 'edit_document');
}
```

### 4. `includes/ajax-gestore-handlers.php` (MODIFICATO)
**Function**: `meridiana_ajax_delete_documento()` (linea 220)

**Before**:
```php
// Archive old PDF if exists (opzionale in questa fase)
// TODO: Implementare archiving in fase successiva

// Hard delete
$deleted = wp_delete_post($post_id, true);
```

**After**:
```php
// Cleanup archivi associati al documento PRIMA della hard delete
if (function_exists('meridiana_cleanup_deleted_document')) {
    meridiana_cleanup_deleted_document($post_id);
}

// Hard delete
$deleted = wp_delete_post($post_id, true);
```

---

## Testing Checklist

### Test 1: Archiviazione File (Edit)

```
1. ✅ Dashboard Gestore → Tab Documentazione
2. ✅ Click Edit su un documento esistente
3. ✅ Seleziona un NUOVO PDF diverso da quello attuale
4. ✅ Clicca "Aggiorna Documento"
5. ✅ Verifica:
   - Documento aggiornato con nuovo PDF
   - Directory /wp-content/uploads/archived-files/ contiene il vecchio PDF
   - Nome file: "original_name_archived_DATE_TIME.pdf"
   - Postmeta _archive_1 e _archive_count = 1 salvati
   - Log: "Documento X - File archiviato: filename_archived_..."
6. ✅ Edit di nuovo e sostituisci il PDF
   - Verifica: ora ci sono 2 file in archived-files/
   - Postmeta _archive_count = 2
```

### Test 2: Cleanup Files (Delete)

```
1. ✅ Dashboard Gestore → Tab Documentazione
2. ✅ Click Delete su un documento che ha archivi
3. ✅ Conferma eliminazione
4. ✅ Verifica:
   - Documento eliminato da database
   - File archiviati ELIMINATI da /wp-content/uploads/archived-files/
   - Postmeta _archive_1, _archive_2, _archive_count PULITI
   - Log: "Documento X - 2 file archiviati eliminati"
   - Redirect dashboard OK
```

### Test 3: Multiple Edits

```
1. ✅ Crea un documento NUOVO
2. ✅ Edit + sostituisci PDF 3 volte diverse
3. ✅ Verifica:
   - 3 file in archived-files/ con timestamp diversi
   - _archive_count = 3
   - _archive_1, _archive_2, _archive_3 tutti presenti
   - Metadata complete per ogni archivio
```

### Test 4: Delete without Edits

```
1. ✅ Crea un documento NUOVO con PDF
2. ✅ Delete subito (senza edit)
3. ✅ Verifica:
   - Documento eliminato
   - No archivi in archived-files/ (nessuno era stato creato)
   - Cleanup completo
```

---

## Security Considerations

### 1. Directory Protection
✅ `.htaccess` blocca accesso diretto ai file archiviati
✅ `index.php` previene directory listing
✅ Solo backend PHP può leggere i file

### 2. Permission Checks
✅ Tutte le funzioni di archiving sono interne (non AJAX)
✅ Solo AJAX handlers con capability check chiamano archiving
✅ `manage_platform` o `manage_options` richiesto

### 3. File Validation
✅ Verifica che il file esista prima di copiare
✅ Verifica che il post sia di tipo corretto (protocollo/modulo)
✅ Verifica che l'attachment sia valido

### 4. Database Safety
✅ Use of `update_post_meta` con serializzazione automatica
✅ Nessuna SQL injection (uses WordPress API)
✅ Metadata isolato per post (no global state)

---

## Performance Impact

### CPU
- ✅ Minimal: Operazioni file sono dirette (no loop su attachment library)
- ✅ Archiving è on-demand durante edit (non cron)

### Storage
- ⚠️ Accumulo: Directory archived-files/ crescerà con ogni edit
- 💡 Futura: Implementare cron job `meridiana_cleanup_old_archives()` per pulizia >90 giorni

### Database
- ✅ Minimal: Solo postmeta per documento
- ✅ Index friendly: Meta keys sono standard (`_archive_count`, `_archive_N`)

---

## Future Enhancements

### 1. Restore from Archive
```php
meridiana_restore_archived_file($post_id, $archive_number);
```
- Skeleton function già presente
- Implementazione: copiare file da archived-files/ al media library

### 2. Archive Cleanup Cron Job
```php
// Elimina archivi >90 giorni
meridiana_cleanup_old_archives(90);
```
- Skeleton function già presente
- Implementare come scheduled cron job

### 3. Audit Log Dashboard
- Mostrare lista di archivi per documento
- Visualizzare: filename originale, data archiving, chi ha fatto edit
- Link per download/restore (future)

### 4. Archive Export
- CSV export di tutti gli archivi
- Backup tools integrazione

---

## Debugging & Logs

### Error Logs Location
```
/home/username/Local Sites/nuova-formazione/logs/php-error.log
```

### Log Messages
```
// Success archiving
"Meridiana: Documento 456 - File archiviato: protocollo_archived_2025-10-28_14-30-45.pdf"

// Error archiving
"Meridiana: Impossibile archiviare file /path/to/file.pdf"

// Cleanup success
"Meridiana: Documento 456 - 2 file archiviati eliminati"

// Cleanup error
"Meridiana: ERRORE - Impossibile eliminare file archiviato: /path/to/archived.pdf"
```

### Check Archives via WP CLI
```bash
# List posts with archives
wp post meta list 123 --key=_archive_count

# Get archive metadata
wp post meta get 123 --key=_archive_1

# Check archive directory
ls -la /wp-content/uploads/archived-files/
```

---

## Implementation Notes

### Decision: Serializzazione vs Custom Table
✅ Scelto: Postmeta serializzato
- Pro: Semplice, WordPress-native, no schema migration
- Con: Max archivi per documento (praticamente illimitato con postmeta)

### Decision: Copy vs Move
✅ Scelto: Copy (non move)
- Pro: File originale rimane intatto se copy fallisce
- Con: Spazio disco aumentato (ma è lo scopo dell'archivio)

### Decision: Hook Placement
✅ Scelto: Hook esplicito in AJAX + redundant on delete_post
- Pro: Garantisce cleanup anche se AJAX non chiama
- Con: Doppia chiamata, ma idempotentz (secondo cleanup è no-op)

---

## Version History

**28 Ottobre 2025 - v1.0**
- ✅ Initial implementation
- ✅ Auto-archive on edit
- ✅ Auto-cleanup on delete
- ✅ Metadata tracking
- ⏳ Future: Restore, Cron cleanup, Audit log

---

## References

- Core Module: `includes/meridiana-archive-system.php`
- Integration Points:
  - `includes/gestore-acf-forms.php:2225` (archive on edit)
  - `includes/ajax-gestore-handlers.php:220` (cleanup on delete)
- Tests: Manual testing checklist above
