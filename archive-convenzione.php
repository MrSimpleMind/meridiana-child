<?php
/**
 * Archive Template: Convenzioni
 * 
 * Stesso layout di archive.php (articoli)
 * Con ricerca e filtri per Convenzioni
 * 
 * @package Meridiana Child Theme
 */

get_header();
?>

<div class="content-wrapper">
    <?php 
    get_template_part('templates/parts/navigation/mobile-bottom-nav');
    get_template_part('templates/parts/navigation/desktop-sidebar');
    ?>
    
    <main class="archive-page archive-convenzioni-page">
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
            
            <!-- Search Box -->
            <div class="search-wrapper">
                <div class="search-field">
                    <i data-lucide="search"></i>
                    <input 
                        type="text" 
                        id="searchConvenzioni" 
                        class="search-input" 
                        placeholder="Cerca convenzioni..."
                        autocomplete="off">
                </div>
            </div>
            
            <!-- Results Count -->
            <div class="results-count">
                <span id="resultsCountText">Caricamento...</span>
            </div>
            
            <!-- Convenzioni List -->
            <div class="articoli-list" id="convenzioniList">
                <!-- Popolato via JavaScript -->
            </div>
            
            <!-- No Results -->
            <div class="no-results" id="noResults" style="display: none;">
                <i data-lucide="inbox"></i>
                <p>Nessuna convenzione trovata</p>
            </div>
            
        </div>
    </main>
</div>

<script>
(function() {
    'use strict';
    
    // Dati convenzioni caricati da PHP
    const allConvenzioni = <?php 
        $args = array(
            'post_type' => 'convenzione',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'convenzione_attiva',
                    'value' => '1',
                    'compare' => '='
                )
            ),
            'orderby' => 'title',
            'order' => 'ASC'
        );
        
        $query = new WP_Query($args);
        $convenzioni_data = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                
                // Immagine da ACF
                $immagine_id = get_field('immagine_evidenza');
                $immagine = $immagine_id ? wp_get_attachment_image_url($immagine_id, 'medium') : false;
                
                // Descrizione da ACF
                $descrizione_raw = get_field('descrizione');
                $descrizione = $descrizione_raw ? wp_trim_words(strip_tags($descrizione_raw), 20) : get_the_excerpt();
                
                $convenzioni_data[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'excerpt' => $descrizione,
                    'permalink' => get_the_permalink(),
                    'date' => get_the_date('d M Y'),
                    'category' => 'Convenzione',
                    'image' => $immagine,
                );
            }
            wp_reset_postdata();
        }
        echo json_encode($convenzioni_data);
    ?>;
    
    // DOM Elements
    const searchInput = document.getElementById('searchConvenzioni');
    const convenzioniList = document.getElementById('convenzioniList');
    const noResults = document.getElementById('noResults');
    const resultsCountText = document.getElementById('resultsCountText');
    
    let filteredConvenzioni = [...allConvenzioni];
    
    /**
     * Render convenzioni
     */
    function renderConvenzioni(convenzioni) {
        if (convenzioni.length === 0) {
            convenzioniList.innerHTML = '';
            noResults.style.display = 'flex';
            resultsCountText.textContent = 'Nessun risultato';
            return;
        }
        
        noResults.style.display = 'none';
        resultsCountText.textContent = convenzioni.length === 1 ? '1 risultato' : `${convenzioni.length} risultati`;
        
        convenzioniList.innerHTML = convenzioni.map(convenzione => `
            <a href="${convenzione.permalink}" class="articolo-item">
                ${convenzione.image ? `
                    <div class="articolo-image">
                        <img src="${convenzione.image}" alt="${convenzione.title}" loading="lazy">
                    </div>
                ` : ''}
                
                <div class="articolo-content">
                    <h3 class="articolo-title">${convenzione.title}</h3>
                    <p class="articolo-excerpt">${convenzione.excerpt}</p>
                </div>
                <div class="articolo-arrow">
                    <i data-lucide="chevron-right"></i>
                </div>
                
                <div class="articolo-meta">
                    <span class="articolo-date">
                        <i data-lucide="calendar"></i>
                        ${convenzione.date}
                    </span>
                    <span class="articolo-category">
                        <i data-lucide="tag"></i>
                        ${convenzione.category}
                    </span>
                </div>
            </a>
        `).join('');
        
        // Re-init Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
    
    /**
     * Filter convenzioni
     */
    function filterConvenzioni() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        filteredConvenzioni = allConvenzioni.filter(convenzione => {
            if (!searchTerm) return true;
            
            return convenzione.title.toLowerCase().includes(searchTerm) ||
                   convenzione.excerpt.toLowerCase().includes(searchTerm);
        });
        
        renderConvenzioni(filteredConvenzioni);
    }
    
    /**
     * Event listeners
     */
    searchInput.addEventListener('input', filterConvenzioni);
    
    /**
     * Init
     */
    renderConvenzioni(allConvenzioni);
    
    // Init Lucide
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
})();
</script>

<?php get_footer(); ?>
