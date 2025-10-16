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
    
    <main class="archive-salute-page">
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
                <!-- Grid Salute -->
                <div class="salute-archive-grid">
                    <?php while ($salute->have_posts()): $salute->the_post(); 
                        $featured_image_id = get_post_thumbnail_id();
                        $featured_image_url = $featured_image_id ? wp_get_attachment_image_url($featured_image_id, 'medium') : '';
                        $contenuto = get_field('contenuto');
                        $excerpt = $contenuto ? wp_trim_words(strip_tags($contenuto), 20) : '';
                        $categories = get_the_category();
                    ?>
                    
                    <a href="<?php the_permalink(); ?>" class="salute-card">
                        <?php if ($featured_image_url): ?>
                        <div class="salute-card__image" style="background-image: url('<?php echo esc_url($featured_image_url); ?>');">
                            <div class="salute-card__overlay"></div>
                        </div>
                        <?php else: ?>
                        <div class="salute-card__placeholder">
                            <i data-lucide="heart"></i>
                        </div>
                        <?php endif; ?>
                        
                        <div class="salute-card__content">
                            <h3 class="salute-card__title"><?php the_title(); ?></h3>
                            
                            <div class="salute-card__meta">
                                <span class="salute-card__date">
                                    <i data-lucide="calendar"></i>
                                    <?php echo get_the_date('d/m/Y'); ?>
                                </span>
                                
                                <?php if ($categories): ?>
                                <span class="salute-card__category">
                                    <i data-lucide="tag"></i>
                                    <?php echo esc_html($categories[0]->name); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($excerpt): ?>
                            <p class="salute-card__excerpt"><?php echo esc_html($excerpt); ?></p>
                            <?php endif; ?>
                        </div>
                    </a>
                    
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            <?php else: ?>
                <p class="no-content">Nessun contenuto disponibile.</p>
            <?php endif; ?>
            
        </div>
    </main>
</div>

<?php get_footer(); ?>
