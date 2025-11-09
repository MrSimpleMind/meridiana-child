<?php
/**
 * Footer Template
 * Child Theme - Cooperativa La Meridiana
 */
?>

        <?php 
        // User Profile Modal (solo per utenti loggati)
        if (is_user_logged_in()) {
            get_template_part('templates/parts/user-profile-modal');
        }
        
        // Sidebar Navigation Desktop (sempre visibile su desktop)
        get_template_part('templates/parts/navigation/sidebar-nav'); 
        
        // Bottom Navigation Mobile
        get_template_part('templates/parts/navigation/bottom-nav'); 
        ?>

        <?php wp_footer(); ?>
        
        <!-- Initialize Lucide Icons -->
        <script src="https://unpkg.com/lucide@0.288.0"></script>
        <script>
            // Inizializzazione iniziale
            lucide.createIcons();

            // Re-inizializza le icone quando il DOM cambia (es. Alpine.js aggiorna attributi)
            // Usa un MutationObserver per intercettare i cambi di attributi data-lucide
            const observer = new MutationObserver((mutations) => {
                let needsUpdate = false;
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'data-lucide') {
                        needsUpdate = true;
                    }
                });
                if (needsUpdate) {
                    lucide.createIcons();
                }
            });

            // Osserva tutto il body per cambi di attributi
            observer.observe(document.body, {
                attributes: true,
                attributeFilter: ['data-lucide'],
                subtree: true
            });
        </script>
    </body>
</html>
