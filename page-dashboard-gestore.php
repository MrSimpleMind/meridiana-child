<?php
/**
 * Template: Dashboard Gestore
 * Pagina per la gestione contenuti (Documentazione, Comunicazioni, Convenzioni, Salute, Utenti)
 */

if (!current_user_can('manage_platform') && !current_user_can('manage_options')) {
    wp_redirect(home_url());
    exit;
}

get_header();
?>

<div class="content-wrapper">
<div class="gestore-dashboard" x-data="gestoreDashboard()">
    <div class="dashboard-header">
        <div class="container">
            <h1 class="dashboard-header__title">Dashboard Gestione Contenuti</h1>
            <p class="dashboard-header__subtitle">Gestisci documentazione, comunicazioni, utenti e contenuti della piattaforma</p>
        </div>
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
    
    <div class="dashboard-modal" x-show="modalOpen" x-cloak @keydown.escape="closeModal()">
        <div class="dashboard-modal__overlay" @click="closeModal()"></div>
        <div class="dashboard-modal__body">
            <div class="dashboard-modal__header">
                <h2 x-text="getModalTitle()"></h2>
                <button type="button" class="dashboard-modal__close" @click="closeModal()">
                    <i data-lucide="x"></i>
                </button>
            </div>
            <div class="dashboard-modal__content"><!-- Form ACF via AJAX --></div>
        </div>
    </div>
</div>
</div>

<?php get_footer(); ?>
