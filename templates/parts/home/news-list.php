<?php
/**
 * Home - Ultime Notizie
 * Mostra le ultime 3 comunicazioni (Post standard)
 */

// Query: Ultime 3 news
$news = new WP_Query(array(
    'post_type' => 'post',
    'posts_per_page' => 3,
    'orderby' => 'date',
    'order' => 'DESC'
));

if (!$news->have_posts()) {
    echo '<p class="no-content">Nessuna notizia disponibile.</p>';
    return;
}
?>

<div class="news-list">
    <?php while ($news->have_posts()): $news->the_post(); ?>
    
    <a href="<?php the_permalink(); ?>" class="news-item">
        <div class="news-item__content">
            <h3 class="news-item__title"><?php the_title(); ?></h3>
            <p class="news-item__excerpt"><?php echo wp_trim_words(get_the_excerpt(), 12); ?></p>
        </div>
        <div class="news-item__arrow">
            <i data-lucide="chevron-right"></i>
        </div>
    </a>
    
    <?php endwhile; wp_reset_postdata(); ?>
</div>
