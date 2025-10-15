<?php
/**
 * Home - Salute e Benessere
 * Mostra gli ultimi 3 articoli di salute e benessere
 */

// Query: Ultimi 3 articoli salute-e-benessere-l
$salute = new WP_Query(array(
    'post_type' => 'salute-e-benessere-l',
    'posts_per_page' => 3,
    'orderby' => 'date',
    'order' => 'DESC'
));

if (!$salute->have_posts()) {
    echo '<p class="no-content">Nessun contenuto disponibile.</p>';
    return;
}
?>

<div class="salute-list">
    <?php while ($salute->have_posts()): $salute->the_post(); 
        // Usa il campo ACF contenuto
        $contenuto = get_field('contenuto');
        $excerpt = $contenuto ? wp_trim_words(strip_tags($contenuto), 12) : '';
    ?>
    
    <a href="<?php the_permalink(); ?>" class="salute-item">
        <div class="salute-item__content">
            <h3 class="salute-item__title"><?php the_title(); ?></h3>
            <?php if ($excerpt): ?>
            <p class="salute-item__excerpt"><?php echo esc_html($excerpt); ?></p>
            <?php endif; ?>
        </div>
        <div class="salute-item__arrow">
            <i data-lucide="chevron-right"></i>
        </div>
    </a>
    
    <?php endwhile; wp_reset_postdata(); ?>
</div>
