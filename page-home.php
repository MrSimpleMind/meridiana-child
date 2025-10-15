<?php
/**
 * Template Name: Home Dashboard
 * Description: Pagina principale della piattaforma - Mobile First
 */

get_header(); 

$current_user = wp_get_current_user();
$user_first_name = $current_user->first_name ? $current_user->first_name : $current_user->display_name;
?>

<div class="content-wrapper">
    <div class="container home-container">
        
        <!-- Header Utente Mobile -->
        <div class="home-header">
            <div class="home-header__user">
                <div class="user-avatar">
                    <i data-lucide="user"></i>
                </div>
                <h1 class="home-header__greeting">Ciao <?php echo esc_html($user_first_name); ?></h1>
            </div>
            <button class="btn-icon home-header__notifications" aria-label="Notifiche">
                <i data-lucide="bell"></i>
                <?php 
                // Conteggio notifiche (da implementare)
                $notifiche_count = 0; // TODO: implementare logica conteggio
                if ($notifiche_count > 0): 
                ?>
                <span class="badge-count"><?php echo $notifiche_count; ?></span>
                <?php endif; ?>
            </button>
        </div>

        <!-- Sezione "Per te" - Convenzioni -->
        <section class="home-section home-convenzioni">
            <div class="home-section__header">
                <h2 class="home-section__title">Per te</h2>
                <?php 
                // Cerca la pagina per titolo invece che per slug
                $convenzioni_page = get_page_by_title('Archivio convenzioni');
                echo '<!-- DEBUG Convenzioni: ' . ($convenzioni_page ? 'Trovata ID: ' . $convenzioni_page->ID : 'NON TROVATA') . ' -->';
                if ($convenzioni_page):
                ?>
                <a href="<?php echo get_permalink($convenzioni_page); ?>" class="btn btn-link">
                    Vedi tutto <i data-lucide="arrow-right"></i>
                </a>
                <?php else: ?>
                <!-- DEBUG: Pagina Archivio convenzioni non trovata -->
                <?php endif; ?>
            </div>
            
            <?php get_template_part('templates/parts/home/convenzioni-carousel'); ?>
        </section>

        <!-- Sezione "Ultime notizie" -->
        <section class="home-section home-news">
            <div class="home-section__header">
                <h2 class="home-section__title">Ultime notizie</h2>
                <?php 
                $blog_page = get_option('page_for_posts');
                $blog_url = $blog_page ? get_permalink($blog_page) : home_url('/blog/');
                ?>
                <a href="<?php echo esc_url($blog_url); ?>" class="btn btn-link">
                    Vedi tutto <i data-lucide="arrow-right"></i>
                </a>
            </div>
            
            <?php get_template_part('templates/parts/home/news-list'); ?>
        </section>

        <!-- Sezione "Salute e benessere" -->
        <section class="home-section home-salute">
            <div class="home-section__header">
                <h2 class="home-section__title">Salute e benessere</h2>
                <?php 
                // Cerca la pagina per titolo invece che per slug
                $salute_page = get_page_by_title('Archivio salute e benessere');
                echo '<!-- DEBUG Salute: ' . ($salute_page ? 'Trovata ID: ' . $salute_page->ID : 'NON TROVATA') . ' -->';
                if ($salute_page):
                ?>
                <a href="<?php echo get_permalink($salute_page); ?>" class="btn btn-link">
                    Vedi tutto <i data-lucide="arrow-right"></i>
                </a>
                <?php else: ?>
                <!-- DEBUG: Pagina Archivio salute e benessere non trovata -->
                <?php endif; ?>
            </div>
            
            <?php get_template_part('templates/parts/home/salute-list'); ?>
        </section>

    </div>
</div>

<?php get_footer(); ?>
