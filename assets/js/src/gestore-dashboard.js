/**
 * Alpine.js Component: gestoreDashboard
 * FIX: Aggiunto Step 1 scelta CPT, form rendering corretto, media picker fix
 */

// ============================================
// HELPER: Media Picker per File Field
// ============================================

window.meridiana_open_media_picker = function(button, inputField) {
    if (typeof wp === 'undefined' || !wp.media) {
        console.error('WordPress media library not loaded');
        return;
    }

    // Crea media frame se non esiste
    if (!window.meridiana_file_frame) {
        window.meridiana_file_frame = wp.media({
            title: 'Seleziona File PDF',
            button: { text: 'Seleziona' },
            library: { type: 'application/pdf' },
            multiple: false
        });

        // Quando un file viene selezionato
        window.meridiana_file_frame.on('select', function() {
            const attachment = window.meridiana_file_frame.state().get('selection').first().toJSON();
            
            // Trova l'input field hidden per il file ID
            const hiddenInput = document.querySelector('input[name="acf[field_pdf_protocollo]"], input[name="acf[field_pdf_modulo]"]');
            if (hiddenInput) {
                hiddenInput.value = attachment.id;
                
                // Trigger change event per ACF
                const event = new Event('change', { bubbles: true });
                hiddenInput.dispatchEvent(event);
                
                // Update preview
                const previewText = document.querySelector('.acf-file-uploader .description');
                if (previewText) {
                    previewText.textContent = '✓ File selezionato: ' + attachment.filename;
                }
                
                console.log('[MediaPicker] File selezionato:', attachment.id, attachment.filename);
            }
        });
    }

    // Apri il media picker
    window.meridiana_file_frame.open();
};

// ============================================
// ALPINE COMPONENT
// ============================================

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

            let targetCPT = cpt;

            if (postType === 'documenti') {
                targetCPT = targetCPT || this.selectedCPT || 'protocollo';
                this.selectedCPT = targetCPT;
            }

            this.modalOpen = true;

            try {
                const formData = new FormData();
                formData.append('action', 'gestore_load_form');
                formData.append('post_type', postType);
                formData.append('action_type', action);
                formData.append('post_id', postId || 0);
                if (targetCPT) {
                    formData.append('cpt', targetCPT);
                }
                formData.append('nonce', meridiana.nonce);

                const response = await fetch(meridiana.ajaxurl, {
                    method: 'POST',
                    body: formData,
                });

                const data = await response.json();
                if (data.success) {
                    this.modalContent = data.data.form_html;
                    if (postType === 'documenti' && data.data?.document_cpt) {
                        this.selectedCPT = data.data.document_cpt;
                    }
                    this.modalStep = 'form';

                    this.$nextTick(() => {
                        // Reinitialize ACF fields (media picker, file uploads, etc.)
                        if (window.acf && this.$refs.modalContent) {
                            // Append action triggers ACF field initialization
                            window.acf.doAction('append', this.$refs.modalContent);
                            
                            // Fix label accessibility issues
                            if (window.fixACFLabelRelationships) {
                                window.fixACFLabelRelationships(this.$refs.modalContent);
                            }
                            
                            // FIX MEDIA PICKER: Bind buttons per file field nel modal
                            const addFileButtons = this.$refs.modalContent.querySelectorAll('.acf-file-uploader .button');
                            if (addFileButtons.length > 0 && typeof wp !== 'undefined' && wp.media) {
                                addFileButtons.forEach(button => {
                                    // Remove old listeners
                                    const clone = button.cloneNode(true);
                                    button.parentNode.replaceChild(clone, button);
                                    
                                    // Bind new click handler
                                    clone.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        window.meridiana_open_media_picker(this);
                                    });
                                });
                            }
                        }
                        
                        // Render icons
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    });
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
                const container = this.$refs.modalContent || document;
                const formElement = container.querySelector('form[data-gestore-form]');
                if (!formElement) {
                    this.errorMessage = 'Form non trovato';
                    this.isLoading = false;
                    return;
                }

                const formData = new FormData(formElement);
                formData.set('action', 'gestore_save_form');
                formData.set('post_type', this.selectedPostType);
                const targetCPT = this.selectedPostType === 'documenti' ? (this.selectedCPT || 'protocollo') : null;
                if (targetCPT) {
                    formData.set('cpt', targetCPT);
                }
                formData.set('post_id', this.selectedPostId || 0);
                formData.set('nonce', meridiana.nonce);

                const response = await fetch(meridiana.ajaxurl, {
                    method: 'POST',
                    body: formData,
                });

                const result = await response.json();
                if (result.success) {
                    this.successMessage = result.data?.message || 'Salvato con successo';
                    this.closeModal();
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
            const typeMap = {
                'documenti': this.selectedCPT === 'protocollo' ? 'Nuovo Protocollo' : 'Nuovo Modulo',
                'comunicazioni': 'Nuova Comunicazione',
                'convenzioni': 'Nuova Convenzione',
                'salute': 'Nuovo Articolo',
                'utenti': 'Nuovo Utente'
            };
            const type = typeMap[this.selectedPostType] || 'Elemento';
            return `${this.selectedPostId ? 'Modifica' : type.split(' ')[0]} ${type.replace(/^Nuovo\s|^Modifica\s/, '')}`;
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
