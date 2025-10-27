<?php
/**
 * Template Name: Pagina Contatti
 * Rubrica contatti dipendenti con ricerca e filtri
 * 
 * @package Meridiana Child
 */

if (!defined('ABSPATH')) exit;

get_header();
?>

<div class="content-wrapper">
    <?php 
    // Include navigation (mobile + desktop)
    get_template_part('templates/parts/navigation/mobile-bottom-nav');
    get_template_part('templates/parts/navigation/desktop-sidebar');
    ?>
    
    <main class="page-contatti">
        <div class="contatti-container">
            
            <!-- Header -->
            <div class="page-header">
                <a href="#" onclick="history.back(); return false;" class="back-link">
                    <i data-lucide="arrow-left"></i>
                    <span>Torna indietro</span>
                </a>
            </div>

            <!-- SEARCH BAR + TOGGLE FILTRI -->
            <div class="docs-search-container">
                <div class="docs-search-wrapper">
                    <div class="search-input-group">
                        <i data-lucide="search"></i>
                        <input
                            type="text"
                            id="searchContacts"
                            class="docs-search-input"
                            placeholder="Barra di ricerca"
                            aria-label="Cerca per nome"
                            autocomplete="off">
                        <button id="clearSearch" class="docs-search-clear" aria-label="Pulisci ricerca"><i data-lucide="x"></i></button>
                    </div>
                </div>
                <button
                    id="contattiFilterToggle"
                    class="docs-filter-toggle"
                    aria-expanded="false"
                    aria-controls="contattiFiltersPanel"
                    aria-label="Mostra/nascondi filtri">
                    <i data-lucide="filter"></i>
                    <span class="docs-filter-toggle__text">Filtri</span>
                </button>
            </div>

            <!-- FILTRI COLLASSABILI PANEL -->
            <div
                id="contattiFiltersPanel"
                class="docs-filters-panel"
                aria-hidden="true">

                <!-- FILTRI TASSONOMIE -->
                <div class="docs-filters">
                    <!-- Filtro: Ruolo -->
                    <div class="filter-group">
                        <label for="filterRuolo" class="filter-group__label">Ruolo</label>
                        <select id="filterRuolo" class="docs-filter-select">
                            <option value="" selected>Tutti</option>
                            <!-- Popolato dinamicamente da JavaScript -->
                        </select>
                    </div>

                    <!-- Filtro: Unità d'Offerta -->
                    <div class="filter-group">
                        <label for="filterUDO" class="filter-group__label">Unità d'offerta</label>
                        <select id="filterUDO" class="docs-filter-select">
                            <option value="" selected>Tutte</option>
                            <option value="ambulatori">Ambulatori</option>
                            <option value="ap">AP</option>
                            <option value="cdi">CDI</option>
                            <option value="cure_domiciliari">Cure Domiciliari</option>
                            <option value="hospice">Hospice</option>
                            <option value="paese">Paese</option>
                            <option value="r20">R20</option>
                            <option value="rsa">RSA</option>
                            <option value="rsa_aperta">RSA Aperta</option>
                            <option value="rsd">RSD</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Loading -->
            <div class="loading-state" id="loadingContacts" style="display: none;">
                <div class="spinner"></div>
                <p>Caricamento contatti...</p>
            </div>
            
            <!-- Lista Contatti -->
            <div class="contatti-list" id="contattiList">
                <!-- Popolato via JavaScript -->
            </div>
            
            <!-- No Results -->
            <div class="docs-no-results" id="noResults" style="display: none;">
                <i data-lucide="user-x"></i>
                <p>Nessun contatto trovato</p>
            </div>

        </div>
    </main>
</div>

<!-- Fuse.js Library -->
<script src="https://cdn.jsdelivr.net/npm/fuse.js@7.0.0/dist/fuse.min.js"></script>

<script>
(function() {
    'use strict';

    // =========================================================================
    // DATI CARICATI DA PHP - CPT Organigramma
    // =========================================================================
    const allContacts = <?php
        $args = array(
            'post_type' => 'organigramma',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        );

        $query = new WP_Query($args);
        $contacts_data = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                $post_id = get_the_ID();
                $title = get_the_title();

                // Divide titolo in nome/cognome (formato "Nome Cognome")
                $parts = explode(' ', trim($title), 2);
                $nome = isset($parts[0]) ? $parts[0] : '';
                $cognome = isset($parts[1]) ? $parts[1] : '';

                $image_field = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                $image_url = $image_field ? $image_field : '';

                $contacts_data[] = array(
                    'id' => $post_id,
                    'nome' => $nome,
                    'cognome' => $cognome,
                    'display_name' => strtolower($title),
                    'display_name_full' => $title,
                    'ruolo' => get_field('ruolo') ?: '',
                    'udo' => get_field('udo_riferimento') ?: '',
                    'email' => get_field('email_aziendale') ?: '',
                    'telefono' => get_field('telefono_aziendale') ?: '',
                    'image' => $image_url,
                );
            }
            wp_reset_postdata();
        }
        echo json_encode($contacts_data);
    ?>;

    // =========================================================================
    // DOM ELEMENTS
    // =========================================================================
    const searchInput = document.getElementById('searchContacts');
    const searchClear = document.getElementById('clearSearch');
    const filterToggle = document.getElementById('contattiFilterToggle');
    const filtersPanel = document.getElementById('contattiFiltersPanel');
    const filterRuolo = document.getElementById('filterRuolo');
    const filterUDO = document.getElementById('filterUDO');
    const contattiList = document.getElementById('contattiList');
    const noResults = document.getElementById('noResults');

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
    const contactsData = allContacts.map(contact => ({
        id: contact.id,
        displayName: contact.display_name,
        nome: contact.nome.toLowerCase(),
        cognome: contact.cognome.toLowerCase(),
        ruolo: contact.ruolo.toLowerCase(),
        udo: contact.udo,
        element: {
            id: contact.id,
            nome: contact.nome,
            cognome: contact.cognome,
            display_name_full: contact.display_name_full,
            ruolo: contact.ruolo,
            udo: contact.udo,
            email: contact.email,
            telefono: contact.telefono,
            image: contact.image
        }
    }));

    // =========================================================================
    // FUSE.JS CONFIGURATION
    // =========================================================================
    const fuse = new Fuse(contactsData, {
        keys: ['displayName', 'nome', 'cognome'],
        threshold: 0.4,
        minMatchCharLength: 2,
        includeScore: true,
        ignoreLocation: true
    });

    // =========================================================================
    // FORMAT UDO
    // =========================================================================
    function formatUDO(udo) {
        const labels = {
            'ambulatori': 'Ambulatori',
            'ap': 'AP',
            'cdi': 'CDI',
            'cure_domiciliari': 'Cure Domiciliari',
            'hospice': 'Hospice',
            'paese': 'Paese',
            'r20': 'R20',
            'rsa': 'RSA',
            'rsa_aperta': 'RSA Aperta',
            'rsd': 'RSD'
        };
        return labels[udo] || udo;
    }

    // =========================================================================
    // RENDER CONTATTI
    // =========================================================================
    function renderContacts(contacts) {
        if (contacts.length === 0) {
            contattiList.innerHTML = '';
            noResults.style.display = 'flex';
            return;
        }

        noResults.style.display = 'none';

        contattiList.innerHTML = contacts.map(contact => `
            <div class="contact-card">
                <div class="contact-card__avatar">
                    ${contact.element.image ? `<img src="${contact.element.image}" alt="${contact.element.cognome} ${contact.element.nome}" class="contact-card__avatar-img">` : `<i data-lucide="user" style="width: 24px; height: 24px;"></i>`}
                </div>
                <div class="contact-card__info">
                    <h3 class="contact-card__name">${contact.element.cognome} ${contact.element.nome}</h3>
                    <div class="contact-card__meta">
                        ${contact.element.ruolo ? `<span class="contact-meta__ruolo">${contact.element.ruolo}</span>` : ''}
                        ${contact.element.udo ? `<span class="contact-meta__udo">${formatUDO(contact.element.udo)}</span>` : ''}
                    </div>
                </div>
                <div class="contact-card__actions">
                    ${contact.element.telefono ? `<a href="tel:${contact.element.telefono}" class="btn btn-sm btn-outline"><i data-lucide="phone"></i><span>Interno</span></a>` : ''}
                    ${contact.element.email ? `<a href="mailto:${contact.element.email}" class="btn btn-sm btn-primary"><i data-lucide="mail"></i><span>Email</span></a>` : ''}
                </div>
            </div>
        `).join('');

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    // =========================================================================
    // FILTER LOGIC
    // =========================================================================
    function filterContacts() {
        const searchTerm = searchInput ? searchInput.value.trim() : '';
        const ruoloFilter = filterRuolo ? filterRuolo.value : '';
        const udoFilter = filterUDO ? filterUDO.value : '';

        let filteredContacts = [...contactsData];

        // Ricerca Fuse.js
        if (searchTerm && searchTerm.length >= 2) {
            const fuseResults = fuse.search(searchTerm);
            const fuseIds = fuseResults.map(result => result.item.id);
            filteredContacts = filteredContacts.filter(contact => fuseIds.includes(contact.id));
        }

        // Filtro Ruolo
        if (ruoloFilter) {
            filteredContacts = filteredContacts.filter(contact =>
                contact.element.ruolo.toLowerCase() === ruoloFilter.toLowerCase()
            );
        }

        // Filtro UDO
        if (udoFilter) {
            filteredContacts = filteredContacts.filter(contact => contact.element.udo === udoFilter);
        }

        renderContacts(filteredContacts);
    }

    // =========================================================================
    // POPULATE RUOLO FILTER
    // =========================================================================
    function populateRuoloFilter() {
        const ruoli = [...new Set(allContacts.map(c => c.ruolo).filter(Boolean))].sort();
        ruoli.forEach(ruolo => {
            const option = document.createElement('option');
            option.value = ruolo;
            option.textContent = ruolo;
            filterRuolo.appendChild(option);
        });
    }

    // =========================================================================
    // EVENT LISTENERS
    // =========================================================================
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            if (searchClear) searchClear.style.display = this.value ? 'block' : 'none';
            filterContacts();
        });
    }

    if (searchClear) {
        searchClear.addEventListener('click', function(e) {
            e.preventDefault();
            if (searchInput) searchInput.value = '';
            searchClear.style.display = 'none';
            filterContacts();
            if (searchInput) searchInput.focus();
        });
    }

    if (filterRuolo) filterRuolo.addEventListener('change', filterContacts);
    if (filterUDO) filterUDO.addEventListener('change', filterContacts);

    // =========================================================================
    // INIT
    // =========================================================================
    populateRuoloFilter();
    renderContacts(contactsData);
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
})();
</script>

<?php
get_footer();
