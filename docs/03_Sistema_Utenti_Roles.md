# 👥 Sistema Utenti, Ruoli e Autenticazione

> **Contesto**: Gestione completa utenti, ruoli custom, login biometrico, membership

**Leggi anche**: 
- `05_Gestione_Frontend_Forms.md` per form gestione utenti
- `09_Sicurezza_Performance_GDPR.md` per security hardening

---

## 🎯 Overview Sistema Utenti

### Ruoli Definiti

1. **Administrator** (WordPress default) - Accesso completo backend
2. **Gestore Piattaforma** (custom) - Gestione frontend-only
3. **Utente Standard** (Subscriber custom) - Fruizione contenuti

### Caratteristiche Chiave

- **Login biometrico** (WebAuthn) per tutti gli utenti
- **Membership forzata**: tutto il sito dietro login
- **No auto-registrazione**: solo admin/gestore creano utenti
- **Custom fields** per profilazione utenti

---

## 👨‍💼 Ruolo: GESTORE PIATTAFORMA

### Descrizione
Ruolo per gestori della piattaforma. **Non accede al backend WordPress**, gestisce tutto via form frontend.

### Capabilities

```php
// includes/user-roles.php

function crea_ruolo_gestore_piattaforma() {
    // Rimuovi ruolo se esiste (per update)
    remove_role('gestore_piattaforma');
    
    // Crea ruolo con capabilities
    add_role(
        'gestore_piattaforma',
        'Gestore Piattaforma',
        array(
            'read' => true, // Accesso frontend
            
            // CPT Permissions
            'edit_posts' => true,
            'edit_published_posts' => true,
            'publish_posts' => true,
            'delete_posts' => true,
            'delete_published_posts' => true,
            
            // Upload files
            'upload_files' => true,
            
            // User management
            'list_users' => true,
            'create_users' => true,
            'edit_users' => true,
            'delete_users' => true,
            
            // Taxonomy management
            'manage_categories' => true,
            
            // Analytics view
            'view_analytics' => true, // Custom capability
        )
    );
}
add_action('init', 'crea_ruolo_gestore_piattaforma');
```

### Blocco Accesso Backend

```php
// includes/membership.php

function blocca_backend_gestore() {
    if (is_admin() && !defined('DOING_AJAX')) {
        $user = wp_get_current_user();
        
        if (in_array('gestore_piattaforma', $user->roles)) {
            wp_redirect(home_url());
            exit;
        }
    }
}
add_action('admin_init', 'blocca_backend_gestore');
```

### Dashboard Gestore (Frontend)

**Pagina dedicata:** `/dashboard-gestore`

**Accesso:**
- Form gestione documenti (Protocolli, Moduli, Convenzioni, etc)
- Gestione utenti CRUD
- Analytics visualizzazioni
- Invio notifiche custom

**Template:** `page-dashboard-gestore.php` (vedi `08_Pagine_Templates.md`)

---

## 👤 Ruolo: UTENTE STANDARD

### Descrizione
Dipendenti della Cooperativa. Fruiscono contenuti, seguono corsi, vedono documenti.

### Capabilities

```php
function modifica_subscriber_capabilities() {
    $role = get_role('subscriber');
    
    if ($role) {
        // Capabilities base
        $role->add_cap('read');
        
        // Visualizzazione documenti
        $role->add_cap('read_protocollo');
        $role->add_cap('read_modulo');
        $role->add_cap('read_convenzione');
        
        // Download propri certificati
        $role->add_cap('download_own_certificates');
        
        // NO edit/publish nulla
    }
}
add_action('init', 'modifica_subscriber_capabilities');
```

---

## 🔑 Custom Fields Utente (ACF)

### Definizione Fields

```php
// includes/acf-config.php

acf_add_local_field_group(array(
    'key' => 'group_user_fields',
    'title' => 'Dati Dipendente',
    'fields' => array(
        array(
            'key' => 'field_stato_utente',
            'label' => 'Stato Utente',
            'name' => 'stato_utente',
            'type' => 'radio',
            'choices' => array(
                'attivo' => 'Attivo',
                'sospeso' => 'Sospeso',
                'licenziato' => 'Licenziato',
            ),
            'default_value' => 'attivo',
            'layout' => 'horizontal',
            'required' => 1,
        ),
        array(
            'key' => 'field_link_autologin',
            'label' => 'Link Autologin Esterno',
            'name' => 'link_autologin_esterno',
            'type' => 'url',
            'instructions' => 'URL piattaforma formazione certificata esterna (SSO)',
        ),
        array(
            'key' => 'field_profilo_professionale',
            'label' => 'Profilo Professionale',
            'name' => 'profilo_professionale',
            'type' => 'taxonomy',
            'taxonomy' => 'profili_professionali',
            'field_type' => 'select',
            'return_format' => 'id',
            'required' => 1,
        ),
        array(
            'key' => 'field_udo_riferimento',
            'label' => 'Unità di Offerta',
            'name' => 'udo_riferimento',
            'type' => 'taxonomy',
            'taxonomy' => 'unita_offerta',
            'field_type' => 'select',
            'return_format' => 'id',
            'required' => 1,
        ),
    ),
    'location' => array(
        array(
            array(
                'param' => 'user_form',
                'operator' => '==',
                'value' => 'edit', // Profilo utente
            ),
        ),
    ),
));
```

### Uso Custom Fields

```php
// Get profilo professionale utente corrente
$user_id = get_current_user_id();
$profilo = get_field('profilo_professionale', 'user_' . $user_id);
$udo = get_field('udo_riferimento', 'user_' . $user_id);
$stato = get_field('stato_utente', 'user_' . $user_id);

// Check se attivo
if ($stato === 'attivo') {
    // Mostra contenuti
}
```

---

## 🔐 Login Biometrico (WebAuthn)

### Plugin: WP WebAuthn

**Installazione:**
```bash
wp plugin install wp-webauthn --activate
```

### Come Funziona

1. **Primo accesso utente:**
   - Login classico (username/password)
   - Prompt registrazione device biometrico
   - Browser genera coppia chiavi pubblica/privata
   - Chiave privata resta nel device (Secure Enclave)
   - Chiave pubblica salvata su server WordPress

2. **Login successivi:**
   - Utente clicca "Login con biometria"
   - Richiesta verifica biometrica (Face ID/Impronta)
   - Device firma challenge con chiave privata
   - Server verifica firma con chiave pubblica
   - Accesso consentito

### Configurazione Plugin

**Settings → WP WebAuthn:**
```
✅ Enable WebAuthn login
✅ Force WebAuthn for all users (dopo primo setup)
✅ Show WebAuthn button on login page
✅ Remember device for 30 days
⬜ Allow password login as fallback (solo per admin)
```

### Personalizzazione Login Page

```php
// includes/membership.php

function custom_login_page_style() {
    ?>
    <style>
        /* Custom login page style */
        body.login {
            background: linear-gradient(135deg, #ab1120 0%, #8a0e1a 100%);
        }
        
        #login h1 a {
            background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/assets/images/logo-white.svg);
            background-size: contain;
            width: 200px;
            height: 80px;
        }
        
        .login form {
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
        }
    </style>
    <?php
}
add_action('login_enqueue_scripts', 'custom_login_page_style');
```

### Privacy & Security

**Vantaggi WebAuthn:**
- ✅ Zero dati biometrici sul server
- ✅ Solo chiave pubblica matematica salvata
- ✅ Impossibile "rubare" impronte/facciali
- ✅ GDPR compliant (privacy by design)
- ✅ Phishing-resistant (dominio-specifico)
- ✅ Standard W3C aperto

**Costo:** €0 - Standard nativo browser

---

## 🚪 Membership Logic

### Forza Login Globale

```php
// includes/membership.php

function forza_login_globale() {
    // Escludi pagine pubbliche
    $public_pages = array(
        'wp-login.php',
        'wp-register.php', // Se dovesse servire in futuro
    );
    
    $current_page = basename($_SERVER['REQUEST_URI']);
    
    if (!is_user_logged_in() && !in_array($current_page, $public_pages)) {
        wp_redirect(wp_login_url());
        exit;
    }
}
add_action('template_redirect', 'forza_login_globale');
```

### Nascondi Admin Bar per Utenti Standard

```php
function nascondi_admin_bar() {
    $user = wp_get_current_user();
    
    if (in_array('subscriber', $user->roles) || in_array('gestore_piattaforma', $user->roles)) {
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'nascondi_admin_bar');
```

### Redirect dopo Login

```php
function redirect_dopo_login($redirect_to, $request, $user) {
    // Admin → backend
    if (in_array('administrator', $user->roles)) {
        return admin_url();
    }
    
    // Gestore → dashboard frontend
    if (in_array('gestore_piattaforma', $user->roles)) {
        return home_url('/dashboard-gestore');
    }
    
    // Utente standard → home
    return home_url();
}
add_filter('login_redirect', 'redirect_dopo_login', 10, 3);
```

---

## 👥 Gestione Utenti Frontend

### Form Creazione Utente (per Gestore)

**Via ACF Frontend Form** (vedi `05_Gestione_Frontend_Forms.md` per dettagli completi)

```php
// Shortcode form nuovo utente
function shortcode_form_nuovo_utente() {
    // Check permission
    if (!current_user_can('create_users')) {
        return 'Accesso negato.';
    }
    
    acf_form(array(
        'post_id' => 'new_user',
        'new_post' => array(
            'post_type' => 'user', // Special ACF handling
        ),
        'fields' => array(
            'field_stato_utente',
            'field_profilo_professionale',
            'field_udo_riferimento',
            'field_link_autologin',
        ),
        'submit_value' => 'Crea Utente',
        'updated_message' => 'Utente creato con successo!',
    ));
}
add_shortcode('form_nuovo_utente', 'shortcode_form_nuovo_utente');
```

### Hook Creazione Utente

```php
// includes/user-roles.php

function dopo_creazione_utente($user_id) {
    // 1. Invia email credenziali
    $user = get_userdata($user_id);
    $password_reset_link = wp_lostpassword_url();
    
    wp_mail(
        $user->user_email,
        'Benvenuto in Piattaforma Formazione',
        "Ciao {$user->display_name},\n\nIl tuo account è stato creato.\nImposta la tua password: {$password_reset_link}"
    );
    
    // 2. Auto-enrollment corsi obbligatori (vedi 07_Notifiche_Automazioni.md)
    autoenroll_corsi_obbligatori($user_id);
    
    // 3. Sync con Brevo
    sync_utente_brevo($user_id);
}
add_action('user_register', 'dopo_creazione_utente');
```

---

## 🔍 Query Utenti

### Filtrare per Custom Field

```php
// Get tutti utenti attivi
$args = array(
    'meta_query' => array(
        array(
            'key' => 'stato_utente',
            'value' => 'attivo',
            'compare' => '='
        ),
    ),
);
$utenti_attivi = get_users($args);
```

### Filtrare per Profilo/UDO

```php
// Utenti di una specifica UDO
$args = array(
    'meta_query' => array(
        array(
            'key' => 'udo_riferimento',
            'value' => $udo_term_id,
            'compare' => '='
        ),
    ),
);
$utenti_udo = get_users($args);
```

---

## 🛡 Security Best Practices

### Password Policy

```php
function strong_password_policy($errors, $update, $user) {
    if (!empty($_POST['pass1'])) {
        $password = $_POST['pass1'];
        
        // Minimo 12 caratteri
        if (strlen($password) < 12) {
            $errors->add('password_too_short', 'Password deve essere almeno 12 caratteri.');
        }
        
        // Deve contenere maiuscole, minuscole, numeri
        if (!preg_match('/[A-Z]/', $password) || 
            !preg_match('/[a-z]/', $password) || 
            !preg_match('/[0-9]/', $password)) {
            $errors->add('password_weak', 'Password deve contenere maiuscole, minuscole e numeri.');
        }
    }
    
    return $errors;
}
add_filter('user_profile_update_errors', 'strong_password_policy', 10, 3);
```

### Session Timeout

```php
function session_timeout() {
    // Logout automatico dopo 8 ore inattività
    if (is_user_logged_in()) {
        $timeout = 8 * HOUR_IN_SECONDS;
        $last_activity = get_user_meta(get_current_user_id(), 'last_activity', true);
        
        if ($last_activity && (time() - $last_activity > $timeout)) {
            wp_logout();
            wp_redirect(wp_login_url() . '?timeout=1');
            exit;
        }
        
        // Aggiorna last activity
        update_user_meta(get_current_user_id(), 'last_activity', time());
    }
}
add_action('init', 'session_timeout');
```

### Limita Tentativi Login

**Via Plugin:** Defender Pro (WPmuDEV) gestisce automaticamente.

**Configurazione Defender:**
```
Security → Firewall → Login Protection
✅ Enable login protection
Max attempts: 5
Lockout duration: 30 minutes
✅ Log failed attempts
```

---

## 🤖 Checklist per IA

Quando lavori con utenti/auth:

- [ ] Mai hard-codare ruoli, usa `current_user_can()`
- [ ] Sempre check permissions prima di form/azioni
- [ ] `get_current_user_id()` per utente corrente
- [ ] User meta: usa prefix `user_` (es: `'user_' . $user_id`)
- [ ] Password reset: usa `wp_lostpassword_url()`
- [ ] Email utente: sempre validate con `is_email()`
- [ ] Sanitize input: `sanitize_email()`, `sanitize_text_field()`
- [ ] Escape output: `esc_html()`, `esc_attr()`
- [ ] Test con utenti di ruoli diversi
- [ ] Log azioni critiche (creazione/eliminazione utenti)

---

**👥 Sistema utenti completo, sicuro e scalabile.**
