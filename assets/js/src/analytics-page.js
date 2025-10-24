document.addEventListener('DOMContentLoaded', function() {
    const analyticsDashboard = document.querySelector('.analitiche-dashboard');

    // Only proceed if we are on the analytics page and the dashboard container exists
    if (!analyticsDashboard) {
        return; 
    }

    const ajaxUrl = analyticsDashboard.dataset.ajaxUrl;
    const nonce = analyticsDashboard.dataset.nonce;

    const globalStatsCardsContainer = document.getElementById('globalStatsCards');
    const contentDistributionChartCanvas = document.getElementById('contentDistributionChart');

    // Function to fetch global stats
    async function fetchGlobalStats() {
        try {
            const response = await fetch(ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'meridiana_analytics_get_global_stats',
                    nonce: nonce,
                }),
            });
            const data = await response.json();

            if (data.success) {
                updateGlobalStatsCards(data.data);
                renderContentDistributionChart(data.data);
            } else {
                console.error('Error fetching global stats:', data.data);
                displayError('Impossibile caricare le statistiche globali.');
            }
        } catch (error) {
            console.error('Network error fetching global stats:', error);
            displayError('Errore di rete durante il caricamento delle statistiche.');
        }
    }

    // Function to update global stats cards
    function updateGlobalStatsCards(stats) {
        globalStatsCardsContainer.innerHTML = `
            <div class="stat-card">
                <span class="stat-card__value">${stats.total_users}</span>
                <span class="stat-card__label">Utenti Totali</span>
            </div>
            <div class="stat-card">
                <span class="stat-card__value">${stats.active_users}</span>
                <span class="stat-card__label">Utenti Attivi</span>
            </div>
            <div class="stat-card">
                <span class="stat-card__value">${stats.suspended_users}</span>
                <span class="stat-card__label">Utenti Sospesi</span>
            </div>
            <div class="stat-card">
                <span class="stat-card__value">${stats.fired_users}</span>
                <span class="stat-card__label">Utenti Licenziati</span>
            </div>
            <div class="stat-card">
                <span class="stat-card__value">${stats.total_protocols}</span>
                <span class="stat-card__label">Protocolli Totali</span>
            </div>
            <div class="stat-card">
                <span class="stat-card__value">${stats.total_ats_protocols}</span>
                <span class="stat-card__label">Protocolli ATS</span>
            </div>
            <div class="stat-card">
                <span class="stat-card__value">${stats.total_modules}</span>
                <span class="stat-card__label">Moduli Totali</span>
            </div>
            <div class="stat-card">
                <span class="stat-card__value">${stats.total_convenzioni}</span>
                <span class="stat-card__label">Convenzioni Totali</span>
            </div>
            <div class="stat-card">
                <span class="stat-card__value">${stats.total_salute_benessere}</span>
                <span class="stat-card__label">Salute & Benessere</span>
            </div>
            <div class="stat-card">
                <span class="stat-card__value">${stats.total_comunicazioni}</span>
                <span class="stat-card__label">Comunicazioni Totali</span>
            </div>
        `;
    }

    // Function to render content distribution chart
    function renderContentDistributionChart(stats) {
        if (!contentDistributionChartCanvas) return;

        const ctx = contentDistributionChartCanvas.getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: [
                    'Protocolli', 'Moduli', 'Convenzioni',
                    'Salute & Benessere', 'Comunicazioni'
                ],
                datasets: [{
                    data: [
                        stats.total_protocols,
                        stats.total_modules,
                        stats.total_convenzioni,
                        stats.total_salute_benessere,
                        stats.total_comunicazioni
                    ],
                    backgroundColor: [
                        '#FF6384', // Red
                        '#36A2EB', // Blue
                        '#FFCE56', // Yellow
                        '#4BC0C0', // Green
                        '#9966FF'  // Purple
                    ],
                    hoverBackgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    title: {
                        display: false,
                        text: 'Distribuzione Contenuti'
                    }
                }
            }
        });
    }

    // Helper to display errors (can be improved with a proper notification system)
    function displayError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'notification notification-error';
        errorDiv.innerHTML = `<i data-lucide="alert-circle"></i><span>${message}</span>`;
        globalStatsCardsContainer.before(errorDiv); // Insert before the stats cards
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    // Initialize Lucide icons if available
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Fetch stats on page load
    fetchGlobalStats();
});
