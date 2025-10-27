<?php
$documenti_query = new WP_Query(['post_type' => ['protocollo', 'modulo'], 'posts_per_page' => 50, 'orderby' => 'date', 'order' => 'DESC']);

// Carica le tassonomie per i filtri
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
?>
<div class="tab-header">
    <h2>Documentazione</h2>
    <div style="display: flex; gap: 12px;">
        <button class="btn btn-primary" @click="openFormModal('documenti', 'new', 0, 'modulo')" style="flex: 1;"><i data-lucide="plus"></i> Nuovo Modulo</button>
        <button class="btn btn-primary" @click="openFormModal('documenti', 'new', 0, 'protocollo')" style="flex: 1;"><i data-lucide="plus"></i> Nuovo Protocollo</button>
    </div>
</div>

<!-- SEARCH BAR + TOGGLE FILTRI -->
<div class="docs-search-container">
    <!-- Search -->
    <div class="docs-search-wrapper">
        <div class="search-input-group">
            <i data-lucide="search"></i>
            <input
                type="text"
                id="documenti-search"
                class="docs-search-input"
                placeholder="Barra di ricerca"
                aria-label="Cerca documenti"
            >
            <button id="documenti-search-clear" class="docs-search-clear" aria-label="Pulisci ricerca">
                <i data-lucide="x"></i>
            </button>
        </div>
    </div>

    <!-- Toggle Filtri Button -->
    <button
        id="documenti-filter-toggle"
        class="docs-filter-toggle"
        aria-expanded="false"
        aria-controls="documenti-filters-panel"
        aria-label="Mostra/nascondi filtri"
    >
        <i data-lucide="filter"></i>
        <span class="docs-filter-toggle__text">Filtri</span>
    </button>
</div>

<!-- FILTRI COLLASSABILI PANEL -->
<div
    id="documenti-filters-panel"
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
            <label for="filter-profilo-documenti" class="filter-group__label">Profilo Professionale</label>
            <select id="filter-profilo-documenti" class="docs-filter-select">
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
            <label for="filter-udo-documenti" class="filter-group__label">Unità d'offerta</label>
            <select id="filter-udo-documenti" class="docs-filter-select">
                <option value="" selected>Tutte</option>
                <?php foreach ($udo as $term): ?>
                <option value="<?php echo esc_attr($term->term_id); ?>">
                    <?php echo esc_html($term->name); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <!-- Filter: Area di Competenza -->
        <?php if (!empty($area_competenza) && !is_wp_error($area_competenza)): ?>
        <div class="filter-group">
            <label for="filter-area-competenza-documenti" class="filter-group__label">Area di Competenza</label>
            <select id="filter-area-competenza-documenti" class="docs-filter-select">
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

<?php if ($documenti_query->have_posts()): ?>

    <!-- CARD LAYOUT - Card List -->
    <div class="item-cards-container laptop-only" id="documenti-cards">
        <?php
        $documenti_query->rewind_posts();
        while ($documenti_query->have_posts()): $documenti_query->the_post();
            $post_type = get_post_type();
            $is_ats = ($post_type === 'protocollo') && get_field('pianificazione_ats', get_the_ID());
            $type_label = $post_type === 'protocollo' ? 'Protocollo' : 'Modulo';
            $status = get_post_status();
            $status_class = $status === 'publish' ? 'badge-success' : 'badge-warning';

            // Carica i termini per i data attributes
            $profilo_terms = get_the_terms(get_the_ID(), 'profilo-professionale');
            $udo_terms = get_the_terms(get_the_ID(), 'unita-offerta');
            $area_competenza_terms = get_the_terms(get_the_ID(), 'area-competenza');

            $profilo_ids = (is_array($profilo_terms)) ? implode(',', wp_list_pluck($profilo_terms, 'term_id')) : '';
            $udo_ids = (is_array($udo_terms)) ? implode(',', wp_list_pluck($udo_terms, 'term_id')) : '';
            $area_ids = (is_array($area_competenza_terms)) ? implode(',', wp_list_pluck($area_competenza_terms, 'term_id')) : '';
        ?>
        <div class="item-card"
             data-item-id="<?php echo get_the_ID(); ?>"
             data-post-type="<?php echo esc_attr($post_type); ?>"
             data-is-ats="<?php echo esc_attr($is_ats ? '1' : '0'); ?>"
             data-title="<?php echo esc_attr(strtolower(get_the_title())); ?>"
             data-profilo-ids="<?php echo esc_attr($profilo_ids); ?>"
             data-udo-ids="<?php echo esc_attr($udo_ids); ?>"
             data-area-ids="<?php echo esc_attr($area_ids); ?>">
            <div class="item-card__header" data-toggle="card-<?php echo get_the_ID(); ?>">
                <div class="item-card__info">
                    <div class="item-card__title"><?php the_title(); ?></div>
                    <div class="item-card__meta">
                        <span class="item-card__type <?php echo esc_attr('type-' . $post_type); ?>"><?php echo esc_html($type_label); ?></span>
                        <?php if ($is_ats): ?>
                            <span class="item-card__type type-ats">ATS</span>
                        <?php endif; ?>
                        <span class="item-card__separator">•</span>
                        <span class="badge <?php echo esc_attr($status_class); ?>"><?php echo ucfirst($status); ?></span>
                        <span class="item-card__separator">•</span>
                        <span class="item-card__date"><?php echo get_the_date('d/m/Y'); ?></span>
                    </div>
                </div>
                <div class="item-card__actions-group">
                    <button class="btn-icon" @click.stop="openFormModal('documenti', 'edit', <?php echo get_the_ID(); ?>, '<?php echo esc_attr($post_type); ?>')" title="Modifica"><i data-lucide="edit-2"></i></button>
                    <button class="btn-icon" @click.stop="deletePost(<?php echo get_the_ID(); ?>)" title="Elimina"><i data-lucide="trash-2"></i></button>
                    <a href="<?php the_permalink(); ?>" class="btn-icon" title="Visualizza" target="_blank"><i data-lucide="eye"></i></a>
                    <button class="item-card__toggle" aria-label="Espandi dettagli"><i data-lucide="chevron-down"></i></button>
                </div>
            </div>
            <div class="item-card__content" id="card-<?php echo get_the_ID(); ?>">
                <?php
                // Lista dei campi da escludere
                $exclude_fields = ['pdf_modulo', 'pdf_protocollo', 'pianificazione_ats'];

                // Recupera tutte le ACF field
                $fields = get_fields(get_the_ID());
                if ($fields && is_array($fields)) {
                    foreach ($fields as $field_name => $field_value) {
                        // Salta i campi vuoti, quelli privati (iniziano con _) e quelli esclusi
                        if (empty($field_value) || strpos($field_name, '_') === 0 || in_array($field_name, $exclude_fields)) {
                            continue;
                        }

                        // Ottieni le info del campo per il label
                        $field_object = get_field_object($field_name, get_the_ID());
                        $field_label = $field_object ? $field_object['label'] : ucwords(str_replace('_', ' ', $field_name));

                        // Formatta il valore a seconda del tipo
                        $display_value = '';
                        if (is_array($field_value)) {
                            // Se è il campo "moduli_allegati", mostra i titoli dei post
                            if ($field_name === 'moduli_allegati' && !empty($field_value)) {
                                $module_titles = [];
                                foreach ($field_value as $module_id) {
                                    $module_post = get_post($module_id);
                                    if ($module_post) {
                                        $module_titles[] = esc_html($module_post->post_title);
                                    }
                                }
                                $display_value = implode(', ', $module_titles);
                            } else {
                                $display_value = implode(', ', array_map('esc_html', $field_value));
                            }
                        } elseif (is_object($field_value)) {
                            $display_value = isset($field_value->post_title) ? esc_html($field_value->post_title) : esc_html((string)$field_value);
                        } else {
                            $display_value = esc_html($field_value);
                        }

                        if ($display_value) {
                            echo '<div class="item-card__row">';
                            echo '<span class="item-card__label">' . esc_html($field_label) . '</span>';
                            echo '<span class="item-card__value">' . $display_value . '</span>';
                            echo '</div>';
                        }
                    }
                }

                // Lista delle tassonomie da mostrare (esplicite per evitare problemi)
                $taxonomy_names = ['unita-offerta', 'profilo-professionale', 'area-competenza', 'category'];

                foreach ($taxonomy_names as $tax_name) {
                    $terms = get_the_terms(get_the_ID(), $tax_name);
                    if ($terms && !is_wp_error($terms) && !empty($terms)) {
                        // Ottieni il label della tassonomia
                        $tax_object = get_taxonomy($tax_name);
                        $tax_label = $tax_object ? $tax_object->label : ucwords(str_replace('_', ' ', $tax_name));

                        // Mostra TUTTI i termini selezionati
                        $term_names = wp_list_pluck($terms, 'name');
                        echo '<div class="item-card__row">';
                        echo '<span class="item-card__label">' . esc_html($tax_label) . '</span>';
                        echo '<span class="item-card__value">' . esc_html(implode(', ', $term_names)) . '</span>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

<?php else: ?>
<div class="no-content"><i data-lucide="inbox"></i><p>Nessun documento trovato</p></div>
<?php endif; wp_reset_postdata(); ?>

<!-- Fuse.js Library -->
<script src="https://cdn.jsdelivr.net/npm/fuse.js@7.0.0/dist/fuse.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // =================================================================
    // TOGGLE FILTRI PANEL
    // =================================================================
    const filterToggle = document.getElementById('documenti-filter-toggle');
    const filtersPanel = document.getElementById('documenti-filters-panel');

    if (filterToggle && filtersPanel) {
        filterToggle.addEventListener('click', function(e) {
            e.preventDefault();
            const isExpanded = filterToggle.getAttribute('aria-expanded') === 'true';
            filterToggle.setAttribute('aria-expanded', !isExpanded);
            filtersPanel.setAttribute('aria-hidden', isExpanded);
            filtersPanel.classList.toggle('docs-filters-panel--open');
        });
    }

    // =================================================================
    // DOCUMENT FILTERS LOGIC
    // =================================================================
    const searchInput = document.getElementById('documenti-search');
    const searchClear = document.getElementById('documenti-search-clear');
    const filterProfilo = document.getElementById('filter-profilo-documenti');
    const filterUdo = document.getElementById('filter-udo-documenti');
    const filterAreaCompetenza = document.getElementById('filter-area-competenza-documenti');
    const typeButtons = document.querySelectorAll('#documenti-filters-panel .docs-type-btn');
    const itemsContainer = document.querySelector('#documenti-cards');
    const itemCards = itemsContainer ? itemsContainer.querySelectorAll('.item-card') : [];

    let selectedType = 'all';

    // Preparazione dati per Fuse.js
    const documentsData = Array.from(itemCards).map(item => {
        return {
            id: item.dataset.itemId,
            title: item.querySelector('.item-card__title').textContent,
            postType: item.dataset.postType,
            isAts: item.dataset.isAts === '1',
            profiloIds: item.dataset.profiloIds ? item.dataset.profiloIds.split(',') : [],
            udoIds: item.dataset.udoIds ? item.dataset.udoIds.split(',') : [],
            areaIds: item.dataset.areaIds ? item.dataset.areaIds.split(',') : [],
            element: item
        };
    });

    // Configurazione Fuse.js
    const fuseOptions = {
        keys: ['title'],
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
            filteredItems = filteredItems.filter(item => item.areaIds.includes(areaCompetenzaFilter));
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
        if (visibleCount === 0 && !itemsContainer.querySelector('.no-content')) {
            const noContentDiv = document.createElement('div');
            noContentDiv.className = 'no-content';
            noContentDiv.innerHTML = '<i data-lucide="inbox"></i><p>Nessun documento trovato</p>';
            itemsContainer.after(noContentDiv);
        } else if (visibleCount > 0) {
            const noContent = itemsContainer.parentElement.querySelector('.no-content');
            if (noContent) noContent.remove();
        }

        // Re-init Lucide icons
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    // Event listeners
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            if (searchClear) searchClear.style.display = this.value ? 'block' : 'none';
            filterDocuments();
        });
    }

    if (searchClear) {
        searchClear.addEventListener('click', function(e) {
            e.preventDefault();
            if (searchInput) searchInput.value = '';
            searchClear.style.display = 'none';
            filterDocuments();
            if (searchInput) searchInput.focus();
        });
    }

    if (filterProfilo) filterProfilo.addEventListener('change', filterDocuments);
    if (filterUdo) filterUdo.addEventListener('change', filterDocuments);
    if (filterAreaCompetenza) filterAreaCompetenza.addEventListener('change', filterDocuments);

    // Event listeners per filtri tipo
    typeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            typeButtons.forEach(b => b.classList.remove('docs-type-btn--active'));
            this.classList.add('docs-type-btn--active');
            selectedType = this.dataset.type;
            filterDocuments();
        });
    });

    // =================================================================
    // CARD TOGGLE LOGIC
    // =================================================================
    // Handler per il click sul toggle button
    document.querySelectorAll('.item-card__toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const header = this.closest('.item-card__header');
            const cardId = header.getAttribute('data-toggle');
            const content = document.getElementById(cardId);
            if (content && this) {
                content.classList.toggle('open');
                this.classList.toggle('open');
            }
        });
    });

    // Handler per il click sull'header (area titolo e meta)
    document.querySelectorAll('.item-card__header').forEach(header => {
        header.addEventListener('click', function(e) {
            if (e.target.closest('.item-card__toggle') || e.target.closest('.item-card__actions-group')) {
                return;
            }
            const cardId = this.getAttribute('data-toggle');
            const content = document.getElementById(cardId);
            const toggle = this.querySelector('.item-card__toggle');
            if (content && toggle) {
                content.classList.toggle('open');
                toggle.classList.toggle('open');
            }
        });
    });
});
</script>
