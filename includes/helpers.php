<?php
/**
 * Helper Functions
 * 
 * Funzioni di utilità riutilizzabili in tutta la piattaforma
 */

if (!defined('ABSPATH')) {
    exit;
}

// ... [contenuto precedente rimane identico] ...

/**
 * PROMPT 4: Mostra immagine in evidenza nel contenuto single
 * Hook in blocksy:single:content:top per iniettare l'immagine
 * 
 * @return void
 */
function meridiana_display_featured_image() {
    // Solo su single post/custom post types
    if (!is_singular(array('post', 'convenzione', 'salute_benessere', 'protocollo', 'modulo'))) {
        return;
    }
    
    // Recupera ID immagine in evidenza
    $image_id = get_post_thumbnail_id();
    
    // Se non c'è immagine, esci silenziosamente
    if (!$image_id) {
        return;
    }
    
    // Mostra immagine in formato 'large' per web performance
    $image_html = wp_get_attachment_image(
        $image_id,
        'large',
        false,
        array(
            'class' => 'meridiana-featured-image',
            'alt' => get_the_title(),
            'loading' => 'eager'
        )
    );
    
    // Wrapper styling con design system
    echo '<div class="meridiana-featured-image-wrapper">';
    echo $image_html;
    echo '</div>';
}
add_action('blocksy:single:content:top', 'meridiana_display_featured_image', 1);

// ... [resto del file] ...
