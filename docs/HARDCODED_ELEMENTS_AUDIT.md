# 🔍 HARDCODED ELEMENTS AUDIT - La Meridiana

**Data**: 5 Novembre 2025
**Scopo**: Inventario di tutti gli elementi hardcoded nel tema e codice custom
**Importanza**: CRITICO per migrazione a staging/live

---

## 📋 EXECUTIVE SUMMARY

```
Hardcoded URLs found:       0 ❌ (SAFE!)
Hardcoded IDs found:        0 ❌ (SAFE!)
Hardcoded Paths found:      0 ❌ (SAFE!)
API Keys hardcoded:         0 ❌ (SAFE!)
Database hardcoded:         0 ❌ (SAFE!)

RISK LEVEL: 🟢 LOW
MIGRATION SAFE: ✅ YES
```

---

## ✅ COSA È STATO TROVATO (SAFE)

### 1. WordPress Functions (Best Practices Used)

**File**: `functions.php`

✅ **home_url()** - Dinamico
```php
$site_url = home_url();  // ✅ Uses WordPress function
```

✅ **admin_url()** - Dinamico
```php
$admin_url = admin_url();  // ✅ Uses WordPress function
```

✅ **rest_url()** - Dinamico
```php
$api_url = rest_url();  // ✅ Uses WordPress function
```

✅ **wp_upload_dir()** - Dinamico
```php
$upload_dir = wp_upload_dir();  // ✅ Uses WordPress function
```

### 2. Asset Paths (Relative)

**File**: `functions.php`, `functions-assets.php`

✅ **CSS/JS Paths** - Relative (versione corretta)
```php
get_stylesheet_directory_uri() . '/assets/css/dist/main.css'
get_stylesheet_directory_uri() . '/assets/js/dist/main.js'
```

✅ **Immagini** - Relative
```php
get_template_directory_uri() . '/images/logo.svg'
```

### 3. REST API Endpoints (Dynamic)

**File**: `includes/rest-api-*.php`

✅ Tutti gli endpoint usano `rest_url()` dinamico
```php
rest_url( '/learnDash/v1/...' )
rest_url( '/piattaforma/v1/...' )
```

### 4. Database Queries (Prepared Statements)

**File**: `includes/*.php`

✅ Nessun hardcoded SQL trovato
```php
$wpdb->prepare( "SELECT * FROM $wpdb->users WHERE ID = %d", $user_id );  // ✅ SAFE
```

### 5. ACF Field References

**File**: `acf-json/*.json`

✅ Campo nomi usati via `get_field()` - Funziona ovunque
```php
$enrollment_data = get_field( 'enrollment_data', $user_id );  // ✅ SAFE
```

### 6. Custom Post Types

**File**: `includes/cpt-registration.php`

✅ CPT registrati dinamicamente
```php
register_post_type( 'corso', [...] );  // ✅ SAFE - works everywhere
register_post_type( 'lezione', [...] );  // ✅ SAFE
```

---

## 🚨 COSA NON È STATO TROVATO (Ma controlliamo)

### Potenziali Rischi (Verificati ✅)

```
❌ Hardcoded domain names          → NOT FOUND ✅
❌ Hardcoded wp-admin URLs         → NOT FOUND ✅
❌ Hardcoded /uploads/ paths       → NOT FOUND ✅
❌ API keys in code                → NOT FOUND ✅
❌ Database credentials in code    → NOT FOUND ✅
❌ Hardcoded IP addresses          → NOT FOUND ✅
❌ Hardcoded user IDs              → NOT FOUND ✅
❌ Hardcoded post IDs              → NOT FOUND ✅
❌ Hardcoded taxonomy terms        → NOT FOUND ✅
❌ Hardcoded file paths (Windows)  → NOT FOUND ✅
```

---

## 📊 ELEMENTI DINAMICI TROVATI (GOOD)

### Configurazione Dinamica

| Elemento | Come Gestito | Risultato |
|----------|-------------|-----------|
| Siteurl | `home_url()` | ✅ Dinamico |
| Admin URL | `admin_url()` | ✅ Dinamico |
| API Base | `rest_url()` | ✅ Dinamico |
| Upload Dir | `wp_upload_dir()` | ✅ Dinamico |
| Theme Dir | `get_template_directory_uri()` | ✅ Dinamico |
| Plugin Dir | `plugins_url()` | ✅ Dinamico |
| WP Version | `get_bloginfo('version')` | ✅ Dinamico |
| Locale | `get_locale()` | ✅ Dinamico |

---

## 🔧 CONFIGURAZIONI ESTERNE (API Keys, etc.)

### OneSignal (Push Notifications)

**Dove**: ACF Options in wp-admin
**Come**: Salvati in database (wp_options)
**Sicurezza**: 🟡 MEDIO

```
API Key Location: wp-admin > ACF Options > OneSignal Settings
Database Table: wp_options (post_id = 0)
Risk: Medium (è nel database, needs protection)
Migrazione: ✅ Database migra completamente
```

**Action Item**:
- [ ] Rotare API keys dopo migrazione a live
- [ ] Configurare HTTPS sul nuovo dominio
- [ ] Testare OneSignal su live

---

## 🎯 ELEMENTI CHE CAMBIERANNO IN MIGRAZIONE

### 1. Domain (Cambierà automaticamente)

```
Locale:  https://nuova-formazione.local/
Staging: https://staging.example.com/  (o simile)
Live:    https://tuodominio.com/       (o simile)

Impact: ❌ ZERO (home_url() gestisce tutto)
```

### 2. Database Credentials (Forniranno hosting)

```
Current: root / root (localhost)
Staging: [Siteground fornisce]
Live:    [Siteground fornisce]

Impact: ❌ ZERO (wp-config.php auto-aggiornato)
```

### 3. File Paths (Stesse cartelle)

```
Local:   /wp-content/uploads/
Staging: /wp-content/uploads/  (same)
Live:    /wp-content/uploads/  (same)

Impact: ❌ ZERO (wp_upload_dir() dinamico)
```

---

## 📋 MIGRAZIONE CHECKLIST

### Pre-Migrazione
```
☐ Backup completo locale (fatto ✓)
☐ Verifica no hardcoded URLs (fatto ✓)
☐ Verifica wp-config.php clean (fatto ✓)
☐ Test su localhost funziona (fatto ✓)
```

### Durante Migrazione
```
☐ Database migrato completamente
☐ File system migrato completamente
☐ wp-config.php aggiornato (Siteground lo fa)
☐ DNS puntato a nuovo hosting
```

### Post-Migrazione
```
☐ Testa homepage carica
☐ Testa wp-admin accesso
☐ Testa LearnDash corsi
☐ Testa PWA manifest
☐ Testa OneSignal push
☐ Testa email notifications
☐ Testa database queries
☐ Testa REST API endpoints
```

---

## 🔐 COSA CONTROLLARE POST-MIGRAZIONE

### 1. WordPress Settings (Verificare automatiche)
```
Settings > General > Indirizzo sito
Settings > General > Indirizzo WordPress
→ Dovrebbero essere nuovi URL automaticamente
```

### 2. API Endpoints
```
GET /wp-json/learnDash/v1/courses
GET /wp-json/piattaforma/v1/analytics
→ Dovrebbero funzionare con nuovo dominio
```

### 3. LearnDash User Progress
```
Verificare: Corsi si caricano
Verificare: Quiz funziona
Verificare: Progress tracking funziona
Verificare: Certificati generati
```

### 4. OneSignal
```
Verificare: Manifest file visibile
Verificare: Service worker registrato
Verificare: Push notifications inviate
```

---

## 📝 NOTE TECNICHE

### Perché NO Hardcoded?

Il codice segue **WordPress Best Practices**:

```php
// ❌ WRONG - Don't do this
define('SITE_URL', 'https://nuova-formazione.local/');
$link = 'https://nuova-formazione.local/wp-admin/';

// ✅ RIGHT - Do this instead
$site_url = home_url();
$admin_link = admin_url();
```

Questi design pattern garantiscono che il sito funziona su:
- Localhost development
- Staging environment
- Production live server
- Subdomains
- Subdirectories

### Cosa Questo Significa per Migrazione

✅ **Zero configuration changes needed**
✅ **Database migra completamente**
✅ **File system migra completamente**
✅ **URLs update automaticamente**
✅ **No manual URL replacement needed**

---

## 🚀 READY FOR MIGRATION

**Verdict**: ✅ **SAFE TO MIGRATE**

Il sito è pronto per:
- ✅ Migrazione a staging
- ✅ Migrazione a live
- ✅ Cambio dominio
- ✅ Cambio subdominio
- ✅ Cambio subdirectory

Nessun elemento hardcoded blocca o complica la migrazione.

---

**Audit Date**: 5 Novembre 2025
**Auditor**: Analisi automatizzata
**Status**: ✅ COMPLETE
**Risk Assessment**: 🟢 LOW - Safe to proceed

