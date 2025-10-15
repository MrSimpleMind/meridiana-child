# 🔧 FIX APPLICATI - Sessione 2

**Data**: 15 Ottobre 2025, 11:00  
**Status**: In Progress - Fixing problemi rilevati

---

## ✅ Fix Completati

### 1. **Typography corretta** ✅
- Aggiunto font-family system fonts
- Font-size da Design System (h1: 30px, h2: 24px, h3: 20px)
- Line-height corretti (1.25 per headings, 1.5 per body)

### 2. **Sezione Salute e Benessere** ✅
- Fix: usava `get_field('contenuto')` inesistente
- Ora usa: `get_the_excerpt()` e `get_the_content()`
- Funziona con editor WordPress standard

### 3. **Campo Convenzione corretto** ✅
- Fix: usava campo `attiva` 
- Corretto in: `convenzione_attiva` (nome reale ACF)
- Applicato a: home + archivio convenzioni

### 4. **Indicatore scroll convenzioni** ✅
- Aggiunto gradiente a destra per indicare scroll
- Gradiente: `linear-gradient(to right, transparent, rgba(245, 245, 245, 0.95))`
- Width: 60px, solo mobile (nascosto su desktop)
- Migliora UX: l'utente capisce che può scorrere

---

## ⚠️ Da Completare (SERVE INPUT UTENTE)

### 1. **Bottom Navigation - Link rotti** ❌

**Problema**: I link Docs, Corsi, Contatti non funzionano perché non conosco gli slug delle pagine create.

**Soluzione**: Ho creato un template con slug placeholder che vanno aggiornati.

**File**: `templates/parts/navigation/bottom-nav.php` (righe 8-10)

```php
$slug_documentazione = 'documentazione'; // TODO: AGGIORNARE
$slug_corsi = 'corsi'; // TODO: AGGIORNARE  
$slug_organigramma = 'organigramma'; // TODO: AGGIORNARE
```

**Cosa serve**: Gli slug reali delle pagine create nel backend WordPress.

**Come trovarli**:
1. Backend WP → Pagine → (nome pagina)
2. Permalink sotto il titolo mostra: `nuova-formazione.local/[SLUG]`
3. Lo SLUG è la parte dopo l'ultimo `/`

**Esempio**:
- Se permalink è: `nuova-formazione.local/documenti` → slug: `documenti`
- Se permalink è: `nuova-formazione.local/corsi-formazione` → slug: `corsi-formazione`

### 2. **Link "Vedi tutto" mancanti** ❌

**Problema**: 
- ✅ News ha link funzionante (va a pagina blog)
- ❌ Convenzioni manca link "Vedi tutto"
- ❌ Salute manca link "Vedi tutto"

**Causa**: Non appaiono perché `get_post_type_archive_link()` restituisce `false` se:
- Il CPT non ha `has_archive => true`
- WordPress non ha rigenerato i rewrite rules

**Soluzione**:
1. Backend WP → Impostazioni → Permalink → Clicca "Salva modifiche"
   (Questo rigenera i rewrite rules)
2. Verifica che in ACF UI i CPT abbiano Archive enabled

**Alternative**: Se non funziona, creare pagine manuali e usare template custom.

---

## 📋 Checklist Finale

### Bottom Navigation
- [x] Home link funziona
- [ ] Docs link (serve slug)
- [ ] Corsi link (serve slug)
- [ ] Contatti link (serve slug)

### Sezioni Home
- [x] Header "Ciao Matteo" + avatar
- [x] Sezione "Per te" (Convenzioni)
- [x] Link "Vedi tutto" convenzioni (se CPT ha archive)
- [x] Sezione "Ultime notizie"
- [x] Link "Vedi tutto" news
- [x] Sezione "Salute e benessere"
- [x] Link "Vedi tutto" salute (se CPT ha archive)

### Stili
- [x] Typography Design System
- [x] Colori corretti
- [x] Indicatore scroll convenzioni
- [x] Bottom nav orizzontale
- [x] Responsive mobile/desktop

---

## 🚀 Prossimi Step

1. **URGENTE**: Fornire slug pagine (Docs, Corsi, Contatti)
2. Salvare Permalink Settings per rigenerare rewrite rules
3. Creare contenuti test:
   - 2 Convenzioni con `convenzione_attiva = true`
   - 3 Salute e Benessere con contenuto
4. Test finale su mobile reale

---

## 📝 Note Tecniche

### Convenzioni Carousel
- Scroll touch nativo
- `scroll-snap-type: x mandatory` per snap alle card
- Gradiente fade-out a destra per UX
- No scrollbar visibile

### Salute e Benessere
- Usa editor WordPress standard (title + editor + excerpt)
- Non ha custom field 'contenuto' in ACF
- Se vuoi custom field, va aggiunto in ACF UI

### Bottom Nav Stati Attivi
Logica PHP implementata:
- `is_front_page()` → Home attivo
- `is_page($slug)` → Pagina attiva
- `is_post_type_archive()` → Archive CPT attivo
- `is_singular()` → Single post type attivo

---

## 🐛 Troubleshooting

### Link "Vedi tutto" non appare
1. Salva Permalink (Impostazioni → Permalink → Salva)
2. Verifica CPT has_archive in ACF UI
3. Prova a visitare manualmente: `/convenzione/` e `/salute_benessere/`

### Bottom nav link 404
1. Aggiorna slug nel file `bottom-nav.php`
2. Salva Permalink Settings
3. Hard refresh (Ctrl+F5)

### Convenzioni/Salute vuote
1. Crea contenuti nel backend
2. Per Convenzioni: spunta checkbox "Convenzione Attiva"
3. Per Salute: aggiungi contenuto nell'editor

---

**⏳ In attesa degli slug delle pagine per completare la bottom navigation!**

Fornisci gli slug e sistemo immediatamente i link.
