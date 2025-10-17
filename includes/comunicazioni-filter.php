<?php
/**
 * PROMPT 6: Filtro Comunicazioni per Categoria con AJAX
 * 
 * Sistema di filtraggio dinamico per comunicazioni.
 * Quando l'utente seleziona una categoria, la lista si aggiorna in tempo reale
 * senza ricaricare la pagina.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX Handler: Filtra comunicazioni per categoria
 * 
 * Recupera comunicazioni filtrate per categoria via AJAX
 * Response: HTML lista comunicazioni formattate
 */
function meridiana_filter_comunicazioni_ajax() {
    // Verifica nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'meridiana_comunicazioni_filter')) {
        wp_send_json_error('Nonce verificaton failed', 403);
    }
    
    // Recupera categoria selezionata
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $paged = isset($_POST['page']) ? intval($_POST['page']) : 1;
    
    // Parametri query base
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => 10,
        'paged' => $paged,
        'orderby' => 'date',
        'order' => 'DESC',
    );
    
    // Se categoria selezionata, filtra per essa
    if ($category_id > 0) {
        $args['cat'] = $category_id;
    }
    
    // Esegui query
    $comunicazioni = new WP_Query($args);
    
    // Se nessun risultato
    if (!$comunicazioni->have_posts()) {
        wp_send_json_success(array(
            'html' => '<div class="comunicazioni-empty"><p>Nessuna comunicazione trovata</p></div>',
            'pagination' => '',
            'total' => 0,
        ));
    }
    
    // Renderizza comunicazioni
    ob_start();
    
    while ($comunicazioni->have_posts()) {
        $comunicazioni->the_post();
        get_template_part('templates/parts/comunicazione-card');
    }
    
    $html = ob_get_clean();
    
    // Genera paginazione
    $pagination = meridiana_render_pagination($comunicazioni);
    
    wp_send_json_success(array(
        'html' => $html,
        'pagination' => $pagination,
        'total' => $comunicazioni->found_posts,
    ));
    
    wp_die();
}
add_action('wp_ajax_meridiana_filter_comunicazioni', 'meridiana_filter_comunicazioni_ajax');
add_action('wp_ajax_nopriv_meridiana_filter_comunicazioni', 'meridiana_filter_comunicazioni_ajax');

/**
 * Ottieni tutte le categorie disponibili per comunicazioni
 * 
 * @return array Array di categorie con id e nome
 */
function meridiana_get_comunicazioni_categories() {
    // Query categorie che hanno almeno un post
    $categories = get_categories(array(
        'hide_empty' => true,
        'orderby' => 'name',
        'order' => 'ASC',
    ));
    
    return $categories;
}

/**
 * Renderizza filtro categorie comunicazioni con pattern di organigramma
 * 
 * @param array $args Opzioni:
 *   - class: CSS custom
 *   - placeholder: Testo placeholder
 * 
 * @return string HTML filtro
 */
function meridiana_render_comunicazioni_filter($args = array()) {
    $defaults = array(
        'class' => '',
        'placeholder' => 'Seleziona categoria',
    );
    
    $args = wp_parse_args($args, $defaults);
    
    // Ottieni categorie
    $categories = meridiana_get_comunicazioni_categories();
    
    // Nonce per AJAX
    $nonce = wp_create_nonce('meridiana_comunicazioni_filter');
    
    // HTML filtro GROUP con BADGE pattern
    $html = '<div class="filter-group ' . esc_attr($args['class']) . '">';
    
    $html .= '<div class="filter-item">';
    $html .= '<label for="comunicazioni_category_filter" class="filter-label">';
    $html .= '<i data-lucide="filter-circle" class="filter-label__icon"></i>';
    $html .= 'Categoria';
    $html .= '</label>';
    
    $html .= '<div class="filter-wrapper">';
    $html .= '<select id="comunicazioni_category_filter" class="filter-select" data-nonce="' . esc_attr($nonce) . '">';
    
    // Option "Tutte le categorie"
    $html .= '<option value="0">Seleziona</option>';
    
    // Option per ogni categoria
    if ($categories) {
        foreach ($categories as $category) {
            $html .= sprintf(
                '<option value="%d">%s (%d)</option>',
                $category->term_id,
                esc_html($category->name),
                $category->count
            );
        }
    }
    
    $html .= '</select>';
    
    // BADGE (mostrato sopra select)
    $html .= '<div class="filter-badge" id="filterCategoriaBadge">';
    $html .= '<span>Tutte</span>';
    $html .= '<i data-lucide="chevron-down" class="filter-badge__icon"></i>';
    $html .= '</div>';
    
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}

/**
 * Echo shortcut per filtro
 * 
 * @param array $args Opzioni
 */
function meridiana_comunicazioni_filter($args = array()) {
    echo meridiana_render_comunicazioni_filter($args);
}

/**
 * Renderizza lista comunicazioni formattate
 * 
 * Utilizzata sia nel template iniziale che nella risposta AJAX
 * 
 * @param WP_Query $query Query con comunicazioni
 * @return string HTML lista
 */
function meridiana_render_comunicazioni_list($query = null) {
    if ($query === null) {
        // Default query: tutte le comunicazioni recenti
        $query = new WP_Query(array(
            'post_type' => 'post',
            'posts_per_page' => 10,
            'orderby' => 'date',
            'order' => 'DESC',
        ));
    }
    
    if (!$query->have_posts()) {
        return '<div class="comunicazioni-empty"><p>Nessuna comunicazione trovata</p></div>';
    }
    
    ob_start();
    
    echo '<div class="comunicazioni-list">';
    
    while ($query->have_posts()) {
        $query->the_post();
        get_template_part('templates/parts/comunicazione-card');
    }
    
    echo '</div>';
    
    wp_reset_postdata();
    
    return ob_get_clean();
}

/**
 * Renderizza paginazione
 * 
 * @param WP_Query $query Query con comunicazioni
 * @return string HTML paginazione
 */
function meridiana_render_pagination($query) {
    if ($query->max_num_pages <= 1) {
        return '';
    }
    
    $total_pages = $query->max_num_pages;
    $current_page = get_query_var('paged') ? get_query_var('paged') : 1;
    
    $pagination = '<div class="comunicazioni-pagination">';
    
    $pagination .= '<ul class="pagination">';
    
    // Link "Precedente"
    if ($current_page > 1) {
        $pagination .= sprintf(
            '<li class="pagination__item"><a href="#" class="pagination__link pagination__prev" data-page="%d" rel="prev"><i data-lucide="chevron-left"></i> Precedente</a></li>',
            $current_page - 1
        );
    }
    
    // Numeri pagine
    for ($i = 1; $i <= $total_pages; $i++) {
        $active_class = ($i === $current_page) ? 'active' : '';
        $pagination .= sprintf(
            '<li class="pagination__item"><a href="#" class="pagination__link %s" data-page="%d">%d</a></li>',
            $active_class,
            $i,
            $i
        );
    }
    
    // Link "Successivo"
    if ($current_page < $total_pages) {
        $pagination .= sprintf(
            '<li class="pagination__item"><a href="#" class="pagination__link pagination__next" data-page="%d" rel="next">Successivo <i data-lucide="chevron-right"></i></a></li>',
            $current_page + 1
        );
    }
    
    $pagination .= '</ul>';
    $pagination .= '</div>';
    
    return $pagination;
}

/**
 * Echo shortcut per lista comunicazioni
 * 
 * @param WP_Query $query Query (optional)
 */
function meridiana_comunicazioni_list($query = null) {
    echo meridiana_render_comunicazioni_list($query);
}

/**
 * Echo shortcut per paginazione
 * 
 * @param WP_Query $query Query
 */
function meridiana_comunicazioni_pagination($query) {
    echo meridiana_render_pagination($query);
}
