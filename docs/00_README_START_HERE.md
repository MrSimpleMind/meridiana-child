# 🏥 Piattaforma Formazione Cooperativa La Meridiana

> **QUESTO È IL FILE PRINCIPALE - LEGGI SEMPRE QUESTO PER PRIMO**

---

## 📚 **GLOSSARIO E NOMENCLATURE - IMPORTANTE LEGGERLO**

### Terminologia CPT e Contenuti

Per evitare confusione nella comunicazione interna, usa SEMPRE questi termini:

| Cosa Si Gestisce | Termine Corretto | Note |
|------------------|------------------|-------|
| Convenzioni aziendali | **CONVENZIONI** | CPT: `convenzione`. Es: archivio `/convenzione/`, single `$post_type = 'convenzione'` |
| Notizie aziendali | **ARTICOLI** / Comunicazioni | CPT: `post` (standard WP) oppure custom `comunicazioni`. NOTA: Non chiamare mai "news" o confondersi con Salute |
| Salute e Benessere | **SALUTE E BENESSERE** | CPT: `salute_benessere`. Contenuti wellness separati dalle comunicazioni |
| Documenti operativi (parte 1) | **PROTOCOLLI** | CPT: `protocollo`. Con/senza flag ATS. Linee guida operative |
| Documenti operativi (parte 2) | **MODULI** | CPT: `modulo`. Form, checklist, template operativi |
| Tutti i documenti insieme | **DOCUMENTAZIONE** | Label generale. Quando parli di "sezione documentazione" intendi: Protocolli + Moduli |
| Sezione formazione online | **CORSI** | Integrazione LearnDash. CPT generato da LearnDash stesso |
| Struttura organizzativa | **ORGANIGRAMMA** | CPT: `organigramma`. DIVERSO dagli utenti WP (che sono nella wp_users table) |
| Dipendenti del sistema | **UTENTI** / User Base | Standard `wp_users` di WordPress. DIVERSO da Organigramma |

### Perché Questa Distinzione?

**UTENTI vs ORGANIGRAMMA:**
- **UTENTI** = Credenziali di accesso, ruoli WP, permessi, profilo login (nella `wp_users` table)
- **ORGANIGRAMMA** = Struttura gerarchica aziendale, posizioni, reparti (CPT custom, con relazioni)

Esempio: Un dipendente UTENTE può non essere nell'ORGANIGRAMMA (es. utente temporaneo), o inversamente, una posizione ORGANIGRAMMA potrebbe non avere UTENTE.

**COMUNICAZIONI vs SALUTE E BENESSERE:**
- **COMUNICAZIONI/ARTICOLI** = News generiche aziendali
- **SALUTE E BENESSERE** = Articoli wellness specifici (benessere, salute mentale, prevenzione)

Sono sezioni distinte nelle archive, sebbene possano usare lo stesso template di rendering.

**DOCUMENTAZIONE = Protezioni + Moduli:**
- **PROTOCOLLI** = Cosa fare (linee guida)
- **MODULI** = Come farlo (form, checklist, template)
- Insieme = **DOCUMENTAZIONE**

### In Codice

```php
// ✅ CORRETTO - Quando query convenzioni:
$convenzioni = new WP_Query(array(
    'post_type' => 'convenzione',  // NOT 'convenzioni'
    'posts_per_page' => -1,
));

// ✅ CORRETTO - Breadcrumb label:
'convenzione' => 'Torna a Convenzioni',  // Titolo user-friendly

// ✅ CORRETTO - URL archivio:
home_url('/convenzione/')  // Slug singolare

// ❌ SBAGLIATO - Non fare mai:
'post_type' => 'convenzioni'  // Plurale - non esiste!
home_url('/convenzioni/')     // Sbagliato, causa 404
```

### In Documentazione Interna

Quando scrivi nei file `.md` della documentazione:

- "Gestione CONVENZIONI" ✅
- "CPT Convenzione" ✅
- "Sezione ARTICOLI/Comunicazioni" ✅
- "Archivio DOCUMENTAZIONE (Protocolli + Moduli)" ✅
- "ORGANIGRAMMA aziendale" ✅
- "Base UTENTI WordPress" ✅

**Evita:**
- "Convenzioni" vs "convenzioni" (usa capitale all'inizio)
- "Notizie" quando intendi "Articoli"
- Confondere "UTENTI" con "ORGANIGRAMMA"
- "Docs" genericamente - specifica "Protocolli" o "Moduli"

---

Piattaforma interna di formazione e documentazione per **300 dipendenti** della Cooperativa La Meridiana (settore socio-sanitario). Sistema completo per gestione documenti operativi (protocolli, moduli), corsi formativi con certificazioni, organigramma, convenzioni aziendali e contenuti welfare.

**Obiettivi chiave:**
- Accesso mobile-first ai documenti operativi
- Sistema certificazioni con scadenze
- Analytics compliance per audit
- Notifiche push per aggiornamenti
- Login biometrico per sicurezza

---

## 🛠 Stack Tecnologico

### Core
- **WordPress 6.x** (PHP 8.1+)
- **Blocksy Free** (tema base) + Child Theme Custom
- **Hosting**: WPmuDEV (2GB RAM, Redis, CDN inclusa)

### Plugin Essenziali
- **ACF Pro** - Custom Post Types e Frontend Forms
- **LearnDash LMS** - Corsi e certificazioni
- **WP WebAuthn** - Login biometrico
- **Super PWA** - Progressive Web App
- **OneSignal** - Push notifications (free tier)
- **PDF Embedder** - Embed PDF non scaricabili

### Frontend Stack
- **Alpine.js 3.x** - Interattività leggera (15kb)
- **SCSS** - Styling modulare
- **Lucide Icons** - Iconografia lightweight

---

## 🎯 Decisioni Architetturali Chiave

### 1. **Mobile-First con Bottom Navigation**
- Bottom nav a 5 tab (stile app native)
- Desktop: top navigation classica
- Touch targets: min 44x44px (WCAG)

### 2. **Custom Table per Analytics** 
```sql
wp_document_views -- non wp_postmeta!
```
- Performance: query dedicate
- Scalabilità: migliaia di record
- Report veloci per compliance

### 3. **File Archiving System**
- Vecchi file → `/uploads/archive/` con timestamp
- Auto-eliminazione dopo 30 giorni
- Log completo operazioni
- Recovery possibile

### 4. **Login Biometrico (WebAuthn)**
- Standard W3C, costo zero
- Dati biometrici restano sul device (privacy)
- Supporto impronta/FaceID/riconoscimento facciale

### 5. **Frontend-Only Role per Gestore**
- NO accesso backend WordPress
- Gestione contenuti via ACF Forms
- Dashboard analytics custom

### 6. **PWA con Offline Capabilities**
- Installabile su home screen
- Service worker per cache
- Push notifications native

---

## 📚 Documentazione - Indice

### 🎨 **01_Design_System.md**
Colori brand, typography, spacing system, componenti UI (buttons, cards, forms, badges, tables), SCSS structure, stati interattivi, breakpoints responsive.

**Quando leggerlo:** Task su UI/UX, styling, componenti, layout, CSS/SCSS.

---

### 📦 **02_Struttura_Dati_CPT.md**
Tutti i Custom Post Types (Protocollo, Modulo, Convenzione, Organigramma, Salute, Comunicazioni, Corsi), taxonomies, custom fields ACF, relazioni.

**Quando leggerlo:** Creazione/modifica CPT, custom fields, query documenti, gestione contenuti.

---

### 👥 **03_Sistema_Utenti_Roles.md**
Ruoli WordPress custom (Gestore Piattaforma, Utente Standard), capabilities, custom fields utente, login biometrico, membership logic.

**Quando leggerlo:** Gestione utenti, permissions, login, ruoli, profili.

---

### 🧭 **04_Navigazione_Layout.md**
Bottom navigation mobile, desktop header, menu structure, stati attivi, mobile menu overlay, user menu, HTML/CSS completo.

**Quando leggerlo:** Modifiche navigazione, menu, header/footer, layout generale.

---

### 📝 **05_Gestione_Frontend_Forms.md**
ACF Forms per inserimento/modifica contenuti, file upload system, archiving logic, validazione, sicurezza, form per ogni CPT.

**Quando leggerlo:** Form frontend, upload file, gestione contenuti da frontend.

---

### 📊 **06_Analytics_Tracking.md**
Database schema custom table, tracking real-time, dashboard analytics, report compliance, chi ha visto/non visto documenti, export CSV.

**Quando leggerlo:** Analytics, tracking visualizzazioni, report, compliance audit.

---

### 🔔 **07_Notifiche_Automazioni.md**
Push notifications (OneSignal), email (Brevo), trigger automatici, cron jobs, auto-enrollment corsi, scadenze certificati.

**Quando leggerlo:** Notifiche, email, automazioni, scheduling, integrazione terze parti.

---

### 📄 **08_Pagine_Templates.md**
Struttura e layout di ogni pagina (Home, Documentazione, Corsi, Organigramma, Convenzioni, Analytics), template parts riutilizzabili.

**Quando leggerlo:** Creazione/modifica pagine, template specifici, layout pagine.

---

### 🔒 **09_Sicurezza_Performance_GDPR.md**
Security hardening, performance optimization, caching strategy, accessibility WCAG 2.1 AA, GDPR compliance, best practices.

**Quando leggerlo:** Ottimizzazione, sicurezza, performance, compliance legale.

---

### 🚀 **10_Deployment_Roadmap.md**
Roadmap sviluppo fase per fase, checklist pre-lancio, testing, manutenzione, scalabilità, backup strategy.

**Quando leggerlo:** Planning, deployment, launch, manutenzione post-lancio.

---

## 📅 Timeline e Stato Avanzamento

### ✅ Completato
- [x] Analisi requisiti
- [x] Scelta stack tecnologico
- [x] Struttura documentazione completa
- [x] Setup hosting e WordPress
- [x] Configurazione Blocksy base
- [x] Creazione child theme structure
- [x] Functions.php con enqueue e includes
- [x] Costanti tema (MERIDIANA_CHILD_DIR/URI)
- [x] Filtri ACF JSON sync configurati
- [x] Helper functions base (includes/helpers.php)
- [x] Security hardening base (includes/security.php)
- [x] Tasklist priorità creata e aggiornata
- [x] Design System SCSS completo (variabili, componenti, responsive)
- [x] Setup compilazione SCSS/JS (npm, webpack, package.json)
- [x] Risoluzione errori compilazione (mixin custom-scrollbar)
- [x] File demo Design System per testing componenti
- [x] **FIX CRITICO**: CSS compilation pipeline corretta (main.css)
- [x] Bottom navigation mobile funzionante
- [x] Sidebar navigation desktop funzionante
- [x] Template Home Dashboard completo (page-home.php)
- [x] Template Archivio Convenzioni (archive-convenzione.php)
- [x] Template Single Convenzione (single-convenzione.php)
- [x] Template Archivio Salute (archive-salute-e-benessere-l.php)
- [x] Template Single Salute (single-salute-e-benessere-l.php)
- [x] Sistema Avatar completo con 28 immagini predefinite
- [x] Modal profilo utente con AJAX (upload avatar, cambio password)
- [x] User Profile AJAX handler sicuro (nonce, sanitization)
- [x] Debug system avatar con logging (`?meridiana_avatar_debug=1`)
- [x] Carousel convenzioni con navigazione desktop
- [x] Layout responsive mobile-first completo
- [x] Fix hover card (no underline)
- [x] Correzioni campi ACF (immagine, descrizione, contenuto)
- [x] Riorganizzazione documentazione (docs/archive/)

### 🟡 In Corso
- [ ] **PRIORITÀ**: Verificare CPT in ACF UI (Protocollo, Modulo, Organigramma)
- [ ] Creazione Taxonomies (Unità Offerta, Profili, Aree Competenza)
- [ ] Field Groups utente (UDO, Profilo, Stato)
- [ ] Sistema Ruoli custom (Gestore Piattaforma)
- [ ] Template Documentazione con filtri

### ⬜ Da Fare (Prossime 2 settimane)
- [ ] Single Protocollo/Modulo (PDF viewer/download)
- [ ] Pagina Corsi con tabs LearnDash
- [ ] Frontend Forms ACF per Gestore
- [ ] File Management System (archiving + recovery)
- [ ] Login biometrico WP WebAuthn
- [ ] Testing cross-browser device reali
- [ ] Popolamento contenuti iniziali

**Ultimo Aggiornamento**: 17 Ottobre 2025  
**Fase Corrente**: Fase 1 ✅ COMPLETATA | Fase 2-4 🔄 In Corso (30-40%)  
**Completamento Totale**: ~25% del progetto

---

## 💰 Costi Annuali

```
Hosting WPmuDEV (2GB):      $300/anno
ACF Pro:                     $59/anno  
LearnDash:                  $199/anno
────────────────────────────────────
TOTALE:                     $558/anno ($46.50/mese)

Costo per utente/anno: $1.86
```

**Tool gratuiti:** Blocksy, WebAuthn, PWA, OneSignal, Brevo (free tier), Alpine.js, Lucide Icons.

---

## 🎯 KPI e Metriche Target

- **Utenti**: 300 dipendenti
- **Concurrent users max**: 20
- **Documenti**: ~200 PDF
- **Lighthouse Score**: >90
- **First Contentful Paint**: <1.5s
- **Time to Interactive**: <3.5s
- **Uptime**: 99.9%

---

## 📞 Contatti Progetto

**Cliente**: Cooperativa La Meridiana  
**Settore**: Socio-sanitario  
**Data Inizio**: Ottobre 2025  
**Versione Doc**: 2.0

---

## 🤖 Note per l'Agente IA

### Workflow Ottimale

1. **All'avvio di OGNI conversazione**: Leggi questo file (00_README_START_HERE.md)
2. **In base al task dell'utente**: Leggi i file specifici secondo l'indice sopra
3. **Dopo completamento task**: Aggiorna la sezione "Timeline e Stato Avanzamento" in questo file

### ⚠️ REGOLE CRITICHE - COMPILAZIONE CSS

**SE le modifiche CSS/SCSS non vengono applicate nel browser:**

1. **VERIFICA quale file viene caricato**: Controlla `functions.php` → `wp_enqueue_style` → quale file CSS viene effettivamente caricato?
2. **VERIFICA quale file viene generato**: Controlla `package.json` → script `build:scss` → quale file genera? (`main.css` o `main.min.css`?)
3. **DEVONO COINCIDERE**: Il file caricato da WordPress DEVE essere lo stesso generato da sass
4. **FIX**: Se non coincidono, modifica `functions.php` per caricare il file corretto

**MAI E POI MAI:**
- ❌ Scrivere hotfix CSS inline con `<style>` nei template PHP
- ❌ Usare `!important` per "forzare" stili che non funzionano
- ❌ Aggiungere CSS temporaneo "da rimuovere dopo"

**Il problema è SEMPRE nella pipeline di compilazione, mai nel CSS stesso.**

---

### Priorità Contestuali

- **Design/UI** → File 01
- **Dati/CPT** → File 02  
- **Utenti/Auth** → File 03
- **Navigazione** → File 04
- **Form** → File 05
- **Analytics** → File 06
- **Notifiche** → File 07
- **Template** → File 08
- **Security/Performance** → File 09
- **Deployment** → File 10

### Principi Chiave da Ricordare

- **Mobile-first**: Ogni decisione parte dal mobile
- **Performance**: Ogni byte conta (300 utenti concorrenti)
- **Accessibility**: WCAG 2.1 AA obbligatorio
- **Blocksy = base**: Non modificare mai il parent theme
- **Child theme = logica**: Tutta la customizzazione nel child
- **No bloat**: Se una feature non serve, non includerla

---

**🎯 Obiettivo**: Piattaforma veloce, sicura, accessibile, facile da usare per 300 operatori socio-sanitari.
