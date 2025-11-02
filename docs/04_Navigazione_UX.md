# 🧭 Navigazione e Layout

> **Ultimo aggiornamento**: 1 Novembre 2025
> **Fonte**: `templates/parts/navigation/`, `assets/css/src/layout/_navigation.scss`

**Leggi anche**:
- `01_Design_System.md` per lo styling dei componenti
- `08_Pagine_Templates.md` per l'implementazione nei template

---

## 🎯 Filosofia di Navigazione

Il sistema di navigazione è progettato per essere **mobile-first**, garantendo un'esperienza utente ottimale su tutti i dispositivi.

- **Mobile (`< 1024px`)**: Una **Bottom Navigation** fissa offre accesso immediato alle 5 sezioni principali, in stile app nativa.
- **Desktop (`>= 1024px`)**: Una **Sidebar Verticale a sinistra**, collassabile per massimizzare lo spazio di lavoro.
- **Accessibilità**: Tutti gli elementi interattivi sono accessibili via tastiera e hanno un'area di tocco minima di 44x44px (WCAG).
- **Coerenza**: Le icone e le etichette sono coerenti tra le due modalità.

---

## 🖥️ Navigazione Desktop: Sidebar Collassabile (`>= 1024px`)

La navigazione su desktop è gestita da una sidebar verticale che può essere espansa (240px) o collassata (70px) per ottimizzare lo spazio.

**File Principali**:
- `templates/parts/navigation/sidebar-nav.php`
- `assets/css/src/layout/_navigation.scss`

### Caratteristiche

- **Stato Persistente**: Lo stato (espanso/collassato) viene salvato nel `localStorage` del browser, mantenendo la preferenza dell'utente tra le sessioni.
- **Intelligenza Responsiva**: La sidebar è sempre espansa di default su schermi larghi e si adatta dinamicamente al resize della finestra.
- **Transizioni Fluide**: Animazioni CSS (`cubic-bezier`) per larghezza, opacità e trasformazioni rendono l'esperienza fluida.
- **Toggle Button**: Un pulsante dedicato permette di collassare o espandere la sidebar.

### Struttura HTML (`sidebar-nav.php`)

```html
<aside class="sidebar-nav" :class="sidebarCollapsed ? 'collapsed' : ''" x-data="sidebar()">
    <!-- Logo -->
    <div class="sidebar-nav__header">
        <a href="<?php echo home_url(); ?>">
            <img src=".../logo.svg" alt="Logo" class="logo-expanded">
            <img src=".../logo-icon.svg" alt="Logo Icon" class="logo-collapsed">
        </a>
    </div>

    <!-- Toggle Button -->
    <button @click="toggleSidebar" class="sidebar-nav__toggle" aria-label="Toggle sidebar">
        <i data-lucide="chevron-left"></i>
    </button>

    <!-- Menu Principale -->
    <nav class="sidebar-nav__menu">
        <a href="/" class="menu-item active">
            <i data-lucide="home"></i>
            <span>Home</span>
        </a>
        <!-- Altri item... -->
    </nav>

    <!-- Menu Utente (Footer) -->
    <div class="sidebar-nav__footer">
        <div class="user-profile">
            <!-- Avatar, nome, ruolo -->
        </div>
        <a href="<?php echo wp_logout_url(); ?>" class="menu-item logout">
            <i data-lucide="log-out"></i>
            <span>Esci</span>
        </a>
    </div>
</aside>
```

### Logica Alpine.js (`sidebar-nav.php`)

```javascript
function sidebar() {
    return {
        sidebarCollapsed: window.innerWidth < 1024 ? true : (localStorage.getItem('sidebarCollapsed') === 'true' || false),
        
        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            if (window.innerWidth >= 1024) {
                localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
            }
        },
        
        init() {
            window.addEventListener('resize', () => {
                if (window.innerWidth < 1024) {
                    this.sidebarCollapsed = true;
                } else {
                    this.sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true' || false;
                }
            });
        }
    }
}
```

---

## 📱 Navigazione Mobile: Bottom Bar (`< 1024px`)

Su schermi più piccoli, una bottom navigation bar fissa garantisce l'accesso rapido alle sezioni più importanti.

**File Principali**:
- `templates/parts/navigation/bottom-nav.php`
- `assets/css/src/layout/_navigation.scss`

### Struttura HTML (`bottom-nav.php`)

```html
<nav class="bottom-nav" role="navigation">
    <a href="/" class="bottom-nav__item active">
        <i data-lucide="home"></i>
        <span>Home</span>
    </a>
    <a href="/documentazione/" class="bottom-nav__item">
        <i data-lucide="file-text"></i>
        <span>Documenti</span>
    </a>
    <a href="/corsi/" class="bottom-nav__item">
        <i data-lucide="graduation-cap"></i>
        <span>Corsi</span>
    </a>
    <a href="/organigramma/" class="bottom-nav__item">
        <i data-lucide="users"></i>
        <span>Contatti</span>
    </a>
    
    <!-- Bottone per aprire il menu overlay con le voci secondarie -->
    <button class="bottom-nav__item" @click="$dispatch('open-mobile-menu')">
        <i data-lucide="menu"></i>
        <span>Altro</span>
    </button>
</nav>
```

### Menu Overlay Mobile

Il pulsante "Altro" apre un pannello modale che contiene link a sezioni secondarie come "Convenzioni", "Salute e Benessere" e link amministrativi per il Gestore.

---

## 🎯 Gestione dello Stato Attivo

Per evidenziare la pagina corrente, viene utilizzata una funzione helper PHP che confronta lo slug della pagina corrente con le condizioni della query di WordPress.

### Funzione PHP (`includes/helpers.php`)

```php
function get_current_nav_class($page_slug) {
    $current_page = '';
    
    if (is_front_page()) $current_page = 'home';
    elseif (is_post_type_archive('protocollo') || is_post_type_archive('modulo')) $current_page = 'documentazione';
    elseif (is_post_type_archive('sfwd-courses')) $current_page = 'corsi';
    elseif (is_page('contatti')) $current_page = 'organigramma';
    elseif (is_page('convenzioni')) $current_page = 'convenzioni';
    elseif (is_page('analytics')) $current_page = 'analytics';
    
    return $current_page === $page_slug ? 'active' : '';
}
```

### Utilizzo nel Template

```php
<a href="<?php echo home_url(); ?>" class="menu-item <?php echo get_current_nav_class('home'); ?>">
    <i data-lucide="home"></i>
    <span>Home</span>
</a>
```

---

## 📐 Layout e Container

Per mantenere coerenza visiva, il layout principale è gestito da classi container e da un wrapper per il contenuto.

- **`.sidebar-layout`**: Contenitore principale che affianca la sidebar e il contenuto.
- **`.content-wrapper`**: Avvolge il contenuto principale della pagina. Su desktop, ha un `padding-left` che si adatta dinamicamente alla larghezza della sidebar (70px o 240px).
- **`.container`**: Limita la larghezza massima del contenuto a `1400px` e applica padding laterali consistenti.

Questo sistema garantisce che il contenuto non si sovrapponga mai alla sidebar e si adatti fluidamente alle sue transizioni.

---

## 🤖 Checklist per Sviluppo

Quando si lavora sulla navigazione:

- **Mobile First**: Progettare e testare prima sulla `bottom-nav`, poi adattare per la `sidebar-nav`.
- **Accessibilità**: Assicurarsi che tutti gli elementi siano navigabili via tastiera e che `aria-current="page"` sia applicato correttamente.
- **Performance**: Le icone Lucide sono renderizzate via JavaScript. Assicurarsi che lo script sia caricato in modo efficiente.
- **Coerenza**: Mantenere le stesse icone e (ove possibile) etichette tra la versione mobile e desktop.
- **Z-Index**: Rispettare il sistema di `z-index` definito nel Design System per evitare sovrapposizioni con modali o altri elementi flottanti.