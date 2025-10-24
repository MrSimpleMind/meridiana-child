/**
 * Analytics Tab Component
 * Alpine.js x-data="analyticsTab()"
 * 
 * Funzionalità:
 * - Ricerca utenti real-time con debounce
 * - Switch tra tab "Visualizzati" e "Da visualizzare"
 * - Toggle user details
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('analyticsTab', () => ({
        // Estado
        userSearchQuery: '',
        userSearchResults: [],
        selectedUser: null,
        userDetailTab: 'viewed',
        searchTimeout: null,
        
        // Inizializzazione
        init() {
            // Setup listeners per lucide icons
            if (window.lucide) {
                window.lucide.createIcons();
            }
        },
        
        /**
         * Ricerca utenti con debounce
         */
        searchUsers() {
            // Clear precedente timeout
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }
            
            // Se query vuota, pulisci risultati
            if (!this.userSearchQuery.trim()) {
                this.userSearchResults = [];
                return;
            }
            
            // Debounce 300ms
            this.searchTimeout = setTimeout(() => {
                this._fetchSearchResults();
            }, 300);
        },
        
        /**
         * Fetch effettivo con AJAX
         */
        async _fetchSearchResults() {
            try {
                const response = await fetch(window.meridiana?.ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'meridiana_analytics_search_users',
                        query: this.userSearchQuery,
                        nonce: window.meridiana?.nonce || '',
                    }),
                });
                
                const data = await response.json();
                
                if (data.success && Array.isArray(data.data)) {
                    this.userSearchResults = data.data;
                } else {
                    this.userSearchResults = [];
                }
            } catch (error) {
                console.error('Search error:', error);
                this.userSearchResults = [];
            }
        },
        
        /**
         * Seleziona utente e carica dettagli
         */
        async selectUser(user) {
            this.selectedUser = user;
            this.userSearchResults = [];
            this.userSearchQuery = '';
            this.userDetailTab = 'viewed';
            
            // Carica visualizzazioni se necessario
            await this._loadUserViewedDocuments();
        },
        
        /**
         * Carica documenti visualizzati dall'utente
         */
        async _loadUserViewedDocuments() {
            if (!this.selectedUser) return;
            
            // TODO: Fetch da API endpoint
            // const response = await fetch(`/wp-json/piattaforma/v1/analytics/user/${this.selectedUser.ID}/viewed`);
            // const data = await response.json();
            // this.selectedUser.viewedDocuments = data;
        },
        
        /**
         * Close user detail
         */
        closeUserDetail() {
            this.selectedUser = null;
        },
    }));
});
