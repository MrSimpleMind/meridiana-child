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
            
            
            <!-- Search & Filters -->
            <div class="contatti-filters">
                
                <!-- Barra di ricerca -->
                <div class="search-box">
                    <i data-lucide="search"></i>
                    <input 
                        type="text" 
                        id="searchContacts" 
                        class="search-input" 
                        placeholder="Cerca per nome..."
                        autocomplete="off">
                    <button type="button" class="search-clear" id="clearSearch" style="display: none;">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                
                <!-- Filtri -->
                <div class="filter-group">
                    <div class="filter-item">
                        <label for="filterRuolo" class="filter-label">
                            <i data-lucide="briefcase" class="filter-label__icon"></i>
                            Ruolo
                        </label>
                        <div class="filter-wrapper">
                            <select id="filterRuolo" class="filter-select">
                                <option value="">Seleziona</option>
                                <!-- Popolato dinamicamente da JavaScript -->
                            </select>
                            <div class="filter-badge" id="filterRuoloBadge">
                                <span>Tutti</span>
                                <i data-lucide="chevron-down" class="filter-badge__icon"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="filter-item">
                        <label for="filterUDO" class="filter-label">
                            <i data-lucide="building-2" class="filter-label__icon"></i>
                            Unità d'offerta
                        </label>
                        <div class="filter-wrapper">
                            <select id="filterUDO" class="filter-select">
                                <option value="">Seleziona</option>
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
                            <div class="filter-badge" id="filterUDOBadge">
                                <span>Tutte</span>
                                <i data-lucide="chevron-down" class="filter-badge__icon"></i>
                            </div>
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
            <div class="no-results" id="noResults" style="display: none;">
                <i data-lucide="user-x"></i>
                <p>Nessun contatto trovato</p>
            </div>
            
        </div>
    </main>
</div>

<script>
// Dati contatti (CPT Organigramma) - Popolati da PHP
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
                'display_name' => $title,
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

console.log('Contatti caricati:', allContacts);

let filteredContacts = [...allContacts];

// Render contatti
function renderContacts(contacts) {
    const list = document.getElementById('contattiList');
    const noResults = document.getElementById('noResults');
    
    if (contacts.length === 0) {
        list.innerHTML = '';
        noResults.style.display = 'flex';
        return;
    }
    
    noResults.style.display = 'none';
    
    list.innerHTML = contacts.map(contact => `
        <div class="contact-card">
            <div class="contact-card__avatar">
                ${contact.image ? `<img src="${contact.image}" alt="${contact.cognome} ${contact.nome}" class="contact-card__avatar-img">` : `<i data-lucide="user" style="width: 24px; height: 24px;"></i>`}
            </div>
            <div class="contact-card__info">
                <h3 class="contact-card__name">${contact.cognome} ${contact.nome}</h3>
                <div class="contact-card__meta">
                    ${contact.ruolo ? `<span class="contact-meta__ruolo">${contact.ruolo}</span>` : ''}
                    ${contact.udo ? `<span class="contact-meta__udo">${formatUDO(contact.udo)}</span>` : ''}
                </div>
            </div>
            <div class="contact-card__actions">
                ${contact.telefono ? `<a href="tel:${contact.telefono}" class="btn btn-sm btn-outline"><i data-lucide="phone"></i><span>Interno</span></a>` : ''}
                ${contact.email ? `<a href="mailto:${contact.email}" class="btn btn-sm btn-primary"><i data-lucide="mail"></i><span>Email</span></a>` : ''}
            </div>
        </div>
    `).join('');
    
    // Re-initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

// Format UDO
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

// Filtra contatti
function filterContacts() {
    const searchTerm = document.getElementById('searchContacts').value.toLowerCase();
    const ruolo = document.getElementById('filterRuolo').value;
    const udo = document.getElementById('filterUDO').value;
    
    filteredContacts = allContacts.filter(contact => {
        const matchSearch = !searchTerm || 
            contact.nome.toLowerCase().includes(searchTerm) ||
            contact.cognome.toLowerCase().includes(searchTerm) ||
            contact.display_name.toLowerCase().includes(searchTerm);
        
        const matchRuolo = !ruolo || contact.ruolo.toLowerCase() === ruolo.toLowerCase();
        const matchUDO = !udo || contact.udo === udo;
        
        return matchSearch && matchRuolo && matchUDO;
    });
    
    renderContacts(filteredContacts);
    
    // Show/hide clear button
    document.getElementById('clearSearch').style.display = searchTerm ? 'flex' : 'none';
}

// Popola filtri dropdown dinamicamente
function populateFilters() {
    // Popola dropdown Ruoli (unici)
    const ruoli = [...new Set(allContacts.map(c => c.ruolo).filter(Boolean))];
    const ruoloSelect = document.getElementById('filterRuolo');
    ruoli.forEach(ruolo => {
        const option = document.createElement('option');
        option.value = ruolo;
        option.textContent = ruolo;
        ruoloSelect.appendChild(option);
    });
}

// Aggiorna badge quando cambia selezione
function updateFilterBadges() {
    const ruoloSelect = document.getElementById('filterRuolo');
    const udoSelect = document.getElementById('filterUDO');
    const ruoloBadge = document.getElementById('filterRuoloBadge');
    const udoBadge = document.getElementById('filterUDOBadge');
    
    // Aggiorna badge Ruolo
    if (ruoloSelect.value) {
        const selectedText = ruoloSelect.options[ruoloSelect.selectedIndex].text;
        ruoloBadge.querySelector('span').textContent = selectedText || 'Seleziona';
    } else {
        ruoloBadge.querySelector('span').textContent = 'Tutti';
    }
    
    // Aggiorna badge UDO
    if (udoSelect.value) {
        const selectedText = udoSelect.options[udoSelect.selectedIndex].text;
        udoBadge.querySelector('span').textContent = selectedText || 'Seleziona';
    } else {
        udoBadge.querySelector('span').textContent = 'Tutte';
    }
}

// Event listeners
document.getElementById('searchContacts').addEventListener('input', filterContacts);
document.getElementById('filterRuolo').addEventListener('change', function() {
    updateFilterBadges();
    filterContacts();
});
document.getElementById('filterUDO').addEventListener('change', function() {
    updateFilterBadges();
    filterContacts();
});
document.getElementById('clearSearch').addEventListener('click', function() {
    document.getElementById('searchContacts').value = '';
    filterContacts();
});

// Initial setup
populateFilters();
updateFilterBadges(); // Inizializza badge
renderContacts(allContacts);

// Initialize Lucide on load
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}
</script>

<?php
get_footer();
