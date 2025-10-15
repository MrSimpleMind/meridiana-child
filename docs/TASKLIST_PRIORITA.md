# 📋 TaskList Ordinata per Priorità e Logica

> **Aggiornato**: 15 Ottobre 2025 - 10:00  
> **Stato**: In Sviluppo - Fase 1 COMPLETATA AL 100% 🎉  
> **Hotfix Applicato**: CSS temporaneo attivo (compilare SCSS per soluzione definitiva)  
> Questo file contiene tutte le task ordinate per importanza logica e dipendenze

---

## 🎯 Legenda Priorità

- **P0 - CRITICO**: Bloccante per tutto, deve essere fatto per primo
- **P1 - ALTA**: Fondamentale per funzionalità core
- **P2 - MEDIA**: Importante ma non bloccante
- **P3 - BASSA**: Nice-to-have, ottimizzazioni

---

## FASE 1: FONDAMENTA ⚡ (P0 - Settimane 1-2)

### 1.1 Setup Base
- [ ] **P0** - Installare e configurare plugin essenziali (ACF Pro, LearnDash, WebAuthn, Super PWA, OneSignal)
- [ ] **P0** - Attivare child theme Blocksy e verificare funzionamento
- [ ] **P0** - Configurare ambiente sviluppo (SCSS compilation, file watcher)
- [ ] **P0** - Setup Git repository e .gitignore

### 1.2 Design System & SCSS ✅
- [x] **P0** - Creare struttura SCSS modulare (`/assets/css/src/`)
- [x] **P0** - Definire variabili CSS custom properties (colori, spacing, typography)
- [x] **P0** - Implementare grid system e layout base
- [x] **P0** - Creare componenti base (buttons, forms, cards, badges, tables)
- [x] **P0** - Implementare breakpoints responsive e mobile-first approach
- [x] **P0** - Setup NPM e package.json per compilazione SCSS
- [x] **P0** - Creato README con guide all'uso del Design System
- [x] **P0** - Risolto errore compilazione SCSS (mixin custom-scrollbar con color-mix)
- [x] **P0** - Configurato Webpack con webpack.config.js
- [x] **P0** - Creato entry point JS (assets/js/src/index.js)
- [x] **P0** - Verificata compilazione CSS/JS funzionante (npm run build)
- [x] **P0** - Creato file demo Design System per testing componenti

### 1.3 Navigazione e Layout ✅
- [x] **P0** - Implementare bottom navigation mobile (HTML/CSS/Alpine.js)
- [x] **P0** - Integrare Lucide Icons
- [ ] **P0** - Creare desktop header navigation
- [ ] **P0** - Implementare menu overlay mobile
- [ ] **P0** - Testare navigation su dispositivi touch

---

## FASE 2: STRUTTURA DATI 📦 (P1 - Settimane 2-3)

### 2.1 Custom Post Types ✅
- [x] **P1** - Registrare CPT: Protocollo (via ACF UI)
- [x] **P1** - Registrare CPT: Modulo (via ACF UI)
- [x] **P1** - Registrare CPT: Convenzione (via ACF UI)
- [x] **P1** - Registrare CPT: Organigramma (via ACF UI)
- [x] **P1** - Registrare CPT: Salute e Benessere (via ACF UI)
- [x] **P1** - Configurare Post Standard per Comunicazioni

### 2.2 Taxonomies ✅
- [x] **P1** - Creare taxonomy: Unità di Offerta (condivisa Protocollo/Modulo) (via ACF UI)
- [x] **P1** - Creare taxonomy: Profili Professionali (condivisa Protocollo/Modulo) (via ACF UI)
- [x] **P1** - Creare taxonomy: Aree Competenza (solo Modulo) (via ACF UI)
- [ ] **P1** - Creare taxonomy: Tipologia Corso (LearnDash)
- [ ] **P1** - Popolare termini di default per tutte le taxonomies
- [ ] **P1** - Creare categorie predefinite per Comunicazioni

### 2.3 ACF Field Groups ✅
- [x] **P1** - Field group: Protocollo (PDF, riassunto, moduli collegati, flag ATS)
- [x] **P1** - Field group: Modulo (PDF)
- [x] **P1** - Field group: Convenzione (attiva, immagine, contatti, allegati)
- [x] **P1** - Field group: Organigramma (ruolo, UDO, email, telefono)
- [x] **P1** - Field group: Salute Benessere (risorse repeater)
- [x] **P1** - Field group: Utente (UDO, Profilo, Stato, Link esterno corsi)
- [x] **P1** - ACF JSON sync configurato e funzionante

---

## FASE 3: SISTEMA UTENTI 👥 (P1 - Settimana 3)

### 3.1 Ruoli e Capabilities
- [ ] **P1** - Creare ruolo custom: "Gestore Piattaforma"
- [ ] **P1** - Configurare capabilities Gestore (NO backend, solo frontend)
- [ ] **P1** - Configurare capabilities "Utente Standard"
- [ ] **P1** - Implementare custom capability: `view_analytics`

### 3.2 Login & Autenticazione
- [ ] **P1** - Configurare WP WebAuthn (biometric login)
- [ ] **P1** - Personalizzare pagina login WordPress
- [ ] **P1** - Implementare redirect post-login (dashboard home)
- [ ] **P1** - Testare login biometrico su mobile (iOS/Android)

---

## FASE 4: TEMPLATE PAGINE 📄 (P1-P2 - Settimane 4-5)

### 4.1 Pagine Core (PHP Templates)
- [x] **P1** - Template: Home Dashboard (`page-home.php`) ✅
- [x] **P1** - Template: Archivio Convenzioni (`archive-convenzione.php`) ✅
- [x] **P1** - Template: Archivio Salute (`archive-salute_benessere.php`) ✅
- [ ] **P1** - Template: Documentazione con filtri (`page-documentazione.php`)
- [ ] **P1** - Template: Single Protocollo (visualizzazione PDF non scaricabile)
- [ ] **P1** - Template: Single Modulo (download PDF)
- [ ] **P1** - Template: Archivio Convenzioni (`archive-convenzione.php`)
- [ ] **P1** - Template: Single Convenzione
- [ ] **P2** - Template: Pagina Organigramma (griglia contatti)
- [ ] **P2** - Template: Archivio Salute e Benessere
- [ ] **P2** - Template: Single Salute e Benessere
- [ ] **P2** - Template: Archivio Comunicazioni (blog-style)
- [ ] **P2** - Template: Single Comunicazione

### 4.2 Template Corsi (LearnDash Override)
- [ ] **P1** - Template: Pagina Corsi con tabs (`page-corsi.php`)
- [ ] **P2** - Override template: Single Corso LearnDash
- [ ] **P2** - Override template: Lesson LearnDash
- [ ] **P2** - Template certificato PDF personalizzato

### 4.3 Template Analytics (Solo Gestore)
- [ ] **P2** - Template: Dashboard Analytics (`page-analytics.php`)
- [ ] **P2** - Partial: KPI widgets
- [ ] **P2** - Partial: Tabella documenti con filtri
- [ ] **P2** - Partial: Vista dettaglio documento (chi ha visto/non visto)

### 4.4 Template Parts Riutilizzabili
- [ ] **P1** - Card: Documento (Protocollo/Modulo)
- [ ] **P1** - Card: Corso
- [ ] **P2** - Card: Convenzione
- [ ] **P2** - Card: Comunicazione
- [ ] **P2** - Sidebar filtri documentazione
- [ ] **P2** - Feed attività home
- [ ] **P2** - Widget progressi corsi

---

## FASE 5: FRONTEND FORMS 📝 (P1-P2 - Settimana 5-6)

### 5.1 ACF Frontend Forms - Gestore
- [ ] **P1** - Form: Inserimento nuovo Protocollo
- [ ] **P1** - Form: Modifica Protocollo esistente
- [ ] **P1** - Form: Inserimento nuovo Modulo
- [ ] **P1** - Form: Modifica Modulo esistente
- [ ] **P2** - Form: Inserimento nuova Convenzione
- [ ] **P2** - Form: Modifica Convenzione
- [ ] **P2** - Form: Inserimento nuovo contatto Organigramma
- [ ] **P2** - Form: Inserimento nuova Comunicazione
- [ ] **P2** - Form: Inserimento contenuto Salute e Benessere

### 5.2 File Management System
- [ ] **P1** - Implementare upload PDF con validazione (max size, mime types)
- [ ] **P1** - Sistema archiving: spostare vecchi file in `/uploads/archive/`
- [ ] **P1** - Log operazioni file (insert/update/delete)
- [ ] **P1** - Implementare cron job pulizia archivio (30 giorni)
- [ ] **P2** - Recovery file archiviati (se necessario)

---

## FASE 6: ANALYTICS & TRACKING 📊 (P2 - Settimana 6-7)

### 6.1 Database Custom Table
- [ ] **P2** - Creare tabella: `wp_document_views`
- [ ] **P2** - Script migrazione/rollback tabella
- [ ] **P2** - Indici database per performance

### 6.2 Tracking System
- [ ] **P2** - JavaScript: Tracking apertura documento (Alpine.js component)
- [ ] **P2** - AJAX endpoint: Salvataggio view in custom table
- [ ] **P2** - Query: Documenti più visti
- [ ] **P2** - Query: Documenti non visti da utente
- [ ] **P2** - Query: Compliance (% utenti che hanno visto documento)

### 6.3 Dashboard Analytics
- [ ] **P2** - Widget KPI: Total views, Unique users, Documenti non visti
- [ ] **P2** - Tabella documenti con filtri (tipo, UDO, data)
- [ ] **P2** - Vista dettaglio: Lista utenti (visto/non visto) con export CSV
- [ ] **P2** - Grafici visualizzazioni (Chart.js - opzionale)

---

## FASE 7: NOTIFICHE & AUTOMAZIONI 🔔 (P2-P3 - Settimana 7-8)

### 7.1 Push Notifications (OneSignal)
- [ ] **P2** - Configurare OneSignal API
- [ ] **P2** - Implementare funzione invio push notification
- [ ] **P2** - Trigger: Nuovo Protocollo pubblicato
- [ ] **P2** - Trigger: Nuova Comunicazione pubblicata
- [ ] **P2** - Trigger: Nuova Convenzione attiva
- [ ] **P3** - Form custom notifica per Gestore

### 7.2 Email Notifications (Brevo)
- [ ] **P2** - Configurare Brevo API
- [ ] **P2** - Sync utenti con lista Brevo
- [ ] **P2** - Email: Benvenuto nuovo utente
- [ ] **P3** - Email: Digest settimanale (cron job)

### 7.3 Automazioni Corsi
- [ ] **P1** - Auto-enrollment corsi obbligatori per nuovi utenti
- [ ] **P2** - Cron job: Check certificati in scadenza (7 giorni)
- [ ] **P2** - Notifica certificato in scadenza (push + email)
- [ ] **P2** - Auto re-enrollment su certificato scaduto

---

## FASE 8: SICUREZZA & PERFORMANCE 🔒 (P1-P2 - Settimana 8-9)

### 8.1 Security Hardening
- [ ] **P1** - Disabilitare XML-RPC
- [ ] **P1** - Limitare tentativi login (Limit Login Attempts)
- [ ] **P1** - Header security (X-Frame-Options, CSP)
- [ ] **P1** - Sanitizzazione input forms (nonce, sanitize functions)
- [ ] **P1** - Rate limiting API/AJAX calls
- [ ] **P2** - HTTPS enforcement (SSL via WPmuDEV)

### 8.2 Performance Optimization
- [ ] **P1** - Minificazione CSS/JS
- [ ] **P1** - Lazy loading immagini
- [ ] **P1** - Configurare Redis cache (WPmuDEV)
- [ ] **P1** - CDN setup (WPmuDEV CDN)
- [ ] **P2** - Ottimizzare query database (caching query results)
- [ ] **P2** - Defer/async JavaScript non-critical
- [ ] **P2** - Ottimizzare font loading (font-display: swap)

### 8.3 PWA Configuration
- [ ] **P2** - Configurare Super PWA (manifest, icons)
- [ ] **P2** - Service worker per offline capabilities
- [ ] **P2** - Testare installazione su home screen (iOS/Android)

### 8.4 GDPR Compliance
- [ ] **P2** - Cookie banner (se necessario)
- [ ] **P2** - Privacy policy page
- [ ] **P2** - Informativa trattamento dati utenti
- [ ] **P3** - Export/delete dati utente (GDPR request)

---

## FASE 9: ACCESSIBILITY & UX 🎨 (P2-P3 - Settimana 9)

### 9.1 WCAG 2.1 AA Compliance
- [ ] **P2** - Contrasto colori (ratio 4.5:1 per testo normale)
- [ ] **P2** - Touch targets min 44x44px
- [ ] **P2** - Keyboard navigation completa
- [ ] **P2** - Screen reader friendly (ARIA labels)
- [ ] **P2** - Form labels e error messages accessibili
- [ ] **P3** - Focus indicators visibili

### 9.2 User Experience
- [ ] **P2** - Loading states per operazioni async
- [ ] **P2** - Error handling user-friendly
- [ ] **P2** - Success messages post-action
- [ ] **P3** - Skeleton screens (loading placeholders)
- [ ] **P3** - Animazioni micro-interactions (subtle, performant)

---

## FASE 10: TESTING & QA 🧪 (P1 - Settimana 10)

### 10.1 Testing Funzionale
- [ ] **P1** - Test registrazione/login utenti
- [ ] **P1** - Test CRUD documenti (Gestore)
- [ ] **P1** - Test visualizzazione documenti (Utente Standard)
- [ ] **P1** - Test enrollment/completamento corsi
- [ ] **P1** - Test notifiche push/email
- [ ] **P1** - Test analytics tracking
- [ ] **P2** - Test form frontend (validazione, upload)

### 10.2 Testing Cross-Browser/Device
- [ ] **P1** - Test mobile (iOS Safari, Android Chrome)
- [ ] **P1** - Test desktop (Chrome, Firefox, Edge)
- [ ] **P2** - Test tablet (iPad, Android tablet)
- [ ] **P2** - Test login biometrico (vari device)

### 10.3 Performance Testing
- [ ] **P1** - Lighthouse audit (target: >90)
- [ ] **P1** - GTmetrix/PageSpeed Insights
- [ ] **P2** - Load testing (simulate 20 concurrent users)
- [ ] **P2** - Database query optimization

### 10.4 Security Testing
- [ ] **P1** - Penetration testing base
- [ ] **P1** - Test injection attacks (SQL, XSS)
- [ ] **P2** - Verificare permissions/capabilities per ruoli

---

## FASE 11: CONTENUTI & ONBOARDING 📚 (P2-P3 - Settimana 11)

### 11.1 Contenuti Iniziali
- [ ] **P2** - Inserire 10 protocolli di esempio
- [ ] **P2** - Inserire 10 moduli di esempio
- [ ] **P2** - Inserire contatti organigramma
- [ ] **P2** - Inserire convenzioni attive
- [ ] **P3** - Inserire 2-3 corsi di test (LearnDash)
- [ ] **P3** - Inserire contenuti Salute e Benessere
- [ ] **P3** - Inserire comunicazioni iniziali

### 11.2 User Onboarding
- [ ] **P2** - Creare 5 utenti di test (vari ruoli/UDO)
- [ ] **P2** - Guida PDF per Gestore Piattaforma
- [ ] **P3** - Video tutorial (opzionale)
- [ ] **P3** - FAQ page

---

## FASE 12: DEPLOYMENT & LANCIO 🚀 (P1 - Settimana 12)

### 12.1 Pre-Launch Checklist
- [ ] **P1** - Backup completo database + files
- [ ] **P1** - Verificare tutte le credenziali API (OneSignal, Brevo)
- [ ] **P1** - Disabilitare indicizzazione search engines (se interno)
- [ ] **P1** - Configurare monitoraggio uptime
- [ ] **P1** - Testare recupero password utenti
- [ ] **P1** - Email/notifica test a tutti i canali

### 12.2 Launch
- [ ] **P1** - Deploy su produzione (WPmuDEV)
- [ ] **P1** - Creare utenti reali (import CSV?)
- [ ] **P1** - Comunicazione lancio piattaforma a dipendenti
- [ ] **P1** - Supporto attivo primi giorni (help desk)

### 12.3 Post-Launch Monitoring
- [ ] **P1** - Monitorare errori server (7 giorni)
- [ ] **P1** - Monitorare feedback utenti
- [ ] **P2** - Analytics piattaforma (Google Analytics - opzionale)
- [ ] **P2** - Raccogliere richieste feature/bug

---

## FASE 13: MANUTENZIONE CONTINUA 🔧 (P2-P3 - Ongoing)

### 13.1 Aggiornamenti
- [ ] **P2** - Update WordPress core (mensile)
- [ ] **P2** - Update plugin (mensile)
- [ ] **P2** - Update theme (quando disponibile)
- [ ] **P3** - Review security advisories

### 13.2 Backup & Disaster Recovery
- [ ] **P1** - Backup automatico giornaliero (WPmuDEV)
- [ ] **P2** - Test restore da backup (trimestrale)
- [ ] **P3** - Disaster recovery plan documentato

### 13.3 Ottimizzazioni Continue
- [ ] **P3** - Review analytics performance (mensile)
- [ ] **P3** - Ottimizzare query lente (se presenti)
- [ ] **P3** - Aggiungere feature richieste da utenti

---

## 📊 Riepilogo Sforzo

| Fase | Settimane | Priorità | Status |
|------|-----------|----------|--------|
| 1. Fondamenta | 1-2 | P0 | ✅ **100% COMPLETO** (SCSS/JS ✅, Nav Mobile ✅, Home ✅, Hotfix CSS ✅) |
| 2. Struttura Dati | 2-3 | P1 | ⬜ Todo |
| 3. Sistema Utenti | 3 | P1 | ⬜ Todo |
| 4. Template Pagine | 4-5 | P1-P2 | ⬜ Todo |
| 5. Frontend Forms | 5-6 | P1-P2 | ⬜ Todo |
| 6. Analytics | 6-7 | P2 | ⬜ Todo |
| 7. Notifiche | 7-8 | P2-P3 | ⬜ Todo |
| 8. Sicurezza & Performance | 8-9 | P1-P2 | ⬜ Todo |
| 9. Accessibility & UX | 9 | P2-P3 | ⬜ Todo |
| 10. Testing | 10 | P1 | ⬜ Todo |
| 11. Contenuti | 11 | P2-P3 | ⬜ Todo |
| 12. Deployment | 12 | P1 | ⬜ Todo |
| 13. Manutenzione | Ongoing | P2-P3 | ⬜ Todo |

**Timeline stimata**: 12 settimane (~3 mesi)

---

## 🎯 Note Importanti

1. **Non saltare le fasi P0/P1**: Sono fondamentali e bloccanti
2. **Le task P3 possono essere posticipate** se il tempo stringe
3. **Testing non è opzionale**: La fase 10 è critica
4. **Backup prima di ogni deploy**: Sempre

---

**📋 Tasklist completa e pronta per l'implementazione.**
