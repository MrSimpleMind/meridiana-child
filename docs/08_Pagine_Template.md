# 📄 Struttura Pagine e Template

> **Ultimo aggiornamento**: 1 Novembre 2025
> **Fonte**: directory `templates/`, `page-*.php`, `single-*.php`, `archive.php`

**Leggi anche**:
- `01_Design_System.md` per i componenti UI
- `04_Navigazione_UX.md` per la struttura della navigazione
- `02_Struttura_Dati_CPT.md` per le query dei contenuti

---

## 📐 Architettura Generale dei Template

La piattaforma utilizza un'architettura di template flessibile e modulare, basata su un layout principale e template part riutilizzabili.

### Layout Principale

Ogni pagina della piattaforma condivide una struttura comune:

```php
// Inizio di un template di pagina (es. page-home.php)

get_header(); // Carica l'header, che a sua volta include la sidebar di navigazione
?>

<div class="content-wrapper">
    <div class="container">
        <?php // Contenuto specifico della pagina ?>
    </div>
</div>

<?php
get_footer(); // Carica il footer, che a sua volta include la bottom bar per il mobile
```

- **`get_header()`**: Include `header.php`, che renderizza la sidebar di navigazione (`sidebar-nav.php`) per la versione desktop.
- **`.content-wrapper`**: Un contenitore che si adatta dinamicamente alla larghezza della sidebar (espansa o collassata).
- **`.container`**: Limita la larghezza del contenuto e applica un padding laterale consistente.
- **`get_footer()`**: Include `footer.php`, che renderizza la bottom navigation bar (`bottom-nav.php`) per la versione mobile.

---

## 🏠 Home Page (Dashboard Utente)

**Template**: `page-home.php`

Pagina di atterraggio per tutti gli utenti dopo il login. Fornisce un riepilogo delle attività recenti e accesso rapido alle sezioni principali.

### Sezioni Principali

- **Quick Actions**: Pulsanti per le azioni più comuni (Cerca Protocolli, I Miei Corsi, etc.).
- **Feed Attività**: Elenco degli ultimi contenuti pubblicati (Comunicazioni, Convenzioni, Salute e Benessere).
- **I Miei Progressi**: Riepilogo dei corsi in corso e completati (integrazione con LearnDash).

### Template Parts Utilizzati

- `templates/parts/home/convenzioni-carousel.php`
- `templates/parts/home/news-list.php`
- `templates/parts/home/salute-list.php`

---

## 🗂️ Archivi Unificati

**Template**: `archive.php`

Un unico template gestisce le pagine di archivio per tutti i CPT (`post`, `convenzione`, `salute-e-benessere-l`, etc.), ad eccezione della documentazione che ha una sua pagina dedicata.

### Logica di Funzionamento

Il template `archive.php` utilizza `get_post_type()` per determinare il tipo di contenuto da visualizzare e adatta dinamicamente il titolo e le card.

```php
// in archive.php

$post_type = get_post_type();
$archive_title = get_the_archive_title();

// ...

<div class="archive-grid">
    <?php while (have_posts()) : the_post(); ?>
        <?php 
        // Carica la card appropriata in base al post type
        if ($post_type === 'post') {
            get_template_part('templates/parts/cards/card-article');
        } elseif ($post_type === 'convenzione') {
            get_template_part('templates/parts/cards/card-convenzione');
        } else {
            // Card di default
        }
        ?>
    <?php endwhile; ?>
</div>
```

---

## 📄 Pagine Singole

### Documento Singolo (Protocollo/Modulo)

**Template**: `single-documento.php`

Questo template unificato gestisce la visualizzazione sia dei `protocolli` che dei `moduli`.

- **Trigger**: Viene caricato grazie a un filtro su `template_include` in `functions.php`.
- **Logica Condizionale**: All'interno del template, `get_post_type()` viene usato per differenziare la visualizzazione:
  - **Protocollo**: Mostra il PDF embeddato (non scaricabile) e avvia il tracking della visualizzazione.
  - **Modulo**: Mostra un pulsante per il download diretto del file.
- **Metadati**: Visualizza tassonomie collegate (UDO, Profilo Professionale) e un riassunto.
- **Moduli Correlati**: Se è un protocollo, elenca i moduli collegati tramite il campo relationship di ACF.

### Altri Post Singoli

- **`single-convenzione.php`**: Dettaglio di una convenzione, con descrizione, contatti e allegati.
- **`single-salute-e-benessere-l.php`**: Articolo di salute e benessere, con contenuto e risorse correlate.
- **`single.php`**: Template di fallback per i post standard (Comunicazioni).
- **`single-sfwd-courses.php`**: Template per i corsi di LearnDash, che si integra con la struttura del plugin.

---

## 🛠️ Pagine Funzionali

### Dashboard Gestore

**Template**: `page-dashboard-gestore.php`

Pagina accessibile solo ai ruoli `gestore_piattaforma` e `administrator`. È una vera e propria applicazione frontend per la gestione dei contenuti, basata su **Alpine.js** e **AJAX**.

- **Struttura a Tab**: La navigazione è suddivisa in tab (Documenti, Utenti, Comunicazioni, etc.).
- **Operazioni CRUD**: Ogni tab permette di creare, modificare ed eliminare contenuti senza ricaricare la pagina.
- **Modal per Form**: I form di inserimento/modifica vengono caricati dinamicamente in un modal.
- **Template Parts**: Ogni tab carica un template part dedicato (es. `templates/parts/gestore/tab-documenti.php`).

### Pagina Analytics

**Template**: `page-analitiche.php`

Dashboard per la visualizzazione dei dati di tracking, accessibile solo ai gestori e amministratori.

- **Componenti**: KPI globali, grafici (distribuzione utenti, contenuti), e tabelle dati.
- **Interattività**: Filtri per data, tipo di documento, UDO, e ricerca per utenti e documenti.
- **Drill-down**: Dalle tabelle principali è possibile accedere a viste di dettaglio per singolo documento o utente.

### Pagina Contatti

**Template**: `page-contatti.php`

Visualizza l'organigramma aziendale, recuperando i dati dal CPT `organigramma`.

---

## 🤖 Checklist per Sviluppo

- **Modularità**: Utilizzare `get_template_part()` il più possibile per creare componenti riutilizzabili (es. le card).
- **Wrapper di Contenuto**: Iniziare sempre un nuovo template con `get_header()` e `<div class="content-wrapper">` e chiuderlo con `</div>` e `get_footer()`.
- **Permessi**: All'inizio di ogni template che mostra dati sensibili (es. `page-analitiche.php`), inserire un controllo sui permessi con `current_user_can()`.
- **Query**: Utilizzare `WP_Query` per le liste di contenuti custom e ricordarsi di usare `wp_reset_postdata()` dopo ogni loop custom.
- **Dati Dinamici**: Evitare testo hard-coded. Usare `get_the_title()`, `the_content()`, `get_field()` etc. per popolare i template.