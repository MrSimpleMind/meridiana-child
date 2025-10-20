<?php
/**
 * Archive Template: Salute e Benessere
 * Loop di tutti gli articoli
 */

get_header();
?>

<div class="content-wrapper">
    <?php 
    // Include navigation (mobile + desktop)
    get_template_part('templates/parts/navigation/mobile-bottom-nav');
    get_template_part('templates/parts/navigation/desktop-sidebar');
    ?>
    
    <main class="archive-page archive-salute-page">
        <div class="archive-container">
            
            <!-- Breadcrumb Navigation -->
            <?php meridiana_render_breadcrumb(); ?>
            
            <!-- Back Navigation -->
            <div class="back-link-wrapper">
                <a href="<?php echo esc_url(meridiana_get_parent_url()); ?>" class="back-link">
                    <i data-lucide="arrow-left"></i>
                    <span><?php echo esc_html(meridiana_get_back_label()); ?></span>
                </a>
            </div>
            
            <!-- Page Title -->
            <h1 class="archive-page__title">Salute e Benessere</h1>
            
            <?php
            $salute = new WP_Query(array(
                'post_type' => 'salute-e-benessere-l',
                'posts_per_page' => -1,
                'orderby' => 'date',
                'order' => 'DESC'
            ));
            
            if ($salute->have_posts()): ?>
                <!-- Grid Articoli -->
                <div class="articles-grid">
                    <?php while ($salute->have_posts()): $salute->the_post(); ?>
                        <?php get_template_part('templates/parts/cards/card-article'); ?>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            <?php else: ?>
                <p class="no-content">Nessun contenuto disponibile.</p>
            <?php endif; ?>
            
        </div>
    </main>
</div>

<?php get_footer(); ?>
