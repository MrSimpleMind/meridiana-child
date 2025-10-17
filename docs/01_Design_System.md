# 🎨 Design System e Identità Visiva

> **Contesto**: Sistema di design completo per piattaforma formazione Cooperativa La Meridiana

**Leggi anche**: 
- `04_Navigazione_Layout.md` per applicazione pratica del design system
- `08_Pagine_Templates.md` per uso dei componenti nei template

---

## 🎯 Filosofia Design

### Principi Guida
- **Mobile-first**: Pensato per smartphone, adattato a desktop
- **Clean & Functional**: Focus su usabilità, non decorazione
- **Fast & Lightweight**: Ogni elemento deve avere uno scopo
- **Accessible**: WCAG 2.1 AA compliant
- **Professional**: Aspetto serio adatto al settore socio-sanitario

---

## 🎨 Sistema Colori

### Colori Primari (Brand Cooperativa)

```scss
// _variables.scss

// ROSSO BRAND COOPERATIVA LA MERIDIANA
$color-primary: #ab1120;           // Rosso brand | rgb(171, 17, 32)
$color-primary-dark: #8a0e1a;      // Rosso hover/active | rgb(138, 14, 26)
$color-primary-light: #c91428;     // Rosso chiaro accenti
$color-primary-bg-light: #fef2f3;  // Background rosso chiaro
$color-primary-text-dark: #6b0d14; // Testo rosso scuro
```

### Colori Funzionali

```scss
$color-secondary: #10B981;         // Verde conferma/successo
$color-secondary-dark: #065F46;    
$color-secondary-light: #34D399;   

$color-success: #10B981;           // Verde | rgb(16, 185, 129)
$color-success-dark: #065F46;      
$color-warning: #F59E0B;           // Giallo attenzione
$color-error: #c91428;             // Rosso errore (allineato brand)
$color-info: #06B6D4;              // Cyan info
```

### Colori Testo

```scss
$color-text-primary: #1F2937;      // Testo principale | rgb(31, 41, 55)
$color-text-secondary: #6B7280;    // Testo secondario | rgb(107, 114, 128)
$color-text-tertiary: #4B5563;     // Testo paragrafi
$color-text-muted: #9CA3AF;        // Testo disabilitato
$color-text-light: #6B7280;        // Testo chiaro (metadata)
```

### Colori Background

```scss
$color-bg-primary: #FFFFFF;        // Sfondo principale
$color-bg-secondary: #F8F9FA;      // Sfondo secondario (header tabelle)
$color-bg-tertiary: #F5F5F5;       // Background body | rgb(245, 245, 245)
$color-bg-quaternary: #FAFAFA;     // Background alternativo
$color-bg-hover: #F9FAFB;          // Background hover rows
```

### Colori Bordi

```scss
$color-border: #E5E7EB;            // Bordi standard
$color-border-light: #F3F4F6;      // Bordi chiari/separatori
$color-border-input: #D1D5DB;      // Bordi input fields
$color-border-disabled: #E5E7EB;   // Bordi elementi disabilitati
```

### Colori Aggiuntivi

```scss
$color-sidebar-dark: #2D3748;      // Grigio scuro nav | rgb(45, 55, 72)
$color-accent-purple: #8B5CF6;     // Viola accenti speciali
$color-accent-lime: #84CC16;       // Lime highlight

$color-overlay-dark: rgba(0, 0, 0, 0.5);
$color-overlay-light: rgba(255, 255, 255, 0.9);
```

### CSS Custom Properties

```scss
:root {
    --color-primary: #{$color-primary};
    --color-primary-dark: #{$color-primary-dark};
    --color-primary-light: #{$color-primary-light};
    
    --color-secondary: #{$color-secondary};
    --color-success: #{$color-success};
    --color-warning: #{$color-warning};
    --color-error: #{$color-error};
    --color-info: #{$color-info};
    
    --color-text-primary: #{$color-text-primary};
    --color-text-secondary: #{$color-text-secondary};
    --color-text-muted: #{$color-text-muted};
    
    --color-bg-primary: #{$color-bg-primary};
    --color-bg-secondary: #{$color-bg-secondary};
    --color-bg-tertiary: #{$color-bg-tertiary};
    
    --color-border: #{$color-border};
    --color-border-light: #{$color-border-light};
}
```

**⚠️ IMPORTANTE**: Usa sempre `var(--color-primary)` nel CSS, mai valori hard-coded.

---

## 📝 Tipografia

### Font Stack

```scss
// Sistema Fonts (performance ottimale)
$font-family-base: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 
                   'Helvetica Neue', Arial, sans-serif;

// Opzione Google Fonts (se richiesto)
// @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap');
// $font-family-base: 'Inter', -apple-system, sans-serif;
// $font-family-heading: 'Poppins', $font-family-base;
```

### Font Sizes (Mobile-First)

```scss
$font-size-xs: 0.75rem;    // 12px
$font-size-sm: 0.875rem;   // 14px
$font-size-base: 1rem;     // 16px
$font-size-lg: 1.125rem;   // 18px
$font-size-xl: 1.25rem;    // 20px
$font-size-2xl: 1.5rem;    // 24px
$font-size-3xl: 1.875rem;  // 30px
$font-size-4xl: 2.25rem;   // 36px
```

### Font Weights

```scss
$font-weight-normal: 400;
$font-weight-medium: 500;
$font-weight-semibold: 600;
$font-weight-bold: 700;
```

### Line Heights

```scss
$line-height-tight: 1.25;
$line-height-normal: 1.5;
$line-height-relaxed: 1.75;
```

### Hierarchy Tipografica

```scss
h1, .h1 {
    font-family: var(--font-family-heading);
    font-size: var(--font-size-3xl);      // 30px mobile
    font-weight: var(--font-weight-bold);
    line-height: var(--line-height-tight);
    
    @media (min-width: 768px) {
        font-size: var(--font-size-4xl);  // 36px desktop
    }
}

h2, .h2 {
    font-size: var(--font-size-2xl);      // 24px mobile
    font-weight: var(--font-weight-semibold);
    
    @media (min-width: 768px) {
        font-size: var(--font-size-3xl);  // 30px desktop
    }
}

h3, .h3 {
    font-size: var(--font-size-xl);       // 20px mobile
    font-weight: var(--font-weight-semibold);
    
    @media (min-width: 768px) {
        font-size: var(--font-size-2xl);  // 24px desktop
    }
}

body {
    font-family: var(--font-family-base);
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-normal);
    line-height: var(--line-height-normal);
    color: var(--color-text-primary);
}
```

---

## 📏 Spacing System

### Sistema Container Unificato (✨ Nuovo - Ottobre 2025)

**Tutte le pagine usano padding consistente tramite classi unificate**:

```scss
// layout/_containers.scss

// Padding standard per TUTTE le pagine
.page-container,
.archive-container,
.home-container,
.contatti-container,
.corsi-container,
.documentazione-container {
    max-width: 1400px;
    margin: 0 auto;
    
    // Mobile
    padding: var(--space-4) var(--space-4);  // 16px
    
    // Tablet
    @media (min-width: 768px) {
        padding: var(--space-8) var(--space-6);  // 32px 24px
    }
    
    // Desktop
    @media (min-width: 1200px) {
        padding: var(--space-10) var(--space-8);  // 40px 32px
    }
}

// Container più stretto per contenuti di lettura
.single-container {
    max-width: 900px;
    margin: 0 auto;
    // Stesso padding delle altre pagine
}
```

**Uso nei Template**:
```php
<!-- Archivi (Convenzioni, News, Salute) -->
<div class="archive-container">
    <!-- Contenuto archivio -->
</div>

<!-- Home -->
<div class="home-container">
    <!-- Contenuto homepage -->
</div>

<!-- Contatti/Organigramma -->
<div class="contatti-container">
    <!-- Contenuto organigramma -->
</div>

<!-- Corsi (quando implementato) -->
<div class="corsi-container">
    <!-- Contenuto corsi -->
</div>

<!-- Documentazione (quando implementato) -->
<div class="documentazione-container">
    <!-- Contenuto documentazione -->
</div>
```

**⚠️ IMPORTANTE**: Non ridefinire mai `padding` o `max-width` nei file SCSS specifici delle pagine. Il padding è gestito centralmente in `layout/_containers.scss` per garantire consistenza.

### Scala Spaziatura (basata su 4px)

```scss
$spacing-unit: 0.25rem; // 4px

$space-1: $spacing-unit;      // 4px
$space-2: $spacing-unit * 2;  // 8px
$space-3: $spacing-unit * 3;  // 12px
$space-4: $spacing-unit * 4;  // 16px
$space-5: $spacing-unit * 5;  // 20px
$space-6: $spacing-unit * 6;  // 24px
$space-8: $spacing-unit * 8;  // 32px
$space-10: $spacing-unit * 10; // 40px
$space-12: $spacing-unit * 12; // 48px
$space-16: $spacing-unit * 16; // 64px
$space-20: $spacing-unit * 20; // 80px

:root {
    --space-1: #{$space-1};
    --space-2: #{$space-2};
    --space-3: #{$space-3};
    --space-4: #{$space-4};
    --space-5: #{$space-5};
    --space-6: #{$space-6};
    --space-8: #{$space-8};
    --space-10: #{$space-10};
    --space-12: #{$space-12};
    --space-16: #{$space-16};
    --space-20: #{$space-20};
}
```

---

## 🔲 Border Radius & Shadows

### Border Radius

```scss
$radius-xs: 3px;       // Checkbox
$radius-sm: 4px;       // Input fields
$radius-md: 6px;       // Pulsanti standard  
$radius-lg: 8px;       // Card, container
$radius-xl: 12px;      // Modal, dialog
$radius-2xl: 16px;     // Header, hero
$radius-full: 9999px;  // Badge, avatar

:root {
    --radius-xs: #{$radius-xs};
    --radius-sm: #{$radius-sm};
    --radius-md: #{$radius-md};
    --radius-lg: #{$radius-lg};
    --radius-xl: #{$radius-xl};
    --radius-2xl: #{$radius-2xl};
    --radius-full: #{$radius-full};
}
```

### Shadows (Elevation System)

```scss
$shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.05);
$shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);              // Card base
$shadow-md: 0 4px 16px rgba(0, 0, 0, 0.12);             // Card hover
$shadow-lg: 0 4px 16px rgba(0, 0, 0, 0.1);              // Modal
$shadow-xl: 0 8px 32px rgba(0, 0, 0, 0.1);              // Header, hero
$shadow-2xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 
             0 10px 10px -5px rgba(0, 0, 0, 0.04);      // Dropdown

$shadow-focus: 0 0 0 3px rgba(171, 17, 32, 0.1);       // Focus outline rosso
$shadow-button-hover: 0 2px 8px rgba(171, 17, 32, 0.3); // Button hover

:root {
    --shadow-xs: #{$shadow-xs};
    --shadow-sm: #{$shadow-sm};
    --shadow-md: #{$shadow-md};
    --shadow-lg: #{$shadow-lg};
    --shadow-xl: #{$shadow-xl};
    --shadow-2xl: #{$shadow-2xl};
    --shadow-focus: #{$shadow-focus};
    --shadow-button-hover: #{$shadow-button-hover};
}
```

---

## 🎚 Sistema Z-Index

```scss
$z-base: 1;
$z-dropdown: 100;
$z-sticky: 10;
$z-fixed: 100;
$z-modal-backdrop: 1000;
$z-modal: 1001;
$z-popover: 1100;
$z-tooltip: 1200;
$z-notification: 1300;

:root {
    --z-base: #{$z-base};
    --z-dropdown: #{$z-dropdown};
    --z-sticky: #{$z-sticky};
    --z-fixed: #{$z-fixed};
    --z-modal-backdrop: #{$z-modal-backdrop};
    --z-modal: #{$z-modal};
    --z-popover: #{$z-popover};
    --z-tooltip: #{$z-tooltip};
    --z-notification: #{$z-notification};
}

// Applicazione
.dropdown-menu { z-index: var(--z-dropdown); }
.bottom-nav { z-index: var(--z-fixed); }
.modal { z-index: var(--z-modal); }
```

---

## ✨ Stati Interattivi

### Mixins per Stati

```scss
// Hover state
@mixin hover-state {
    transition: all 0.2s ease;
    
    &:hover {
        @content;
    }
}

// Focus state (accessibilità)
@mixin focus-state {
    &:focus-visible {
        outline: none;
        box-shadow: var(--shadow-focus);
        @content;
    }
}

// Active state
@mixin active-state {
    &:active {
        transform: scale(0.98);
        @content;
    }
}

// Disabled state
@mixin disabled-state {
    &:disabled,
    &[disabled],
    &.disabled {
        opacity: 0.6;
        cursor: not-allowed;
        background-color: var(--color-bg-secondary);
        color: var(--color-text-muted);
        @content;
    }
}

// Esempio uso completo
.interactive-element {
    @include hover-state {
        background-color: var(--color-bg-hover);
    }
    @include focus-state;
    @include active-state;
    @include disabled-state;
}
```

---

## 🧩 Componenti UI

### Buttons

```scss
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: var(--space-3) var(--space-6); // 12px 24px
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    line-height: var(--line-height-tight);
    border-radius: var(--radius-md);
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    min-height: 40px; // Touch-friendly
    
    &:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
}

.btn-primary {
    background-color: var(--color-primary);
    color: #FFFFFF;
    
    &:hover:not(:disabled) {
        background-color: var(--color-primary-dark);
        box-shadow: var(--shadow-button-hover);
        transform: translateY(-1px);
    }
    
    &:active {
        transform: scale(0.98);
    }
    
    &:focus-visible {
        outline: none;
        box-shadow: var(--shadow-focus);
    }
}

.btn-secondary {
    background-color: var(--color-bg-secondary);
    color: var(--color-text-primary);
    
    &:hover:not(:disabled) {
        background-color: var(--color-border-input);
    }
}

.btn-outline {
    background-color: transparent;
    border: 2px solid var(--color-primary);
    color: var(--color-primary);
    padding: calc(var(--space-3) - 2px) calc(var(--space-6) - 2px);
    
    &:hover:not(:disabled) {
        background-color: var(--color-primary);
        color: white;
    }
}

.btn-link {
    background-color: transparent;
    color: var(--color-primary);
    text-decoration: underline;
    padding: 0;
    min-height: auto;
    
    &:hover:not(:disabled) {
        color: var(--color-primary-dark);
    }
}

// Size variants
.btn-lg {
    padding: var(--space-4) var(--space-8);
    font-size: var(--font-size-lg);
    min-height: 48px;
}

.btn-sm {
    padding: var(--space-2) var(--space-4);
    font-size: var(--font-size-sm);
    min-height: 32px;
}

.btn-xs {
    padding: var(--space-1) var(--space-3);
    font-size: var(--font-size-xs);
    min-height: 28px;
}
```

### Cards

```scss
.card {
    background-color: var(--color-bg-primary);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    transition: all 0.2s ease;
    
    &:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-4px);
    }
}

.card-header {
    padding: var(--space-4) var(--space-6);
    border-bottom: 1px solid var(--color-border);
    background-color: var(--color-bg-secondary);
    font-weight: var(--font-weight-semibold);
}

.card-body {
    padding: var(--space-6);
}

.card-footer {
    padding: var(--space-4) var(--space-6);
    border-top: 1px solid var(--color-border);
    background-color: var(--color-bg-secondary);
}

// Card con icona
.card-icon {
    .card-icon__area {
        padding: var(--space-5) var(--space-6);
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: var(--color-primary-bg-light);
        
        i, svg {
            width: 32px;
            height: 32px;
            color: var(--color-primary);
        }
    }
    
    .card-icon__content {
        padding: var(--space-6);
    }
}

// Page card (con border-top accent)
.card-page {
    border-top: 4px solid var(--color-primary);
    
    .card-page__header {
        background-color: var(--color-bg-secondary);
        padding: var(--space-4) var(--space-5);
        border-bottom: 1px solid var(--color-border);
        font-weight: var(--font-weight-semibold);
    }
    
    .card-page__content {
        padding: var(--space-5);
    }
}

// Card clickable
.card-clickable {
    cursor: pointer;
    
    &:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }
}
```

### Badges

```scss
.badge {
    display: inline-flex;
    align-items: center;
    padding: var(--space-1) var(--space-3);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-semibold);
    border-radius: var(--radius-full);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-primary {
    background-color: var(--color-primary);
    color: white;
}

.badge-success {
    background-color: var(--color-success);
    color: white;
}

.badge-warning {
    background-color: var(--color-warning);
    color: var(--color-text-primary);
}

.badge-error {
    background-color: var(--color-error);
    color: white;
}

.badge-info {
    background-color: var(--color-info);
    color: white;
}

// Badge dot (notifiche)
.badge-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: var(--color-error);
    border: 2px solid var(--color-bg-primary);
}
```

### Input Fields

```scss
.input-field {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid var(--color-border-input);
    border-radius: var(--radius-sm);
    font-size: var(--font-size-sm);
    color: var(--color-text-primary);
    background-color: var(--color-bg-primary);
    transition: all 0.2s ease;
    min-height: 40px;
    
    &::placeholder {
        color: var(--color-text-muted);
    }
    
    &:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: var(--shadow-focus);
    }
    
    &:disabled {
        background-color: var(--color-bg-secondary);
        color: var(--color-text-muted);
        cursor: not-allowed;
    }
}

.textarea {
    @extend .input-field;
    min-height: 120px;
    resize: vertical;
}

.select-field {
    @extend .input-field;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23ab1120' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    padding-right: 36px;
    cursor: pointer;
}

.search-input {
    @extend .input-field;
    padding-left: 40px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%236B7280' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: left 12px center;
}

// Input group (con label)
.input-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
    
    label {
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-medium);
        color: var(--color-text-primary);
    }
    
    .input-helper {
        font-size: var(--font-size-xs);
        color: var(--color-text-secondary);
    }
    
    .input-error {
        font-size: var(--font-size-xs);
        color: var(--color-error);
    }
}

// Error state
.input-field.error {
    border-color: var(--color-error);
    
    &:focus {
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
    }
}
```

### Checkbox Custom

```scss
.checkbox-custom {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    cursor: pointer;
    user-select: none;
    
    input[type="checkbox"] {
        appearance: none;
        width: 16px;
        height: 16px;
        border: 2px solid var(--color-border-input);
        border-radius: 3px;
        background-color: var(--color-bg-primary);
        cursor: pointer;
        position: relative;
        transition: all 0.2s ease;
        flex-shrink: 0;
        
        &:checked {
            background-color: var(--color-primary);
            border-color: var(--color-primary);
            
            &::after {
                content: '';
                position: absolute;
                left: 4px;
                top: 1px;
                width: 4px;
                height: 8px;
                border: solid white;
                border-width: 0 2px 2px 0;
                transform: rotate(45deg);
            }
        }
        
        &:focus-visible {
            outline: none;
            box-shadow: var(--shadow-focus);
        }
    }
    
    label {
        font-size: var(--font-size-sm);
        cursor: pointer;
    }
}
```

### Tabelle

```scss
.table {
    width: 100%;
    border-collapse: collapse;
    background-color: var(--color-bg-primary);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    
    thead {
        background-color: var(--color-bg-secondary);
        
        th {
            padding: 16px 20px;
            text-align: left;
            font-weight: var(--font-weight-semibold);
            font-size: var(--font-size-sm);
            color: var(--color-text-primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--color-border);
        }
    }
    
    tbody {
        tr {
            border-bottom: 1px solid var(--color-border-light);
            transition: background-color 0.2s ease;
            
            &:hover {
                background-color: var(--color-bg-hover);
            }
            
            &:last-child {
                border-bottom: none;
            }
        }
        
        td {
            padding: 16px 20px;
            font-size: var(--font-size-sm);
            color: var(--color-text-primary);
        }
    }
    
    // Tabella clickable rows
    &.table-clickable {
        tbody tr {
            cursor: pointer;
            
            &:active {
                background-color: var(--color-bg-secondary);
            }
        }
    }
    
    // Responsive mobile
    @media (max-width: 768px) {
        display: block;
        overflow-x: auto;
        
        thead {
            display: none;
        }
        
        tr {
            display: block;
            margin-bottom: var(--space-4);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
        }
        
        td {
            display: block;
            padding: var(--space-3);
            position: relative;
            padding-left: 40%;
            
            &::before {
                content: attr(data-label);
                position: absolute;
                left: var(--space-3);
                font-weight: var(--font-weight-semibold);
                color: var(--color-text-secondary);
            }
        }
    }
}
```

---

## 📱 Responsive Breakpoints

```scss
$breakpoint-sm: 576px;   // Smartphone landscape
$breakpoint-md: 768px;   // Tablet portrait
$breakpoint-lg: 992px;   // Tablet landscape
$breakpoint-xl: 1200px;  // Desktop
$breakpoint-2xl: 1400px; // Large desktop

// Mixins
@mixin sm {
    @media (min-width: $breakpoint-sm) { @content; }
}

@mixin md {
    @media (min-width: $breakpoint-md) { @content; }
}

@mixin lg {
    @media (min-width: $breakpoint-lg) { @content; }
}

@mixin xl {
    @media (min-width: $breakpoint-xl) { @content; }
}

// Uso
.container {
    padding: var(--space-4);
    
    @include md {
        padding: var(--space-6);
    }
    
    @include lg {
        padding: var(--space-8);
    }
}
```

---

## 🎨 Iconografia

### Sistema Icone: Lucide (Consigliato)

```html
<!-- Lightweight, solo 4kb -->
<script src="https://unpkg.com/lucide@latest"></script>
<script>
  lucide.createIcons();
</script>

<!-- Uso: -->
<i data-lucide="home"></i>
<i data-lucide="file-text"></i>
<i data-lucide="users"></i>
```

### Icone Necessarie

- Home: `home`
- Documentazione: `file-text` / `folder`
- Corsi: `graduation-cap` / `book-open`
- Organigramma: `users` / `user-circle`
- Convenzioni: `tag` / `gift`
- Salute: `heart` / `activity`
- Account: `user`
- Notifiche: `bell`
- Menu: `menu`
- Cerca: `search`

---

## 🎯 Note Implementazione

### Configurazione Blocksy (vedi anche 00_README)

```
Appearance → Customize → General → Colors
- Primary Color: #ab1120
- Link Color: #ab1120
- Button Color: #ab1120

Typography:
- System fonts consigliati per performance
- Body: 16px
- H1: 30px mobile / 36px desktop

Performance:
✅ Lazy Load Images
✅ Minify CSS/JS
✅ Remove jQuery Migrate
✅ Inline Critical CSS
```

### SCSS Structure nel Child Theme

```
assets/css/src/
├── main.scss              # Entry point
├── _variables.scss        # Questo file
├── _mixins.scss          # Mixin stati interattivi
├── _reset.scss           # CSS reset
├── base/
│   ├── _typography.scss
│   ├── _grid.scss
│   └── _utilities.scss
├── components/
│   ├── _buttons.scss     # Stili sopra
│   ├── _cards.scss
│   ├── _badges.scss
│   ├── _forms.scss
│   ├── _modals.scss
│   └── _tables.scss
├── layout/
│   └── (vedi 04_Navigazione_Layout.md)
└── pages/
    └── (vedi 08_Pagine_Templates.md)
```

### Build Process

```bash
# Installazione dipendenze (prima volta)
npm install

# Development (watch mode con auto-compile)
npm run watch

# Build singoli
npm run build:scss  # Compila solo SCSS
npm run build:js    # Compila solo JS

# Production (tutto insieme)
npm run build
```

#### ✅ Risoluzione Errori Compilazione (Ottobre 2025)

**Problema**: Il mixin `custom-scrollbar` usava `darken($thumb-color, 10%)` con default `var(--color-border-input)`, causando errore "is not a color" perché Sass non può manipolare CSS custom properties.

**Soluzione Implementata**:
```scss
// _mixins.scss - Line 171
// Usa color-mix() CSS per compatibilità con custom properties
background: $thumb-color;
background: color-mix(in srgb, #{$thumb-color}, black 10%);
```

**Altri Fix**:
- Creato `webpack.config.js` per configurare entry point corretto (`assets/js/src/index.js`)
- Creato `assets/js/src/index.js` come entry point minimale con evento `meridiana:frontend-ready`
- Bundle JS generato correttamente in `assets/js/dist/main.min.js`

**Warning Residui** (da gestire in futuro):
- `@import` deprecato → Migrare a `@use/@forward` (Dart Sass 3.0)
- Funzioni globali `darken()` → Usare `sass:color.adjust()`

#### 📋 File Demo Design System

È disponibile un file demo per testare tutti i componenti:
```
templates/design-system-demo.php
includes/design-system-demo.php
```

**Come visualizzarlo**:
1. Assicurati di essere loggato come amministratore
2. Aggiungi `?design-system-demo=1` alla URL del tuo sito
   - Esempio: `https://nuova-formazione.local/?design-system-demo=1`
3. Vedrai una pagina vetrina con tutti i componenti stilati

**Nota**: Visibile solo agli amministratori autenticati. Per disattivare, rimuovi la `require_once` corrispondente da `functions.php` e cancella i due file.

---

## 🤖 Checklist per IA

Quando lavori su design/UI:

- [ ] Usa sempre CSS custom properties (`var(--color-primary)`)
- [ ] Non hard-codare mai valori colori/spacing
- [ ] Rispetta mobile-first (media queries min-width)
- [ ] Touch targets: minimo 44x44px
- [ ] Contrasto WCAG AA: minimo 4.5:1 per testo
- [ ] Transition: sempre 0.2s ease
- [ ] Hover/focus/active/disabled: usa i mixin
- [ ] Border radius: usa le variabili definite
- [ ] Shadow: usa elevation system
- [ ] Z-index: usa sistema stratificato

---

**🎨 Design System completo e pronto all'uso.**
