<?php
/**
 * Sidebar Navigation - Desktop Only
 * Navigazione laterale per versione desktop
 */

$current_user = wp_get_current_user();
$user_name = $current_user->display_name;
$user_role = 'Dipendente'; // Default

// Determina il ruolo leggibile
if (current_user_can('view_analytics')) {
    $user_role = 'Gestore Piattaforma';
}
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
        
        <a href="<?php echo get_post_type_archive_link('protocollo'); ?>" 
           class="sidebar-nav__item <?php echo (is_post_type_archive('protocollo') || is_post_type_archive('modulo')) ? 'active' : ''; ?>">
            <i data-lucide="file-text"></i>
            <span>Documentazione</span>
        </a>
        
        <a href="<?php echo get_post_type_archive_link('sfwd-courses'); ?>" 
           class="sidebar-nav__item <?php echo is_post_type_archive('sfwd-courses') ? 'active' : ''; ?>">
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
        
        <a href="<?php echo get_permalink(get_page_by_path('organigramma')); ?>" 
           class="sidebar-nav__item <?php echo is_page('organigramma') ? 'active' : ''; ?>">
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
    </div>
    
    <!-- Footer con info utente -->
    <div class="sidebar-nav__footer">
        <div class="sidebar-nav__user" onclick="openUserProfileModal()" role="button" tabindex="0" aria-label="Apri profilo utente">
            <div class="user-avatar">
                <i data-lucide="user"></i>
            </div>
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
