# Struttura Dati (CPT, Tassonomie, Campi Custom)

> Ultimo aggiornamento: 22 ottobre 2025  
> Fonte: export ACF JSON in `acf-json/`

## Panoramica
- Tutte le entita custom (CPT e tassonomie) sono gestite tramite ACF Pro e sincronizzate nei JSON del tema child.
- Ogni CPT personalizzato ha `has_archive: true` e risponde agli URL `/slug/` standard di WordPress.
- I CPT sono pubblici, non gerarchici, disponibili nel REST API e mostrati nel menu di amministrazione.

### Riepilogo CPT
| CPT | Slug | Icona menu | Supports | Archivio | Tassonomie collegate |
| --- | --- | --- | --- | --- | --- |
| Protocollo | `protocollo` | `dashicons-list-view` | title, custom-fields | Yes (`/protocollo/`) | `unita-offerta`, `profilo-professionale` |
| Modulo | `modulo` | `dashicons-edit-large` | title, thumbnail, custom-fields | Yes (`/modulo/`) | `unita-offerta`, `profilo-professionale`, `area-competenza` |
| Convenzione | `convenzione` | `dashicons-database-export` | title, thumbnail, custom-fields | Yes (`/convenzione/`) | `category` |
| Organigramma | `organigramma` | `dashicons-info-outline` | title, thumbnail, custom-fields | Yes (`/organigramma/`) | nessuna custom |
| Salute e Benessere | `salute-e-benessere-l` | `dashicons-heart` | title, thumbnail, custom-fields | Yes (`/salute-e-benessere-l/`) | `category` |

> Anche il post type core `post` (Comunicazioni) utilizza la tassonomia `category`. I corsi LearnDash (`sfwd-courses`) sono definiti dal plugin.

---

## Dettaglio CPT

### 1. Protocollo (`protocollo`)
**Scopo**: documenti procedurali visualizzabili online (PDF non scaricabile).

- Voce di menu "Protocolli", visibile in admin bar e REST.
- Archiviazione attiva: `/protocollo/`.
- Tassonomie abilitate: `unita-offerta`, `profilo-professionale`.

**Campi ACF (group_protocollo)**
| Campo | Key | Tipo | Obbl. | Note |
| --- | --- | --- | --- | --- |
| PDF Protocollo | `field_pdf_protocollo` | file | Yes | Solo PDF; `return_format: id` |
| Riassunto | `field_riassunto_protocollo` | textarea | No | Breve descrizione |
| Moduli Allegati | `field_moduli_allegati` | relationship | No | Seleziona CPT `modulo`; filtra per tassonomia |
| Pianificazione ATS | `field_pianificazione_ats` | true_false | No | Flag ATS (toggle UI) |

### 2. Modulo (`modulo`)
**Scopo**: moduli operativi scaricabili.

- Menu "Moduli", archiviazione `/modulo/`.
- Tassonomie associate: `unita-offerta`, `profilo-professionale`, `area-competenza`.

**Campi ACF (group_modulo)**
| Campo | Key | Tipo | Obbl. | Note |
| --- | --- | --- | --- | --- |
| PDF Modulo | `field_pdf_modulo` | file | Yes | Solo PDF; `return_format: id` |

### 3. Convenzione (`convenzione`)
**Scopo**: convenzioni aziendali per welfare dipendenti.

- Menu "Convenzioni", archiviazione `/convenzione/`.
- Usa la tassonomia core `category` per eventuali gruppi tematici.

**Campi ACF (group_convenzione)**
| Campo | Key | Tipo | Obbl. | Note |
| --- | --- | --- | --- | --- |
| Convenzione Attiva | `field_convenzione_attiva` | true_false | No | Default attiva (toggle Attiva/Scaduta) |
| Descrizione | `field_descrizione_convenzione` | wysiwyg | Yes | Toolbar completa + media |
| Immagine in Evidenza | `field_immagine_convenzione` | image | No | `return_format: id`, anteprima medium |
| Contatti | `field_contatti_convenzione` | wysiwyg | No | Toolbar basic, niente media |
| Allegati | `field_allegati_convenzione` | repeater | No | Sotto-campi `file` (file), `descrizione` (text) |

### 4. Organigramma (`organigramma`)
**Scopo**: rubrica delle figure aziendali.

- Menu "Organigrammi", archiviazione `/organigramma/`.
- Nessuna tassonomia collegata; i filtri avvengono tramite campi select statici.

**Campi ACF (group_organigramma)**
| Campo | Key | Tipo | Obbl. | Note |
| --- | --- | --- | --- | --- |
| Ruolo | `field_ruolo_organigramma` | text | Yes | Titolo/posizione |
| UDO di riferimento | `field_udo_riferimento_organigramma` | select | No | Scelte fisse (Ambulatori, AP, CDI, ...) |
| Email aziendale | `field_email_aziendale` | email | No | |
| Telefono aziendale | `field_telefono_aziendale` | text | No | |

### 5. Salute e Benessere (`salute-e-benessere-l`)
**Scopo**: contenuti welfare per i lavoratori.

- Menu "Salute e Benessere Lavoratori", archiviazione `/salute-e-benessere-l/`.
- Usa la tassonomia `category`.

**Campi ACF (group_salute_benessere)**
| Campo | Key | Tipo | Obbl. | Note |
| --- | --- | --- | --- | --- |
| Contenuto | `field_contenuto_salute` | wysiwyg | Yes | Corpo principale dell'articolo |
| Risorse | `field_risorse_salute` | repeater | No | Ogni riga: `tipo` (select link/file), `titolo`, `url` (solo link), `file` (solo file) |

### 6. Comunicazioni (`post`)
- Post type core di WordPress per news aziendali.
- Usa `category` e tag standard.
- Condivide i template archivio con gli altri CPT (vedi `archive.php`).

### 7. Corsi (LearnDash)
- Post type `sfwd-courses` definito dal plugin LearnDash.
- Nessuna definizione ACF nel repository corrente; eventuali tassonomie custom vanno gestite dal plugin.

---

## Tassonomie Custom (ACF)
| Tassonomia | Slug | Gerarchica | Oggetti collegati | Note |
| --- | --- | --- | --- | --- |
| Unita di Offerta | `unita-offerta` | Yes | `protocollo`, `modulo`, `organigramma` | Reparti/servizi aziendali |
| Profilo Professionale | `profilo-professionale` | Yes | `protocollo`, `modulo` | Figure professionali target |
| Area di Competenza | `area-competenza` | Yes | `modulo` | Aree tematiche dei moduli |

**Impostazioni comuni**
- Public, visibili nel menu amministratore, nel REST (`show_in_rest: true`) e con rewrite attivo (`/slug/`).
- Permettono gestione completa (crea/modifica/elimina) a chi possiede `manage_categories`.

> I campi select statici presenti nei gruppi ACF (es. `udo_riferimento` per organigramma e utenti) replicano gli stessi valori delle tassonomie. Valutare la sincronizzazione futura per evitare duplicazioni manuali.

---

## Campi Custom Utente (group_user_fields)
| Campo | Key | Tipo | Obbl. | Note |
| --- | --- | --- | --- | --- |
| Stato Utente | `field_stato_utente` | radio | Yes | Valori: Attivo, Sospeso, Licenziato |
| Link Autologin Piattaforma Esterna | `field_link_autologin` | url | No | URL SSO per portali esterni |
| Codice Fiscale | `field_68f1eb8305594` | text | No | Campo libero |
| Profilo Professionale | `field_profilo_professionale_user` | select | No | Scelte statiche (label restituita) |
| UDO di Riferimento | `field_udo_riferimento_user` | select | No | Scelte statiche (label restituita) |

I valori sono accessibili tramite `get_field( field_name, 'user_' . $user_id )` e vengono gestiti dagli handler AJAX del tema per l'aggiornamento profilo.

---

## Note operative
- Dopo ogni modifica a CPT o tassonomie in ACF, esportare e versionare i JSON aggiornati (ACF Tools -> Export/Sync) e quindi fare flush dei permalink (Impostazioni -> Permalink -> Salva).
- I template PHP fanno affidamento sugli slug esatti: usare sempre `salute-e-benessere-l` e le tassonomie con trattino (`unita-offerta`, `profilo-professionale`, `area-competenza`).
- Le query per la documentazione frontend combinano `protocollo` e `modulo`: ricordarsi di includere entrambe le tassonomie nelle `tax_query` per filtri mirati.

Questo documento sostituisce tutte le versioni precedenti di `02_Struttura_Dati_CPT*.md`.
