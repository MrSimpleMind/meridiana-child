<?php
/**
 * Archive Template: Convenzioni
 * Loop di tutte le convenzioni attive
 */

get_header();
?>

<div class="content-wrapper">
    <?php 
    // Include navigation (mobile + desktop)
    get_template_part('templates/parts/navigation/mobile-bottom-nav');
    get_template_part('templates/parts/navigation/desktop-sidebar');
    ?>
    
    <main class="archive-page archive-convenzioni-page">
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
            
            <!-- Page Title REMOVED (breadcrumb is enough) -->
            
            <?php
            $convenzioni = new WP_Query(array(
                'post_type' => 'convenzione',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => 'convenzione_attiva',
                        'value' => '1',
                        'compare' => '='
                    )
                ),
                'orderby' => 'title',
                'order' => 'ASC'
            ));
            
            if ($convenzioni->have_posts()): ?>
                <!-- Grid Convenzioni -->
                <div class="convenzioni-grid">
                    <?php while ($convenzioni->have_posts()): $convenzioni->the_post(); 
                        // CORRETTO: USA CAMPO ACF 'immagine_evidenza' che ritorna ID
                        $immagine_id = get_field('immagine_evidenza');
                        $immagine_url = $immagine_id ? wp_get_attachment_image_url($immagine_id, 'medium') : '';
                        
                        $descrizione_raw = get_field('descrizione');
                        $descrizione = $descrizione_raw ? wp_trim_words(strip_tags($descrizione_raw), 20) : '';
                    ?>
                    
                    <a href="<?php the_permalink(); ?>" class="convenzione-card">
                        <?php if ($immagine_url): ?>
                        <div class="convenzione-card__image" style="background-image: url('<?php echo esc_url($immagine_url); ?>');">
                            <div class="convenzione-card__overlay"></div>
                        </div>
                        <?php else: ?>
                        <div class="convenzione-card__placeholder">
                            <i data-lucide="tag"></i>
                        </div>
                        <?php endif; ?>
                        
                        <div class="convenzione-card__content">
                            <h3 class="convenzione-card__title"><?php the_title(); ?></h3>
                            <?php if ($descrizione): ?>
                            <p class="convenzione-card__description"><?php echo esc_html($descrizione); ?></p>
                            <?php endif; ?>
                        </div>
                    </a>
                    
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            <?php else: ?>
                <p class="no-content">Nessuna convenzione disponibile al momento.</p>
            <?php endif; ?>
            
        </div>
    </main>
</div>

<?php get_footer(); ?>
