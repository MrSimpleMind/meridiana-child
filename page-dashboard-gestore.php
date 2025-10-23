<?php
/**
 * Template: Dashboard Gestore
 * Pagina per la gestione contenuti (Documentazione, Comunicazioni, Convenzioni, Salute, Utenti)
 */

if (!current_user_can('manage_platform') && !current_user_can('manage_options')) {
    wp_redirect(home_url());
    exit;
}

if (function_exists('acf_form_head')) {
    acf_form_head();
}

get_header();
?>

<div class="content-wrapper">
<div class="gestore-dashboard" x-data="gestoreDashboard()" x-cloak>
    <!-- Success Message -->
    <div class="notification notification-success" x-show="successMessage" @click.away="successMessage = ''">
        <i data-lucide="check-circle"></i>
        <span x-text="successMessage"></span>
    </div>

    <!-- Error Message -->
    <div class="notification notification-error" x-show="errorMessage" @click.away="errorMessage = ''">
        <i data-lucide="alert-circle"></i>
        <span x-text="errorMessage"></span>
    </div>
    <div class="dashboard-tabs-container">
        <div class="container">
            <div class="dashboard-tabs">
                <button type="button" class="dashboard-tabs__item" :class="{ 'active': activeTab === 'documenti' }" @click="activeTab = 'documenti'">
                    <i data-lucide="file-text"></i><span>Documentazione</span>
                </button>
                <button type="button" class="dashboard-tabs__item" :class="{ 'active': activeTab === 'comunicazioni' }" @click="activeTab = 'comunicazioni'">
                    <i data-lucide="newspaper"></i><span>Comunicazioni</span>
                </button>
                <button type="button" class="dashboard-tabs__item" :class="{ 'active': activeTab === 'convenzioni' }" @click="activeTab = 'convenzioni'">
                    <i data-lucide="tag"></i><span>Convenzioni</span>
                </button>
                <button type="button" class="dashboard-tabs__item" :class="{ 'active': activeTab === 'salute' }" @click="activeTab = 'salute'">
                    <i data-lucide="heart"></i><span>Salute & Benessere</span>
                </button>
                <button type="button" class="dashboard-tabs__item" :class="{ 'active': activeTab === 'utenti' }" @click="activeTab = 'utenti'">
                    <i data-lucide="users"></i><span>Utenti</span>
                </button>
            </div>
        </div>
    </div>
    
    <div class="dashboard-content-container">
        <div class="container">
            <div class="dashboard-content">
                <div class="dashboard-tab-pane" x-show="activeTab === 'documenti'" x-cloak>
                    <?php get_template_part('templates/parts/gestore/tab-documenti'); ?>
                </div>
                <div class="dashboard-tab-pane" x-show="activeTab === 'comunicazioni'" x-cloak>
                    <?php get_template_part('templates/parts/gestore/tab-comunicazioni'); ?>
                </div>
                <div class="dashboard-tab-pane" x-show="activeTab === 'convenzioni'" x-cloak>
                    <?php get_template_part('templates/parts/gestore/tab-convenzioni'); ?>
                </div>
                <div class="dashboard-tab-pane" x-show="activeTab === 'salute'" x-cloak>
                    <?php get_template_part('templates/parts/gestore/tab-salute'); ?>
                </div>
                <div class="dashboard-tab-pane" x-show="activeTab === 'utenti'" x-cloak>
                    <?php get_template_part('templates/parts/gestore/tab-utenti'); ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal -->
    <div class="dashboard-modal" x-show="modalOpen" x-cloak @keydown.escape="closeModal()">
        <div class="dashboard-modal__overlay" @click="closeModal()"></div>
        <div class="dashboard-modal__body">
            <!-- STEP 1: Scelta Tipo Documento -->
            <template x-if="modalStep === 'choose'">
                <div>
                    <div class="dashboard-modal__header">
                        <h2>Nuovo Documento</h2>
                        <button type="button" class="dashboard-modal__close" @click="closeModal()">
                            <i data-lucide="x"></i>
                        </button>
                    </div>
                    <div class="dashboard-modal__content">
                        <p style="margin-bottom: 24px; text-align: center; color: #666;">Seleziona il tipo di documento da creare:</p>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <button type="button" class="btn btn-primary" @click="selectCPT('protocollo')" :disabled="isLoading">
                                <i data-lucide="file-text" style="margin-right: 8px;"></i>
                                Protocollo
                            </button>
                            <button type="button" class="btn btn-primary" @click="selectCPT('modulo')" :disabled="isLoading">
                                <i data-lucide="file" style="margin-right: 8px;"></i>
                                Modulo
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- STEP 2: Form -->
            <template x-if="modalStep === 'form'">
                <div>
                    <div class="dashboard-modal__header">
                        <h2 x-text="getModalTitle()"></h2>
                        <button type="button" class="dashboard-modal__close" @click="closeModal()">
                            <i data-lucide="x"></i>
                        </button>
                    </div>
                    <div class="dashboard-modal__content" x-ref="modalContent" x-html="modalContent"></div>
                    <div class="dashboard-modal__footer" x-show="modalContent">
                        <button type="button" class="btn btn-secondary" @click="closeModal()" :disabled="isLoading">
                            Annulla
                        </button>
                        <button type="button" class="btn btn-primary" @click="submitForm()" :disabled="isLoading" x-show="!isLoading">
                            <i data-lucide="save"></i> Salva
                        </button>
                        <span x-show="isLoading" class="loading-spinner"><i data-lucide="loader"></i> Salvataggio...</span>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
</div>

<?php get_footer(); ?>


