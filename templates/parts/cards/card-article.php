<?php
/**
 * Card Component: Article/Salute & Benessere
 * Visualizza un singolo articolo in formato card
 * 
 * @package Meridiana Child
 */

$immagine_id = get_post_thumbnail_id();
$immagine_url = $immagine_id ? wp_get_attachment_image_url($immagine_id, 'large') : '';

// Recupera estratto o prime righe del contenuto
$contenuto = get_field('contenuto');
$excerpt = $contenuto ? wp_trim_words(strip_tags($contenuto), 30) : wp_trim_words(get_the_excerpt(), 30);
?>

<a href="<?php the_permalink(); ?>" class="salute-card">
    <?php if ($immagine_url): ?>
    <div class="salute-card__image" style="background-image: url('<?php echo esc_url($immagine_url); ?>');">
        <div class="salute-card__overlay"></div>
    </div>
    <?php else: ?>
    <div class="salute-card__placeholder">
        <i data-lucide="heart"></i>
    </div>
    <?php endif; ?>
    
    <div class="salute-card__content">
        <h3 class="salute-card__title"><?php the_title(); ?></h3>
        <?php if ($excerpt): ?>
        <p class="salute-card__excerpt"><?php echo esc_html($excerpt); ?></p>
        <?php endif; ?>
        
        <div class="salute-card__meta">
            <span class="salute-card__date"><?php echo get_the_date('d M Y'); ?></span>
        </div>
    </div>
</a>
