# 📋 REQUISITI SISTEMA ANALYTICS - GRIGLIA PROTOCOLLI × PROFILI

**Versione**: 1.0
**Data**: 30 Ottobre 2025
**Autore**: Utente / Meridiana Platform
**Status**: Approvato - Pronto per Implementazione

---

## 1. EXECUTIVE SUMMARY

Il sistema Analytics della piattaforma Meridiana deve tracciare le visualizzazioni dei protocolli con granularità per profilo professionale, al fine di:

1. **Misurare l'engagement** di specifiche categorie professionali verso specifici protocolli
2. **Identificare gap formativi** attraverso analisi di percentuale di fruizione
3. **Fornire reportistica visuale** tramite matrice interattiva (Battaglia Navale)

Questo documento specifica il comportamento funzionale, i dati tracciati, e il formato di visualizzazione.

---

## 2. PROBLEMA SPECIFICO

### 2.1 Contesto Attuale

La piattaforma Meridiana è una LMS (Learning Management System) per la formazione aziendale di una cooperativa sanitaria. Contiene:
- **Protocolli**: Documenti di procedura operativa (es. "Whistleblowing")
- **Moduli**: Contenuti formativi tematici
- **Utenti**: Divisi in profili professionali (Infermieri, Medici, ASA/OSS, etc.)

### 2.2 Il Problema

**Attualmente**: Non esiste una visione aggregata di quali profili professionali hanno consultato quali protocolli.

**Necessità**:
- Capire se il 100% degli infermieri ha visto il protocollo "Whistleblowing"
- Capire se solo il 30% dei medici ha visto il protocollo "Emergenze"
- Identificare quali gruppi professionali mancano alla fruizione di documentazione critica

---

## 3. REQUISITI FUNZIONALI

### 3.1 Tracciamento Visualizzazioni Uniche

**RF-1: Registrazione Visualizzazione Unica**

```
SCENARIO: Un infermiere (Mario Rossi) visualizza il protocollo "Whistleblowing"
├─ T0: Mario apre la pagina del protocollo
├─ T0+5sec: Sistema registra in DB una visualizzazione unica
├─ T0+10sec: Mario ricerca di nuovo il protocollo e lo apre
└─ T0+15sec: Sistema NON registra una nuova visualizzazione (già esiste!)

RISULTATO: DB contiene UNA SOLA visualizzazione per (Mario, Whistleblowing)
```

**Caratteristica Chiave**: Duplicati non consentiti durante la stessa "versione" del documento.

---

**RF-2: Cattura Profilo Professionale**

```
SCENARIO: Un utente visualizza un protocollo per la prima volta
├─ Sistema legge il campo "profilo_professionale" dall'utente
├─ Sistema registra il profilo al momento della visualizzazione
├─ Profilo salvato in DB accompagna la visualizzazione (immutabile)

RESULT: Ogni visualizzazione contiene il profilo dell'utente al momento della vista
```

**Motivo**: Il profilo di un utente potrebbe cambiare nel tempo (promozione, trasferimento). La visualizzazione deve riflettere il profilo al momento della fruizione, non quello odierno.

---

**RF-3: Reset su Modifica Documento**

```
SCENARIO: Whistleblowing è stato visualizzato 87 volte
├─ Gestore aggiorna il documento (es. aggiunge nuovi punti)
├─ Sistema rileva post_modified > precedente
├─ Sistema considera la nuova versione come "nuovo documento"
├─ Nuove visualizzazioni partono da ZERO

RISULTATO:
- Versione Vecchia: 87 visualizzazioni
- Versione Nuova (post-modifica): 0 visualizzazioni

TRACCIAMENTO TEMPORALE:
├─ 01/10 - 15/10: Versione 1 → 87 views
├─ 16/10: Documento modificato!
└─ 16/10 - 30/10: Versione 2 → 5 views (nuovo ciclo)
```

**Importanza**: Un documento aggiornato ha contenuto diverso. Le vecchie visualizzazioni non indicano se gli utenti hanno visto la versione aggiornata.

---

### 3.2 Aggregazione Dati per Griglia

**RF-4: Conteggio Visualizzazioni per Profilo × Protocollo**

```
QUERY LOGICA:
┌─────────────────────────────────────────┐
│ Per ogni COMBINAZIONE (Protocollo, Profilo)
│ CONTA le visualizzazioni uniche
└─────────────────────────────────────────┘

ESEMPIO OUTPUT:
┌──────────────────┬──────────┬────────┬─────────┐
│ Protocollo       │ Infermieri │ Medici │ ASA/OSS │
├──────────────────┼──────────┼────────┼─────────┤
│ Whistleblowing   │ 12       │ 5      │ 8       │
│ Emergenze        │ 3        │ 15     │ 2       │
│ Igiene Mani      │ 18       │ 1      │ 20      │
└──────────────────┴──────────┴────────┴─────────┘

INTERPRETAZIONE:
- 12 infermieri unici hanno visualizzato Whistleblowing
- 5 medici unici hanno visualizzato Whistleblowing
- 8 ASA/OSS unici hanno visualizzato Whistleblowing
```

---

**RF-5: Conteggio Totale Utenti per Profilo**

```
QUERY LOGICA:
┌─────────────────────────────────────────┐
│ Per ogni PROFILO PROFESSIONALE
│ CONTA quanti utenti lo hanno
│ (indipendentemente dalle loro visualizzazioni)
└─────────────────────────────────────────┘

ESEMPIO OUTPUT:
┌──────────────────┬─────────┐
│ Profilo          │ Totale  │
├──────────────────┼─────────┤
│ Infermieri       │ 50      │
│ Medici           │ 15      │
│ ASA/OSS          │ 30      │
│ Educatori        │ 22      │
└──────────────────┴─────────┘

UTILIZZO: Serve come DENOMINATORE per calcolare percentuali
```

---

**RF-6: Calcolo Percentuale di Engagement**

```
FORMULA:
┌────────────────────────────────────────┐
│ % = (Visualizzazioni Uniche / Totale Utenti) × 100
└────────────────────────────────────────┘

ESEMPIO CONCRETO:
┌─────────────────────────────────────────────────┐
│ Whistleblowing × Infermieri
├─────────────────────────────────────────────────┤
│ Visualizzazioni uniche: 12
│ Totale infermieri: 50
│ Percentuale: (12 / 50) × 100 = 24%
│ Interpretazione: Solo il 24% degli infermieri ha
│                  visualizzato Whistleblowing
└─────────────────────────────────────────────────┘

ALTRO ESEMPIO:
┌─────────────────────────────────────────────────┐
│ Igiene Mani × ASA/OSS
├─────────────────────────────────────────────────┤
│ Visualizzazioni uniche: 20
│ Totale ASA/OSS: 30
│ Percentuale: (20 / 30) × 100 = 66.67%
│ Interpretazione: Due terzi degli ASA/OSS
│                  hanno visualizzato il protocollo
└─────────────────────────────────────────────────┘
```

---

### 3.3 Presentazione Visuale

**RF-7: Griglia Interattiva "Battaglia Navale"**

```
LAYOUT TABELLA:
┌──────────────────┬───────────────┬────────────┬─────────────┬──────────┐
│ Protocollo       │ Infermieri    │ Medici     │ ASA/OSS     │ Educatori│
│ (righe)          │ (n=50)        │ (n=15)     │ (n=30)      │ (n=22)   │
├──────────────────┼───────────────┼────────────┼─────────────┼──────────┤
│ Whistleblowing   │ 12 (24%)      │ 5 (33%)    │ 8 (26%)     │ 6 (27%)  │
│                  │ ████░░░░░     │ █████░░░░░ │ ██████░░░░░ │ ██████░░ │
├──────────────────┼───────────────┼────────────┼─────────────┼──────────┤
│ Emergenze        │ 3 (6%)        │ 15 (100%)  │ 2 (6%)      │ 1 (4%)   │
│                  │ ░░░░░░░░░░    │ ██████████ │ ░░░░░░░░░░  │ ░░░░░░░░░░
├──────────────────┼───────────────┼────────────┼─────────────┼──────────┤
│ Igiene Mani      │ 18 (36%)      │ 1 (6%)     │ 20 (66%)    │ 14 (63%) │
│                  │ ███████░░░░░  │ ░░░░░░░░░░ │ █████████░░ │ █████████░
└──────────────────┴───────────────┴────────────┴─────────────┴──────────┘

INTESTAZIONI:
- Colonna "Profilo (n=X)": Mostra il totale di utenti con quel profilo
- Celle: Mostrano sia COUNT che PERCENTUALE

BARRE PERCENTUALI (opzionali):
- Visualizzazione visuale della % di engagement
- Lunghezza proporzionale al valore (0-100%)
```

---

**RF-8: Color Coding Celle**

```
SCALE COLORI IN BASE A PERCENTUALE:

Percentuale ≥ 75%  →  🟩 VERDE SCURO (engagement eccellente)
├─ Interpretazione: Quasi tutti hanno visto il documento
├─ Colore: #4CAF50 (rgba(76, 175, 80, 0.9))
└─ Esempio: Emergenze × Medici (100%)

Percentuale 50-74% →  🟨 GIALLO (engagement buono)
├─ Interpretazione: Maggioranza ha visto
├─ Colore: #FFC107 (rgba(255, 193, 7, 0.8))
└─ Esempio: Igiene Mani × ASA/OSS (66%)

Percentuale 25-49% →  🟧 ARANCIONE (engagement medio)
├─ Interpretazione: Una minoranza rilevante non ha visto
├─ Colore: #FF9800 (rgba(255, 152, 0, 0.7))
└─ Esempio: Whistleblowing × Infermieri (24%)

Percentuale < 25%  →  🟥 ROSSO (engagement scarso)
├─ Interpretazione: Pochi hanno visto - ALERT!
├─ Colore: #F44336 (rgba(244, 67, 54, 0.7))
└─ Esempio: Emergenze × Educatori (4%)

LEGENDA:
┌──────────────────────────────┐
│ 🟩 ≥75%  Eccellente          │
│ 🟨 50-75% Buono              │
│ 🟧 25-50% Medio              │
│ 🟥 <25%  Scarso - Azione     │
└──────────────────────────────┘
```

---

## 4. REQUISITI NON-FUNZIONALI

**RNF-1: Performance**
- Griglia carica in < 2 secondi anche con 100+ protocolli
- Query aggregazione ottimizzate con indici
- Caching dei dati della griglia (cache 1 ora)

**RNF-2: Efficienza Spazio**
- Tabella non duplica dati identici
- UNIQUE KEY previene visualizzazioni duplicate
- Spazio consumato: ~1KB per visualizzazione

**RNF-3: Integrità Dati**
- Una volta registrata, visualizzazione non può essere modificata
- Reset automatico su modifica documento
- Audit trail: view_timestamp immutabile

**RNF-4: Usabilità**
- Griglia responsiva (scroll orizzontale su mobile)
- Tooltip su celle con dettagli
- Possibilità di esportare dati in CSV

**RNF-5: Privacy**
- No storage di IP address aggiuntivi non necessari
- Conformità GDPR per dati utenti
- Accesso ristretto a gestori piattaforma

---

## 5. GLOSSARIO

| Termine | Definizione | Esempio |
|---------|-----------|---------|
| **Protocollo** | Documento di procedura operativa | "Whistleblowing - Segnalazioni di Condotte Illecite" |
| **Visualizzazione** | Evento di apertura di un protocollo | Mario apre Whistleblowing |
| **Visualizzazione Unica** | Una sola registrazione per utente × protocollo × versione | Mario apre Whistleblowing il 1° ottobre = 1 visualizzazione (anche se lo riapre 5 volte quel giorno) |
| **Profilo Professionale** | Ruolo/Categoria dell'utente | Infermiere, Medico, ASA/OSS |
| **Version** | Versione del documento (basato su post_modified) | Whistleblowing versione del 01/10, versione del 16/10 |
| **Engagement** | Percentuale di utenti con quel profilo che hanno visto il protocollo | 24% di engagement di infermieri su Whistleblowing |
| **Matrice** | Tabella di incrocio profili × protocolli | La griglia "Battaglia Navale" |

---

## 6. CASISTICHE D'USO

### Caso 1: Nuovo Utente Visualizza Protocollo

```
ATTORE: Lucia (Infermiera, profilo_professionale = "infermiere")
AZIONE: Apre il protocollo "Whistleblowing"
SISTEMA:
├─ 1. Verifica se (lucia.id, whistleblowing.id, whistleblowing.post_modified)
│      esiste nel DB
├─ 2. NON esiste → INSERT una riga con:
│      ├─ user_id = lucia.id
│      ├─ document_id = whistleblowing.id
│      ├─ user_profile = "infermiere" (snapshot del profilo attuale)
│      ├─ document_version = "2025-10-30 12:00:00" (post_modified)
│      └─ view_timestamp = "2025-10-30 14:30:15" (ora attuale)
└─ 3. Griglia aggiornata:
       Whistleblowing × Infermieri: 24 visualizzazioni uniche

SCHEMA VISUALIZZAZIONE CREATA:
┌─────────┬──────────────┬──────────────┬──────────────┬──────────────────┐
│ id      │ user_id      │ document_id  │ user_profile │ document_version │
├─────────┼──────────────┼──────────────┼──────────────┼──────────────────┤
│ 12457   │ 42 (Lucia)   │ 89           │ "infermiere" │ 2025-10-30 12:00 │
└─────────┴──────────────┴──────────────┴──────────────┴──────────────────┘
```

---

### Caso 2: Stesso Utente Visualizza Protocollo Altre Volte

```
ATTORE: Lucia (stessa utente)
AZIONE: Apre di nuovo il protocollo "Whistleblowing" (stesso giorno)
SISTEMA:
├─ 1. Verifica se (lucia.id, whistleblowing.id, whistleblowing.post_modified)
│      esiste nel DB
├─ 2. ESISTE → Skip, NON INSERT
└─ 3. Griglia RIMANE:
       Whistleblowing × Infermieri: 24 visualizzazioni uniche (nessun cambio)

SCHEMA RISULTANTE:
┌─────────┬──────────────┬──────────────┬──────────────┬──────────────────┐
│ id      │ user_id      │ document_id  │ user_profile │ document_version │
├─────────┼──────────────┼──────────────┼──────────────┼──────────────────┤
│ 12457   │ 42 (Lucia)   │ 89           │ "infermiere" │ 2025-10-30 12:00 │
│         │              │              │              │                  │
│ (No new│ (duplicate   │              │              │                  │
│  rows  │ entry!)      │              │              │                  │
└─────────┴──────────────┴──────────────┴──────────────┴──────────────────┘

COMPORTAMENTO:
- Lucia può aprire Whistleblowing 100 volte
- La riga nel DB rimane 1 sola
- Griglia rimane invariata a 24 visualizzazioni per infermieri
```

---

### Caso 3: Protocollo Viene Aggiornato

```
EVENTO: Gestore modifica "Whistleblowing"
TRIGGER: post_modified cambia da "2025-10-30 12:00:00" a "2025-10-30 18:30:00"

RISULTATO:
├─ Versione Vecchia rimane nel DB con 24 visualizzazioni
└─ Versione Nuova parte da 0 visualizzazioni

VISUALIZZAZIONE DATI PRIMA MODIFICA:
┌─────────┬──────────────┬──────────────┬──────────────┬──────────────────┐
│ id      │ user_id      │ document_id  │ user_profile │ document_version │
├─────────┼──────────────┼──────────────┼──────────────┼──────────────────┤
│ 12457   │ 42 (Lucia)   │ 89           │ "infermiere" │ 2025-10-30 12:00 │
│ 12458   │ 43 (Marco)   │ 89           │ "medico"     │ 2025-10-30 12:00 │
│ ... × 22│ ...          │ ...          │ ...          │ ...              │
└─────────┴──────────────┴──────────────┴──────────────┴──────────────────┘
CONTEGGIO VERSIONE VECCHIA: 24 visualizzazioni

DOPO MODIFICA - Lucia riapre Whistleblowing:
├─ Sistema vede document_version = "2025-10-30 18:30:00" (DIVERSA!)
├─ Cerca (lucia.id, whistleblowing.id, "2025-10-30 18:30:00")
├─ NON esiste → INSERT nuova riga
└─ Versione nuova ora ha: 1 visualizzazione

VISUALIZZAZIONE DATI DOPO MODIFICA:
┌─────────┬──────────────┬──────────────┬──────────────┬──────────────────┐
│ id      │ user_id      │ document_id  │ user_profile │ document_version │
├─────────┼──────────────┼──────────────┼──────────────┼──────────────────┤
│ 12457   │ 42 (Lucia)   │ 89           │ "infermiere" │ 2025-10-30 12:00 │  ← Vecchia
│ 12458   │ 43 (Marco)   │ 89           │ "medico"     │ 2025-10-30 12:00 │  ← Vecchia
│ ... × 22│ ...          │ ...          │ ...          │ 2025-10-30 12:00 │  ← Vecchia
├─────────┼──────────────┼──────────────┼──────────────┼──────────────────┤
│ 12479   │ 42 (Lucia)   │ 89           │ "infermiere" │ 2025-10-30 18:30 │  ← NUOVA!
└─────────┴──────────────┴──────────────┴──────────────┴──────────────────┘

CONTEGGIO VERSIONE NUOVA: 1 visualizzazione
TIMELINE:
├─ 01/10 - 30/10 (mattina): Versione 1 → 24 visualizzazioni
├─ 30/10 (18:30): Protocollo modificato!
└─ 30/10 (sera) - ?: Versione 2 → inizia da 0, Lucia la porta a 1
```

---

### Caso 4: Cambio Profilo Utente

```
SCENARIO: Marco era "medico", poi promosso a "coordinatore_unita"

PRIMA DELLA PROMOZIONE:
├─ Marco visualizza Whistleblowing il 01/10
├─ Registro: user_id=43, document_id=89, user_profile="medico",
│            document_version="2025-10-30 12:00"
└─ Griglia: Whistleblowing × Medici: include Marco

DOPO PROMOZIONE (15/10):
├─ Marco visualizza di nuovo Whistleblowing il 15/10
├─ Profilo attuale di Marco: "coordinatore_unita"
├─ document_version STESSO (documento non modificato)
├─ Sistema controlla: (marco.id, whistleblowing.id, "2025-10-30 12:00")
├─ ESISTE → Skip, NON INSERT
└─ Griglia NON cambia!

INTERPRETAZIONE:
- Quella visualizzazione rimarrà associata a "medico" per sempre
- Anche se Marco è ora coordinatore
- Perché riflette cosa ha visto con quale profilo al momento

RIFLESSIONE:
Se volessimo che Marco si "riconta" da coordinatore,
dovremmo richiedere UNA MODIFICA AL PROTOCOLLO
(così document_version cambia → nuova visualizzazione unica →
nuovo profilo registrato)
```

---

## 7. FLUSSO DATI VISIVO

```
┌─────────────────────────────────────────────────────┐
│ UTENTE APRE PROTOCOLLO                              │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
         ┌───────────────────┐
         │ Check 5 secondi?  │
         │ (requisito legacy)│
         └────────┬──────────┘
                  │ SÌ
                  ▼
    ┌─────────────────────────────────┐
    │ Leggi:                          │
    │ - user_id (chi)                 │
    │ - document_id (cosa)            │
    │ - post_modified (versione)      │
    │ - profilo_professionale (quale) │
    └────────┬────────────────────────┘
             │
             ▼
    ┌──────────────────────────────┐
    │ Query DB:                    │
    │ SELECT * WHERE               │
    │  user_id = ?                 │
    │  AND document_id = ?         │
    │  AND document_version = ?    │
    └────────┬─────────────────────┘
             │
        ┌────┴────┐
        │          │
     ESISTE    NON ESISTE
        │          │
        │          ▼
        │   ┌──────────────────┐
        │   │ INSERT nuova riga│
        │   │ Nel DB           │
        │   └────────┬─────────┘
        │            │
        └────┬───────┘
             │
             ▼
    ┌──────────────────────────┐
    │ Aggiorna Cache Griglia   │
    │ (ripulisce cache > 1h)   │
    └──────────────────────────┘

FINE → Visualizzazione registrata (o skippata se duplicata)
```

---

## 8. IMPLEMENTAZIONE - RIEPILOGO TECNICO

Per dettagli tecnici implementativi, vedi: **ANALYTICS_ARCHITECTURE.md**

**Files coinvolti**:
- Database: `wp_document_views` (tabella custom)
- Backend: `includes/analytics.php`, `api/analytics-api.php`
- Frontend: `assets/js/src/protocol-grid.js`
- Template: `page-analitiche.php`
- Stili: `assets/scss/components/_analitiche.scss`

---

## 9. APPROVAZIONI

| Ruolo | Nome | Data | Firma |
|-------|------|------|-------|
| Richiedente | Utente / Gestore Piattaforma | 30/10/2025 | ✓ |
| Progettista | Claude | 30/10/2025 | ✓ |
| Sviluppatore | In Assegnazione | — | — |

---

**Documento APPROVATO e PRONTO PER IMPLEMENTAZIONE**
