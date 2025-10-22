/**
 * Alpine.js Component: gestoreDashboard
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('gestoreDashboard', () => ({
        activeTab: 'documenti',
        modalOpen: false,
        modalTitle: '',
        modalContent: '',
        selectedPostId: null,
        selectedPostType: null,
        selectedUserId: null,
        isLoading: false,
        errorMessage: '',
        successMessage: '',

        async openFormModal(postType, action = 'new', postId = null) {
            this.selectedPostType = postType;
            this.selectedPostId = postId;
            this.isLoading = true;
            this.errorMessage = '';
            this.modalOpen = true;
            this.isLoading = false;
        },

        closeModal() {
            this.modalOpen = false;
            this.modalContent = '';
            this.selectedPostId = null;
            this.selectedPostType = null;
            this.selectedUserId = null;
            this.errorMessage = '';
        },

        async deletePost(postId) {
            if (!confirm('Sei sicuro di voler eliminare questo elemento?')) return;
            this.isLoading = true;
            try {
                const response = await fetch(meridiana.ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ action: 'meridiana_delete_post', nonce: meridiana.nonce, post_id: postId }),
                });
                const data = await response.json();
                if (data.success) {
                    this.successMessage = 'Elemento eliminato';
                    setTimeout(() => { location.reload(); }, 1000);
                } else {
                    this.errorMessage = data.data || 'Errore';
                }
            } catch (error) {
                console.error('Delete error:', error);
                this.errorMessage = 'Errore di rete';
            } finally {
                this.isLoading = false;
            }
        },

        async deleteUser(userId) {
            if (!confirm('Sei sicuro di voler eliminare questo utente?')) return;
            this.isLoading = true;
            try {
                const response = await fetch(meridiana.ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ action: 'meridiana_delete_user', nonce: meridiana.nonce, user_id: userId }),
                });
                const data = await response.json();
                if (data.success) {
                    this.successMessage = 'Utente eliminato';
                    setTimeout(() => { location.reload(); }, 1000);
                } else {
                    this.errorMessage = data.data || 'Errore';
                }
            } catch (error) {
                console.error('Delete user error:', error);
                this.errorMessage = 'Errore di rete';
            } finally {
                this.isLoading = false;
            }
        },

        async resetUserPassword(userId) {
            if (!confirm('Inviare email di reset password?')) return;
            this.isLoading = true;
            try {
                const response = await fetch(meridiana.ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ action: 'meridiana_reset_user_password', nonce: meridiana.nonce, user_id: userId }),
                });
                const data = await response.json();
                if (data.success) {
                    this.successMessage = 'Email inviata';
                    setTimeout(() => { this.successMessage = ''; }, 3000);
                } else {
                    this.errorMessage = data.data || 'Errore';
                }
            } catch (error) {
                console.error('Password reset error:', error);
                this.errorMessage = 'Errore di rete';
            } finally {
                this.isLoading = false;
            }
        },

        getModalTitle() {
            const typeMap = { 'documenti': 'Documento', 'comunicazioni': 'Comunicazione', 'convenzioni': 'Convenzione', 'salute': 'Articolo', 'utenti': 'Utente' };
            const type = typeMap[this.selectedPostType] || 'Elemento';
            return `${this.selectedPostId ? 'Modifica' : 'Nuovo'} ${type}`;
        },

        showNotification(message, type = 'success') {
            if (type === 'success') {
                this.successMessage = message;
                setTimeout(() => { this.successMessage = ''; }, 3000);
            } else {
                this.errorMessage = message;
                setTimeout(() => { this.errorMessage = ''; }, 3000);
            }
        },
    }));
});
