/**
 * Alpine.js Component: gestoreDashboard
 * FIX: Aggiunto Step 1 scelta CPT, form rendering corretto
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('gestoreDashboard', () => ({
        activeTab: 'documenti',
        modalOpen: false,
        modalContent: '',
        selectedPostId: null,
        selectedPostType: null, // 'documenti' | 'utenti'
        selectedCPT: null, // 'protocollo' | 'modulo' (per documenti)
        isLoading: false,
        modalStep: 'choose', // 'choose' = scelta CPT, 'form' = carica form
        errorMessage: '',
        successMessage: '',

        openDocumentoChoice() {
            this.selectedPostType = 'documenti';
            this.modalStep = 'choose';
            this.modalOpen = true;
            this.selectedCPT = null;
        },

        async selectCPT(cpt) {
            // Dopo scelta CPT, carica il form
            this.selectedCPT = cpt;
            this.modalStep = 'form';
            await this.openFormModal('documenti', 'new', 0, cpt);
        },

        async openFormModal(postType, action = 'new', postId = null, cpt = null) {
            this.selectedPostType = postType;
            this.selectedPostId = postId;
            this.isLoading = true;
            this.errorMessage = '';
            this.modalContent = '';
            
            // Per documenti, usa il CPT selezionato
            if (postType === 'documenti' && !cpt) {
                cpt = this.selectedCPT || 'protocollo';
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'gestore_load_form');
                formData.append('post_type', postType);
                formData.append('action_type', action);
                formData.append('post_id', postId || 0);
                formData.append('cpt', cpt || 'protocollo');
                formData.append('nonce', meridiana.nonce);
                
                const response = await fetch(meridiana.ajaxurl, {
                    method: 'POST',
                    body: formData,
                });
                
                const data = await response.json();
                if (data.success) {
                    this.modalContent = data.data.form_html;
                    this.modalStep = 'form';
                } else {
                    this.errorMessage = data.data?.message || 'Errore caricamento form';
                    console.error('Form load error:', data);
                }
            } catch (error) {
                console.error('Form load error:', error);
                this.errorMessage = 'Errore di rete';
            } finally {
                this.isLoading = false;
            }
        },

        closeModal() {
            this.modalOpen = false;
            this.modalContent = '';
            this.selectedPostId = null;
            this.selectedPostType = null;
            this.selectedCPT = null;
            this.modalStep = 'choose';
            this.errorMessage = '';
        },

        async submitForm() {
            this.isLoading = true;
            this.errorMessage = '';
            
            try {
                // Cerca il form dentro modalContent
                const formElement = document.querySelector('form[data-gestore-form]');
                if (!formElement) {
                    this.errorMessage = 'Form non trovato';
                    this.isLoading = false;
                    return;
                }

                const formData = new FormData(formElement);
                formData.append('action', 'gestore_save_form');
                formData.append('post_type', this.selectedPostType);
                formData.append('cpt', this.selectedCPT || 'protocollo');
                formData.append('post_id', this.selectedPostId || 0);
                formData.append('nonce', meridiana.nonce);
                
                const response = await fetch(meridiana.ajaxurl, {
                    method: 'POST',
                    body: formData,
                });
                
                const result = await response.json();
                if (result.success) {
                    this.successMessage = result.data?.message || 'Salvato con successo';
                    this.closeModal();
                    // Ricarica tabella dopo 1 secondo
                    setTimeout(() => { location.reload(); }, 1000);
                } else {
                    this.errorMessage = result.data?.message || 'Errore durante il salvataggio';
                    console.error('Save error:', result);
                }
            } catch (error) {
                console.error('Submit error:', error);
                this.errorMessage = 'Errore di rete durante il salvataggio';
            } finally {
                this.isLoading = false;
            }
        },

        async deletePost(postId) {
            if (!confirm('Sei sicuro di voler eliminare questo elemento?')) return;
            this.isLoading = true;
            try {
                const response = await fetch(meridiana.ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ action: 'gestore_delete_documento', nonce: meridiana.nonce, post_id: postId }),
                });
                const data = await response.json();
                if (data.success) {
                    this.successMessage = 'Elemento eliminato';
                    setTimeout(() => { location.reload(); }, 1000);
                } else {
                    this.errorMessage = data.data?.message || 'Errore';
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
                    body: new URLSearchParams({ action: 'gestore_delete_user', nonce: meridiana.nonce, user_id: userId }),
                });
                const data = await response.json();
                if (data.success) {
                    this.successMessage = 'Utente eliminato';
                    setTimeout(() => { location.reload(); }, 1000);
                } else {
                    this.errorMessage = data.data?.message || 'Errore';
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
                    body: new URLSearchParams({ action: 'gestore_reset_password', nonce: meridiana.nonce, user_id: userId }),
                });
                const data = await response.json();
                if (data.success) {
                    this.successMessage = 'Email inviata';
                    setTimeout(() => { this.successMessage = ''; }, 3000);
                } else {
                    this.errorMessage = data.data?.message || 'Errore';
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
