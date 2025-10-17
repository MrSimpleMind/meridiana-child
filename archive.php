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
    
    <main class="archive-page archive-news-page">
        <div class="archive-container">
            
            <!-- Header con Torna Indietro -->
            <div class="archive-header">
                <a href="<?php echo home_url('/'); ?>" class="back-link">
                    <i data-lucide="arrow-left"></i>
                    <span>Torna indietro</span>
                </a>
            </div>
            
            <?php if (have_posts()): ?>
            
            <!-- Grid Articoli -->
            <div class="articles-grid">
                <?php while (have_posts()): the_post(); ?>
                    <?php get_template_part('templates/parts/cards/card-article'); ?>
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

<?php get_footer(); ?>
