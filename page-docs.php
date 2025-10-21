<?php
/*
Template Name: Documentazione
*/

get_header();

// Query tutti i protocolli e moduli
$args = array(
    'post_type' => array('protocollo', 'modulo'),
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
);

$query = new WP_Query($args);
$documents = $query->posts;

// Ottenere tutti i termini per i filtri
$profili = get_terms(array(
    'taxonomy' => 'profili_professionali',
    'hide_empty' => false,
));

$udo = get_terms(array(
    'taxonomy' => 'unita_offerta',
    'hide_empty' => false,
));
?>

<div class="content-wrapper documentazione-page">
    <div class="documentazione-container">
        
        <!-- BACK BUTTON -->
        <?php meridiana_render_back_button(); ?>

        <!-- TITOLO -->
        <h1 class="docs-page-title">Documentazione</h1>

        <!-- SEARCH BAR + FILTRI -->
        <div class="docs-search-filters">
            <!-- Search -->
            <div class="docs-search-wrapper">
                <div class="search-input-group">
                    <i data-lucide="search"></i>
                    <input 
                        type="text" 
                        id="docs-search" 
                        class="docs-search-input" 
                        placeholder="Barra di ricerca"
                        aria-label="Cerca documenti"
                    >
                    <button id="docs-search-clear" class="docs-search-clear" style="display: none;" aria-label="Pulisci ricerca">
                        <i data-lucide="x"></i>
                    </button>
                </div>
            </div>

            <!-- Filtri -->
            <div class="docs-filters">
                <!-- Filter: Profilo Professionale -->
                <?php if (!empty($profili) && !is_wp_error($profili)): ?>
                <div class="filter-group">
                    <select id="filter-profilo" class="docs-filter-select">
                        <option value="">Profilo Professionale</option>
                        <?php foreach ($profili as $term): ?>
                        <option value="<?php echo esc_attr($term->term_id); ?>">
                            <?php echo esc_html($term->name); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <!-- Filter: Unità d'Offerta -->
                <?php if (!empty($udo) && !is_wp_error($udo)): ?>
                <div class="filter-group">
                    <select id="filter-udo" class="docs-filter-select">
                        <option value="">Unità d'offerta</option>
                        <?php foreach ($udo as $term): ?>
                        <option value="<?php echo esc_attr($term->term_id); ?>">
                            <?php echo esc_html($term->name); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- DOCUMENTS LIST -->
        <div id="docs-list" class="docs-list">
            <?php if (!empty($documents)): ?>
                <?php foreach ($documents as $doc): ?>
                    <?php 
                    $type = get_post_type($doc->ID);
                    $type_label = ($type === 'protocollo') ? 'Protocollo' : 'Modulo';
                    $type_short = ($type === 'protocollo') ? 'P' : 'M';
                    
                    $riassunto = get_field('riassunto', $doc->ID);
                    $profilo_terms = get_the_terms($doc->ID, 'profili_professionali');
                    $udo_terms = get_the_terms($doc->ID, 'unita_offerta');
                    
                    // Featured image per il thumbnail
                    $thumbnail_url = get_the_post_thumbnail_url($doc->ID, 'thumbnail');
                    if (!$thumbnail_url) {
                        $thumbnail_url = false;
                    }
                    ?>

                    <article class="docs-item" 
                             data-post-id="<?php echo esc_attr($doc->ID); ?>" 
                             data-post-type="<?php echo esc_attr($type); ?>" 
                             data-title="<?php echo esc_attr(strtolower($doc->post_title)); ?>">
                        
                        <!-- THUMBNAIL / IMMAGINE -->
                        <div class="docs-item__thumbnail">
                            <?php if ($thumbnail_url): ?>
                                <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr($doc->post_title); ?>">
                            <?php else: ?>
                                <div class="docs-item__placeholder">
                                    <i data-lucide="file-text"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- CONTENT -->
                        <div class="docs-item__content">
                            <!-- Badge Tipo (alto a sinistra del contenuto) -->
                            <div class="docs-item__badge">
                                <span class="badge badge-<?php echo ($type === 'protocollo') ? 'blue' : 'green'; ?>">
                                    <?php echo $type_short; ?>
                                </span>
                            </div>
                            
                            <h3 class="docs-item__title">
                                <?php echo esc_html($doc->post_title); ?>
                            </h3>

                            <?php if ($riassunto): ?>
                            <p class="docs-item__description">
                                <?php echo esc_html(wp_trim_words($riassunto, 15)); ?>
                            </p>
                            <?php endif; ?>

                            <!-- META: Profilo + UDO -->
                            <div class="docs-item__meta">
                                <?php if (!empty($profilo_terms) && !is_wp_error($profilo_terms)): ?>
                                    <span class="docs-meta-tag">
                                        <strong>Profilo:</strong> <?php echo esc_html($profilo_terms[0]->name); ?>
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($udo_terms) && !is_wp_error($udo_terms)): ?>
                                    <span class="docs-meta-tag">
                                        <strong>UDO:</strong> <?php echo esc_html($udo_terms[0]->name); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Hidden Data for Fuse.js -->
                            <div style="display: none;">
                                <?php if (!empty($profilo_terms) && !is_wp_error($profilo_terms)): ?>
                                    <?php foreach ($profilo_terms as $term): ?>
                                        <span class="docs-taxonomy-data" data-taxonomy="profilo" data-term-id="<?php echo esc_attr($term->term_id); ?>"></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <?php if (!empty($udo_terms) && !is_wp_error($udo_terms)): ?>
                                    <?php foreach ($udo_terms as $term): ?>
                                        <span class="docs-taxonomy-data" data-taxonomy="udo" data-term-id="<?php echo esc_attr($term->term_id); ?>"></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- CTA BUTTON -->
                        <div class="docs-item__action">
                            <a href="<?php echo esc_url(get_permalink($doc->ID)); ?>" class="btn btn-primary">
                                <i data-lucide="eye"></i>
                                Visualizza
                            </a>
                        </div>

                    </article>

                <?php endforeach; ?>
            <?php else: ?>
                <div class="docs-no-results">
                    <i data-lucide="inbox"></i>
                    <p>Nessun documento trovato</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- Fuse.js Library -->
<script src="https://cdn.jsdelivr.net/npm/fuse.js@7.0.0/dist/fuse.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('docs-search');
    const searchClear = document.getElementById('docs-search-clear');
    const filterProfilo = document.getElementById('filter-profilo');
    const filterUdo = document.getElementById('filter-udo');
    const docsList = document.getElementById('docs-list');
    const docItems = docsList.querySelectorAll('.docs-item');

    // Preparazione dati per Fuse.js
    const documentsData = Array.from(docItems).map(item => {
        const taxonomySpans = item.querySelectorAll('.docs-taxonomy-data');
        
        const profiloIds = Array.from(taxonomySpans)
            .filter(span => span.dataset.taxonomy === 'profilo')
            .map(span => span.dataset.termId);
        
        const udoIds = Array.from(taxonomySpans)
            .filter(span => span.dataset.taxonomy === 'udo')
            .map(span => span.dataset.termId);
        
        return {
            id: item.dataset.postId,
            title: item.querySelector('.docs-item__title').textContent,
            description: item.querySelector('.docs-item__description') ? item.querySelector('.docs-item__description').textContent : '',
            profiloIds: profiloIds,
            udoIds: udoIds,
            element: item
        };
    });

    // Configurazione Fuse.js
    const fuseOptions = {
        keys: [
            { name: 'title', weight: 0.7 },
            { name: 'description', weight: 0.3 }
        ],
        threshold: 0.4,
        minMatchCharLength: 2,
        includeScore: true,
        ignoreLocation: true
    };
    const fuse = new Fuse(documentsData, fuseOptions);

    // Funzione principale di filtro
    function filterDocuments() {
        const searchTerm = searchInput.value.trim();
        const profiloFilter = filterProfilo ? filterProfilo.value : '';
        const udoFilter = filterUdo ? filterUdo.value : '';

        let filteredItems = [...documentsData];

        // Ricerca Fuse.js
        if (searchTerm && searchTerm.length >= 2) {
            const fuseResults = fuse.search(searchTerm);
            const fuseIds = fuseResults.map(result => result.item.id);
            filteredItems = filteredItems.filter(item => fuseIds.includes(item.id));
        }

        // Filtro Profilo
        if (profiloFilter) {
            filteredItems = filteredItems.filter(item => item.profiloIds.includes(profiloFilter));
        }

        // Filtro UDO
        if (udoFilter) {
            filteredItems = filteredItems.filter(item => item.udoIds.includes(udoFilter));
        }

        // Aggiorna DOM
        const filteredIds = filteredItems.map(item => item.id);
        let visibleCount = 0;

        documentsData.forEach(item => {
            const isVisible = filteredIds.includes(item.id);
            item.element.style.display = isVisible ? '' : 'none';
            if (isVisible) visibleCount++;
        });

        // Mostra/nascondi "no results"
        if (visibleCount === 0 && !docsList.querySelector('.docs-no-results')) {
            const noResultsDiv = document.createElement('div');
            noResultsDiv.className = 'docs-no-results';
            noResultsDiv.innerHTML = '<i data-lucide="inbox"></i><p>Nessun documento trovato</p>';
            docsList.appendChild(noResultsDiv);
        } else if (visibleCount > 0) {
            const noResults = docsList.querySelector('.docs-no-results');
            if (noResults) noResults.remove();
        }

        // Re-init Lucide icons
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    // Event listeners
    searchInput.addEventListener('input', function() {
        searchClear.style.display = this.value ? 'block' : 'none';
        filterDocuments();
    });

    searchClear.addEventListener('click', function(e) {
        e.preventDefault();
        searchInput.value = '';
        searchClear.style.display = 'none';
        filterDocuments();
        searchInput.focus();
    });

    if (filterProfilo) filterProfilo.addEventListener('change', filterDocuments);
    if (filterUdo) filterUdo.addEventListener('change', filterDocuments);
});
</script>

<?php
wp_reset_postdata();
get_footer();
?>
