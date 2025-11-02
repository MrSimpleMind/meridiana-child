# 🚀 Deployment, Roadmap e Manutenzione

> **Ultimo aggiornamento**: 1 Novembre 2025
> **Stato del Progetto**: Sviluppo attivo, vicino al completamento delle funzionalità principali.

**Leggi anche**:
- Tutti gli altri file di documentazione per i dettagli di implementazione.

---

## 📅 Roadmap dello Sviluppo

### ✅ Fase 1: Fondamenta (Completata)

**Obiettivo**: Setup dell'ambiente di sviluppo e delle configurazioni di base.

- **Hosting e Core**: Attivato hosting WPmuDEV, installato WordPress, configurato SSL e backup.
- **Plugin**: Installati e attivati tutti i plugin necessari (ACF Pro, LearnDash, Defender Pro, etc.).
- **Child Theme**: Creata la struttura del tema child, configurato `package.json` e gli strumenti di build (Sass, Webpack).

### ✅ Fase 2: Core Features (Completata)

**Obiettivo**: Definizione della struttura dati, del design system e dei template di base.

- **CPT e Tassonomie**: Registrati tutti i CPT (`protocollo`, `modulo`, `convenzione`, etc.) e le tassonomie (`unita-offerta`, `profilo-professionale`) tramite ACF.
- **Campi ACF**: Creati tutti i field group per i CPT e per gli utenti.
- **Design System**: Implementato il sistema di colori, tipografia, spacing e componenti in SCSS.
- **Navigazione**: Realizzati i template per la sidebar desktop collassabile e la bottom bar mobile.
- **Template di Pagina**: Creati i template per le pagine principali (`page-home.php`, `archive.php`, `single-documento.php`, etc.).

### ✅ Fase 3: Advanced Features (Completata)

**Obiettivo**: Implementazione delle logiche di interazione complesse.

- **Sistema Utenti e Ruoli**: Creati i ruoli `gestore_piattaforma` e `subscriber` con le relative capabilities.
- **Membership Logic**: Implementato il sistema di login forzato e il blocco del backend per i non-amministratori.
- **Frontend Forms**: Sviluppata la Dashboard Gestore con un sistema CRUD custom basato su AJAX e Alpine.js.
- **File Archiving**: Implementato il sistema di archiviazione e pulizia automatica dei file PDF.
- **Analytics**: Creata la tabella custom `wp_document_views` e implementato il tracking delle visualizzazioni.

### 🟡 Fase 4: Notifiche e Automazioni (In Corso)

**Obiettivo**: Completare il sistema di notifiche e le automazioni per i corsi.

- **Notifiche Push**: Il sistema basato su ACF è funzionante. Da testare a fondo con segmenti complessi.
- **Automazioni Corsi**: Da implementare la logica per gli alert di scadenza dei certificati.

### ⬜ Fase 5: Contenuti e Testing (Da Iniziare)

**Obiettivo**: Popolare la piattaforma con i contenuti reali e condurre test utente (UAT).

- [ ] Caricamento di tutti i documenti, convenzioni, e altri contenuti.
- [ ] Creazione di un set di utenti di test con profili diversi.
- [ ] Esecuzione di test completi su tutte le funzionalità.

### ⬜ Fase 6: Launch (Da Iniziare)

**Obiettivo**: Mettere online la piattaforma.

- [ ] Eseguire la checklist pre-lancio.
- [ ] Eseguire il deploy in produzione.
- [ ] Monitorare la piattaforma e raccogliere feedback.

---

## ✅ Checklist Pre-Lancio

### Tecnica

- [ ] **WordPress e Plugin**: Tutti aggiornati all'ultima versione stabile.
- [ ] **PHP**: Versione 8.1+ attiva e stabile.
- [ ] **Performance**:
    - [ ] Lighthouse Score > 90 per mobile e desktop.
    - [ ] Caching (pagina, oggetti, browser) attivo e configurato.
    - [ ] CDN attiva per tutti gli asset.
    - [ ] Immagini ottimizzate (lazy loading e WebP abilitati).
- [ ] **Sicurezza**:
    - [ ] SSL attivo e forzato.
    - [ ] Security headers implementati.
    - [ ] Firewall di Defender Pro attivo.
    - [ ] Backup automatici giornalieri verificati.
- [ ] **Funzionalità**:
    - [ ] Tutti i form della Dashboard Gestore testati.
    - [ ] Sistema di archiviazione file verificato.
    - [ ] Tracking analytics funzionante e accurato.
    - [ ] Notifiche push e email testate.
- [ ] **Database**: Indici presenti su tutte le colonne interrogate frequentemente nelle tabelle custom.

### Contenuti e Utenti

- [ ] Contenuti iniziali caricati per tutti i CPT.
- [ ] Utenti reali importati e profilati correttamente con i campi ACF.
- [ ] Ruoli assegnati correttamente.

### Legale e Compliance

- [ ] **GDPR**: Privacy Policy pubblicata e completa. DPA firmati con i fornitori.
- [ ] **Accessibilità**: Verificata la conformità con WCAG 2.1 AA (navigazione da tastiera, contrasto, etc.).

---

## 🔄 Manutenzione Post-Lancio

### Settimanale
- **Aggiornamenti**: Controllare e applicare aggiornamenti di plugin e temi (prima in staging).
- **Backup**: Verificare l'esito positivo dei backup automatici.
- **Log**: Controllare i log degli errori per identificare eventuali problemi.

### Mensile
- **Audit di Sicurezza**: Eseguire una scansione completa con Defender Pro.
- **Performance**: Eseguire un audit con Lighthouse per monitorare le performance.
- **Database**: Ottimizzare le tabelle del database.

### Trimestrale
- **Utenti**: Rivedere e pulire gli utenti inattivi o licenziati.
- **Contenuti**: Archiviare o eliminare contenuti obsoleti.

---

## 📈 Scalabilità

- **Se Utenti > 500**: Valutare un upgrade del piano di hosting per maggiori risorse (RAM, CPU).
- **Se Documenti > 1000**: Considerare l'implementazione di un sistema di ricerca più performante (es. Elasticsearch) se la ricerca nativa dovesse rallentare.
- **Se Analytics > 1M di righe**: Attivare un cron job per archiviare i record di visualizzazione più vecchi di 2 anni per mantenere la tabella `wp_document_views` snella e performante.