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
        errorMessage: '',
        successMessage: '',
        activeEditors: [],
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

            this.initRichTextEditors(target);
            this.initRepeaterControls(target);

            const convenzioneToggle = target.querySelector('#convenzione_attiva');
            if (convenzioneToggle) {
                const labelElement = convenzioneToggle.closest('label');
                const labelSpan = labelElement ? labelElement.querySelector('span') : null;
                const updateConvenzioneLabel = () => {
                    if (labelSpan) {
                        labelSpan.textContent = convenzioneToggle.checked ? 'Attiva' : 'Scaduta';
                    }
                };
                updateConvenzioneLabel();
                if (!convenzioneToggle.dataset.bound) {
                    convenzioneToggle.dataset.bound = '1';
                    convenzioneToggle.addEventListener('change', updateConvenzioneLabel);
                }
            }

            const atsToggle = target.querySelector('#ats_flag');
            if (atsToggle) {
                const label = target.querySelector('[data-ats-label]');
                const updateLabel = () => {
                    if (label) {
                        label.textContent = atsToggle.checked ? 'SÃŒ, pianificazione ATS' : 'NO, documento standard';
                    }
                };

                updateLabel();

                if (!atsToggle.dataset.bound) {
                    atsToggle.dataset.bound = '1';
                    atsToggle.addEventListener('change', updateLabel);
                }
            }
        },

        initRichTextEditors(target) {
            if (!target || typeof window === 'undefined' || !window.wp || !window.wp.editor) {
                return;
            }

            const editors = target.querySelectorAll('.wysiwyg-editor[data-wysiwyg]');
            editors.forEach((textarea) => {
                const editorId = textarea.getAttribute('id');
                if (!editorId) {
                    return;
                }

                let settings = { tinymce: true, quicktags: true, mediaButtons: true };
                const settingsAttr = textarea.getAttribute('data-editor-settings');
                if (settingsAttr) {
                    try {
                        settings = JSON.parse(settingsAttr);
                    } catch (error) {
                        console.warn('Impossibile parsare le impostazioni editor', error);
                    }
                }

                try {
                    if (window.wp.editor && window.wp.editor.remove) {
                        window.wp.editor.remove(editorId);
                    }
                } catch (error) {
                    // ignore
                }

                if (window.wp.editor && window.wp.editor.initialize) {
                    window.wp.editor.initialize(editorId, settings);
                    if (!this.activeEditors.includes(editorId)) {
                        this.activeEditors.push(editorId);
                    }
                }
            });
        },

        destroyRichTextEditors() {
            if (!this.activeEditors.length || typeof window === 'undefined' || !window.wp || !window.wp.editor) {
                this.activeEditors = [];
                return;
            }

            this.activeEditors.forEach((editorId) => {
                try {
                    if (window.wp.editor && window.wp.editor.remove) {
                        window.wp.editor.remove(editorId);
                    }
                } catch (error) {
                    // ignore
                }
            });

            this.activeEditors = [];
        },

        initRepeaterControls(target) {
            if (!target) {
                return;
            }

            const wrappers = target.querySelectorAll('[data-repeater]');
            wrappers.forEach((wrapper) => {
                const rowsContainer = wrapper.querySelector('[data-repeater-rows]');
                if (!rowsContainer) {
                    return;
                }

                const repeaterName = wrapper.getAttribute('data-repeater') || '';
                const templateSelector = repeaterName ? `template[data-repeater-template="${repeaterName}"]` : 'template[data-repeater-template]';
                let templateNode = wrapper.querySelector(templateSelector);
                if (!templateNode) {
                    templateNode = wrapper.querySelector('template');
                }

                const addSelector = repeaterName ? `[data-repeater-add="${repeaterName}"]` : '[data-repeater-add]';
                const addButton = wrapper.querySelector(addSelector);

                const reindexRows = () => {
                    const rows = rowsContainer.querySelectorAll('[data-repeater-row]');
                    rows.forEach((row, index) => {
                        row.querySelectorAll('[name]').forEach((input) => {
                            const currentName = input.getAttribute('name');
                            if (!currentName) {
                                return;
                            }
                            input.setAttribute('name', currentName.replace(/\[[0-9]+\]/, `[${index}]`));
                        });
                    });
                };

                const initialiseRow = (row) => {
                    if (!row || row.dataset.repeaterInitialised === '1') {
                        return;
                    }
                    row.dataset.repeaterInitialised = '1';
                    this.initDocumentFormEnhancements(row);
                    const risorsaRow = row.querySelector('[data-risorsa-row]');
                    if (risorsaRow) {
                        this.initResourceRow(risorsaRow);
                    }
                };

                if (addButton && !addButton.dataset.bound) {
                    addButton.dataset.bound = '1';
                    addButton.addEventListener('click', (event) => {
                        event.preventDefault();
                        if (!templateNode) {
                            return;
                        }
                        const nextIndex = rowsContainer.querySelectorAll('[data-repeater-row]').length;
                        const markup = templateNode.innerHTML.replace(/__index__/g, nextIndex);
                        const fragmentWrapper = document.createElement('div');
                        fragmentWrapper.innerHTML = markup.trim();
                        const newRow = fragmentWrapper.firstElementChild;
                        if (!newRow) {
                            return;
                        }
                        rowsContainer.appendChild(newRow);
                        initialiseRow(newRow);
                        reindexRows();
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    });
                }

                if (!rowsContainer.dataset.repeaterBound) {
                    rowsContainer.dataset.repeaterBound = '1';
                    rowsContainer.addEventListener('click', (event) => {
                        const trigger = event.target.closest('[data-repeater-remove]');
                        if (!trigger) {
                            return;
                        }
                        event.preventDefault();
                        const row = trigger.closest('[data-repeater-row]');
                        if (row) {
                            row.remove();
                            reindexRows();
                        }
                    });
                }

                rowsContainer.querySelectorAll('[data-repeater-row]').forEach((row) => initialiseRow(row));
                reindexRows();
            });
        },

        initResourceRow(row) {
            if (!row || row.dataset.risorsaBound === '1') {
                return;
            }
            const typeSelect = row.querySelector('[data-risorsa-type]');
            if (!typeSelect) {
                return;
            }
            const toggleFields = () => {
                const current = typeSelect.value || 'link';
                row.querySelectorAll('[data-risorsa-field]').forEach((field) => {
                    if (!field) {
                        return;
                    }
                    field.hidden = field.getAttribute('data-risorsa-field') !== current;
                });
            };
            toggleFields();
            typeSelect.addEventListener('change', toggleFields);
            row.dataset.risorsaBound = '1';
        },

        syncEditors() {
            if (!this.activeEditors.length || typeof window === 'undefined') {
                return;
            }

            const hasWP = window.wp && window.wp.editor && window.wp.editor.get;
            const hasTinyMCE = typeof window.tinyMCE !== 'undefined';

            this.activeEditors.forEach((editorId) => {
                let synced = false;
                if (hasWP) {
                    try {
                        const editorInstance = window.wp.editor.get(editorId);
                        if (editorInstance && editorInstance.triggerSave) {
                            editorInstance.triggerSave();
                            synced = true;
                        }
                    } catch (error) {
                        // fallback
                    }
                }

                if (!synced && hasTinyMCE) {
                    const tinyInstance = window.tinyMCE.get(editorId);
                    if (tinyInstance && tinyInstance.save) {
                        tinyInstance.save();
                    }
                }
            });
        },

        async openFormModal(postType, action = 'new', postId = null, cpt = null) {
            this.destroyRichTextEditors();
            this.selectedPostType = postType;
            this.selectedPostId = postId;
            this.isLoading = true;
            this.errorMessage = '';
            this.modalContent = '';

            let targetCPT = cpt;

            if (postType === 'documenti') {
                targetCPT = targetCPT || this.selectedCPT || 'protocollo';
                this.selectedCPT = targetCPT;
            } else {
                this.selectedCPT = null;
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
            this.destroyRichTextEditors();
            this.modalOpen = false;
            this.modalContent = '';
            this.selectedPostId = null;
            this.selectedPostType = null;
            this.selectedCPT = null;
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

                this.syncEditors();

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

        async deleteComunicazione(postId) {
            if (!confirm('Sei sicuro di voler eliminare questa comunicazione?')) return;
            this.isLoading = true;
            try {
                const response = await fetch(meridiana.ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ action: 'gestore_delete_comunicazione', nonce: meridiana.nonce, post_id: postId }),
                });
                const data = await response.json();
                if (data.success) {
                    this.successMessage = data.data?.message || 'Comunicazione eliminata';
                    setTimeout(() => { location.reload(); }, 1000);
                } else {
                    this.errorMessage = data.data?.message || 'Errore';
                }
            } catch (error) {
                console.error('Delete comunicazione error:', error);
                this.errorMessage = 'Errore di rete';
            } finally {
                this.isLoading = false;
            }
        },

        async deleteConvenzione(postId) {
            if (!confirm('Sei sicuro di voler eliminare questa convenzione?')) return;
            this.isLoading = true;
            try {
                const response = await fetch(meridiana.ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ action: 'gestore_delete_convenzione', nonce: meridiana.nonce, post_id: postId }),
                });
                const data = await response.json();
                if (data.success) {
                    this.successMessage = data.data?.message || 'Convenzione eliminata';
                    setTimeout(() => { location.reload(); }, 1000);
                } else {
                    this.errorMessage = data.data?.message || 'Errore';
                }
            } catch (error) {
                console.error('Delete convenzione error:', error);
                this.errorMessage = 'Errore di rete';
            } finally {
                this.isLoading = false;
            }
        },

        async deleteSalute(postId) {
            if (!confirm('Sei sicuro di voler eliminare questo contenuto?')) return;
            this.isLoading = true;
            try {
                const response = await fetch(meridiana.ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ action: 'gestore_delete_salute', nonce: meridiana.nonce, post_id: postId }),
                });
                const data = await response.json();
                if (data.success) {
                    this.successMessage = data.data?.message || 'Contenuto eliminato';
                    setTimeout(() => { location.reload(); }, 1000);
                } else {
                    this.errorMessage = data.data?.message || 'Errore';
                }
            } catch (error) {
                console.error('Delete salute error:', error);
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
            if (!this.selectedPostType) {
                return '';
            }

            if (this.selectedPostType === 'documenti') {
                const isProtocollo = (this.selectedCPT || 'protocollo') === 'protocollo';
                const baseLabel = isProtocollo ? 'Protocollo' : 'Modulo';
                return (this.selectedPostId ? 'Modifica ' : 'Nuovo ') + baseLabel;
            }

            const labels = {
                'comunicazioni': ['Nuova Comunicazione', 'Modifica Comunicazione'],
                'convenzioni': ['Nuova Convenzione', 'Modifica Convenzione'],
                'salute': ['Nuovo Articolo', 'Modifica Articolo'],
                'utenti': ['Nuovo Utente', 'Modifica Utente'],
            };
            const typeLabels = labels[this.selectedPostType] || ['Nuovo Elemento', 'Modifica Elemento'];
            return this.selectedPostId ? typeLabels[1] : typeLabels[0];
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


