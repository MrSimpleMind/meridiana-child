<?php
/**
 * Template Name: Pagina Analitiche
 *
 * @package Meridiana Child
 */

if (!current_user_can('gestore_piattaforma') && !current_user_can('manage_options')) {
    wp_redirect(home_url());
    exit;
}

get_header();
?>

<div class="content-wrapper">
    <?php get_template_part('templates/parts/navigation/desktop-sidebar'); ?>
    
    <main class="page-analitiche">
        <div class="analitiche-dashboard"
             data-ajax-url="<?php echo esc_attr(admin_url('admin-ajax.php')); ?>"
             data-nonce="<?php echo esc_attr(wp_create_nonce('wp_rest')); ?>"
             x-data="analyticsDashboard()"
             x-ref="dashboard"
             x-cloak>

            <div class="dashboard-tabs-container">
                <div class="container">
                    <div class="dashboard-tabs">
                        <button type="button" class="dashboard-tabs__item" :class="{ 'active': activeTab === 'overview' }" @click="setTab('overview')">
                            <i data-lucide="activity"></i>
                            <span>Panoramica</span>
                        </button>
                        <button type="button" class="dashboard-tabs__item" :class="{ 'active': activeTab === 'matrix' }" @click="setTab('matrix')">
                            <i data-lucide="grid-3x3"></i>
                            <span>Matrice</span>
                        </button>
                        <button type="button" class="dashboard-tabs__item" :class="{ 'active': activeTab === 'documents' }" @click="setTab('documents')">
                            <i data-lucide="file-text"></i>
                            <span>Analisi documenti</span>
                        </button>
                        <button type="button" class="dashboard-tabs__item" :class="{ 'active': activeTab === 'users' }" @click="setTab('users')">
                            <i data-lucide="user"></i>
                            <span>Analisi utenti</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="dashboard-content-container">
                <div class="container">
                    <div class="dashboard-content">
                        <div class="dashboard-tab-pane" x-show="activeTab === 'overview'" x-cloak>
                            <!-- HERO SECTION: Utenti Totali + Breakdown -->
                            <div class="analitiche-users-hero">
                                <div class="analitiche-users-hero__left">
                                    <div class="analitiche-users-hero__number" x-text="globalStatsTotalUsers || '—'"></div>
                                    <p class="analitiche-users-hero__subtitle">Utenti attivi</p>

                                    <div class="analitiche-users-hero__status-breakdown">
                                        <div class="status-item">
                                            <span class="status-item__icon" style="background-color: #10b981;"></span>
                                            <span class="status-item__label">Attivi</span>
                                            <span class="status-item__count" x-text="usersStatusBreakdown?.attivo || '0'"></span>
                                        </div>
                                        <div class="status-item">
                                            <span class="status-item__icon" style="background-color: #f59e0b;"></span>
                                            <span class="status-item__label">Sospesi</span>
                                            <span class="status-item__count" x-text="usersStatusBreakdown?.sospeso || '0'"></span>
                                        </div>
                                        <div class="status-item">
                                            <span class="status-item__icon" style="background-color: #ef4444;"></span>
                                            <span class="status-item__label">Licenziati</span>
                                            <span class="status-item__count" x-text="usersStatusBreakdown?.licenziato || '0'"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="analitiche-users-hero__center">
                                    <div class="analytics-loading" x-show="usersBreakdownLoading" x-cloak>
                                        <span class="loading-spinner"><i data-lucide="loader"></i> Caricamento...</span>
                                    </div>
                                    <div class="analitiche-users-hero__chart-container" x-show="!usersBreakdownLoading" x-cloak>
                                        <canvas id="usersBreakdownChart" x-ref="usersBreakdownChart"></canvas>
                                    </div>
                                </div>

                                <div class="analitiche-users-hero__right">
                                    <div class="analitiche-users-hero__legend" x-show="!usersBreakdownLoading" x-cloak>
                                        <template x-for="profile in usersBreakdownProfiles" :key="profile.key">
                                            <div class="legend-item">
                                                <span class="legend-item__dot" :style="'background-color: ' + getProfileColor(profile.key)"></span>
                                                <span class="legend-item__label" x-text="profile.label"></span>
                                                <span class="legend-item__count" x-text="profile.count"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- GRID DI STATISTICHE SECONDARIE -->
                            <div class="analitiche-section analitiche-section--no-shadow">
                                <h2 class="analitiche-section__title">Altre Statistiche</h2>
                                <div class="stats-cards-grid stats-cards-grid--compact" id="globalStatsCards" x-ref="globalStats">
                                    <div class="stat-card loading">
                                        <span class="stat-card__value">...</span>
                                        <span class="stat-card__label">Caricamento Protocolli</span>
                                    </div>
                                    <div class="stat-card loading">
                                        <span class="stat-card__value">...</span>
                                        <span class="stat-card__label">Caricamento Moduli</span>
                                    </div>
                                    <div class="stat-card loading">
                                        <span class="stat-card__value">...</span>
                                        <span class="stat-card__label">Caricamento Convenzioni</span>
                                    </div>
                                    <div class="stat-card loading">
                                        <span class="stat-card__value">...</span>
                                        <span class="stat-card__label">Caricamento Salute &amp; Benessere</span>
                                    </div>
                                    <div class="stat-card loading">
                                        <span class="stat-card__value">...</span>
                                        <span class="stat-card__label">Caricamento Comunicazioni</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="dashboard-tab-pane" x-show="activeTab === 'matrix'" x-cloak>
                            <div class="analitiche-section analitiche-section--matrix">
                                <h2 class="analitiche-section__title">Matrice Protocolli × Profili Professionali</h2>
                                <p class="analitiche-section__description">Visualizzazioni uniche per combinazione protocollo/profilo con percentuale di engagement</p>
                                <div class="protocol-grid-container" x-ref="protocolGrid" x-show="!gridLoading" x-cloak>
                                    <!-- Caricato dinamicamente da Alpine.js -->
                                </div>
                                <div class="analytics-loading" x-show="gridLoading" x-cloak>
                                    <span class="loading-spinner"><i data-lucide="loader"></i> Caricamento griglia...</span>
                                </div>
                                <div class="analytics-error" x-show="gridError" x-cloak>
                                    <p x-text="gridError"></p>
                                </div>
                            </div>
                        </div>

                        <div class="dashboard-tab-pane" x-show="activeTab === 'users'" x-cloak>
                            <section class="analytics-card analytics-card--filters">
                                <div class="analytics-card__header">
                                    <div>
                                        <h3>Analisi per Utente</h3>
                                        <p>Verifica quali documenti ha consultato un collaboratore e quando.</p>
                                    </div>
                                    <div class="analytics-card__status" x-show="userLoading" x-cloak>
                                        <span class="loading-spinner"><i data-lucide="loader"></i> Caricamento...</span>
                                    </div>
                                    <div class="analytics-actions" x-show="userViews.length" x-cloak>
                                        <button type="button"
                                                class="analytics-button analytics-button--primary"
                                                @click="exportUserViews('csv')">Esporta CSV</button>
                                        <button type="button"
                                                class="analytics-button analytics-button--outline"
                                                @click="exportUserViews('xls')">Esporta Excel</button>
                                    </div>
                                </div>

                                <label class="analytics-input-label" for="analytics-user-search">Cerca utente</label>
                                <div class="analytics-search-field">
                                    <div class="analytics-search">
                                        <input id="analytics-user-search"
                                               type="text"
                                               class="analytics-input"
                                               placeholder="Digita nome o email"
                                               x-model.debounce.400ms="userQuery"
                                               @input="handleUserQuery"
                                               autocomplete="off">
                                        <button type="button"
                                                class="analytics-button analytics-button--ghost"
                                                x-show="userQuery"
                                                @click="resetUserSearch"
                                                x-cloak>Reset</button>
                                    </div>

                                    <div class="analytics-search-results" x-show="userResults.length" x-cloak>
                                        <template x-for="result in userResults" :key="result.ID">
                                            <button type="button" class="analytics-search-result" @click="selectUser(result)">
                                                <div>
                                                    <span class="analytics-search-result__name" x-text="result.display_name"></span>
                                                    <span class="analytics-search-result__meta" x-text="result.user_email"></span>
                                                </div>
                                                <span class="analytics-search-result__badge" x-text="result.udo || 'N/D'"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <template x-if="userError">
                                    <p class="analytics-error" x-text="userError"></p>
                                </template>
                            </section>

                            <div class="analytics-results-grid">
                                <template x-if="userSelected">
                                    <div class="analytics-panel" x-cloak>
                                        <div class="analytics-panel__header">
                                            <div>
                                                <h4 x-text="userSelected.display_name"></h4>
                                                <p x-text="userSelected.user_email"></p>
                                            </div>
                                            <div class="analytics-panel__filters">
                                                <label for="analytics-user-sort">Ordina</label>
                                                <select id="analytics-user-sort" class="analytics-input analytics-input--small" x-model="userSort">
                                                    <option value="recent">Più recenti</option>
                                                    <option value="views">Numero visualizzazioni</option>
                                                    <option value="title">Titolo A-Z</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="analytics-empty" x-show="!userViews.length && !userLoading" x-cloak>
                                            <p>Questo utente non ha ancora visualizzato documenti monitorati.</p>
                                        </div>

                                        <div class="analytics-table-wrapper" x-show="userViews.length" x-cloak>
                                            <table class="analytics-table">
                                                <thead>
                                                    <tr>
                                                        <th>Documento</th>
                                                        <th class="is-center">Tipo</th>
                                                        <th class="is-center">Visualizzazioni</th>
                                                        <th>Ultima visualizzazione</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <template x-for="view in sortedUserViews()" :key="view.document_id + '-' + view.document_version">
                                                        <tr>
                                                            <td>
                                                                <span class="analytics-table__title" x-text="view.post_title"></span>
                                                            </td>
                                                            <td class="is-center">
                                                                <span class="analytics-badge" x-text="formatDocumentType(view.post_type)"></span>
                                                            </td>
                                                            <td class="is-center" x-text="view.view_count"></td>
                                                            <td x-text="formatDate(view.last_view)"></td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="dashboard-tab-pane" x-show="activeTab === 'documents'" x-cloak>
                            <section class="analytics-card analytics-card--filters">
                                <div class="analytics-card__header">
                                    <div>
                                        <h3>Analisi per Documento</h3>
                                        <p>Scopri chi ha letto ogni protocollo o modulo.</p>
                                    </div>
                                    <div class="analytics-card__status" x-show="documentLoading" x-cloak>
                                        <span class="loading-spinner"><i data-lucide="loader"></i> Caricamento...</span>
                                    </div>
                                </div>

                                <div class="analytics-field-group">
                                    <label class="analytics-input-label" for="analytics-document-type">Tipo di documento</label>
                                    <select id="analytics-document-type" class="analytics-input" x-model="documentTypeFilter" @change="handleDocumentTypeChange">
                                        <option value="all">Tutti</option>
                                        <option value="protocollo">Protocolli</option>
                                        <option value="modulo">Moduli</option>
                                    </select>
                                </div>

                                <div class="analytics-field-group">
                                    <label class="analytics-input-label" for="analytics-document-select">Documento</label>
                                    <div class="analytics-select-field">
                                        <select id="analytics-document-select"
                                                class="analytics-input"
                                                x-model="documentSelectionId"
                                                @change="handleDocumentSelection">
                                            <option value="">Seleziona documento</option>
                                            <template x-for="doc in filteredDocumentOptions()" :key="doc.ID">
                                                <option :value="doc.ID" x-text="doc.post_title"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>

                                <div class="analytics-field-group">
                                    <label class="analytics-input-label" for="analytics-profile-filter">Profilo Professionale</label>
                                    <select id="analytics-profile-filter" class="analytics-input" x-model="documentProfileFilter" @change="handleDocumentProfileChange">
                                        <option value="">Tutti i profili</option>
                                        <template x-for="profile in availableProfiles" :key="profile.key">
                                            <option :value="profile.key" x-text="profile.label"></option>
                                        </template>
                                    </select>
                                </div>

                                <template x-if="documentError">
                                    <p class="analytics-error" x-text="documentError"></p>
                                </template>
                            </section>

                            <div class="analytics-results-grid">
                                <template x-if="documentDetails">
                                    <div class="analytics-panel" x-cloak>
                                        <div class="analytics-panel__header">
                                            <div>
                                                <h4 x-text="documentDetails.document.title"></h4>
                                                <p><span class="analytics-badge" x-text="formatDocumentType(documentDetails.document.type)"></span></p>
                                            </div>
                                            <div class="analytics-panel__stats">
                                                <div class="analytics-pill">
                                                    <strong x-text="documentDetails.viewers.length"></strong>
                                                    <span>hanno visualizzato</span>
                                                </div>
                                                <div class="analytics-pill">
                                                    <strong x-text="documentDetails.non_viewers_count"></strong>
                                                    <span>non hanno ancora visto</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="analytics-panel__body analytics-panel__body--split">
                                            <div>
                                                <div class="analytics-panel__subheader">
                                                    <h5>Utenti che hanno visualizzato</h5>
                                                    <div class="analytics-actions" x-show="documentDetails.viewers.length" x-cloak>
                                                        <button type="button" class="analytics-button analytics-button--primary" @click="exportDocumentViewers('csv')">CSV</button>
                                                        <button type="button" class="analytics-button analytics-button--outline" @click="exportDocumentViewers('xls')">Excel</button>
                                                    </div>
                                                    <select class="analytics-input analytics-input--small" x-model="viewerSort">
                                                        <option value="recent">Più recenti</option>
                                                        <option value="name">Nome A-Z</option>
                                                        <option value="views">Numero visualizzazioni</option>
                                                    </select>
                                                </div>
                                                <div class="analytics-table-wrapper">
                                                    <table class="analytics-table">
                                                        <thead>
                                                            <tr>
                                                                <th>Utente</th>
                                                                <th>Data visualizzazione</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <template x-for="viewer in sortedDocumentViewers()" :key="viewer.user_id">
                                                                <tr>
                                                                    <td>
                                                                        <span class="analytics-table__title" x-text="viewer.display_name"></span>
                                                                        <span class="analytics-table__subtitle" x-text="viewer.user_email"></span>
                                                                    </td>
                                                                    <td x-text="formatDate(viewer.last_view)"></td>
                                                                </tr>
                                                            </template>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="analytics-panel__subheader">
                                                    <h5>Utenti che non hanno visualizzato</h5>
                                                    <div class="analytics-actions" x-show="documentDetails.non_viewers.length" x-cloak>
                                                        <button type="button" class="analytics-button analytics-button--primary" @click="exportDocumentNonViewers('csv')">CSV</button>
                                                        <button type="button" class="analytics-button analytics-button--outline" @click="exportDocumentNonViewers('xls')">Excel</button>
                                                    </div>
                                                </div>
                                                <div class="analytics-scrollable">
                                                    <template x-if="documentDetails.non_viewers.length">
                                                        <ul class="analytics-list">
                                                            <template x-for="user in limitedNonViewers()" :key="user.ID">
                                                                <li>
                                                                    <span class="analytics-table__title" x-text="user.display_name"></span>
                                                                    <span class="analytics-table__subtitle" x-text="user.user_email"></span>
                                                                </li>
                                                            </template>
                                                        </ul>
                                                    </template>
                                                    <div class="analytics-empty" x-show="!documentDetails.non_viewers.length" x-cloak>
                                                        <p>Tutti gli utenti attivi hanno visualizzato questo documento.</p>
                                                    </div>
                                                    <p class="analytics-hint" x-show="documentDetails.non_viewers.length > limitedNonViewers().length" x-cloak>
                                                        Mostrati i primi <span x-text="limitedNonViewers().length"></span> su <span x-text="documentDetails.non_viewers.length"></span> utenti.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php
get_footer();
