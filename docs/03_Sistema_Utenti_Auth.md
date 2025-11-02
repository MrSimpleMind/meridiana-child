# 👥 Sistema Utenti, Ruoli e Autenticazione

> **Ultimo aggiornamento**: 1 Novembre 2025
> **Fonte**: `includes/user-roles.php`, `includes/membership.php`, `acf-json/group_user_fields.json`

**Leggi anche**:
- `05_Gestione_Frontend_Forms.md` per i form di gestione utenti
- `09_Sicurezza_Performance.md` per le pratiche di hardening

---

## 🎯 Overview del Sistema Utenti

### Ruoli Definiti

1.  **Administrator** (WordPress default) - Accesso completo al backend, gestione totale.
2.  **Gestore Piattaforma** (`gestore_piattaforma`) - Ruolo custom per la gestione dei contenuti e degli utenti esclusivamente dal frontend.
3.  **Utente Standard** (`subscriber`) - Ruolo di base per i dipendenti, con permessi di lettura estesi.

### Caratteristiche Chiave

- **Membership Forzata**: L'intero sito è accessibile solo dopo il login. Gli utenti non autenticati vengono reindirizzati alla pagina di login.
- **Registrazione Chiusa**: Non è permessa l'auto-registrazione. Gli utenti vengono creati esclusivamente dall'Amministratore o dal Gestore Piattaforma.
- **Profilazione Utente**: Campi custom ACF per profilare ogni utente in base a stato, profilo professionale e unità di offerta.
- **Sicurezza**: Blocco dell'accesso al backend per i non-amministratori e rimozione della admin bar.

---

## 👨‍💼 Ruolo: Gestore Piattaforma (`gestore_piattaforma`)

### Descrizione
Questo ruolo è pensato per i responsabili della gestione dei contenuti della piattaforma. **Non ha accesso al backend di WordPress** e opera interamente tramite la **Dashboard Gestore** (`/dashboard-gestore/`) disponibile nel frontend.

### Capabilities

Le capabilities sono definite in `includes/user-roles.php`.

```php
$gestore_capabilities = array(
    // Permessi di base
    'read' => true,

    // Gestione contenuti e utenti dal frontend
    'gestione_frontend' => true, // Capability custom per l'accesso ai form
    'view_analytics' => true,      // Visualizzazione della dashboard analytics
    'manage_platform_users' => true, // CRUD utenti
    'manage_platform' => true,      // Accesso generale alla dashboard gestore

    // Permessi sui post (necessari per le operazioni di ACF e AJAX)
    'edit_posts'   => false, // Bloccato per sicurezza, la gestione avviene via AJAX
    'delete_posts' => false,

    // Permessi sui corsi LearnDash
    'view_all_courses' => true,
);
```

### Blocco Accesso al Backend

L'accesso alla dashboard di WordPress (`/wp-admin/`) è bloccato per questo ruolo tramite un reindirizzamento alla homepage.

```php
// in includes/user-roles.php
function meridiana_restrict_admin_access() {
    if (is_admin() && !current_user_can('administrator') && !(defined('DOING_AJAX') && DOING_AJAX)) {
        wp_redirect(home_url());
        exit;
    }
}
add_action('admin_init', 'meridiana_restrict_admin_access');
```

---

## 👤 Ruolo: Utente Standard (`subscriber`)

### Descrizione
È il ruolo base per tutti i dipendenti della cooperativa. Eredita dal ruolo "Sottoscrittore" di WordPress, ma con l'aggiunta di capabilities custom per la fruizione dei contenuti.

### Capabilities Aggiuntive

```php
// in includes/user-roles.php
$subscriber_role = get_role('subscriber');
if ($subscriber_role) {
    $subscriber_role->add_cap('view_documenti');
    $subscriber_role->add_cap('download_moduli');
    $subscriber_role->add_cap('view_organigramma');
    $subscriber_role->add_cap('view_convenzioni');
    $subscriber_role->add_cap('view_comunicazioni');
    $subscriber_role->add_cap('access_courses');
    $subscriber_role->add_cap('download_certificates');
}
```

---

## 🔑 Campi Custom Utente (ACF)

I campi custom sono definiti in `acf-json/group_user_fields.json` e sono cruciali per la segmentazione e l'analytics.

| Campo | Key | Tipo | Obbl. | Note |
|---|---|---|---|---|
| Stato Utente | `field_stato_utente` | radio | Sì | Valori: `attivo`, `sospeso`, `licenziato`. Default: `attivo`. |
| Link Autologin Esterno | `field_link_autologin` | url | No | URL per SSO a piattaforme di formazione esterne. |
| Codice Fiscale | `field_68f1eb8305594` | text | No | |
| Profilo Professionale | `field_profilo_professionale_user` | select | No | Scelte statiche che replicano la tassonomia `profilo-professionale`. `return_format: label`. |
| UDO di Riferimento | `field_udo_riferimento_user` | select | No | Scelte statiche che replicano la tassonomia `unita-offerta`. `return_format: label`. |

### Utilizzo dei Campi Custom

Per recuperare il valore di un campo custom per un utente:

```php
$user_id = get_current_user_id();

// Recupera il valore salvato (es. 'asa_oss')
$profilo_value = get_field('profilo_professionale', 'user_' . $user_id, false);

// Recupera l'etichetta (es. 'ASA/OSS')
$profilo_label = get_field('profilo_professionale', 'user_' . $user_id);

$stato = get_field('stato_utente', 'user_' . $user_id);
if ($stato === 'attivo') {
    // L'utente è attivo
}
```

---

## 🚪 Logica di Membership

Il file `includes/membership.php` gestisce le regole di accesso alla piattaforma.

- **`meridiana_force_login()`**: Reindirizza tutti gli utenti non autenticati a `wp-login.php`, ad eccezione di chiamate AJAX e REST API.
- **`pre_option_users_can_register`**: Filtro impostato a `__return_zero` per disabilitare la registrazione pubblica.
- **`meridiana_login_redirect()`**: Gestisce i reindirizzamenti post-login in base al ruolo:
  - **Administrator**: `/wp-admin/`
  - **Tutti gli altri**: Homepage del sito (`/`)
- **`meridiana_logout_redirect()`**: Reindirizza alla pagina di login dopo il logout.
- **`meridiana_check_user_status()`**: Controlla lo stato dell'utente (`attivo`, `sospeso`, `licenziato`) ad ogni caricamento di pagina. Se l'utente non è attivo, viene automaticamente disconnesso.

### Personalizzazione Pagina di Login

La pagina di login è personalizzata con il logo e i colori della cooperativa per offrire un'esperienza coerente.

```php
// in includes/membership.php
add_action('login_enqueue_scripts', 'meridiana_login_logo');
add_filter('login_headerurl', 'meridiana_login_logo_url');
add_filter('login_headertext', 'meridiana_login_logo_url_title');
add_filter('login_message', 'meridiana_login_message');
```

---

## 🤖 Checklist per Sviluppo

Quando si lavora con utenti, ruoli o permessi:

- **Verifica Permessi**: Utilizzare sempre `current_user_can()` prima di eseguire azioni sensibili o mostrare contenuti riservati.
- **Mai Hard-codare Ruoli**: Basare le logiche sulle `capabilities` piuttosto che sul nome del ruolo, per maggiore flessibilità.
- **Accesso ai Meta Utente**: Usare sempre il formato `'user_' . $user_id` come secondo parametro per le funzioni ACF (`get_field`, `update_field`).
- **Sanificazione e Validazione**: Sanificare sempre gli input (`sanitize_text_field`, `sanitize_email`) e validare i dati prima di salvarli.
- **Escape Output**: Eseguire sempre l'escape dell'output (`esc_html`, `esc_attr`, `esc_url`) per prevenire attacchi XSS.
- **Test Multi-Ruolo**: Testare le funzionalità con utenti di ruoli diversi (Admin, Gestore, Utente Standard) per assicurarsi che i permessi siano applicati correttamente.