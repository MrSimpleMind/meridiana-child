# 🧪 PROMPT 8: Design System Compliance Testing
## Single Salute e Benessere - Cross-Browser & Device Testing

**Data**: 20 Ottobre 2025  
**Fase**: Fase 4 (Template Pagine) - 100%  
**Completamento Progetto**: ~52% (template salute fixed + breadcrumb + layout grid)

---

## 📋 Cambiamenti Implementati

### ✅ COMPLETATO - Step 1: HTML Template Refactoring

**File**: `single-salute-e-benessere-l.php`

```diff
✅ Aggiunto breadcrumb navigation (meridiana_render_breadcrumb())
✅ Back button dinamico (meridiana_get_parent_url() + meridiana_get_back_label())
✅ Layout con grid 2 colonne (content 1fr + sidebar 300-350px)
✅ Featured image con lazy loading e aspect ratio responsive
✅ Sidebar sticky positioning su desktop
✅ Classe main corretta: single-salute-benessere-page
✅ ACF fields correttamente recuperati con fallback
```

### ✅ COMPLETATO - Step 2: SCSS Alignment

**File**: `_single-salute-benessere.scss`

```diff
✅ Rimosso padding ridefinito (usa _containers.scss)
✅ Design system variables (--color-*, --space-*, --shadow-*, --radius-*)
✅ Coerenza con single-convenzione.php
✅ Grid layout responsive (1col mobile, 2col tablet+)
✅ Sidebar sticky con max-height calcolata
```

### ✅ COMPLETATO - Step 3: Cache Bust

**File**: `functions.php`

```diff
✅ Version bump: 1.0.0 → 1.0.1
✅ CSS version = time() (sempre aggiornato)
✅ JS version = time() (sempre aggiornato)
✅ Browser forza download di nuovi asset
```

---

## 🎯 Testing Checklist - DO THIS NOW

### DEVICE 1: MOBILE (320px - iPhone SE)

**URL**: `nuova-formazione.local/salute-e-benessere-l/{id-articolo}/`

#### Visual Elements Check
- [ ] **Back Button** - Visibile e leggibile
  - Text: "Torna indietro" 
  - Icon: arrow-left (16x16px)
  - Tap target: minimo 44x44px ✓
  - Color: brand rosso (#ab1120)

- [ ] **Breadcrumb** - NON VISIBLE su mobile
  - Deve collassare automaticamente (media query max-width: 768px)
  - Solo back button visibile

- [ ] **Title (H1)** - Dimensione corretta
  - Font size: 24px (mobile) da `var(--font-size-2xl)`
  - Font weight: bold (700)
  - Color: text-primary (#1F2937)
  - No wrapping issues

- [ ] **Featured Image**
  - Aspect ratio: 4:3 (mobile override)
  - Full width della container
  - Shadow visible: `var(--shadow-sm)`
  - No scroll horizontal
  - Image loads with lazy loading

- [ ] **Content Area**
  - Testo readable: 16px base
  - Line height: 1.5 (readable)
  - Spacing tra paragrafi: 20px (var(--space-5))
  - Links color: #ab1120 with underline
  - Immagini inline: max-width 100%

- [ ] **Sidebar Risorse** - UNDER CONTENT
  - Stacked under main content (grid-column: 1 on mobile)
  - Full width della container
  - Section background: #F8F9FA (var(--color-bg-secondary))
  - Padding: 16px (var(--space-4))
  - Border radius: 8px (var(--radius-lg))

#### Interaction Check
- [ ] Link "Torna indietro" - Click va all'archivio salute
- [ ] Resource links - Aprono in nuova tab (_blank)
- [ ] File download - Funzionano correttamente
- [ ] Icon display - Lucide icons visibili (no broken SVGs)

---

### DEVICE 2: TABLET (768px - iPad Portrait)

**Testing Setup**: DevTools F12 → Responsive Mode → iPad

#### Layout Check
- [ ] **Grid Layout** - ATTIVA (2 colonne)
  - Content: 1fr (main)
  - Sidebar: 300px (fixed width)
  - Gap: 32px (var(--space-8))
  - Sidebar appare a DESTRA di content

- [ ] **Featured Image**
  - Aspect ratio: 16:9 (torna a desktop ratio)
  - Shadow on hover: `var(--shadow-md)`
  - Transform translateY(-4px) on hover

- [ ] **Sidebar Positioning**
  - Position: sticky
  - Top: 16px (var(--space-4))
  - Max-height: calc(100vh - 32px)
  - Overflow-y: auto (scroll interno se tall)

#### Typography
- [ ] Title: 30px (font-size-3xl - tablet già 30px)
- [ ] Section title: 18px (font-size-lg)
- [ ] Body text: 16px
- [ ] Small text (file size): 14px (font-size-sm)

#### Spacing
- [ ] Back button margin-bottom: 24px (var(--space-6))
- [ ] Title margin-bottom: 32px (var(--space-8))
- [ ] Content margin: correctly spaced
- [ ] Section padding: 24px (var(--space-6))

---

### DEVICE 3: DESKTOP (1024px+ - MacBook 15")

**Testing Setup**: Browser window 1024px+ width

#### Full Grid Layout
- [ ] **Container width** - 900px max-width (single-container)
- [ ] **Centered alignment** - margin: 0 auto
- [ ] **Grid columns** - 1fr 350px (sidebar più larga che tablet)
- [ ] **Gap** - 40px (var(--space-10))
- [ ] **Overall balance** - Layout simmetrico e readable

#### Featured Image
- [ ] Full width in content area
- [ ] Aspect ratio: 16:9 maintained
- [ ] Smooth loading (lazy load completes)
- [ ] No border issues (border-radius: 8px)

#### Sidebar Desktop Features
- [ ] **Sticky positioning** - Scrolls with page content
- [ ] **Top spacing** - 16px from top when sticky
- [ ] **Max-height** - calc(100vh - 32px) = usable max height
- [ ] **Overflow handling** - Internal scroll if content > max-height
- [ ] **Padding** - 24px (var(--space-6))
- [ ] **Background** - #F8F9FA visible

#### Resource Links Styling
- [ ] **Normal state**
  - Border: 1px solid #E5E7EB (var(--color-border))
  - Background: white (#FFFFFF)
  - Padding: 12px 16px (var(--space-3) var(--space-4))
  - Icon: 16x16px
  - Text: 14px semibold

- [ ] **Hover state**
  - Background: #fef2f3 (var(--color-primary-bg-light))
  - Border: primary color (#ab1120)
  - Transform: translateX(2px)
  - Text color: primary-dark (#8a0e1a)
  - Arrow icon appears (opacity: 0 → 1)
  - Duration: 0.2s ease

- [ ] **Focus state** (keyboard navigation)
  - Box-shadow: var(--shadow-focus) (rosso outline)
  - Outline: none
  - Visibile anche col tastiera

---

## 🎨 Design System Compliance - Verification

### Colors ✅
- [ ] Primary rosso (#ab1120) - Brand color su links, hover
- [ ] Primary dark (#8a0e1a) - Darker hover state
- [ ] Primary bg light (#fef2f3) - Hover background
- [ ] Text primary (#1F2937) - Titoli, body
- [ ] Text secondary (#6B7280) - Metadata, small text
- [ ] Background secondary (#F8F9FA) - Sidebar/sections
- [ ] Border (#E5E7EB) - Link borders, separators

### Typography ✅
- [ ] Font family: -apple-system, BlinkMacSystemFont (system fonts, no custom)
- [ ] H1: 30px mobile / 36px desktop (var(--font-size-*))
- [ ] H2-H6: responsive sizes
- [ ] Body: 16px base (var(--font-size-base))
- [ ] Small: 14px (var(--font-size-sm))
- [ ] Font weights: 400 normal, 600 semibold, 700 bold

### Spacing ✅
- [ ] Unit: 4px base (var(--space-1) = 4px, var(--space-4) = 16px, etc)
- [ ] Margins/padding: sempre multiples di 4
- [ ] Gap tra colonne: var(--space-8) o var(--space-10)
- [ ] Section padding: var(--space-4) o var(--space-6)

### Shadows ✅
- [ ] Small: var(--shadow-sm) - 0 2px 8px rgba(0,0,0,0.08)
- [ ] Medium: var(--shadow-md) - on hover
- [ ] Focus: var(--shadow-focus) - rosso ring su focus

### Border Radius ✅
- [ ] Small: 4px (var(--radius-sm)) - input fields
- [ ] Medium: 6px (var(--radius-md)) - buttons
- [ ] Large: 8px (var(--radius-lg)) - cards/sections
- [ ] Full: 9999px (var(--radius-full)) - avatars/badges

### Responsive Breakpoints ✅
- [ ] Mobile first: 320px base
- [ ] Tablet: 768px (grid attiva)
- [ ] Desktop: 1024px+ (full experience)
- [ ] Desktop+: 1440px (wide screens)

---

## 🔍 Accessibility (WCAG 2.1 AA)

- [ ] **Keyboard Navigation** - Tab through all interactive elements
  - Back button
  - Resource links
  - All clickable areas accessible

- [ ] **Focus Indicators** - Visible on all interactive elements
  - Box-shadow rosso (var(--shadow-focus))
  - Outline: none (custom styling)

- [ ] **Color Contrast** - All text readable
  - Primary text (#1F2937) on white: 16.6:1 ✓ (AA/AAA)
  - Links (#ab1120) on white: 7.3:1 ✓ (AA/AAA)
  - Secondary text (#6B7280) on white: 6.5:1 ✓ (AA)

- [ ] **Semantic HTML** - Correct tag usage
  - `<main>` tag: content principale
  - `<header>` tag: page header
  - `<article>` tag: content
  - `<aside>` tag: sidebar

- [ ] **Alternative Text** - Images have alt text
  - Featured image: alt attribute present
  - Icons: `data-lucide` visible (SVG icons, no img tag)

- [ ] **Screen Reader** - Tested with VoiceOver/NVDA
  - Heading hierarchy correct
  - List items announced properly
  - Links have meaningful text

---

## 🚀 Performance Metrics

- [ ] **Lighthouse Score** - Target >90
  - FCP (First Contentful Paint): <1.5s
  - LCP (Largest Contentful Paint): <2.5s
  - CLS (Cumulative Layout Shift): <0.1
  - TTI (Time to Interactive): <3.5s

- [ ] **Image Performance**
  - Lazy loading active (loading="lazy")
  - Correct size format ('large')
  - No multiple requests

- [ ] **CSS/JS Loading**
  - CSS loaded inline (dist/main.css)
  - JS async/defer properly
  - No render blocking

---

## ✨ Visual Coherence Checklist

### Compared to `single-convenzione.php`

- [ ] **Header styling** - SAME spacing, typography
- [ ] **Featured image** - SAME aspect ratio behavior
- [ ] **Grid layout** - SAME 2-column structure
- [ ] **Sidebar** - SAME sticky positioning
- [ ] **Color scheme** - SAME brand colors
- [ ] **Typography scale** - SAME hierarchy
- [ ] **Spacing system** - SAME var(--space-*) usage
- [ ] **Interaction states** - SAME hover/focus/active

### Compared to Breadcrumb (PROMPT 5)

- [ ] **Breadcrumb visible** - On desktop/tablet
- [ ] **Back button present** - Fallback on mobile
- [ ] **Navigation hierarchy** - Home > Archive > Single
- [ ] **Link colors** - Consistent primary color

---

## 🐛 Known Issues to Monitor

1. **Sidebar on very narrow tablets** (< 600px)
   - May revert to full-width layout
   - Check media query boundaries

2. **Very long resource titles**
   - Should ellipsis (text-overflow: ellipsis)
   - Verify wrapping behavior

3. **Image loading on slow connections**
   - Lazy load may delay visible content
   - Test network throttling in DevTools

---

## 📊 Testing Results - Record Here

### Test Date: _______________
### Tester: _______________

| Device | Browser | Status | Notes |
|--------|---------|--------|-------|
| iPhone SE (320px) | Safari | [ ] | |
| iPhone 14 (430px) | Chrome | [ ] | |
| iPad (768px) | Safari | [ ] | |
| MacBook (1024px) | Chrome | [ ] | |
| Desktop (1440px) | Firefox | [ ] | |

---

## 🎯 Next Steps After Testing

1. **If all tests pass** ✅
   - Mark PROMPT 8 as COMPLETATO
   - Update TASKLIST_PRIORITA.md
   - Move to PROMPT 9 (Frontend Forms)

2. **If issues found** ⚠️
   - Document specifici issues con device/browser
   - Fix SCSS da relativo file
   - Re-test
   - Repeat loop

3. **Performance optimization** (if needed)
   - Minify CSS (already done)
   - Optimize images (WebP)
   - Cache strategy
   - CDN delivery

---

## 📝 Compilation Notes

**SCSS Compilation Status**: ✅ Ready  
**CSS Loaded**: ✅ main.css da /dist/  
**JS Loaded**: ✅ main.min.js da /dist/  
**Cache Bust**: ✅ Attivo (time() in functions.php)

**Commands to Recompile**:
```bash
# From: C:\Users\utente\Local Sites\nuova-formazione\app\public\wp-content\themes\meridiana-child

# Watch mode (for development)
npm run watch

# Build once
npm run build:scss

# Production build
npm run build
```

---

**Document versione**: 1.0  
**Last Updated**: 20 Ottobre 2025  
**Status**: Ready for Testing 🚀
