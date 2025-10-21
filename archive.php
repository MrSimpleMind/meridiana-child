<?php
/**
 * Archive Template: Comunicazioni / Articoli
 * 
 * Filtro semplice come organigramma - funzionante
 * Classes match SCSS: articles-grid, article-card
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
    
    <main class="archive-page archive-articoli-page">
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
            
            <!-- Page Title REMOVED (breadcrumb is enough) -->
            
            <!-- Search Box -->
            <div class="search-wrapper">
                <div class="search-field">
                    <i data-lucide="search"></i>
                    <input 
                        type="text" 
                        id="searchArticoli" 
                        class="search-input" 
                        placeholder="Cerca notizie..."
                        autocomplete="off">
                </div>
            </div>
            
            <!-- Results Count -->
            <div class="results-count">
                <span id="resultsCountText">Caricamento...</span>
            </div>
            
            <!-- Articoli List - Grid Container -->
            <div id="articoliList">
                <!-- Popolato via JavaScript con articles-grid -->
            </div>
            
            <!-- No Results -->
            <div class="no-results" id="noResults" style="display: none;">
                <i data-lucide="inbox"></i>
                <p>Nessuna notizia trovata</p>
            </div>
            
        </div>
    </main>
</div>

<script>
(function() {
    'use strict';
    
    // Dati articoli caricati da PHP
    const allArticles = <?php 
        $args = array(
            'post_type' => 'post',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        $query = new WP_Query($args);
        $articles_data = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                
                $title = get_the_title();
                
                // Skip meme posts
                if (stripos($title, 'meme') !== false) {
                    continue;
                }
                
                $featured_image = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                $categories = get_the_category();
                $category_name = !empty($categories) ? $categories[0]->name : 'Uncategorized';
                
                $articles_data[] = array(
                    'id' => get_the_ID(),
                    'title' => $title,
                    'excerpt' => get_the_excerpt(),
                    'permalink' => get_the_permalink(),
                    'date' => get_the_date('d M Y'),
                    'category' => $category_name,
                    'image' => $featured_image,
                );
            }
            wp_reset_postdata();
        }
        echo json_encode($articles_data);
    ?>;
    
    // DOM Elements
    const searchInput = document.getElementById('searchArticoli');
    const articoliListContainer = document.getElementById('articoliList');
    const noResults = document.getElementById('noResults');
    const resultsCountText = document.getElementById('resultsCountText');
    
    let filteredArticles = [...allArticles];
    
    /**
     * Render articoli with correct SCSS classes
     */
    function renderArticles(articles) {
        if (articles.length === 0) {
            articoliListContainer.innerHTML = '';
            noResults.style.display = 'flex';
            resultsCountText.textContent = 'Nessun risultato';
            return;
        }
        
        noResults.style.display = 'none';
        resultsCountText.textContent = articles.length === 1 ? '1 risultato' : `${articles.length} risultati`;
        
        articoliListContainer.innerHTML = `
            <div class="articles-grid">
                ${articles.map(article => `
                    <a href="${article.permalink}" class="article-card">
                        <!-- Image -->
                        ${article.image ? `
                            <div class="article-card__image" style="background-image: url('${article.image}');">
                                <div class="article-card__overlay"></div>
                            </div>
                        ` : `
                            <div class="article-card__placeholder">
                                <i data-lucide="image"></i>
                            </div>
                        `}
                        
                        <!-- Content -->
                        <div class="article-card__content">
                            <h3 class="article-card__title">${article.title}</h3>
                            <p class="article-card__excerpt">${article.excerpt}</p>
                        </div>
                        
                        <!-- Meta -->
                        <div class="article-card__meta">
                            <span class="article-card__date">
                                <i data-lucide="calendar"></i>
                                ${article.date}
                            </span>
                            <span class="article-card__category">
                                <i data-lucide="tag"></i>
                                ${article.category}
                            </span>
                        </div>
                    </a>
                `).join('')}
            </div>
        `;
        
        // Re-init Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
    
    /**
     * Filter articoli
     */
    function filterArticles() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        filteredArticles = allArticles.filter(article => {
            if (!searchTerm) return true;
            
            return article.title.toLowerCase().includes(searchTerm) ||
                   article.excerpt.toLowerCase().includes(searchTerm) ||
                   article.category.toLowerCase().includes(searchTerm);
        });
        
        renderArticles(filteredArticles);
    }
    
    /**
     * Event listeners
     */
    searchInput.addEventListener('input', filterArticles);
    
    /**
     * Init
     */
    renderArticles(allArticles);
    
    // Init Lucide
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
})();
</script>

<?php get_footer(); ?>
