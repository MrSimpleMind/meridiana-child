# 📊 Analytics e Tracking Visualizzazioni

> **Ultimo aggiornamento**: 1 Novembre 2025
> **Fonte**: `includes/analytics.php`, `includes/ajax-analytics.php`

**Leggi anche**:
- `02_Struttura_Dati_CPT.md` per i documenti tracciabili
- `08_Pagine_Template.md` per la dashboard analytics

---

## 🎯 Overview del Sistema Analytics

### Obiettivi

- **Tracciare le visualizzazioni uniche** dei documenti (`protocollo`, `modulo`) per utente e per versione del documento.
- **Fornire report di compliance** dettagliati per audit, mostrando chi ha visualizzato e chi non ha visualizzato un documento.
- **Dashboard per il Gestore** con KPI aggregati e analisi approfondite.
- **Esportazione CSV** delle liste di utenti per analisi offline.

### Stack Tecnologico

- **Tabella Database Custom**: `wp_document_views` per performance e scalabilità.
- **Endpoint AJAX**: Un set completo di endpoint per servire i dati alla dashboard in modo asincrono.
- **JavaScript (Alpine.js + Chart.js)**: Per il tracking lato client e la visualizzazione dei dati.

---

## 🗄 Schema del Database

Il cuore del sistema è la tabella custom `wp_document_views`, progettata per essere performante e scalabile.

**File di riferimento**: `includes/analytics.php`

### Struttura della Tabella

```sql
CREATE TABLE IF NOT EXISTS {$wpdb->prefix}document_views (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    document_id BIGINT NOT NULL,
    document_type VARCHAR(50) NOT NULL,
    user_profile VARCHAR(100) DEFAULT NULL, -- Profilo al momento della visualizzazione
    user_udo VARCHAR(100) DEFAULT NULL,      -- UDO al momento della visualizzazione
    document_version DATETIME NOT NULL,      -- Timestamp di modifica del documento
    view_timestamp DATETIME NOT NULL,
    view_duration INT DEFAULT NULL,          -- Durata in secondi
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    UNIQUE KEY unique_view_idx (user_id, document_id, document_version),
    INDEX timestamp_idx (view_timestamp),
    INDEX document_idx (document_id, document_type),
    INDEX profile_idx (user_profile),
    INDEX udo_idx (user_udo)
);
```

### Campi Chiave e Motivazioni

- **`document_version`**: Timestamp di `post_modified_gmt` del documento. È fondamentale per il tracciamento della compliance: una nuova visualizzazione viene registrata solo se l'utente non ha ancora visto *quella specifica versione* del documento.
- **`user_profile` e `user_udo`**: Salvano una fotografia del profilo e dell'UDO dell'utente al momento della visualizzazione. Questo permette analisi storiche accurate, anche se il profilo dell'utente cambia in futuro.
- **`unique_view_idx`**: Un indice `UNIQUE` sulla combinazione di `user_id`, `document_id`, e `document_version`. Questo previene l'inserimento di visualizzazioni duplicate per la stessa versione di un documento, garantendo che ogni riga rappresenti una visualizzazione unica e valida ai fini della compliance.

---

## 📡 Endpoint AJAX e REST

Il sistema si basa su un set di endpoint AJAX per fornire dati alla dashboard in modo dinamico.

**File di riferimento**: `includes/ajax-analytics.php`

| Azione AJAX | Funzione PHP | Scopo |
|---|---|---|
| `meridiana_analytics_search_users` | `meridiana_ajax_analytics_search_users` | Ricerca utenti per nome o email. |
| `meridiana_analytics_get_global_stats` | `meridiana_ajax_get_global_stats` | Fornisce statistiche globali (totale utenti, documenti, etc.). |
| `meridiana_analytics_get_users_by_profile` | `meridiana_ajax_get_users_by_profile` | Restituisce il breakdown degli utenti per profilo professionale e stato. |
| `meridiana_track_document_view` | `meridiana_ajax_track_document_view` | Endpoint principale per il tracciamento delle visualizzazioni. |
| `meridiana_analytics_get_content_distribution` | `meridiana_ajax_get_content_distribution` | Fornisce dati per il grafico di distribuzione dei contenuti. |
| `meridiana_analytics_get_user_views` | `meridiana_ajax_analytics_get_user_views` | Ottiene i documenti visualizzati da un utente specifico. |
| `meridiana_analytics_search_documents` | `meridiana_ajax_analytics_search_documents` | Ricerca documenti per titolo. |
| `meridiana_analytics_get_document_insights` | `meridiana_ajax_analytics_get_document_insights` | Fornisce dettagli su chi ha visto e chi non ha visto un documento. |
| `meridiana_analytics_get_views_by_profile_*` | `meridiana_ajax_analytics_get_views_by_profile_*` | Dati per la matrice di visualizzazioni per profilo. |

---

## 🔍 Tracking Lato Client

Il tracciamento è gestito da un componente Alpine.js che monitora il tempo di permanenza sulla pagina di un documento.

**File di riferimento**: `assets/js/src/tracking.js`

### Logica di Funzionamento

1.  **Inizializzazione**: Al caricamento della pagina di un documento, il componente `documentTracker` avvia un timer.
2.  **Invio Dati**: La visualizzazione viene inviata all'endpoint `meridiana_track_document_view` solo quando l'utente lascia la pagina (`beforeunload` event) o cambia tab (`visibilitychange` event).
3.  **Durata Minima**: Una visualizzazione viene considerata valida e tracciata solo se la durata è di almeno 5 secondi.
4.  **`keepalive: true`**: L'opzione `keepalive` nella richiesta `fetch` assicura che la chiamata AJAX venga completata anche se la pagina si sta chiudendo.

### Implementazione nel Template

```php
// single-documento.php

<div x-data="documentTracker(<?php echo get_the_ID(); ?>)">
    <?php // Contenuto del documento con il PDF embedder ?>
</div>
```

---

## 📈 Funzioni di Query Principali

Il file `includes/analytics.php` contiene diverse funzioni per interrogare la tabella `document_views`.

- **`meridiana_get_document_views($doc_id)`**: Restituisce il numero di visualizzazioni per un documento.
- **`meridiana_get_unique_document_views_by_user($doc_id)`**: Ottiene gli utenti unici che hanno visualizzato una versione di un documento.
- **`meridiana_get_users_who_not_viewed($doc_id)`**: Restituisce un elenco di utenti attivi che non hanno ancora visualizzato l'ultima versione di un documento. Questa è una query fondamentale per i report di compliance.
- **`meridiana_get_views_by_professional_profile($doc_type)`**: Aggrega i dati per la matrice di visualizzazioni, mostrando la copertura per profilo professionale.

---

## 📊 Dashboard Analytics

La pagina `/analitiche/` offre una visione completa dei dati raccolti, suddivisa in sezioni.

- **Panoramica**: KPI principali (utenti totali, documenti totali) e grafici sulla distribuzione degli utenti per profilo e stato.
- **Analisi Contenuti**: Una tabella dettagliata con tutti i documenti tracciabili, con metriche come visualizzazioni uniche, totali e durata media.
- **Analisi Utenti**: Un'interfaccia per cercare un utente e vedere in dettaglio quali documenti ha visualizzato.
- **Analisi Documento**: Un'interfaccia per cercare un documento e ottenere due liste: chi lo ha visualizzato e chi (tra gli utenti attivi) non lo ha ancora fatto, con la possibilità di esportare entrambe le liste in formato CSV.

---

## 🤖 Checklist per Sviluppo

- **Indici Database**: Assicurarsi che la tabella `document_views` abbia indici appropriati su `user_id`, `document_id`, `view_timestamp`, `user_profile` e `user_udo` per garantire query veloci.
- **Caching**: Utilizzare la Transient API di WordPress (`get_transient`, `set_transient`) per mettere in cache i risultati delle query più pesanti (es. statistiche globali) per almeno 1 ora.
- **Permessi**: Proteggere sempre gli endpoint AJAX e le pagine di analytics con `current_user_can('view_analytics')`.
- **Scalabilità**: Monitorare la dimensione della tabella `document_views`. Se supera 1 milione di righe, considerare l'implementazione di un sistema di archiviazione o partitioning del database.