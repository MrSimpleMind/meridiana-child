<?php
/**
 * Bottom Navigation - Mobile Only
 * Navigazione principale mobile con 4 tab fisse
 * 
 * UNIFORME AL DESKTOP: Punta agli stessi percorsi di sidebar-nav.php
 * per garantire coerenza UX tra mobile e desktop
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
    
    <!-- DOCUMENTAZIONE - Archive CPT 'protocollo' (come desktop) -->
    <a href="<?php echo get_post_type_archive_link('protocollo'); ?>" 
       class="bottom-nav__item <?php echo (is_post_type_archive('protocollo') || is_post_type_archive('modulo') || is_singular('protocollo') || is_singular('modulo')) ? 'active' : ''; ?>">
        <i data-lucide="file-text"></i>
        <span>Documenti</span>
    </a>
    
    <!-- CORSI - Archive CPT 'sfwd-courses' (come desktop) -->
    <a href="<?php echo get_post_type_archive_link('sfwd-courses'); ?>" 
       class="bottom-nav__item <?php echo (is_post_type_archive('sfwd-courses') || is_singular('sfwd-courses')) ? 'active' : ''; ?>">
        <i data-lucide="graduation-cap"></i>
        <span>Corsi</span>
        <?php 
        $notifiche_corsi = 0; // TODO: Implementare conteggio notifiche corsi
        if ($notifiche_corsi > 0): 
        ?>
        <span class="badge-dot" aria-label="<?php echo $notifiche_corsi; ?> nuove notifiche"></span>
        <?php endif; ?>
    </a>
    
    <!-- CONTATTI - Page 'contatti' (come desktop) -->
    <a href="<?php echo home_url('/contatti/'); ?>" 
       class="bottom-nav__item <?php echo is_page('contatti') ? 'active' : ''; ?>">
        <i data-lucide="users"></i>
        <span>Contatti</span>
    </a>
</nav>
