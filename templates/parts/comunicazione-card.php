<?php
/**
 * Template Parte: Card Comunicazione
 * 
 * Renderizza una singola comunicazione come card.
 * DESIGN SYSTEM COMPLIANT - Ottobre 2025
 * 
 * Stili:
 * - Featured image in 16:9 responsive
 * - Typography secondo hierarchy
 * - Colori brand e spacing system
 * - Mobile-first responsive
 * - WCAG 2.1 AA accessible
 * 
 * Usata sia nel template archivio che nella risposta AJAX
 */

if (!defined('ABSPATH')) {
    exit;
}

$post_id = get_the_ID();
$categories = get_the_category($post_id);
$featured_image = get_the_post_thumbnail_url($post_id, 'large');
$post_date = get_the_date('d/m/Y', $post_id);
$post_author = get_the_author();
$post_title = get_the_title();
$post_excerpt = get_the_excerpt();
$post_permalink = get_permalink();

// Se non c'è excerpt, genera dai primi 25 parole del contenuto
if (!$post_excerpt) {
    $content = wp_strip_all_tags(get_the_content());
    $post_excerpt = wp_trim_words($content, 25, '...');
}
?>

<article class="comunicazione-card" data-post-id="<?php echo esc_attr($post_id); ?>">
    
    <!-- Featured Image - 16:9 Responsive -->
    <div class="comunicazione-card__image-wrapper">
        <?php if ($featured_image): ?>
            <img 
                class="comunicazione-card__image"
                src="<?php echo esc_url($featured_image); ?>" 
                alt="<?php echo esc_attr($post_title); ?>"
                loading="lazy"
                width="400"
                height="225">
        <?php else: ?>
            <!-- Placeholder se manca featured image -->
            <div class="comunicazione-card__image-placeholder">
                <i data-lucide="file-text" class="comunicazione-card__placeholder-icon"></i>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Content Container -->
    <div class="comunicazione-card__content">
        
        <!-- Categories Badges -->
        <?php if ($categories): ?>
            <div class="comunicazione-card__categories" role="list">
                <?php foreach ($categories as $category): ?>
                    <span class="badge badge-info" role="listitem">
                        <?php echo esc_html($category->name); ?>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Title -->
        <h3 class="comunicazione-card__title">
            <a 
                href="<?php echo esc_url($post_permalink); ?>" 
                class="comunicazione-card__link"
                title="Leggi articolo: <?php echo esc_attr($post_title); ?>">
                <?php echo esc_html($post_title); ?>
            </a>
        </h3>
        
        <!-- Excerpt -->
        <p class="comunicazione-card__excerpt">
            <?php echo wp_kses_post($post_excerpt); ?>
        </p>
        
        <!-- Meta (Date & Author) -->
        <div class="comunicazione-card__meta">
            <time 
                datetime="<?php echo esc_attr(get_the_date('c')); ?>" 
                class="comunicazione-card__date">
                <i data-lucide="calendar" class="comunicazione-card__meta-icon"></i>
                <span><?php echo esc_html($post_date); ?></span>
            </time>
            
            <span class="comunicazione-card__author">
                <i data-lucide="user" class="comunicazione-card__meta-icon"></i>
                <span><?php echo esc_html($post_author); ?></span>
            </span>
        </div>
        
        <!-- Read More Button -->
        <a 
            href="<?php echo esc_url($post_permalink); ?>" 
            class="comunicazione-card__read-more btn btn-secondary btn-sm"
            aria-label="Leggi articolo completo: <?php echo esc_attr($post_title); ?>">
            <span>Leggi di più</span>
            <i data-lucide="arrow-right"></i>
        </a>
        
    </div>
    
</article>
