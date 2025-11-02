# 🔒 Sicurezza, Performance e GDPR

> **Ultimo aggiornamento**: 1 Novembre 2025
> **Fonte**: `includes/security.php`, `functions.php`, configurazione hosting

**Leggi anche**:
- `03_Sistema_Utenti_Roles.md` per l'autenticazione e i permessi
- `00_README_START_HERE.md` per i KPI di performance target

---

## 🛡 Sicurezza

La sicurezza della piattaforma è garantita da una combinazione di plugin specializzati, configurazioni a livello server e hardening custom implementato nel tema.

### Hardening a Livello di Tema (`includes/security.php`)

Il file `includes/security.php` contiene una serie di misure di sicurezza per ridurre la superficie d'attacco di WordPress.

- **Rimozione Informazioni Sensibili**: Vengono rimosse dall'header la versione di WordPress (`wp_generator`), il link al manifest di Windows Live Writer (`wlwmanifest_link`) e il link RSD (`rsd_link`).
- **Disabilitazione XML-RPC**: L'endpoint XML-RPC, spesso bersaglio di attacchi brute-force, è completamente disabilitato tramite il filtro `xmlrpc_enabled`.
- **Prevenzione User Enumeration**: Vengono bloccate le richieste del tipo `/?author=N` che potrebbero rivelare i nomi utente. Inoltre, gli endpoint del REST API per elencare gli utenti (`/wp/v2/users`) sono disabilitati per i non autenticati.
- **Security Headers**: Vengono inviati header HTTP per migliorare la sicurezza lato client:
  - `X-Content-Type-Options: nosniff`
  - `X-Frame-Options: SAMEORIGIN`
  - `X-XSS-Protection: 1; mode=block`
  - `Referrer-Policy: strict-origin-when-cross-origin`
  - `Strict-Transport-Security` (se il sito è in HTTPS)
- **Limitazione Tentativi di Login**: Un sistema custom basato su transienti limita i tentativi di login falliti da un singolo IP, complementare a quanto offerto da Defender Pro.
- **Validazione Upload**: Un filtro su `wp_handle_upload_prefilter` controlla che i file caricati siano solo dei tipi consentiti (PDF, JPG, PNG, etc.) e che non superino una dimensione massima (10MB).

### Hardening a Livello di Configurazione

- **`wp-config.php`**: `DISALLOW_FILE_EDIT` è impostato su `true` per impedire la modifica di file di temi e plugin dal backend.
- **Plugin Esterni**: L'hosting **WPmuDEV** fornisce i plugin **Defender Pro** (firewall, malware scan, 2FA) e **Smush Pro** (ottimizzazione immagini), che sono parte integrante della strategia di sicurezza.

### Best Practices nel Codice

- **SQL Injection**: Tutte le query custom al database utilizzano `$wpdb->prepare()` per sanificare gli input.
- **Cross-Site Scripting (XSS)**: L'output viene sistematicamente "escapato" tramite funzioni come `esc_html()`, `esc_attr()`, `esc_url()` e `wp_kses_post()`.

---

## ⚡ Performance

### Obiettivi

- **Lighthouse Score**: > 90
- **First Contentful Paint (FCP)**: < 1.5s
- **Time to Interactive (TTI)**: < 3.5s

### Strategie di Ottimizzazione

- **Caching a più livelli**:
  - **Page Caching**: Gestito da **Hummingbird Pro** (fornito da WPmuDEV).
  - **Object Caching**: **Redis** è attivo a livello server (fornito da WPmuDEV) per memorizzare in RAM i risultati di query complesse e transienti.
  - **Browser Caching**: Configurazioni ottimali per la cache del browser gestite da Hummingbird Pro.
  - **Transient API**: Le query più pesanti, come quelle per le statistiche della dashboard analytics, vengono messe in cache per 1-6 ore utilizzando la Transient API di WordPress.

- **Ottimizzazione degli Asset**:
  - **CSS e JS**: Minificati e combinati tramite `npm run build` (Webpack).
  - **Immagini**: Ottimizzate automaticamente all'upload da **Smush Pro**, che gestisce anche il lazy loading e la conversione in formato WebP.
  - **Icone**: Utilizzo di **Lucide Icons**, una libreria di icone SVG leggera e performante.

- **Ottimizzazione delle Query**:
  - Le query complesse, specialmente quelle per l'analytics, sono state scritte per essere il più performanti possibile, sfruttando gli indici del database.
  - Si evita di eseguire query all'interno di loop.

- **CDN**: Gli asset statici sono serviti tramite la CDN di WPmuDEV, riducendo la latenza per gli utenti.

---

## ♿ Accessibilità (WCAG 2.1 AA)

- **HTML Semantico**: Utilizzo corretto di tag come `<header>`, `<nav>`, `<main>`, `<aside>`, `<footer>`.
- **ARIA Roles**: Attributi ARIA per migliorare la semantica di componenti interattivi come modal e dropdown.
- **Navigazione da Tastiera**: Tutti gli elementi interattivi sono raggiungibili e utilizzabili tramite tastiera.
- **Contrasto Colori**: La palette di colori rispetta i requisiti minimi di contrasto (4.5:1 per il testo normale).
- **Testo Alternativo**: Tutte le immagini significative hanno un testo `alt` descrittivo.
- **Focus Visibile**: Gli indicatori di focus sono chiari e ben visibili per facilitare la navigazione da tastiera.

---

## 🔐 Conformità GDPR

- **Privacy Policy**: Una pagina di privacy policy dettagliata è disponibile e linkata nel footer.
- **Data Processing Agreement (DPA)**: Sono stati stipulati DPA con tutti i fornitori di terze parti che trattano dati personali (WPmuDEV, OneSignal, Brevo).
- **Diritto all'Oblio**: La funzione `gdpr_delete_user_data()` (in `includes/security.php`) viene agganciata all'hook `delete_user` per anonimizzare i dati di analytics e rimuovere l'utente dai servizi di terze parti.
- **Portabilità dei Dati**: Una funzione `gdpr_export_user_data()` è predisposta per esportare i dati di un utente in formato JSON, inclusi dati anagrafici, campi custom, corsi completati e documenti visualizzati.

---

## 🤖 Checklist per Sviluppo

- **Sicurezza**: Verificare sempre i permessi, usare nonce, sanificare input e "eseguire l'escape" dell'output.
- **Performance**: Mettere in cache le query complesse, ottimizzare le immagini e scrivere codice performante.
- **GDPR**: Assicurarsi che ogni nuovo dato personale raccolto sia documentato nella privacy policy e gestito in conformità con il regolamento.