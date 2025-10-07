# 🔔 Notifiche e Automazioni

> **Contesto**: Sistema notifiche push (OneSignal), email (Brevo), automazioni trigger

**Leggi anche**: 
- `03_Sistema_Utenti_Roles.md` per sync utenti
- `02_Struttura_Dati_CPT.md` per trigger su CPT

---

## 🎯 Overview Sistema Notifiche

### Canali
1. **Push Notifications** (OneSignal) - PWA
2. **Email** (Brevo) - Transazionali e digest

### Trigger
- Nuovi contenuti (Protocolli, Comunicazioni, Convenzioni)
- Corsi in scadenza (7 giorni prima)
- Certificati scaduti
- Messaggi custom da Gestore

---

## 📱 Push Notifications (OneSignal)

### Setup OneSignal

```php
// includes/notifications.php

define('ONESIGNAL_APP_ID', 'your-app-id');
define('ONESIGNAL_API_KEY', 'your-api-key');

function invia_push_notification($heading, $content, $url, $segments = ['All']) {
    $response = wp_remote_post('https://onesignal.com/api/v1/notifications', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . ONESIGNAL_API_KEY,
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'app_id' => ONESIGNAL_APP_ID,
            'included_segments' => $segments,
            'headings' => array('it' => $heading),
            'contents' => array('it' => $content),
            'url' => $url,
            'chrome_web_icon' => get_stylesheet_directory_uri() . '/assets/images/logo.png',
        )),
    ));
    
    if (is_wp_error($response)) {
        error_log('OneSignal error: ' . $response->get_error_message());
        return false;
    }
    
    return true;
}
```

### Trigger: Nuovo Protocollo

```php
function notifica_nuovo_protocollo($post_id, $post) {
    if ($post->post_type !== 'protocollo') {
        return;
    }
    
    if ($post->post_status !== 'publish') {
        return;
    }
    
    // Evita notifica su update
    if (get_post_meta($post_id, '_notified', true)) {
        return;
    }
    
    invia_push_notification(
        'Nuovo Protocollo Pubblicato',
        $post->post_title,
        get_permalink($post_id)
    );
    
    update_post_meta($post_id, '_notified', 1);
}
add_action('publish_protocollo', 'notifica_nuovo_protocollo', 10, 2);
```

### Trigger: Nuova Comunicazione

```php
function notifica_nuova_comunicazione($post_id, $post) {
    if ($post->post_type !== 'post') {
        return;
    }
    
    if (get_post_meta($post_id, '_notified', true)) {
        return;
    }
    
    invia_push_notification(
        'Nuova Comunicazione',
        $post->post_title,
        get_permalink($post_id)
    );
    
    update_post_meta($post_id, '_notified', 1);
}
add_action('publish_post', 'notifica_nuova_comunicazione', 10, 2);
```

### Trigger: Nuova Convenzione

```php
function notifica_nuova_convenzione($post_id, $post) {
    if ($post->post_type !== 'convenzione') {
        return;
    }
    
    // Solo se attiva
    if (!get_field('convenzione_attiva', $post_id)) {
        return;
    }
    
    if (get_post_meta($post_id, '_notified', true)) {
        return;
    }
    
    invia_push_notification(
        'Nuova Convenzione Disponibile',
        $post->post_title,
        get_permalink($post_id)
    );
    
    update_post_meta($post_id, '_notified', 1);
}
add_action('publish_convenzione', 'notifica_nuova_convenzione', 10, 2);
```

### Notifica Custom (Form Gestore)

```php
// Shortcode form notifica custom
function shortcode_form_notifica_custom() {
    if (!current_user_can('view_analytics')) {
        return 'Accesso negato.';
    }
    
    ob_start();
    ?>
    <form method="post" class="form-notifica">
        <?php wp_nonce_field('send_notification', 'notification_nonce'); ?>
        
        <div class="input-group">
            <label>Titolo</label>
            <input type="text" name="notification_title" class="input-field" required>
        </div>
        
        <div class="input-group">
            <label>Messaggio</label>
            <textarea name="notification_content" class="textarea" required></textarea>
        </div>
        
        <div class="input-group">
            <label>Link (opzionale)</label>
            <input type="url" name="notification_url" class="input-field">
        </div>
        
        <button type="submit" name="send_notification" class="btn btn-primary">
            Invia Notifica a Tutti
        </button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('form_notifica_custom', 'shortcode_form_notifica_custom');

// Process form
function process_notifica_custom() {
    if (!isset($_POST['send_notification'])) {
        return;
    }
    
    if (!wp_verify_nonce($_POST['notification_nonce'], 'send_notification')) {
        return;
    }
    
    if (!current_user_can('view_analytics')) {
        wp_die('Permessi insufficienti');
    }
    
    $title = sanitize_text_field($_POST['notification_title']);
    $content = sanitize_textarea_field($_POST['notification_content']);
    $url = esc_url_raw($_POST['notification_url']) ?: home_url();
    
    invia_push_notification($title, $content, $url);
    
    wp_redirect(add_query_arg('notification_sent', '1', $_SERVER['REQUEST_URI']));
    exit;
}
add_action('init', 'process_notifica_custom');
```

---

## 📧 Email Notifications (Brevo)

### Setup Brevo

```php
// includes/notifications.php

define('BREVO_API_KEY', 'your-api-key');
define('BREVO_LISTA_ID', 123); // ID lista dipendenti

function invia_email_brevo($to_email, $to_name, $subject, $html_content, $template_id = null) {
    $data = array(
        'sender' => array(
            'name' => 'Cooperativa La Meridiana',
            'email' => 'noreply@cooperativaemeridiana.it',
        ),
        'to' => array(
            array(
                'email' => $to_email,
                'name' => $to_name,
            ),
        ),
        'subject' => $subject,
    );
    
    if ($template_id) {
        $data['templateId'] = $template_id;
    } else {
        $data['htmlContent'] = $html_content;
    }
    
    $response = wp_remote_post('https://api.brevo.com/v3/smtp/email', array(
        'headers' => array(
            'api-key' => BREVO_API_KEY,
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode($data),
    ));
    
    if (is_wp_error($response)) {
        error_log('Brevo error: ' . $response->get_error_message());
        return false;
    }
    
    return true;
}
```

### Sync Utente con Brevo

```php
function sync_utente_brevo($user_id) {
    $user = get_userdata($user_id);
    
    $udo = get_field('udo_riferimento', 'user_' . $user_id);
    $udo_name = $udo ? get_term($udo)->name : '';
    
    $profilo = get_field('profilo_professionale', 'user_' . $user_id);
    $profilo_name = $profilo ? get_term($profilo)->name : '';
    
    wp_remote_post('https://api.brevo.com/v3/contacts', array(
        'headers' => array(
            'api-key' => BREVO_API_KEY,
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'email' => $user->user_email,
            'attributes' => array(
                'NOME' => $user->first_name,
                'COGNOME' => $user->last_name,
                'UDO' => $udo_name,
                'PROFILO' => $profilo_name,
            ),
            'listIds' => array(BREVO_LISTA_ID),
            'updateEnabled' => true,
        )),
    ));
}
add_action('user_register', 'sync_utente_brevo');
add_action('profile_update', 'sync_utente_brevo');
```

### Email: Nuovo Utente

```php
function email_nuovo_utente($user_id) {
    $user = get_userdata($user_id);
    $password_reset_link = wp_lostpassword_url();
    
    $subject = 'Benvenuto in Piattaforma Formazione';
    $message = "
    <h2>Benvenuto, {$user->first_name}!</h2>
    <p>Il tuo account sulla Piattaforma Formazione è stato creato con successo.</p>
    <p><strong>Username:</strong> {$user->user_login}<br>
    <strong>Email:</strong> {$user->user_email}</p>
    <p>Per impostare la tua password, clicca sul link:</p>
    <p><a href='{$password_reset_link}'>Imposta Password</a></p>
    <p>Una volta impostata la password, potrai accedere con le tue impronte digitali o Face ID.</p>
    ";
    
    invia_email_brevo(
        $user->user_email,
        $user->display_name,
        $subject,
        $message
    );
}
add_action('user_register', 'email_nuovo_utente');
```

### Email: Digest Settimanale

```php
// Cron job ogni lunedì 9:00
function schedule_weekly_digest() {
    if (!wp_next_scheduled('send_weekly_digest')) {
        wp_schedule_event(strtotime('next Monday 9:00'), 'weekly', 'send_weekly_digest');
    }
}
add_action('init', 'schedule_weekly_digest');

function send_weekly_digest() {
    // Get contenuti ultimi 7 giorni
    $date_from = date('Y-m-d', strtotime('-7 days'));
    
    $new_docs = get_posts(array(
        'post_type' => array('protocollo', 'modulo'),
        'date_query' => array(
            array('after' => $date_from),
        ),
        'posts_per_page' => -1,
    ));
    
    $new_comm = get_posts(array(
        'post_type' => 'post',
        'date_query' => array(
            array('after' => $date_from),
        ),
        'posts_per_page' => -1,
    ));
    
    if (empty($new_docs) && empty($new_comm)) {
        return; // Niente da inviare
    }
    
    // Build HTML
    $html = '<h2>Riepilogo Settimanale</h2>';
    
    if (!empty($new_docs)) {
        $html .= '<h3>Nuovi Documenti</h3><ul>';
        foreach ($new_docs as $doc) {
            $html .= '<li><a href="' . get_permalink($doc) . '">' . $doc->post_title . '</a></li>';
        }
        $html .= '</ul>';
    }
    
    if (!empty($new_comm)) {
        $html .= '<h3>Nuove Comunicazioni</h3><ul>';
        foreach ($new_comm as $comm) {
            $html .= '<li><a href="' . get_permalink($comm) . '">' . $comm->post_title . '</a></li>';
        }
        $html .= '</ul>';
    }
    
    // Invia a tutti gli utenti attivi
    $users = get_users(array(
        'meta_query' => array(
            array(
                'key' => 'stato_utente',
                'value' => 'attivo',
            ),
        ),
    ));
    
    foreach ($users as $user) {
        invia_email_brevo(
            $user->user_email,
            $user->display_name,
            'Riepilogo Settimanale - Piattaforma Formazione',
            $html
        );
    }
}
add_action('send_weekly_digest', 'send_weekly_digest');
```

---

## 🎓 Automazioni Corsi

### Auto-Enrollment Nuovi Utenti

```php
function autoenroll_corsi_obbligatori($user_id) {
    // Query corsi obbligatori interni
    $corsi = get_posts(array(
        'post_type' => 'sfwd-courses',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'tipologia_corso',
                'field' => 'slug',
                'terms' => 'obbligatori-interni',
            ),
        ),
    ));
    
    foreach ($corsi as $corso) {
        // LearnDash function
        ld_update_course_access($user_id, $corso->ID);
    }
}
add_action('user_register', 'autoenroll_corsi_obbligatori');
```

### Alert Scadenza Certificato

```php
// Cron giornaliero
function check_certificati_in_scadenza() {
    if (!wp_next_scheduled('check_certificati_scadenza')) {
        wp_schedule_event(time(), 'daily', 'check_certificati_scadenza');
    }
}
add_action('init', 'check_certificati_in_scadenza');

function send_certificati_alerts() {
    $users = get_users();
    
    foreach ($users as $user) {
        // Get corsi completati
        $completed_courses = learndash_user_get_completed_courses($user->ID);
        
        foreach ($completed_courses as $course_id) {
            $completion_date = get_user_meta($user->ID, 'course_completed_' . $course_id, true);
            
            if (!$completion_date) continue;
            
            // Validità 1 anno
            $expiry_date = strtotime('+1 year', $completion_date);
            $days_to_expiry = ($expiry_date - time()) / DAY_IN_SECONDS;
            
            // Alert 7 giorni prima
            if ($days_to_expiry <= 7 && $days_to_expiry > 0) {
                $course = get_post($course_id);
                
                // Push notification
                invia_push_notification(
                    'Certificato in Scadenza',
                    "Il certificato '{$course->post_title}' scade tra " . ceil($days_to_expiry) . " giorni.",
                    get_permalink($course_id)
                );
                
                // Email
                invia_email_brevo(
                    $user->user_email,
                    $user->display_name,
                    'Certificato in Scadenza',
                    "<p>Il tuo certificato <strong>{$course->post_title}</strong> scade tra " . ceil($days_to_expiry) . " giorni.</p>
                    <p><a href='" . get_permalink($course_id) . "'>Rinnova ora</a></p>"
                );
            }
            
            // Alert se scaduto
            if ($days_to_expiry <= 0) {
                // Re-enroll automaticamente
                ld_update_course_access($user->ID, $course_id, true);
                
                // Notifica urgente
                invia_push_notification(
                    'Certificato Scaduto - Azione Richiesta',
                    "Il certificato '{$course->post_title}' è scaduto. Completa nuovamente il corso.",
                    get_permalink($course_id)
                );
            }
        }
    }
}
add_action('check_certificati_scadenza', 'send_certificati_alerts');
```

---

## 🗑 Pulizia File Archiviati

```php
// Già schedulato in file-management.php (vedi 05_Gestione_Frontend_Forms.md)
// Cron giornaliero elimina file in /archive più vecchi di 30 giorni

function cleanup_archived_files() {
    $upload_dir = wp_upload_dir();
    $archive_dir = $upload_dir['basedir'] . '/archive/';
    
    if (!is_dir($archive_dir)) {
        return;
    }
    
    $files = glob($archive_dir . '*');
    $now = time();
    
    foreach ($files as $file) {
        if (is_file($file)) {
            $file_time = filemtime($file);
            
            // Se più vecchio di 30 giorni
            if ($now - $file_time >= (30 * DAY_IN_SECONDS)) {
                unlink($file);
                error_log("File archiviato eliminato: " . basename($file));
            }
        }
    }
}

// Schedule
if (!wp_next_scheduled('cleanup_archived_files')) {
    wp_schedule_event(time(), 'daily', 'cleanup_archived_files');
}
add_action('cleanup_archived_files', 'cleanup_archived_files');
```

---

## 🤖 Checklist per IA

Quando lavori con notifiche/automazioni:

- [ ] OneSignal: sempre check response errors
- [ ] Brevo: gestisci rate limits (300/day free tier)
- [ ] Email: usa template HTML responsive
- [ ] Push: max 240 caratteri per content
- [ ] Cron: usa `wp_schedule_event` non system cron
- [ ] Evita duplicate notifications (check _notified meta)
- [ ] Log notifiche inviate per debug
- [ ] Test con utenti reali per timing
- [ ] Unsub link in digest email (GDPR)
- [ ] Fallback se servizio notifiche down

---

**🔔 Sistema notifiche completo e affidabile.**
