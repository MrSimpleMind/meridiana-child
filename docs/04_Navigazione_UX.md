# 🧭 Navigazione e Layout

> **Contesto**: Sistema di navigazione mobile-first, bottom nav, desktop header, menu structure

**Leggi anche**: 
- `01_Design_System.md` per styling componenti
- `08_Pagine_Templates.md` per implementazione nei template

---

## 📱 Filosofia Navigazione

### Principi
- **Mobile-first**: Bottom navigation come app native
- **Desktop**: Top navigation classica
- **Touch-friendly**: Min 44x44px (WCAG)
- **Massimo 5 tab principali** visibili
- **Menu overlay** per voci secondarie

---

## 📲 Bottom Navigation (Mobile < 768px)

### HTML Structure

```html
<!-- Bottom Navigation - Solo mobile -->
<nav class="bottom-nav" role="navigation" aria-label="Navigazione principale">
    <a href="/" class="bottom-nav__item" aria-current="page">
        <i data-lucide="home"></i>
        <span>Home</span>
    </a>
    
    <a href="/documentazione" class="bottom-nav__item">
        <i data-lucide="file-text"></i>
        <span>Documenti</span>
    </a>
    
    <a href="/corsi" class="bottom-nav__item">
        <i data-lucide="graduation-cap"></i>
        <span>Corsi</span>
        <span class="badge-dot" aria-label="2 nuove notifiche"></span>
    </a>
    
    <a href="/organigramma" class="bottom-nav__item">
        <i data-lucide="users"></i>
        <span>Contatti</span>
    </a>
    
    <button class="bottom-nav__item" id="mobile-menu-trigger">
        <i data-lucide="menu"></i>
        <span>Altro</span>
    </button>
</nav>
```

### CSS (vedi 01_Design_System.md per completo)

```scss
.bottom-nav {
    display: flex;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: var(--color-bg-primary);
    border-top: 1px solid var(--color-border);
    padding: var(--space-2) 0;
    z-index: var(--z-fixed); // 100
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
    
    @media (min-width: 768px) {
        display: none;
    }
}

.bottom-nav__item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--space-1);
    padding: var(--space-2);
    color: var(--color-text-secondary);
    font-size: var(--font-size-xs);
    min-height: 44px;
    min-width: 44px;
    
    &[aria-current="page"],
    &.active {
        color: var(--color-primary);
    }
}
```

### Mobile Menu Overlay

```html
<div class="mobile-menu-overlay" id="mobile-menu" hidden>
    <div class="mobile-menu">
        <div class="mobile-menu__header">
            <h2>Menu</h2>
            <button class="btn-close" aria-label="Chiudi menu">
                <i data-lucide="x"></i>
            </button>
        </div>
        
        <nav class="mobile-menu__nav">
            <a href="/convenzioni">
                <i data-lucide="tag"></i>
                <span>Convenzioni</span>
            </a>
            <a href="/salute-benessere">
                <i data-lucide="heart"></i>
                <span>Salute e Benessere</span>
            </a>
            <a href="/analytics" class="admin-only">
                <i data-lucide="bar-chart-2"></i>
                <span>Analytics</span>
            </a>
        </nav>
        
        <div class="mobile-menu__footer">
            <a href="/logout" class="btn btn-outline">
                <i data-lucide="log-out"></i>
                Esci
            </a>
        </div>
    </div>
</div>
```

### JavaScript (Alpine.js)

```javascript
// assets/js/src/mobile-menu.js

document.addEventListener('alpine:init', () => {
    Alpine.data('mobileMenu', () => ({
        open: false,
        
        toggle() {
            this.open = !this.open;
            document.body.style.overflow = this.open ? 'hidden' : '';
        },
        
        close() {
            this.open = false;
            document.body.style.overflow = '';
        }
    }));
});

// Usage in HTML:
// <div x-data="mobileMenu">
//     <button @click="toggle()">Menu</button>
//     <div x-show="open" @click.away="close()">...</div>
// </div>
```

---

## 🖥 Desktop Navigation (>= 768px)

### HTML Structure

```html
<header class="site-header" role="banner">
    <div class="container">
        <div class="site-header__inner">
            <!-- Logo -->
            <a href="/" class="site-logo">
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/logo.svg" 
                     alt="Cooperativa La Meridiana">
            </a>
            
            <!-- Main Nav -->
            <nav class="main-nav" role="navigation">
                <a href="/" aria-current="page">Home</a>
                <a href="/documentazione">Documentazione</a>
                <a href="/corsi">Corsi</a>
                <a href="/organigramma">Organigramma</a>
                
                <!-- Dropdown -->
                <div class="nav-dropdown">
                    <button class="nav-dropdown__trigger">
                        Altro <i data-lucide="chevron-down"></i>
                    </button>
                    <div class="nav-dropdown__menu">
                        <a href="/convenzioni">Convenzioni</a>
                        <a href="/salute-benessere">Salute e Benessere</a>
                    </div>
                </div>
            </nav>
            
            <!-- Header Actions -->
            <div class="header-actions">
                <!-- Notifiche -->
                <button class="btn-icon" aria-label="Notifiche">
                    <i data-lucide="bell"></i>
                    <span class="badge-count">2</span>
                </button>
                
                <!-- User Menu -->
                <div class="user-menu">
                    <button class="user-menu__trigger">
                        <span class="user-avatar">
                            <i data-lucide="user"></i>
                        </span>
                        <span class="user-name"><?php echo wp_get_current_user()->display_name; ?></span>
                        <i data-lucide="chevron-down"></i>
                    </button>
                    <div class="user-menu__dropdown">
                        <?php if(current_user_can('view_analytics')): ?>
                        <a href="/analytics">Analytics</a>
                        <hr>
                        <?php endif; ?>
                        <a href="<?php echo wp_logout_url(home_url()); ?>">Esci</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
```

### CSS Desktop Navigation

```scss
.site-header {
    display: none;
    background-color: var(--color-bg-primary);
    border-bottom: 1px solid var(--color-border);
    position: sticky;
    top: 0;
    z-index: var(--z-sticky);
    box-shadow: var(--shadow-sm);
    
    @media (min-width: 768px) {
        display: block;
    }
}

.site-header__inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--space-4) 0;
    gap: var(--space-8);
}

.main-nav {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    flex: 1;
    
    a {
        padding: var(--space-3) var(--space-4);
        color: var(--color-text-secondary);
        font-weight: var(--font-weight-medium);
        border-radius: var(--radius-md);
        
        &:hover {
            color: var(--color-primary);
            background-color: var(--color-bg-secondary);
        }
        
        &[aria-current="page"] {
            color: var(--color-primary);
        }
    }
}

.nav-dropdown {
    position: relative;
    
    &__trigger {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        padding: var(--space-3) var(--space-4);
        border: none;
        background: none;
        cursor: pointer;
    }
    
    &__menu {
        position: absolute;
        top: 100%;
        left: 0;
        margin-top: var(--space-2);
        background: var(--color-bg-primary);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-lg);
        min-width: 200px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.2s ease;
    }
    
    &:hover &__menu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
}
```

---

## 🎯 Gestione Stato Attivo

### PHP Function

```php
// includes/helpers.php

function get_current_nav_class($page_slug) {
    $current_page = '';
    
    if (is_front_page()) {
        $current_page = 'home';
    } elseif (is_post_type_archive('protocollo') || is_post_type_archive('modulo')) {
        $current_page = 'documentazione';
    } elseif (is_post_type_archive('sfwd-courses')) {
        $current_page = 'corsi';
    } elseif (is_page('organigramma')) {
        $current_page = 'organigramma';
    } elseif (is_page('convenzioni')) {
        $current_page = 'convenzioni';
    }
    
    return $current_page === $page_slug ? 'active' : '';
}

// Uso:
// <a href="/" class="<?php echo get_current_nav_class('home'); ?>">
```

---

## 📐 Layout Container

### Container System

```scss
.container {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 var(--space-4);
    
    @media (min-width: 768px) {
        padding: 0 var(--space-6);
    }
    
    @media (min-width: 1200px) {
        padding: 0 var(--space-8);
    }
}

.container-narrow {
    max-width: 900px;
}

.container-wide {
    max-width: 1600px;
}
```

### Content Wrapper (per bottom nav)

```scss
.content-wrapper {
    min-height: 100vh;
    padding-bottom: 80px; // Altezza bottom nav + margine
    
    @media (min-width: 768px) {
        padding-bottom: 0;
        padding-top: var(--space-6);
    }
}
```

---

## 🎨 Template Parts

### Bottom Nav Template

```php
// templates/parts/navigation/bottom-nav.php

<nav class="bottom-nav" role="navigation" aria-label="Navigazione principale">
    <a href="<?php echo home_url(); ?>" 
       class="bottom-nav__item <?php echo is_front_page() ? 'active' : ''; ?>">
        <i data-lucide="home"></i>
        <span>Home</span>
    </a>
    
    <a href="<?php echo get_post_type_archive_link('protocollo'); ?>" 
       class="bottom-nav__item <?php echo get_current_nav_class('documentazione'); ?>">
        <i data-lucide="file-text"></i>
        <span>Documenti</span>
    </a>
    
    <a href="<?php echo get_post_type_archive_link('sfwd-courses'); ?>" 
       class="bottom-nav__item <?php echo get_current_nav_class('corsi'); ?>">
        <i data-lucide="graduation-cap"></i>
        <span>Corsi</span>
        <?php 
        $count_notifiche = get_corsi_notifiche_count();
        if($count_notifiche > 0): 
        ?>
        <span class="badge-dot"></span>
        <?php endif; ?>
    </a>
    
    <a href="<?php echo get_permalink(get_page_by_path('organigramma')); ?>" 
       class="bottom-nav__item <?php echo get_current_nav_class('organigramma'); ?>">
        <i data-lucide="users"></i>
        <span>Contatti</span>
    </a>
    
    <button class="bottom-nav__item" 
            x-data 
            @click="$dispatch('open-mobile-menu')">
        <i data-lucide="menu"></i>
        <span>Altro</span>
    </button>
</nav>
```

### Desktop Header Template

```php
// templates/parts/navigation/desktop-nav.php

<header class="site-header" role="banner">
    <div class="container">
        <div class="site-header__inner">
            <a href="<?php echo home_url(); ?>" class="site-logo">
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/logo.svg" 
                     alt="Cooperativa La Meridiana">
            </a>
            
            <nav class="main-nav">
                <a href="<?php echo home_url(); ?>" 
                   <?php echo is_front_page() ? 'aria-current="page"' : ''; ?>>
                    Home
                </a>
                <a href="<?php echo get_post_type_archive_link('protocollo'); ?>">
                    Documentazione
                </a>
                <a href="<?php echo get_post_type_archive_link('sfwd-courses'); ?>">
                    Corsi
                </a>
                <a href="<?php echo get_permalink(get_page_by_path('organigramma')); ?>">
                    Organigramma
                </a>
                
                <div class="nav-dropdown">
                    <button class="nav-dropdown__trigger">
                        Altro <i data-lucide="chevron-down"></i>
                    </button>
                    <div class="nav-dropdown__menu">
                        <a href="<?php echo get_post_type_archive_link('convenzione'); ?>">
                            Convenzioni
                        </a>
                        <a href="<?php echo get_post_type_archive_link('salute_benessere'); ?>">
                            Salute e Benessere
                        </a>
                    </div>
                </div>
            </nav>
            
            <div class="header-actions">
                <?php get_template_part('templates/parts/navigation/notifiche-icon'); ?>
                <?php get_template_part('templates/parts/navigation/user-menu'); ?>
            </div>
        </div>
    </div>
</header>
```

---

## 🤖 Checklist per IA

Quando lavori su navigazione:

- [ ] Bottom nav: sempre 5 item max visibili
- [ ] Touch targets: minimo 44x44px
- [ ] Active state: `aria-current="page"` per screen reader
- [ ] Badge notifiche: sempre con `aria-label`
- [ ] Mobile menu: gestire `overflow: hidden` su body
- [ ] Z-index: rispetta sistema stratificato (vedi 01_Design_System)
- [ ] Sticky header: usa `position: sticky` non `fixed`
- [ ] Logo: sempre link a home
- [ ] Dropdown: accessibile via tastiera (`:focus-within`)
- [ ] Test su mobile reale, non solo dev tools

---

**🧭 Sistema di navigazione completo mobile-first.**
