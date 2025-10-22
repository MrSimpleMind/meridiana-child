<?php
/**
 * Taxonomy Helpers
 *
 * La registrazione delle tassonomie (Unita di Offerta, Profilo Professionale,
 * Aree di Competenza, Tipologia Corso, ecc.) e ora gestita esclusivamente
 * tramite ACF Pro e i relativi file JSON in acf-json/.
 *
 * Questo file rimane per fornire funzioni di supporto riutilizzabili nei
 * template e negli handler PHP del tema child.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ottieni i termini di una taxonomy in formato chiave => valore.
 *
 * @param string $taxonomy Nome della taxonomy
 * @return array
 */
function get_taxonomy_options($taxonomy) {
    $terms = get_terms(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
    ));

    $options = array();

    if (!is_wp_error($terms) && !empty($terms)) {
        foreach ($terms as $term) {
            $options[$term->term_id] = $term->name;
        }
    }

    return $options;
}

/**
 * Genera l'HTML di un campo select popolato con i termini di una taxonomy.
 *
 * @param string $taxonomy Nome della taxonomy
 * @param string $name      Nome dell'input select
 * @param mixed  $selected  Valore selezionato
 * @param array  $args      Argomenti addizionali (class, id, placeholder)
 * @return string
 */
function get_taxonomy_select($taxonomy, $name, $selected = '', $args = array()) {
    $defaults = array(
        'class' => 'select-field',
        'id' => $name,
        'show_option_none' => '-- Seleziona --',
    );

    $args = wp_parse_args($args, $defaults);

    $terms = get_terms(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
    ));

    $output = sprintf(
        '<select name="%s" id="%s" class="%s">',
        esc_attr($name),
        esc_attr($args['id']),
        esc_attr($args['class'])
    );

    if (!empty($args['show_option_none'])) {
        $output .= sprintf(
            '<option value="">%s</option>',
            esc_html($args['show_option_none'])
        );
    }

    if (!is_wp_error($terms) && !empty($terms)) {
        foreach ($terms as $term) {
            $output .= sprintf(
                '<option value="%s"%s>%s</option>',
                esc_attr($term->term_id),
                selected($selected, $term->term_id, false),
                esc_html($term->name)
            );
        }
    }

    $output .= '</select>';

    return $output;
}
