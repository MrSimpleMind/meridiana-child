<?php
$salute_query = new WP_Query([
    'post_type' => 'salute-e-benessere-l',
    'posts_per_page' => 50,
    'orderby' => 'date',
    'order' => 'DESC',
]);

// Carica categorie uniche
$categories_unique = get_terms(array(
    'taxonomy' => 'category',
    'hide_empty' => false,
    'number' => 0,
));
?>
<div class="tab-header">
    <h2>Salute & Benessere</h2>
    <button class="btn btn-primary" @click="openFormModal('salute', 'new', 0, null)"><i data-lucide="plus"></i> Nuovo Articolo</button>
</div>

<!-- SEARCH BAR + TOGGLE FILTRI -->
<div class="docs-search-container">
    <div class="docs-search-wrapper">
        <div class="search-input-group">
            <i data-lucide="search"></i>
            <input type="text" id="salute-search" class="docs-search-input" placeholder="Barra di ricerca" aria-label="Cerca articoli">
            <button id="salute-search-clear" class="docs-search-clear" aria-label="Pulisci ricerca"><i data-lucide="x"></i></button>
        </div>
    </div>
    <button id="salute-filter-toggle" class="docs-filter-toggle" aria-expanded="false" aria-controls="salute-filters-panel" aria-label="Mostra/nascondi filtri">
        <i data-lucide="filter"></i> <span class="docs-filter-toggle__text">Filtri</span>
    </button>
</div>

<!-- FILTRI COLLASSABILI PANEL -->
<div id="salute-filters-panel" class="docs-filters-panel" aria-hidden="true">
    <div class="docs-type-filters">
        <label class="docs-type-label">Stato:</label>
        <div class="docs-type-buttons">
            <button class="docs-type-btn docs-type-btn--active" data-status="all">Tutti</button>
            <button class="docs-type-btn" data-status="publish">Pubblicati</button>
            <button class="docs-type-btn" data-status="draft">Bozze</button>
        </div>
    </div>
    <div class="docs-filters">
        <?php if (!empty($categories_unique) && !is_wp_error($categories_unique)): ?>
        <div class="filter-group">
            <label for="filter-category-salute" class="filter-group__label">Categoria</label>
            <select id="filter-category-salute" class="docs-filter-select">
                <option value="" selected>Tutte</option>
                <?php foreach ($categories_unique as $cat): ?>
                <option value="<?php echo esc_attr($cat->term_id); ?>"><?php echo esc_html($cat->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($salute_query->have_posts()): ?>

    <!-- CARD LAYOUT - Card List -->
    <div class="item-cards-container laptop-only">
        <?php
        $salute_query->rewind_posts();
        while ($salute_query->have_posts()): $salute_query->the_post();
            $post_id = get_the_ID();
            $status = get_post_status($post_id);
            $updated_date = get_the_modified_date('d/m/Y');
            $risorse = get_field('risorse', $post_id);
            $risorse_count = is_array($risorse) ? count($risorse) : 0;
            $categories = get_the_category($post_id);
            $category_ids = !empty($categories) ? implode(',', wp_list_pluck($categories, 'term_id')) : '';
        ?>
        <div class="item-card"
             data-item-id="<?php echo $post_id; ?>"
             data-title="<?php echo esc_attr(strtolower(get_the_title())); ?>"
             data-status="<?php echo esc_attr($status); ?>"
             data-category-ids="<?php echo esc_attr($category_ids); ?>">
            <div class="item-card__header" data-toggle="card-<?php echo $post_id; ?>">
                <div class="item-card__info">
                    <div class="item-card__title"><?php the_title(); ?></div>
                    <div class="item-card__meta">
                        <span class="badge <?php echo $status === 'publish' ? 'badge-success' : 'badge-warning'; ?>"><?php echo $status === 'publish' ? 'Pubblicato' : 'Bozza'; ?></span>
                        <?php
                        $categories = get_the_terms($post_id, 'category');
                        if (!is_wp_error($categories) && !empty($categories)) {
                            foreach ($categories as $cat) {
                                echo '<span class="item-card__separator">•</span>';
                                echo '<span class="item-card__category-text">' . esc_html($cat->name) . '</span>';
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="item-card__actions-group">
                    <button class="btn-icon" @click.stop="openFormModal('salute', 'edit', <?php echo $post_id; ?>, null)" title="Modifica"><i data-lucide="edit-2"></i></button>
                    <button class="btn-icon" @click.stop="deleteSalute(<?php echo $post_id; ?>)" title="Elimina"><i data-lucide="trash-2"></i></button>
                    <a href="<?php the_permalink(); ?>" class="btn-icon" title="Visualizza" target="_blank"><i data-lucide="eye"></i></a>
                </div>
            </div>
            <div class="item-card__content" id="card-<?php echo $post_id; ?>">
            </div>
        </div>
        <?php endwhile; ?>
    </div>

<?php else: ?>
<div class="no-content"><i data-lucide="inbox"></i><p><?php esc_html_e('Nessun contenuto trovato', 'meridiana-child'); ?></p></div>
<?php endif; wp_reset_postdata(); ?>

<script src="https://cdn.jsdelivr.net/npm/fuse.js@7.0.0/dist/fuse.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterToggle = document.getElementById('salute-filter-toggle');
    const filtersPanel = document.getElementById('salute-filters-panel');
    if (filterToggle && filtersPanel) {
        filterToggle.addEventListener('click', function(e) {
            e.preventDefault();
            const isExpanded = filterToggle.getAttribute('aria-expanded') === 'true';
            filterToggle.setAttribute('aria-expanded', !isExpanded);
            filtersPanel.setAttribute('aria-hidden', isExpanded);
            filtersPanel.classList.toggle('docs-filters-panel--open');
        });
    }

    const searchInput = document.getElementById('salute-search');
    const searchClear = document.getElementById('salute-search-clear');
    const filterCategory = document.getElementById('filter-category-salute');
    const statusButtons = document.querySelectorAll('#salute-filters-panel [data-status]');
    const itemsContainer = document.querySelector('.item-cards-container');
    const itemCards = itemsContainer ? itemsContainer.querySelectorAll('.item-card') : [];

    let selectedStatus = 'all';

    const itemsData = Array.from(itemCards).map(item => ({
        id: item.dataset.itemId,
        title: item.dataset.title,
        status: item.dataset.status,
        categoryIds: item.dataset.categoryIds ? item.dataset.categoryIds.split(',') : [],
        element: item
    }));

    const fuse = new Fuse(itemsData, {
        keys: ['title'],
        threshold: 0.4,
        minMatchCharLength: 2,
        includeScore: true,
        ignoreLocation: true
    });

    function filterItems() {
        const searchTerm = searchInput ? searchInput.value.trim() : '';
        const categoryFilter = filterCategory ? filterCategory.value : '';
        let filteredItems = [...itemsData];

        if (selectedStatus !== 'all') {
            filteredItems = filteredItems.filter(item => item.status === selectedStatus);
        }

        if (searchTerm && searchTerm.length >= 2) {
            const fuseResults = fuse.search(searchTerm);
            const fuseIds = fuseResults.map(result => result.item.id);
            filteredItems = filteredItems.filter(item => fuseIds.includes(item.id));
        }

        if (categoryFilter) {
            filteredItems = filteredItems.filter(item => item.categoryIds.includes(categoryFilter));
        }

        const filteredIds = filteredItems.map(item => item.id);
        let visibleCount = 0;
        itemsData.forEach(item => {
            const isVisible = filteredIds.includes(item.id);
            item.element.style.display = isVisible ? '' : 'none';
            if (isVisible) visibleCount++;
        });

        if (itemsContainer && itemsContainer.parentElement) {
            if (visibleCount === 0 && !itemsContainer.parentElement.querySelector('.no-content')) {
                const noContentDiv = document.createElement('div');
                noContentDiv.className = 'no-content';
                noContentDiv.innerHTML = '<i data-lucide="inbox"></i><p>Nessun articolo trovato</p>';
                itemsContainer.after(noContentDiv);
            } else if (visibleCount > 0) {
                const noContent = itemsContainer.parentElement.querySelector('.no-content');
                if (noContent) noContent.remove();
            }
        }

        if (window.lucide) lucide.createIcons();
    }

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
});
</script>
