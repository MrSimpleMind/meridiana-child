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

// Mapping tra nomi lunghi (dropdown) e nomi brevi (database)
const PROFILE_NAME_MAPPING = {
    'coordinatore unità di offerta': 'coordinatore',
    'coordinatore': 'coordinatore',
    'addetto manutenzione': 'addetto_manutenzione',
    'asa/oss': 'asa_oss',
    'assistente sociale': 'assistente_sociale',
    'educatore': 'educatore',
    'fkt': 'fkt',
    'impiegato amministrativo': 'impiegato_amministrativo',
    'infermiere': 'infermiere',
    'logopedista': 'logopedista',
    'medico': 'medico',
    'psicologa': 'psicologa',
    'receptionista': 'receptionista',
    'terapista occupazionale': 'terapista_occupazionale',
    'volontari': 'volontari'
};

function normalizeProfileName(profileName) {
    if (!profileName) return '';
    const normalized = profileName.toLowerCase().replace(/_/g, ' ').trim();
    return PROFILE_NAME_MAPPING[normalized] || normalized;
}

/**
 * Abbrevia i nomi dei profili per la visualizzazione nella griglia
 * Solo per UI, non cambia il DB
 */
function abbreviateProfileName(fullName) {
    const abbreviations = {
        'Addetto Manutenzione': 'Add. Manutenzione',
        'ASA/OSS': 'ASA/OSS',
        'Assistente Sociale': 'Ass. Sociale',
        'Coordinatore Unità di Offerta': 'Cood. Unità di Offerta',
        'Educatore': 'Educatore',
        'FKT': 'FKT',
        'Impiegato Amministrativo': 'Imp. Amministrativo',
        'Infermiere': 'Infermiere',
        'Logopedista': 'Logopedista',
        'Medico': 'Medico',
        'Psicologa': 'Psicologa',
        'Receptionista': 'Receptionista',
        'Terapista Occupazionale': 'Ter. Occupazionale',
        'Volontari': 'Volontari'
    };
    return abbreviations[fullName] || fullName;
}

/**
 * Converte i nomi dei profili in sigle brevi per le intestazioni della matrice
 */
function getProfileAcronym(fullName) {
    // Normalizza il nome per la ricerca
    const normalized = fullName.toLowerCase().trim();

    const acronyms = {
        'addetto manutenzione': 'MAN',
        'asa/oss': 'OSS',
        'assistente sociale': 'ASOC',
        'coordinatore unità di offerta': 'COORD',
        'coordinatore': 'COORD',
        'educatore': 'EDU',
        'fkt': 'FKT',
        'impiegato amministrativo': 'AMM',
        'infermiere': 'INF',
        'logopedista': 'LOG',
        'medico': 'MED',
        'psicologa': 'PSI',
        'receptionista': 'REC',
        'terapista occupazionale': 'TOCC',
        'volontari': 'VOL'
    };

    return acronyms[normalized] || fullName.substring(0, 3).toUpperCase();
}

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

console.log("[ANALITICHE.JS] File loaded and executing");

document.addEventListener("alpine:init", () => {
    console.log("[ANALITICHE.JS] alpine:init event fired - registering analyticsDashboard component");
    Alpine.data("analyticsDashboard", () => ({
        activeTab: "overview",
        ajaxUrl: "",
        nonce: "",
        gridLoading: false,
        gridError: "",
        protocolGridData: null,
        protocolSearchQuery: "",
        protocolCurrentPage: 1,
        protocolPageSize: 20,
        protocolTotalPages: 1,
        protocolFilteredData: [],
        chartInstance: null,
        profileProtocolChartInstance: null,
        profileModuleChartInstance: null,
        usersBreakdownChartInstance: null,
        usersBreakdownLoading: false,
        usersBreakdownProfiles: [],
        usersStatusBreakdown: { attivo: 0, sospeso: 0, licenziato: 0 },
        globalStatsTotalUsers: 0,
        globalStatsError: "",
        // Colori per i profili
        profileColors: [
            'rgba(6, 182, 212, 0.8)',      // Cyan
            'rgba(34, 197, 94, 0.8)',      // Green
            'rgba(249, 115, 22, 0.8)',     // Orange
            'rgba(168, 85, 247, 0.8)',     // Purple
            'rgba(59, 130, 246, 0.8)',     // Blue
            'rgba(239, 68, 68, 0.8)',      // Red
            'rgba(236, 72, 153, 0.8)',     // Pink
            'rgba(30, 144, 255, 0.8)',     // DodgerBlue
            'rgba(255, 165, 0, 0.8)',      // Orange
            'rgba(72, 219, 251, 0.8)',     // LightBlue
            'rgba(156, 39, 176, 0.8)',     // Purple
            'rgba(0, 188, 212, 0.8)',      // Cyan
            'rgba(76, 175, 80, 0.8)',      // Green
            'rgba(244, 67, 54, 0.8)',      // Red
        ],
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
        // Memoria: dati aggregati di TUTTI i profili, caricati una sola volta
        allProfilesProtocolsMemory: {},
        allProfilesModulesMemory: {},
        profilesDataLoaded: false,

        init() {
            // Leggi gli attributi data-* direttamente da this.$el (elemento con x-data)
            this.ajaxUrl = this.$el?.dataset?.ajaxUrl || "";
            this.nonce = this.$el?.dataset?.nonce || "";

            console.log("[analyticsDashboard.init] ajaxUrl:", this.ajaxUrl);
            console.log("[analyticsDashboard.init] nonce:", this.nonce);

            this.fetchGlobalStats();
            this.fetchUsersBreakdown();
            this.fetchAllProfessionalProfiles();
            // Carica i dati di TUTTI i profili in memoria (una sola volta)
            this.loadAllProfilesDataInMemory();
            this.fetchContentDistribution();
            // Carica la griglia protocolli × profili
            this.fetchProtocolGrid();
        },

        // -------------------- Griglia Protocolli × Profili --------------------
        fetchProtocolGrid() {
            console.log("[fetchProtocolGrid] START");
            console.log("[fetchProtocolGrid] nonce:", this.nonce);

            this.gridLoading = true;
            this.gridError = "";

            const headers = {
                'Content-Type': 'application/json'
            };

            // Aggiungi nonce se disponibile
            if (this.nonce) {
                headers['X-WP-Nonce'] = this.nonce;
            }

            console.log("[fetchProtocolGrid] headers:", headers);

            fetch('/wp-json/piattaforma/v1/analytics/protocol-grid', {
                method: 'GET',
                headers: headers
            })
            .then(response => response.json())
            .then(data => {
                console.log("[fetchProtocolGrid] Response:", data);

                if (!data.success) {
                    throw new Error(data.data?.message || 'Errore nel caricamento della griglia');
                }

                this.protocolGridData = data.data;
                this.$nextTick(() => {
                    this.renderProtocolGrid();
                });
            })
            .catch(error => {
                console.error("[fetchProtocolGrid] Error:", error);
                this.gridError = error.message || 'Errore nel caricamento della griglia';
            })
            .finally(() => {
                this.gridLoading = false;
            });
        },

        renderProtocolGrid() {
            console.log("[renderProtocolGrid] START");

            const container = this.$refs.protocolGrid;
            if (!container || !this.protocolGridData) {
                return;
            }

            const { protocols, profile_headers, total_protocols, total_profiles } = this.protocolGridData;

            console.log("[renderProtocolGrid] Total protocols:", total_protocols);
            console.log("[renderProtocolGrid] Total profiles:", total_profiles);
            console.log("[renderProtocolGrid] Protocols data:", protocols);

            if (!protocols || protocols.length === 0) {
                container.innerHTML = '<div class="analytics-empty"><p>Nessun protocollo pubblicato nel sistema.</p></div>';
                return;
            }

            // Inizializza i dati filtrati se non sono già stati inizializzati
            if (this.protocolFilteredData.length === 0 && !this.protocolSearchQuery) {
                this.protocolFilteredData = protocols.slice();
                this.calculateTotalPages();
            }

            // Ottieni i protocolli paginati
            const paginatedProtocols = this.getPaginatedProtocols();

            console.log("[renderProtocolGrid] Filtered protocols:", this.protocolFilteredData.length);
            console.log("[renderProtocolGrid] Paginated protocols:", paginatedProtocols.length);
            console.log("[renderProtocolGrid] Current page:", this.protocolCurrentPage);
            console.log("[renderProtocolGrid] Total pages:", this.protocolTotalPages);

            // Intestazioni e contatori + legenda
            let html = `
                <div class="protocol-grid-outer">
                    <div class="protocol-grid-main">
                        <div class="protocol-grid-header">
                            <div class="protocol-grid-info">
                                <span class="protocol-grid-info__item">Protocolli: <strong>${total_protocols}</strong></span>
                                <span class="protocol-grid-info__item">Profili: <strong>${total_profiles}</strong></span>
                            </div>
                        </div>
                        <div class="protocol-grid-legend-horizontal">
                            <h4 class="protocol-grid-legend-title-inline">Legenda:</h4>
                            <div class="protocol-grid-legend-inline">
                                <div class="protocol-grid-legend__item-inline">
                                    <span class="protocol-grid-legend__color protocol-grid-legend__color--green"></span>
                                    <span>≥ 75% <span class="protocol-grid-legend__sublabel-inline">(Eccellente)</span></span>
                                </div>
                                <div class="protocol-grid-legend__item-inline">
                                    <span class="protocol-grid-legend__color protocol-grid-legend__color--yellow"></span>
                                    <span>50-75% <span class="protocol-grid-legend__sublabel-inline">(Buono)</span></span>
                                </div>
                                <div class="protocol-grid-legend__item-inline">
                                    <span class="protocol-grid-legend__color protocol-grid-legend__color--orange"></span>
                                    <span>25-50% <span class="protocol-grid-legend__sublabel-inline">(Medio)</span></span>
                                </div>
                                <div class="protocol-grid-legend__item-inline">
                                    <span class="protocol-grid-legend__color protocol-grid-legend__color--red"></span>
                                    <span>&lt; 25% <span class="protocol-grid-legend__sublabel-inline">(Scarso)</span></span>
                                </div>
                                <div class="protocol-grid-legend__item-inline">
                                    <span class="protocol-grid-legend__color protocol-grid-legend__color--empty"></span>
                                    <span>0% <span class="protocol-grid-legend__sublabel-inline">(Non visto)</span></span>
                                </div>
                            </div>
                        </div>
                        <div class="protocol-grid-container-split">
                            <!-- TABELLA FISSA (sinistra) -->
                            <div class="protocol-grid-fixed-col">
                                <table class="protocol-grid-table-fixed">
                                    <thead>
                                        <tr>
                                            <th class="protocol-grid__th-protocol">
                                                <div class="protocol-search-wrapper">
                                                    <input type="text"
                                                           class="protocol-search-input"
                                                           placeholder="Cerca protocollo..."
                                                           x-model="protocolSearchQuery"
                                                           @input.debounce.300ms="handleProtocolSearch()"
                                                           @keydown.escape="protocolSearchQuery = ''; handleProtocolSearch()">
                                                    <span class="protocol-search-icon" x-show="!protocolSearchQuery">
                                                        <i data-lucide="search"></i>
                                                    </span>
                                                    <button type="button"
                                                            class="protocol-search-clear"
                                                            x-show="protocolSearchQuery"
                                                            @click="protocolSearchQuery = ''; handleProtocolSearch()"
                                                            x-cloak>
                                                        <i data-lucide="x"></i>
                                                    </button>
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
            `;

            // Righe protocolli (solo nella tabella fissa)
            paginatedProtocols.forEach(protocol => {
                html += `<tr class="protocol-grid__row">
                    <td class="protocol-grid__td-protocol" title="${protocol.document_title}">
                        <strong>${protocol.document_title}</strong>
                    </td>
                </tr>`;
            });

            html += `
                                    </tbody>
                                </table>
                            </div>

                            <!-- TABELLA SCROLLABILE (destra) -->
                            <div class="protocol-grid-scroll-wrapper">
                                <table class="protocol-grid-table-scroll">
                                    <thead>
                                        <tr>
            `;

            // Intestazioni profili
            if (profile_headers && profile_headers.length > 0) {
                profile_headers.forEach(header => {
                    html += `<th class="protocol-grid__th-profile" title="${header.name} (${header.total_users} utenti)">
                        <span class="protocol-grid__th-name">${getProfileAcronym(header.name)}</span>
                        <span class="protocol-grid__th-count">(${header.total_users})</span>
                    </th>`;
                });
            }

            html += `
                                        </tr>
                                    </thead>
                                    <tbody>
            `;

            // Righe protocolli (dati nella tabella scrollabile)
            paginatedProtocols.forEach(protocol => {
                html += `<tr class="protocol-grid__row">
                `;

                if (profile_headers && profile_headers.length > 0) {
                    profile_headers.forEach(header => {
                        const profileData = protocol.profiles[header.name];
                        const cellClass = this.getGridCellClass(profileData?.percentage || 0);

                        // Se unique_users è 0, mostra come "Non visto"
                        const cellContent = profileData && profileData.unique_users > 0
                            ? `<span class="protocol-grid__count">${profileData.unique_users}</span><span class="protocol-grid__percentage">${profileData.percentage}%</span>`
                            : '<span class="protocol-grid__empty">—</span>';

                        html += `<td class="protocol-grid__td-profile ${cellClass}" title="Visualizzazioni: ${profileData?.unique_users || 0} su ${profileData?.total_users || 0}">
                            ${cellContent}
                        </td>`;
                    });
                }

                html += `</tr>`;
            });

            html += `
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Controlli Paginazione -->
                        <div class="protocol-pagination">
                            <div class="protocol-pagination__top">
                                <div class="protocol-pagination__info">
                                    <span>Mostra <strong>${paginatedProtocols.length}</strong> di <strong>${this.protocolFilteredData.length}</strong> protocolli</span>
                                </div>
                                <div class="protocol-pagination__size-control">
                                    <label for="protocolPageSize" class="protocol-pagination__label">Righe per pagina:</label>
                                    <select id="protocolPageSize"
                                            class="protocol-pagination__select"
                                            x-model="protocolPageSize"
                                            @change="changeProtocolPageSize($event.target.value)">
                                        <option value="10">10</option>
                                        <option value="20">20</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="200">200</option>
                                    </select>
                                </div>
                            </div>
                            <div class="protocol-pagination__bottom">
                                <div class="protocol-pagination__buttons">
                                    <button type="button"
                                            class="protocol-pagination__btn"
                                            @click="changeProtocolPage(1)"
                                            :disabled="protocolCurrentPage === 1">
                                        <i data-lucide="chevrons-left"></i>
                                    </button>
                                    <button type="button"
                                            class="protocol-pagination__btn"
                                            @click="changeProtocolPage(protocolCurrentPage - 1)"
                                            :disabled="protocolCurrentPage === 1">
                                        <i data-lucide="chevron-left"></i>
                                    </button>
                                    <span class="protocol-pagination__page">
                                        Pagina <strong x-text="protocolCurrentPage"></strong> di <strong x-text="protocolTotalPages"></strong>
                                    </span>
                                    <button type="button"
                                            class="protocol-pagination__btn"
                                            @click="changeProtocolPage(protocolCurrentPage + 1)"
                                            :disabled="protocolCurrentPage === protocolTotalPages">
                                        <i data-lucide="chevron-right"></i>
                                    </button>
                                    <button type="button"
                                            class="protocol-pagination__btn"
                                            @click="changeProtocolPage(protocolTotalPages)"
                                            :disabled="protocolCurrentPage === protocolTotalPages">
                                        <i data-lucide="chevrons-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            container.innerHTML = html;
            console.log("[renderProtocolGrid] COMPLETE - Rendered " + protocols.length + " protocols");

            // Sincronizza scroll verticale tra tabella fissa e scrollabile
            this.$nextTick(() => {
                this.setupSyncScroll();
            });
        },

        setupSyncScroll() {
            const scrollWrapper = document.querySelector('.protocol-grid-scroll-wrapper');
            const fixedWrapper = document.querySelector('.protocol-grid-fixed-col');

            if (!scrollWrapper || !fixedWrapper) {
                return;
            }

            console.log("[setupSyncScroll] Setting up synchronized scroll");

            // Sincronizza scroll verticale: quando scrolli la colonna destra, la sinistra scorre di conseguenza
            scrollWrapper.addEventListener('scroll', () => {
                fixedWrapper.scrollTop = scrollWrapper.scrollTop;
            });

            // Sincronizza anche l'inverso: quando scrolli la colonna sinistra, la destra segue
            fixedWrapper.addEventListener('scroll', () => {
                scrollWrapper.scrollTop = fixedWrapper.scrollTop;
            });
        },

        getGridCellClass(percentage) {
            if (percentage >= 75) return 'protocol-grid__cell--excellent';
            if (percentage >= 50) return 'protocol-grid__cell--good';
            if (percentage >= 25) return 'protocol-grid__cell--medium';
            return 'protocol-grid__cell--poor';
        },

        // -------------------- Paginazione e Ricerca Protocolli --------------------
        filterProtocolsBySearch() {
            if (!this.protocolGridData || !this.protocolGridData.protocols) {
                this.protocolFilteredData = [];
                return;
            }

            const query = this.protocolSearchQuery.toLowerCase().trim();

            if (!query) {
                this.protocolFilteredData = this.protocolGridData.protocols.slice();
            } else {
                this.protocolFilteredData = this.protocolGridData.protocols.filter(protocol => {
                    return protocol.document_title.toLowerCase().includes(query);
                });
            }

            // Resetta alla prima pagina quando si filtra
            this.protocolCurrentPage = 1;
            this.calculateTotalPages();
        },

        calculateTotalPages() {
            const total = this.protocolFilteredData.length;
            this.protocolTotalPages = Math.ceil(total / this.protocolPageSize) || 1;

            // Se la pagina corrente è oltre il totale, torna all'ultima pagina valida
            if (this.protocolCurrentPage > this.protocolTotalPages) {
                this.protocolCurrentPage = this.protocolTotalPages;
            }
        },

        getPaginatedProtocols() {
            const startIndex = (this.protocolCurrentPage - 1) * this.protocolPageSize;
            const endIndex = startIndex + this.protocolPageSize;
            return this.protocolFilteredData.slice(startIndex, endIndex);
        },

        changeProtocolPage(newPage) {
            if (newPage < 1 || newPage > this.protocolTotalPages) {
                return;
            }
            this.protocolCurrentPage = newPage;
            this.$nextTick(() => {
                this.renderProtocolGrid();
            });
        },

        changeProtocolPageSize(newSize) {
            this.protocolPageSize = parseInt(newSize);
            this.protocolCurrentPage = 1;
            this.calculateTotalPages();
            this.$nextTick(() => {
                this.renderProtocolGrid();
            });
        },

        handleProtocolSearch() {
            this.filterProtocolsBySearch();
            this.$nextTick(() => {
                this.renderProtocolGrid();
            });
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
            // Salva il totale utenti per usarlo nella hero section
            this.globalStatsTotalUsers = stats.total_users || 0;

            const cards = [
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

        // -------------------- Breakdown Utenti per Profilo --------------------
        fetchUsersBreakdown() {
            console.log("[fetchUsersBreakdown] START");

            this.usersBreakdownLoading = true;

            this.request({ action: "meridiana_analytics_get_users_by_profile" })
                .then((data) => {
                    console.log("[fetchUsersBreakdown] Response:", data);
                    if (!data.success) {
                        throw data.data || "Errore nel caricamento";
                    }

                    // Estrai profili e status dall'oggetto data.data
                    this.usersBreakdownProfiles = data.data.profiles || [];
                    this.usersStatusBreakdown = data.data.status || { attivo: 0, sospeso: 0, licenziato: 0 };

                    console.log("[fetchUsersBreakdown] Profiles:", this.usersBreakdownProfiles);
                    console.log("[fetchUsersBreakdown] Status:", this.usersStatusBreakdown);

                    this.$nextTick(() => {
                        this.renderUsersBreakdownChart();
                    });
                })
                .catch((error) => {
                    console.error("[fetchUsersBreakdown] Error:", error);
                })
                .finally(() => {
                    this.usersBreakdownLoading = false;
                });
        },

        renderUsersBreakdownChart() {
            console.log("[renderUsersBreakdownChart] START");

            const canvas = this.$refs.usersBreakdownChart;
            if (!canvas || typeof Chart === "undefined" || !this.usersBreakdownProfiles || this.usersBreakdownProfiles.length === 0) {
                return;
            }

            // Prepara i dati per il chart (pie)
            const labels = this.usersBreakdownProfiles.map(item => item.label);
            const counts = this.usersBreakdownProfiles.map(item => item.count);
            const colors = this.usersBreakdownProfiles.map((item, idx) => this.profileColors[idx % this.profileColors.length]);

            if (this.usersBreakdownChartInstance) {
                this.usersBreakdownChartInstance.destroy();
            }

            this.usersBreakdownChartInstance = new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: counts,
                        backgroundColor: colors,
                        borderColor: colors.map(c => c.replace('0.8', '1')),
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false  // Usiamo la legenda custom a destra
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ' + context.parsed + ' utenti';
                                }
                            }
                        }
                    }
                }
            });

            console.log("[renderUsersBreakdownChart] COMPLETE");
        },

        getProfileColor(profileKey) {
            const idx = this.usersBreakdownProfiles.findIndex(p => p.key === profileKey);
            return idx >= 0 ? this.profileColors[idx % this.profileColors.length] : '#ccc';
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
                        this.allProfessionalProfiles = data.data.profiles || [];
                        // Potresti voler salvare anche le UDOs se necessario per altri filtri
                        // this.allUdos = data.data.udos || [];
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

        // Carica i dati aggregati di TUTTI i profili e li salva in memoria (una sola volta)
        loadAllProfilesDataInMemory() {
            console.log("[loadAllProfilesDataInMemory] START - Caricamento dati...");

            if (typeof Chart === "undefined") {
                console.warn("[loadAllProfilesDataInMemory] Chart non definito, uscita");
                return;
            }

            console.log("[loadAllProfilesDataInMemory] Ajax URL:", this.ajaxUrl);
            console.log("[loadAllProfilesDataInMemory] Nonce:", this.nonce);

            Promise.all([
                this.request({ action: "meridiana_analytics_get_views_by_profile_protocols" }),
                this.request({ action: "meridiana_analytics_get_views_by_profile_modules" })
            ])
            .then(([protocolsResponse, modulesResponse]) => {
                console.log("[loadAllProfilesDataInMemory] Responses ricevute");
                console.log("Protocols Response:", protocolsResponse);
                console.log("Modules Response:", modulesResponse);

                // Salva in memoria i dati di TUTTI i profili
                if (protocolsResponse.success) {
                    // Crea un oggetto con chiave = profilo NORMALIZZATO A MINUSCOLO, valore = dati
                    const protocolsMap = {};
                    (protocolsResponse.data || []).forEach(item => {
                        console.log("[loadAllProfilesDataInMemory] Protocolo item:", item);
                        // Normalizza a minuscolo e converti underscore in spazi per la ricerca case-insensitive
                        const keyLower = item.profilo_professionale.toLowerCase().replace(/_/g, ' ');
                        protocolsMap[keyLower] = item;
                    });
                    this.allProfilesProtocolsMemory = protocolsMap;
                    console.log("[loadAllProfilesDataInMemory] Protocols map:", this.allProfilesProtocolsMemory);
                } else {
                    console.error("[loadAllProfilesDataInMemory] Protocols response error:", protocolsResponse.data);
                }

                if (modulesResponse.success) {
                    // Crea un oggetto con chiave = profilo NORMALIZZATO A MINUSCOLO, valore = dati
                    const modulesMap = {};
                    (modulesResponse.data || []).forEach(item => {
                        console.log("[loadAllProfilesDataInMemory] Module item:", item);
                        // Normalizza a minuscolo e converti underscore in spazi per la ricerca case-insensitive
                        const keyLower = item.profilo_professionale.toLowerCase().replace(/_/g, ' ');
                        modulesMap[keyLower] = item;
                    });
                    this.allProfilesModulesMemory = modulesMap;
                    console.log("[loadAllProfilesDataInMemory] Modules map:", this.allProfilesModulesMemory);
                } else {
                    console.error("[loadAllProfilesDataInMemory] Modules response error:", modulesResponse.data);
                }

                this.profilesDataLoaded = true;
                console.log("[loadAllProfilesDataInMemory] COMPLETE - Dati caricati in memoria");
            })
            .catch((error) => {
                console.error("[loadAllProfilesDataInMemory] CATCH ERROR:", error);
            });
        },

        // Legge il profilo selezionato dalla memoria e aggiorna i grafici
        fetchProfileViewsWithFilter() {
            console.log("[fetchProfileViewsWithFilter] START");

            if (typeof Chart === "undefined") {
                console.warn("[fetchProfileViewsWithFilter] Chart non definito, uscita");
                return;
            }

            const selectedProfile = String(this.profileSelectedFilter);
            console.log("[fetchProfileViewsWithFilter] selectedProfile:", selectedProfile);
            console.log("[fetchProfileViewsWithFilter] allProfilesProtocolsMemory:", this.allProfilesProtocolsMemory);
            console.log("[fetchProfileViewsWithFilter] allProfilesModulesMemory:", this.allProfilesModulesMemory);

            // Se il selettore è vuoto, mostra il messaggio
            if (!selectedProfile) {
                console.log("[fetchProfileViewsWithFilter] Profile empty, showing message");
                this.profileProtocolsData = [];
                this.profileModulesData = [];
                this.renderProfileCharts();
                return;
            }

            // Legge dalla memoria (dati già caricati all'init)
            // Normalizza il nome del profilo convertendo i nomi lunghi ai nomi brevi
            const selectedProfileLower = normalizeProfileName(selectedProfile);
            console.log("[fetchProfileViewsWithFilter] selectedProfileNormalized:", selectedProfileLower);

            // Se il profilo non ha dati, crea un oggetto con 0
            const protocolData = this.allProfilesProtocolsMemory[selectedProfileLower] || {
                profilo_professionale: selectedProfile,
                unique_users: 0,
                unique_documents: 0
            };

            const moduleData = this.allProfilesModulesMemory[selectedProfileLower] || {
                profilo_professionale: selectedProfile,
                unique_users: 0,
                unique_documents: 0
            };

            // Metti i dati nella memoria locale per il rendering
            this.profileProtocolsData = [protocolData];
            this.profileModulesData = [moduleData];

            console.log("[fetchProfileViewsWithFilter] protocolData:", protocolData);
            console.log("[fetchProfileViewsWithFilter] moduleData:", moduleData);
            console.log("[fetchProfileViewsWithFilter] profileProtocolsData:", this.profileProtocolsData);
            console.log("[fetchProfileViewsWithFilter] profileModulesData:", this.profileModulesData);

            // Renderizza con i dati dalla memoria
            this.$nextTick(() => {
                console.log("[fetchProfileViewsWithFilter] Calling renderProfileCharts");
                this.renderProfileCharts();
            });
        },

        getAllProfilesUnion() {
            const protocolProfiles = this.profileProtocolsData.map(item => item.profilo_professionale);
            const moduleProfiles = this.profileModulesData.map(item => item.profilo_professionale);
            const allProfiles = [...new Set([...protocolProfiles, ...moduleProfiles])];
            return allProfiles.sort();
        },

        renderProfileChart(canvas, dataset, type, selectedFilter = "") {
            console.log(`[renderProfileChart] START - type: ${type}, selectedFilter: ${selectedFilter}`);
            console.log(`[renderProfileChart] canvas:`, canvas);
            console.log(`[renderProfileChart] dataset:`, dataset);

            const chartRef = type === 'protocols' ? 'profileProtocolChartInstance' : 'profileModuleChartInstance';
            const messageRef = type === 'protocols' ? 'profileProtocolMessage' : 'profileModuleMessage';

            console.log(`[renderProfileChart] chartRef: ${chartRef}, messageRef: ${messageRef}`);

            // Distruggi il grafico se esiste
            if (this[chartRef]) {
                console.log(`[renderProfileChart] Destroying existing chart`);
                this[chartRef].destroy();
                this[chartRef] = null;
            }

            if (!canvas || !dataset || !dataset.length) {
                console.warn(`[renderProfileChart] Canvas o dataset vuoto, uscita`);
                this[messageRef] = "";
                return;
            }

            // Filtra i dati in base al profilo selezionato (solo se selectedFilter è presente)
            let filteredData = dataset;

            if (selectedFilter) {
                console.log(`[renderProfileChart] Filtrando per profilo: ${selectedFilter}`);
                // Normalizza il nome del profilo convertendo i nomi lunghi ai nomi brevi
                const selectedFilterNormalized = normalizeProfileName(selectedFilter);
                // Anche i dati del database devono essere normalizzati allo stesso modo
                const itemNormalized = normalizeProfileName(dataset[0]?.profilo_professionale || '');
                filteredData = dataset.filter(item => normalizeProfileName(item.profilo_professionale || '') === selectedFilterNormalized);
                console.log(`[renderProfileChart] selectedFilterNormalized: ${selectedFilterNormalized}`);
                console.log(`[renderProfileChart] Filtered data:`, filteredData);
            }

            // Se non ci sono dati filtrati, mostra un messaggio
            if (!filteredData || !filteredData.length) {
                const typeLabel = type === 'protocols' ? 'protocolli' : 'moduli';
                console.warn(`[renderProfileChart] No filtered data, showing message`);
                this[messageRef] = `Questo profilo professionale non ha visualizzato ${typeLabel}.`;
                return;
            }

            // Determina se i dati contengono post_title (dati per documento) o no (dati aggregati per profilo)
            const hasPostTitle = filteredData.some(item => item.post_title);
            console.log(`[renderProfileChart] hasPostTitle: ${hasPostTitle}`);

            // Genera labels: usa post_title se disponibile, altrimenti usa profilo_professionale
            const labels = filteredData.map((item) => hasPostTitle ? item.post_title : item.profilo_professionale);
            const data = filteredData.map((item) => Number(item.unique_users));

            console.log(`[renderProfileChart] labels:`, labels);
            console.log(`[renderProfileChart] data:`, data);

            // Doppio controllo - se i dati sono vuoti, mostra messaggio
            if (!labels || !labels.length || !data || !data.length) {
                const typeLabel = type === 'protocols' ? 'protocolli' : 'moduli';
                console.warn(`[renderProfileChart] Labels o data vuoti, showing message`);
                this[messageRef] = `Questo profilo professionale non ha visualizzato ${typeLabel}.`;
                return;
            }

            // Resetta il messaggio
            this[messageRef] = "";

            // Crea il grafico
            console.log(`[renderProfileChart] Creating new chart...`);
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

            console.log(`[renderProfileChart] COMPLETE - Chart created`);
        },

        renderProfileCharts() {
            // Copia il valore del filtro PRIMA di fare qualsiasi render
            const selectedFilter = String(this.profileSelectedFilter);
            const protocolData = Array.isArray(this.profileProtocolsData) ? this.profileProtocolsData.slice() : [];
            const moduleData = Array.isArray(this.profileModulesData) ? this.profileModulesData.slice() : [];

            // Se nessun profilo è selezionato, mostra messaggio
            if (!selectedFilter) {
                this.profileProtocolMessage = "Per favore seleziona un profilo professionale";
                this.profileModuleMessage = "Per favore seleziona un profilo professionale";

                // Distruggi i grafici se esistono
                if (this.profileProtocolChartInstance) {
                    this.profileProtocolChartInstance.destroy();
                    this.profileProtocolChartInstance = null;
                }
                if (this.profileModuleChartInstance) {
                    this.profileModuleChartInstance.destroy();
                    this.profileModuleChartInstance = null;
                }

                return;
            }

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
                    if (dateB - dateA !== 0) return dateB - dateA; // Ordina per data
                    // Se le date sono uguali, ordina per versione del documento (più recente prima)
                    const versionA = new Date(normalizeDate(a.document_version || ""));
                    const versionB = new Date(normalizeDate(b.document_version || ""));
                    return versionB - versionA;
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
                    if (dateB - dateA !== 0) return dateB - dateA; // Ordina per data
                    // Se le date sono uguali, ordina per versione del documento (più recente prima)
                    const versionA = new Date(normalizeDate(a.document_version || ""));
                    const versionB = new Date(normalizeDate(b.document_version || ""));
                    return versionB - versionA;
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
                { key: 'document_version', label: 'Versione Documento', formatter: (row) => formatDateValue(row.document_version) },
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
                { key: 'document_version', label: 'Versione Documento', formatter: (row) => formatDateValue(row.document_version) },
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
