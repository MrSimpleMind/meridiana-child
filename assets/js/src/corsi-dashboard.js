/**
 * Alpine.js Component: corsiDashboard
 * Gestisce il dashboard dei corsi con tabs, enrollment, e certificati
 *
 * @package Meridiana Child
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('corsiDashboard', () => ({
        // ========================================
        // STATE
        // ========================================

        activeTab: 'in-progress',
        userId: null,
        ajaxUrl: null,
        nonce: null,

        // Course Lists
        inProgressCourses: [],
        completedCourses: [],
        optionalCourses: [],
        certificates: [],

        // UI State
        isLoading: false,
        errorMessage: '',
        successMessage: '',

        // ========================================
        // LIFECYCLE
        // ========================================

        init() {
            // Recupera dati dalle data attributes
            const container = this.$el.closest('[x-data*="corsiDashboard"]');
            this.userId = parseInt(container?.dataset.userId || 0);
            this.ajaxUrl = container?.dataset.ajaxUrl || '/wp-admin/admin-ajax.php';
            this.restUrl = container?.dataset.restUrl || '/wp-json/learnDash/v1/';
            this.nonce = container?.dataset.nonce || '';

            // Carica i corsi al mount
            this.loadAllCourses();

            // Watcher per cambi di tab
            this.$watch('activeTab', (value) => {
                this.persistActiveTab(value);
            });

            // Ripristina il tab precedente se salvato
            const savedTab = localStorage.getItem('meridiana_corsi_active_tab');
            if (savedTab && ['in-progress', 'completed', 'optional', 'certificates'].includes(savedTab)) {
                this.activeTab = savedTab;
            }
        },

        // ========================================
        // COMPUTED PROPERTIES
        // ========================================

        get inProgressCount() {
            return this.inProgressCourses.length;
        },

        get completedCount() {
            return this.completedCourses.length;
        },

        get optionalCount() {
            return this.optionalCourses.length;
        },

        get certificatesCount() {
            return this.certificates.length;
        },

        // ========================================
        // TAB MANAGEMENT
        // ========================================

        setTab(tabName) {
            if (['in-progress', 'completed', 'optional', 'certificates'].includes(tabName)) {
                this.activeTab = tabName;
            }
        },

        persistActiveTab(tabName) {
            localStorage.setItem('meridiana_corsi_active_tab', tabName);
        },

        // ========================================
        // DATA LOADING
        // ========================================

        async loadAllCourses() {
            if (!this.userId) {
                console.error('User ID not found');
                return;
            }

            this.isLoading = true;
            this.errorMessage = '';

            try {
                // Carica tutti i corsi e il loro stato per l'utente
                const response = await fetch(
                    `${this.restUrl}user/${this.userId}/courses`,
                    {
                        headers: {
                            'X-WP-Nonce': this.nonce,
                        },
                    }
                );

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                // Organizza i corsi per categoria
                this.inProgressCourses = data.in_progress || [];
                this.completedCourses = data.completed || [];
                this.optionalCourses = data.optional || [];
                this.certificates = data.certificates || [];

            } catch (error) {
                console.error('Error loading courses:', error);
                this.errorMessage = 'Errore nel caricamento dei corsi. Per favore ricarica la pagina.';
            } finally {
                this.isLoading = false;
            }
        },

        // ========================================
        // COURSE ENROLLMENT
        // ========================================

        async enrollCourse(courseId) {
            if (!courseId || !this.userId) {
                console.error('Missing course ID or user ID');
                return;
            }

            this.isLoading = true;
            this.errorMessage = '';
            this.successMessage = '';

            try {
                const response = await fetch(
                    `${this.restUrl}user/${this.userId}/courses/${courseId}/enroll`,
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': this.nonce,
                        },
                    }
                );

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                // Ricarica i corsi per aggiornare lo stato
                await this.loadAllCourses();
                this.successMessage = 'Ti sei iscritto al corso! Puoi iniziare subito.';

                // Nascondi il messaggio dopo 3 secondi
                setTimeout(() => {
                    this.successMessage = '';
                }, 3000);

            } catch (error) {
                console.error('Error enrolling in course:', error);
                this.errorMessage = 'Errore nell\'iscrizione al corso. Per favore riprova.';
            } finally {
                this.isLoading = false;
            }
        },

        // ========================================
        // CERTIFICATE MANAGEMENT
        // ========================================

        async downloadCertificate(courseId) {
            if (!courseId) {
                console.error('Missing course ID');
                return;
            }

            this.isLoading = true;
            this.errorMessage = '';

            try {
                const response = await fetch(
                    `${this.restUrl}courses/${courseId}/certificate`,
                    {
                        method: 'GET',
                        headers: {
                            'X-WP-Nonce': this.nonce,
                        },
                    }
                );

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.download_url) {
                    // Scarica il certificato
                    this.downloadFile(data.download_url, `certificato-${courseId}.pdf`);
                } else {
                    throw new Error('Download URL not found');
                }

            } catch (error) {
                console.error('Error downloading certificate:', error);
                this.errorMessage = 'Errore nel download del certificato. Per favore riprova.';
            } finally {
                this.isLoading = false;
            }
        },

        async downloadCourseCertificate(courseId) {
            // Alias per coerenza con i template
            return this.downloadCertificate(courseId);
        },

        async shareCertificate(certificateId) {
            if (!certificateId) {
                console.error('Missing certificate ID');
                return;
            }

            try {
                // Usa l'API Web Share se disponibile
                if (navigator.share) {
                    await navigator.share({
                        title: 'Il Mio Certificato',
                        text: 'Scopri il certificato che ho conseguito!',
                        url: window.location.href,
                    });
                } else {
                    // Fallback: copia URL negli appunti
                    await navigator.clipboard.writeText(window.location.href);
                    this.successMessage = 'Link copiato negli appunti!';
                    setTimeout(() => {
                        this.successMessage = '';
                    }, 2000);
                }
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.error('Error sharing certificate:', error);
                    this.errorMessage = 'Errore nella condivisione del certificato.';
                }
            }
        },

        // ========================================
        // UTILITY METHODS
        // ========================================

        downloadFile(url, filename) {
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },

        // ========================================
        // LESSON TRACKING
        // ========================================

        async markLessonAsViewed(lessonId, duration = 0) {
            if (!lessonId || !this.userId) {
                return;
            }

            try {
                await fetch(
                    `${this.restUrl}lessons/${lessonId}/mark-viewed`,
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': this.nonce,
                        },
                        body: JSON.stringify({
                            duration: duration,
                        }),
                    }
                );
            } catch (error) {
                console.error('Error marking lesson as viewed:', error);
            }
        },

        // ========================================
        // UI HELPERS
        // ========================================

        closeAlert() {
            this.errorMessage = '';
            this.successMessage = '';
        },

        isCurrentTab(tabName) {
            return this.activeTab === tabName;
        },

        getTabLabel(tabName) {
            const labels = {
                'in-progress': 'In Corso',
                'completed': 'Completati',
                'optional': 'Facoltativi',
                'certificates': 'Certificati',
            };
            return labels[tabName] || tabName;
        },

        getCoursesForTab(tabName) {
            switch (tabName) {
                case 'in-progress':
                    return this.inProgressCourses;
                case 'completed':
                    return this.completedCourses;
                case 'optional':
                    return this.optionalCourses;
                case 'certificates':
                    return this.certificates;
                default:
                    return [];
            }
        },
    }));
});

// ========================================
// EXPORT FOR TESTING
// ========================================

if (typeof module !== 'undefined' && module.exports) {
    module.exports = { corsiDashboard: true };
}
