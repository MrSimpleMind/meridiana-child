<?php
/**
 * ACF Configuration & Field Groups
 * 
 * =======================================================================
 * IMPORTANTE: I FIELD GROUPS VANNO CREATI TRAMITE ACF PRO UI
 * =======================================================================
 * 
 * Vai in ACF → Field Groups → Add New
 * 
 * =======================================================================
 * FIELD GROUPS DA CREARE:
 * =======================================================================
 * 
 * 1. PROTOCOLLO - Campi
 *    Location: Post Type = protocollo
 *    Fields:
 *    - pdf_protocollo (File Upload, Return Format: URL, Mime Types: pdf)
 *    - riassunto (Text Area, Rows: 5)
 *    - moduli_allegati (Relationship, Post Type: modulo, Return Format: Post Object)
 *    - pianificazione_ats (True/False, Message: "Questo protocollo è parte della Pianificazione ATS")
 * 
 * 2. MODULO - Campi
 *    Location: Post Type = modulo
 *    Fields:
 *    - pdf_modulo (File Upload, Return Format: URL, Mime Types: pdf)
 * 
 * 3. CONVENZIONE - Campi
 *    Location: Post Type = convenzione
 *    Fields:
 *    - convenzione_attiva (True/False, Default: Yes, Message: "Convenzione attiva")
 *    - descrizione (WYSIWYG Editor, Toolbar: Full, Media Upload: Yes)
 *    - immagine_evidenza (Image, Return Format: URL, Preview Size: Medium)
 *    - contatti (WYSIWYG Editor, Toolbar: Basic, Media Upload: No)
 *    - allegati (Repeater)
 *      Sub fields:
 *      - file_allegato (File Upload, Return Format: Array)
 *      - titolo_allegato (Text)
 * 
 * 4. ORGANIGRAMMA - Campi
 *    Location: Post Type = organigramma
 *    Fields:
 *    - ruolo (Text, Required: Yes)
 *    - udo_riferimento (Taxonomy Term, Taxonomy: unita_offerta, Return Format: Object)
 *    - email_aziendale (Email, Required: Yes)
 *    - telefono_aziendale (Text, Placeholder: "+39 XXX XXXXXXX")
 * 
 * 5. SALUTE E BENESSERE - Campi
 *    Location: Post Type = salute_benessere
 *    Fields:
 *    - contenuto (WYSIWYG Editor, Toolbar: Full, Media Upload: Yes)
 *    - immagine_evidenza (Image, Return Format: URL, Preview Size: Large)
 *    - risorse (Repeater)
 *      Sub fields:
 *      - tipo_risorsa (Select: Link Esterno, File Download)
 *      - titolo_risorsa (Text)
 *      - url_risorsa (URL, Conditional Logic: tipo_risorsa = Link Esterno)
 *      - file_risorsa (File Upload, Conditional Logic: tipo_risorsa = File Download)
 * 
 * 6. UTENTE - Custom Fields (User Meta)
 *    Location: User Form = All
 *    Fields:
 *    - stato_utente (Radio Button, Choices: attivo|Attivo, sospeso|Sospeso, licenziato|Licenziato, Default: attivo)
 *    - link_autologin_esterno (URL, Instructions: "URL SSO per piattaforma formazione esterna certificata")
 *    - profilo_professionale (Taxonomy Term, Taxonomy: profili_professionali, Return Format: Object)
 *    - udo_riferimento (Taxonomy Term, Taxonomy: unita_offerta, Return Format: Object)
 * 
 * =======================================================================
 * SETTINGS CONSIGLIATE PER TUTTI I FIELD GROUPS:
 * =======================================================================
 * 
 * Style: Standard (WP metabox)
 * Position: Normal (After Content) o High Priority per campi importanti
 * Label Placement: Top (più leggibile)
 * Instruction Placement: Below Label
 * Hide on Screen: (seleziona cosa nascondere se serve)
 * 
 * =======================================================================
 * DOPO AVER CREATO I FIELD GROUPS:
 * =======================================================================
 * 
 * 1. Testa aggiungendo un contenuto di esempio per ogni CPT
 * 2. Verifica che i campi si salvino correttamente
 * 3. Testa il recupero dati con get_field() nel frontend
 * 
 * Esempio:
 * $pdf_url = get_field('pdf_protocollo', $post_id);
 * $riassunto = get_field('riassunto', $post_id);
 * 
 * =======================================================================
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Configurazioni ACF custom
 */

// Nascondi ACF menu per non-admin (opzionale)
/*
function meridiana_hide_acf_menu() {
    if (!current_user_can('administrator')) {
        return false;
    }
    return true;
}
add_filter('acf/settings/show_admin', 'meridiana_hide_acf_menu');
*/

/**
 * Personalizza path per salvataggio Field Groups
 * IMPORTANTE: Abilita version control per configurazioni ACF
 * 
 * NOTA: Questi filtri DEVONO essere eseguiti PRIMA che ACF si inizializzi
 */

// Save JSON path - priorità 10 (default)
add_filter('acf/settings/save_json', 'meridiana_acf_json_save_point');
function meridiana_acf_json_save_point($path) {
    // Percorso assoluto alla cartella acf-json del child theme
    $path = MERIDIANA_CHILD_DIR . '/acf-json';
    
    // Verifica che la cartella esista e sia scrivibile
    if (!file_exists($path)) {
        wp_mkdir_p($path);
    }
    
    // Log per debug (solo in WP_DEBUG mode)
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('ACF JSON Save Path: ' . $path);
    }
    
    return $path;
}

// Load JSON path - priorità 10 (default)
add_filter('acf/settings/load_json', 'meridiana_acf_json_load_point');
function meridiana_acf_json_load_point($paths) {
    // Rimuovi il path originale di ACF
    unset($paths[0]);
    
    // Aggiungi il path del child theme
    $paths[] = MERIDIANA_CHILD_DIR . '/acf-json';
    
    // Log per debug (solo in WP_DEBUG mode)
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('ACF JSON Load Paths: ' . print_r($paths, true));
    }
    
    return $paths;
}

/**
 * Helper: Ottieni valore campo ACF in modo sicuro
 * Con fallback se campo non esiste
 * 
 * @param string $field_name Nome campo
 * @param int $post_id Post ID (default: current post)
 * @param mixed $default Valore default se campo vuoto
 * @return mixed Valore campo o default
 */
function get_acf_field_safe($field_name, $post_id = null, $default = '') {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    if (!function_exists('get_field')) {
        return $default;
    }
    
    $value = get_field($field_name, $post_id);
    
    return !empty($value) ? $value : $default;
}

/**
 * Helper: Ottieni opzioni select da taxonomy per ACF
 * 
 * @param string $taxonomy Nome taxonomy
 * @return array Formato per ACF select field
 */
function get_acf_taxonomy_choices($taxonomy) {
    $terms = get_terms(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
    ));
    
    $choices = array();
    
    if (!is_wp_error($terms) && !empty($terms)) {
        foreach ($terms as $term) {
            $choices[$term->term_id] = $term->name;
        }
    }
    
    return $choices;
}

/**
 * Valida upload PDF (solo per protocolli e moduli)
 * 
 * @param array $errors Errori
 * @param array $file File info
 * @param array $attachment Attachment data
 * @return array Errori aggiornati
 */
function validate_pdf_upload($errors, $file, $attachment) {
    $allowed_mime = array('application/pdf');
    
    if (!in_array($file['type'], $allowed_mime)) {
        $errors[] = 'Solo file PDF sono permessi.';
    }
    
    // Limite dimensione: 10MB
    $max_size = 10 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        $errors[] = 'Il file PDF deve essere massimo 10MB.';
    }
    
    return $errors;
}
// add_filter('acf/validate_attachment', 'validate_pdf_upload', 10, 3);

/**
 * Esempio: Popolare select dinamicamente con taxonomy
 * (Se usi Select invece di Taxonomy field in ACF)
 */
/*
function populate_unita_offerta_choices($field) {
    $field['choices'] = get_acf_taxonomy_choices('unita_offerta');
    return $field;
}
add_filter('acf/load_field/name=udo_riferimento', 'populate_unita_offerta_choices');
*/
