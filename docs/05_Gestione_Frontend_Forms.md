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

---

## 🔄 Sistema di Recovery File Archiviati

### Perché Serve

Se il Gestore carica un file sbagliato per errore, ha 30 giorni per recuperare la versione precedente prima che venga eliminata definitivamente.

### Query File Archiviati per Documento

```php
// includes/file-management.php

function get_archived_files($post_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'file_archive_log';
    
    // Get ID file attuale
    $post_type = get_post_type($post_id);
    $field_name = ($post_type === 'protocollo') ? 'pdf_protocollo' : 'pdf_modulo';
    $current_file_id = get_field($field_name, $post_id);
    
    // Query file archiviati
    $archived = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table 
        WHERE file_id != %d 
        AND deleted_date IS NULL
        AND archived_path LIKE %s
        ORDER BY archived_date DESC",
        $current_file_id,
        '%' . sanitize_title(get_the_title($post_id)) . '%'
    ));
    
    return $archived;
}
```

### UI: Lista File Archiviati

```php
// Template part per form modifica documento
// templates/parts/forms/file-recovery-section.php

<?php
$archived_files = get_archived_files(get_the_ID());

if (empty($archived_files)) {
    return;
}
?>

<div class="file-recovery-section">
    <h3>📋 Versioni Precedenti (Recovery)</h3>
    <p class="helper-text">File archiviati disponibili per recupero (eliminazione automatica dopo 30 giorni)</p>
    
    <table class="table">
        <thead>
            <tr>
                <th>Nome File</th>
                <th>Data Archiving</th>
                <th>Archiviato Da</th>
                <th>Giorni Rimanenti</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($archived_files as $file): 
                $days_left = 30 - floor((time() - strtotime($file->archived_date)) / DAY_IN_SECONDS);
                $user = get_userdata($file->user_id);
            ?>
            <tr>
                <td><?php echo basename($file->archived_path); ?></td>
                <td><?php echo wp_date('d/m/Y H:i', strtotime($file->archived_date)); ?></td>
                <td><?php echo $user ? $user->display_name : 'N/A'; ?></td>
                <td>
                    <span class="badge <?php echo $days_left < 7 ? 'badge-warning' : 'badge-info'; ?>">
                        <?php echo $days_left; ?> giorni
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline" 
                            onclick="recuperaFile(<?php echo $file->id; ?>, <?php echo get_the_ID(); ?>)">
                        <i data-lucide="rotate-ccw"></i> Recupera
                    </button>
                    <a href="<?php echo site_url('/wp-content/uploads/archive/' . basename($file->archived_path)); ?>" 
                       class="btn btn-sm" 
                       target="_blank">
                        <i data-lucide="eye"></i> Anteprima
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
```

### Function: Recupera File Archiviato

```php
// includes/file-management.php

function recupera_file_archiviato($archive_id, $post_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'file_archive_log';
    
    // Check permission
    if (!current_user_can('edit_posts')) {
        return new WP_Error('unauthorized', 'Non autorizzato');
    }
    
    // Get archived file info
    $archived = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE id = %d",
        $archive_id
    ));
    
    if (!$archived || !file_exists($archived->archived_path)) {
        return new WP_Error('file_not_found', 'File archiviato non trovato');
    }
    
    // Get current file
    $post_type = get_post_type($post_id);
    $field_name = ($post_type === 'protocollo') ? 'pdf_protocollo' : 'pdf_modulo';
    $current_file_id = get_field($field_name, $post_id);
    
    // Archivia il file ATTUALE (quello che stiamo per sostituire)
    archivia_file_vecchio($current_file_id);
    
    // Ripristina il file archiviato
    $upload_dir = wp_upload_dir();
    $new_filename = basename($archived->archived_path);
    $new_path = $upload_dir['path'] . '/' . $new_filename;
    
    // Copia (non sposta) file archiviato
    if (!copy($archived->archived_path, $new_path)) {
        return new WP_Error('copy_failed', 'Errore copia file');
    }
    
    // Crea nuovo attachment in WordPress
    $attachment = array(
        'post_mime_type' => 'application/pdf',
        'post_title' => preg_replace('/\.[^.]+$/', '', $new_filename),
        'post_content' => '',
        'post_status' => 'inherit',
    );
    
    $attach_id = wp_insert_attachment($attachment, $new_path, $post_id);
    
    // Update ACF field
    update_field($field_name, $attach_id, $post_id);
    
    // Log recovery
    $wpdb->insert(
        $table,
        array(
            'file_id' => $attach_id,
            'archived_path' => $new_path,
            'archived_date' => current_time('mysql'),
            'user_id' => get_current_user_id(),
        ),
        array('%d', '%s', '%s', '%d')
    );
    
    return array(
        'success' => true,
        'message' => 'File recuperato con successo',
        'new_attachment_id' => $attach_id,
    );
}

// REST API endpoint per recovery
function register_file_recovery_endpoint() {
    register_rest_route('piattaforma/v1', '/file-recovery', array(
        'methods' => 'POST',
        'callback' => 'api_file_recovery',
        'permission_callback' => function() {
            return current_user_can('edit_posts');
        },
    ));
}
add_action('rest_api_init', 'register_file_recovery_endpoint');

function api_file_recovery($request) {
    $archive_id = intval($request->get_param('archive_id'));
    $post_id = intval($request->get_param('post_id'));
    
    $result = recupera_file_archiviato($archive_id, $post_id);
    
    if (is_wp_error($result)) {
        return new WP_Error(
            $result->get_error_code(),
            $result->get_error_message(),
            array('status' => 400)
        );
    }
    
    return rest_ensure_response($result);
}
```

### JavaScript: AJAX Recovery

```javascript
// assets/js/src/file-recovery.js

async function recuperaFile(archiveId, postId) {
    if (!confirm('Sei sicuro di voler recuperare questa versione? Il file attuale verrà archiviato.')) {
        return;
    }
    
    try {
        const response = await fetch('/wp-json/piattaforma/v1/file-recovery', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': window.meridiana.nonce,
            },
            body: JSON.stringify({
                archive_id: archiveId,
                post_id: postId,
            }),
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('File recuperato con successo!');
            location.reload();
        } else {
            alert('Errore: ' + data.message);
        }
    } catch (error) {
        console.error('Recovery error:', error);
        alert('Errore durante il recupero del file');
    }
}
```

### Shortcode: Integrazione nel Form Modifica

```php
// Aggiungere nel form di modifica documento (dopo il campo file)

<div class="form-section">
    <?php 
    if (isset($_GET['edit'])) {
        echo do_shortcode('[file_recovery_list post_id="' . intval($_GET['edit']) . '"]');
    }
    ?>
</div>

// Shortcode
function shortcode_file_recovery_list($atts) {
    $atts = shortcode_atts(array(
        'post_id' => 0,
    ), $atts);
    
    if (!$atts['post_id']) {
        return '';
    }
    
    ob_start();
    set_query_var('post_id', $atts['post_id']);
    get_template_part('templates/parts/forms/file-recovery-section');
    return ob_get_clean();
}
add_shortcode('file_recovery_list', 'shortcode_file_recovery_list');
```

### CSS Styling

```scss
// assets/css/src/components/_file-recovery.scss

.file-recovery-section {
    margin-top: var(--space-8);
    padding: var(--space-6);
    background: var(--color-bg-secondary);
    border-radius: var(--radius-lg);
    border: 2px dashed var(--color-border);
    
    h3 {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        margin-bottom: var(--space-4);
    }
    
    .helper-text {
        color: var(--color-text-secondary);
        font-size: var(--font-size-sm);
        margin-bottom: var(--space-4);
    }
    
    .badge-warning {
        background-color: var(--color-warning);
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
}
```

### Dashboard Widget: File in Scadenza

```php
// Widget per Gestore: file archiviati che scadranno tra 7 giorni

function widget_file_in_scadenza() {
    global $wpdb;
    $table = $wpdb->prefix . 'file_archive_log';
    
    $expiring = $wpdb->get_results(
        "SELECT * FROM $table 
        WHERE deleted_date IS NULL 
        AND archived_date < DATE_SUB(NOW(), INTERVAL 23 DAY)
        ORDER BY archived_date ASC
        LIMIT 10"
    );
    
    if (empty($expiring)) {
        return;
    }
    
    echo '<div class="widget-file-expiring">';
    echo '<h3>⚠️ File in Scadenza (< 7 giorni)</h3>';
    echo '<ul>';
    
    foreach ($expiring as $file) {
        $days_left = 30 - floor((time() - strtotime($file->archived_date)) / DAY_IN_SECONDS);
        echo '<li>';
        echo basename($file->archived_path) . ' - <strong>' . $days_left . ' giorni</strong>';
        echo '</li>';
    }
    
    echo '</ul>';
    echo '</div>';
}
```

### Checklist Recovery System

- [ ] Tabella `file_archive_log` creata
- [ ] Function `recupera_file_archiviato()` implementata
- [ ] REST API endpoint `/file-recovery` registrato
- [ ] Template part `file-recovery-section.php` creato
- [ ] JavaScript AJAX funzionante
- [ ] CSS styling applicato
- [ ] Shortcode `[file_recovery_list]` funzionante
- [ ] Widget file in scadenza per dashboard
- [ ] Test: archivia file, modifica, recupera versione precedente
- [ ] Test: verifica eliminazione automatica dopo 30 giorni
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
