# 📋 Changelog - Meridiana Child Theme

Questo file tiene traccia di tutti i cambiamenti significativi al tema child.

---

## [15 Ottobre 2025] - Risoluzione Errori Compilazione SCSS/JS

### 🐛 Bug Fix - Compilazione SCSS

**Problema**: 
- Il comando `npm run watch` falliva con errore: `$color: var(--color-border-input) is not a color`
- Il mixin `custom-scrollbar` in `assets/css/src/_mixins.scss` usava `darken($thumb-color, 10%)` ma il valore di default era `var(--color-border-input)` (CSS custom property)
- Sass non può manipolare dinamicamente le custom properties CSS

**Soluzione Implementata**:
```scss
// File: assets/css/src/_mixins.scss (Line 171)
// PRIMA (causava errore):
background: darken($thumb-color, 10%);

// DOPO (funzionante):
background: $thumb-color;
background: color-mix(in srgb, #{$thumb-color}, black 10%);
```

**Perché funziona**:
- `color-mix()` è una funzione CSS nativa che opera a runtime nel browser
- Supporta sia colori statici che custom properties
- Mantiene la compatibilità con il design system basato su CSS variables

### 🐛 Bug Fix - Webpack Configuration

**Problema**: 
- Webpack cercava `./src/index.js` nella root del tema e falliva
- Errore: `Can't resolve './src'`
- Nessun bundle JS veniva generato

**Soluzione Implementata**:
- Creato `webpack.config.js` con configurazione corretta:
  - Entry point: `assets/js/src/index.js`
  - Output: `assets/js/dist/main.min.js`
  - Mode: development con sourcemap, production minificato

**File Creati**:
```
webpack.config.js              # Configurazione Webpack
assets/js/src/index.js         # Entry point JS minimale
```

### ✅ Risultati

**Build SCSS**: ✅ Funzionante
```bash
npm run build:scss  # Compila assets/css/src/* → assets/css/dist/main.css
```

**Build JS**: ✅ Funzionante
```bash
npm run build:js    # Compila assets/js/src/index.js → assets/js/dist/main.min.js
```

**Watch Mode**: ✅ Funzionante
```bash
npm run watch       # Auto-compila SCSS + JS in modalità sviluppo
```

### ⚠️ Warning Residui (NON bloccanti)

Il processo di build mostra alcuni warning di deprecazione Sass:

1. **@import deprecato**: Dart Sass 3.0 rimuoverà `@import`
   - **Azione futura**: Migrare a `@use` e `@forward`
   - **File coinvolti**: `assets/css/src/main.scss`

2. **Funzioni globali deprecate**: `darken()`, `lighten()` ecc.
   - **Azione futura**: Usare `sass:color.adjust()` o `sass:color.scale()`
   - **File coinvolti**: `assets/css/src/components/_buttons.scss`, `_mixins.scss`

**Nota**: Questi warning non impediscono la compilazione, ma andranno risolti prima di Dart Sass 3.0.

### 📋 File Demo Design System

**Creati**:
- `templates/design-system-demo.php` - Pagina vetrina componenti
- `includes/design-system-demo.php` - Logica caricamento demo

**Come usare**:
1. Login come amministratore WordPress
2. Aggiungi `?design-system-demo=1` alla URL
   - Esempio: `https://nuova-formazione.local/?design-system-demo=1`
3. Visualizza tutti i componenti stilati (buttons, cards, forms, tables, etc.)

**Sicurezza**: Visibile solo ad amministratori autenticati.

### 📚 Documentazione Aggiornata

File aggiornati con le informazioni sui fix:
- ✅ `docs/01_Design_System.md` - Aggiunta sezione "Risoluzione Errori Compilazione"
- ✅ `docs/TASKLIST_PRIORITA.md` - Marcate come completate le task SCSS/JS setup
- ✅ `docs/00_README_START_HERE.md` - Aggiornata timeline stato avanzamento

### 🎯 Prossimi Passi

**Fase 1.3 - Navigazione e Layout** (priorità P0):
- [ ] Implementare bottom navigation mobile (HTML/CSS/Alpine.js)
- [ ] Creare desktop header navigation
- [ ] Integrare Lucide Icons
- [ ] Implementare menu overlay mobile
- [ ] Testare navigation su dispositivi touch

---

## [14 Ottobre 2025] - Setup Iniziale Design System

### ✅ Completato

- Creata struttura SCSS modulare completa
- Definite tutte le variabili CSS (colori, spacing, typography, shadows, etc.)
- Implementati componenti base (buttons, cards, badges, forms, tables)
- Setup sistema grid responsive mobile-first
- Configurato npm con script build/watch per SCSS e JS
- Documentato Design System completo in `docs/01_Design_System.md`

---

**Formato Versionamento**: [Data] - Descrizione  
**Mantenuto da**: Team Sviluppo Piattaforma Meridiana
