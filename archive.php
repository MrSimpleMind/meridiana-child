<?php
/**
 * Template Archivio Comunicazioni
 * 
 * Mostra tutte le comunicazioni con filtro per categoria AJAX
 * 
 * PROMPT 6: Filtro Comunicazioni con AJAX Dinamico
 */

get_header();

// Recupera comunicazioni per la query iniziale
$args = array(
    'post_type' => 'post',
    'posts_per_page' => 10,
    'orderby' => 'date',
    'order' => 'DESC',
);
$comunicazioni = new WP_Query($args);
?>

<main class="site-main">
    <div class="container">
        
        <!-- Intestazione -->
        <header class="page-header">
            <h1><?php single_post_title(); ?></h1>
            <p class="page-subtitle">Tutte le comunicazioni aziendali</p>
        </header>
        
        <!-- Breadcrumb (PROMPT 5) -->
        <?php meridiana_breadcrumb(); ?>
        
        <!-- Filtro per Categoria (PROMPT 6) -->
        <?php meridiana_comunicazioni_filter(array(
            'placeholder' => 'Tutte le categorie',
            'class' => 'mb-6',
        )); ?>
        
        <!-- Lista Comunicazioni (iniziale) -->
        <div id="comunicazioni-container">
            <?php meridiana_comunicazioni_list($comunicazioni); ?>
            
            <!-- Paginazione -->
            <?php if ($comunicazioni->max_num_pages > 1): ?>
                <?php meridiana_comunicazioni_pagination($comunicazioni); ?>
            <?php endif; ?>
        </div>
        
    </div>
</main>

<?php get_footer();
