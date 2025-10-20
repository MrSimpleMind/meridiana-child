<?php
/**
 * Bottom Navigation - Mobile Only
 * Navigazione principale mobile con tab fisse + menu overlay
 * 
 * UNIFORME AL DESKTOP: Punta agli stessi percorsi di sidebar-nav.php
 * per garantire coerenza UX tra mobile e desktop
 * 
 * DESIGN:
 * - 5 tab principali (home, docs, corsi, organigramma, menu)
 * - Tab "menu" apre overlay con voci aggiuntive (convenzioni, salute, comunicazioni, analytics)
 */

$current_user = wp_get_current_user();
$blog_page_id = get_option('page_for_posts');
?>

<nav class="bottom-nav" role="navigation" aria-label="Navigazione principale">
    <!-- HOME -->
    <a href="<?php echo home_url(); ?>" 
       class="bottom-nav__item <?php echo is_front_page() ? 'active' : ''; ?>"
       <?php echo is_front_page() ? 'aria-current="page"' : ''; ?>>
        <i data-lucide="home"></i>
        <span>Home</span>
    </a>
    
    <!-- DOCUMENTAZIONE - Archive CPT 'protocollo' -->
    <a href="<?php echo get_post_type_archive_link('protocollo'); ?>" 
       class="bottom-nav__item <?php echo (is_post_type_archive('protocollo') || is_post_type_archive('modulo') || is_singular('protocollo') || is_singular('modulo')) ? 'active' : ''; ?>">
        <i data-lucide="file-text"></i>
        <span>Documenti</span>
    </a>
    
    <!-- CORSI - Archive CPT 'sfwd-courses' -->
    <a href="<?php echo get_post_type_archive_link('sfwd-courses'); ?>" 
       class="bottom-nav__item <?php echo (is_post_type_archive('sfwd-courses') || is_singular('sfwd-courses')) ? 'active' : ''; ?>">
        <i data-lucide="graduation-cap"></i>
        <span>Corsi</span>
    </a>
    
    <!-- ORGANIGRAMMA - Page 'contatti' -->
    <a href="<?php echo home_url('/contatti/'); ?>" 
       class="bottom-nav__item <?php echo is_page('contatti') ? 'active' : ''; ?>">
        <i data-lucide="users"></i>
        <span>Organigramma</span>
    </a>
    
    <!-- MENU OVERLAY (hamburger) - mostra voci aggiuntive -->
    <button class="bottom-nav__item bottom-nav__menu-toggle" 
            id="bottom-nav-menu-toggle"
            aria-label="Apri menu"
            aria-expanded="false"
            aria-controls="bottom-nav-overlay">
        <i data-lucide="menu"></i>
        <span>Menu</span>
    </button>
</nav>

<!-- MENU OVERLAY - Voci aggiuntive -->
<div class="bottom-nav-overlay" id="bottom-nav-overlay" hidden>
    <div class="bottom-nav-overlay__header">
        <h2>Menu</h2>
        <button class="bottom-nav-overlay__close" id="bottom-nav-menu-close" aria-label="Chiudi menu">
            <i data-lucide="x"></i>
        </button>
    </div>
    
    <nav class="bottom-nav-overlay__menu">
        <!-- CONVENZIONI -->
        <a href="<?php echo get_post_type_archive_link('convenzione'); ?>" 
           class="bottom-nav-overlay__item <?php echo is_post_type_archive('convenzione') ? 'active' : ''; ?>">
            <i data-lucide="tag"></i>
            <span>Convenzioni</span>
        </a>
        
        <!-- SALUTE E BENESSERE -->
        <a href="<?php echo get_post_type_archive_link('salute-e-benessere-l'); ?>" 
           class="bottom-nav-overlay__item <?php echo is_post_type_archive('salute-e-benessere-l') ? 'active' : ''; ?>">
            <i data-lucide="heart"></i>
            <span>Salute e Benessere</span>
        </a>
        
        <!-- COMUNICAZIONI -->
        <?php if ($blog_page_id): ?>
        <a href="<?php echo get_permalink($blog_page_id); ?>" 
           class="bottom-nav-overlay__item <?php echo is_home() || is_singular('post') ? 'active' : ''; ?>">
            <i data-lucide="newspaper"></i>
            <span>Comunicazioni</span>
        </a>
        <?php endif; ?>
        
        <!-- ANALYTICS (solo per admin/gestore) -->
        <?php if (current_user_can('view_analytics') || current_user_can('gestore_piattaforma')): ?>
        <a href="<?php echo get_permalink(get_page_by_path('analytics')); ?>" 
           class="bottom-nav-overlay__item <?php echo is_page('analytics') ? 'active' : ''; ?>">
            <i data-lucide="bar-chart-2"></i>
            <span>Analytics</span>
        </a>
        <?php endif; ?>
    </nav>
</div>

<!-- JavaScript per menu overlay mobile -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('bottom-nav-menu-toggle');
    const menuClose = document.getElementById('bottom-nav-menu-close');
    const menuOverlay = document.getElementById('bottom-nav-overlay');
    
    if (!menuToggle || !menuOverlay) return;
    
    // Apri menu
    menuToggle.addEventListener('click', function() {
        menuOverlay.hidden = false;
        menuToggle.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden'; // Disabilita scroll
    });
    
    // Chiudi menu
    function closeMenu() {
        menuOverlay.hidden = true;
        menuToggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = 'auto';
    }
    
    menuClose.addEventListener('click', closeMenu);
    
    // Chiudi menu se clicchi su un link
    const menuItems = menuOverlay.querySelectorAll('a');
    menuItems.forEach(item => {
        item.addEventListener('click', closeMenu);
    });
    
    // Chiudi menu se clicchi outside
    menuOverlay.addEventListener('click', function(e) {
        if (e.target === menuOverlay) {
            closeMenu();
        }
    });
    
    // Chiudi menu con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !menuOverlay.hidden) {
            closeMenu();
        }
    });
});
</script>
