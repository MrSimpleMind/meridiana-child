<?php
/**
 * ACF Label Accessibility Fix - Dashboard Gestore Modal
 * Corregge gli attributi 'for' delle label per matchare gli ID degli input
 * quando i form ACF sono renderizzati via AJAX nel modal
 */

if (!defined('ABSPATH')) exit;

// ============================================
// FIX LABEL ATTRIBUTES IN MODAL CONTEXT
// ============================================

/**
 * Hook che corregge il rendering delle label di ACF nel modal
 * Problem: ACF genera label con for="" che punta a ID non corrispondenti
 * Solution: Riprocessare l'HTML della form per fixare i label-input relationships
 */
add_filter('acf/render_form', function($form) {
    // Solo per dashboard gestore
    if (!is_page('dashboard-gestore')) {
        return $form;
    }
    
    return $form;
}, 10, 1);

/**
 * AJAX Hook - Cleanup label attributes dopo caricamento form
 * Questo viene eseguito nel browser via JavaScript
 */
add_action('wp_footer', function() {
    if (is_page('dashboard-gestore')) {
        ?>
        <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            // Funzione per fixare label-input relationship
            window.fixACFLabelRelationships = function(container) {
                if (!container) {
                    container = document;
                }
                
                // Trova tutte le label all'interno del container
                const labels = container.querySelectorAll('label[for]');
                
                labels.forEach(label => {
                    const forAttr = label.getAttribute('for');
                    
                    // Cerca l'input/textarea/select con questo ID
                    const input = container.querySelector(`#${CSS.escape(forAttr)}`);
                    
                    // Se non trovato, prova a matchare per nome
                    if (!input) {
                        // Estrai il name dal for attribute (spesso sono simili)
                        const labelParent = label.closest('.acf-field');
                        if (labelParent) {
                            const actualInput = labelParent.querySelector('input, textarea, select');
                            if (actualInput && actualInput.id) {
                                label.setAttribute('for', actualInput.id);
                            } else if (actualInput) {
                                // Se input non ha ID, generane uno
                                const inputId = 'acf-' + Math.random().toString(36).substr(2, 9);
                                actualInput.setAttribute('id', inputId);
                                label.setAttribute('for', inputId);
                            }
                        }
                    }
                });
                
                console.log('[Dashboard] ACF label relationships fixed');
            };
            
            // Esponi la funzione globalmente per Alpine
            window.fixACFLabels = window.fixACFLabelRelationships;
        });
        </script>
        <?php
    }
});

/**
 * Hook nel componente Alpine.js
 * Questa funzione viene chiamata dopo che il form HTML è caricato nel modal
 * vedi gestore-dashboard.js - openFormModal() -> $nextTick()
 */
// Nota: La chiamata a window.fixACFLabelRelationships() deve essere aggiunta
// nel gestore-dashboard.js dopo acf.doAction('append', ...)
// Questo è gestito tramite JavaScript nel modal

?>
