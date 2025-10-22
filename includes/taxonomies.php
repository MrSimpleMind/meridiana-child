<?php
/**
 * Taxonomies Registration
 * 
 * =======================================================================
 * IMPORTANTE: LE TAXONOMIES VANNO CREATE TRAMITE ACF PRO UI
 * =======================================================================
 * 
 * Vai in ACF → Taxonomies → Add New
 * 
 * Crea le seguenti taxonomies con queste configurazioni:
 * 
 * =======================================================================
 * 1. UNITÀ DI OFFERTA
 * =======================================================================
 * Key: unita_offerta
 * Plural Label: Unità di Offerta
 * Singular Label: Unità di Offerta
 * 
 * Settings:
 * ✓ Hierarchical: Yes (come categorie)
 * ✓ Post Types: protocollo, modulo, organigramma
 * ✓ Show in Menu: Yes
 * ✓ Show in REST: Yes
 * 
 * Terms da creare:
 * - Ambulatori
 * - AP
 * - CDI
 * - Cure Domiciliari
 * - Hospice
 * - Paese
 * - R20
 * - RSA
 * - RSA Aperta
 * - RSD
 * 
 * =======================================================================
 * 2. PROFILI PROFESSIONALI
 * =======================================================================
 * Key: profili_professionali
 * Plural Label: Profili Professionali
 * Singular Label: Profilo Professionale
 * 
 * Settings:
 * ✓ Hierarchical: Yes
 * ✓ Post Types: protocollo, modulo
 * ✓ Show in Menu: Yes
 * ✓ Show in REST: Yes
 * 
 * Terms da creare:
 * - Addetto Manutenzione
 * - ASA/OSS
 * - Assistente Sociale
 * - Coordinatore Unità di Offerta
 * - Educatore
 * - FKT
 * - Impiegato Amministrativo
 * - Infermiere
 * - Logopedista
 * - Medico
 * - Psicologa
 * - Receptionista
 * - Terapista Occupazionale
 * - Volontari
 * 
 * =======================================================================
 * 3. AREE DI COMPETENZA
 * =======================================================================
 * Key: aree_competenza
 * Plural Label: Aree di Competenza
 * Singular Label: Area di Competenza
 * 
 * Settings:
 * ✓ Hierarchical: Yes
 * ✓ Post Types: modulo
 * ✓ Show in Menu: Yes
 * ✓ Show in REST: Yes
 * 
 * Terms da creare:
 * - HACCP
 * - Manutenzione
 * - Molteplice
 * - Privacy
 * - Risorse Umane
 * - Sanitaria
 * - Sicurezza
 * - Ufficio Tecnico
 * 
 * =======================================================================
 * 4. TIPOLOGIA CORSO (per LearnDash)
 * =======================================================================
 * Key: tipologia_corso
 * Plural Label: Tipologie Corso
 * Singular Label: Tipologia Corso
 * 
 * Settings:
 * ✓ Hierarchical: Yes
 * ✓ Post Types: sfwd-courses (corso LearnDash)
 * ✓ Show in Menu: Yes
 * ✓ Show in REST: Yes
 * 
 * Terms da creare:
 * - Obbligatori Interni
 * - Obbligatori Esterni
 * - Facoltativi
 * 
 * =======================================================================
 * DOPO AVER CREATO LE TAXONOMIES TRAMITE ACF PRO UI:
 * =======================================================================
 * 
 * 1. Vai in Settings → Permalinks
 * 2. Clicca "Salva modifiche" (per flush rewrite rules)
 * 3. Vai in ogni taxonomy e aggiungi i terms sopra elencati
 * 4. Assegna le taxonomies ai CPT corretti (già configurato in ACF)
 * 
 * =======================================================================
 * NOTE IMPORTANTI:
 * =======================================================================
 * 
 * - Le taxonomies sono CONDIVISE tra CPT
 * - unita_offerta è usata da: Protocollo, Modulo, Organigramma, User Meta
 * - profili_professionali è usata da: Protocollo, Modulo, User Meta
 * - Questo permette filtri coerenti e relazioni tra contenuti
 * 
 * =======================================================================
 */

// Questo file è documentazione, non contiene codice da eseguire
// Tutto viene gestito tramite ACF Pro UI

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Se preferisci registrare taxonomies via codice (sconsigliato, meglio ACF Pro UI),
 * puoi decommentare il codice qui sotto e personalizzarlo.
 */

function meridiana_register_taxonomies() {
    
    // UNITÀ DI OFFERTA
    register_taxonomy('unita_offerta', array('protocollo', 'modulo', 'organigramma'), array(
        'labels' => array(
            'name' => 'Unità di Offerta',
            'singular_name' => 'Unità di Offerta',
            'search_items' => 'Cerca Unità di Offerta',
            'all_items' => 'Tutte le Unità di Offerta',
            'edit_item' => 'Modifica Unità di Offerta',
            'update_item' => 'Aggiorna Unità di Offerta',
            'add_new_item' => 'Aggiungi Unità di Offerta',
            'new_item_name' => 'Nuova Unità di Offerta',
            'menu_name' => 'Unità di Offerta',
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'unita-offerta'),
        'show_in_rest' => true,
    ));
    
    // PROFILI PROFESSIONALI
    register_taxonomy('profili_professionali', array('protocollo', 'modulo'), array(
        'labels' => array(
            'name' => 'Profili Professionali',
            'singular_name' => 'Profilo Professionale',
            'search_items' => 'Cerca Profili',
            'all_items' => 'Tutti i Profili',
            'edit_item' => 'Modifica Profilo',
            'update_item' => 'Aggiorna Profilo',
            'add_new_item' => 'Aggiungi Profilo',
            'new_item_name' => 'Nuovo Profilo',
            'menu_name' => 'Profili Professionali',
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'profilo'),
        'show_in_rest' => true,
    ));
    
    // AREE DI COMPETENZA
    register_taxonomy('aree_competenza', array('modulo'), array(
        'labels' => array(
            'name' => 'Aree di Competenza',
            'singular_name' => 'Area di Competenza',
            'search_items' => 'Cerca Aree',
            'all_items' => 'Tutte le Aree',
            'edit_item' => 'Modifica Area',
            'update_item' => 'Aggiorna Area',
            'add_new_item' => 'Aggiungi Area',
            'new_item_name' => 'Nuova Area',
            'menu_name' => 'Aree di Competenza',
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'area-competenza'),
        'show_in_rest' => true,
    ));
    
    // TIPOLOGIA CORSO (LearnDash)
    register_taxonomy('tipologia_corso', array('sfwd-courses'), array(
        'labels' => array(
            'name' => 'Tipologie Corso',
            'singular_name' => 'Tipologia Corso',
            'search_items' => 'Cerca Tipologie',
            'all_items' => 'Tutte le Tipologie',
            'edit_item' => 'Modifica Tipologia',
            'update_item' => 'Aggiorna Tipologia',
            'add_new_item' => 'Aggiungi Tipologia',
            'new_item_name' => 'Nuova Tipologia',
            'menu_name' => 'Tipologie Corso',
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'tipologia-corso'),
        'show_in_rest' => true,
    ));
}
add_action('init', 'meridiana_register_taxonomies');

/**
 * Helper: Ottieni terms di una taxonomy in formato select options
 * Utile per form frontend
 * 
 * @param string $taxonomy Nome taxonomy
 * @return array Array di terms (id => name)
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
 * Helper: Ottieni HTML select di una taxonomy
 * 
 * @param string $taxonomy Nome taxonomy
 * @param string $name Nome campo
 * @param mixed $selected Valore selezionato
 * @param array $args Argomenti aggiuntivi
 * @return string HTML select
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
    
    $output = sprintf('<select name="%s" id="%s" class="%s">', 
        esc_attr($name), 
        esc_attr($args['id']), 
        esc_attr($args['class'])
    );
    
    if (!empty($args['show_option_none'])) {
        $output .= sprintf('<option value="">%s</option>', esc_html($args['show_option_none']));
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
