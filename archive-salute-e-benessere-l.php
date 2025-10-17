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
            
            <!-- Header con Torna Indietro -->
            <div class="archive-header">
                <a href="<?php echo home_url('/'); ?>" class="back-link">
                    <i data-lucide="arrow-left"></i>
                    <span>Torna indietro</span>
                </a>
            </div>
            
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
