<?php
/**
 * Template: Single Post (News/Comunicazioni)
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
    
    <main class="single-news-page">
        <?php while (have_posts()): the_post(); ?>
        
        <div class="single-container">
            <!-- Header con Torna Indietro -->
            <div class="single-header">
                <a href="#" onclick="history.back(); return false;" class="back-link">
                    <i data-lucide="arrow-left"></i>
                    <span>Torna indietro</span>
                </a>
            </div>
            
            <!-- Content -->
            <article class="single-content">
                
                <!-- Titolo -->
                <h1 class="single-title"><?php the_title(); ?></h1>
                
                <!-- Meta Info -->
                <div class="news-meta">
                    <span class="news-meta__item">
                        <i data-lucide="calendar"></i>
                        <?php echo get_the_date('d/m/Y'); ?>
                    </span>
                    
                    <?php 
                    $categories = get_the_category();
                    if ($categories): ?>
                    <span class="news-meta__item">
                        <i data-lucide="tag"></i>
                        <?php 
                        $cat_names = array_map(function($cat) {
                            return $cat->name;
                        }, $categories);
                        echo esc_html(implode(', ', $cat_names));
                        ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <!-- Contenuto -->
                <div class="single-body wysiwyg-content">
                    <?php the_content(); ?>
                </div>
                
                <!-- Call to Action (opzionale con custom field ACF) -->
                <?php 
                $cta_text = get_field('cta_text');
                $cta_url = get_field('cta_url');
                
                if ($cta_text && $cta_url): ?>
                <div class="cta-box">
                    <a href="<?php echo esc_url($cta_url); ?>" class="btn btn--primary btn--large" target="_blank" rel="noopener">
                        <?php echo esc_html($cta_text); ?>
                        <i data-lucide="arrow-right"></i>
                    </a>
                </div>
                <?php endif; ?>
                
            </article>
        </div>
        
        <?php endwhile; ?>
    </main>
</div>

<?php
get_footer();
