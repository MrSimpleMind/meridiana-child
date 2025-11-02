# 🏥 Piattaforma Formazione Cooperativa La Meridiana

> **QUESTO È IL FILE PRINCIPALE - LEGGI SEMPRE QUESTO PER PRIMO**

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

### 👥 **03_Sistema_Utenti_Auth.md**
Ruoli WordPress custom (Gestore Piattaforma, Utente Standard), capabilities, custom fields utente, login biometrico, membership logic.

**Quando leggerlo:** Gestione utenti, permissions, login, ruoli, profili.

---

### 🧭 **04_Navigazione_UX.md**
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

### 📄 **08_Pagine_Template.md**
Struttura e layout di ogni pagina (Home, Documentazione, Corsi, Organigramma, Convenzioni, Analytics), template parts riutilizzabili.

**Quando leggerlo:** Creazione/modifica pagine, template specifici, layout pagine.

---

### 🔒 **09_Sicurezza_Performance_GDPR.md**
Security hardening, performance optimization, caching strategy, accessibility WCAG 2.1 AA, GDPR compliance, best practices.

**Quando leggerlo:** Ottimizzazione, sicurezza, performance, compliance legale.

---

### 🚀 **10_Deployment_Checklist.md**
Roadmap sviluppo fase per fase, checklist pre-lancio, testing, manutenzione, scalabilità, backup strategy.

**Quando leggerlo:** Planning, deployment, launch, manutenzione post-lancio.

---

## 📅 Timeline e Stato Avanzamento

**Ultimo Aggiornamento**: 1 Novembre 2025

### ✅ Fase 1: Fondamenta (Completata)
- Setup ambiente, plugin e tema child.

### ✅ Fase 2: Struttura Dati e Design (Completata)
- Creazione di tutti i CPT, Tassonomie e Campi Custom.
- Implementazione completa del Design System in SCSS.

### ✅ Fase 3: Navigazione e Layout di Base (Completata)
- Implementazione della sidebar desktop collassabile e della bottom bar mobile.
- Creazione dei template di pagina principali.

### ✅ Fase 4: Funzionalità Avanzate (Completata)
- Sviluppo del sistema di ruoli e permessi.
- Implementazione della logica di membership e login forzato.
- Creazione della Dashboard Gestore con form CRUD via AJAX.
- Sviluppo del sistema di archiviazione e pulizia dei file.
- Implementazione del sistema di analytics con tabella custom e tracking.

### ✅ Fase 5: Documentazione (Completata)
- **Revisione e aggiornamento completo di tutta la documentazione nella directory `/docs` per riflettere lo stato attuale del codice.**

### 🟡 Fase 6: Notifiche e Automazioni (In Corso)
- [ ] Completare e testare le automazioni per i corsi LearnDash (scadenza certificati).
- [ ] Implementare le notifiche email transazionali (es. digest settimanale).

### ⬜ Fase 7: Contenuti e Testing (Da Iniziare)
- [ ] Popolamento della piattaforma con i contenuti reali.
- [ ] User Acceptance Testing (UAT) con un gruppo di utenti pilota.

### ⬜ Fase 8: Launch e Manutenzione (Da Iniziare)
- [ ] Esecuzione della checklist di pre-lancio.
- [ ] Deploy in produzione e monitoraggio iniziale.

---

## 🤖 Note per l'Agente IA

### Workflow Ottimale

1.  **All'avvio di OGNI conversazione**: Leggi questo file (`00_README_START_HERE.md`).
2.  **In base al task dell'utente**: Leggi i file specifici secondo l'indice sopra.
3.  **Dopo completamento task**: Aggiorna la sezione "Timeline e Stato Avanzamento" in questo file.

### Principi Chiave da Ricordare

- **Mobile-first**: Ogni decisione parte dal mobile.
- **Performance**: Ogni byte conta.
- **Accessibility**: WCAG 2.1 AA obbligatorio.
- **Child Theme**: Tutta la logica custom risiede nel tema child.
- **No Bloat**: Se una feature non serve, non includerla.