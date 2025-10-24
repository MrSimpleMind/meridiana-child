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
        <div class="container">
            <h1 class="page-title">Analitiche</h1>
            <div class="analitiche-dashboard" 
                 data-ajax-url="<?php echo esc_attr(admin_url('admin-ajax.php')); ?>"
                 data-nonce="<?php echo esc_attr(wp_create_nonce('wp_rest')); ?>">
                <div class="analitiche-section">
                    <h2 class="analitiche-section__title">Statistiche Globali</h2>
                    <div class="stats-cards-grid" id="globalStatsCards">
                        <!-- Le card statistiche verranno popolate qui via JavaScript -->
                        <div class="stat-card loading">
                            <span class="stat-card__value">...</span>
                            <span class="stat-card__label">Caricamento Utenti</span>
                        </div>
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
                            <span class="stat-card__label">Caricamento Salute & Benessere</span>
                        </div>
                        <div class="stat-card loading">
                            <span class="stat-card__value">...</span>
                            <span class="stat-card__label">Caricamento Comunicazioni</span>
                        </div>
                    </div>
                </div>

                <div class="analitiche-section">
                    <h2 class="analitiche-section__title">Distribuzione Contenuti</h2>
                    <div class="chart-container">
                        <canvas id="contentDistributionChart"></canvas>
                    </div>
                </div>

                <div class="analitiche-section">
                    <h2 class="analitiche-section__title">Analitiche Dettagliate</h2>
                    <p>Questa sezione verrà implementata successivamente.</p>
                </div>
            </div>
        </div>
    </main>
</div>

<?php
get_footer();
