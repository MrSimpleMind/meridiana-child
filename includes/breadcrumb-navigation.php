<?php
/**
 * PROMPT 5: Breadcrumb & Back Navigation Intelligente
 * 
 * Genera URL intelligenti per i pulsanti "Torna indietro"
 * Segue la gerarchia: Single Post → Archive → Home
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ottieni URL genitore basato sulla pagina corrente
 * 
 * Determina a quale livello gerarchico tornare:
 * - Single Post → Archive dello stesso CPT
 * - Archive → Home
 * - Home → Home (rimane)
 * 
 * @return string URL pagina genitore
 */
function meridiana_get_parent_url() {
    // Se è single post di un CPT custom
    if (is_singular(array('convenzione', 'salute_benessere', 'protocollo', 'modulo', 'organigramma'))) {
        $post_type = get_post_type();
        
        // Mappa CPT → Archive URL
        $archive_urls = array(
            'convenzione' => home_url('/convenzioni/'),
            'salute_benessere' => home_url('/salute-benessere/'),
            'protocollo' => home_url('/protocolli/'),
            'modulo' => home_url('/moduli/'),
            'organigramma' => home_url('/organigramma/'),
        );
        
        return isset($archive_urls[$post_type]) ? $archive_urls[$post_type] : home_url();
    }
    
    // Se è archive/elenco di un CPT
    if (is_post_type_archive() || is_archive()) {
        return home_url();
    }
    
    // Se è una pagina custom (documentazione, corsi, etc)
    if (is_page()) {
        $page_id = get_the_ID();
        $page_slug = get_post_field('post_name', $page_id);
        
        // Mappa pagine custom → URL genitore
        $parent_urls = array(
            'documentazione' => home_url(),
            'corsi' => home_url(),
            'organigramma' => home_url(),
            'contatti' => home_url(),
            'analytics' => home_url(),
        );
        
        return isset($parent_urls[$page_slug]) ? $parent_urls[$page_slug] : home_url();
    }
    
    // Default: Home
    return home_url();
}

/**
 * Genera label intelligente per pulsante "Torna indietro"
 * 
 * @return string Etichetta del pulsante (es: "Torna a Convenzioni")
 */
function meridiana_get_back_label() {
    if (is_singular(array('convenzione', 'salute_benessere', 'protocollo', 'modulo', 'organigramma'))) {
        $post_type = get_post_type();
        
        // Mappa CPT → Etichetta
        $labels = array(
            'convenzione' => 'Torna a Convenzioni',
            'salute_benessere' => 'Torna a Salute e Benessere',
            'protocollo' => 'Torna a Protocolli',
            'modulo' => 'Torna a Moduli',
            'organigramma' => 'Torna a Organigramma',
        );
        
        return isset($labels[$post_type]) ? $labels[$post_type] : 'Torna indietro';
    }
    
    if (is_post_type_archive() || is_archive()) {
        return 'Torna alla Home';
    }
    
    if (is_page()) {
        return 'Torna alla Home';
    }
    
    return 'Torna indietro';
}

/**
 * Renderizza pulsante "Torna indietro" intelligente
 * 
 * @param array $args Array di opzioni:
 *   - class: Classe CSS aggiuntive (default: '')
 *   - icon: Nome icona Lucide (default: 'arrow-left')
 *   - label: Etichetta custom (default: generata automaticamente)
 * 
 * @return string HTML pulsante
 */
function meridiana_render_back_button($args = array()) {
    $defaults = array(
        'class' => '',
        'icon' => 'arrow-left',
        'label' => '',
    );
    
    $args = wp_parse_args($args, $defaults);
    
    // Usa label personalizzata oppure genera automaticamente
    $label = !empty($args['label']) ? $args['label'] : meridiana_get_back_label();
    $url = meridiana_get_parent_url();
    
    // Costruisci HTML pulsante
    $button_html = sprintf(
        '<a href="%s" class="btn-back %s" title="%s">
            <i data-lucide="%s" style="display: inline-block; width: 16px; height: 16px; margin-right: 6px; vertical-align: middle;"></i>
            %s
        </a>',
        esc_url($url),
        esc_attr($args['class']),
        esc_attr($label),
        esc_attr($args['icon']),
        esc_html($label)
    );
    
    return $button_html;
}

/**
 * Echo shortcut per renderizzare pulsante "Torna indietro"
 * 
 * @param array $args Stessi parametri di meridiana_render_back_button()
 */
function meridiana_back_button($args = array()) {
    echo meridiana_render_back_button($args);
}

/**
 * Genera breadcrumb intelligente (Home > Convenzioni > Singola Convenzione)
 * 
 * @return string HTML breadcrumb
 */
function meridiana_render_breadcrumb() {
    $breadcrumb_html = '<nav class="breadcrumb" aria-label="Breadcrumb">';
    $breadcrumb_html .= '<ol class="breadcrumb__list">';
    
    // Home link (sempre presente)
    $breadcrumb_html .= '<li class="breadcrumb__item">';
    $breadcrumb_html .= sprintf(
        '<a href="%s" class="breadcrumb__link">Home</a>',
        esc_url(home_url())
    );
    $breadcrumb_html .= '</li>';
    
    // Se è single post, mostra archive
    if (is_singular(array('convenzione', 'salute_benessere', 'protocollo', 'modulo', 'organigramma'))) {
        $post_type = get_post_type();
        
        $archive_info = array(
            'convenzione' => array('url' => home_url('/convenzioni/'), 'label' => 'Convenzioni'),
            'salute_benessere' => array('url' => home_url('/salute-benessere/'), 'label' => 'Salute e Benessere'),
            'protocollo' => array('url' => home_url('/protocolli/'), 'label' => 'Protocolli'),
            'modulo' => array('url' => home_url('/moduli/'), 'label' => 'Moduli'),
            'organigramma' => array('url' => home_url('/organigramma/'), 'label' => 'Organigramma'),
        );
        
        if (isset($archive_info[$post_type])) {
            $breadcrumb_html .= '<li class="breadcrumb__item">';
            $breadcrumb_html .= sprintf(
                '<a href="%s" class="breadcrumb__link">%s</a>',
                esc_url($archive_info[$post_type]['url']),
                esc_html($archive_info[$post_type]['label'])
            );
            $breadcrumb_html .= '</li>';
        }
        
        // Titolo post corrente (non è link)
        $breadcrumb_html .= '<li class="breadcrumb__item" aria-current="page">';
        $breadcrumb_html .= sprintf(
            '<span class="breadcrumb__current">%s</span>',
            esc_html(get_the_title())
        );
        $breadcrumb_html .= '</li>';
    }
    
    // Se è archive, mostra nome archive
    if (is_post_type_archive()) {
        $post_type = get_post_type();
        
        $archive_labels = array(
            'convenzione' => 'Convenzioni',
            'salute_benessere' => 'Salute e Benessere',
            'protocollo' => 'Protocolli',
            'modulo' => 'Moduli',
        );
        
        $label = isset($archive_labels[$post_type]) ? $archive_labels[$post_type] : get_post_type_object($post_type)->label;
        
        $breadcrumb_html .= '<li class="breadcrumb__item" aria-current="page">';
        $breadcrumb_html .= sprintf(
            '<span class="breadcrumb__current">%s</span>',
            esc_html($label)
        );
        $breadcrumb_html .= '</li>';
    }
    
    $breadcrumb_html .= '</ol>';
    $breadcrumb_html .= '</nav>';
    
    return $breadcrumb_html;
}

/**
 * Echo shortcut per breadcrumb
 */
function meridiana_breadcrumb() {
    echo meridiana_render_breadcrumb();
}
