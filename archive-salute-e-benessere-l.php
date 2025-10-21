<?php
/**
 * Archive Template: Salute e Benessere
 * 
 * Stesso layout di archive.php (articoli)
 * Con ricerca e filtri per Salute e Benessere
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
    
    <main class="archive-page archive-salute-page">
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
                        id="searchSalute" 
                        class="search-input" 
                        placeholder="Cerca articoli..."
                        autocomplete="off">
                </div>
            </div>
            
            <!-- Results Count -->
            <div class="results-count">
                <span id="resultsCountText">Caricamento...</span>
            </div>
            
            <!-- Salute List -->
            <div class="articoli-list" id="saluteList">
                <!-- Popolato via JavaScript -->
            </div>
            
            <!-- No Results -->
            <div class="no-results" id="noResults" style="display: none;">
                <i data-lucide="inbox"></i>
                <p>Nessun articolo trovato</p>
            </div>
            
        </div>
    </main>
</div>

<script>
(function() {
    'use strict';
    
    // Dati salute e benessere caricati da PHP
    const allSalute = <?php 
        $args = array(
            'post_type' => 'salute-e-benessere-l',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        $query = new WP_Query($args);
        $salute_data = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                
                // Featured image
                $featured_image = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                
                // Excerpt or fallback to excerpt from contenuto field
                $excerpt = get_the_excerpt();
                if (!$excerpt) {
                    $contenuto_raw = get_field('contenuto');
                    $excerpt = $contenuto_raw ? wp_trim_words(strip_tags($contenuto_raw), 20) : '';
                }
                
                // Categoria
                $categories = get_the_category();
                $category_name = !empty($categories) ? $categories[0]->name : 'Salute e Benessere';
                
                $salute_data[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'excerpt' => $excerpt,
                    'permalink' => get_the_permalink(),
                    'date' => get_the_date('d M Y'),
                    'category' => $category_name,
                    'image' => $featured_image,
                );
            }
            wp_reset_postdata();
        }
        echo json_encode($salute_data);
    ?>;
    
    // DOM Elements
    const searchInput = document.getElementById('searchSalute');
    const saluteList = document.getElementById('saluteList');
    const noResults = document.getElementById('noResults');
    const resultsCountText = document.getElementById('resultsCountText');
    
    let filteredSalute = [...allSalute];
    
    /**
     * Render salute
     */
    function renderSalute(salute) {
        if (salute.length === 0) {
            saluteList.innerHTML = '';
            noResults.style.display = 'flex';
            resultsCountText.textContent = 'Nessun risultato';
            return;
        }
        
        noResults.style.display = 'none';
        resultsCountText.textContent = salute.length === 1 ? '1 risultato' : `${salute.length} risultati`;
        
        saluteList.innerHTML = salute.map(article => `
            <a href="${article.permalink}" class="articolo-item">
                ${article.image ? `
                    <div class="articolo-image">
                        <img src="${article.image}" alt="${article.title}" loading="lazy">
                    </div>
                ` : ''}
                
                <div class="articolo-content">
                    <h3 class="articolo-title">${article.title}</h3>
                    <p class="articolo-excerpt">${article.excerpt}</p>
                </div>
                <div class="articolo-arrow">
                    <i data-lucide="chevron-right"></i>
                </div>
                
                <div class="articolo-meta">
                    <span class="articolo-date">
                        <i data-lucide="calendar"></i>
                        ${article.date}
                    </span>
                    <span class="articolo-category">
                        <i data-lucide="tag"></i>
                        ${article.category}
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
     * Filter salute
     */
    function filterSalute() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        filteredSalute = allSalute.filter(article => {
            if (!searchTerm) return true;
            
            return article.title.toLowerCase().includes(searchTerm) ||
                   article.excerpt.toLowerCase().includes(searchTerm) ||
                   article.category.toLowerCase().includes(searchTerm);
        });
        
        renderSalute(filteredSalute);
    }
    
    /**
     * Event listeners
     */
    searchInput.addEventListener('input', filterSalute);
    
    /**
     * Init
     */
    renderSalute(allSalute);
    
    // Init Lucide
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
})();
</script>

<?php get_footer(); ?>
