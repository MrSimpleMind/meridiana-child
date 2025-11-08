# Design System - Analisi e Correzioni

**Data**: 8 Gennaio 2025
**Sviluppatore**: Claude (AI Assistant)
**Branch**: `claude/analyze-design-system-011CUwEFaNWKknhKbQZxXoUn`

## 📋 Indice

1. [Errore Critico Risolto](#errore-critico-risolto)
2. [Problema Header Sticky](#problema-header-sticky)
3. [Modifiche Implementate](#modifiche-implementate)
4. [Struttura Design System](#struttura-design-system)
5. [Testing](#testing)
6. [Come Usare Questa Documentazione](#come-usare-questa-documentazione)

---

## 🚨 Errore Critico Risolto

### Problema
Il sito è andato in errore critico dopo l'ultimo intervento sul design system.

### Causa
Nel file `functions.php` era presente un blocco di CSS inline commentato che conteneva codice HTML/CSS all'interno di un blocco commento PHP `/* */`. Questo causava errori di parsing PHP perché il contenuto HTML/CSS non era correttamente escapato.

```php
// PRIMA (ERRATO):
/*
function meridiana_add_inline_styles() {
    ?>
    <style>
    .convenzione-card { ... }
    </style>
    <?php
}
*/
```

### Soluzione
Il blocco problematico è stato completamente rimosso nel commit `8436580` perché:
1. Tutti gli stili erano già stati migrati al design system SCSS
2. Il blocco era commentato e non più in uso
3. Conteneva HTML/CSS che causava errori di parsing

```php
// DOPO (CORRETTO):
// REMOVED: meridiana_add_inline_styles() - All styles migrated to SCSS
// This function was causing PHP parse errors due to HTML/CSS content within PHP comment blocks
```

**Riferimenti**:
- File: `functions.php` (linee 224-226)
- Commit: `8436580`

---

## 🎯 Problema Header Sticky

### Descrizione
Gli header con le tab navigation nelle pagine **Corsi**, **Dashboard Gestore** e **Analitiche** non erano attaccati al top-header principale. C'era uno spazio bianco tra il top-header e le tab.

### Comportamento Atteso
- Le tab navigation devono partire **direttamente attaccate** al top-header senza spazio
- Quando l'utente scrolla, le tab devono rimanere **sticky** sotto il top-header fisso
- Il contenuto deve partire subito sotto le tab, senza margini superiori

### Cause Identificate

#### 1. Padding sul Body
Il file `layout/_top-header.scss` applicava un padding-top a tutte le pagine:

```scss
// PRIMA (ERRATO):
body.logged-in {
    padding-top: 50px; // Crea spazio bianco sopra
}

body.admin-bar {
    padding-top: 20px;
}
```

**Problema**: Questo creava uno spazio bianco sopra il contenuto principale, anche per le pagine con tab navigation.

#### 2. Margin-top sui Container
I file SCSS delle pagine (`_corsi.scss`, `_gestore-dashboard.scss`, `_analitiche.scss`) avevano margini superiori sui container di contenuto:

```scss
// PRIMA (ERRATO):
.dashboard-content-container {
    margin-top: var(--space-8); // Spazio tra tab e contenuto
}
```

#### 3. Padding-top sulle Pagine
Le classi `.page-corsi`, `.page-analitiche` non avevano regole specifiche per rimuovere padding/margin ereditati.

---

## 🔧 Modifiche Implementate

### 1. Top Header (layout/_top-header.scss)

**Prima:**
```scss
body.logged-in {
    padding-top: 50px;
}

body.admin-bar {
    padding-top: 20px;
}
```

**Dopo:**
```scss
// Padding solo per le pagine SENZA tab navigation
body.logged-in:not(.page-template-page-corsi):not(.page-template-page-dashboard-gestore):not(.page-template-page-analitiche) {
    padding-top: var(--top-header-height); // 64px
}

body.admin-bar:not(.page-template-page-corsi):not(.page-template-page-dashboard-gestore):not(.page-template-page-analitiche) {
    padding-top: 20px;
}
```

**Risultato**: Le pagine con tab navigation (Corsi, Dashboard Gestore, Analitiche) non hanno più padding-top, quindi le tab partono direttamente dal top del viewport sotto il top-header fisso.

---

### 2. Pagina Corsi (pages/_corsi.scss)

**Modifiche:**
```scss
.page-corsi {
    width: 100%;
    padding-top: 0 !important; // Rimuovi padding
    margin-top: 0 !important;  // Rimuovi margin
}

.corsi-tabs-container {
    background-color: var(--color-sidebar-dark);
    border-bottom: 1px solid #1F2937;
    position: sticky;
    top: var(--dashboard-tabs-offset); // Si attacca sotto il top-header quando sticky
    z-index: 50;
    margin: 0;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.18);
}

.corsi-content-container {
    background-color: transparent;
    border-radius: 0;
    box-shadow: none;
    padding: var(--space-8);
    min-height: 400px;
    margin-top: 0; // Content parte subito sotto le tab
}
```

---

### 3. Dashboard Gestore (pages/_gestore-dashboard.scss)

**Modifiche:**
```scss
.page-template-page-dashboard-gestore {
    padding-top: 0 !important;
    margin-top: 0 !important;

    .gestore-dashboard {
        padding-top: 0 !important;
        margin-top: 0 !important;
    }

    .content-wrapper {
        padding-top: 0 !important;
    }
}

.dashboard-tabs-container {
    background-color: var(--color-sidebar-dark);
    border-bottom: 1px solid #1F2937;
    position: sticky;
    top: var(--dashboard-tabs-offset);
    z-index: 50;
    margin: 0;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.18);
}

.dashboard-content-container {
    background-color: transparent;
    border-radius: 0;
    box-shadow: none;
    padding: var(--space-8);
    min-height: 400px;
    margin-top: 0; // Content parte subito sotto le tab
}
```

**Nota**: È stata rimossa una stringa errata (URL GitHub) che si era insinuata nel file alla riga 15.

---

### 4. Pagina Analitiche (pages/_analitiche.scss)

**Modifiche:**
```scss
.page-analitiche {
    padding-top: 0 !important;
    margin-top: 0 !important;
}

.page-analitiche .analitiche-dashboard {
    padding: 0 0 24px;
    margin-top: 0 !important;
}

.page-analitiche .dashboard-tabs-container {
    margin-top: 0;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.18); // Shadow consistente
    background-color: var(--color-sidebar-dark) !important;
    border-bottom: 1px solid #1F2937 !important;
    position: sticky;
    top: var(--dashboard-tabs-offset);
    z-index: 50;
    margin: 0;
}

.page-analitiche .dashboard-content-container {
    margin-top: 0; // Content parte subito sotto le tab
    padding-top: var(--space-6); // Padding interno per separare contenuto
    background-color: transparent;
}
```

---

## 📐 Struttura Design System

### Organizzazione File SCSS

```
assets/css/src/
├── main.scss                    # Entry point principale
├── _variables.scss              # Variabili colori, font, spaziature
├── _mixins.scss                 # Mixin riutilizzabili
├── _dark-mode.scss              # Dark mode CSS custom properties
├── _reset.scss                  # Reset CSS
├── base/
│   ├── _typography.scss         # Tipografia base
│   ├── _layout-variables.scss   # Variabili layout (altezze, offset)
│   ├── _grid.scss               # Sistema di griglia
│   └── _utilities.scss          # Utility classes
├── components/
│   ├── _buttons.scss
│   ├── _cards.scss
│   ├── _badges.scss
│   ├── _forms.scss
│   ├── _tables.scss
│   ├── _tab-navigation.scss     # Tab navigation unificata
│   ├── _user-profile-modal.scss
│   ├── _avatar-selector.scss
│   ├── _featured-image.scss
│   ├── _breadcrumb.scss
│   └── _comunicazioni-filter.scss
├── layout/
│   ├── _containers.scss
│   ├── _navigation.scss
│   └── _top-header.scss         # Top header sticky globale
└── pages/
    ├── _home.scss
    ├── _archive.scss
    ├── _contatti.scss
    ├── _single-convenzione.scss
    ├── _single-salute-benessere.scss
    ├── _single-comunicazioni.scss
    ├── _single-documento.scss
    ├── _docs-page.scss
    ├── _gestore-dashboard.scss  # Dashboard Gestore
    ├── _analytics-gestore.scss
    ├── _analitiche.scss         # Pagina Analitiche
    ├── _corsi.scss              # Pagina Corsi
    ├── _learndash.scss
    └── _login.scss
```

### Variabili Layout Chiave

Definite in `base/_layout-variables.scss`:

```scss
:root {
    // Altezze componenti fissi
    --top-header-height: 64px;
    --bottom-nav-height: 56px;
    --sidebar-width-expanded: 240px;
    --sidebar-width-collapsed: 70px;

    // Offset per sticky positioning
    --dashboard-tabs-offset: var(--top-header-height);
}

// Admin bar WordPress (con login)
body.admin-bar {
    --dashboard-tabs-offset: calc(var(--top-header-height) + 32px);

    @media (max-width: 782px) {
        --dashboard-tabs-offset: calc(var(--top-header-height) + 46px);
    }
}
```

**Come funziona:**
- `--top-header-height`: Altezza fissa del top-header (64px)
- `--dashboard-tabs-offset`: Offset per posizionare le tab sticky sotto il top-header
- Con WordPress admin bar, l'offset aumenta di 32px (desktop) o 46px (mobile)

---

## 🧪 Testing

### Build CSS

Per compilare i file SCSS in CSS:

```bash
# Installa dipendenze (solo la prima volta)
npm install

# Compila CSS (production)
npm run build:scss

# Compila CSS + watch mode (development)
npm run watch:scss
```

### Output Build

Il CSS compilato viene salvato in:
```
assets/css/dist/main.css
```

Questo file viene caricato in `functions.php` con cache busting automatico:
```php
$css_version = file_exists($css_file_path)
    ? filemtime($css_file_path)
    : MERIDIANA_CHILD_VERSION;

wp_enqueue_style(
    'meridiana-child-style',
    MERIDIANA_CHILD_URI . '/assets/css/dist/main.css',
    array('blocksy-parent-style'),
    $css_version
);
```

### Cosa Testare

#### 1. Pagina Corsi (`/corsi`)
- ✅ Le tab devono essere attaccate al top-header senza spazio
- ✅ Scrollando, le tab devono rimanere sticky sotto il top-header
- ✅ Il contenuto deve partire subito sotto le tab
- ✅ Con admin bar, le tab devono comunque funzionare correttamente

#### 2. Dashboard Gestore (`/dashboard-gestore`)
- ✅ Le tab devono essere attaccate al top-header senza spazio
- ✅ Scrollando, le tab devono rimanere sticky sotto il top-header
- ✅ Il contenuto deve partire subito sotto le tab
- ✅ I tab di gestione (Documenti, Comunicazioni, ecc.) devono funzionare

#### 3. Pagina Analitiche (`/analitiche`)
- ✅ Le tab devono essere attaccate al top-header senza spazio
- ✅ Scrollando, le tab devono rimanere sticky sotto il top-header
- ✅ Il contenuto deve avere un piccolo padding-top per separazione
- ✅ Grafici e statistiche devono essere visibili

#### 4. Altre Pagine
- ✅ Le pagine senza tab (Home, Convenzioni, ecc.) devono avere padding-top normale
- ✅ Il top-header deve rimanere fisso su tutte le pagine

---

## 📚 Come Usare Questa Documentazione

### Per Risolvere Problemi Simili

Se il sito va in errore critico o ci sono problemi di layout:

1. **Errori PHP**: Controlla `functions.php` per blocchi commentati con contenuto HTML/CSS
2. **Spazi bianchi sopra il contenuto**: Verifica `padding-top` e `margin-top` nei file SCSS
3. **Tab navigation non sticky**: Controlla `position: sticky` e `top` nei file SCSS delle pagine

### Per Modificare il Layout

#### Cambiare l'altezza del top-header:
```scss
// In base/_layout-variables.scss
:root {
    --top-header-height: 80px; // Modifica qui
}
```

#### Cambiare il comportamento sticky delle tab:
```scss
// In pages/_corsi.scss (o altri file pagina)
.corsi-tabs-container {
    position: sticky;
    top: var(--dashboard-tabs-offset); // Offset sotto top-header
    // oppure
    top: 0; // Sticky in cima al viewport
}
```

#### Aggiungere padding al contenuto:
```scss
// In pages/_corsi.scss
.corsi-content-container {
    padding: var(--space-8); // Aumenta/diminuisci
    margin-top: var(--space-4); // Aggiungi spazio sopra
}
```

### Per Aggiungere Nuove Pagine con Tab

Segui questo pattern per pagine con tab navigation:

```scss
// In pages/_nuova-pagina.scss

.page-nuova-pagina {
    padding-top: 0 !important;
    margin-top: 0 !important;
}

.nuova-pagina-tabs-container {
    background-color: var(--color-sidebar-dark);
    border-bottom: 1px solid #1F2937;
    position: sticky;
    top: var(--dashboard-tabs-offset);
    z-index: 50;
    margin: 0;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.18);
}

.nuova-pagina-content-container {
    margin-top: 0;
    padding: var(--space-8);
}
```

E aggiungi l'esclusione in `layout/_top-header.scss`:

```scss
body.logged-in:not(.page-template-page-corsi):not(.page-template-page-dashboard-gestore):not(.page-template-page-analitiche):not(.page-template-nuova-pagina) {
    padding-top: var(--top-header-height);
}
```

---

## 🔄 Workflow Sviluppo

### 1. Modifiche SCSS

```bash
# Apri il file SCSS da modificare
nano assets/css/src/pages/_corsi.scss

# Fai le modifiche
# Salva il file
```

### 2. Build CSS

```bash
# Compila i CSS
npm run build:scss

# Oppure attiva il watch mode per compilare automaticamente
npm run watch:scss
```

### 3. Test nel Browser

- Apri la pagina nel browser
- Forza refresh (Ctrl+Shift+R o Cmd+Shift+R)
- Verifica che le modifiche siano applicate

### 4. Commit

```bash
git add .
git commit -m "Descrizione delle modifiche"
git push -u origin branch-name
```

---

## ⚠️ Note Importanti

### Cache Busting

Il tema usa cache busting automatico basato su `filemtime()`:

```php
$css_version = file_exists($css_file_path)
    ? filemtime($css_file_path)
    : MERIDIANA_CHILD_VERSION;
```

Questo significa che ogni volta che compili i CSS, il browser caricherà automaticamente la nuova versione senza bisogno di pulire manualmente la cache.

### WordPress Admin Bar

L'admin bar di WordPress modifica l'offset delle tab sticky. Le variabili CSS si adattano automaticamente:

```scss
body.admin-bar {
    --dashboard-tabs-offset: calc(var(--top-header-height) + 32px); // Desktop

    @media (max-width: 782px) {
        --dashboard-tabs-offset: calc(var(--top-header-height) + 46px); // Mobile
    }
}
```

### !important

L'uso di `!important` è giustificato solo per sovrascrivere stili globali del body. Usarlo con parsimonia.

```scss
// OK - Sovrascrive padding globale del body
.page-corsi {
    padding-top: 0 !important;
}

// EVITA - Non serve !important qui
.corsi-content-container {
    margin-top: 0; // Sufficiente senza !important
}
```

---

## 📝 Changelog

### 8 Gennaio 2025

**Errore Critico:**
- ✅ Rimosso blocco CSS inline commentato in `functions.php` (commit `8436580`)

**Header Sticky:**
- ✅ Modificato `layout/_top-header.scss` per escludere padding-top dalle pagine con tab
- ✅ Aggiornato `pages/_corsi.scss` per rimuovere padding/margin e attaccare tab al top-header
- ✅ Aggiornato `pages/_gestore-dashboard.scss` per rimuovere padding/margin e attaccare tab al top-header
- ✅ Aggiornato `pages/_analitiche.scss` per rimuovere padding/margin e attaccare tab al top-header
- ✅ Rimossa stringa errata (URL GitHub) in `_gestore-dashboard.scss`
- ✅ Compilato CSS con successo

---

## 🆘 Supporto

Se qualcosa non funziona dopo aver applicato queste modifiche:

1. **Controlla la console del browser**: Apri DevTools (F12) e cerca errori JavaScript o CSS
2. **Verifica il CSS compilato**: Controlla che `assets/css/dist/main.css` sia stato aggiornato
3. **Forza refresh**: Usa Ctrl+Shift+R (o Cmd+Shift+R su Mac)
4. **Controlla le classi CSS**: Usa DevTools per ispezionare gli elementi e vedere quali stili sono applicati
5. **Ricompila i CSS**: Esegui `npm run build:scss` per ricompilare

### Debug CSS

Per vedere quale stile sta causando problemi:

```scss
// Aggiungi temporaneamente un bordo colorato
.page-corsi {
    border: 5px solid red !important; // Visibile?
}

.corsi-tabs-container {
    border: 5px solid blue !important; // Visibile?
}

.corsi-content-container {
    border: 5px solid green !important; // Visibile?
}
```

Ricompila e guarda dove appaiono i bordi colorati nel browser.

---

**Fine Documentazione**

Questa guida può essere fornita a qualsiasi assistente IA o sviluppatore per comprendere:
- Cosa è stato fatto
- Perché è stato fatto
- Come replicare le modifiche
- Come debuggare problemi simili
