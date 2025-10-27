<?php
$users_query = new WP_User_Query([
    'orderby' => 'display_name',
    'order' => 'ASC',
    'number' => 50,
]);
$users = $users_query->get_results();

$default_stato_choices = [
    'attivo' => 'Attivo',
    'sospeso' => 'Sospeso',
    'licenziato' => 'Licenziato',
];
$stato_field_def = function_exists('acf_get_field') ? acf_get_field('field_stato_utente') : null;
$stato_choices = is_array($stato_field_def) && !empty($stato_field_def['choices']) ? $stato_field_def['choices'] : $default_stato_choices;

$status_badge_classes = [
    'attivo' => 'badge badge-sm badge-status-active',
    'sospeso' => 'badge badge-sm badge-status-pending',
    'licenziato' => 'badge badge-sm badge-status-expired',
];

// Carica profili e UDO unici dagli utenti
$profili_unique = [];
$udo_unique = [];
foreach ($users as $user) {
    $user_meta_key = 'user_' . $user->ID;
    $profilo = get_field('profilo_professionale', $user_meta_key);
    $udo = get_field('udo_riferimento', $user_meta_key);
    if ($profilo && !in_array($profilo, $profili_unique)) {
        $profili_unique[] = $profilo;
    }
    if ($udo && !in_array($udo, $udo_unique)) {
        $udo_unique[] = $udo;
    }
}
sort($profili_unique);
sort($udo_unique);
?>
<div class="tab-header">
    <h2>Utenti</h2>
    <button class="btn btn-primary" @click="openFormModal('utenti', 'new')"><i data-lucide="plus"></i> Nuovo Utente</button>
</div>

<!-- SEARCH BAR + TOGGLE FILTRI -->
<div class="docs-search-container">
    <!-- Search -->
    <div class="docs-search-wrapper">
        <div class="search-input-group">
            <i data-lucide="search"></i>
            <input
                type="text"
                id="utenti-search"
                class="docs-search-input"
                placeholder="Barra di ricerca"
                aria-label="Cerca utenti"
            >
            <button id="utenti-search-clear" class="docs-search-clear" aria-label="Pulisci ricerca">
                <i data-lucide="x"></i>
            </button>
        </div>
    </div>

    <!-- Toggle Filtri Button -->
    <button
        id="utenti-filter-toggle"
        class="docs-filter-toggle"
        aria-expanded="false"
        aria-controls="utenti-filters-panel"
        aria-label="Mostra/nascondi filtri"
    >
        <i data-lucide="filter"></i>
        <span class="docs-filter-toggle__text">Filtri</span>
    </button>
</div>

<!-- FILTRI COLLASSABILI PANEL -->
<div
    id="utenti-filters-panel"
    class="docs-filters-panel"
    aria-hidden="true"
>
    <!-- FILTRI STATO -->
    <div class="docs-type-filters">
        <label class="docs-type-label">Stato:</label>
        <div class="docs-type-buttons">
            <button class="docs-type-btn docs-type-btn--active" data-status="all" aria-label="Mostra tutti gli utenti">
                Tutti
            </button>
            <?php foreach ($stato_choices as $stato_key => $stato_label): ?>
            <button class="docs-type-btn" data-status="<?php echo esc_attr($stato_key); ?>" aria-label="Mostra <?php echo esc_attr($stato_label); ?>">
                <?php echo esc_html($stato_label); ?>
            </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- FILTRI PROFILO E UDO -->
    <div class="docs-filters">
        <!-- Filter: Profilo Professionale -->
        <?php if (!empty($profili_unique)): ?>
        <div class="filter-group">
            <label for="filter-profilo-utenti" class="filter-group__label">Profilo Professionale</label>
            <select id="filter-profilo-utenti" class="docs-filter-select">
                <option value="" selected>Tutti</option>
                <?php foreach ($profili_unique as $profilo): ?>
                <option value="<?php echo esc_attr($profilo); ?>">
                    <?php echo esc_html($profilo); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <!-- Filter: Unità d'Offerta -->
        <?php if (!empty($udo_unique)): ?>
        <div class="filter-group">
            <label for="filter-udo-utenti" class="filter-group__label">Unità d'offerta</label>
            <select id="filter-udo-utenti" class="docs-filter-select">
                <option value="" selected>Tutte</option>
                <?php foreach ($udo_unique as $udo): ?>
                <option value="<?php echo esc_attr($udo); ?>">
                    <?php echo esc_html($udo); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($users)): ?>

    <!-- CARD LAYOUT - Card List -->
    <div class="users-cards-container laptop-only">
        <?php foreach ($users as $user):
            $user_meta_key = 'user_' . $user->ID;
            $full_name = trim($user->first_name . ' ' . $user->last_name);
            if ($full_name === '') {
                $full_name = $user->display_name ?: $user->user_login;
            }
            $stato_field = get_field_object('field_stato_utente', $user_meta_key);
            $stato_value = is_array($stato_field) ? ($stato_field['value'] ?? '') : '';
            $stato_label = $stato_value && isset($stato_field['choices'][$stato_value])
                ? $stato_field['choices'][$stato_value]
                : ($stato_value && isset($stato_choices[$stato_value]) ? $stato_choices[$stato_value] : '');
            $profilo = get_field('profilo_professionale', $user_meta_key);
            $udo = get_field('udo_riferimento', $user_meta_key);
            $link_autologin = get_field('link_autologin_esterno', $user_meta_key);
            $codice_fiscale = get_field('codice_fiscale', $user_meta_key);

            $status_key = is_string($stato_value) ? $stato_value : '';
            $status_badge_class = $status_badge_classes[$status_key] ?? 'badge badge-sm badge-secondary';
        ?>
        <div class="user-card"
             data-user-id="<?php echo $user->ID; ?>"
             data-name="<?php echo esc_attr(strtolower($full_name)); ?>"
             data-status="<?php echo esc_attr($status_key); ?>"
             data-profilo="<?php echo esc_attr($profilo); ?>"
             data-udo="<?php echo esc_attr($udo); ?>">
            <div class="user-card__header" data-toggle="card-<?php echo $user->ID; ?>">
                <div class="user-card__info">
                    <div class="user-card__title"><?php echo esc_html($full_name); ?></div>
                    <div class="user-card__meta">
                        <?php if ($stato_label): ?>
                            <span class="user-card__badge <?php echo esc_attr($status_badge_class); ?>"><?php echo esc_html($stato_label); ?></span>
                        <?php endif; ?>
                        <?php if ($profilo): ?>
                            <span class="user-card__separator">•</span>
                            <span class="user-card__profilo-text"><?php echo esc_html($profilo); ?></span>
                        <?php endif; ?>
                        <?php if ($udo): ?>
                            <span class="user-card__separator">•</span>
                            <span class="user-card__udo-text"><?php echo esc_html($udo); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="user-card__actions-group">
                    <button class="btn-icon" @click.stop="openFormModal('utenti', 'edit', <?php echo $user->ID; ?>)" title="Modifica"><i data-lucide="edit-2"></i></button>
                    <button class="btn-icon" @click.stop="resetUserPassword(<?php echo $user->ID; ?>)" title="Reset Password"><i data-lucide="key"></i></button>
                    <button class="btn-icon" @click.stop="deleteUser(<?php echo $user->ID; ?>)" title="Elimina"><i data-lucide="trash-2"></i></button>
                    <button class="user-card__toggle" aria-label="Espandi dettagli"><i data-lucide="chevron-down"></i></button>
                </div>
            </div>
            <div class="user-card__content" id="card-<?php echo $user->ID; ?>">
                <div class="user-card__row">
                    <span class="user-card__label">Email</span>
                    <span class="user-card__value"><?php echo esc_html($user->user_email); ?></span>
                </div>
                <div class="user-card__row">
                    <span class="user-card__label">Link Autologin</span>
                    <span class="user-card__value">
                        <?php if (!empty($link_autologin)): ?>
                            <a href="<?php echo esc_url($link_autologin); ?>" class="link-status link-status--available" target="_blank" rel="noopener noreferrer">
                                <i data-lucide="arrow-up-right"></i> Apri
                            </a>
                        <?php else: ?>
                            <span class="text-gray-500">Non disponibile</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="user-card__row">
                    <span class="user-card__label">Codice Fiscale</span>
                    <span class="user-card__value"><?php echo $codice_fiscale ? esc_html($codice_fiscale) : 'N/D'; ?></span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

<?php else: ?>
<div class="no-content"><i data-lucide="inbox"></i><p>Nessun utente trovato</p></div>
<?php endif; ?>

<!-- Fuse.js Library -->
<script src="https://cdn.jsdelivr.net/npm/fuse.js@7.0.0/dist/fuse.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // =================================================================
    // TOGGLE FILTRI PANEL
    // =================================================================
    const filterToggle = document.getElementById('utenti-filter-toggle');
    const filtersPanel = document.getElementById('utenti-filters-panel');

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
    // USERS FILTERS LOGIC
    // =================================================================
    const searchInput = document.getElementById('utenti-search');
    const searchClear = document.getElementById('utenti-search-clear');
    const filterProfilo = document.getElementById('filter-profilo-utenti');
    const filterUdo = document.getElementById('filter-udo-utenti');
    const statusButtons = document.querySelectorAll('[data-status]');
    const usersContainer = document.querySelector('.users-cards-container');
    const userCards = usersContainer ? usersContainer.querySelectorAll('.user-card') : [];

    let selectedStatus = 'all';

    // Preparazione dati per Fuse.js
    const usersData = Array.from(userCards).map(item => {
        return {
            id: item.dataset.userId,
            name: item.dataset.name,
            status: item.dataset.status,
            profilo: item.dataset.profilo,
            udo: item.dataset.udo,
            element: item
        };
    });

    // Configurazione Fuse.js
    const fuseOptions = {
        keys: ['name'],
        threshold: 0.4,
        minMatchCharLength: 2,
        includeScore: true,
        ignoreLocation: true
    };
    const fuse = new Fuse(usersData, fuseOptions);

    // Funzione principale di filtro
    function filterUsers() {
        const searchTerm = searchInput.value.trim();
        const profiloFilter = filterProfilo ? filterProfilo.value : '';
        const udoFilter = filterUdo ? filterUdo.value : '';

        let filteredItems = [...usersData];

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

        // Filtro Profilo
        if (profiloFilter) {
            filteredItems = filteredItems.filter(item => item.profilo === profiloFilter);
        }

        // Filtro UDO
        if (udoFilter) {
            filteredItems = filteredItems.filter(item => item.udo === udoFilter);
        }

        // Aggiorna DOM
        const filteredIds = filteredItems.map(item => item.id);
        let visibleCount = 0;

        usersData.forEach(item => {
            const isVisible = filteredIds.includes(item.id);
            item.element.style.display = isVisible ? '' : 'none';
            if (isVisible) visibleCount++;
        });

        // Mostra/nascondi "no results"
        if (visibleCount === 0 && !usersContainer.parentElement.querySelector('.no-content')) {
            const noContentDiv = document.createElement('div');
            noContentDiv.className = 'no-content';
            noContentDiv.innerHTML = '<i data-lucide="inbox"></i><p>Nessun utente trovato</p>';
            usersContainer.after(noContentDiv);
        } else if (visibleCount > 0) {
            const noContent = usersContainer.parentElement.querySelector('.no-content');
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
            filterUsers();
        });
    }

    if (searchClear) {
        searchClear.addEventListener('click', function(e) {
            e.preventDefault();
            if (searchInput) searchInput.value = '';
            searchClear.style.display = 'none';
            filterUsers();
            if (searchInput) searchInput.focus();
        });
    }

    if (filterProfilo) filterProfilo.addEventListener('change', filterUsers);
    if (filterUdo) filterUdo.addEventListener('change', filterUsers);

    // Event listeners per filtri stato
    statusButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            statusButtons.forEach(b => b.classList.remove('docs-type-btn--active'));
            this.classList.add('docs-type-btn--active');
            selectedStatus = this.dataset.status;
            filterUsers();
        });
    });

    // =================================================================
    // CARD TOGGLE LOGIC
    // =================================================================
    document.querySelectorAll('.user-card__header').forEach(header => {
        header.addEventListener('click', function() {
            const cardId = this.getAttribute('data-toggle');
            const content = document.getElementById(cardId);
            const toggle = this.querySelector('.user-card__toggle');

            if (content && toggle) {
                content.classList.toggle('open');
                toggle.classList.toggle('open');
            }
        });
    });
});
</script>
