<?php
/**
 * Archive Template: UNIFICATO per Comunicazioni, Convenzioni, Salute e Benessere
 *
 * Genera HTML con classi CSS corrette per styling.
 * Include search bar, filter toggle, e filtri collassabili.
 *
 * @package Meridiana Child Theme
 */

get_header();

// Determina il tipo di CPT corrente
$post_type = get_post_type();
$search_input_id = 'archiveSearch';
$filter_toggle_id = 'archiveFilterToggle';
$filters_panel_id = 'archiveFiltersPanel';
$list_container_id = 'archiveList';

// Configurazione per ogni CPT
$archive_config = array(
    'post' => array(
        'label' => 'Comunicazioni',
        'search_placeholder' => 'Barra di ricerca',
        'no_results_text' => 'Nessuna notizia trovata'
    ),
    'convenzione' => array(
        'label' => 'Convenzioni',
        'search_placeholder' => 'Barra di ricerca',
        'no_results_text' => 'Nessuna convenzione trovata'
    ),
    'salute-e-benessere-l' => array(
        'label' => 'Salute e Benessere',
        'search_placeholder' => 'Barra di ricerca',
        'no_results_text' => 'Nessun articolo trovato'
    )
);

$current_config = isset($archive_config[$post_type]) ? $archive_config[$post_type] : $archive_config['post'];

// Carica categorie uniche per questo CPT
$categories = get_terms(array(
    'taxonomy' => 'category',
    'hide_empty' => false,
    'number' => 0,
));
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
            <div class="back-button-wrapper">
                <a href="<?php echo esc_url(meridiana_get_parent_url()); ?>" class="back-button">
                    <i data-lucide="arrow-left"></i>
                    <span><?php echo esc_html(meridiana_get_back_label()); ?></span>
                </a>
            </div>

            <!-- SEARCH BAR + TOGGLE FILTRI -->
            <div class="docs-search-container">
                <div class="docs-search-wrapper">
                    <div class="search-input-group">
                        <i data-lucide="search"></i>
                        <input
                            type="text"
                            id="<?php echo esc_attr($search_input_id); ?>"
                            class="docs-search-input"
                            placeholder="<?php echo esc_attr($current_config['search_placeholder']); ?>"
                            aria-label="Cerca contenuti"
                            autocomplete="off">
                        <button id="archiveSearchClear" class="docs-search-clear" aria-label="Pulisci ricerca"><i data-lucide="x"></i></button>
                    </div>
                </div>
                <button
                    id="<?php echo esc_attr($filter_toggle_id); ?>"
                    class="docs-filter-toggle"
                    aria-expanded="false"
                    aria-controls="<?php echo esc_attr($filters_panel_id); ?>"
                    aria-label="Mostra/nascondi filtri">
                    <i data-lucide="filter"></i>
                    <span class="docs-filter-toggle__text">Filtri</span>
                </button>
            </div>

            <!-- FILTRI COLLASSABILI PANEL -->
            <div
                id="<?php echo esc_attr($filters_panel_id); ?>"
                class="docs-filters-panel"
                aria-hidden="true">

                <!-- FILTRI TIPO/STATO - Diversi per ogni CPT -->
                <div class="docs-type-filters">
                    <?php if ($post_type === 'convenzione'): ?>
                        <label class="docs-type-label">Stato:</label>
                        <div class="docs-type-buttons">
                            <button class="docs-type-btn docs-type-btn--active" data-status="all">Tutti</button>
                            <button class="docs-type-btn" data-status="active">Attive</button>
                            <button class="docs-type-btn" data-status="expired">Scadute</button>
                        </div>
                    <?php else: ?>
                        <label class="docs-type-label">Stato:</label>
                        <div class="docs-type-buttons">
                            <button class="docs-type-btn docs-type-btn--active" data-status="all">Tutti</button>
                            <button class="docs-type-btn" data-status="publish">Pubblicati</button>
                            <button class="docs-type-btn" data-status="draft">Bozze</button>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- FILTRI TASSONOMIE -->
                <div class="docs-filters">
                    <?php if (!empty($categories) && !is_wp_error($categories)): ?>
                    <div class="filter-group">
                        <label for="archiveFilterCategory" class="filter-group__label">Categoria</label>
                        <select id="archiveFilterCategory" class="docs-filter-select">
                            <option value="" selected>Tutte</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo esc_attr($cat->term_id); ?>"><?php echo esc_html($cat->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Archive List - LISTA VERTICALE (non grid) -->
            <div id="<?php echo esc_attr($list_container_id); ?>">
                <!-- Popolato via JavaScript con classi corrette -->
            </div>
            
            <!-- No Results -->
            <div class="docs-no-results" id="noResults" style="display: none;">
                <i data-lucide="inbox"></i>
                <p><?php echo esc_html($current_config['no_results_text']); ?></p>
            </div>

        </div>
    </main>
</div>

<!-- Fuse.js Library -->
<script src="https://cdn.jsdelivr.net/npm/fuse.js@7.0.0/dist/fuse.min.js"></script>

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
            // QUERY CONVENZIONI - Senza filtro meta_query per mostrare entrambe attive/scadute
            $args = array(
                'post_type' => 'convenzione',
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC'
            );

            $query = new WP_Query($args);

            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();

                    $is_active = (bool) get_field('convenzione_attiva', get_the_ID());
                    $immagine_id = get_field('immagine_evidenza');
                    $immagine = $immagine_id ? wp_get_attachment_image_url($immagine_id, 'medium') : false;
                    $descrizione_raw = get_field('descrizione');
                    $descrizione = $descrizione_raw ? wp_trim_words(strip_tags($descrizione_raw), 30) : get_the_excerpt();
                    $categories = get_the_category();
                    $category_ids = !empty($categories) ? implode(',', wp_list_pluck($categories, 'term_id')) : '';
                    $category_name = !empty($categories) ? $categories[0]->name : 'Convenzione';

                    $items_data[] = array(
                        'id' => get_the_ID(),
                        'title' => strtolower(get_the_title()),
                        'titleDisplay' => get_the_title(),
                        'excerpt' => $descrizione,
                        'permalink' => get_the_permalink(),
                        'date' => get_the_date('d M Y'),
                        'category' => $category_name,
                        'categoryIds' => $category_ids,
                        'status' => $is_active ? 'active' : 'expired',
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
                    $post_status = get_post_status(get_the_ID());
                    $categories = get_the_category();
                    $category_ids = !empty($categories) ? implode(',', wp_list_pluck($categories, 'term_id')) : '';
                    $category_name = !empty($categories) ? $categories[0]->name : 'Salute e Benessere';

                    $items_data[] = array(
                        'id' => get_the_ID(),
                        'title' => strtolower(get_the_title()),
                        'titleDisplay' => get_the_title(),
                        'excerpt' => $excerpt,
                        'permalink' => get_the_permalink(),
                        'date' => get_the_date('d M Y'),
                        'category' => $category_name,
                        'categoryIds' => $category_ids,
                        'status' => $post_status,
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
                    $post_status = get_post_status(get_the_ID());
                    $categories = get_the_category();
                    $category_ids = !empty($categories) ? implode(',', wp_list_pluck($categories, 'term_id')) : '';
                    $category_name = !empty($categories) ? $categories[0]->name : 'Comunicazioni';

                    $items_data[] = array(
                        'id' => get_the_ID(),
                        'title' => strtolower($title),
                        'titleDisplay' => $title,
                        'excerpt' => get_the_excerpt(),
                        'permalink' => get_the_permalink(),
                        'date' => get_the_date('d M Y'),
                        'category' => $category_name,
                        'categoryIds' => $category_ids,
                        'status' => $post_status,
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
    const searchClear = document.getElementById('archiveSearchClear');
    const filterToggle = document.getElementById('<?php echo esc_js($filter_toggle_id); ?>');
    const filtersPanel = document.getElementById('<?php echo esc_js($filters_panel_id); ?>');
    const filterCategory = document.getElementById('archiveFilterCategory');
    const statusButtons = document.querySelectorAll('#<?php echo esc_js($filters_panel_id); ?> [data-status]');
    const listContainer = document.getElementById('<?php echo esc_js($list_container_id); ?>');
    const noResults = document.getElementById('noResults');

    let selectedStatus = 'all';

    // =========================================================================
    // TOGGLE FILTRI PANEL
    // =========================================================================
    if (filterToggle && filtersPanel) {
        filterToggle.addEventListener('click', function(e) {
            e.preventDefault();
            const isExpanded = filterToggle.getAttribute('aria-expanded') === 'true';
            filterToggle.setAttribute('aria-expanded', !isExpanded);
            filtersPanel.setAttribute('aria-hidden', isExpanded);
            filtersPanel.classList.toggle('docs-filters-panel--open');
        });
    }

    // =========================================================================
    // PREPARAZIONE DATI PER FUSE.JS
    // =========================================================================
    const itemsData = allItems.map(item => ({
        id: item.id,
        title: item.title,
        titleDisplay: item.titleDisplay,
        excerpt: item.excerpt,
        status: item.status,
        categoryIds: item.categoryIds ? item.categoryIds.split(',') : [],
        element: {
            id: item.id,
            title: item.titleDisplay,
            excerpt: item.excerpt,
            permalink: item.permalink,
            date: item.date,
            category: item.category,
            image: item.image
        }
    }));

    // =========================================================================
    // FUSE.JS CONFIGURATION
    // =========================================================================
    const fuse = new Fuse(itemsData, {
        keys: ['title', 'excerpt'],
        threshold: 0.4,
        minMatchCharLength: 2,
        includeScore: true,
        ignoreLocation: true
    });

    // =========================================================================
    // RENDER - GENERA HTML CON CLASSI CSS CORRETTE
    // =========================================================================
    function renderItems(items) {
        if (items.length === 0) {
            listContainer.innerHTML = '';
            noResults.style.display = 'flex';
            return;
        }

        noResults.style.display = 'none';

        listContainer.innerHTML = `
            <div class="archive-list">
                ${items.map(item => `
                    <a href="${item.element.permalink}" class="archive-item">
                        ${item.element.image ? `
                            <div class="archive-item__image" style="background-image: url('${item.element.image}');"></div>
                        ` : `
                            <div class="archive-item__placeholder">
                                <i data-lucide="image"></i>
                            </div>
                        `}
                        <div class="archive-item__body">
                            <div class="archive-item__content">
                                <h3 class="archive-item__title">${item.element.title}</h3>
                                <p class="archive-item__excerpt">${item.element.excerpt}</p>
                            </div>
                            <div class="archive-item__meta">
                                <span class="archive-item__date">
                                    <i data-lucide="calendar"></i>
                                    ${item.element.date}
                                </span>
                                <span class="archive-item__category">
                                    <i data-lucide="tag"></i>
                                    ${item.element.category}
                                </span>
                            </div>
                        </div>
                    </a>
                `).join('')}
            </div>
        `;

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    // =========================================================================
    // FILTER LOGIC
    // =========================================================================
    function filterItems() {
        const searchTerm = searchInput ? searchInput.value.trim() : '';
        const categoryFilter = filterCategory ? filterCategory.value : '';
        let filteredItems = [...itemsData];

        // Filtro Stato
        if (selectedStatus !== 'all') {
            filteredItems = filteredItems.filter(item => item.status === selectedStatus);
        }

        // Ricerca Fuse.js
        if (searchTerm && searchTerm.length >= 2) {
            const fuseResults = fuse.search(searchTerm);
            const fuseIds = fuseResults.map(result => result.item.id);
            filteredItems = filteredItems.filter(item => fuseIds.includes(item.id));
        }

        // Filtro Categoria
        if (categoryFilter) {
            filteredItems = filteredItems.filter(item => item.categoryIds.includes(categoryFilter));
        }

        renderItems(filteredItems);
    }

    // =========================================================================
    // EVENT LISTENERS
    // =========================================================================
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            if (searchClear) searchClear.style.display = this.value ? 'block' : 'none';
            filterItems();
        });
    }

    if (searchClear) {
        searchClear.addEventListener('click', function(e) {
            e.preventDefault();
            if (searchInput) searchInput.value = '';
            searchClear.style.display = 'none';
            filterItems();
            if (searchInput) searchInput.focus();
        });
    }

    if (filterCategory) filterCategory.addEventListener('change', filterItems);

    statusButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            statusButtons.forEach(b => b.classList.remove('docs-type-btn--active'));
            this.classList.add('docs-type-btn--active');
            selectedStatus = this.dataset.status;
            filterItems();
        });
    });

    // =========================================================================
    // INIT
    // =========================================================================
    renderItems(itemsData);
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
})();
</script>

<?php get_footer(); ?>
