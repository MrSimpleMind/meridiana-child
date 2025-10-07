# 📝 Gestione Frontend e ACF Forms

> **Contesto**: Form frontend per inserimento/modifica contenuti, file upload system, gestione Gestore Piattaforma

**Leggi anche**: 
- `02_Struttura_Dati_CPT.md` per fields disponibili
- `03_Sistema_Utenti_Roles.md` per permissions

---

## 🎯 Overview Sistema Form

### Form Necessarie

1. **Protocollo** - Inserimento/Modifica
2. **Modulo** - Inserimento/Modifica
3. **Convenzione** - Inserimento/Modifica
4. **Comunicazione** - Inserimento/Modifica
5. **Organigramma** - Inserimento/Modifica
6. **Salute e Benessere** - Inserimento/Modifica
7. **Utente** - CRUD completo

### Stack Tecnico

- **ACF Pro** - Frontend Forms native
- **Custom PHP** - Validation e file management
- **Alpine.js** - Interattività client-side

---

## 📄 Form: PROTOCOLLO

### Shortcode Form

```php
// includes/acf-forms.php

function shortcode_form_protocollo() {
    // Check permission
    if (!current_user_can('edit_posts')) {
        return '<p class="error">Non hai i permessi per questa azione.</p>';
    }
    
    // Get ID se modifica
    $post_id = isset($_GET['edit']) ? intval($_GET['edit']) : 'new_post';
    
    // ACF Form Args
    $args = array(
        'post_id' => $post_id,
        'post_title' => true,
        'post_content' => false,
        'new_post' => array(
            'post_type' => 'protocollo',
            'post_status' => 'publish',
        ),
        'fields' => array(
            'field_pdf_protocollo',
            'field_riassunto',
            'field_moduli_allegati',
            'field_pianificazione_ats',
        ),
        'field_groups' => array('group_protocollo'),
        'submit_value' => $post_id === 'new_post' ? 'Pubblica Protocollo' : 'Aggiorna Protocollo',
        'updated_message' => 'Protocollo salvato con successo!',
        'return' => add_query_arg('success', '1', $_SERVER['REQUEST_URI']),
    );
    
    // Aggiungi taxonomies
    $args['fields'][] = 'unita_offerta';
    $args['fields'][] = 'profili_professionali';
    
    ob_start();
    acf_form($args);
    return ob_get_clean();
}
add_shortcode('form_protocollo', 'shortcode_form_protocollo');
```

### Hook File Upload (Archiving System)

```php
// includes/file-management.php

function gestione_aggiornamento_pdf_protocollo($post_id) {
    // Solo per protocolli
    if (get_post_type($post_id) !== 'protocollo') {
        return;
    }
    
    // Get nuovo e vecchio file
    $new_file_id = get_field('pdf_protocollo', $post_id);
    $old_file_id = get_post_meta($post_id, '_previous_pdf_id', true);
    
    // Se c'è un vecchio file e è diverso dal nuovo
    if ($old_file_id && $old_file_id != $new_file_id) {
        // Archivia vecchio file
        archivia_file_vecchio($old_file_id);
    }
    
    // Salva nuovo file ID per prossimo update
    update_post_meta($post_id, '_previous_pdf_id', $new_file_id);
}
add_action('acf/save_post', 'gestione_aggiornamento_pdf_protocollo', 20);
```

### File Archiving Logic

```php
// includes/file-management.php

function archivia_file_vecchio($file_id) {
    $file_path = get_attached_file($file_id);
    
    if (!file_exists($file_path)) {
        return false;
    }
    
    // Crea cartella archive se non esiste
    $upload_dir = wp_upload_dir();
    $archive_dir = $upload_dir['basedir'] . '/archive/';
    
    if (!file_exists($archive_dir)) {
        mkdir($archive_dir, 0755, true);
    }
    
    // Nuovo nome con timestamp
    $timestamp = current_time('Y-m-d_H-i-s');
    $filename = basename($file_path);
    $archived_path = $archive_dir . $timestamp . '_' . $filename;
    
    // Sposta file
    if (rename($file_path, $archived_path)) {
        // Log operazione
        log_file_archiving($file_id, $archived_path);
        
        // Schedule eliminazione dopo 30 giorni
        wp_schedule_single_event(
            strtotime('+30 days'),
            'elimina_file_archiviato',
            array($archived_path)
        );
        
        return true;
    }
    
    return false;
}

function log_file_archiving($file_id, $archived_path) {
    global $wpdb;
    
    $wpdb->insert(
        $wpdb->prefix . 'file_archive_log',
        array(
            'file_id' => $file_id,
            'archived_path' => $archived_path,
            'archived_date' => current_time('mysql'),
            'user_id' => get_current_user_id(),
        ),
        array('%d', '%s', '%s', '%d')
    );
}

// Cron job eliminazione
function elimina_file_archiviato($file_path) {
    if (file_exists($file_path)) {
        unlink($file_path);
        error_log("File archiviato eliminato: {$file_path}");
    }
}
add_action('elimina_file_archiviato', 'elimina_file_archiviato');
```

### Database Table per Log

```php
// includes/file-management.php - Activation hook

function crea_tabella_archive_log() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'file_archive_log';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        file_id BIGINT NOT NULL,
        archived_path VARCHAR(255) NOT NULL,
        archived_date DATETIME NOT NULL,
        user_id BIGINT NOT NULL,
        deleted_date DATETIME DEFAULT NULL,
        INDEX file_id_idx (file_id),
        INDEX archived_date_idx (archived_date)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'crea_tabella_archive_log');
```

---

## 📋 Form: MODULO

### Shortcode (Simile a Protocollo)

```php
function shortcode_form_modulo() {
    if (!current_user_can('edit_posts')) {
        return '<p class="error">Non hai i permessi.</p>';
    }
    
    $post_id = isset($_GET['edit']) ? intval($_GET['edit']) : 'new_post';
    
    acf_form(array(
        'post_id' => $post_id,
        'post_title' => true,
        'new_post' => array(
            'post_type' => 'modulo',
            'post_status' => 'publish',
        ),
        'fields' => array(
            'field_pdf_modulo',
        ),
        'submit_value' => $post_id === 'new_post' ? 'Pubblica Modulo' : 'Aggiorna Modulo',
        'updated_message' => 'Modulo salvato!',
    ));
}
add_shortcode('form_modulo', 'shortcode_form_modulo');
```

---

## 🏷 Form: CONVENZIONE

### Shortcode

```php
function shortcode_form_convenzione() {
    if (!current_user_can('edit_posts')) {
        return '<p class="error">Non hai i permessi.</p>';
    }
    
    $post_id = isset($_GET['edit']) ? intval($_GET['edit']) : 'new_post';
    
    acf_form(array(
        'post_id' => $post_id,
        'post_title' => true,
        'post_content' => true,
        'new_post' => array(
            'post_type' => 'convenzione',
            'post_status' => 'publish',
        ),
        'fields' => array(
            'field_convenzione_attiva',
            'field_convenzione_immagine',
            'field_convenzione_contatti',
            'field_convenzione_allegati',
        ),
        'submit_value' => 'Salva Convenzione',
        'updated_message' => 'Convenzione salvata!',
    ));
}
add_shortcode('form_convenzione', 'shortcode_form_convenzione');
```

---

## 👔 Form: ORGANIGRAMMA

### Shortcode

```php
function shortcode_form_organigramma() {
    if (!current_user_can('edit_posts')) {
        return '<p class="error">Non hai i permessi.</p>';
    }
    
    $post_id = isset($_GET['edit']) ? intval($_GET['edit']) : 'new_post';
    
    acf_form(array(
        'post_id' => $post_id,
        'post_title' => true,
        'new_post' => array(
            'post_type' => 'organigramma',
            'post_status' => 'publish',
        ),
        'fields' => array(
            'field_ruolo',
            'field_udo_riferimento',
            'field_email_aziendale',
            'field_telefono_aziendale',
        ),
        'submit_value' => 'Salva Contatto',
        'updated_message' => 'Contatto salvato!',
    ));
}
add_shortcode('form_organigramma', 'shortcode_form_organigramma');
```

---

## 👥 Form: GESTIONE UTENTI

### Form Nuovo Utente

```php
function shortcode_form_nuovo_utente() {
    if (!current_user_can('create_users')) {
        return '<p class="error">Non hai i permessi.</p>';
    }
    
    // Form HTML custom (ACF non supporta user form creation direttamente)
    ob_start();
    ?>
    <form method="post" action="" class="form-utente" x-data="userForm">
        <?php wp_nonce_field('crea_utente_nonce', 'utente_nonce'); ?>
        
        <div class="input-group">
            <label for="user_login">Username *</label>
            <input type="text" name="user_login" id="user_login" 
                   class="input-field" required>
        </div>
        
        <div class="input-group">
            <label for="user_email">Email *</label>
            <input type="email" name="user_email" id="user_email" 
                   class="input-field" required>
        </div>
        
        <div class="input-group">
            <label for="first_name">Nome</label>
            <input type="text" name="first_name" id="first_name" 
                   class="input-field">
        </div>
        
        <div class="input-group">
            <label for="last_name">Cognome</label>
            <input type="text" name="last_name" id="last_name" 
                   class="input-field">
        </div>
        
        <div class="input-group">
            <label for="role">Ruolo</label>
            <select name="role" id="role" class="select-field">
                <option value="subscriber">Utente Standard</option>
                <?php if(current_user_can('manage_options')): ?>
                <option value="gestore_piattaforma">Gestore Piattaforma</option>
                <?php endif; ?>
            </select>
        </div>
        
        <!-- ACF Fields -->
        <?php 
        // Render ACF fields programmatically
        acf_render_field(acf_get_field('field_stato_utente'));
        acf_render_field(acf_get_field('field_profilo_professionale'));
        acf_render_field(acf_get_field('field_udo_riferimento'));
        acf_render_field(acf_get_field('field_link_autologin'));
        ?>
        
        <button type="submit" name="crea_utente" class="btn btn-primary btn-lg">
            Crea Utente
        </button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('form_nuovo_utente', 'shortcode_form_nuovo_utente');
```

### Processa Form Utente

```php
function processa_form_nuovo_utente() {
    // Verifica nonce
    if (!isset($_POST['utente_nonce']) || 
        !wp_verify_nonce($_POST['utente_nonce'], 'crea_utente_nonce')) {
        return;
    }
    
    if (!isset($_POST['crea_utente'])) {
        return;
    }
    
    // Check permission
    if (!current_user_can('create_users')) {
        wp_die('Permessi insufficienti');
    }
    
    // Sanitize input
    $username = sanitize_user($_POST['user_login']);
    $email = sanitize_email($_POST['user_email']);
    $first_name = sanitize_text_field($_POST['first_name']);
    $last_name = sanitize_text_field($_POST['last_name']);
    $role = sanitize_text_field($_POST['role']);
    
    // Validate
    if (username_exists($username)) {
        wp_die('Username già esistente');
    }
    
    if (email_exists($email)) {
        wp_die('Email già esistente');
    }
    
    // Crea utente
    $user_id = wp_create_user($username, wp_generate_password(), $email);
    
    if (is_wp_error($user_id)) {
        wp_die($user_id->get_error_message());
    }
    
    // Update meta
    wp_update_user(array(
        'ID' => $user_id,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'role' => $role,
    ));
    
    // Save ACF fields
    update_field('stato_utente', $_POST['acf']['field_stato_utente'], 'user_' . $user_id);
    update_field('profilo_professionale', $_POST['acf']['field_profilo_professionale'], 'user_' . $user_id);
    update_field('udo_riferimento', $_POST['acf']['field_udo_riferimento'], 'user_' . $user_id);
    update_field('link_autologin_esterno', $_POST['acf']['field_link_autologin'], 'user_' . $user_id);
    
    // Hook post-creazione (vedi 07_Notifiche_Automazioni.md)
    do_action('dopo_creazione_utente', $user_id);
    
    // Redirect con success message
    wp_redirect(add_query_arg('user_created', $user_id, $_SERVER['REQUEST_URI']));
    exit;
}
add_action('init', 'processa_form_nuovo_utente');
```

---

## 🎨 Styling Form

### CSS Form Wrapper

```scss
.form-wrapper {
    max-width: 800px;
    margin: 0 auto;
    padding: var(--space-6);
    
    form {
        background: var(--color-bg-primary);
        border-radius: var(--radius-lg);
        padding: var(--space-8);
        box-shadow: var(--shadow-sm);
    }
    
    .input-group {
        margin-bottom: var(--space-6);
    }
    
    .acf-field {
        margin-bottom: var(--space-6);
    }
    
    .acf-label {
        font-weight: var(--font-weight-semibold);
        margin-bottom: var(--space-2);
    }
}
```

---

## ✅ Validation Custom

### Client-Side (Alpine.js)

```javascript
// assets/js/src/alpine-components.js

Alpine.data('userForm', () => ({
    validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },
    
    async checkUsername(username) {
        // Check via AJAX se username esiste
        const response = await fetch('/wp-json/piattaforma/v1/check-username', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({username})
        });
        return await response.json();
    }
}));
```

### Server-Side Validation

```php
// includes/acf-forms.php

function validate_pdf_file($valid, $value, $field, $input) {
    if (!$valid) {
        return $valid;
    }
    
    // Check file size (max 10MB)
    if ($value['size'] > 10 * 1024 * 1024) {
        $valid = 'Il file non può superare 10MB.';
    }
    
    // Check MIME type
    if ($value['type'] !== 'application/pdf') {
        $valid = 'Solo file PDF sono permessi.';
    }
    
    return $valid;
}
add_filter('acf/validate_value/type=file', 'validate_pdf_file', 10, 4);
```

---

## 🤖 Checklist per IA

Quando lavori con form frontend:

- [ ] Sempre check `current_user_can()` prima del form
- [ ] Nonce per sicurezza: `wp_nonce_field()`
- [ ] Sanitize input: `sanitize_text_field()`, `sanitize_email()`
- [ ] Validate server-side, non solo client
- [ ] File upload: check MIME type e dimensione
- [ ] Success/error messages user-friendly
- [ ] Redirect dopo submit (PRG pattern)
- [ ] Log azioni critiche (file upload, user creation)
- [ ] Test con utenti di ruoli diversi
- [ ] Gestione errori graceful (wp_die vs echo)

---

**📝 Sistema form completo, sicuro e user-friendly.**
