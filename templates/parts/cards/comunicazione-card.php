<?php
/**
 * Card Component: Comunicazione
 * Visualizza una comunicazione/news in formato card
 * 
 * @package Meridiana Child
 */

$immagine_url = get_the_post_thumbnail_url(get_the_ID(), 'large');
$excerpt = wp_trim_words(get_the_excerpt(), 25);
$categories = get_the_category();
$category_name = !empty($categories) ? $categories[0]->name : '';
?>

<a href="<?php the_permalink(); ?>" class="comunicazione-card" data-post-id="<?php echo get_the_ID(); ?>">
    
    <?php if ($immagine_url): ?>
    <div class="comunicazione-card__image" style="background-image: url('<?php echo esc_url($immagine_url); ?>');">
        <div class="comunicazione-card__overlay"></div>
        <?php if ($category_name): ?>
            <div class="comunicazione-card__badge"><?php echo esc_html($category_name); ?></div>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="comunicazione-card__placeholder">
        <i data-lucide="newspaper"></i>
    </div>
    <?php endif; ?>
    
    <div class="comunicazione-card__content">
        <h3 class="comunicazione-card__title"><?php the_title(); ?></h3>
        
        <?php if ($excerpt): ?>
        <p class="comunicazione-card__excerpt"><?php echo esc_html($excerpt); ?></p>
        <?php endif; ?>
        
        <div class="comunicazione-card__meta">
            <span class="comunicazione-card__date">
                <i data-lucide="calendar"></i>
                <?php echo get_the_date('d M Y'); ?>
            </span>
            <?php if (!$immagine_url && $category_name): ?>
            <span class="comunicazione-card__category">
                <i data-lucide="tag"></i>
                <?php echo esc_html($category_name); ?>
            </span>
            <?php endif; ?>
        </div>
    </div>
    
</a>
