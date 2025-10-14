# 🎨 Design System SCSS - Guida all'Uso

> **Piattaforma Formazione Cooperativa La Meridiana**

## 📦 Installazione

### 1. Installa le dipendenze NPM

```bash
cd C:\Users\strik\Local Sites\nuova-formazione\app\public\wp-content\themes\meridiana-child
npm install
```

### 2. Comandi disponibili

```bash
# Sviluppo (watch mode - rileva modifiche SCSS automaticamente)
npm run watch

# Build production (CSS minificato)
npm run build

# Solo SCSS watch
npm run watch:scss

# Solo SCSS build
npm run build:scss
```

---

## 📂 Struttura File

```
assets/css/
├── src/                          # File sorgente SCSS (modifica questi)
│   ├── main.scss                 # Entry point principale
│   ├── _variables.scss           # Variabili colori, spacing, ecc.
│   ├── _mixins.scss             # Mixin per stati interattivi
│   ├── _reset.scss              # CSS reset
│   ├── base/
│   │   ├── _typography.scss     # Headings, paragrafi, utility text
│   │   ├── _grid.scss           # Flexbox e CSS Grid system
│   │   └── _utilities.scss      # Classi utility (margin, padding, ecc.)
│   ├── components/
│   │   ├── _buttons.scss        # Bottoni e varianti
│   │   ├── _cards.scss          # Card e varianti
│   │   ├── _badges.scss         # Badge e status indicators
│   │   ├── _forms.scss          # Input, textarea, checkbox, ecc.
│   │   └── _tables.scss         # Tabelle e varianti
│   ├── layout/                  # (da implementare fase 1.3)
│   └── pages/                   # (da implementare fase 4)
└── dist/                         # File compilati (non modificare)
    └── main.min.css              # CSS finale (enqueued in functions.php)
```

---

## 🎨 Come Usare il Design System

### 1. Colori

**Usa sempre le CSS custom properties:**

```html
<!-- ✅ CORRETTO -->
<div style="background-color: var(--color-primary)">Rosso brand</div>
<p style="color: var(--color-text-primary)">Testo principale</p>

<!-- ❌ SBAGLIATO -->
<div style="background-color: #ab1120">Non hard-codare mai!</div>
```

**Colori disponibili:**

- `--color-primary` → Rosso brand (#ab1120)
- `--color-primary-dark` → Rosso hover
- `--color-success` → Verde successo
- `--color-warning` → Giallo attenzione
- `--color-error` → Rosso errore
- `--color-text-primary` → Testo principale
- `--color-text-secondary` → Testo secondario
- `--color-bg-primary` → Background bianco
- `--color-bg-secondary` → Background grigio chiaro

[Vedi tutte le variabili in `_variables.scss`]

### 2. Spacing

**Usa le utility classes o custom properties:**

```html
<!-- Utility classes -->
<div class="mt-4 mb-6 p-4">
  Margin-top: 16px, Margin-bottom: 24px, Padding: 16px
</div>

<!-- Custom properties -->
<div style="padding: var(--space-4)">Padding: 16px</div>
```

**Scala spacing (4px base):**

- `--space-1` = 4px
- `--space-2` = 8px
- `--space-3` = 12px
- `--space-4` = 16px
- `--space-5` = 20px
- `--space-6` = 24px
- `--space-8` = 32px

### 3. Bottoni

```html
<!-- Primary button -->
<button class="btn btn-primary">Conferma</button>

<!-- Secondary button -->
<button class="btn btn-secondary">Annulla</button>

<!-- Outline button -->
<button class="btn btn-outline">Modifica</button>

<!-- Size variants -->
<button class="btn btn-primary btn-lg">Grande</button>
<button class="btn btn-primary btn-sm">Piccolo</button>

<!-- With icon -->
<button class="btn btn-primary">
  <i data-lucide="download"></i>
  Scarica
</button>

<!-- Icon only -->
<button class="btn btn-icon btn-primary">
  <i data-lucide="trash"></i>
</button>
```

### 4. Cards

```html
<!-- Basic card -->
<div class="card">
  <div class="card-header">Titolo</div>
  <div class="card-body">
    <p>Contenuto della card</p>
  </div>
  <div class="card-footer">Footer</div>
</div>

<!-- Card clickable -->
<div class="card card-clickable">
  <div class="card-body">Click me!</div>
</div>

<!-- Document card (per protocolli/moduli) -->
<div class="card card-document">
  <div class="card-document__thumbnail">
    <i data-lucide="file-text"></i>
  </div>
  <div class="card-document__content">
    <h3 class="card-document__title">Protocollo 01</h3>
    <div class="card-document__meta">
      <span><i data-lucide="calendar"></i> 14 Ott 2025</span>
      <span><i data-lucide="user"></i> Mario Rossi</span>
    </div>
  </div>
</div>
```

### 5. Badges

```html
<!-- Color variants -->
<span class="badge badge-primary">Attivo</span>
<span class="badge badge-success">Completato</span>
<span class="badge badge-warning">In attesa</span>
<span class="badge badge-error">Scaduto</span>

<!-- Status badges (preconfigurati) -->
<span class="badge badge-status-active">Attivo</span>
<span class="badge badge-status-pending">In sospeso</span>
<span class="badge badge-status-expired">Scaduto</span>
<span class="badge badge-ats">ATS</span>

<!-- Dot badge (notifiche) -->
<span class="badge-dot"></span>
```

### 6. Forms

```html
<!-- Input con label -->
<div class="input-group">
  <label>Nome <span class="required">*</span></label>
  <input type="text" class="input-field" placeholder="Inserisci nome" />
  <span class="input-helper">Inserisci il tuo nome completo</span>
</div>

<!-- Input con errore -->
<div class="input-group">
  <label>Email</label>
  <input type="email" class="input-field error" />
  <span class="input-error">
    <i data-lucide="alert-circle"></i>
    Email non valida
  </span>
</div>

<!-- Textarea -->
<div class="input-group">
  <label>Descrizione</label>
  <textarea class="textarea" rows="5"></textarea>
</div>

<!-- Select -->
<div class="input-group">
  <label>Categoria</label>
  <select class="select-field">
    <option>Seleziona...</option>
    <option>Opzione 1</option>
  </select>
</div>

<!-- Checkbox custom -->
<div class="checkbox-custom">
  <input type="checkbox" id="terms" />
  <label for="terms">Accetto i termini</label>
</div>

<!-- Toggle switch -->
<div class="toggle-switch">
  <input type="checkbox" id="notifications" />
  <label for="notifications">Notifiche attive</label>
</div>

<!-- Search input -->
<input type="text" class="input-field search-input" placeholder="Cerca..." />
```

### 7. Tabelle

```html
<!-- Basic table -->
<div class="table-responsive">
  <table class="table">
    <thead>
      <tr>
        <th>Nome</th>
        <th>Email</th>
        <th class="text-right">Azioni</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td data-label="Nome">Mario Rossi</td>
        <td data-label="Email">mario@example.com</td>
        <td data-label="Azioni" class="text-right">
          <div class="table-actions">
            <button class="btn btn-sm btn-primary">Modifica</button>
            <button class="btn btn-sm btn-secondary">Elimina</button>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</div>

<!-- Clickable rows -->
<table class="table table-clickable">
  <!-- ... -->
</table>

<!-- Striped table -->
<table class="table table-striped">
  <!-- ... -->
</table>
```

### 8. Grid System

```html
<!-- Flexbox Grid -->
<div class="row">
  <div class="col-12 col-md-6 col-lg-4">Colonna 1</div>
  <div class="col-12 col-md-6 col-lg-4">Colonna 2</div>
  <div class="col-12 col-md-6 col-lg-4">Colonna 3</div>
</div>

<!-- CSS Grid -->
<div class="grid grid-cols-1 grid-cols-md-2 grid-cols-lg-3 gap-4">
  <div>Item 1</div>
  <div>Item 2</div>
  <div>Item 3</div>
</div>

<!-- Flexbox utilities -->
<div class="flex items-center justify-between gap-4">
  <span>Left</span>
  <span>Right</span>
</div>
```

---

## 🔧 Personalizzazione

### Aggiungere nuovi colori

**1. Aggiungi in `_variables.scss`:**

```scss
$color-custom: #FF6B6B;
```

**2. Aggiungi in `:root` (stessa sezione):**

```scss
:root {
  --color-custom: #{$color-custom};
}
```

**3. Usa nel codice:**

```html
<div style="color: var(--color-custom)">Testo custom</div>
```

### Aggiungere nuovi componenti

**1. Crea file in `components/`:**

```scss
// components/_alerts.scss
.alert {
  padding: var(--space-4);
  border-radius: var(--radius-md);
  // ... stili
}
```

**2. Importa in `main.scss`:**

```scss
@import 'components/alerts';
```

**3. Ricompila:**

```bash
npm run build
```

---

## 🎯 Best Practices

### ✅ DO (Fai così)

- Usa sempre CSS custom properties (`var(--nome)`)
- Usa utility classes quando possibile
- Mobile-first: scrivi stili base, poi `@include md { }` per desktop
- Rispetta la nomenclatura BEM per componenti custom
- Usa mixins `@include hover-state`, `@include focus-state`

### ❌ DON'T (Evita)

- Hard-codare colori/spacing (`#ab1120`, `16px`)
- Scrivere `!important` (quasi mai necessario)
- Media query desktop-first (`max-width`)
- Modificare file in `/dist/` (vengono sovrascritti)
- Dimenticare l'accessibilità (contrasto, focus states)

---

## 🐛 Troubleshooting

### Il CSS non si aggiorna?

```bash
# Pulisci cache e ricompila
rm -rf assets/css/dist/*
npm run build
```

### Errori di compilazione SCSS?

- Verifica sintassi SCSS (punti e virgola, parentesi chiuse)
- Controlla che le variabili esistano in `_variables.scss`
- Guarda gli errori nel terminale dove gira `npm run watch`

### Gli stili non si applicano?

- Verifica che il file sia enqueued in `functions.php`
- Controlla la specificità CSS (usa DevTools browser)
- Pulisci cache browser (Ctrl+Shift+R)

---

## 📚 Risorse

- **Documentazione completa**: `docs/01_Design_System.md`
- **Lucide Icons**: https://lucide.dev/icons/
- **SCSS Guide**: https://sass-lang.com/guide

---

## 🤝 Contribuire

Quando aggiungi nuovi stili:

1. Segui la struttura esistente
2. Documenta le nuove classi/variabili
3. Testa su mobile e desktop
4. Verifica accessibilità (contrasto, focus)
5. Ricompila con `npm run build`

---

**🎨 Design System pronto all'uso!**
