<?php
/**
 * Archive Template: UNIFICATO per Comunicazioni, Convenzioni, Salute e Benessere
 * 
 * ⚠️ IMPORTANTE - CSS CLASSES:
 * Questo template genera SEMPRE le stesse classi CSS (vedi sotto).
 * Lo SCSS _archive.scss DEVE usare questi selettori ESATTAMENTE:
 *
 * .archive-list (container wrapper)
 * .archive-item (singolo item - link)
 * .archive-item__image (immagine sinistra)
 * .archive-item__body (wrapper content + meta)
 * .archive-item__content (titolo + excerpt)
 * .archive-item__meta (data + categoria)
 * .archive-item__title
 * .archive-item__excerpt
 * .archive-item__date
 * .archive-item__category
 * .archive-item__placeholder (fallback se no image)
 *
 * Se lo SCSS usa classi DIVERSE, le regole CSS non vengono applicate!
 * 
 * @package Meridiana Child Theme
 */

get_header();

// Determina il tipo di CPT corrente
$post_type = get_post_type();
$search_input_id = 'archiveSearch';
$list_container_id = 'archiveList';

// Configurazione per ogni CPT
$archive_config = array(
    'post' => array(
        'label' => 'Comunicazioni',
        'search_placeholder' => 'Cerca notizie...',
        'no_results_text' => 'Nessuna notizia trovata'
    ),
    'convenzione' => array(
        'label' => 'Convenzioni',
        'search_placeholder' => 'Cerca convenzioni...',
        'no_results_text' => 'Nessuna convenzione trovata'
    ),
    'salute-e-benessere-l' => array(
        'label' => 'Salute e Benessere',
        'search_placeholder' => 'Cerca articoli...',
        'no_results_text' => 'Nessun articolo trovato'
    )
);

$current_config = isset($archive_config[$post_type]) ? $archive_config[$post_type] : $archive_config['post'];
?>

<div class="content-wrapper">
    <?php 
    get_template_part('templates/parts/navigation/mobile-bottom-nav');
    get_template_part('templates/parts/navigation/desktop-sidebar');
    ?>
    
    <main class="archive-page archive-<?php echo esc_attr($post_type); ?>-page">
        <div class="archive-container">
            
            <!-- Breadcrumb Navigation -->
            <?php meridiana_render_breadcrumb(); ?>
            
            <!-- Back Button -->
            <div class="page-header">
                <a href="<?php echo esc_url(meridiana_get_parent_url()); ?>" class="back-link">
                    <i data-lucide="arrow-left"></i>
                    <span><?php echo esc_html(meridiana_get_back_label()); ?></span>
                </a>
            </div>
            
            <!-- Search Box -->
            <div class="search-wrapper">
                <div class="search-field">
                    <i data-lucide="search"></i>
                    <input 
                        type="text" 
                        id="<?php echo esc_attr($search_input_id); ?>" 
                        class="search-input" 
                        placeholder="<?php echo esc_attr($current_config['search_placeholder']); ?>"
                        autocomplete="off">
                </div>
            </div>
            
            <!-- Results Count -->
            <div class="results-count">
                <span id="resultsCountText">Caricamento...</span>
            </div>
            
            <!-- Archive List - LISTA VERTICALE (non grid) -->
            <div id="<?php echo esc_attr($list_container_id); ?>">
                <!-- Popolato via JavaScript con classi corrette -->
            </div>
            
            <!-- No Results -->
            <div class="no-results" id="noResults" style="display: none;">
                <i data-lucide="inbox"></i>
                <p><?php echo esc_html($current_config['no_results_text']); ?></p>
            </div>
            
        </div>
    </main>
</div>

<script>
(function() {
    'use strict';
    
    const POST_TYPE = '<?php echo esc_js($post_type); ?>';
    
    // =========================================================================
    // DATI CARICATI DA PHP - Varia a seconda del CPT
    // =========================================================================
    const allItems = <?php 
        $items_data = array();
        
        if ($post_type === 'convenzione') {
            // QUERY CONVENZIONI
            $args = array(
                'post_type' => 'convenzione',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => 'convenzione_attiva',
                        'value' => '1',
                        'compare' => '='
                    )
                ),
                'orderby' => 'title',
                'order' => 'ASC'
            );
            
            $query = new WP_Query($args);
            
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    
                    $immagine_id = get_field('immagine_evidenza');
                    $immagine = $immagine_id ? wp_get_attachment_image_url($immagine_id, 'medium') : false;
                    $descrizione_raw = get_field('descrizione');
                    $descrizione = $descrizione_raw ? wp_trim_words(strip_tags($descrizione_raw), 30) : get_the_excerpt();
                    
                    $items_data[] = array(
                        'id' => get_the_ID(),
                        'title' => get_the_title(),
                        'excerpt' => $descrizione,
                        'permalink' => get_the_permalink(),
                        'date' => get_the_date('d M Y'),
                        'category' => 'Convenzione',
                        'image' => $immagine,
                    );
                }
                wp_reset_postdata();
            }
            
        } elseif ($post_type === 'salute-e-benessere-l') {
            // QUERY SALUTE E BENESSERE
            $args = array(
                'post_type' => 'salute-e-benessere-l',
                'posts_per_page' => -1,
                'orderby' => 'date',
                'order' => 'DESC'
            );
            
            $query = new WP_Query($args);
            
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    
                    $featured_image = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                    $excerpt = get_the_excerpt();
                    if (!$excerpt) {
                        $contenuto_raw = get_field('contenuto');
                        $excerpt = $contenuto_raw ? wp_trim_words(strip_tags($contenuto_raw), 30) : '';
                    }
                    $categories = get_the_category();
                    $category_name = !empty($categories) ? $categories[0]->name : 'Salute e Benessere';
                    
                    $items_data[] = array(
                        'id' => get_the_ID(),
                        'title' => get_the_title(),
                        'excerpt' => $excerpt,
                        'permalink' => get_the_permalink(),
                        'date' => get_the_date('d M Y'),
                        'category' => $category_name,
                        'image' => $featured_image,
                    );
                }
                wp_reset_postdata();
            }
            
        } else {
            // DEFAULT: COMUNICAZIONI (post standard)
            $args = array(
                'post_type' => 'post',
                'posts_per_page' => -1,
                'orderby' => 'date',
                'order' => 'DESC'
            );
            
            $query = new WP_Query($args);
            
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    
                    $title = get_the_title();
                    if (stripos($title, 'meme') !== false) {
                        continue;
                    }
                    
                    $featured_image = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                    $categories = get_the_category();
                    $category_name = !empty($categories) ? $categories[0]->name : 'Comunicazioni';
                    
                    $items_data[] = array(
                        'id' => get_the_ID(),
                        'title' => $title,
                        'excerpt' => get_the_excerpt(),
                        'permalink' => get_the_permalink(),
                        'date' => get_the_date('d M Y'),
                        'category' => $category_name,
                        'image' => $featured_image,
                    );
                }
                wp_reset_postdata();
            }
        }
        
        echo json_encode($items_data);
    ?>;
    
    // =========================================================================
    // DOM ELEMENTS
    // =========================================================================
    const searchInput = document.getElementById('<?php echo esc_js($search_input_id); ?>');
    const listContainer = document.getElementById('<?php echo esc_js($list_container_id); ?>');
    const noResults = document.getElementById('noResults');
    const resultsCountText = document.getElementById('resultsCountText');
    
    let filteredItems = [...allItems];
    
    // =========================================================================
    // RENDER - GENERA HTML CON CLASSI CSS CORRETTE
    // ⚠️ ATTENZIONE: Le classi CSS qui DEVONO corrispondere a quelle in _archive.scss
    // =========================================================================
    function renderItems(items) {
        if (items.length === 0) {
            listContainer.innerHTML = '';
            noResults.style.display = 'flex';
            resultsCountText.textContent = 'Nessun risultato';
            return;
        }
        
        noResults.style.display = 'none';
        resultsCountText.textContent = items.length === 1 ? '1 risultato' : `${items.length} risultati`;
        
        // CLASSI CSS GENERATE (devono corrispondere allo SCSS):
        listContainer.innerHTML = `
            <div class="archive-list">
                ${items.map(item => `
                    <a href="${item.permalink}" class="archive-item">
                        <!-- IMMAGINE SINISTRA (desktop) / SOPRA (mobile) -->
                        ${item.image ? `
                            <div class="archive-item__image" style="background-image: url('${item.image}');"></div>
                        ` : `
                            <div class="archive-item__placeholder">
                                <i data-lucide="image"></i>
                            </div>
                        `}
                        
                        <!-- BODY: Content + Meta -->
                        <div class="archive-item__body">
                            <!-- Contenuto (Titolo + Excerpt) -->
                            <div class="archive-item__content">
                                <h3 class="archive-item__title">${item.title}</h3>
                                <p class="archive-item__excerpt">${item.excerpt}</p>
                            </div>
                            
                            <!-- Meta (Data + Categoria) -->
                            <div class="archive-item__meta">
                                <span class="archive-item__date">
                                    <i data-lucide="calendar"></i>
                                    ${item.date}
                                </span>
                                <span class="archive-item__category">
                                    <i data-lucide="tag"></i>
                                    ${item.category}
                                </span>
                            </div>
                        </div>
                    </a>
                `).join('')}
            </div>
        `;
        
        // Re-init Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
    
    // =========================================================================
    // FILTER
    // =========================================================================
    function filterItems() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        filteredItems = allItems.filter(item => {
            if (!searchTerm) return true;
            
            return item.title.toLowerCase().includes(searchTerm) ||
                   item.excerpt.toLowerCase().includes(searchTerm) ||
                   item.category.toLowerCase().includes(searchTerm);
        });
        
        renderItems(filteredItems);
    }
    
    // =========================================================================
    // EVENT LISTENERS
    // =========================================================================
    searchInput.addEventListener('input', filterItems);
    
    // =========================================================================
    // INIT
    // =========================================================================
    renderItems(allItems);
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
})();
</script>

<?php get_footer(); ?>
