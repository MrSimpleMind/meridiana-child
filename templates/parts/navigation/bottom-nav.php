<?php
/**
 * Bottom Navigation - Mobile Only (4 Tab)
 * Navigazione principale mobile semplificata
 * 
 * DESIGN:
 * - 4 tab principali (Home, Documenti, Corsi, Contatti)
 * - Voci extra (Convenzioni, Salute, Comunicazioni) accessibili dalla Home in mobile
 */
?>

<nav class="bottom-nav" role="navigation" aria-label="Navigazione principale">
    <!-- HOME -->
    <a href="<?php echo home_url(); ?>" 
       class="bottom-nav__item <?php echo is_front_page() ? 'active' : ''; ?>"
       <?php echo is_front_page() ? 'aria-current="page"' : ''; ?>>
        <i data-lucide="home"></i>
        <span>Home</span>
    </a>
    
    <!-- DOCUMENTI - Pagina /docs/ -->
    <a href="<?php echo home_url('/docs/'); ?>" 
       class="bottom-nav__item <?php echo is_page('docs') ? 'active' : ''; ?>">
        <i data-lucide="file-text"></i>
        <span>Documenti</span>
    </a>
    
    <!-- CORSI - Archive CPT 'sfwd-courses' -->
    <a href="<?php echo get_post_type_archive_link('sfwd-courses'); ?>" 
       class="bottom-nav__item <?php echo (is_post_type_archive('sfwd-courses') || is_singular('sfwd-courses')) ? 'active' : ''; ?>">
        <i data-lucide="graduation-cap"></i>
        <span>Corsi</span>
    </a>
    
    <!-- CONTATTI (ex Organigramma) - Page 'contatti' -->
    <a href="<?php echo home_url('/contatti/'); ?>"
       class="bottom-nav__item <?php echo is_page('contatti') ? 'active' : ''; ?>">
        <i data-lucide="users"></i>
        <span>Contatti</span>
    </a>

    <!-- ADMIN MENU (Gestore/Admin only) con popup -->
    <?php if (current_user_can('gestore_piattaforma') || current_user_can('manage_options')): ?>
    <div class="bottom-nav__item-wrapper" x-data="{ adminMenuOpen: false }">
        <button type="button"
                class="bottom-nav__item <?php echo (is_page('dashboard-gestore') || is_page('analitiche')) ? 'active' : ''; ?>"
                @click="adminMenuOpen = !adminMenuOpen"
                @click.away="adminMenuOpen = false">
            <i data-lucide="shield"></i>
            <span>Admin</span>
        </button>

        <!-- Popup menu -->
        <div class="bottom-nav__popup" x-show="adminMenuOpen" x-cloak x-transition>
            <a href="<?php echo home_url('/dashboard-gestore/'); ?>"
               class="bottom-nav__popup-item <?php echo is_page('dashboard-gestore') ? 'active' : ''; ?>">
                <i data-lucide="settings"></i>
                <span>Dashboard Gestore</span>
            </a>
            <a href="<?php echo home_url('/analitiche/'); ?>"
               class="bottom-nav__popup-item <?php echo is_page('analitiche') ? 'active' : ''; ?>">
                <i data-lucide="bar-chart-2"></i>
                <span>Analitiche</span>
            </a>
        </div>
    </div>
    <?php endif; ?>
</nav>
