# 📝 Gestione Frontend e File Archiving

> **Ultimo aggiornamento**: 1 Novembre 2025
> **Fonte**: `page-dashboard-gestore.php`, `includes/gestore-acf-forms.php`, `includes/ajax-gestore-handlers.php`, `includes/meridiana-archive-system.php`

**Leggi anche**:
- `02_Struttura_Dati_CPT.md` per i campi disponibili
- `03_Sistema_Utenti_Roles.md` per i permessi di gestione

---

## 🎯 Architettura del Sistema di Gestione

La gestione dei contenuti da parte del ruolo **Gestore Piattaforma** avviene interamente nel frontend, attraverso una **Dashboard Gestore** (`/dashboard-gestore/`). Questo sistema **non utilizza la funzione `acf_form()`** standard, ma si basa su un'architettura custom costruita con **Alpine.js** e **AJAX** per offrire un'esperienza più fluida e integrata, simile a una Single Page Application (SPA).

### Flusso Operativo

1.  **Caricamento Tabella**: Al caricamento di una tab (es. "Documenti"), una query PHP iniziale popola la tabella con i contenuti esistenti.
2.  **Azione Utente**: L'utente clicca "Aggiungi Nuovo" o "Modifica" su un item.
3.  **Richiesta AJAX del Form**: Alpine.js invia una richiesta AJAX all'endpoint `gestore_load_form`.
4.  **Rendering PHP del Form**: Il file `includes/gestore-acf-forms.php` genera l'HTML del form richiesto, pre-popolato con i dati se si tratta di una modifica.
5.  **Iniezione nel Modal**: L'HTML del form viene restituito e iniettato in un modal nel frontend.
6.  **Submit AJAX**: L'utente compila e invia il form. Alpine.js intercetta il submit e invia i dati all'endpoint `gestore_save_form`.
7.  **Salvataggio PHP**: Il file `includes/ajax-gestore-handlers.php` riceve i dati, li valida, e li salva (creando o aggiornando il post/utente).
8.  **Feedback e Aggiornamento UI**: Il server restituisce un messaggio di successo o errore. Alpine.js aggiorna la tabella dei dati in tempo reale, senza ricaricare la pagina.

### Vantaggi di Questo Approccio

- **Performance**: Nessun ricaricamento della pagina per le operazioni CRUD.
- **Controllo Totale**: Logica di validazione, salvataggio e gestione dei file interamente customizzabile.
- **UX Migliore**: L'interfaccia è più reattiva e moderna.
- **Sicurezza**: Ogni endpoint AJAX è protetto da nonce e check di capabilities.

---

## 🗂️ Gestione dei Contenuti (CPT)

La logica per la gestione di **Protocolli, Moduli, Comunicazioni, Convenzioni e Salute e Benessere** segue lo stesso pattern.

### 1. Rendering del Form (PHP)

La funzione `meridiana_render_documento_form()` (e le sue varianti per gli altri CPT) in `includes/gestore-acf-forms.php` non usa `acf_form()`, ma costruisce l'HTML del form manualmente, campo per campo.

```php
// Esempio per il campo file di un protocollo
function meridiana_render_acf_fields_for_post($post_type, $post_id = 0, $action = 'new') {
    // ... logica per recuperare i valori...

    $pdf_value = get_field('field_pdf_protocollo', $post_id);
    $pdf_info = meridiana_get_attachment_info($pdf_value);

    // HTML del campo
    ?>
    <div class="media-field" data-media-field>
        <input type="hidden" name="acf[field_pdf_protocollo]" value="<?php echo esc_attr($pdf_info['id']); ?>" />
        <button type="button" class="button media-picker">Seleziona PDF</button>
        <span class="media-file-name"><?php echo esc_html($pdf_info['name']); ?></span>
    </div>
    <?php
}
```

### 2. Salvataggio del Form (PHP)

L'handler `meridiana_ajax_save_documento()` in `includes/ajax-gestore-handlers.php` gestisce il salvataggio.

```php
function meridiana_ajax_save_documento() {
    // ... nonce e permission check ...

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $title = sanitize_text_field($_POST['post_title']);
    $cpt = sanitize_text_field($_POST['cpt']);

    // Gestione del file PDF (vecchio e nuovo)
    $pdf_field_key = ($cpt === 'protocollo') ? 'field_pdf_protocollo' : 'field_pdf_modulo';
    $old_attachment_id = $post_id ? get_field($pdf_field_key, $post_id, false) : 0;
    $new_attachment_id = isset($_POST['acf'][$pdf_field_key]) ? intval($_POST['acf'][$pdf_field_key]) : 0;

    // ... logica di creazione/aggiornamento post ...

    // Aggiorna i campi ACF
    update_field($pdf_field_key, $new_attachment_id, $post_id);
    // ... altri campi ...

    // Trigger per il sistema di archiviazione
    if ($old_attachment_id && $old_attachment_id != $new_attachment_id) {
        do_action('meridiana_document_file_changed', $post_id, $old_attachment_id, $new_attachment_id, 'edit_document');
    }

    wp_send_json_success(...);
}
```

---

## 🔄 Sistema di Archiviazione e Pulizia dei File

Un sistema robusto gestisce l'archiviazione dei file PDF quando vengono sostituiti, per prevenire la perdita di dati e consentire il ripristino.

**File di Riferimento**: `includes/meridiana-archive-system.php`

### Flusso di Archiviazione

1.  **Trigger**: Quando un documento viene salvato e il campo `pdf_protocollo` o `pdf_modulo` cambia, viene lanciato l'hook `meridiana_document_file_changed`.
2.  **Archiviazione**: La funzione `meridiana_archive_replaced_document()` viene eseguita.
    - Il vecchio file PDF viene **copiato** (non spostato) dalla directory standard di upload a `/wp-content/uploads/archived-files/`.
    - Il nuovo nome del file archiviato include un timestamp (es. `nomefile_archived_2025-11-01_10-30-00.pdf`).
3.  **Salvataggio Metadati**: Un nuovo post meta (`_archive_1`, `_archive_2`, etc.) viene aggiunto al post del documento, contenente un array con tutte le informazioni sull'archivio (path, nome originale, data, utente che ha eseguito l'azione).
4.  **Pulizia Media Library**: Il vecchio attachment viene **eliminato** dalla Media Library di WordPress, per mantenere pulito l'elenco dei media.

### Flusso di Pulizia (Cleanup)

1.  **Trigger**: Quando un documento (`protocollo` o `modulo`) viene eliminato definitivamente (`wp_delete_post` con `force_delete=true`), viene lanciato l'hook `delete_post`.
2.  **Pulizia**: La funzione `meridiana_cleanup_deleted_document()` viene eseguita.
    - Legge tutti i metadati `_archive_*` associati al post.
    - Per ogni archivio, **elimina il file fisico** dalla directory `/archived-files/`.
    - Elimina tutti i post meta `_archive_*` dal database.

### Flusso di Ripristino (Recovery)

Il sistema prevede anche una logica di ripristino (attualmente non esposta nell'UI ma pronta per essere usata):

1.  **Trigger**: Un'azione utente (es. click su un pulsante "Ripristina") chiama l'endpoint AJAX `meridiana_restore_archive`.
2.  **Ripristino**: La funzione `meridiana_restore_archive_file()` viene eseguita.
    - Il file **attualmente attivo** viene prima archiviato, per non perderlo.
    - Il file selezionato dall'archivio viene **ricopiato** nella directory standard degli upload.
    - Viene creato un **nuovo attachment** nella Media Library.
    - Il campo ACF del documento viene aggiornato con l'ID del nuovo attachment.
    - Il file e i metadati dell'archivio ripristinato vengono eliminati dallo storico.

---

## 🤖 Checklist per Sviluppo sui Form

- **Permessi**: Verificare sempre i permessi con `current_user_can()` all'inizio di ogni handler AJAX.
- **Nonce**: Utilizzare sempre `wp_verify_nonce()` per proteggere gli endpoint da attacchi CSRF.
- **Sanificazione**: Sanificare tutti gli input provenienti da `$_POST` o `$_GET` con funzioni come `sanitize_text_field()`, `intval()`, `esc_url_raw()`.
- **Validazione Server-Side**: Non fidarsi mai della validazione client-side. Replicare sempre i controlli critici (es. campi obbligatori, formati) lato server.
- **Gestione File**: Per gli upload, controllare sempre il tipo MIME e la dimensione del file.
- **Feedback Utente**: Fornire sempre messaggi di successo o errore chiari e traducibili.
- **Logging**: Loggare le azioni critiche (creazione/eliminazione di contenuti, archiviazione file) per facilitare il debug.