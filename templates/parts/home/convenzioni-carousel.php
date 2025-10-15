<?php
/**
 * Home - Convenzioni Carousel
 * Mostra convenzioni attive in un carousel orizzontale con indicatori
 */

// Query: convenzioni attive (massimo 6 per non appesantire)
$convenzioni = new WP_Query(array(
    'post_type' => 'convenzione',
    'posts_per_page' => 6,
    'meta_query' => array(
        array(
            'key' => 'convenzione_attiva',
            'value' => '1',
            'compare' => '='
        )
    ),
    'orderby' => 'date',
    'order' => 'DESC'
));

if (!$convenzioni->have_posts()) {
    echo '<p class="no-content">Nessuna convenzione disponibile al momento.</p>';
    return;
}

$total_posts = $convenzioni->post_count;
?>

<div class="convenzioni-carousel">
    <div class="convenzioni-carousel__wrapper">
        <?php while ($convenzioni->have_posts()): $convenzioni->the_post(); 
            // CORRETTO: Usa il campo ACF 'immagine_evidenza' che ritorna ID
            $immagine_id = get_field('immagine_evidenza');
            $immagine_url = $immagine_id ? wp_get_attachment_image_url($immagine_id, 'medium') : '';
            
            // Descrizione dal campo ACF
            $descrizione_raw = get_field('descrizione');
            $descrizione = $descrizione_raw ? wp_trim_words(strip_tags($descrizione_raw), 15) : get_the_excerpt();
        ?>
        
        <a href="<?php the_permalink(); ?>" class="convenzione-card">
            <?php if ($immagine_url): ?>
            <div class="convenzione-card__image" style="background-image: url('<?php echo esc_url($immagine_url); ?>');">
                <div class="convenzione-card__overlay"></div>
            </div>
            <?php else: ?>
            <div class="convenzione-card__placeholder">
                <i data-lucide="tag"></i>
            </div>
            <?php endif; ?>
            
            <div class="convenzione-card__content">
                <h3 class="convenzione-card__title"><?php the_title(); ?></h3>
                <?php if ($descrizione): ?>
                <p class="convenzione-card__description"><?php echo esc_html($descrizione); ?></p>
                <?php endif; ?>
            </div>
        </a>
        
        <?php endwhile; wp_reset_postdata(); ?>
    </div>
    
    <?php if ($total_posts > 1): // Mostra hint e indicatori solo se ci sono più elementi ?>
    
    <!-- Hint scroll (solo mobile) -->
    <div class="convenzioni-carousel__scroll-hint">
        <span>Scorri per vedere altre convenzioni</span>
        <i data-lucide="chevron-right"></i>
    </div>
    
    <!-- Indicatori -->
    <div class="convenzioni-carousel__indicators">
        <?php for ($i = 0; $i < $total_posts; $i++): ?>
        <span class="carousel-indicator <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>"></span>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<script>
// Script per aggiornare gli indicatori durante lo scroll (solo mobile)
if (window.innerWidth < 768) {
    const wrapper = document.querySelector('.convenzioni-carousel__wrapper');
    const indicators = document.querySelectorAll('.carousel-indicator');
    const scrollHint = document.querySelector('.convenzioni-carousel__scroll-hint');
    
    if (wrapper && indicators.length > 0) {
        // Aggiorna indicatori durante scroll
        wrapper.addEventListener('scroll', function() {
            const scrollLeft = wrapper.scrollLeft;
            const cardWidth = 280 + 16; // Card width + gap
            const activeIndex = Math.round(scrollLeft / cardWidth);
            
            indicators.forEach((indicator, index) => {
                indicator.classList.toggle('active', index === activeIndex);
            });
            
            // Nascondi hint dopo primo scroll
            if (scrollLeft > 10 && scrollHint) {
                scrollHint.style.display = 'none';
            }
        });
    }
}
</script>
