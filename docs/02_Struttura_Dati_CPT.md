# 📦 Struttura Dati e Custom Post Types

> **Contesto**: Definizione completa di tutti i Custom Post Types, taxonomies, custom fields e relazioni

**Leggi anche**: 
- `05_Gestione_Frontend_Forms.md` per form di inserimento/modifica
- `06_Analytics_Tracking.md` per tracking visualizzazioni documenti

---

## 📋 Overview Struttura Dati

### CPT Principali

1. **Protocollo** - Documenti operativi PDF (non scaricabili)
2. **Modulo** - Moduli operativi PDF (scaricabili)
3. **Convenzione** - Convenzioni aziendali per dipendenti
4. **Organigramma** - Rubrica figure apicali
5. **Salute e Benessere** - Contenuti welfare
6. **Comunicazioni** - Post standard WordPress (no CPT custom)
7. **Corsi** - LearnDash (gestito da plugin)

---

## 📄 CPT: PROTOCOLLO

### Descrizione
Protocolli operativi scannerizzati della Cooperativa. Visibili solo online tramite embed PDF, **non scaricabili** dagli utenti.

### Registrazione CPT

```php
// includes/cpt-register.php

function registra_cpt_protocollo() {
    $labels = array(
        'name' => 'Protocolli',
        'singular_name' => 'Protocollo',
        'add_new' => 'Aggiungi Protocollo',
        'add_new_item' => 'Aggiungi Nuovo Protocollo',
        'edit_item' => 'Modifica Protocollo',
        'view_item' => 'Visualizza Protocollo',
        'search_items' => 'Cerca Protocolli',
    );
    
    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-media-document',
        'supports' => array('title', 'thumbnail'),
        'show_in_rest' => true,
        'capability_type' => 'post',
        'map_meta_cap' => true,
        'rewrite' => array('slug' => 'protocolli'),
    );
    
    register_post_type('protocollo', $args);
}
add_action('init', 'registra_cpt_protocollo');
```

### Custom Fields (ACF)

```php
// includes/acf-config.php

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
    'key' => 'group_protocollo',
    'title' => 'Dati Protocollo',
    'fields' => array(
        array(
            'key' => 'field_pdf_protocollo',
            'label' => 'File PDF Protocollo',
            'name' => 'pdf_protocollo',
            'type' => 'file',
            'required' => 1,
            'return_format' => 'id',
            'mime_types' => 'pdf',
        ),
        array(
            'key' => 'field_riassunto',
            'label' => 'Riassunto',
            'name' => 'riassunto',
            'type' => 'textarea',
            'rows' => 4,
        ),
        array(
            'key' => 'field_moduli_allegati',
            'label' => 'Moduli Collegati',
            'name' => 'moduli_allegati',
            'type' => 'relationship',
            'post_type' => array('modulo'),
            'filters' => array('search', 'taxonomy'),
            'return_format' => 'id',
        ),
        array(
            'key' => 'field_pianificazione_ats',
            'label' => 'È un Piano di ATS?',
            'name' => 'pianificazione_ats',
            'type' => 'true_false',
            'ui' => 1,
            'default_value' => 0,
        ),
    ),
    'location' => array(
        array(
            array(
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'protocollo',
            ),
        ),
    ),
));

endif;
```

### Taxonomies

**unita_offerta** (condivisa con Modulo):
- Ambulatori
- AP
- CDI
- Cure Domiciliari
- Hospice
- Paese
- R20
- RSA
- RSA Aperta
- RSD

**profili_professionali** (condivisa con Modulo):
- Addetto Manutenzione
- ASA/OSS
- Assistente Sociale
- Coordinatore Unità di Offerta
- Educatore
- FKT
- Impiegato Amministrativo
- Infermiere
- Logopedista
- Medico
- Psicologa
- Receptionista
- Terapista Occupazionale
- Volontari

### Query Example

```php
// Query protocolli per UDO specifica
$args = array(
    'post_type' => 'protocollo',
    'posts_per_page' => -1,
    'tax_query' => array(
        array(
            'taxonomy' => 'unita_offerta',
            'field' => 'slug',
            'terms' => 'rsa',
        ),
    ),
);
$protocolli = new WP_Query($args);
```

---

## 📋 CPT: MODULO

### Descrizione
Moduli operativi **scaricabili e stampabili** dal personale.

### Registrazione CPT

```php
function registra_cpt_modulo() {
    $labels = array(
        'name' => 'Moduli',
        'singular_name' => 'Modulo',
        'add_new' => 'Aggiungi Modulo',
        'add_new_item' => 'Aggiungi Nuovo Modulo',
        'edit_item' => 'Modifica Modulo',
    );
    
    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-media-spreadsheet',
        'supports' => array('title', 'thumbnail'),
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'moduli'),
    );
    
    register_post_type('modulo', $args);
}
add_action('init', 'registra_cpt_modulo');
```

### Custom Fields (ACF)

```php
acf_add_local_field_group(array(
    'key' => 'group_modulo',
    'title' => 'Dati Modulo',
    'fields' => array(
        array(
            'key' => 'field_pdf_modulo',
            'label' => 'File PDF Modulo',
            'name' => 'pdf_modulo',
            'type' => 'file',
            'required' => 1,
            'return_format' => 'array',
            'mime_types' => 'pdf',
        ),
    ),
    'location' => array(
        array(
            array(
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'modulo',
            ),
        ),
    ),
));
```

### Taxonomies

**Condivise con Protocollo:**
- `unita_offerta`
- `profili_professionali`

**Specifica Modulo:**

**aree_competenza**:
- HACCP
- Manutenzione
- Molteplice
- Privacy
- Risorse Umane
- Sanitaria
- Sicurezza
- Ufficio Tecnico

---

## 🏷 CPT: CONVENZIONE

### Descrizione
Convenzioni aziendali attive o scadute per i dipendenti.

### Registrazione CPT

```php
function registra_cpt_convenzione() {
    $labels = array(
        'name' => 'Convenzioni',
        'singular_name' => 'Convenzione',
        'add_new' => 'Aggiungi Convenzione',
    );
    
    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-tag',
        'supports' => array('title', 'editor', 'thumbnail'),
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'convenzioni'),
    );
    
    register_post_type('convenzione', $args);
}
add_action('init', 'registra_cpt_convenzione');
```

### Custom Fields (ACF)

```php
acf_add_local_field_group(array(
    'key' => 'group_convenzione',
    'title' => 'Dati Convenzione',
    'fields' => array(
        array(
            'key' => 'field_convenzione_attiva',
            'label' => 'Convenzione Attiva',
            'name' => 'convenzione_attiva',
            'type' => 'true_false',
            'ui' => 1,
            'default_value' => 1,
            'message' => 'Convenzione attualmente valida',
        ),
        array(
            'key' => 'field_convenzione_immagine',
            'label' => 'Immagine in Evidenza',
            'name' => 'immagine_evidenza',
            'type' => 'image',
            'return_format' => 'array',
            'preview_size' => 'medium',
        ),
        array(
            'key' => 'field_convenzione_contatti',
            'label' => 'Contatti',
            'name' => 'contatti',
            'type' => 'wysiwyg',
            'tabs' => 'visual',
            'toolbar' => 'basic',
        ),
        array(
            'key' => 'field_convenzione_allegati',
            'label' => 'Allegati',
            'name' => 'allegati',
            'type' => 'repeater',
            'layout' => 'table',
            'button_label' => 'Aggiungi Allegato',
            'sub_fields' => array(
                array(
                    'key' => 'field_allegato_nome',
                    'label' => 'Nome File',
                    'name' => 'nome',
                    'type' => 'text',
                ),
                array(
                    'key' => 'field_allegato_file',
                    'label' => 'File',
                    'name' => 'file',
                    'type' => 'file',
                    'return_format' => 'array',
                ),
            ),
        ),
    ),
    'location' => array(
        array(
            array(
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'convenzione',
            ),
        ),
    ),
));
```

### Display Logic

```php
// Filtra solo convenzioni attive per default
$args = array(
    'post_type' => 'convenzione',
    'meta_key' => 'convenzione_attiva',
    'meta_value' => '1',
);
```

---

## 👔 CPT: ORGANIGRAMMA

### Descrizione
Rubrica delle figure apicali con contatti diretti (email/telefono).

### Registrazione CPT

```php
function registra_cpt_organigramma() {
    $labels = array(
        'name' => 'Organigramma',
        'singular_name' => 'Contatto',
        'add_new' => 'Aggiungi Contatto',
    );
    
    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => false,
        'menu_icon' => 'dashicons-groups',
        'supports' => array('title'),
        'show_in_rest' => true,
        'publicly_queryable' => false,
    );
    
    register_post_type('organigramma', $args);
}
add_action('init', 'registra_cpt_organigramma');
```

### Custom Fields (ACF)

```php
acf_add_local_field_group(array(
    'key' => 'group_organigramma',
    'title' => 'Dati Contatto',
    'fields' => array(
        array(
            'key' => 'field_ruolo',
            'label' => 'Ruolo',
            'name' => 'ruolo',
            'type' => 'text',
            'required' => 1,
        ),
        array(
            'key' => 'field_udo_riferimento',
            'label' => 'Unità di Offerta',
            'name' => 'udo_riferimento',
            'type' => 'taxonomy',
            'taxonomy' => 'unita_offerta',
            'field_type' => 'select',
            'return_format' => 'id',
        ),
        array(
            'key' => 'field_email_aziendale',
            'label' => 'Email Aziendale',
            'name' => 'email_aziendale',
            'type' => 'email',
            'required' => 1,
        ),
        array(
            'key' => 'field_telefono_aziendale',
            'label' => 'Telefono Aziendale',
            'name' => 'telefono_aziendale',
            'type' => 'text',
        ),
    ),
    'location' => array(
        array(
            array(
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'organigramma',
            ),
        ),
    ),
));
```

### Frontend Display

```php
// Template: click-to-email e click-to-call
$email = get_field('email_aziendale');
$tel = get_field('telefono_aziendale');
?>

<div class="card-organigramma">
    <h3><?php the_title(); ?></h3>
    <p class="ruolo"><?php the_field('ruolo'); ?></p>
    
    <div class="actions">
        <a href="mailto:<?php echo $email; ?>" class="btn btn-primary">
            <i data-lucide="mail"></i> Email
        </a>
        
        <?php if($tel): ?>
        <a href="tel:<?php echo $tel; ?>" class="btn btn-secondary">
            <i data-lucide="phone"></i> Chiama
        </a>
        <?php endif; ?>
    </div>
</div>
```

---

## 💚 CPT: SALUTE E BENESSERE

### Descrizione
Contenuti welfare per il benessere dei dipendenti.

### Registrazione CPT

```php
function registra_cpt_salute_benessere() {
    $labels = array(
        'name' => 'Salute e Benessere',
        'singular_name' => 'Articolo Benessere',
        'add_new' => 'Aggiungi Articolo',
    );
    
    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-heart',
        'supports' => array('title', 'editor', 'thumbnail'),
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'salute-benessere'),
    );
    
    register_post_type('salute_benessere', $args);
}
add_action('init', 'registra_cpt_salute_benessere');
```

### Custom Fields (ACF)

```php
acf_add_local_field_group(array(
    'key' => 'group_salute_benessere',
    'title' => 'Risorse Aggiuntive',
    'fields' => array(
        array(
            'key' => 'field_risorse',
            'label' => 'Risorse',
            'name' => 'risorse',
            'type' => 'repeater',
            'layout' => 'table',
            'button_label' => 'Aggiungi Risorsa',
            'sub_fields' => array(
                array(
                    'key' => 'field_risorsa_tipo',
                    'label' => 'Tipo',
                    'name' => 'tipo',
                    'type' => 'select',
                    'choices' => array(
                        'link' => 'Link Esterno',
                        'file' => 'File Scaricabile',
                    ),
                ),
                array(
                    'key' => 'field_risorsa_titolo',
                    'label' => 'Titolo',
                    'name' => 'titolo',
                    'type' => 'text',
                ),
                array(
                    'key' => 'field_risorsa_url',
                    'label' => 'URL',
                    'name' => 'url',
                    'type' => 'url',
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_risorsa_tipo',
                                'operator' => '==',
                                'value' => 'link',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_risorsa_file',
                    'label' => 'File',
                    'name' => 'file',
                    'type' => 'file',
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_risorsa_tipo',
                                'operator' => '==',
                                'value' => 'file',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'location' => array(
        array(
            array(
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'salute_benessere',
            ),
        ),
    ),
));
```

---

## 📢 COMUNICAZIONI (Post Standard)

### Descrizione
Le comunicazioni aziendali utilizzano **post standard di WordPress** senza custom field o CPT aggiuntivi.

### Campi Disponibili (Nativi WordPress)
- **Titolo** - Oggetto della comunicazione
- **Editor** - Contenuto completo (supporta formattazione, link, elenchi)
- **Immagine in evidenza** - Immagine copertina
- **Categorie** - Per organizzare le comunicazioni (es. "HR", "Sicurezza", "Welfare")
- **Data pubblicazione** - Timestamp automatico
- **Autore** - Chi ha pubblicato

### Configurazione Categorie Suggerite

```php
// includes/taxonomies.php

function crea_categorie_comunicazioni() {
    $categorie = array(
        'HR e Risorse Umane',
        'Sicurezza',
        'Welfare',
        'Procedure',
        'Eventi',
        'Generale'
    );
    
    foreach($categorie as $cat) {
        if(!term_exists($cat, 'category')) {
            wp_insert_term($cat, 'category');
        }
    }
}
add_action('init', 'crea_categorie_comunicazioni', 11);
```

### Display Frontend

```php
// Query comunicazioni recenti
$args = array(
    'post_type' => 'post',
    'posts_per_page' => 10,
    'orderby' => 'date',
    'order' => 'DESC'
);
$comunicazioni = new WP_Query($args);

while($comunicazioni->have_posts()): $comunicazioni->the_post();
    // Display card
endwhile;
```

---

## 🎓 CPT: CORSI (LearnDash)

### Descrizione
Gestito dal plugin **LearnDash**. Non richied registrazione manuale.

### Taxonomy Custom

```php
// includes/taxonomies.php

function registra_taxonomy_tipologia_corso() {
    register_taxonomy(
        'tipologia_corso',
        'sfwd-courses', // CPT di LearnDash
        array(
            'label' => 'Tipologia Corso',
            'hierarchical' => true,
            'show_in_rest' => true,
        )
    );
}
add_action('init', 'registra_taxonomy_tipologia_corso');

// Aggiungi i termini di default
function aggiungi_termini_tipologia_corso() {
    $terms = array(
        'Obbligatori Interni',
        'Obbligatori Esterni',
        'Facoltativi',
    );
    
    foreach($terms as $term) {
        if(!term_exists($term, 'tipologia_corso')) {
            wp_insert_term($term, 'tipologia_corso');
        }
    }
}
add_action('init', 'aggiungi_termini_tipologia_corso', 11);
```

### Funzionalità LearnDash

- **Certificati PDF personalizzati** (via LearnDash settings)
- **Auto-enrollment** per nuovi dipendenti (vedi `07_Notifiche_Automazioni.md`)
- **Scadenza certificazione** con re-enrollment automatico
- **Tracking completamento** (native LearnDash)

---

## 🔗 Registrazione Taxonomies

```php
// includes/taxonomies.php

// Unità di Offerta (condivisa)
function registra_taxonomy_unita_offerta() {
    register_taxonomy(
        'unita_offerta',
        array('protocollo', 'modulo'), // Multi-CPT
        array(
            'label' => 'Unità di Offerta',
            'hierarchical' => true,
            'show_in_rest' => true,
            'rewrite' => array('slug' => 'udo'),
        )
    );
    
    // Termini di default
    $termini = array(
        'Ambulatori', 'AP', 'CDI', 'Cure Domiciliari',
        'Hospice', 'Paese', 'R20', 'RSA', 'RSA Aperta', 'RSD'
    );
    
    foreach($termini as $termine) {
        if(!term_exists($termine, 'unita_offerta')) {
            wp_insert_term($termine, 'unita_offerta');
        }
    }
}
add_action('init', 'registra_taxonomy_unita_offerta');

// Profili Professionali (condivisa)
function registra_taxonomy_profili_professionali() {
    register_taxonomy(
        'profili_professionali',
        array('protocollo', 'modulo'),
        array(
            'label' => 'Profili Professionali',
            'hierarchical' => true,
            'show_in_rest' => true,
            'rewrite' => array('slug' => 'profilo'),
        )
    );
    
    $termini = array(
        'Addetto Manutenzione', 'ASA/OSS', 'Assistente Sociale',
        'Coordinatore Unità di Offerta', 'Educatore', 'FKT',
        'Impiegato Amministrativo', 'Infermiere', 'Logopedista',
        'Medico', 'Psicologa', 'Receptionista',
        'Terapista Occupazionale', 'Volontari'
    );
    
    foreach($termini as $termine) {
        if(!term_exists($termine, 'profili_professionali')) {
            wp_insert_term($termine, 'profili_professionali');
        }
    }
}
add_action('init', 'registra_taxonomy_profili_professionali');

// Aree Competenza (solo Modulo)
function registra_taxonomy_aree_competenza() {
    register_taxonomy(
        'aree_competenza',
        'modulo',
        array(
            'label' => 'Aree di Competenza',
            'hierarchical' => true,
            'show_in_rest' => true,
        )
    );
    
    $termini = array(
        'HACCP', 'Manutenzione', 'Molteplice', 'Privacy',
        'Risorse Umane', 'Sanitaria', 'Sicurezza', 'Ufficio Tecnico'
    );
    
    foreach($termini as $termine) {
        if(!term_exists($termine, 'aree_competenza')) {
            wp_insert_term($termine, 'aree_competenza');
        }
    }
}
add_action('init', 'registra_taxonomy_aree_competenza');
```

---

## 🔍 Query Avanzate

### Filtrare per Multiple Taxonomies

```php
$args = array(
    'post_type' => 'protocollo',
    'posts_per_page' => -1,
    'tax_query' => array(
        'relation' => 'AND',
        array(
            'taxonomy' => 'unita_offerta',
            'field' => 'slug',
            'terms' => 'rsa',
        ),
        array(
            'taxonomy' => 'profili_professionali',
            'field' => 'slug',
            'terms' => 'infermiere',
        ),
    ),
);
```

### Ricerca Full-Text con Custom Fields

```php
// Ricerca in titolo + riassunto
$args = array(
    'post_type' => 'protocollo',
    's' => $search_term, // Cerca nel titolo
    'meta_query' => array(
        array(
            'key' => 'riassunto',
            'value' => $search_term,
            'compare' => 'LIKE'
        ),
    ),
);
```

### Documenti Collegati (Relationship)

```php
// Get moduli collegati a un protocollo
$protocollo_id = get_the_ID();
$moduli_ids = get_field('moduli_allegati', $protocollo_id);

if($moduli_ids) {
    $moduli = get_posts(array(
        'post_type' => 'modulo',
        'post__in' => $moduli_ids,
        'orderby' => 'post__in',
    ));
}
```

---

## 🤖 Checklist per IA

Quando lavori con CPT/Fields:

- [ ] Sempre `register_post_type` prima di ACF fields
- [ ] Taxonomy prima dei CPT che le usano
- [ ] `show_in_rest => true` per Gutenberg
- [ ] `supports => array()` minimale (solo necessario)
- [ ] Relationship fields: `return_format => 'id'` per performance
- [ ] File upload: sempre `mime_types` specificato
- [ ] Required fields solo se veramente obbligatori
- [ ] Slug CPT: massimo 20 caratteri, no underscore
- [ ] Taxonomy slug: breve e SEO-friendly
- [ ] Test query con WP_Query prima di mettere in produzione

---

**📦 Struttura dati completa e relazioni definite.**
