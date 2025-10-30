# 📊 ARCHITETTURA SISTEMA ANALYTICS - GRIGLIA PROTOCOLLI × PROFILI

## 🎯 OBIETTIVO

Implementare un sistema di tracking delle **visualizzazioni uniche** di protocolli per profilo professionale, con griglia interattiva tipo "Battaglia Navale" che mostri:
- **Asse Y**: Protocolli
- **Asse X**: Profili Professionali
- **Celle**: Visualizzazioni uniche + percentuale di engagement

## 📋 REQUISITI FUNZIONALI

1. **Visualizzazione Unica**: Una volta che un utente visualizza un protocollo (dopo 5 secondi), la visualizzazione è registrata UNA SOLA VOLTA per quella versione
2. **Reset su Modifica**: Se il protocollo viene aggiornato, la visualizzazione si resetta (nuova versione)
3. **Cattura Profilo**: Al momento della visualizzazione, salva il profilo professionale dell'utente
4. **Griglia Color-Coddata**: Le celle mostrano percentuale di engagement con colori (verde alta %, rosso bassa %)

## 🏗️ ARCHITETTURA TECNICA

### **LAYER 1: DATABASE**

**Tabella**: `wp_document_views`

```sql
CREATE TABLE wp_document_views (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    document_id BIGINT UNSIGNED NOT NULL,
    document_type VARCHAR(50) NOT NULL,
    document_version DATETIME DEFAULT NULL,  -- ← NUOVO: post_modified
    user_profile VARCHAR(255),               -- ← Profilo al momento della vista
    view_timestamp DATETIME NOT NULL,
    view_duration INT UNSIGNED DEFAULT 0,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_view (user_id, document_id, document_version),
    KEY idx_user_id (user_id),
    KEY idx_document_id (document_id),
    KEY idx_timestamp (view_timestamp),
    KEY idx_profile (user_profile)
);
```

### **LAYER 2: BACKEND (PHP)**

#### **File**: `includes/analytics.php`

**Nuove Funzioni**:

```php
meridiana_get_grid_protocol_views()
├─ Query: SELECT protocol, profile, COUNT(DISTINCT user_id) as unique_views
└─ Return: Array di righe (protocol_id, title, profile, count)

meridiana_get_profile_totals()
├─ Query: SELECT profile, COUNT(DISTINCT user_id) as total
└─ Return: Array (profile => total_users)
```

#### **File**: `api/analytics-api.php`

**Modifiche Endpoint `/track-view`**:
```
Nuovo Logic:
1. Recupera post_modified come document_version
2. Controlla se (user_id + document_id + document_version) esiste
3. Se NO → INSERT (visualizzazione unica!)
4. Se SÌ → Skip
```

**Nuovo Endpoint**: `GET /wp-json/piattaforma/v1/grid/protocol-views`
```
Response:
{
    "protocols": [
        {
            "id": 1,
            "title": "Whistleblowing",
            "views": {
                "infermiere": { "count": 12, "percentage": 24 },
                "medico": { "count": 5, "percentage": 33 }
            }
        }
    ],
    "profiles": {
        "infermiere": { "name": "Infermiere", "total": 50 },
        "medico": { "name": "Medico", "total": 15 }
    },
    "grid_date": "2025-10-30 12:00:00"
}
```

### **LAYER 3: FRONTEND (JavaScript + HTML)**

#### **File**: `assets/js/src/protocol-grid.js`

**Componente Alpine**: `protocolGrid()`

Funzioni:
- `init()` - Carica dati dall'API
- `fetchGridData()` - fetch GET /grid/protocol-views
- `buildGridData()` - Organizza dati per rendering
- `getColorStyle(percentage)` - Ritorna CSS color in base a %

Colori:
```
≥ 75% → Verde (engagement alto)
50-75% → Giallo (engagement medio-alto)
25-50% → Arancione (engagement medio-basso)
< 25% → Rosso (engagement basso)
```

#### **File**: `page-analitiche.php`

**Sezione in Tab "Panoramica"**:
```html
<div class="analitiche-section analitiche-section--protocol-grid">
    <h2>Matrice Protocolli × Profili Professionali</h2>
    <table class="protocol-grid-table">
        <!-- Header: Profili con totali -->
        <!-- Body: Protocolli × Profili con % engagement -->
    </table>
</div>
```

#### **File**: `assets/scss/components/_analitiche.scss`

**Stili per Griglia**:
- Tabella scrollabile orizzontale
- Celle con colori dinamici
- Hover effects
- Legend per interpretazione colori

## 🔄 FLOW COMUNICAZIONE

```
1. VISUALIZZAZIONE DOCUMENTO
   Frontend → POST /track-view (document_id, duration)
   Backend → Registra visualizzazione unica + profilo

2. DASHBOARD ANALYTICS
   Frontend → GET /grid/protocol-views
   Backend → Query DB per matrice protocolli × profili
   Frontend → Render griglia con colori
```

## 📁 FILE INTERESSATI

### **Modificare**:
- `includes/analytics.php` - Aggiungere 2 nuove funzioni
- `api/analytics-api.php` - Modificare track_view + aggiungere endpoint grid
- `page-analitiche.php` - Aggiungere sezione griglia in overview
- `assets/scss/components/_analitiche.scss` - Aggiungere stili

### **Creare**:
- `assets/js/src/protocol-grid.js` - Nuovo componente Alpine

### **Database**:
- Aggiungere colonna `document_version`
- Aggiungere UNIQUE KEY

## ⚡ PRIORITÀ IMPLEMENTAZIONE

1. **P1 - Database**: Alterare tabella (documento_version + UNIQUE KEY)
2. **P2 - Backend Query**: Aggiungere funzioni analytics.php
3. **P3 - Backend API**: Modificare track_view + endpoint grid
4. **P4 - Frontend Logic**: Creare protocol-grid.js
5. **P5 - Frontend UI**: HTML griglia + CSS

## 🧪 TEST POINTS

- [ ] Verificare che prima visualizzazione registra (unique_view = 1)
- [ ] Verificare che seconda visualizzazione SKIPS (unique_view rimane 1)
- [ ] Verificare che modifica protocolo resetta contatori
- [ ] Verificare che griglia carica correttamente
- [ ] Verificare colori basati su percentuali
- [ ] Verificare responsive su mobile (scroll orizzontale)

## 📚 RIFERIMENTI

- **Requisiti**: Visualizzazioni uniche per documento, reset su update, griglia per profilo
- **Dati Chiave**: user_id, document_id, document_version, user_profile
- **Metriche**: COUNT visualizzazioni, TOTALE utenti per profilo, PERCENTUALE engagement
