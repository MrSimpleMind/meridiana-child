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
            lucide.createIcons();
        </script>
    </body>
</html>
