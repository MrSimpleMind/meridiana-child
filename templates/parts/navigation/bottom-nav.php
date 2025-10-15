<?php
/**
 * Bottom Navigation - Mobile Only
 * Navigazione principale mobile con 4 tab fisse
 */

// Slug delle pagine
$slug_documentazione = 'docs';
$slug_corsi = 'corsi';
$slug_contatti = 'contatti';
?>

<nav class="bottom-nav" role="navigation" aria-label="Navigazione principale">
    <a href="<?php echo home_url(); ?>" 
       class="bottom-nav__item <?php echo is_front_page() ? 'active' : ''; ?>"
       <?php echo is_front_page() ? 'aria-current="page"' : ''; ?>>
        <i data-lucide="home"></i>
        <span>Home</span>
    </a>
    
    <a href="<?php echo home_url('/' . $slug_documentazione . '/'); ?>" 
       class="bottom-nav__item <?php echo (is_post_type_archive('protocollo') || is_post_type_archive('modulo') || is_singular('protocollo') || is_singular('modulo') || is_page($slug_documentazione)) ? 'active' : ''; ?>">
        <i data-lucide="file-text"></i>
        <span>Docs</span>
    </a>
    
    <a href="<?php echo home_url('/' . $slug_corsi . '/'); ?>" 
       class="bottom-nav__item <?php echo (is_post_type_archive('sfwd-courses') || is_singular('sfwd-courses') || is_page($slug_corsi)) ? 'active' : ''; ?>">
        <i data-lucide="graduation-cap"></i>
        <span>Corsi</span>
        <?php 
        // TODO: implementare conteggio notifiche corsi
        $corsi_notifiche = 0;
        if ($corsi_notifiche > 0): 
        ?>
        <span class="badge-dot" aria-label="<?php echo $corsi_notifiche; ?> nuove notifiche"></span>
        <?php endif; ?>
    </a>
    
    <a href="<?php echo home_url('/' . $slug_contatti . '/'); ?>" 
       class="bottom-nav__item <?php echo (is_page($slug_contatti) || is_singular('organigramma')) ? 'active' : ''; ?>">
        <i data-lucide="users"></i>
        <span>Contatti</span>
    </a>
</nav>
