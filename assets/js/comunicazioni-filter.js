/**
 * PROMPT 6: Filtro Comunicazioni AJAX - Pattern Organigramma
 * 
 * Gestisce il filtro per categoria dinamico
 * Aggiorna la lista comunicazioni senza ricaricare la pagina
 * Con badge styling come in organigramma
 */

document.addEventListener('DOMContentLoaded', function() {
    const categoryFilter = document.getElementById('comunicazioni_category_filter');
    
    if (!categoryFilter) {
        return; // Se non c'è il filtro, esci
    }
    
    const nonce = categoryFilter.getAttribute('data-nonce');
    const comunicazioniContainer = document.querySelector('.comunicazioni-list');
    const paginationContainer = document.querySelector('.comunicazioni-pagination');
    const categoryBadge = document.getElementById('filterCategoriaBadge');
    
    if (!comunicazioniContainer) {
        return;
    }
    
    /**
     * Filtra comunicazioni per categoria
     * 
     * @param {number} categoryId - ID categoria (0 = tutte)
     * @param {number} page - Numero pagina (default: 1)
     */
    function filterComunicazioni(categoryId = 0, page = 1) {
        // Mostra loading
        comunicazioniContainer.style.opacity = '0.5';
        
        // AJAX Request
        fetch(meridiana.ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'meridiana_filter_comunicazioni',
                nonce: nonce,
                category_id: categoryId,
                page: page,
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Aggiorna lista
                comunicazioniContainer.innerHTML = data.data.html;
                
                // Aggiorna paginazione
                if (paginationContainer && data.data.pagination) {
                    paginationContainer.innerHTML = data.data.pagination;
                    
                    // Re-bind paginazione click
                    bindPaginationLinks(categoryId);
                }
                
                // Re-initialize Lucide icons
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            } else {
                comunicazioniContainer.innerHTML = '<div class="alert alert-error">Errore nel caricamento. Riprova.</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            comunicazioniContainer.innerHTML = '<div class="alert alert-error">Errore di connessione.</div>';
        })
        .finally(() => {
            // Rimuovi loading
            comunicazioniContainer.style.opacity = '1';
        });
    }
    
    /**
     * Re-bind click handler per link paginazione
     * 
     * @param {number} categoryId - ID categoria corrente
     */
    function bindPaginationLinks(categoryId = 0) {
        const paginationLinks = document.querySelectorAll('.pagination__link');
        
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                const page = this.getAttribute('data-page');
                if (page) {
                    filterComunicazioni(categoryId, page);
                    
                    // Scroll al top delle comunicazioni
                    comunicazioniContainer.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    }
    
    /**
     * Aggiorna il badge della categoria
     */
    function updateCategoryBadge() {
        if (categoryFilter.value && categoryBadge) {
            const selectedText = categoryFilter.options[categoryFilter.selectedIndex].text;
            categoryBadge.querySelector('span').textContent = selectedText;
        } else if (categoryBadge) {
            categoryBadge.querySelector('span').textContent = 'Tutte';
        }
    }
    
    // Event listener per cambio categoria
    categoryFilter.addEventListener('change', function() {
        const selectedCategory = this.value;
        updateCategoryBadge(); // Aggiorna badge
        filterComunicazioni(selectedCategory, 1); // Reset a pagina 1
    });
    
    // Bind iniziale paginazione
    bindPaginationLinks(0);
    updateCategoryBadge(); // Inizializza badge
});
