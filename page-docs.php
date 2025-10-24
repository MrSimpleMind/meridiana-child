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

// ============================================================
// LOAD TAXONOMIES - CARICA TUTTE LE TASSONOMIE DA ACF
// ============================================================

global $wpdb;

// Prova 1: Usa get_terms standard con i nomi CORRETTI (con trattino)
$profili = get_terms(array(
    'taxonomy' => 'profilo-professionale',
    'hide_empty' => false,
    'number' => 0,
));

$udo = get_terms(array(
    'taxonomy' => 'unita-offerta',
    'hide_empty' => false,
    'number' => 0,
));

$area_competenza = get_terms(array(
    'taxonomy' => 'area-competenza',
    'hide_empty' => false,
    'number' => 0,
));

error_log( '=== DEBUG PAGE-DOCS ===' );
error_log( 'Profili: ' . count($profili) );
error_log( 'UDO: ' . count($udo) );
error_log( 'Area: ' . count($area_competenza) );

?>

<div class="content-wrapper documentazione-page">
    <div class="documentazione-container">
        
        <!-- BACK BUTTON -->
        <?php meridiana_render_back_button(); ?>

        <!-- SEARCH BAR + TOGGLE FILTRI -->
        <div class="docs-search-container">
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
                    <button id="docs-search-clear" class="docs-search-clear" aria-label="Pulisci ricerca">
                        <i data-lucide="x"></i>
                    </button>
                </div>
            </div>

            <!-- Toggle Filtri Button -->
            <button 
                id="docs-filter-toggle" 
                class="docs-filter-toggle" 
                aria-expanded="false"
                aria-controls="docs-filters-panel"
                aria-label="Mostra/nascondi filtri"
            >
                <i data-lucide="filter"></i>
                <span class="docs-filter-toggle__text">Filtri</span>
            </button>
        </div>

        <!-- FILTRI COLLASSABILI PANEL -->
        <div 
            id="docs-filters-panel" 
            class="docs-filters-panel" 
            aria-hidden="true"
        >
            <!-- FILTRI TIPO DOCUMENTO -->
            <div class="docs-type-filters">
                <label class="docs-type-label">Tipo:</label>
                <div class="docs-type-buttons">
                    <button class="docs-type-btn docs-type-btn--active" data-type="all" aria-label="Mostra tutti i documenti">
                        Tutti
                    </button>
                    <button class="docs-type-btn" data-type="protocollo" aria-label="Mostra solo Protocolli">
                        Protocolli
                    </button>
                    <button class="docs-type-btn" data-type="ats" aria-label="Mostra solo Piani ATS">
                        ATS
                    </button>
                    <button class="docs-type-btn" data-type="modulo" aria-label="Mostra solo Moduli">
                        Moduli
                    </button>
                </div>
            </div>

            <!-- FILTRI TASSONOMIE -->
            <div class="docs-filters">
                    <!-- Filter: Profilo Professionale -->
                    <?php if (!empty($profili) && !is_wp_error($profili)): ?>
                    <div class="filter-group">
                        <label for="filter-profilo" class="filter-group__label">Profilo Professionale</label>
                        <select id="filter-profilo" class="docs-filter-select">
                            <option value="" selected>Tutti</option>
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
                        <label for="filter-udo" class="filter-group__label">Unità d'offerta</label>
                        <select id="filter-udo" class="docs-filter-select">
                            <option value="" selected>Tutte</option>
                            <?php foreach ($udo as $term): ?>
                            <option value="<?php echo esc_attr($term->term_id); ?>">
                                <?php echo esc_html($term->name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <!-- Filter: Area di Competenza (solo Moduli) -->
                    <?php if (!empty($area_competenza) && !is_wp_error($area_competenza)): ?>
                    <div class="filter-group">
                        <label for="filter-area-competenza" class="filter-group__label">Area di Competenza</label>
                        <select id="filter-area-competenza" class="docs-filter-select">
                            <option value="" selected>Tutte</option>
                            <?php foreach ($area_competenza as $term): ?>
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
                    
                    // Verifica se è ATS
                    $is_ats = ($type === 'protocollo') ? get_field('pianificazione_ats', $doc->ID) : false;
                    
                    $riassunto = get_field('riassunto', $doc->ID);
                    $profilo_terms = get_the_terms($doc->ID, 'profilo-professionale');
                    $udo_terms = get_the_terms($doc->ID, 'unita-offerta');
                    $area_competenza_terms = get_the_terms($doc->ID, 'area-competenza');
                    
                    // Featured image per il thumbnail
                    $thumbnail_url = get_the_post_thumbnail_url($doc->ID, 'thumbnail');
                    if (!$thumbnail_url) {
                        $thumbnail_url = false;
                    }
                    ?>

                    <article class="docs-item" 
                             data-post-id="<?php echo esc_attr($doc->ID); ?>" 
                             data-post-type="<?php echo esc_attr($type); ?>" 
                             data-is-ats="<?php echo esc_attr($is_ats ? '1' : '0'); ?>"
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
                                                                            <div class="docs-item__badge">
                                                                                <?php
                                                                                // Mostra sempre il badge del tipo primario (P o M)
                                                                                if ($type === 'protocollo') {
                                                                                    echo meridiana_get_badge('protocollo', 'P');
                                                                                } else { // modulo
                                                                                    echo meridiana_get_badge('modulo', 'M');
                                                                                }
                                                    
                                                                                // Se è un protocollo ATS, aggiungi il badge ATS
                                                                                if ($is_ats) {
                                                                                    echo meridiana_get_badge('ats', 'ATS');
                                                                                }
                                                                                ?>
                                                                            </div>                            <h3 class="docs-item__title">
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
                                        <strong>Profilo:</strong> <?php echo esc_html(implode(', ', wp_list_pluck($profilo_terms, 'name'))); ?>
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($udo_terms) && !is_wp_error($udo_terms)): ?>
                                    <span class="docs-meta-tag">
                                        <strong>UDO:</strong> <?php echo esc_html(implode(', ', wp_list_pluck($udo_terms, 'name'))); ?>
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

                                <?php if (!empty($area_competenza_terms) && !is_wp_error($area_competenza_terms)): ?>
                                    <?php foreach ($area_competenza_terms as $term): ?>
                                        <span class="docs-taxonomy-data" data-taxonomy="area-competenza" data-term-id="<?php echo esc_attr($term->term_id); ?>"></span>
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
    // =================================================================
    // TOGGLE FILTRI PANEL - Mostra/nascondi filtri collassabili
    // =================================================================
    const filterToggle = document.getElementById('docs-filter-toggle');
    const filtersPanel = document.getElementById('docs-filters-panel');
    
    if (filterToggle && filtersPanel) {
        filterToggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Toggle aria-expanded e aria-hidden attributes
            const isExpanded = filterToggle.getAttribute('aria-expanded') === 'true';
            filterToggle.setAttribute('aria-expanded', !isExpanded);
            filtersPanel.setAttribute('aria-hidden', isExpanded);
            
            // Toggle class per CSS transitions
            filtersPanel.classList.toggle('docs-filters-panel--open');
        });
    }

    // =================================================================
    // DOCUMENT FILTERS LOGIC
    // =================================================================
    const searchInput = document.getElementById('docs-search');
    const searchClear = document.getElementById('docs-search-clear');
    const filterProfilo = document.getElementById('filter-profilo');
    const filterUdo = document.getElementById('filter-udo');
    const filterAreaCompetenza = document.getElementById('filter-area-competenza');
    const typeButtons = document.querySelectorAll('.docs-type-btn');
    const docsList = document.getElementById('docs-list');
    const docItems = docsList.querySelectorAll('.docs-item');

    let selectedType = 'all';

    // Preparazione dati per Fuse.js
    const documentsData = Array.from(docItems).map(item => {
        const taxonomySpans = item.querySelectorAll('.docs-taxonomy-data');
        
        const profiloIds = Array.from(taxonomySpans)
            .filter(span => span.dataset.taxonomy === 'profilo')
            .map(span => span.dataset.termId);
        
        const udoIds = Array.from(taxonomySpans)
            .filter(span => span.dataset.taxonomy === 'udo')
            .map(span => span.dataset.termId);
        
        const areaCompetenzaIds = Array.from(taxonomySpans)
            .filter(span => span.dataset.taxonomy === 'area-competenza')
            .map(span => span.dataset.termId);
        
        return {
            id: item.dataset.postId,
            title: item.querySelector('.docs-item__title').textContent,
            description: item.querySelector('.docs-item__description') ? item.querySelector('.docs-item__description').textContent : '',
            postType: item.dataset.postType,
            isAts: item.dataset.isAts === '1',
            profiloIds: profiloIds,
            udoIds: udoIds,
            areaCompetenzaIds: areaCompetenzaIds,
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
        const areaCompetenzaFilter = filterAreaCompetenza ? filterAreaCompetenza.value : '';

        let filteredItems = [...documentsData];

        // Filtro Tipo Documento
        if (selectedType !== 'all') {
            filteredItems = filteredItems.filter(item => {
                if (selectedType === 'ats') {
                    return item.postType === 'protocollo' && item.isAts;
                }
                return item.postType === selectedType;
            });
        }

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

        // Filtro Area di Competenza
        if (areaCompetenzaFilter) {
            filteredItems = filteredItems.filter(item => item.areaCompetenzaIds.includes(areaCompetenzaFilter));
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
    if (filterAreaCompetenza) filterAreaCompetenza.addEventListener('change', filterDocuments);

    // Event listeners per filtri tipo
    typeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // Rimuovi active da tutti
            typeButtons.forEach(b => b.classList.remove('docs-type-btn--active'));
            // Aggiungi active al bottone cliccato
            this.classList.add('docs-type-btn--active');
            // Aggiorna tipo selezionato
            selectedType = this.dataset.type;
            // Filtra
            filterDocuments();
        });
    });
});
</script>

<?php
wp_reset_postdata();
get_footer();
?>
