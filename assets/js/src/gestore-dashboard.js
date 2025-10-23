/**
 * Alpine.js Component: gestoreDashboard
 * FIX: Aggiunto Step 1 scelta CPT, form rendering corretto, media picker fix
 */

// ============================================
// HELPER: Media Picker per File Field
// ============================================


window.meridiana_open_media_picker = function(trigger) {
    if (typeof wp === 'undefined' || !wp.media) {
        console.error('WordPress media library not loaded');
        return;
    }

    const field = trigger.closest('[data-media-field]');
    if (!field) {
        console.warn('Media field wrapper not found');
        return;
    }

    const mediaTypeAttr = field.dataset.mediaType;
    const mediaType = mediaTypeAttr === 'image' ? 'image' : 'application/pdf';
    const allowClear = field.dataset.required !== '1';
    const hiddenInput = field.querySelector('input[type="hidden"]');
    const fileNameElement = field.querySelector('[data-media-file-name]');
    const previewElement = field.querySelector('[data-media-preview]');
    const placeholder = field.dataset.mediaPlaceholder || '';

    const frame = wp.media({
        title: mediaType === 'image' ? 'Seleziona immagine' : 'Seleziona PDF',
        button: { text: 'Usa questo file' },
        library: { type: mediaType },
        multiple: false,
    });

    frame.on('select', () => {
        const attachment = frame.state().get('selection').first().toJSON();
        if (hiddenInput) {
            hiddenInput.value = attachment.id || '';
            hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (fileNameElement) {
            fileNameElement.textContent = attachment.filename || attachment.title || attachment.name || 'File selezionato';
        }

        if (previewElement) {
            if (mediaType === 'image') {
                const previewUrl = (attachment.sizes && (attachment.sizes.medium?.url || attachment.sizes.thumbnail?.url)) || attachment.icon || attachment.url;
                previewElement.innerHTML = previewUrl ? `<img src="${previewUrl}" alt="">` : '';
            } else {
                previewElement.textContent = attachment.filename || attachment.title || attachment.name || '';
            }
        }

        const clearButton = field.querySelector('.media-clear');
        if (clearButton && allowClear) {
            clearButton.hidden = false;
        }

        if (!allowClear && !hiddenInput?.value && fileNameElement) {
            fileNameElement.textContent = placeholder || fileNameElement.textContent;
        }
    });

    frame.open();
};

// ============================================
// ALPINE COMPONENT
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
        allowedTabs: ['documenti', 'comunicazioni', 'convenzioni', 'salute', 'utenti'],

        init() {
            const tabs = this.allowedTabs;

            this.$watch('activeTab', (value) => {
                if (!tabs.includes(value)) {
                    return;
                }
                this.persistActiveTab(value);
            });

            let initialTab = null;
            const hash = (typeof window !== 'undefined' && window.location && window.location.hash)
                ? window.location.hash.replace('#', '')
                : '';

            if (tabs.includes(hash)) {
                initialTab = hash;
            } else {
                try {
                    const stored = (typeof window !== 'undefined' && window.localStorage)
                        ? window.localStorage.getItem('gestoreDashboardActiveTab')
                        : null;
                    if (stored && tabs.includes(stored)) {
                        initialTab = stored;
                    }
                } catch (error) {
                    // storage unavailable
                }
            }

            if (initialTab && initialTab !== this.activeTab) {
                this.activeTab = initialTab;
            } else if (!initialTab) {
                this.persistActiveTab(this.activeTab);
            }
        },

        persistActiveTab(tab) {
            if (!this.allowedTabs.includes(tab)) {
                return;
            }
            try {
                if (typeof window !== 'undefined' && window.localStorage) {
                    window.localStorage.setItem('gestoreDashboardActiveTab', tab);
                }
            } catch (error) {
                // storage unavailable
            }
        },

        initDocumentFormEnhancements(container = null) {
            const target = container || this.$refs.modalContent;
            if (!target) {
                return;
            }

            target.querySelectorAll('[data-media-field]').forEach((field) => {
                const hiddenInput = field.querySelector('input[type="hidden"]');
                const pickButton = field.querySelector('.media-picker');
                const clearButton = field.querySelector('.media-clear');
                const fileName = field.querySelector('[data-media-file-name]');
                const placeholder = field.dataset.mediaPlaceholder || '';
                const preview = field.querySelector('[data-media-preview]');
                const allowClear = field.dataset.required !== '1';

                if (fileName && (!hiddenInput || !hiddenInput.value)) {
                    fileName.textContent = placeholder || fileName.textContent;
                }

                if (pickButton && !pickButton.dataset.bound) {
                    pickButton.dataset.bound = '1';
                    pickButton.addEventListener('click', (event) => {
                        event.preventDefault();
                        window.meridiana_open_media_picker(event.currentTarget);
                    });
                }

                if (clearButton) {
                    if (!allowClear) {
                        clearButton.hidden = true;
                    } else {
                        const toggleClear = () => {
                            clearButton.hidden = !hiddenInput || !hiddenInput.value;
                        };

                        toggleClear();

                        if (!clearButton.dataset.bound) {
                            clearButton.dataset.bound = '1';
                            clearButton.addEventListener('click', (event) => {
                                event.preventDefault();
                                if (!hiddenInput) {
                                    return;
                                }
                                hiddenInput.value = '';
                                hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                                if (fileName) {
                                    fileName.textContent = placeholder || fileName.textContent;
                                }
                                if (preview) {
                                    preview.innerHTML = '';
                                }
                                toggleClear();
                            });
                        }
                    }
                }
            });

            const atsToggle = target.querySelector('#ats_flag');
            if (atsToggle) {
                const label = target.querySelector('[data-ats-label]');
                const updateLabel = () => {
                    if (label) {
                        label.textContent = atsToggle.checked ? 'SÌ, pianificazione ATS' : 'NO, documento standard';
                    }
                };

                updateLabel();

                if (!atsToggle.dataset.bound) {
                    atsToggle.dataset.bound = '1';
                    atsToggle.addEventListener('change', updateLabel);
                }
            }
        },

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
                        if (window.acf && this.$refs.modalContent) {
                            window.acf.doAction('append', this.$refs.modalContent);
                            if (window.fixACFLabelRelationships) {
                                window.fixACFLabelRelationships(this.$refs.modalContent);
                            }
                        }

                        this.initDocumentFormEnhancements(this.$refs.modalContent);

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
                    const selectedType = this.selectedPostType;
                    let nextTab = this.allowedTabs.includes(selectedType) ? selectedType : this.activeTab;
                    if (!this.allowedTabs.includes(nextTab)) {
                        nextTab = 'documenti';
                    }

                    this.successMessage = result.data?.message || 'Salvato con successo';
                    this.activeTab = nextTab;
                    this.persistActiveTab(nextTab);
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
