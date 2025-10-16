<?php
/**
 * Template Name: Archivio Convenzioni
 * Description: Template per visualizzare tutte le convenzioni attive
 */

get_header();
?>

<div class="content-wrapper">
    <div class="container">
        
        <?php
        // Query tutte le convenzioni attive
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
            <div class="convenzioni-grid">
                <?php while ($convenzioni->have_posts()): $convenzioni->the_post(); 
                    $immagine_id = get_post_thumbnail_id();
                    $immagine_url = $immagine_id ? wp_get_attachment_image_url($immagine_id, 'medium') : '';
                    $descrizione_raw = get_field('descrizione');
                    $descrizione = $descrizione_raw ? wp_trim_words(strip_tags($descrizione_raw), 20) : '';
                ?>
                
                <a href="<?php the_permalink(); ?>" class="convenzione-card">
                    <?php if ($immagine_url): ?>
                    <div class="convenzione-card__image">
                        <img src="<?php echo esc_url($immagine_url); ?>" alt="<?php the_title_attribute(); ?>">
                    </div>
                    <?php else: ?>
                    <div class="convenzione-card__placeholder">
                        <i data-lucide="tag" style="width: 48px; height: 48px; color: #9CA3AF;"></i>
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
</div>

<?php get_footer(); ?>
