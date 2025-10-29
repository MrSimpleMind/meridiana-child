const ANALYTICS_CHART_COLORS = [
    "rgba(6, 182, 212, 0.8)",
    "rgba(16, 185, 129, 0.8)",
    "rgba(99, 102, 241, 0.8)",
    "rgba(249, 115, 22, 0.8)",
    "rgba(244, 63, 94, 0.8)",
    "rgba(59, 130, 246, 0.8)"
];

const ANALYTICS_CHART_BORDERS = [
    "rgba(6, 182, 212, 1)",
    "rgba(16, 185, 129, 1)",
    "rgba(99, 102, 241, 1)",
    "rgba(249, 115, 22, 1)",
    "rgba(244, 63, 94, 1)",
    "rgba(59, 130, 246, 1)"
];

const ANALYTICS_DATA = window.meridianaAnalyticsData || {};

function normalizeDate(value) {
    if (!value) {
        return null;
    }
    return value.replace(" ", "T");
}

function formatDateValue(value) {
    if (!value) {
        return "—";
    }
    const date = new Date(normalizeDate(value));
    if (Number.isNaN(date.getTime())) {
        return value;
    }
    return date.toLocaleString("it-IT", { dateStyle: "short", timeStyle: "short" });
}

function downloadDataset(rows, columns, filename, format = 'csv') {
    if (!rows || !rows.length) {
        return;
    }

    const delimiter = ';';
    const sanitize = (value) => {
        if (value === null || value === undefined) {
            value = '';
        }
        const str = String(value).replace(/"/g, '""');
        return '"' + str + '"';
    };

    const headerLine = columns.map((column) => sanitize(column.label)).join(delimiter);
    const lines = rows.map((row) => columns.map((column) => {
        const rawValue = typeof column.formatter === 'function' ? column.formatter(row) : row[column.key];
        return sanitize(rawValue);
    }).join(delimiter));

    const content = [headerLine, ...lines].join('\u000A');
    const blob = new Blob([content], { type: format === 'xls' ? 'application/vnd.ms-excel' : 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename + '.' + (format === 'xls' ? 'xls' : 'csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);
}

document.addEventListener("alpine:init", () => {
    Alpine.data("analyticsDashboard", () => ({
        activeTab: "overview",
        ajaxUrl: "",
        nonce: "",
        chartInstance: null,
        profileProtocolChartInstance: null,
        profileModuleChartInstance: null,
        globalStatsError: "",
        userQuery: "",
        userResults: [],
        userSelected: null,
        userViews: [],
        userSort: "recent",
        userLoading: false,
        userError: "",
        userSearchTimeout: null,
        documentTypeFilter: "all",
        documentSelectionId: "",
        documentOptions: Array.isArray(ANALYTICS_DATA.documents) ? ANALYTICS_DATA.documents : [],
        documentDetails: null,
        documentLoading: false,
        documentError: "",
        viewerSort: "recent",
        profileProtocolsData: [],
        profileModulesData: [],
        profileSelectedFilter: "",
        allProfessionalProfiles: [],
        profileRenderTimeout: null,
        profileProtocolMessage: "",
        profileModuleMessage: "",

        init() {
            this.ajaxUrl = this.$refs.dashboard?.dataset.ajaxUrl || "";
            this.nonce = this.$refs.dashboard?.dataset.nonce || "";
            this.fetchGlobalStats();
            this.fetchAllProfessionalProfiles();
            this.fetchProfileViews();
            this.fetchContentDistribution();
        },

        setTab(tab) {
            this.activeTab = tab;
        },

        request(params) {
            if (!this.ajaxUrl) {
                return Promise.reject("Endpoint non disponibile");
            }
            const payload = new URLSearchParams(Object.assign({}, params, { nonce: this.nonce }));
            return fetch(this.ajaxUrl, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: payload
            }).then((response) => response.json());
        },

        // -------------------- Panoramica --------------------
        fetchGlobalStats() {
            const target = this.$refs.globalStats;
            if (!target) {
                return;
            }

            this.globalStatsError = "";

            this.request({ action: "meridiana_analytics_get_global_stats" })
                .then((data) => {
                    if (!data.success) {
                        throw data.data || "Errore";
                    }
                    this.renderGlobalStats(target, data.data);
                })
                .catch((error) => {
                    this.globalStatsError = typeof error === "string" ? error : "Errore nel caricamento delle statistiche.";
                    target.innerHTML = '<div class="notification notification-error">' + this.globalStatsError + "</div>";
                });
        },

        renderGlobalStats(target, stats) {
            const cards = [
                { label: "Utenti Totali", value: stats.total_users },
                { label: "Protocolli Pubblicati", value: stats.total_protocols },
                { label: "Moduli Pubblicati", value: stats.total_modules },
                { label: "Convenzioni Pubblicate", value: stats.total_convenzioni },
                { label: "Articoli Salute", value: stats.total_salute_benessere },
                { label: "Comunicazioni Pubblicate", value: stats.total_comunicazioni },
                { label: "Protocolli ATS", value: stats.total_ats_protocols }
            ];

            const markup = cards
                .map((card) => {
                    return (
                        '<div class="stat-card">' +
                        '<span class="stat-card__value">' + (card.value || 0) + '</span>' +
                        '<span class="stat-card__label">' + card.label + '</span>' +
                        "</div>"
                    );
                })
                .join("");

            target.innerHTML = markup;
        },

        fetchContentDistribution() {
            const canvas = this.$refs.contentChart;
            if (!canvas || typeof Chart === "undefined") {
                return;
            }

            this.request({ action: "meridiana_analytics_get_content_distribution" })
                .then((data) => {
                    if (!data.success) {
                        throw data.data || "Errore";
                    }
                    this.renderDistributionChart(canvas, data.data || []);
                })
                .catch((error) => {
                    console.error("Errore grafico analitiche:", error);
                });
        },

        renderDistributionChart(canvas, dataset) {
            if (!dataset.length) {
                canvas.parentElement.innerHTML = '<p class="analytics-empty">Nessuna visualizzazione registrata finora.</p>';
                return;
            }

            const labels = dataset.map((item) => this.formatDocumentType(item.document_type));
            const views = dataset.map((item) => Number(item.view_count));
            const unique = dataset.map((item) => Number(item.unique_users));

            if (this.chartInstance) {
                this.chartInstance.destroy();
            }

            this.chartInstance = new Chart(canvas, {
                type: "bar",
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: "Visualizzazioni",
                            data: views,
                            backgroundColor: ANALYTICS_CHART_COLORS,
                            borderColor: ANALYTICS_CHART_BORDERS,
                            borderWidth: 1
                        },
                        {
                            label: "Utenti unici",
                            data: unique,
                            backgroundColor: "rgba(15, 118, 110, 0.15)",
                            borderColor: "rgba(15, 118, 110, 0.8)",
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        },

        // -------------------- Profili Professionali Disponibili --------------------
        fetchAllProfessionalProfiles() {
            this.request({ action: "meridiana_analytics_get_all_professional_profiles" })
                .then((data) => {
                    if (data.success) {
                        this.allProfessionalProfiles = data.data || [];
                    }
                })
                .catch((error) => {
                    console.error("Errore caricamento profili professionali:", error);
                });
        },

        // -------------------- Visualizzazioni per Profilo Professionale --------------------
        fetchProfileViews() {
            if (typeof Chart === "undefined") {
                return;
            }

            Promise.all([
                this.request({ action: "meridiana_analytics_get_views_by_profile_protocols" }),
                this.request({ action: "meridiana_analytics_get_views_by_profile_modules" })
            ])
            .then(([protocolsResponse, modulesResponse]) => {
                if (protocolsResponse.success) {
                    this.profileProtocolsData = protocolsResponse.data || [];
                }
                if (modulesResponse.success) {
                    this.profileModulesData = modulesResponse.data || [];
                }
                // Renderizza dopo che entrambi i dati sono caricati
                this.$nextTick(() => {
                    this.renderProfileCharts();
                });
            })
            .catch((error) => {
                console.error("Errore caricamento profili:", error);
            });
        },

        getAllProfilesUnion() {
            const protocolProfiles = this.profileProtocolsData.map(item => item.profilo_professionale);
            const moduleProfiles = this.profileModulesData.map(item => item.profilo_professionale);
            const allProfiles = [...new Set([...protocolProfiles, ...moduleProfiles])];
            return allProfiles.sort();
        },

        renderProfileChart(canvas, dataset, type, selectedFilter = "") {
            const chartRef = type === 'protocols' ? 'profileProtocolChartInstance' : 'profileModuleChartInstance';
            const messageRef = type === 'protocols' ? 'profileProtocolMessage' : 'profileModuleMessage';

            // DEBUG: Log dei dati
            console.log(`[${type}] Dataset:`, dataset);
            console.log(`[${type}] SelectedFilter:`, selectedFilter);

            // Distruggi il grafico se esiste
            if (this[chartRef]) {
                this[chartRef].destroy();
                this[chartRef] = null;
            }

            if (!canvas || !dataset || !dataset.length) {
                this[messageRef] = "";
                return;
            }

            // Filtra i dati in base al profilo selezionato
            let filteredData = dataset;

            // Se selectedFilter non è vuoto, filtra per quel profilo
            if (selectedFilter) {
                filteredData = dataset.filter(item => item.profilo_professionale === selectedFilter);
                console.log(`[${type}] FilteredData:`, filteredData);
            }

            // Se non ci sono dati filtrati, mostra un messaggio
            if (!filteredData || !filteredData.length) {
                const typeLabel = type === 'protocols' ? 'protocolli' : 'moduli';
                this[messageRef] = `Questo profilo professionale non ha visualizzato ${typeLabel}.`;
                return;
            }

            const labels = filteredData.map((item) => item.profilo_professionale);
            const data = filteredData.map((item) => Number(item.unique_users));

            // Doppio controllo - se i dati sono vuoti, mostra messaggio
            if (!labels || !labels.length || !data || !data.length) {
                const typeLabel = type === 'protocols' ? 'protocolli' : 'moduli';
                this[messageRef] = `Questo profilo professionale non ha visualizzato ${typeLabel}.`;
                return;
            }

            // Resetta il messaggio
            this[messageRef] = "";

            // Crea il grafico
            this[chartRef] = new Chart(canvas, {
                type: "bar",
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: "Utenti unici",
                            data: data,
                            backgroundColor: ANALYTICS_CHART_COLORS,
                            borderColor: ANALYTICS_CHART_BORDERS,
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        },

        renderProfileCharts() {
            // Copia il valore del filtro PRIMA di fare qualsiasi render
            const selectedFilter = String(this.profileSelectedFilter);
            const protocolData = Array.isArray(this.profileProtocolsData) ? this.profileProtocolsData.slice() : [];
            const moduleData = Array.isArray(this.profileModulesData) ? this.profileModulesData.slice() : [];

            // Debounce: cancella timeout precedente
            clearTimeout(this.profileRenderTimeout);

            // Esegui il render con debounce semplice (senza $nextTick per evitare loop)
            this.profileRenderTimeout = setTimeout(() => {
                const canvasProtocol = this.$refs.profileProtocolChart;
                const canvasModule = this.$refs.profileModuleChart;

                if (canvasProtocol && protocolData && protocolData.length) {
                    this.renderProfileChart(canvasProtocol, protocolData, 'protocols', selectedFilter);
                }
                if (canvasModule && moduleData && moduleData.length) {
                    this.renderProfileChart(canvasModule, moduleData, 'modules', selectedFilter);
                }
            }, 100); // Debounce di 100ms
        },

        // -------------------- Ricerca utente --------------------
        handleUserQuery() {
            clearTimeout(this.userSearchTimeout);

            if (!this.userQuery || this.userQuery.length < 2) {
                this.userResults = [];
                return;
            }

            this.userSearchTimeout = setTimeout(() => {
                this.searchUsers();
            }, 250);
        },

        searchUsers() {
            this.request({
                action: "meridiana_analytics_search_users",
                query: this.userQuery
            })
                .then((data) => {
                    this.userResults = data.success ? data.data : [];
                })
                .catch(() => {
                    this.userResults = [];
                });
        },

        resetUserSearch() {
            this.userQuery = "";
            this.userResults = [];
        },

        selectUser(result) {
            this.userSelected = result;
            this.userQuery = result.display_name;
            this.userResults = [];
            this.fetchUserViews(result.ID);
        },

        fetchUserViews(userId) {
            if (!userId) {
                return;
            }

            this.userLoading = true;
            this.userError = "";
            this.userViews = [];

            this.request({
                action: "meridiana_analytics_get_user_views",
                user_id: userId
            })
                .then((data) => {
                    if (!data.success) {
                        throw data.data || "Errore";
                    }
                    this.userViews = data.data.views || [];
                })
                .catch((error) => {
                    this.userError = typeof error === "string" ? error : "Errore nel recupero delle visualizzazioni.";
                })
                .finally(() => {
                    this.userLoading = false;
                });
        },

        sortedUserViews() {
            const views = Array.isArray(this.userViews) ? this.userViews.slice() : [];

            if (this.userSort === "views") {
                views.sort((a, b) => Number(b.view_count) - Number(a.view_count));
            } else if (this.userSort === "title") {
                views.sort((a, b) => (a.post_title || "").localeCompare(b.post_title || ""));
            } else {
                views.sort((a, b) => {
                    const dateA = new Date(normalizeDate(a.last_view || ""));
                    const dateB = new Date(normalizeDate(b.last_view || ""));
                    return dateB - dateA;
                });
            }

            return views;
        },

        // -------------------- Selezione documento --------------------
        handleDocumentTypeChange() {
            if (this.documentSelectionId) {
                const selected = this.documentOptions.find((doc) => String(doc.ID) === String(this.documentSelectionId));
                if (!selected || !this.isDocumentOptionVisible(selected)) {
                    this.documentSelectionId = "";
                    this.documentDetails = null;
                }
            }
        },

        handleDocumentSelection() {
            if (!this.documentSelectionId) {
                this.documentDetails = null;
                return;
            }
            const selected = this.documentOptions.find((doc) => String(doc.ID) === String(this.documentSelectionId));
            if (selected) {
                this.selectDocument(selected);
            } else {
                this.documentDetails = null;
            }
        },

        filteredDocumentOptions() {
            const list = Array.isArray(this.documentOptions) ? this.documentOptions : [];
            let filtered = list;
            if (this.documentTypeFilter !== "all") {
                filtered = list.filter((doc) => doc.post_type === this.documentTypeFilter);
            }
            return filtered.slice().sort((a, b) => (a.post_title || "").localeCompare(b.post_title || ""));
        },

        isDocumentOptionVisible(doc) {
            if (!doc) {
                return false;
            }
            return this.documentTypeFilter === "all" || doc.post_type === this.documentTypeFilter;
        },

        selectDocument(doc) {
            this.documentSelectionId = String(doc.ID);
            this.fetchDocumentInsights(doc.ID);
        },

        fetchDocumentInsights(documentId) {
            if (!documentId) {
                return;
            }

            this.documentLoading = true;
            this.documentError = "";
            this.documentDetails = null;

            this.request({
                action: "meridiana_analytics_get_document_insights",
                document_id: documentId
            })
                .then((data) => {
                    if (!data.success) {
                        throw data.data || "Errore";
                    }
                    this.documentDetails = {
                        document: data.data.document,
                        viewers: data.data.viewers || [],
                        non_viewers: data.data.non_viewers || [],
                        non_viewers_count: data.data.non_viewers_count || 0
                    };
                })
                .catch((error) => {
                    this.documentError = typeof error === "string" ? error : "Errore nel recupero dei dati.";
                })
                .finally(() => {
                    this.documentLoading = false;
                });
        },

        sortedDocumentViewers() {
            if (!this.documentDetails) {
                return [];
            }

            const viewers = this.documentDetails.viewers.slice();

            if (this.viewerSort === "name") {
                viewers.sort((a, b) => (a.display_name || "").localeCompare(b.display_name || ""));
            } else if (this.viewerSort === "views") {
                viewers.sort((a, b) => Number(b.view_count) - Number(a.view_count));
            } else {
                viewers.sort((a, b) => {
                    const dateA = new Date(normalizeDate(a.last_view || ""));
                    const dateB = new Date(normalizeDate(b.last_view || ""));
                    return dateB - dateA;
                });
            }

            return viewers;
        },

        limitedNonViewers(limit) {
            limit = limit || 25;
            if (!this.documentDetails) {
                return [];
            }
            return this.documentDetails.non_viewers.slice(0, limit);
        },

        exportUserViews(format = 'csv') {
            if (!this.userSelected || !this.userViews.length) {
                return;
            }

            const rows = this.sortedUserViews();
            const columns = [
                { key: 'post_title', label: 'Documento' },
                { key: 'post_type', label: 'Tipo', formatter: (row) => this.formatDocumentType(row.post_type) },
                { key: 'view_count', label: 'Visualizzazioni' },
                { key: 'last_view', label: 'Ultima visualizzazione', formatter: (row) => formatDateValue(row.last_view) },
            ];

            downloadDataset(rows, columns, `analytics-utente-${this.userSelected.ID}`, format);
        },

        exportDocumentViewers(format = 'csv') {
            if (!this.documentDetails || !this.documentDetails.viewers.length) {
                return;
            }

            const rows = this.sortedDocumentViewers();
            const docId = this.documentDetails.document.id;
            const columns = [
                { key: 'display_name', label: 'Nome' },
                { key: 'user_email', label: 'Email' },
                { key: 'view_count', label: 'Visualizzazioni' },
                { key: 'last_view', label: 'Ultima visualizzazione', formatter: (row) => formatDateValue(row.last_view) },
            ];

            downloadDataset(rows, columns, `analytics-documento-${docId}-viewers`, format);
        },

        exportDocumentNonViewers(format = 'csv') {
            if (!this.documentDetails || !this.documentDetails.non_viewers.length) {
                return;
            }

            const rows = this.documentDetails.non_viewers.slice();
            const docId = this.documentDetails.document.id;
            const columns = [
                { key: 'display_name', label: 'Nome' },
                { key: 'user_email', label: 'Email' },
            ];

            downloadDataset(rows, columns, `analytics-documento-${docId}-non-viewers`, format);
        },

        formatDocumentType(type) {
            const typeMap = {
                "protocollo": "Protocolli",
                "modulo": "Moduli",
                "convenzione": "Convenzioni",
                "salute-e-benessere-l": "Salute & Benessere",
                "post": "Comunicazioni"
            };

            if (typeMap[type]) {
                return typeMap[type];
            }

            return type ? type.charAt(0).toUpperCase() + type.slice(1) : "";
        },

        formatDate(value) {
            return formatDateValue(value);
        }
    }));
});
