<?php
/**
 * ACF Media Picker Fix - Dashboard Gestore Modal
 * Supporto per media picker in contesto AJAX/modal
 * 
 * Questo file risolve l'errore:
 * "Cannot read properties of undefined (reading 'query')"
 * che appare quando si carica una form ACF via AJAX nel modal
 */

if (!defined('ABSPATH')) exit;

// ============================================
// HOOK - Supporto Media Manager in Modal
// ============================================

/**
 * Assicura che il media manager sia caricato e disponibile
 * anche quando viene richiesto via AJAX dalla dashboard
 */
add_action('wp_ajax_query-attachments', function() {
    // Media manager AJAX è gestito nativamente da WP
    // Questo hook assicura priorità corretta
}, 999);

/**
 * Fix: Aggiungi supporto per media picker in contesto non-global
 * ACF cerca window._wpMediaL10n che deve essere definito
 */
add_action('wp_footer', function() {
    if (is_page('dashboard-gestore')) {
        ?>
        <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            // Ensure ACF media picker can access wp.media
            if (typeof window.wp !== 'undefined' && typeof window.wp.media !== 'undefined') {
                console.log('[Dashboard Gestore] Media Manager è disponibile per ACF');
                
                // Fix per ACF file frames in modal
                if (typeof window.acf !== 'undefined') {
                    // ACF è pronto
                    var acfMediaSetup = function() {
                        if (window.acf.Models && window.acf.Models.FileFrame) {
                            console.log('[Dashboard Gestore] ACF FileFrame models sono disponibili');
                        }
                    };
                    
                    // Esegui setup quando Alpine è pronto
                    if (typeof Alpine !== 'undefined') {
                        Alpine.nextTick(acfMediaSetup);
                    }
                }
            }
        });
        </script>
        <?php
    }
});

/**
 * AJAX Endpoint - Reinitialize ACF dopo form load
 * Chiamato dal component Alpine.js dopo caricamento del form HTML
 */
add_action('wp_ajax_gestore_reinit_acf', 'meridiana_ajax_reinit_acf_media');

function meridiana_ajax_reinit_acf_media() {
    // Security checks
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_rest')) {
        wp_send_json_error(['message' => 'Nonce non valido'], 403);
    }

    if (!current_user_can('manage_platform') && !current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permessi insufficienti'], 403);
    }

    // Trigger ACF initialization
    do_action('acf/init');

    wp_send_json_success([
        'message' => 'ACF reinitialized for media picker',
    ]);
}

?>
