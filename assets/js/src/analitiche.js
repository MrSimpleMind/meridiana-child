document.addEventListener('DOMContentLoaded', function() {
    const analiticheDashboard = document.querySelector('.analitiche-dashboard');
    if (!analiticheDashboard) return;

    const ajaxUrl = analiticheDashboard.dataset.ajaxUrl;
    const nonce = analiticheDashboard.dataset.nonce;
    const globalStatsCards = document.getElementById('globalStatsCards');
    const contentDistributionChartCanvas = document.getElementById('contentDistributionChart');

    // Funzione per caricare le statistiche globali
    function fetchGlobalStats() {
        if (!globalStatsCards) return;

        // Rimuovi le card di caricamento esistenti
        globalStatsCards.innerHTML = ''; 

        fetch(ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'meridiana_analytics_get_global_stats',
                nonce: nonce,
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const stats = data.data;
                
                // Popola le card con i dati reali
                globalStatsCards.innerHTML = `
                    <div class="stat-card">
                        <span class="stat-card__value">${stats.total_users}</span>
                        <span class="stat-card__label">Utenti Totali</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-card__value">${stats.total_protocols}</span>
                        <span class="stat-card__label">Protocolli Pubblicati</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-card__value">${stats.total_modules}</span>
                        <span class="stat-card__label">Moduli Pubblicati</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-card__value">${stats.total_convenzioni}</span>
                        <span class="stat-card__label">Convenzioni Pubblicate</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-card__value">${stats.total_salute_benessere}</span>
                        <span class="stat-card__label">Articoli Salute Pubblicati</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-card__value">${stats.total_comunicazioni}</span>
                        <span class="stat-card__label">Comunicazioni Pubblicate</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-card__value">${stats.total_ats_protocols}</span>
                        <span class="stat-card__label">Protocolli ATS</span>
                    </div>
                `;
            } else {
                globalStatsCards.innerHTML = `<div class="notification notification-error">Errore nel caricamento delle statistiche.</div>`;
                console.error('Error fetching global stats:', data.data);
            }
        })
        .catch(error => {
            globalStatsCards.innerHTML = `<div class="notification notification-error">Errore di rete nel caricamento delle statistiche.</div>`;
            console.error('Network error fetching global stats:', error);
        });
    }

    // Funzione per renderizzare il grafico di distribuzione dei contenuti
    function renderContentDistributionChart() {
        if (!contentDistributionChartCanvas) return;

        // Assumiamo che Chart.js sia disponibile globalmente o caricato in precedenza
        // Se non lo è, dovremmo enqueuarlo o importarlo.
        if (typeof Chart === 'undefined') {
            console.warn('Chart.js non è disponibile. Impossibile renderizzare il grafico.');
            return;
        }

        // Dati di esempio per il grafico (dovrebbero venire da AJAX in futuro)
        const chartData = {
            labels: ['Protocolli', 'Moduli', 'Convenzioni', 'Salute', 'Comunicazioni'],
            datasets: [{
                label: 'Numero di Contenuti',
                data: [12, 19, 3, 5, 2],
                backgroundColor: [
                    'rgba(6, 182, 212, 0.8)', // info color
                    'rgba(16, 185, 129, 0.8)', // success color
                    'rgba(255, 99, 132, 0.8)', // custom
                    'rgba(255, 159, 64, 0.8)', // custom
                    'rgba(54, 162, 235, 0.8)'  // custom
                ],
                borderColor: [
                    'rgba(6, 182, 212, 1)',
                    'rgba(16, 185, 129, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(54, 162, 235, 1)'
                ],
                borderWidth: 1
            }]
        };

        new Chart(contentDistributionChartCanvas, {
            type: 'bar',
            data: chartData,
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
    }

    // Esegui le funzioni al caricamento della pagina
    fetchGlobalStats();
    renderContentDistributionChart();
});
