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
    
    <!-- DOCUMENTI - Archive CPT 'protocollo' -->
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
    
    <!-- CONTATTI (ex Organigramma) - Page 'contatti' -->
    <a href="<?php echo home_url('/contatti/'); ?>" 
       class="bottom-nav__item <?php echo is_page('contatti') ? 'active' : ''; ?>">
        <i data-lucide="users"></i>
        <span>Contatti</span>
    </a>
</nav>
