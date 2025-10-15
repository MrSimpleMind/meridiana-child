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
                <div class="user-avatar" onclick="openUserProfileModal()" style="cursor: pointer;" role="button" tabindex="0" aria-label="Apri profilo utente">
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
                $convenzioni_link = get_post_type_archive_link('convenzione');
                if ($convenzioni_link):
                ?>
                <a href="<?php echo esc_url($convenzioni_link); ?>" class="btn btn-link">
                    Vedi tutto <i data-lucide="arrow-right"></i>
                </a>
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
                $salute_link = get_post_type_archive_link('salute-e-benessere-l');
                if ($salute_link):
                ?>
                <a href="<?php echo esc_url($salute_link); ?>" class="btn btn-link">
                    Vedi tutto <i data-lucide="arrow-right"></i>
                </a>
                <?php endif; ?>
            </div>
            
            <?php get_template_part('templates/parts/home/salute-list'); ?>
        </section>

    </div>
</div>

<?php get_footer(); ?>
