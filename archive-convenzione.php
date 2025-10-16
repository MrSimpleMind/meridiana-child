<?php
/**
 * Archive Template: Convenzioni
 * Loop di tutte le convenzioni attive
 */

get_header();
?>

<div class="content-wrapper">
    <div class="container">

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
</div>

<style>
.content-wrapper {
    padding-top: var(--space-6);
}

.archive-header {
    margin-bottom: var(--space-8);
    padding-bottom: var(--space-6);
    border-bottom: 1px solid var(--color-border-light);
}

.archive-title {
    font-size: var(--font-size-3xl);
    font-weight: var(--font-weight-bold);
    color: var(--color-text-primary);
    margin: 0 0 var(--space-2);
}

.archive-description {
    font-size: var(--font-size-base);
    color: var(--color-text-secondary);
    margin: 0;
}

.convenzioni-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-6);
    margin-top: var(--space-6);
}

@media (min-width: 768px) {
    .convenzioni-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: var(--space-6);
    }
}

@media (min-width: 1200px) {
    .convenzioni-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: var(--space-8);
    }
}

/* Card convenzione per archivio */
.convenzioni-grid .convenzione-card {
    flex: none;
    max-width: none;
    width: 100%;
}
</style>

<?php get_footer(); ?>
