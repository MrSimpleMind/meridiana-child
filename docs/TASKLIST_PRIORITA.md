# ✅ TaskList, Roadmap e Proposte di Miglioramento

> **Ultimo aggiornamento**: 1 Novembre 2025
> **Stato del Progetto**: Sviluppo Funzionalità Principali al 100%.

Questo documento delinea lo stato attuale del progetto, la roadmap per il completamento e una serie di proposte strategiche per migliorare la piattaforma.

---

## 🚀 Stato Attuale del Progetto

La piattaforma ha raggiunto un stadio di sviluppo avanzato. Tutte le fondamenta architetturali e le funzionalità di base sono state implementate e sono stabili.

### ✅ Funzionalità Completate

- **Architettura del Tema**: Tema child basato su Blocksy, con una struttura di file modulare e build process per SCSS/JS.
- **Struttura Dati**: Tutti i CPT (`protocollo`, `modulo`, `convenzione`, etc.) e le tassonomie custom sono state implementate tramite ACF e sono gestite via JSON.
- **Sistema di Navigazione**: Implementata una navigazione ibrida e responsive:
  - **Desktop**: Sidebar verticale collassabile con stato persistente.
  - **Mobile**: Bottom navigation bar in stile app.
- **Dashboard Gestore**: Una completa interfaccia frontend per la gestione di tutti i contenuti (documenti, utenti, comunicazioni, etc.) tramite un'applicazione AJAX/Alpine.js, senza accesso al backend di WordPress.
- **Sistema di Archiviazione File**: Un robusto sistema automatico archivia le versioni precedenti dei file PDF quando vengono sostituiti e li elimina in modo sicuro quando un documento viene cancellato.
- **Sistema di Analytics Completo**: Una tabella custom (`wp_document_views`) traccia le visualizzazioni dei documenti. La dashboard analytics offre una panoramica completa con KPI, grafici, tabelle dati interattive, analisi per singolo utente e per singolo documento, e funzionalità di export CSV.
- **Ricerca Avanzata**: È attivo un sistema di ricerca lato client con **Fuse.js**, che offre una ricerca "fuzzy" (tollerante agli errori di battitura) e istantanea sui documenti.
- **Sistema di Ruoli e Permessi**: Definiti i ruoli `Gestore Piattaforma` e `Utente Standard` con capabilities specifiche.
- **Documentazione**: Tutta la documentazione interna nella directory `/docs` è stata revisionata e aggiornata per riflettere lo stato attuale del codice.

---

## 🎯 Roadmap per il Completamento

Di seguito, i prossimi step ordinati per priorità logica per arrivare al lancio della piattaforma.

### Fase 1: Completamento Funzionalità Core (Priorità Alta)

**Obiettivo**: Finalizzare le funzionalità rimanenti per raggiungere il feature-complete.

1.  **Implementare le Automazioni dei Corsi (LearnDash)**:
    - [ ] **Alert Scadenza Certificati**: Creare il cron job giornaliero che controlla i certificati in scadenza (a 7 giorni) e invia notifiche push ed email agli utenti.
    - [ ] **Re-enrollment Automatico**: Implementare la logica che re-iscrive automaticamente un utente a un corso quando il suo certificato scade.

### Fase 2: Contenuti e User Acceptance Testing (UAT) (Priorità Media)

**Obiettivo**: Popolare la piattaforma e validare le funzionalità con utenti reali.

1.  **Caricamento Contenuti**: Importare tutti i documenti (`protocolli`, `moduli`), `convenzioni`, `comunicazioni` e altri contenuti iniziali.
2.  **Creazione Utenti Pilota**: Creare un gruppo di 15-20 utenti reali con profili e ruoli diversi.
3.  **Sessioni di UAT**: Guidare gli utenti pilota nel testare tutte le funzionalità chiave:
    - Navigazione e ricerca documenti.
    - Completamento di un corso e download del certificato.
    - Visualizzazione delle notifiche.
    - (Per i Gestori) Creazione e modifica di un contenuto tramite la Dashboard Gestore.
4.  **Raccolta Feedback**: Raccogliere e analizzare i feedback per identificare bug o aree di miglioramento dell'UX.

### Fase 3: Lancio e Go-Live (Priorità Bassa)

**Obiettivo**: Eseguire il deploy in produzione e monitorare il lancio.

1.  **Eseguire la Checklist di Pre-Lancio**: Verificare tutti i punti della checklist nel documento `10_Deployment_Checklist.md`.
2.  **Deploy**: Eseguire il deploy in ambiente di produzione.
3.  **Monitoraggio Iniziale**: Monitorare attentamente i log, le performance e l'uptime per le prime 48 ore.

---

## 💡 Proposte e Miglioramenti Strategici

Considerando l'obiettivo di una piattaforma PWA performante per gli utenti e una gestione efficiente per i gestori, ecco alcune proposte per migliorare ulteriormente il progetto.

### Per l'Utente Finale (Mobile-First)

1.  **Modalità Offline Migliorata (PWA)**:
    - **Proposta**: Oltre al caching di base, permettere agli utenti di **salvare offline documenti specifici** (es. i 5 protocolli più importanti per il loro ruolo) per accedervi anche senza connessione.
    - **Vantaggi**: Aumenta drasticamente l'utilità per gli operatori sul campo in zone con scarsa connettività.

2.  **Gamification per i Corsi**:
    - **Proposta**: Introdurre elementi di gamification come badge per il completamento di percorsi formativi, classifiche (opzionali e anonime) per UDO, e "strisce" di corsi completati.
    - **Vantaggi**: Aumenta il coinvolgimento e la motivazione a completare la formazione.

### Per il Gestore della Piattaforma (Desktop)

1.  **Dashboard Analytics Potenziata**:
    - **Proposta**: Aggiungere la possibilità di creare **report personalizzati** (es. "visualizzazioni del protocollo X da parte degli OSS della RSA nell'ultimo mese") e di salvarli per un accesso rapido.
    - **Vantaggi**: Trasforma la dashboard da uno strumento di visualizzazione a uno strumento di analisi proattiva.

2.  **Sistema di Annunci Mirati**:
    - **Proposta**: Creare un'interfaccia nella Dashboard Gestore per inviare **annunci o notifiche push a segmenti di utenti specifici** (es. "A tutti gli infermieri", "Solo al personale della CDI"), sfruttando il sistema di segmentazione già esistente.
    - **Vantaggi**: Comunicazioni più efficaci e mirate.

### Miglioramenti Architetturali

1.  **Sincronizzazione tra Tassonomie e Campi Select**:
    - **Proposta**: Attualmente, i campi select per "Profilo Professionale" e "UDO" negli utenti sono statici. Creare una funzione che popoli dinamicamente queste scelte basandosi sui termini delle tassonomie corrispondenti.
    - **Vantaggi**: Riduce la manutenzione e previene discrepanze tra i dati.

2.  **Componenti Web (Web Components)**:
    - **Proposta**: Per il futuro, considerare di incapsulare alcuni componenti riutilizzabili (es. le card, i modal) in Web Components nativi. Questo li renderebbe agnostici da qualsiasi framework (incluso Alpine.js) e più manutenibili nel lungo periodo.
    - **Vantaggi**: Maggiore manutenibilità e portabilità del codice.
