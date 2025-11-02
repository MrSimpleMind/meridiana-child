# Struttura Dati (CPT, Tassonomie, Campi Custom)

> **Ultimo aggiornamento**: 1 Novembre 2025
> **Fonte**: `acf-json/` directory, `includes/` directory

## Panoramica

La struttura dati della piattaforma è interamente gestita tramite **ACF Pro**, con tutte le configurazioni (Custom Post Types, Tassonomie, Campi Custom) sincronizzate tramite file JSON nella directory `acf-json/` del tema. Questo garantisce il versionamento e la coerenza tra diversi ambienti.

- **CPT**: Tutti i CPT sono pubblici, non gerarchici, esposti nel REST API e hanno un archivio attivo.
- **Tassonomie**: Tutte le tassonomie custom sono gerarchiche e pubbliche.
- **Campi Utente**: I campi custom per gli utenti sono gestiti da ACF e applicati a tutti i ruoli.

---

## Riepilogo CPT

| CPT | Slug | Icona Menu | `supports` | Archivio | Tassonomie Collegate |
|---|---|---|---|---|---|
| **Protocollo** | `protocollo` | `dashicons-list-view` | `title`, `custom-fields` | `/protocollo/` | `unita-offerta`, `profilo-professionale` |
| **Modulo** | `modulo` | `dashicons-edit-large` | `title`, `thumbnail`, `custom-fields` | `/modulo/` | `unita-offerta`, `profilo-professionale`, `area-competenza` |
| **Convenzione** | `convenzione` | `dashicons-database-export` | `title`, `thumbnail`, `custom-fields` | `/convenzione/` | `category` |
| **Organigramma** | `organigramma` | `dashicons-info-outline` | `title`, `thumbnail`, `custom-fields` | `/organigramma/` | `unita-offerta` |
| **Salute e Benessere**| `salute-e-benessere-l` | `dashicons-heart` | `title`, `thumbnail`, `custom-fields` | `/salute-e-benessere-l/` | `category` |

> **Nota**: Il CPT `post` (Comunicazioni) e i CPT di LearnDash (`sfwd-courses`, `sfwd-quiz`, etc.) sono gestiti nativamente da WordPress e dal plugin LearnDash.

---

## Dettaglio CPT e Campi ACF

### 1. Protocollo (`protocollo`)

**Scopo**: Documenti procedurali e linee guida operative, visualizzabili online tramite PDF embedder (non scaricabili).

**File ACF**: `group_protocollo.json`

| Campo | Key | Tipo | Obbl. | Note |
|---|---|---|---|---|
| PDF Protocollo | `field_pdf_protocollo` | file | Sì | Solo PDF. `return_format: id`. |
| Riassunto | `field_riassunto_protocollo` | textarea | No | Breve descrizione del contenuto. |
| Moduli Allegati | `field_moduli_allegati` | relationship | No | Collega uno o più CPT `modulo`. `return_format: id`. |
| Pianificazione ATS | `field_pianificazione_ats` | true_false | No | Flag per protocolli relativi alla pianificazione ATS. |

---

### 2. Modulo (`modulo`)

**Scopo**: Documenti operativi scaricabili (checklist, form, template).

**File ACF**: `group_modulo.json`

| Campo | Key | Tipo | Obbl. | Note |
|---|---|---|---|---|
| PDF Modulo | `field_pdf_modulo` | file | Sì | Solo PDF. `return_format: id`. |

---

### 3. Convenzione (`convenzione`)

**Scopo**: Gestione delle convenzioni aziendali per il welfare dei dipendenti.

**File ACF**: `group_convenzione.json`

| Campo | Key | Tipo | Obbl. | Note |
|---|---|---|---|---|
| Convenzione Attiva | `field_convenzione_attiva` | true_false | No | Default: `true`. UI on/off per "Attiva"/"Scaduta". |
| Descrizione | `field_descrizione_convenzione` | wysiwyg | Sì | Editor completo con upload media. |
| Immagine in Evidenza | `field_immagine_convenzione` | image | No | `return_format: id`. |
| Contatti | `field_contatti_convenzione` | wysiwyg | No | Editor di base senza upload media. |
| Allegati | `field_allegati_convenzione` | repeater | No | Sottocampi: `file` (file, `return_format: array`), `descrizione` (text). |

---

### 4. Organigramma (`organigramma`)

**Scopo**: Rubrica delle figure professionali e dei ruoli chiave all'interno della cooperativa.

**File ACF**: `group_organigramma.json`

| Campo | Key | Tipo | Obbl. | Note |
|---|---|---|---|---|
| Ruolo | `field_ruolo_organigramma` | text | Sì | Posizione o titolo della persona. |
| Unità di Offerta | `field_udo_riferimento_organigramma` | select | No | Scelte statiche che replicano la tassonomia `unita-offerta`. |
| Email Aziendale | `field_email_aziendale` | email | No | Indirizzo email di contatto. |
| Telefono Aziendale | `field_telefono_aziendale` | text | No | Numero di telefono di contatto. |

---

### 5. Salute e Benessere (`salute-e-benessere-l`)

**Scopo**: Articoli e risorse per il benessere dei lavoratori.

**File ACF**: `group_salute_benessere.json`

| Campo | Key | Tipo | Obbl. | Note |
|---|---|---|---|---|
| Contenuto | `field_contenuto_salute` | wysiwyg | Sì | Corpo principale dell'articolo. |
| Risorse | `field_risorse_salute` | repeater | No | Sottocampi: `tipo` (select: link/file), `titolo` (text), `url` (url), `file` (file). |

---

## Tassonomie Custom (ACF)

| Tassonomia | Slug | Gerarchica | Oggetti Collegati | File ACF |
|---|---|---|---|---|
| **Unità di Offerta** | `unita-offerta` | Sì | `protocollo`, `modulo`, `organigramma` | `taxonomy_68e50c5353289.json` |
| **Profilo Professionale** | `profilo-professionale` | Sì | `protocollo`, `modulo` | `taxonomy_68e510b2d2b3c.json` |
| **Area di Competenza** | `area-competenza` | Sì | `modulo` | `taxonomy_68e511a521483.json` |

**Impostazioni Comuni**:
- Tutte le tassonomie sono pubbliche, visibili nel menu di amministrazione e nel REST API.
- Hanno `rewrite` attivo per permettere la navigazione agli archivi (`/unita-offerta/nome-udo/`).
- La gestione dei termini è consentita a chi ha la capability `manage_categories`.

---

## Campi Custom Utente

**Scopo**: Profilare gli utenti per segmentare l'accesso ai contenuti e le notifiche.

**File ACF**: `group_user_fields.json`

| Campo | Key | Tipo | Obbl. | Note |
|---|---|---|---|---|
| Stato Utente | `field_stato_utente` | radio | Sì | Valori: `attivo`, `sospeso`, `licenziato`. Default: `attivo`. |
| Link Autologin Esterno | `field_link_autologin` | url | No | URL per SSO a piattaforme di formazione esterne. |
| Codice Fiscale | `field_68f1eb8305594` | text | No | |
| Profilo Professionale | `field_profilo_professionale_user` | select | No | Scelte statiche che replicano la tassonomia `profilo-professionale`. `return_format: label`. |
| UDO di Riferimento | `field_udo_riferimento_user` | select | No | Scelte statiche che replicano la tassonomia `unita-offerta`. `return_format: label`. |

**Accesso ai Dati**:
I valori dei campi utente sono accessibili tramite `get_field( 'nome_campo', 'user_' . $user_id )`.

---

## Sistema di Notifiche (ACF)

La configurazione delle notifiche push è interamente gestita tramite Pagine di Opzioni ACF.

- **Pagina Opzioni**: "Configurazione Notifiche" (`ui_options_page_690209ffe5d1c.json`)
- **Sotto-pagina**: "Configurazione OneSignal" (`ui_options_page_69020d64aabb9.json`)

**Campi ACF**:
- **Credenziali OneSignal** (`group_notification_onesignal_setup.json`): `App ID` e `REST API Key`.
- **Segmentazioni** (`group_notification_segmentazioni.json`): Un repeater per definire i gruppi di utenti target (es. "Tutti", "Solo Medici", "Solo UDO RSA").
- **Triggers** (`group_notification_triggers.json`): Un repeater per definire quale evento (es. pubblicazione di un `protocollo`) invia quale notifica a quale segmento.

Questo approccio permette una gestione flessibile e granulare delle notifiche direttamente dal backend di WordPress, senza dover modificare il codice.

---

## Note Operative

- **Sincronizzazione**: Dopo ogni modifica a CPT, tassonomie o campi in ACF, i file JSON in `acf-json/` vengono aggiornati automaticamente. È fondamentale committare questi file su Git.
- **Permalink**: In caso di problemi di routing dopo una modifica alla struttura, è buona norma visitare `Impostazioni > Permalink` e salvare le modifiche per fare un flush delle rewrite rules.
- **Query**: Quando si costruiscono query complesse (es. per la pagina "Documentazione"), è necessario includere tutti i CPT e le tassonomie pertinenti per garantire che i filtri funzionino correttamente.