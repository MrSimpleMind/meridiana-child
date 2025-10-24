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

/**
 * Renderizza un badge standardizzato per tipo di post o tassonomia.
 *
 * @param string $type Il tipo di badge (es. 'protocollo', 'modulo', 'category').
 * @param string $text Il testo da visualizzare nel badge.
 * @return string Il markup HTML del badge.
 */
function meridiana_get_badge($type, $text) {
    $class_map = [
        'protocollo' => 'badge-protocollo',
        'modulo'     => 'badge-modulo',
        'ats'        => 'badge-ats',
        'category'   => 'badge-category',
    ];

    $badge_class = isset($class_map[$type]) ? $class_map[$type] : 'badge-secondary';

    return sprintf(
        '<span class="badge %s">%s</span>',
        esc_attr($badge_class),
        esc_html($text)
    );
}

/**
 * Renderizza un badge standardizzato per lo stato di un post.
 *
 * @param string|WP_Post $post Post object o ID.
 * @return string Il markup HTML del badge di stato.
 */
function meridiana_get_status_badge($post) {
    $status = get_post_status($post);
    
    $status_map = [
        'publish' => ['text' => 'Pubblicato', 'class' => 'badge-success'],
        'draft'   => ['text' => 'Bozza', 'class' => 'badge-warning'],
        'pending' => ['text' => 'In attesa', 'class' => 'badge-warning'],
        'private' => ['text' => 'Privato', 'class' => 'badge-secondary'],
        'future'  => ['text' => 'Pianificato', 'class' => 'badge-info'],
    ];

    if (isset($status_map[$status])) {
        $badge_data = $status_map[$status];
        return sprintf(
            '<span class="badge %s">%s</span>',
            esc_attr($badge_data['class']),
            esc_html($badge_data['text'])
        );
    }

    return '';
}
