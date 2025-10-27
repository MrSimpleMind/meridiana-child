<?php
$convenzioni_query = new WP_Query([
    'post_type' => 'convenzione',
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
    <h2>Convenzioni</h2>
    <button class="btn btn-primary" @click="openFormModal('convenzioni', 'new', 0, null)"><i data-lucide="plus"></i> Nuova Convenzione</button>
</div>

<!-- SEARCH BAR + TOGGLE FILTRI -->
<div class="docs-search-container">
    <div class="docs-search-wrapper">
        <div class="search-input-group">
            <i data-lucide="search"></i>
            <input type="text" id="convenzioni-search" class="docs-search-input" placeholder="Barra di ricerca" aria-label="Cerca convenzioni">
            <button id="convenzioni-search-clear" class="docs-search-clear" aria-label="Pulisci ricerca"><i data-lucide="x"></i></button>
        </div>
    </div>
    <button id="convenzioni-filter-toggle" class="docs-filter-toggle" aria-expanded="false" aria-controls="convenzioni-filters-panel" aria-label="Mostra/nascondi filtri">
        <i data-lucide="filter"></i> <span class="docs-filter-toggle__text">Filtri</span>
    </button>
</div>

<!-- FILTRI COLLASSABILI PANEL -->
<div id="convenzioni-filters-panel" class="docs-filters-panel" aria-hidden="true">
    <div class="docs-type-filters">
        <label class="docs-type-label">Stato:</label>
        <div class="docs-type-buttons">
            <button class="docs-type-btn docs-type-btn--active" data-status="all">Tutti</button>
            <button class="docs-type-btn" data-status="active">Attive</button>
            <button class="docs-type-btn" data-status="expired">Scadute</button>
        </div>
    </div>
    <div class="docs-filters">
        <?php if (!empty($categories_unique) && !is_wp_error($categories_unique)): ?>
        <div class="filter-group">
            <label for="filter-category-convenzioni" class="filter-group__label">Categoria</label>
            <select id="filter-category-convenzioni" class="docs-filter-select">
                <option value="" selected>Tutte</option>
                <?php foreach ($categories_unique as $cat): ?>
                <option value="<?php echo esc_attr($cat->term_id); ?>"><?php echo esc_html($cat->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($convenzioni_query->have_posts()): ?>

    <!-- CARD LAYOUT - Card List -->
    <div class="item-cards-container laptop-only">
        <?php
        $convenzioni_query->rewind_posts();
        while ($convenzioni_query->have_posts()): $convenzioni_query->the_post();
            $post_id = get_the_ID();
            $is_active = (bool) get_field('convenzione_attiva', $post_id);
            $updated_date = get_the_modified_date('d/m/Y');
            $categories = get_the_category($post_id);
            $category_ids = !empty($categories) ? implode(',', wp_list_pluck($categories, 'term_id')) : '';
            $status = $is_active ? 'active' : 'expired';
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
                        <span class="badge <?php echo $is_active ? 'badge-success' : 'badge-warning'; ?>"><?php echo $is_active ? 'Attiva' : 'Scaduta'; ?></span>
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
                    <button class="btn-icon" @click.stop="openFormModal('convenzioni', 'edit', <?php echo $post_id; ?>, null)" title="Modifica"><i data-lucide="edit-2"></i></button>
                    <button class="btn-icon" @click.stop="deleteConvenzione(<?php echo $post_id; ?>)" title="Elimina"><i data-lucide="trash-2"></i></button>
                    <a href="<?php the_permalink(); ?>" class="btn-icon" title="Visualizza" target="_blank"><i data-lucide="eye"></i></a>
                </div>
            </div>
            <div class="item-card__content" id="card-<?php echo $post_id; ?>">
            </div>
        </div>
        <?php endwhile; ?>
    </div>

<?php else: ?>
<div class="no-content"><i data-lucide="inbox"></i><p><?php esc_html_e('Nessuna convenzione trovata', 'meridiana-child'); ?></p></div>
<?php endif; wp_reset_postdata(); ?>

<script src="https://cdn.jsdelivr.net/npm/fuse.js@7.0.0/dist/fuse.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterToggle = document.getElementById('convenzioni-filter-toggle');
    const filtersPanel = document.getElementById('convenzioni-filters-panel');
    if (filterToggle && filtersPanel) {
        filterToggle.addEventListener('click', function(e) {
            e.preventDefault();
            const isExpanded = filterToggle.getAttribute('aria-expanded') === 'true';
            filterToggle.setAttribute('aria-expanded', !isExpanded);
            filtersPanel.setAttribute('aria-hidden', isExpanded);
            filtersPanel.classList.toggle('docs-filters-panel--open');
        });
    }

    const searchInput = document.getElementById('convenzioni-search');
    const searchClear = document.getElementById('convenzioni-search-clear');
    const filterCategory = document.getElementById('filter-category-convenzioni');
    const statusButtons = document.querySelectorAll('#convenzioni-filters-panel [data-status]');
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

    const fuse = new Fuse(itemsData, { keys: ['title'], threshold: 0.4, minMatchCharLength: 2 });

    function filterItems() {
        const searchTerm = searchInput.value.trim();
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

        if (visibleCount === 0 && !itemsContainer.parentElement.querySelector('.no-content')) {
            const noContentDiv = document.createElement('div');
            noContentDiv.className = 'no-content';
            noContentDiv.innerHTML = '<i data-lucide="inbox"></i><p>Nessuna convenzione trovata</p>';
            itemsContainer.after(noContentDiv);
        } else if (visibleCount > 0) {
            const noContent = itemsContainer.parentElement.querySelector('.no-content');
            if (noContent) noContent.remove();
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
