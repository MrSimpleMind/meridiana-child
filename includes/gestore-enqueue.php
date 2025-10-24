<?php
/**
 * Enqueue: Gestore Dashboard JavaScript & Analytics
 * 
 * IMPORTANTE: gestore-dashboard.js DEVE caricare PRIMA di Alpine.js
 * altrimenti Alpine non troverà il component gestoreDashboard() registrato
 */

function meridiana_enqueue_gestore_dashboard() {
    if (is_page('dashboard-gestore') && is_user_logged_in() && (current_user_can('manage_platform') || current_user_can('manage_options'))) {
        // Carica gestore-dashboard.js PRIMA, in head, senza dipendenze da Alpine
        wp_enqueue_script(
            'meridiana-gestore-dashboard',
            MERIDIANA_CHILD_URI . '/assets/js/src/gestore-dashboard.js',
            array(), // NO dipendenze
            MERIDIANA_CHILD_VERSION,
            false // in head
        );
        
        // Carica Chart.js via CDN per grafici
        wp_enqueue_script(
            'chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js',
            array(),
            '3.9.1',
            false
        );
        
        // Carica analytics-gestore.js (dipende da Alpine)
        wp_enqueue_script(
            'meridiana-analytics-gestore',
            MERIDIANA_CHILD_URI . '/assets/js/src/analytics-gestore.js',
            array('alpinejs', 'chartjs'), // Carica DOPO Alpine e Chart.js
            MERIDIANA_CHILD_VERSION,
            true // in footer
        );
        
        // Prepara dati per il grafico (collocazione dati PHP)
        $chart_data = meridiana_prepare_chart_data();
        
        // Passa dati al JavaScript
        wp_localize_script('meridiana-analytics-gestore', 'meridiana', array_merge(
            isset($GLOBALS['meridiana']) ? $GLOBALS['meridiana'] : array(),
            array(
                'analyticsChartData' => $chart_data,
            )
        ));
    }
}
// Priorità alta (20) per caricare PRIMA della priorità standard (10)
add_action('wp_enqueue_scripts', 'meridiana_enqueue_gestore_dashboard', 20);

/**
 * Prepara dati per Chart.js Grafico
 */
function meridiana_prepare_chart_data() {
    $stats_contenuti = meridiana_get_cached_stat('contenuti', 'meridiana_get_stats_contenuti');
    
    $chart_data = array();
    
    foreach ($stats_contenuti as $cpt => $data) {
        $chart_data[] = array(
            'label' => $data['label'],
            'count' => $data['count'],
        );
    }
    
    return $chart_data;
}
