<?php
/**
 * PROMPT 5: Breadcrumb & Back Navigation Intelligente
 * 
 * Genera URL intelligenti per i pulsanti "Torna indietro"
 * Segue la gerarchia: Single Post → Archive → Home
 * 
 * Usa funzioni WordPress native: get_post_type_archive_link() e get_option('page_for_posts')
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rileva se l'utente viene dalla homepage
 * 
 * Legge il referrer HTTP e verifica se corrisponde alla homepage
 * Usato per determinare il corretto pulsante "Torna indietro"
 * 
 * @return bool True se viene dalla homepage, False altrimenti
 */
function meridiana_is_referred_from_home() {
    // Recupera il referrer HTTP
    $referrer = isset($_SERVER['HTTP_REFERER']) ? esc_url_raw($_SERVER['HTTP_REFERER']) : '';
    
    // Se non c'è referrer, non viene dalla homepage
    if (empty($referrer)) {
        return false;
    }
    
    // Ottieni l'URL della homepage
    $home = home_url();
    
    // Verifica se il referrer inizia con l'URL homepage
    // Aggiungi "/" alla fine per evitare falsi positivi
    $home_with_slash = trailingslashit($home);
    $referrer_normalized = trailingslashit(parse_url($referrer, PHP_URL_SCHEME) . '://' . parse_url($referrer, PHP_URL_HOST) . parse_url($referrer, PHP_URL_PATH));
    
    // Confronto semplice: se il referrer è la homepage (con o senza query string)
    return $referrer_normalized === $home_with_slash;
}

/**
 * Ottieni URL genitore basato sulla pagina corrente e referrer
 * 
 * Determina a quale livello gerarchico tornare:
 * - Single Post (da homepage) → Homepage
 * - Single Post (da archive) → Archive dello stesso CPT
 * - Archive → Home
 * - Home → Home (rimane)
 * 
 * Usa HTTP_REFERER per tracciare il punto di partenza
 * 
 * @return string URL pagina genitore
 */
function meridiana_get_parent_url() {
    // Se è single post di un CPT custom O post standard (comunicazioni)
    if (is_singular(array('post', 'convenzione', 'salute_benessere', 'salute-e-benessere-l', 'protocollo', 'modulo', 'organigramma'))) {
        $post_type = get_post_type();
        
        // Se viene dalla homepage, torna alla homepage
        if (meridiana_is_referred_from_home()) {
            return home_url();
        }
        
        // Per post standard (comunicazioni), usa page_for_posts
        if ($post_type === 'post') {
            $blog_page = get_option('page_for_posts');
            return $blog_page ? get_permalink($blog_page) : home_url('/blog/');
        }
        
        // Per CPT custom, usa get_post_type_archive_link di WordPress
        $archive_url = get_post_type_archive_link($post_type);
        if ($archive_url) {
            return $archive_url;
        }
        
        // Fallback se non esiste archive link registrato
        return home_url();
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
 * Se viene dalla homepage → "Torna indietro"
 * Se viene dall'archive → "Torna a {Nome Archivio}"
 * 
 * @return string Etichetta del pulsante
 */
function meridiana_get_back_label() {
    if (is_singular(array('post', 'convenzione', 'salute_benessere', 'salute-e-benessere-l', 'protocollo', 'modulo', 'organigramma'))) {
        // Se viene dalla homepage, etichetta generica
        if (meridiana_is_referred_from_home()) {
            return 'Torna indietro';
        }
        
        // Altrimenti, specifica il tipo archivio
        $post_type = get_post_type();
        
        // Mappa CPT → Etichetta
        $labels = array(
            'post' => 'Torna a Comunicazioni',
            'convenzione' => 'Torna a Convenzioni',
            'salute_benessere' => 'Torna a Salute e Benessere',
            'salute-e-benessere-l' => 'Torna a Salute e Benessere',
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
    
    // Costruisci HTML pulsante (niente style inline - gestito in CSS)
    $button_html = sprintf(
        '<a href="%s" class="back-link %s" title="%s">
            <i data-lucide="%s" class="back-link__icon"></i>
            <span>%s</span>
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
    if (is_singular(array('post', 'convenzione', 'salute_benessere', 'salute-e-benessere-l', 'protocollo', 'modulo', 'organigramma'))) {
        $post_type = get_post_type();
        
        // Determina URL e label dell'archive
        $archive_url = '';
        $archive_label = '';
        
        if ($post_type === 'post') {
            // Per post standard (comunicazioni), usa page_for_posts
            $blog_page = get_option('page_for_posts');
            $archive_url = $blog_page ? get_permalink($blog_page) : home_url('/blog/');
            $archive_label = 'Comunicazioni';
        } else {
            // Per CPT custom, usa get_post_type_archive_link di WordPress
            $archive_url = get_post_type_archive_link($post_type);
            
            // Genera label da post_type_object
            $post_type_object = get_post_type_object($post_type);
            $archive_label = $post_type_object ? $post_type_object->labels->name : $post_type;
        }
        
        if ($archive_url) {
            $breadcrumb_html .= '<li class="breadcrumb__item">';
            $breadcrumb_html .= sprintf(
                '<a href="%s" class="breadcrumb__link">%s</a>',
                esc_url($archive_url),
                esc_html($archive_label)
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
        $post_type_object = get_post_type_object($post_type);
        $label = $post_type_object ? $post_type_object->labels->name : $post_type;
        
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
