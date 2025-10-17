<?php
/**
 * Card Articolo Universale
 * Usato per News/Comunicazioni e Salute e Benessere
 * Layout identico per entrambi i tipi
 */

// Setup variabili
$post_type = get_post_type();
$featured_image_id = get_post_thumbnail_id();
$featured_image_url = $featured_image_id ? wp_get_attachment_image_url($featured_image_id, 'medium') : '';

// Gestione categoria ed estratto in base al tipo
if ($post_type === 'salute-e-benessere-l') {
    // Salute e Benessere: NO categorie, estratto da campo ACF - 1 RIGA
    $categories = array();
    $contenuto = get_field('contenuto');
    $excerpt = $contenuto ? wp_trim_words(strip_tags($contenuto), 10) : ''; // 10 parole = circa 1 riga
    $default_icon = 'heart';
} else {
    // Post standard: categorie WordPress, estratto standard - 1 RIGA
    $categories = get_the_category();
    $excerpt = get_the_excerpt();
    $excerpt = $excerpt ? wp_trim_words($excerpt, 10) : ''; // 10 parole = circa 1 riga
    $default_icon = 'newspaper';
}

// Icona (override se passata)
$icon = isset($icon) ? $icon : $default_icon;
?>

<a href="<?php the_permalink(); ?>" class="article-card">
    <?php if ($featured_image_url): ?>
    <div class="article-card__image" style="background-image: url('<?php echo esc_url($featured_image_url); ?>');">
        <div class="article-card__overlay"></div>
    </div>
    <?php else: ?>
    <div class="article-card__placeholder">
        <i data-lucide="<?php echo esc_attr($icon); ?>"></i>
    </div>
    <?php endif; ?>
    
    <div class="article-card__content">
        <h3 class="article-card__title"><?php the_title(); ?></h3>
        
        <div class="article-card__meta">
            <span class="article-card__date">
                <i data-lucide="calendar"></i>
                <?php echo get_the_date('d/m/Y'); ?>
            </span>
            
            <?php if (!empty($categories)): ?>
            <span class="article-card__category">
                <i data-lucide="tag"></i>
                <?php echo esc_html($categories[0]->name); ?>
            </span>
            <?php endif; ?>
        </div>
        
        <?php if ($excerpt): ?>
        <p class="article-card__excerpt"><?php echo esc_html($excerpt); ?></p>
        <?php endif; ?>
    </div>
</a>
