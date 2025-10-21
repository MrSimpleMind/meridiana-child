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
    
    <?php if ($total_posts > 1): // Mostra hint e controlli solo se ci sono più elementi ?>
    
    <!-- Hint scroll (solo mobile) -->
    <div class="convenzioni-carousel__scroll-hint">
        <span>Scorri per vedere altre convenzioni</span>
        <i data-lucide="chevron-right"></i>
    </div>
    
    <!-- Controlli navigazione (solo desktop) -->
    <button class="carousel-control carousel-control--prev" id="carousel-prev" aria-label="Convenzione precedente">
        <i data-lucide="chevron-left"></i>
    </button>
    <button class="carousel-control carousel-control--next" id="carousel-next" aria-label="Convenzione successiva">
        <i data-lucide="chevron-right"></i>
    </button>
    
    <?php endif; ?>
</div>

<script>
// Gestione controlli navigazione carousel (solo desktop)
if (window.innerWidth >= 768) {
    const wrapper = document.querySelector('.convenzioni-carousel__wrapper');
    const prevBtn = document.getElementById('carousel-prev');
    const nextBtn = document.getElementById('carousel-next');
    const scrollHint = document.querySelector('.convenzioni-carousel__scroll-hint');
    
    if (wrapper && prevBtn && nextBtn) {
        // Calcola la larghezza di scroll (1 card alla volta)
        const getScrollAmount = () => {
            const card = wrapper.querySelector('.convenzione-card');
            if (card) {
                const cardWidth = card.offsetWidth;
                const gap = parseFloat(getComputedStyle(wrapper).gap) || 16;
                return cardWidth + gap;
            }
            return wrapper.offsetWidth / 3;
        };
        
        // Aggiorna stato pulsanti
        const updateButtons = () => {
            const scrollLeft = wrapper.scrollLeft;
            const maxScroll = wrapper.scrollWidth - wrapper.clientWidth;
            
            prevBtn.disabled = scrollLeft <= 0;
            nextBtn.disabled = scrollLeft >= maxScroll - 1;
        };
        
        // Event listeners
        prevBtn.addEventListener('click', () => {
            wrapper.scrollBy({ left: -getScrollAmount(), behavior: 'smooth' });
        });
        
        nextBtn.addEventListener('click', () => {
            wrapper.scrollBy({ left: getScrollAmount(), behavior: 'smooth' });
        });
        
        wrapper.addEventListener('scroll', updateButtons);
        
        // Stato iniziale
        updateButtons();
    }
} else {
    // Mobile: nascondi hint durante scroll, mostra quando smette
    const wrapper = document.querySelector('.convenzioni-carousel__wrapper');
    const scrollHint = document.querySelector('.convenzioni-carousel__scroll-hint');
    let scrollTimeout;
    
    if (wrapper && scrollHint) {
        wrapper.addEventListener('scroll', function() {
            // Nascondi hint durante lo scroll
            scrollHint.style.display = 'none';
            
            // Cancella timeout precedente
            clearTimeout(scrollTimeout);
            
            // Mostra hint di nuovo dopo 1 secondo dall'ultimo scroll
            scrollTimeout = setTimeout(() => {
                scrollHint.style.display = 'flex';
            }, 1000);
        });
    }
}
</script>
