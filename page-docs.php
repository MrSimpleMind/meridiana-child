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

$aree = get_terms(array(
    'taxonomy' => 'aree_competenza',
    'hide_empty' => false,
));
?>

<div class="content-wrapper">
    <div class="container">
        <div class="docs-page">
            
            <!-- BACK BUTTON -->
            <?php meridiana_render_back_button(); ?>

            <!-- TOGGLE FILTRI (Mobile Only) -->
            <button class="docs-filters-toggle" id="docs-filters-toggle" type="button" aria-label="Mostra filtri">
                <i data-lucide="filter"></i>
                <span>Mostra Filtri</span>
            </button>

            <div class="docs-layout">
                
                <!-- SIDEBAR FILTRI (Desktop Sticky, Mobile Collapsible) -->
                <aside class="docs-sidebar" id="docs-sidebar">
                    <h2 class="docs-sidebar-title">Filtri</h2>
                    
                    <!-- Search Box -->
                    <div class="docs-search-wrapper">
                        <input 
                            type="text" 
                            id="docs-search" 
                            class="docs-search-input" 
                            placeholder="Cerca per titolo..."
                        >
                        <button id="docs-search-clear" class="docs-search-clear" style="display: none;" aria-label="Pulisci ricerca">
                            <i data-lucide="x"></i>
                        </button>
                    </div>

                    <!-- Filter: Tipo Documento -->
                    <div class="docs-filter-group">
                        <label class="docs-filter-label">Tipo Documento</label>
                        <select id="filter-tipo" class="docs-filter-select">
                            <option value="">Tutti</option>
                            <option value="protocollo">Protocolli (P)</option>
                            <option value="modulo">Moduli (M)</option>
                        </select>
                    </div>

                    <!-- Filter: Profilo Professionale -->
                    <?php if (!empty($profili) && !is_wp_error($profili)): ?>
                    <div class="docs-filter-group">
                        <label class="docs-filter-label">Profilo Professionale</label>
                        <select id="filter-profilo" class="docs-filter-select">
                            <option value="">Tutti</option>
                            <?php foreach ($profili as $term): ?>
                            <option value="<?php echo $term->term_id; ?>">
                                <?php echo esc_html($term->name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <!-- Filter: Unità d'Offerta -->
                    <?php if (!empty($udo) && !is_wp_error($udo)): ?>
                    <div class="docs-filter-group">
                        <label class="docs-filter-label">Unità d'Offerta</label>
                        <select id="filter-udo" class="docs-filter-select">
                            <option value="">Tutti</option>
                            <?php foreach ($udo as $term): ?>
                            <option value="<?php echo $term->term_id; ?>">
                                <?php echo esc_html($term->name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <!-- Filter: Aree Competenza -->
                    <?php if (!empty($aree) && !is_wp_error($aree)): ?>
                    <div class="docs-filter-group">
                        <label class="docs-filter-label">Area Competenza</label>
                        <select id="filter-aree" class="docs-filter-select">
                            <option value="">Tutti</option>
                            <?php foreach ($aree as $term): ?>
                            <option value="<?php echo $term->term_id; ?>">
                                <?php echo esc_html($term->name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                </aside>

                <!-- MAIN CONTENT -->
                <main class="docs-main">
                    
                    <!-- Header con contatore -->
                    <div class="docs-main-header">
                        <h2 class="docs-results-title">Risultati</h2>
                        <span class="docs-results-count" id="docs-count">
                            <?php echo count($documents); ?> documenti
                        </span>
                    </div>

                    <!-- Lista Documenti -->
                    <div id="docs-list" class="docs-list">
                        <?php if (!empty($documents)): ?>
                            <?php foreach ($documents as $doc): ?>
                                <?php 
                                $type = get_post_type($doc->ID);
                                $type_label = ($type === 'protocollo') ? 'Protocollo' : 'Modulo';
                                $type_color = ($type === 'protocollo') ? 'blue' : 'green';
                                
                                $riassunto = get_field('riassunto', $doc->ID);
                                $profilo_terms = get_the_terms($doc->ID, 'profili_professionali');
                                $udo_terms = get_the_terms($doc->ID, 'unita_offerta');
                                $area_terms = get_the_terms($doc->ID, 'aree_competenza');
                                ?>

                                <article class="docs-item" data-post-id="<?php echo $doc->ID; ?>" data-post-type="<?php echo esc_attr($type); ?>" data-title="<?php echo esc_attr(strtolower($doc->post_title)); ?>">
                                    
                                    <!-- Contenuto Card -->
                                    <div class="docs-item-content">
                                        <!-- Badge Tipo (Alto a Sinistra) -->
                                        <div class="docs-item-badge" data-badge="<?php echo esc_attr($type); ?>">
                                            <span class="badge badge-<?php echo esc_attr($type_color); ?>">
                                                <?php echo substr($type_label, 0, 1); ?>
                                            </span>
                                        </div>
                                        
                                        <h3 class="docs-item-title">
                                            <?php echo esc_html($doc->post_title); ?>
                                        </h3>

                                        <?php if ($riassunto): ?>
                                        <p class="docs-item-excerpt">
                                            <?php echo wp_trim_words($riassunto, 20); ?>
                                        </p>
                                        <?php endif; ?>

                                        <!-- Taxonomie visibili -->
                                        <div class="docs-item-meta">
                                            <?php if (!empty($profilo_terms) && !is_wp_error($profilo_terms)): ?>
                                                <span class="docs-meta-label">
                                                    <strong>Profilo:</strong> 
                                                    <?php echo esc_html($profilo_terms[0]->name); ?>
                                                </span>
                                            <?php endif; ?>

                                            <?php if (!empty($udo_terms) && !is_wp_error($udo_terms)): ?>
                                                <span class="docs-meta-label">
                                                    <strong>UDO:</strong> 
                                                    <?php echo esc_html($udo_terms[0]->name); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Hidden Data Attributes per Fuse.js Filtering -->
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

                                            <?php if (!empty($area_terms) && !is_wp_error($area_terms)): ?>
                                                <?php foreach ($area_terms as $term): ?>
                                                    <span class="docs-taxonomy-data" data-taxonomy="area" data-term-id="<?php echo esc_attr($term->term_id); ?>"></span>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- CTA Button -->
                                    <div class="docs-item-action">
                                        <a href="<?php echo esc_url(get_permalink($doc->ID)); ?>" class="btn btn-primary btn-sm">
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

                </main>

            </div>
        </div>
    </div>
</div>

<!-- JavaScript per Filtri e Ricerca con Fuse.js COMPLETO -->
<script src="https://cdn.jsdelivr.net/npm/fuse.js@7.0.0/dist/fuse.min.js"></script>
<link rel="stylesheet" href="<?php echo MERIDIANA_CHILD_URI; ?>/assets/css/dist/badge-override.css?v=<?php echo time(); ?>">
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('docs-search');
    const searchClear = document.getElementById('docs-search-clear');
    const filterTipo = document.getElementById('filter-tipo');
    const filterProfilo = document.getElementById('filter-profilo');
    const filterUdo = document.getElementById('filter-udo');
    const filterAree = document.getElementById('filter-aree');
    const docsList = document.getElementById('docs-list');
    const docsCount = document.getElementById('docs-count');
    const docItems = docsList.querySelectorAll('.docs-item');
    const filtersToggle = document.getElementById('docs-filters-toggle');
    const filtersSidebar = document.getElementById('docs-sidebar');

    // ============================================
    // 1. PREPARAZIONE DATI PER FUSE.JS
    // ============================================
    const documentsData = Array.from(docItems).map(item => {
        // Estrae i termini di tassonomia dai data-attributes nascosti
        const taxonomySpans = item.querySelectorAll('.docs-taxonomy-data');
        
        const profiloIds = Array.from(taxonomySpans)
            .filter(span => span.dataset.taxonomy === 'profilo')
            .map(span => span.dataset.termId);
        
        const udoIds = Array.from(taxonomySpans)
            .filter(span => span.dataset.taxonomy === 'udo')
            .map(span => span.dataset.termId);
        
        const areeIds = Array.from(taxonomySpans)
            .filter(span => span.dataset.taxonomy === 'area')
            .map(span => span.dataset.termId);
        
        return {
            id: item.dataset.postId,
            title: item.querySelector('.docs-item-title').textContent,
            excerpt: item.querySelector('.docs-item-excerpt') ? item.querySelector('.docs-item-excerpt').textContent : '',
            type: item.dataset.postType,
            profiloIds: profiloIds,
            udoIds: udoIds,
            areeIds: areeIds,
            element: item
        };
    });

    // ============================================
    // 2. CONFIGURAZIONE FUSE.JS
    // ============================================
    // Ricerca fuzzy su: titolo e riassunto
    const fuseOptions = {
        keys: [
            {
                name: 'title',
                weight: 0.7  // Titolo ha peso maggiore
            },
            {
                name: 'excerpt',
                weight: 0.3  // Riassunto ha peso minore
            }
        ],
        threshold: 0.4,         // 0.4 = tolleranza 40% (più tollerante ai typo)
        minMatchCharLength: 2,  // Min 2 caratteri per cercare
        includeScore: true,
        ignoreLocation: true,
        useExtendedSearch: false
    };
    const fuse = new Fuse(documentsData, fuseOptions);

    // ============================================
    // 3. FUNZIONE PRINCIPALE DI FILTRO
    // ============================================
    function filterDocuments() {
        const searchTerm = searchInput.value.trim();
        const tipoFilter = filterTipo.value;
        const profiloFilter = filterProfilo ? filterProfilo.value : '';
        const udoFilter = filterUdo ? filterUdo.value : '';
        const areeFilter = filterAree ? filterAree.value : '';

        // Copia dei dati originali
        let filteredItems = [...documentsData];

        // ---- STEP 1: Ricerca con Fuse.js (ricerca fuzzy sul titolo e riassunto) ----
        if (searchTerm && searchTerm.length >= 2) {
            const fuseResults = fuse.search(searchTerm);
            const fuseIds = fuseResults.map(result => result.item.id);
            filteredItems = filteredItems.filter(item => fuseIds.includes(item.id));
        }

        // ---- STEP 2: Filtro Tipo Documento ----
        if (tipoFilter) {
            filteredItems = filteredItems.filter(item => item.type === tipoFilter);
        }

        // ---- STEP 3: Filtro Profilo Professionale ----
        if (profiloFilter) {
            filteredItems = filteredItems.filter(item => item.profiloIds.includes(profiloFilter));
        }

        // ---- STEP 4: Filtro Unità d'Offerta ----
        if (udoFilter) {
            filteredItems = filteredItems.filter(item => item.udoIds.includes(udoFilter));
        }

        // ---- STEP 5: Filtro Area Competenza ----
        if (areeFilter) {
            filteredItems = filteredItems.filter(item => item.areeIds.includes(areeFilter));
        }

        // ============================================
        // 4. AGGIORNA DOM CON RISULTATI
        // ============================================
        const filteredIds = filteredItems.map(item => item.id);
        let visibleCount = 0;

        documentsData.forEach(item => {
            const isVisible = filteredIds.includes(item.id);
            item.element.style.display = isVisible ? '' : 'none';
            if (isVisible) visibleCount++;
        });

        // Aggiorna contatore risultati
        docsCount.textContent = visibleCount + ' documento' + (visibleCount !== 1 ? 'i' : '');

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

        // Re-initialize Lucide icons
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    // ============================================
    // 5. EVENT LISTENERS - RICERCA
    // ============================================
    
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

    // ============================================
    // 6. EVENT LISTENERS - FILTRI DROPDOWN
    // ============================================
    
    filterTipo.addEventListener('change', filterDocuments);
    if (filterProfilo) filterProfilo.addEventListener('change', filterDocuments);
    if (filterUdo) filterUdo.addEventListener('change', filterDocuments);
    if (filterAree) filterAree.addEventListener('change', filterDocuments);

    // ============================================
    // 7. MOBILE: TOGGLE FILTRI COLLAPSIBILI
    // ============================================
    
    if (filtersToggle && filtersSidebar) {
        filtersToggle.addEventListener('click', function(e) {
            e.preventDefault();
            filtersSidebar.classList.toggle('active');
            const isActive = filtersSidebar.classList.contains('active');
            
            // Aggiorna button label e icon
            const icon = isActive ? 'x' : 'filter';
            const text = isActive ? 'Nascondi Filtri' : 'Mostra Filtri';
            filtersToggle.innerHTML = '<i data-lucide="' + icon + '"></i><span>' + text + '</span>';
            
            // Re-initialize Lucide icons
            if (window.lucide) {
                lucide.createIcons();
            }
        });

        // Chiudi filtri quando selezioni un'opzione (mobile UX migliorata)
        const selectElements = filtersSidebar.querySelectorAll('select');
        selectElements.forEach(select => {
            select.addEventListener('change', function() {
                // Solo su mobile (max-width 768px)
                if (window.innerWidth <= 768) {
                    filtersSidebar.classList.remove('active');
                    filtersToggle.innerHTML = '<i data-lucide="filter"></i><span>Mostra Filtri</span>';
                    if (window.lucide) {
                        lucide.createIcons();
                    }
                }
            });
        });
    }

    // ============================================
    // 8. DEBUG LOG
    // ============================================
    console.log('📄 Fuse.js Docs Filter Initialized');
    console.log('📊 Documents loaded:', documentsData.length);
    console.log('🔍 Fuse.js version: 7.0.0');
    console.log('⚙️ Search fields: title (weight 0.7), excerpt (weight 0.3)');
    console.log('📋 Taxonomy filters: profilo, udo, area');
});
</script>

<?php
wp_reset_postdata();
get_footer();
?>
