<?php
/**
 * Template Name: Archivio Salute e Benessere
 * Description: Template per visualizzare tutti gli articoli di Salute e Benessere
 */

get_header();
?>

<div class="content-wrapper">
    <div class="container">
        <h1>Tutte le notizie sulla Salute</h1>
        
        <?php
        // Query tutti gli articoli Salute e Benessere
        // NOTA: slug CPT troncato a 20 caratteri da WordPress
        $salute = new WP_Query(array(
            'post_type' => 'salute-e-benessere-l',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        // DEBUG: Mostra info query
        echo '<!-- DEBUG: Found posts: ' . $salute->found_posts . ' -->';
        echo '<!-- DEBUG: Post type: salute-e-benessere-l -->';
        
        if ($salute->have_posts()): ?>
            <div class="salute-list">
                <?php while ($salute->have_posts()): $salute->the_post(); 
                    $contenuto = get_field('contenuto');
                    $excerpt = $contenuto ? wp_trim_words(strip_tags($contenuto), 20) : '';
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
        <?php else: ?>
            <p class="no-content">Nessun contenuto disponibile.</p>
        <?php endif; ?>
        
    </div>
</div>

<?php get_footer(); ?>
