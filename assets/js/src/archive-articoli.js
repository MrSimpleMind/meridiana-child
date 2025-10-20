/**
 * Archive Articoli - Filter & Search Module
 * 
 * Gestisce:
 * - Search real-time
 * - Filter per categoria (toggle collapsibile)
 * - Conteggio risultati
 * - Re-init Lucide icons
 * 
 * @package Meridiana Child Theme
 * @version 1.0
 */

(function() {
    'use strict';
    
    // Cache DOM elements
    const searchInput = document.getElementById('articoli-search');
    const categorySelect = document.getElementById('articoli-category');
    const articoliList = document.getElementById('articoli-list');
    const articoliItems = articoliList ? articoliList.querySelectorAll('.articolo-item') : [];
    const resultsCountText = document.getElementById('results-count-text');
    const filterToggle = document.querySelector('.filter-toggle');
    const filterPanel = document.getElementById('category-filter');
    
    // Safety check
    if (!articoliList || articoliItems.length === 0) {
        return;
    }
    
    /**
     * Re-init Lucide icons when DOM changes
     */
    function reinitLucideIcons() {
        if (typeof lucide !== 'undefined' && lucide.createIcons) {
            lucide.createIcons();
        }
    }
    
    /**
     * Filter articoli by search term and category
     */
    function filterArticoli() {
        const searchTerm = searchInput?.value.toLowerCase().trim() || '';
        const selectedCategory = categorySelect?.value.trim() || '';
        let visibleCount = 0;
        
        articoliItems.forEach((item) => {
            const title = item.querySelector('.articolo-title')?.textContent.toLowerCase() || '';
            const excerpt = item.querySelector('.articolo-excerpt')?.textContent.toLowerCase() || '';
            const categoryElement = item.querySelector('.articolo-category');
            const itemCategory = categoryElement ? 
                categoryElement.textContent.toLowerCase().trim().replace(/tag\s*/i, '').trim() : '';
            
            // Check search match
            const searchMatch = !searchTerm || 
                title.includes(searchTerm) || 
                excerpt.includes(searchTerm);
            
            // Check category match
            const categoryMatch = !selectedCategory || 
                itemCategory.includes(selectedCategory.toLowerCase());
            
            // Show/Hide
            if (searchMatch && categoryMatch) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });
        
        // Update results count
        if (resultsCountText) {
            if (visibleCount === 0) {
                resultsCountText.textContent = 'Nessun risultato';
            } else if (visibleCount === 1) {
                resultsCountText.textContent = '1 risultato';
            } else {
                resultsCountText.textContent = `${visibleCount} risultati`;
            }
        }
        
        // Handle no-results message
        handleNoResults(visibleCount);
    }
    
    /**
     * Show/hide "no results" message
     */
    function handleNoResults(visibleCount) {
        let noResultsDiv = articoliList.querySelector('.no-results');
        
        if (visibleCount === 0) {
            // Create no-results if doesn't exist
            if (!noResultsDiv) {
                noResultsDiv = document.createElement('div');
                noResultsDiv.className = 'no-results';
                noResultsDiv.innerHTML = `
                    <i data-lucide="inbox"></i>
                    <p>Nessun risultato trovato</p>
                `;
                articoliList.appendChild(noResultsDiv);
                reinitLucideIcons();
            }
        } else {
            // Remove no-results if exists
            if (noResultsDiv) {
                noResultsDiv.remove();
            }
        }
    }
    
    /**
     * Toggle filter panel visibility
     */
    function setupFilterToggle() {
        if (!filterToggle || !filterPanel) {
            console.log('Filter toggle or panel not found');
            return;
        }
        
        filterToggle.addEventListener('click', function(e) {
            e.preventDefault();
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            const newState = !isExpanded;
            
            this.setAttribute('aria-expanded', newState);
            
            if (newState) {
                filterPanel.classList.remove('hidden');
            } else {
                filterPanel.classList.add('hidden');
            }
        });
    }
    
    /**
     * Setup event listeners
     */
    function setupEventListeners() {
        if (searchInput) {
            searchInput.addEventListener('input', filterArticoli);
        }
        
        if (categorySelect) {
            categorySelect.addEventListener('change', filterArticoli);
        }
        
        setupFilterToggle();
    }
    
    /**
     * Initialize module
     */
    function init() {
        reinitLucideIcons();
        setupEventListeners();
        filterArticoli();
    }
    
    // Init when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
