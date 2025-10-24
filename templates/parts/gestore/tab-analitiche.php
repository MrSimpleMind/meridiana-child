<?php
/**
 * Template: Tab Analitiche Dashboard Gestore
 * 
 * Sezioni:
 * - KPI Cards (statistiche utenti + contenuti)
 * - Grafico Distribuzione CPT
 * - Ricerca Utenti (stub con Alpine.js)
 * - Ricerca Protocolli (stub)
 */

if (!defined('ABSPATH')) exit;

// Recupera statistiche con cache
$stats_utenti = meridiana_get_cached_stat('utenti', 'meridiana_get_stats_utenti');
$stats_contenuti = meridiana_get_cached_stat('contenuti', 'meridiana_get_stats_contenuti');
$protocolli_ats = meridiana_get_cached_stat('protocolli_ats', 'meridiana_get_stats_protocolli_ats');
$total_protocolli = $stats_contenuti['protocollo']['count'] ?? 0;
?>

<div class="analytics-container" x-data="analyticsTab()" x-cloak>
    
    <!-- SEZIONE KPI CARDS -->
    <section class="analytics-section analytics-section--kpi">
        <h3 class="analytics-section__title">Statistiche Piattaforma</h3>
        
        <div class="analytics-kpi-grid">
            
            <!-- CARD: Utenti Attivi -->
            <div class="analytics-kpi-card analytics-kpi-card--primary">
                <div class="analytics-kpi-card__icon">
                    <i data-lucide="users"></i>
                </div>
                <div class="analytics-kpi-card__content">
                    <div class="analytics-kpi-card__number"><?php echo intval($stats_utenti['attivi']); ?></div>
                    <div class="analytics-kpi-card__label">Utenti Attivi</div>
                </div>
            </div>
            
            <!-- CARD: Utenti Sospesi -->
            <div class="analytics-kpi-card analytics-kpi-card--warning">
                <div class="analytics-kpi-card__icon">
                    <i data-lucide="pause-circle"></i>
                </div>
                <div class="analytics-kpi-card__content">
                    <div class="analytics-kpi-card__number"><?php echo intval($stats_utenti['sospesi']); ?></div>
                    <div class="analytics-kpi-card__label">Utenti Sospesi</div>
                </div>
            </div>
            
            <!-- CARD: Utenti Licenziati -->
            <div class="analytics-kpi-card analytics-kpi-card--error">
                <div class="analytics-kpi-card__icon">
                    <i data-lucide="x-circle"></i>
                </div>
                <div class="analytics-kpi-card__content">
                    <div class="analytics-kpi-card__number"><?php echo intval($stats_utenti['licenziati']); ?></div>
                    <div class="analytics-kpi-card__label">Utenti Licenziati</div>
                </div>
            </div>
            
        </div>
    </section>
    
    <!-- SEZIONE CONTENUTI -->
    <section class="analytics-section analytics-section--content">
        <h3 class="analytics-section__title">Contenuti della Piattaforma</h3>
        
        <div class="analytics-content-grid">
            
            <?php foreach ($stats_contenuti as $cpt => $data) : ?>
            <div class="analytics-content-card">
                <div class="analytics-content-card__number"><?php echo intval($data['count']); ?></div>
                <div class="analytics-content-card__label"><?php echo esc_html($data['label']); ?></div>
            </div>
            <?php endforeach; ?>
            
        </div>
        
        <!-- SOTTOSEZIONE: Protocolli ATS -->
        <div class="analytics-subsection">
            <div class="analytics-subsection__title">Protocolli con Pianificazione ATS</div>
            <div class="analytics-subsection__stat">
                <span class="analytics-stat__number"><?php echo intval($protocolli_ats); ?></span>
                <span class="analytics-stat__total">di <?php echo intval($total_protocolli); ?> totali</span>
            </div>
        </div>
        
    </section>
    
    <!-- SEZIONE GRAFICO DISTRIBUZIONE -->
    <section class="analytics-section analytics-section--chart">
        <h3 class="analytics-section__title">Distribuzione Contenuti</h3>
        
        <div class="analytics-chart-wrapper">
            <canvas id="analytics-chart" width="400" height="300"></canvas>
        </div>
        
        <div class="analytics-chart__info">
            <p>Visualizzazione della distribuzione dei contenuti per tipo sulla piattaforma.</p>
        </div>
    </section>
    
    <!-- SEZIONE RICERCA UTENTI -->
    <section class="analytics-section analytics-section--search">
        <h3 class="analytics-section__title">Ricerca Utenti</h3>
        
        <div class="analytics-search-box">
            <input 
                type="text" 
                class="analytics-search-box__input"
                x-model="userSearchQuery"
                @input="searchUsers()"
                placeholder="Digita nome utente..."
            />
            <i data-lucide="search" class="analytics-search-box__icon"></i>
        </div>
        
        <!-- Dropdown Risultati Ricerca -->
        <div class="analytics-search-results" x-show="userSearchResults.length > 0">
            <template x-for="user in userSearchResults" :key="user.ID">
                <button 
                    type="button"
                    class="analytics-search-result"
                    @click="selectUser(user)"
                >
                    <span class="analytics-search-result__name" x-text="user.display_name"></span>
                    <span class="analytics-search-result__meta" x-text="user.udo || 'N/A'"></span>
                </button>
            </template>
        </div>
        
        <!-- Messaggio Nessun Risultato -->
        <div class="analytics-search-empty" x-show="userSearchQuery && userSearchResults.length === 0">
            <p>Nessun utente trovato.</p>
        </div>
        
    </section>
    
    <!-- SEZIONE UTENTE SELEZIONATO -->
    <section class="analytics-section analytics-section--user-detail" x-show="selectedUser">
        <div class="analytics-user-header">
            <div class="analytics-user-header__info">
                <h3 x-text="selectedUser.display_name"></h3>
                <p x-text="'UDO: ' + (selectedUser.udo || 'N/A')"></p>
            </div>
            <button 
                type="button" 
                class="analytics-user-header__close"
                @click="selectedUser = null"
            >
                <i data-lucide="x"></i>
            </button>
        </div>
        
        <!-- Toggle Protocolli -->
        <div class="analytics-user-tabs">
            <button 
                type="button" 
                class="analytics-user-tab"
                :class="{ 'active': userDetailTab === 'viewed' }"
                @click="userDetailTab = 'viewed'"
            >
                <i data-lucide="check-circle"></i>
                <span>Protocolli Visualizzati</span>
            </button>
            <button 
                type="button" 
                class="analytics-user-tab"
                :class="{ 'active': userDetailTab === 'not-viewed' }"
                @click="userDetailTab = 'not-viewed'"
            >
                <i data-lucide="circle"></i>
                <span>Da Visualizzare</span>
            </button>
        </div>
        
        <!-- Contenuto Tab -->
        <div class="analytics-user-content">
            <div x-show="userDetailTab === 'viewed'" class="analytics-document-list">
                <!-- Placeholder - verrà riempito con fetch -->
                <p>Caricamento...</p>
            </div>
            <div x-show="userDetailTab === 'not-viewed'" class="analytics-document-list">
                <!-- Placeholder - verrà riempito con fetch -->
                <p>Caricamento...</p>
            </div>
        </div>
        
    </section>
    
    <!-- SEZIONE RICERCA PROTOCOLLI (STUB) -->
    <section class="analytics-section analytics-section--protocol-search">
        <h3 class="analytics-section__title">Ricerca Protocolli</h3>
        
        <div class="analytics-search-box">
            <input 
                type="text" 
                class="analytics-search-box__input"
                placeholder="Digita nome protocollo..."
            />
            <i data-lucide="search" class="analytics-search-box__icon"></i>
        </div>
        
        <div class="analytics-protocol-message">
            <p>Sezione in sviluppo. Potrai cercare protocolli e visualizzare le statistiche di visualizzazione.</p>
        </div>
        
    </section>
    
</div>

<?php
/**
 * Script Chart.js - Grafico Distribuzione
 * 
 * Caricato tramite enqueue in gestore-enqueue.php
 * Dati passati via wp_localize_script
 */
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Verifica che Chart.js sia disponibile
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js non caricato. Verificare enqueue.');
        return;
    }
    
    // Dati grafico (viene da PHP via wp_localize_script)
    const chartData = window.meridiana?.analyticsChartData || [];
    
    if (chartData.length === 0) {
        return;
    }
    
    const ctx = document.getElementById('analytics-chart');
    if (!ctx) return;
    
    // Estrai labels e values
    const labels = chartData.map(item => item.label);
    const values = chartData.map(item => item.count);
    
    // Colori brand (coerenti con design system)
    const colors = [
        '#ab1120',  // Rosso brand
        '#10B981',  // Verde
        '#F59E0B',  // Giallo
        '#06B6D4',  // Cyan
        '#8B5CF6',  // Viola
    ];
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: colors.slice(0, labels.length),
                borderColor: '#FFFFFF',
                borderWidth: 2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 14,
                            family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                        },
                        color: '#1F2937',
                        padding: 20,
                    },
                },
                tooltip: {
                    backgroundColor: 'rgba(31, 41, 55, 0.9)',
                    titleFont: { size: 14 },
                    bodyFont: { size: 13 },
                    padding: 12,
                    borderRadius: 6,
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed + ' contenuti';
                        }
                    }
                },
            },
        },
    });
});
</script>
