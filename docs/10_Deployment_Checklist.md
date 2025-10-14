# 🚀 Deployment e Roadmap

> **Contesto**: Roadmap sviluppo fase per fase, checklist pre-lancio, manutenzione

**Leggi anche**: 
- Tutti gli altri file per dettagli implementazione

---

## 📅 ROADMAP SVILUPPO

### Fase 1: Foundation (Settimane 1-2)

**Obiettivo**: Setup base e configurazione

#### Setup Hosting e Core

- [ ] Attivare hosting WPmuDEV (2GB RAM)
- [ ] Installare WordPress 6.x (PHP 8.1+)
- [ ] Configurare SSL e HTTPS forzato
- [ ] Setup staging environment
- [ ] Configurare backup automatici giornalieri

#### Installazione Plugin

- [ ] Installare e attivare **Blocksy** (free)
- [ ] Installare **ACF Pro** (licenza posseduta)
- [ ] Installare **LearnDash** (licenza da acquistare)
- [ ] Installare **WP WebAuthn** (free)
- [ ] Installare **Super PWA** (free)
- [ ] Installare **PDF Embedder** (posseduto)
- [ ] Attivare **Defender Pro** (WPmuDEV)
- [ ] Attivare **Hummingbird Pro** (WPmuDEV)
- [ ] Attivare **Smush Pro** (WPmuDEV)

#### Configurazione Blocksy

**Vedi file `01_Design_System.md` sezione 1.2.1**

- [ ] Customizer → Colors (imposta brand colors)
- [ ] Typography settings (system fonts o Google Fonts)
- [ ] Performance settings (lazy load, minify)
- [ ] Disabilita features non necessarie
- [ ] Header/Footer builder (disabilita default)

#### Child Theme Setup

- [ ] Creare struttura child theme (vedi Appendice A doc originale)
- [ ] `style.css` con header tema
- [ ] `functions.php` con enqueue e includes
- [ ] Struttura cartelle (`assets/`, `includes/`, `templates/`, `api/`)
- [ ] `package.json` per build SCSS/JS
- [ ] Setup build tools (Sass, esbuild)
- [ ] Test compilazione: `npm run build`

---

### Fase 2: Core Features (Settimane 3-5)

**Obiettivo**: CPT, taxonomies, design system, templates base

#### Custom Post Types e Taxonomies

**Vedi file `02_Struttura_Dati_CPT.md`**

- [ ] Registrare CPT: Protocollo
- [ ] Registrare CPT: Modulo
- [ ] Registrare CPT: Convenzione
- [ ] Registrare CPT: Organigramma
- [ ] Registrare CPT: Salute e Benessere
- [ ] Registrare taxonomy: `unita_offerta` (+ termini default)
- [ ] Registrare taxonomy: `profili_professionali` (+ termini)
- [ ] Registrare taxonomy: `aree_competenza` (+ termini)
- [ ] Registrare taxonomy LearnDash: `tipologia_corso` (+ termini)
- [ ] Test: verificare CPT visibili in backend

#### ACF Fields

- [ ] Field group Protocollo (PDF, riassunto, moduli collegati, flag ATS)
- [ ] Field group Modulo (PDF)
- [ ] Field group Convenzione (attiva, immagine, contatti, allegati)
- [ ] Field group Organigramma (ruolo, UDO, email, telefono)
- [ ] Field group Salute (risorse repeater)
- [ ] Field group User (stato, link autologin, profilo, UDO)
- [ ] Field group Corso (se custom fields necessari)
- [ ] Test: inserire contenuti di prova via backend

#### Design System SCSS

**Vedi file `01_Design_System.md`**

- [ ] `_variables.scss` (colori, font, spacing, radius, shadows, z-index)
- [ ] `_mixins.scss` (stati interattivi)
- [ ] `_reset.scss` (CSS reset)
- [ ] `base/_typography.scss`
- [ ] `base/_grid.scss` (container system)
- [ ] `base/_utilities.scss`
- [ ] `components/_buttons.scss`
- [ ] `components/_cards.scss`
- [ ] `components/_badges.scss`
- [ ] `components/_forms.scss`
- [ ] `components/_tables.scss`
- [ ] Compilare: `npm run build`
- [ ] Test: verificare styling su pagina di prova

#### Navigazione

**Vedi file `04_Navigazione_Layout.md`**

- [ ] `layout/_bottom-nav.scss` (mobile)
- [ ] `layout/_desktop-nav.scss` (header desktop)
- [ ] `layout/_mobile-menu.scss` (overlay)
- [ ] Template part: `templates/parts/navigation/bottom-nav.php`
- [ ] Template part: `templates/parts/navigation/desktop-nav.php`
- [ ] Template part: `templates/parts/navigation/mobile-menu.php`
- [ ] JavaScript: Alpine.js mobile menu logic
- [ ] Test: navigazione su mobile e desktop

#### Template Pagine Base

**Vedi file `08_Pagine_Templates.md`**

- [ ] `page-home.php` (dashboard)
- [ ] `page-documentazione.php` (con filtri sidebar)
- [ ] `page-organigramma.php` (tabella contatti)
- [ ] `page-convenzioni.php` (grid card)
- [ ] `page-corsi.php` (tab navigation)
- [ ] `single-protocollo.php` (con PDF embed + tracking)
- [ ] `single-modulo.php` (con download)
- [ ] `single-convenzione.php`
- [ ] Template parts: `templates/parts/cards/card-documento.php`
- [ ] Test: creare pagine e verificare template

---

### Fase 3: Advanced Features (Settimane 6-7)

**Obiettivo**: Form frontend, analytics, membership, autenticazione

#### Sistema Utenti e Ruoli

**Vedi file `03_Sistema_Utenti_Roles.md`**

- [ ] Creare ruolo: `gestore_piattaforma` (con capabilities)
- [ ] Modificare `subscriber` capabilities
- [ ] Blocco backend per Gestore
- [ ] Redirect post-login per ruolo
- [ ] Nascondere admin bar
- [ ] Test: login con utenti di ruoli diversi

#### Login Biometrico

- [ ] Configurare WP WebAuthn
- [ ] Enable WebAuthn login
- [ ] Customizzare login page style
- [ ] Test: registrazione device su mobile
- [ ] Test: login biometrico iOS/Android

#### Membership Logic

- [ ] Forza login globale (redirect non-logged)
- [ ] Pagine pubbliche whitelist
- [ ] Session timeout (8 ore)
- [ ] Test: accesso senza login

#### ACF Frontend Forms

**Vedi file `05_Gestione_Frontend_Forms.md`**

- [ ] Form Protocollo (shortcode + ACF form)
- [ ] Form Modulo
- [ ] Form Convenzione
- [ ] Form Organigramma
- [ ] Form Salute e Benessere
- [ ] Form Comunicazione
- [ ] Form Gestione Utenti (custom HTML + ACF render)
- [ ] Validation server-side
- [ ] Success/error messages
- [ ] Test: inserimento contenuti da frontend

#### File Management System

- [ ] Database table: `file_archive_log`
- [ ] Logic archiving: sposta vecchio file in `/archive`
- [ ] Cron job: eliminazione dopo 30 giorni
- [ ] Hook `acf/save_post` per trigger archiving
- [ ] Test: aggiornare PDF e verificare archiving

#### Analytics System

**Vedi file `06_Analytics_Tracking.md`**

- [ ] Database table: `document_views`
- [ ] REST API endpoint: `/track-view`
- [ ] REST API endpoint: `/analytics/document/{id}`
- [ ] Alpine.js: `documentTracker` component
- [ ] JavaScript: tracking beforeunload + visibility
- [ ] Implementare tracking in template single
- [ ] Query functions: `get_document_views()`, `get_users_who_viewed()`, `get_users_who_not_viewed()`
- [ ] Test: visualizzare documento e verificare insert DB

#### Dashboard Analytics

- [ ] `page-analytics.php` template
- [ ] Template part: KPI widget
- [ ] Template part: Documents table (con DataTables.js)
- [ ] Template part: Document detail (chi ha visto/non visto)
- [ ] Export CSV (viewed/not viewed)
- [ ] Filters (tipo, UDO, date range)
- [ ] Cache reports (6 ore)
- [ ] Test: verificare analytics con dati reali

---

### Fase 4: Notifiche e Automazioni (Settimana 8)

**Obiettivo**: Push, email, automazioni corsi

#### OneSignal Setup

**Vedi file `07_Notifiche_Automazioni.md`**

- [ ] Creare app OneSignal (free tier)
- [ ] Ottenere App ID e API Key
- [ ] Configurare in `includes/notifications.php`
- [ ] Function: `invia_push_notification()`
- [ ] Hook: nuovo Protocollo → push
- [ ] Hook: nuova Comunicazione → push
- [ ] Hook: nuova Convenzione → push
- [ ] Form: notifica custom da Gestore
- [ ] Test: pubblicare contenuto e verificare push

#### Brevo Setup

- [ ] Creare account Brevo (free tier)
- [ ] Ottenere API Key
- [ ] Creare lista "Dipendenti Cooperativa"
- [ ] Function: `invia_email_brevo()`
- [ ] Function: `sync_utente_brevo()`
- [ ] Hook: `user_register` → sync + email benvenuto
- [ ] Email: digest settimanale (cron lunedì 9:00)
- [ ] Test: creare utente e verificare email

#### Automazioni Corsi

- [ ] Auto-enrollment corsi obbligatori per nuovi utenti
- [ ] Cron: check certificati in scadenza (7 giorni)
- [ ] Alert scadenza: push + email
- [ ] Re-enrollment automatico se scaduto
- [ ] Test: simulare scadenza certificato

#### PWA Configuration

- [ ] Super PWA: enable
- [ ] Manifest.json (nome app, icone, colori)
- [ ] Service worker per offline
- [ ] Testare: installazione su home screen mobile
- [ ] Testare: notifiche push funzionanti

---

### Fase 5: LearnDash e Corsi (Settimana 9)

**Obiettivo**: Configurare corsi, certificati, integration

#### LearnDash Setup

- [ ] Configurazione base LearnDash
- [ ] Creare template certificato PDF custom
- [ ] Personalizzare email notifiche corso
- [ ] Impostare quiz (se necessario)
- [ ] Abilitare drip content (se necessario)

#### Corsi di Prova

- [ ] Creare 2-3 corsi "Obbligatori Interni"
- [ ] Assegnare taxonomy `tipologia_corso`
- [ ] Test auto-enrollment nuovo utente
- [ ] Test completamento corso
- [ ] Test download certificato
- [ ] Verificare scadenza certificato (1 anno)

---

### Fase 6: Contenuti e Testing (Settimana 10)

**Obiettivo**: Import contenuti reali, UAT, ottimizzazione

#### Import Contenuti

- [ ] Caricare tutti i Protocolli (~100 PDF)
- [ ] Caricare tutti i Moduli (~100 PDF)
- [ ] Assegnare taxonomies (UDO, Profili, Aree)
- [ ] Caricare Convenzioni (5-10)
- [ ] Caricare Organigramma (20-30 contatti)
- [ ] Caricare articoli Salute e Benessere (10-15)
- [ ] Caricare Comunicazioni (5-10)

#### Creazione Utenti

- [ ] Creare 10-15 utenti di test
- [ ] Assegnare UDO e Profili differenti
- [ ] 1 utente Gestore Piattaforma
- [ ] 1 utente Admin
- [ ] Test permessi per ruolo

#### User Acceptance Testing (UAT)

- [ ] Test navigazione mobile (real device)
- [ ] Test login biometrico (iOS + Android)
- [ ] Test ricerca documenti
- [ ] Test filtri documentazione
- [ ] Test visualizzazione PDF protocolli
- [ ] Test download PDF moduli
- [ ] Test form inserimento contenuti (Gestore)
- [ ] Test analytics dashboard
- [ ] Test notifiche push
- [ ] Test email
- [ ] Test corsi LearnDash

#### Performance Optimization

**Vedi file `09_Sicurezza_Performance_GDPR.md`**

- [ ] Lighthouse audit mobile
- [ ] Lighthouse audit desktop
- [ ] Ottimizzare immagini (Smush Pro)
- [ ] Minify CSS/JS (Hummingbird)
- [ ] Enable page caching
- [ ] Enable object caching (Redis)
- [ ] Test velocità caricamento pagine
- [ ] Target: FCP <1.5s, TTI <3.5s, LCP <2.5s

#### Security Hardening

- [ ] Defender Pro: attivare firewall
- [ ] Defender Pro: login protection (5 tentativi)
- [ ] Defender Pro: malware scan
- [ ] Security headers attivi
- [ ] File upload validation
- [ ] Test: tentativi login multipli
- [ ] Test: upload file non permessi

---

### Fase 7: Launch (Settimana 11)

**Obiettivo**: Go live, monitoring, training

#### Pre-Launch Checklist

**Vedi sezione completa sotto.**

- [ ] Tutti i check della checklist completati
- [ ] Backup completo pre-launch
- [ ] DNS configurato e testato
- [ ] Monitoring attivo

#### Go Live

- [ ] Switch DNS da staging a production
- [ ] Verifica propagazione DNS
- [ ] Test completo post-launch
- [ ] Monitor performance prime 48h
- [ ] Monitor errori log

#### User Training

- [ ] Preparare documentazione utente
- [ ] Video tutorial: login biometrico
- [ ] Video tutorial: ricerca documenti
- [ ] Video tutorial: completare corsi
- [ ] Video tutorial: (per Gestore) inserire contenuti
- [ ] Sessione formazione live (opzionale)

#### Post-Launch Monitoring

- [ ] Check uptime monitoring (WPmuDEV)
- [ ] Review error logs giornalmente (prima settimana)
- [ ] Monitor performance metrics
- [ ] Raccogliere feedback utenti
- [ ] Fix bug prioritari entro 48h

---

## ✅ CHECKLIST PRE-LANCIO

### Tecnica

**Core WordPress**
- [ ] WordPress aggiornato all'ultima versione stabile
- [ ] Tutti i plugin aggiornati
- [ ] Tema Blocksy aggiornato
- [ ] Child theme attivo e funzionante
- [ ] PHP 8.1+ attivo

**Performance**
- [ ] Lighthouse Score mobile >90
- [ ] Lighthouse Score desktop >90
- [ ] First Contentful Paint <1.5s
- [ ] Time to Interactive <3.5s
- [ ] Largest Contentful Paint <2.5s
- [ ] Caching attivo (page + object)
- [ ] CDN attiva
- [ ] Lazy loading immagini attivo
- [ ] CSS/JS minificati

**Sicurezza**
- [ ] SSL attivo e forzato (HTTPS)
- [ ] Security headers configurati
- [ ] Defender Pro: firewall attivo
- [ ] Defender Pro: malware scan pulito
- [ ] Login protection attivo (5 tentativi)
- [ ] File upload validation
- [ ] SQL injection prevention (prepared statements)
- [ ] XSS prevention (escape output)
- [ ] Backup automatici attivi

**Funzionalità**
- [ ] Membership forzata funzionante
- [ ] Login biometrico testato (iOS + Android)
- [ ] Bottom nav mobile funzionante
- [ ] Desktop nav funzionante
- [ ] Ricerca documenti funzionante
- [ ] Filtri documentazione funzionanti
- [ ] PDF embed protocolli funzionante
- [ ] PDF download moduli funzionante
- [ ] Form frontend testati (tutti)
- [ ] Analytics tracking funzionante
- [ ] Export CSV analytics funzionante
- [ ] Push notifications funzionanti
- [ ] Email notifications funzionanti
- [ ] Auto-enrollment corsi funzionante
- [ ] LearnDash certificati generati correttamente

**Database**
- [ ] Tabella `document_views` creata
- [ ] Tabella `file_archive_log` creata
- [ ] Index database ottimizzati
- [ ] Test query analytics (<500ms)

**Monitoring**
- [ ] Uptime monitoring attivo (WPmuDEV)
- [ ] Error logging attivo
- [ ] Performance monitoring configurato
- [ ] Email alert configurate

---

### Contenuti

- [ ] Tutti i Protocolli caricati e categorizzati
- [ ] Tutti i Moduli caricati e categorizzati
- [ ] Convenzioni caricate (almeno 5)
- [ ] Organigramma completo (tutti i contatti)
- [ ] Articoli Salute e Benessere (almeno 10)
- [ ] Comunicazioni iniziali (benvenuto, istruzioni)
- [ ] Corsi obbligatori creati (almeno 3)
- [ ] Certificati template personalizzato
- [ ] Immagini ottimizzate (WebP se possibile)

---

### Utenti

- [ ] Ruoli configurati correttamente
- [ ] Test user per ogni ruolo creati
- [ ] Permissions verificate per ruolo
- [ ] 10-15 utenti reali importati (per test iniziale)
- [ ] Custom fields utente compilati
- [ ] Link autologin esterni configurati (se necessario)
- [ ] Sync Brevo funzionante

---

### Legale e Compliance

**GDPR**
- [ ] Privacy policy pubblicata
- [ ] Privacy policy aggiornata (include tutti i trattamenti)
- [ ] Cookie policy (se necessario)
- [ ] Cookie banner (se necessario)
- [ ] Terms of service (opzionale)
- [ ] DPA firmati con fornitori (WPmuDEV, Brevo, OneSignal)
- [ ] Documenti DPA archiviati
- [ ] Right to be forgotten implementato
- [ ] Data portability implementata

**Accessibility**
- [ ] WCAG 2.1 AA compliant
- [ ] Test con screen reader
- [ ] Keyboard navigation completa
- [ ] Contrasto colori >4.5:1
- [ ] Alt text su tutte le immagini
- [ ] ARIA labels appropriati

---

## 🔄 MANUTENZIONE POST-LANCIO

### Routine Giornaliera (Prima Settimana)

- [ ] Check error logs
- [ ] Monitor uptime (verificare alert)
- [ ] Verifica performance metrics
- [ ] Rispondere a ticket utenti

### Routine Settimanale

- [ ] Review analytics (visualizzazioni, utenti attivi)
- [ ] Check backup (verifica ultimo backup ok)
- [ ] Update plugin (se disponibili, testare su staging)
- [ ] Review feedback utenti

### Routine Mensile

- [ ] Full site audit (performance, security, links rotti)
- [ ] Database optimization (cleanup)
- [ ] Review storage usage
- [ ] Update documentazione se necessario

### Routine Trimestrale

- [ ] Security audit completo (Defender scan + manual check)
- [ ] Review permissions utenti
- [ ] Cleanup utenti inattivi (licenziati)
- [ ] Backup completo offline (oltre a quelli automatici)

---

## 📈 SCALABILITÀ

### Se Utenti > 500

- [ ] Upgrade hosting a 4GB RAM
- [ ] Considerare Elasticsearch per ricerca full-text
- [ ] Implementare queue system per notifiche (Redis)
- [ ] CDN per file statici (oltre a quello WPmuDEV)
- [ ] Load balancing (tier enterprise WPmuDEV)

### Se Documenti > 500

- [ ] Upgrade storage hosting
- [ ] Implementare pagination avanzata
- [ ] Ottimizzare query con index custom
- [ ] Considerare archiving documenti vecchi (>3 anni)

---

## 🆘 TROUBLESHOOTING

### Performance Issues

1. Check Hummingbird cache (svuotare e rigenerare)
2. Check query slow (Query Monitor plugin su staging)
3. Check Redis (wp-cli: `wp cache flush`)
4. Check CDN (purge cache)
5. Database optimization

### Notifiche Non Funzionanti

1. Check OneSignal API key
2. Check Brevo API key
3. Verify cron jobs running: `wp cron event list`
4. Check error logs
5. Test manual trigger

### Login Issues

1. Check WebAuthn plugin attivo
2. Clear browser cache
3. Test con altro device
4. Fallback: password login (se abilitato)

---

## 📞 SUPPORTO

**WPmuDEV**: Support 24/7 (live chat)  
**ACF**: Forum + documentation  
**LearnDash**: Support ticket system  

**Per emergenze critiche**: Support WPmuDEV prioritario (hosting down, security breach)

---

## 🎯 OBIETTIVI POST-LANCIO

### Mese 1-3
- [ ] 100% utenti con account attivo
- [ ] >80% utenti hanno completato almeno 1 corso
- [ ] >90% documenti visualizzati almeno 1 volta
- [ ] Zero downtime non pianificato
- [ ] Lighthouse score >90 mantenuto

### Mese 4-6
- [ ] Sistema analytics usato regolarmente da Gestore
- [ ] Feedback utenti positivo (survey)
- [ ] Espansione contenuti (nuovi corsi, documenti)

---

**🚀 Roadmap completa e dettagliata per lancio di successo.**
