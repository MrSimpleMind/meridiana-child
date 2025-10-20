<?php
/**
 * Template: Single Comunicazioni/News
 * Visualizza dettaglio completo di una notizia o comunicazione
 * 
 * @package Meridiana Child
 */

if (!defined('ABSPATH')) exit;

get_header();
?>

<div class="content-wrapper">
    <?php 
    // Include navigation (mobile + desktop)
    get_template_part('templates/parts/navigation/mobile-bottom-nav');
    get_template_part('templates/parts/navigation/desktop-sidebar');
    ?>
    
    <main class="single-comunicazioni-page">
        <?php while (have_posts()): the_post(); 
            // Get fields
            $immagine_id = get_post_thumbnail_id();
            $immagine_url = $immagine_id ? wp_get_attachment_image_url($immagine_id, 'large') : '';
            $excerpt = get_the_excerpt();
            $categories = get_the_category();
        ?>
        
        <div class="single-container">
            <!-- Back Navigation -->
            <div class="back-link-wrapper">
                <a href="#" onclick="history.back(); return false;" class="back-link">
                    <i data-lucide="arrow-left"></i>
                    <span>Torna indietro</span>
                </a>
            </div>
            
            <!-- Header -->
            <header class="single-comunicazioni__header">
                <h1 class="single-comunicazioni__title"><?php the_title(); ?></h1>
                
                <!-- Meta Info -->
                <div class="single-comunicazioni__meta">
                    <span class="meta-item">
                        <i data-lucide="calendar"></i>
                        <span><?php echo get_the_date('j F Y'); ?></span>
                    </span>
                    
                    <?php if ($categories): ?>
                    <span class="meta-item">
                        <i data-lucide="tag"></i>
                        <span>
                            <?php 
                            $cat_names = array_map(function($cat) {
                                return $cat->name;
                            }, $categories);
                            echo esc_html(implode(', ', $cat_names));
                            ?>
                        </span>
                    </span>
                    <?php endif; ?>
                </div>
            </header>
            
            <!-- Featured Image -->
            <?php if ($immagine_url): ?>
            <div class="single-comunicazioni__featured-image">
                <img src="<?php echo esc_url($immagine_url); ?>" alt="<?php the_title_attribute(); ?>" class="single-comunicazioni__image">
            </div>
            <?php endif; ?>
            
            <!-- Main Content -->
            <article class="single-comunicazioni__content">
                
                <!-- Excerpt if available -->
                <?php if ($excerpt): ?>
                <div class="single-comunicazioni__excerpt">
                    <?php echo wp_kses_post($excerpt); ?>
                </div>
                <?php endif; ?>
                
                <!-- Full Content -->
                <div class="single-comunicazioni__body wysiwyg-content">
                    <?php the_content(); ?>
                </div>
                
            </article>
        </div>
        
        <?php endwhile; ?>
    </main>
</div>

<?php
get_footer();
