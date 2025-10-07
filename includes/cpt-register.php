<?php
/**
 * Custom Post Types Registration
 * 
 * =======================================================================
 * IMPORTANTE: I CPT VANNO CREATI TRAMITE ACF PRO UI
 * =======================================================================
 * 
 * Vai in ACF → Post Types → Add New
 * 
 * Crea i seguenti CPT con queste configurazioni:
 * 
 * =======================================================================
 * 1. PROTOCOLLO
 * =======================================================================
 * Key: protocollo
 * Plural Label: Protocolli
 * Singular Label: Protocollo
 * 
 * Settings:
 * ✓ Public: Yes
 * ✓ Show in Menu: Yes
 * ✓ Menu Icon: dashicons-media-text
 * ✓ Supports: title, editor (per riassunto)
 * ✓ Has Archive: Yes
 * ✓ Rewrite Slug: documentazione/protocolli
 * ✓ Taxonomies: unita_offerta, profili_professionali
 * 
 * =======================================================================
 * 2. MODULO
 * =======================================================================
 * Key: modulo
 * Plural Label: Moduli
 * Singular Label: Modulo
 * 
 * Settings:
 * ✓ Public: Yes
 * ✓ Show in Menu: Yes
 * ✓ Menu Icon: dashicons-media-document
 * ✓ Supports: title
 * ✓ Has Archive: Yes
 * ✓ Rewrite Slug: documentazione/moduli
 * ✓ Taxonomies: unita_offerta, profili_professionali, aree_competenza
 * 
 * =======================================================================
 * 3. CONVENZIONE
 * =======================================================================
 * Key: convenzione
 * Plural Label: Convenzioni
 * Singular Label: Convenzione
 * 
 * Settings:
 * ✓ Public: Yes
 * ✓ Show in Menu: Yes
 * ✓ Menu Icon: dashicons-tag
 * ✓ Supports: title, editor, thumbnail
 * ✓ Has Archive: Yes
 * ✓ Rewrite Slug: convenzioni
 * 
 * =======================================================================
 * 4. ORGANIGRAMMA
 * =======================================================================
 * Key: organigramma
 * Plural Label: Organigramma
 * Singular Label: Contatto
 * 
 * Settings:
 * ✓ Public: Yes
 * ✓ Show in Menu: Yes
 * ✓ Menu Icon: dashicons-id
 * ✓ Supports: title
 * ✓ Has Archive: Yes
 * ✓ Rewrite Slug: organigramma
 * 
 * =======================================================================
 * 5. SALUTE E BENESSERE
 * =======================================================================
 * Key: salute_benessere
 * Plural Label: Salute e Benessere
 * Singular Label: Articolo Benessere
 * 
 * Settings:
 * ✓ Public: Yes
 * ✓ Show in Menu: Yes
 * ✓ Menu Icon: dashicons-heart
 * ✓ Supports: title, editor, thumbnail
 * ✓ Has Archive: Yes
 * ✓ Rewrite Slug: salute-benessere
 * 
 * =======================================================================
 * 6. COMUNICAZIONI (usa post WordPress default customizzato)
 * =======================================================================
 * Non serve CPT custom, usa i Post standard di WordPress
 * Rinomina semplicemente "Posts" in "Comunicazioni" nel menu admin
 * 
 * =======================================================================
 * 7. CORSI (gestiti da LearnDash)
 * =======================================================================
 * Non serve creare CPT, LearnDash crea automaticamente:
 * - sfwd-courses (Corsi)
 * - sfwd-lessons (Lezioni)
 * - sfwd-topics (Argomenti)
 * - sfwd-quiz (Quiz)
 * - sfwd-certificates (Certificati)
 * 
 * Aggiungi solo la taxonomy custom "tipologia_corso" (vedi taxonomies.php)
 * 
 * =======================================================================
 * DOPO AVER CREATO I CPT TRAMITE ACF PRO UI:
 * =======================================================================
 * 
 * 1. Vai in Settings → Permalinks
 * 2. Clicca "Salva modifiche" (per flush rewrite rules)
 * 3. Testa che gli URL funzionino:
 *    - /documentazione/protocolli/
 *    - /documentazione/moduli/
 *    - /convenzioni/
 *    - /organigramma/
 *    - /salute-benessere/
 * 
 * =======================================================================
 */

// Questo file è documentazione, non contiene codice da eseguire
// Tutto viene gestito tramite ACF Pro UI

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Se preferisci registrare CPT via codice (sconsigliato, meglio ACF Pro UI),
 * puoi decommentare il codice qui sotto e personalizzarlo.
 */

/*
function meridiana_register_cpt() {
    
    // PROTOCOLLO
    register_post_type('protocollo', array(
        'labels' => array(
            'name' => 'Protocolli',
            'singular_name' => 'Protocollo',
            'add_new' => 'Aggiungi Protocollo',
            'add_new_item' => 'Aggiungi Nuovo Protocollo',
            'edit_item' => 'Modifica Protocollo',
            'new_item' => 'Nuovo Protocollo',
            'view_item' => 'Visualizza Protocollo',
            'search_items' => 'Cerca Protocolli',
            'not_found' => 'Nessun protocollo trovato',
            'not_found_in_trash' => 'Nessun protocollo nel cestino'
        ),
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-media-text',
        'supports' => array('title', 'editor'),
        'rewrite' => array('slug' => 'documentazione/protocolli'),
        'show_in_rest' => true,
        'taxonomies' => array('unita_offerta', 'profili_professionali')
    ));
    
    // Aggiungi altri CPT qui...
}
add_action('init', 'meridiana_register_cpt');
*/
