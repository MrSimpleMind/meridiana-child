<?php
/**
 * Archive Template: News/Comunicazioni
 * Loop di tutti gli articoli/news
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
    
    <main class="archive-news-page">
        <div class="archive-container">
            
            <!-- Header con Torna Indietro -->
            <div class="archive-header">
                <a href="<?php echo home_url('/'); ?>" class="back-link">
                    <i data-lucide="arrow-left"></i>
                    <span>Torna indietro</span>
                </a>
            </div>
            
            
            <?php if (have_posts()): ?>
            
            <!-- Grid News -->
            <div class="news-archive-grid">
                <?php while (have_posts()): the_post(); 
                    $featured_image_id = get_post_thumbnail_id();
                    $featured_image_url = $featured_image_id ? wp_get_attachment_image_url($featured_image_id, 'medium') : '';
                    $excerpt = get_the_excerpt();
                    $categories = get_the_category();
                ?>
                
                <a href="<?php the_permalink(); ?>" class="news-card">
                    <?php if ($featured_image_url): ?>
                    <div class="news-card__image" style="background-image: url('<?php echo esc_url($featured_image_url); ?>');">
                        <div class="news-card__overlay"></div>
                    </div>
                    <?php else: ?>
                    <div class="news-card__placeholder">
                        <i data-lucide="newspaper"></i>
                    </div>
                    <?php endif; ?>
                    
                    <div class="news-card__content">
                        <h3 class="news-card__title"><?php the_title(); ?></h3>
                        
                        <div class="news-card__meta">
                            <span class="news-card__date">
                                <i data-lucide="calendar"></i>
                                <?php echo get_the_date('d/m/Y'); ?>
                            </span>
                            
                            <?php if ($categories): ?>
                            <span class="news-card__category">
                                <i data-lucide="tag"></i>
                                <?php echo esc_html($categories[0]->name); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($excerpt): ?>
                        <p class="news-card__excerpt"><?php echo esc_html(wp_trim_words($excerpt, 20)); ?></p>
                        <?php endif; ?>
                    </div>
                </a>
                
                <?php endwhile; ?>
            </div>
            
            <!-- Pagination -->
            <?php 
            $pagination = paginate_links(array(
                'type' => 'array',
                'prev_text' => '<i data-lucide="chevron-left"></i> Precedente',
                'next_text' => 'Successivo <i data-lucide="chevron-right"></i>',
            ));
            
            if ($pagination): ?>
            <nav class="archive-pagination">
                <?php foreach ($pagination as $page): ?>
                    <?php echo $page; ?>
                <?php endforeach; ?>
            </nav>
            <?php endif; ?>
            
            <?php else: ?>
            <p class="no-content">Nessuna notizia disponibile al momento.</p>
            <?php endif; ?>
            
        </div>
    </main>
</div>

<?php
get_footer();
