# 🔒 Sicurezza, Performance e GDPR

> **Contesto**: Security hardening, ottimizzazione performance, compliance GDPR, accessibility

**Leggi anche**: 
- `03_Sistema_Utenti_Roles.md` per autenticazione
- `00_README_START_HERE.md` per KPI target

---

## 🛡 Sicurezza

### WPmuDEV Defender Pro

**Features attive:**

```
Security → Firewall
✅ IP Lockout (5 tentativi, 30 min ban)
✅ 404 Detection (blocca scan automatici)
✅ Geolocation Blocking (opzionale)
✅ Login Protection
✅ Two-Factor Authentication (per admin)

Security → Malware Scanning
✅ Scan automatici giornalieri
✅ Email alert su minacce
✅ Quarantine automatica file sospetti

Security → Audit Logging
✅ Log login/logout
✅ Log modifiche contenuti
✅ Log cambio ruoli utenti
```

### Security Headers

```php
// includes/security.php

function add_security_headers() {
    // HSTS - Force HTTPS
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    
    // XSS Protection
    header('X-XSS-Protection: 1; mode=block');
    
    // MIME sniffing protection
    header('X-Content-Type-Options: nosniff');
    
    // Referrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Permissions Policy
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}
add_action('send_headers', 'add_security_headers');
```

### Hardening wp-config.php

```php
// wp-config.php additions

// Disabilita editing file da backend
define('DISALLOW_FILE_EDIT', true);

// Forza SSL per admin
define('FORCE_SSL_ADMIN', true);

// Aumenta security keys (già generate)
// AUTH_KEY, SECURE_AUTH_KEY, LOGGED_IN_KEY, NONCE_KEY...

// Limita post revisions
define('WP_POST_REVISIONS', 5);

// Empty trash automaticamente dopo 7 giorni
define('EMPTY_TRASH_DAYS', 7);
```

### File Upload Validation

```php
// includes/security.php

function validate_file_upload($file) {
    // Allowed MIME types
    $allowed = array(
        'application/pdf',
        'image/jpeg',
        'image/png',
    );
    
    // Check MIME type
    if (!in_array($file['type'], $allowed)) {
        return new WP_Error('invalid_type', 'Tipo file non permesso');
    }
    
    // Check file size (max 10MB)
    if ($file['size'] > 10 * 1024 * 1024) {
        return new WP_Error('file_too_large', 'File troppo grande (max 10MB)');
    }
    
    // Check double extensions (.pdf.php)
    $filename = $file['name'];
    if (preg_match('/\.(php|phtml|php3|php4|php5|pl|py|jsp|asp|htm|html|shtml|sh|cgi)$/i', $filename)) {
        return new WP_Error('suspicious_extension', 'Estensione file sospetta');
    }
    
    return $file;
}
add_filter('wp_handle_upload_prefilter', 'validate_file_upload');
```

### SQL Injection Prevention

```php
// SEMPRE usa prepared statements

// ❌ SBAGLIATO
$wpdb->query("SELECT * FROM table WHERE id = {$_GET['id']}");

// ✅ CORRETTO
$wpdb->get_results($wpdb->prepare(
    "SELECT * FROM table WHERE id = %d",
    intval($_GET['id'])
));

// Per più parametri
$wpdb->prepare(
    "SELECT * FROM table WHERE user_id = %d AND status = %s",
    $user_id,
    $status
);
```

### XSS Prevention

```php
// SEMPRE escape output

// ❌ SBAGLIATO
echo $user_input;
echo '<a href="' . $url . '">';

// ✅ CORRETTO
echo esc_html($user_input);
echo '<a href="' . esc_url($url) . '">';
echo '<div class="' . esc_attr($class) . '">';
echo wp_kses_post($html_content); // Per HTML sicuro
```

---

## ⚡ Performance

### Target KPI

```
✅ Lighthouse Score: >90
✅ First Contentful Paint: <1.5s
✅ Time to Interactive: <3.5s
✅ Largest Contentful Paint: <2.5s
✅ Cumulative Layout Shift: <0.1
```

### Hummingbird Pro Configuration

```
Performance → Caching
✅ Page Caching (Enabled)
✅ Browser Caching (Enabled)
✅ Gravatar Caching (Enabled)

Performance → Asset Optimization
✅ Minify CSS
✅ Minify JavaScript
✅ Combine CSS files
✅ Defer non-critical CSS
✅ Inline Critical CSS
⬜ Combine JS (può rompere Alpine.js)

Performance → Advanced Tools
✅ Lazy Load Images
✅ Preload Critical Assets
✅ DNS Prefetch
```

### Object Caching (Redis)

**Già attivo su WPmuDEV hosting.**

```php
// Test Redis
if (function_exists('wp_cache_get')) {
    wp_cache_set('test_key', 'test_value', '', 3600);
    $value = wp_cache_get('test_key');
    echo $value; // Should output: test_value
}
```

### Database Query Optimization

```php
// ❌ Lento - Query multipli in loop
foreach ($posts as $post) {
    $meta = get_post_meta($post->ID, 'custom_field', true);
}

// ✅ Veloce - Single query con WP_Query
$args = array(
    'post_type' => 'protocollo',
    'posts_per_page' => 20,
);
$query = new WP_Query($args);

// ✅ Ancora meglio - Cache results
$transient_key = 'protocolli_home';
$protocolli = get_transient($transient_key);

if (false === $protocolli) {
    $protocolli = new WP_Query($args);
    set_transient($transient_key, $protocolli, 1 * HOUR_IN_SECONDS);
}
```

### Image Optimization (Smush Pro)

```
Media → Smush Pro
✅ Automatic compression on upload
✅ Strip EXIF data
✅ Lazy Load images
✅ WebP conversion
✅ Resize large images (max 1920px)
```

### CDN Configuration

**WPmuDEV CDN inclusa e attiva.**

```
Hosting → CDN
✅ Enable CDN
✅ Cache static assets
✅ GZIP compression
```

### Critical CSS

```php
// Inline critical CSS in <head>
function inline_critical_css() {
    $critical_css = file_get_contents(get_stylesheet_directory() . '/assets/css/critical.css');
    echo '<style id="critical-css">' . $critical_css . '</style>';
}
add_action('wp_head', 'inline_critical_css', 1);
```

### Font Loading Optimization

```html
<!-- Preconnect to font CDN -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<!-- Load fonts with display=swap -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
```

---

## ♿ Accessibility (WCAG 2.1 AA)

### Checklist Compliance

```
✅ Semantic HTML (header, nav, main, footer, article)
✅ ARIA labels su elementi interattivi
✅ Focus indicators visibili
✅ Keyboard navigation completa
✅ Contrasto colori minimo 4.5:1
✅ Alt text su tutte le immagini
✅ Form labels appropriate
✅ Skip links
✅ Responsive text sizing (no px fissi)
✅ Touch targets min 44x44px
```

### Skip Links

```php
// header.php - Prima di tutto

<a href="#main-content" class="skip-link">
    Salta al contenuto principale
</a>

<style>
.skip-link {
    position: absolute;
    top: -40px;
    left: 0;
    background: var(--color-primary);
    color: white;
    padding: 8px;
    z-index: 100;
}

.skip-link:focus {
    top: 0;
}
</style>
```

### Aria Labels

```html
<!-- Bottoni icon-only -->
<button aria-label="Chiudi menu">
    <i data-lucide="x"></i>
</button>

<!-- Link con context -->
<a href="/documento-123" aria-label="Leggi protocollo Igiene Mani">
    Leggi di più
</a>

<!-- Form fields -->
<label for="search-input">Cerca documenti</label>
<input type="search" id="search-input" aria-label="Cerca documenti">
```

### Contrasto Colori

```scss
// Tutti i colori rispettano WCAG AA (4.5:1)

// Testo su bianco
$color-text-primary: #1F2937; // Contrasto: 13.9:1 ✅
$color-text-secondary: #6B7280; // Contrasto: 5.6:1 ✅

// Bianco su primary
$color-primary: #ab1120; // Contrasto con white: 5.2:1 ✅

// Test con tool: https://webaim.org/resources/contrastchecker/
```

---

## 🔐 GDPR Compliance

### Privacy Policy

```
Pagina obbligatoria: /privacy-policy

Contenuti minimi:
- Titolare del trattamento
- Tipologie dati raccolti (email, nome, IP per analytics)
- Finalità trattamento (formazione, compliance, comunicazioni)
- Base giuridica (consenso, obbligo legale, interesse legittimo)
- Destinatari dati (WPmuDEV, Brevo, OneSignal - con DPA)
- Conservazione dati (durata account + 1 anno dopo eliminazione)
- Diritti utente (accesso, rettifica, cancellazione, portabilità)
- Contatti DPO (se presente)
```

### Cookie Consent

```php
// Se usi cookies non strettamente necessari, implementa banner

// Plugin consigliato: Complianz GDPR/CCPA
// Oppure custom con Alpine.js
```

### Right to Be Forgotten

```php
// includes/security.php

function gdpr_delete_user_data($user_id) {
    global $wpdb;
    
    // 1. Anonymizza analytics
    $wpdb->update(
        $wpdb->prefix . 'document_views',
        array('user_id' => 0, 'ip_address' => '0.0.0.0'),
        array('user_id' => $user_id),
        array('%d', '%s'),
        array('%d')
    );
    
    // 2. Rimuovi da Brevo
    wp_remote_request('https://api.brevo.com/v3/contacts/' . get_userdata($user_id)->user_email, array(
        'method' => 'DELETE',
        'headers' => array(
            'api-key' => BREVO_API_KEY,
        ),
    ));
    
    // 3. Rimuovi da OneSignal (se possibile via API)
    
    // 4. WordPress elimina automaticamente user meta e posts
}
add_action('delete_user', 'gdpr_delete_user_data');
```

### Data Portability

```php
// Endpoint per export dati utente
function gdpr_export_user_data($user_id) {
    if (!current_user_can('read')) {
        return new WP_Error('unauthorized', 'Non autorizzato');
    }
    
    $user = get_userdata($user_id);
    
    $data = array(
        'user_info' => array(
            'username' => $user->user_login,
            'email' => $user->user_email,
            'nome' => $user->first_name,
            'cognome' => $user->last_name,
        ),
        'custom_fields' => array(
            'stato' => get_field('stato_utente', 'user_' . $user_id),
            'udo' => get_field('udo_riferimento', 'user_' . $user_id),
            'profilo' => get_field('profilo_professionale', 'user_' . $user_id),
        ),
        'corsi_completati' => learndash_user_get_completed_courses($user_id),
        'certificati' => get_user_certificates($user_id),
        'documenti_visualizzati' => get_user_viewed_documents($user_id),
    );
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="user_data_' . $user_id . '.json"');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}
```

### Data Protection Agreement (DPA)

```
Fornitori terze parti con DPA necessario:
✅ WPmuDEV (hosting) - DPA disponibile
✅ Brevo (email) - DPA disponibile
✅ OneSignal (push) - DPA disponibile

Documentare tutti i DPA firmati e conservare copia.
```

---

## 📊 Monitoring

### Uptime Monitoring

**WPmuDEV include uptime monitoring.**

```
Hosting → Monitoring
✅ Check every 5 minutes
✅ Email alert se down >5 min
✅ Performance metrics
```

### Error Logging

```php
// wp-config.php

// Development
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

// Production
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', true); // Log solo
define('WP_DEBUG_DISPLAY', false);

// Log location: /wp-content/debug.log
```

### Performance Monitoring

```php
// Query Monitor plugin (solo staging/dev)
// Mostra:
// - Slow queries
// - HTTP requests
// - Hook execution time
// - PHP errors
```

---

## 🤖 Checklist per IA

Security:
- [ ] Sempre prepared statements SQL
- [ ] Sempre escape output HTML
- [ ] Sanitize input utente
- [ ] Validate file uploads
- [ ] Check permissions prima di azioni
- [ ] Nonce per form submissions
- [ ] HTTPS forzato
- [ ] Security headers attivi

Performance:
- [ ] Cache query pesanti (transients)
- [ ] Lazy load immagini
- [ ] Minify CSS/JS
- [ ] Combine files dove possibile
- [ ] CDN per assets statici
- [ ] Optimize database queries
- [ ] Lighthouse score >90

GDPR:
- [ ] Privacy policy pubblicata
- [ ] Cookie consent (se necessario)
- [ ] Right to be forgotten
- [ ] Data portability
- [ ] DPA con fornitori terzi
- [ ] Log consensi utente

---

**🔒 Piattaforma sicura, veloce e compliant.**
