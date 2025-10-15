<?php
/**
 * Footer Template
 * Child Theme - Cooperativa La Meridiana
 */
?>

        <?php 
        // Bottom Navigation Mobile
        get_template_part('templates/parts/navigation/bottom-nav'); 
        ?>

        <?php wp_footer(); ?>
        
        <!-- Initialize Lucide Icons -->
        <script src="https://unpkg.com/lucide@latest"></script>
        <script>
            lucide.createIcons();
        </script>
    </body>
</html>
