<?php
/**
 * Sidebar Navigation - Desktop Only
 * Navigazione laterale per versione desktop
 * 
 * NUOVO: Profilo Professionale Dinamico
 * Mostra il "Profilo Professionale" dell'utente loggato al posto di "Dipendente"
 * Fallback: Se vuoto, mostra "Dipendente" come default
 */

$current_user = wp_get_current_user();
$user_name = $current_user->display_name;

// **NUOVO**: Recupera il Profilo Professionale dell'utente (return_format: value)
$profilo_value = get_field('profilo_professionale', 'user_' . $current_user->ID);

// La CF ritorna il valore direttamente ("Medico", "Infermiere", ecc.)
// Non è un term_id, quindi usarlo direttamente
// FIX: Se profilo_professionale è vuoto, non usare "Dipendente" di default
// Invece, usa il ruolo WordPress effettivo dell'utente
if (!empty($profilo_value)) {
    $user_role = $profilo_value;
} else {
    // Recupera il ruolo WordPress dell'utente (es: "subscriber", "administrator")
    $roles = $current_user->roles;
    
    // Traduci il ruolo in etichetta leggibile
    if (in_array('administrator', $roles)) {
        $user_role = 'Amministratore';
    } elseif (in_array('editor', $roles)) {
        $user_role = 'Editor';
    } elseif (in_array('author', $roles)) {
        $user_role = 'Autore';
    } else {
        // Fallback finale: "Utente" generico
        $user_role = 'Utente';
    }
}

// Se l'utente è Gestore, mostra SEMPRE questo (priority massima)
if (current_user_can('view_analytics') || current_user_can('gestore_piattaforma')) {
    $user_role = 'Gestore Piattaforma';
}

error_log('[Sidebar] User: ' . $current_user->user_login . ' | Role: ' . $user_role);
?>

<nav class="sidebar-nav" role="navigation" aria-label="Navigazione principale">
    <!-- Logo -->
    <div class="sidebar-nav__logo">
        <?php 
        $custom_logo_id = get_theme_mod('custom_logo');
        if ($custom_logo_id) {
            $logo = wp_get_attachment_image_src($custom_logo_id, 'full');
            echo '<img src="' . esc_url($logo[0]) . '" alt="' . get_bloginfo('name') . '">';
        } else {
            echo '<div class="logo-text">La Meridiana</div>';
        }
        ?>
    </div>
    
    <!-- Menu principale -->
    <div class="sidebar-nav__menu">
        <a href="<?php echo home_url(); ?>" 
           class="sidebar-nav__item <?php echo is_front_page() ? 'active' : ''; ?>"
           <?php echo is_front_page() ? 'aria-current="page"' : ''; ?>>
            <i data-lucide="home"></i>
            <span>Home</span>
        </a>
        
        <a href="<?php echo home_url('/docs/'); ?>" 
           class="sidebar-nav__item <?php echo is_page('docs') ? 'active' : ''; ?>">
            <i data-lucide="file-text"></i>
            <span>Documentazione</span>
        </a>
        
        <a href="<?php echo home_url('/corsi/'); ?>" 
           class="sidebar-nav__item <?php echo (is_post_type_archive('sfwd-courses') || is_page('corsi')) ? 'active' : ''; ?>">
            <i data-lucide="graduation-cap"></i>
            <span>Corsi</span>
            <?php 
            // TODO: Implementare conteggio notifiche corsi
            $notifiche_corsi = 0;
            if ($notifiche_corsi > 0): 
            ?>
            <span class="badge-count"><?php echo $notifiche_corsi; ?></span>
            <?php endif; ?>
        </a>
        
        <a href="<?php echo home_url('/contatti/'); ?>" 
           class="sidebar-nav__item <?php echo is_page('contatti') ? 'active' : ''; ?>">
            <i data-lucide="users"></i>
            <span>Organigramma</span>
        </a>
        
        <div class="sidebar-nav__divider"></div>
        
        <a href="<?php echo get_post_type_archive_link('convenzione'); ?>" 
           class="sidebar-nav__item <?php echo is_post_type_archive('convenzione') ? 'active' : ''; ?>">
            <i data-lucide="tag"></i>
            <span>Convenzioni</span>
        </a>
        
        <a href="<?php echo get_post_type_archive_link('salute-e-benessere-l'); ?>" 
           class="sidebar-nav__item <?php echo is_post_type_archive('salute-e-benessere-l') ? 'active' : ''; ?>">
            <i data-lucide="heart"></i>
            <span>Salute e Benessere</span>
        </a>
        
        <?php 
        $blog_page_id = get_option('page_for_posts');
        if ($blog_page_id):
        ?>
        <a href="<?php echo get_permalink($blog_page_id); ?>" 
           class="sidebar-nav__item <?php echo is_home() || is_singular('post') ? 'active' : ''; ?>">
            <i data-lucide="newspaper"></i>
            <span>Comunicazioni</span>
        </a>
        <?php endif; ?>
        
        <?php if (current_user_can('view_analytics')): ?>
        <div class="sidebar-nav__divider"></div>
        
        <a href="<?php echo get_permalink(get_page_by_path('analytics')); ?>" 
           class="sidebar-nav__item <?php echo is_page('analytics') ? 'active' : ''; ?>">
            <i data-lucide="bar-chart-2"></i>
            <span>Analytics</span>
        </a>
        <?php endif; ?>
        
        <?php if (current_user_can('manage_platform') || current_user_can('manage_options')): ?>
        <div class="sidebar-nav__divider"></div>
        
        <a href="<?php echo home_url('/dashboard-gestore/'); ?>" 
           class="sidebar-nav__item <?php echo is_page('dashboard-gestore') ? 'active' : ''; ?>">
            <i data-lucide="settings"></i>
            <span>Dashboard Gestore</span>
        </a>
        <?php endif; ?>
    </div>
    
    <!-- Footer con info utente -->
    <div class="sidebar-nav__footer">
        <div class="sidebar-nav__user" onclick="openUserProfileModal()" role="button" tabindex="0" aria-label="Apri profilo utente">
            <?php echo meridiana_display_user_avatar(get_current_user_id(), 'medium'); ?>
            <div class="user-info">
                <span class="user-name"><?php echo esc_html($user_name); ?></span>
                <span class="user-role"><?php echo esc_html($user_role); ?></span>
            </div>
        </div>
        
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="sidebar-nav__logout">
            <i data-lucide="log-out"></i>
            <span>Esci</span>
        </a>
    </div>
</nav>
