# 📄 Struttura Pagine e Templates

> **Contesto**: Layout e struttura di tutte le pagine principali della piattaforma

**Leggi anche**: 
- `01_Design_System.md` per componenti UI
- `04_Navigazione_Layout.md` per header/nav
- `02_Struttura_Dati_CPT.md` per query contenuti

---

## 🏠 HOME (Dashboard)

### Template: `page-home.php`

### Layout

```
┌─────────────────────────────┐
│ Desktop Header / Bottom Nav │
├─────────────────────────────┤
│ Quick Actions (4 pulsanti)  │
├─────────────────────────────┤
│ Alerting (badges/notifiche) │
├─────────────────────────────┤
│ Feed Attività (ultimi cont.)│
├─────────────────────────────┤
│ I Miei Progressi (corsi)    │
└─────────────────────────────┘
```

### Implementazione

```php
<?php
/*
Template Name: Home Dashboard
*/

get_header(); ?>

<div class="content-wrapper">
    <div class="container">
        <h1>Benvenuto, <?php echo wp_get_current_user()->first_name; ?></h1>
        
        <!-- Quick Actions -->
        <section class="quick-actions">
            <a href="/documentazione" class="action-card">
                <i data-lucide="file-text"></i>
                <span>Cerca Protocolli</span>
            </a>
            <a href="/documentazione?type=modulo" class="action-card">
                <i data-lucide="folder"></i>
                <span>Cerca Moduli</span>
            </a>
            <a href="/corsi" class="action-card">
                <i data-lucide="graduation-cap"></i>
                <span>I Miei Corsi</span>
            </a>
            <a href="/organigramma" class="action-card">
                <i data-lucide="users"></i>
                <span>Organigramma</span>
            </a>
        </section>
        
        <!-- Alerting -->
        <?php get_template_part('templates/parts/home/alerting'); ?>
        
        <!-- Feed Attività -->
        <?php get_template_part('templates/parts/home/feed-attivita'); ?>
        
        <!-- Progressi Corsi -->
        <?php get_template_part('templates/parts/home/progressi-corsi'); ?>
    </div>
</div>

<?php get_footer(); ?>
```

### Partial: Alerting

```php
<?php
// templates/parts/home/alerting.php

$user_id = get_current_user_id();
$corsi_scadenza = get_corsi_in_scadenza($user_id);
$nuove_comunicazioni = get_comunicazioni_non_lette($user_id);
$nuovi_protocolli = get_protocolli_ultimi_7_giorni();

if (empty($corsi_scadenza) && empty($nuove_comunicazioni) && empty($nuovi_protocolli)) {
    return;
}
?>

<section class="alerting">
    <h2>Notifiche</h2>
    
    <?php if (!empty($corsi_scadenza)): ?>
    <div class="alert alert-warning">
        <i data-lucide="alert-triangle"></i>
        <div>
            <strong>Corsi in Scadenza</strong>
            <p>Hai <?php echo count($corsi_scadenza); ?> certificati in scadenza nei prossimi 7 giorni.</p>
            <a href="/corsi" class="btn btn-sm btn-outline">Visualizza</a>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($nuove_comunicazioni)): ?>
    <div class="alert alert-info">
        <i data-lucide="bell"></i>
        <div>
            <strong>Nuove Comunicazioni</strong>
            <p><?php echo count($nuove_comunicazioni); ?> nuove comunicazioni non lette.</p>
            <a href="/comunicazioni" class="btn btn-sm btn-outline">Leggi</a>
        </div>
    </div>
    <?php endif; ?>
</section>
```

---

## 📚 DOCUMENTAZIONE

### Template: `page-documentazione.php`

### Layout

```
┌───────────────┬─────────────────┐
│ Sidebar       │ Grid Documenti  │
│ - Tipo        │ - Card doc      │
│ - UDO         │ - Card doc      │
│ - Profilo     │ - Card doc      │
│ - Area        │ ...             │
│ - Search      │                 │
│               │ Paginazione     │
└───────────────┴─────────────────┘
```

### Implementazione

```php
<?php
/*
Template Name: Documentazione
*/

get_header();

// Filters from URL
$tipo = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'all';
$udo = isset($_GET['udo']) ? intval($_GET['udo']) : null;
$profilo = isset($_GET['profilo']) ? intval($_GET['profilo']) : null;
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

// Build query
$args = array(
    'post_type' => ($tipo === 'all') ? array('protocollo', 'modulo') : $tipo,
    'posts_per_page' => 20,
    'paged' => get_query_var('paged') ?: 1,
);

if ($udo || $profilo) {
    $args['tax_query'] = array('relation' => 'AND');
    
    if ($udo) {
        $args['tax_query'][] = array(
            'taxonomy' => 'unita_offerta',
            'field' => 'term_id',
            'terms' => $udo,
        );
    }
    
    if ($profilo) {
        $args['tax_query'][] = array(
            'taxonomy' => 'profili_professionali',
            'field' => 'term_id',
            'terms' => $profilo,
        );
    }
}

if ($search) {
    $args['s'] = $search;
}

$query = new WP_Query($args);
?>

<div class="content-wrapper">
    <div class="container">
        <div class="documentazione-layout">
            <!-- Sidebar Filters -->
            <aside class="sidebar-filters">
                <?php get_template_part('templates/parts/documentazione/filtri'); ?>
            </aside>
            
            <!-- Results Grid -->
            <main class="results-grid">
                <div class="results-header">
                    <h1>Documentazione</h1>
                    <span class="results-count"><?php echo $query->found_posts; ?> documenti</span>
                </div>
                
                <?php if ($query->have_posts()): ?>
                    <div class="documents-grid">
                        <?php while ($query->have_posts()): $query->the_post(); ?>
                            <?php get_template_part('templates/parts/cards/card-documento'); ?>
                        <?php endwhile; ?>
                    </div>
                    
                    <?php 
                    // Paginazione
                    the_posts_pagination(array(
                        'mid_size' => 2,
                        'prev_text' => '<i data-lucide="chevron-left"></i>',
                        'next_text' => '<i data-lucide="chevron-right"></i>',
                    ));
                    ?>
                <?php else: ?>
                    <p>Nessun documento trovato con i filtri selezionati.</p>
                <?php endif; ?>
                
                <?php wp_reset_postdata(); ?>
            </main>
        </div>
    </div>
</div>

<?php get_footer(); ?>
```

### Card Documento

```php
<?php
// templates/parts/cards/card-documento.php

$doc_type = get_post_type();
$pdf_field = ($doc_type === 'protocollo') ? 'pdf_protocollo' : 'pdf_modulo';
$pdf_id = get_field($pdf_field);
$pdf_url = wp_get_attachment_url($pdf_id);
$riassunto = get_field('riassunto');
$udo_terms = get_the_terms(get_the_ID(), 'unita_offerta');
?>

<div class="card card-documento">
    <div class="card-documento__header">
        <span class="badge badge-primary"><?php echo $doc_type === 'protocollo' ? 'Protocollo' : 'Modulo'; ?></span>
    </div>
    
    <div class="card-documento__body">
        <h3><?php the_title(); ?></h3>
        
        <?php if ($riassunto): ?>
        <p class="riassunto"><?php echo wp_trim_words($riassunto, 20); ?></p>
        <?php endif; ?>
        
        <?php if ($udo_terms): ?>
        <div class="tags">
            <?php foreach ($udo_terms as $term): ?>
            <span class="tag"><?php echo $term->name; ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="card-documento__footer">
        <?php if ($doc_type === 'protocollo'): ?>
            <button class="btn btn-primary" data-modal-open="pdf-<?php echo get_the_ID(); ?>">
                <i data-lucide="eye"></i> Visualizza
            </button>
        <?php else: ?>
            <a href="<?php echo $pdf_url; ?>" class="btn btn-primary" download>
                <i data-lucide="download"></i> Scarica
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Modal per Protocollo (solo visualizzazione) -->
<?php if ($doc_type === 'protocollo'): ?>
<div class="modal" id="pdf-<?php echo get_the_ID(); ?>" x-data="documentTracker(<?php echo get_the_ID(); ?>)">
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php the_title(); ?></h3>
            <button data-modal-close><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body">
            <?php echo do_shortcode('[pdf-embedder url="' . $pdf_url . '"]'); ?>
        </div>
    </div>
</div>
<?php endif; ?>
```

---

## 🎓 CORSI

### Template: `page-corsi.php`

### Layout con Tabs

```php
<div class="content-wrapper">
    <div class="container">
        <h1>I Miei Corsi</h1>
        
        <div class="tabs" x-data="{ tab: 'obbligatori' }">
            <div class="tabs-nav">
                <button @click="tab = 'obbligatori'" 
                        :class="{ 'active': tab === 'obbligatori' }">
                    Obbligatori
                </button>
                <button @click="tab = 'facoltativi'"
                        :class="{ 'active': tab === 'facoltativi' }">
                    Facoltativi
                </button>
                <button @click="tab = 'completati'"
                        :class="{ 'active': tab === 'completati' }">
                    Completati
                </button>
            </div>
            
            <!-- Tab: Obbligatori -->
            <div x-show="tab === 'obbligatori'" class="tab-content">
                <div class="subtabs" x-data="{ subtab: 'interni' }">
                    <button @click="subtab = 'interni'" 
                            :class="{ 'active': subtab === 'interni' }">
                        Interni
                    </button>
                    <button @click="subtab = 'esterni'"
                            :class="{ 'active': subtab === 'esterni' }">
                        Esterni
                    </button>
                    
                    <!-- Interni -->
                    <div x-show="subtab === 'interni'">
                        <?php get_template_part('templates/parts/corsi/grid-corsi-interni'); ?>
                    </div>
                    
                    <!-- Esterni -->
                    <div x-show="subtab === 'esterni'">
                        <?php 
                        $link_esterno = get_field('link_autologin_esterno', 'user_' . get_current_user_id());
                        if ($link_esterno):
                        ?>
                            <div class="external-platform">
                                <h3>Piattaforma Certificata Esterna</h3>
                                <p>Accedi alla piattaforma di formazione certificata per completare i corsi obbligatori esterni.</p>
                                <a href="<?php echo esc_url($link_esterno); ?>" 
                                   class="btn btn-primary btn-lg" 
                                   target="_blank">
                                    Accedi alla Piattaforma
                                    <i data-lucide="external-link"></i>
                                </a>
                            </div>
                        <?php else: ?>
                            <p>Nessun link configurato per i corsi esterni.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Tab: Facoltativi -->
            <div x-show="tab === 'facoltativi'" class="tab-content">
                <?php get_template_part('templates/parts/corsi/grid-corsi-facoltativi'); ?>
            </div>
            
            <!-- Tab: Completati -->
            <div x-show="tab === 'completati'" class="tab-content">
                <?php get_template_part('templates/parts/corsi/grid-corsi-completati'); ?>
            </div>
        </div>
    </div>
</div>
```

---

## 📊 ANALYTICS (Solo Gestore/Admin)

### Template: `page-analytics.php`

```php
<?php
/*
Template Name: Analytics
*/

// Check permission
if (!current_user_can('view_analytics')) {
    wp_die('Non hai i permessi per visualizzare questa pagina.');
}

get_header();
?>

<div class="content-wrapper">
    <div class="container-wide">
        <h1>Analytics Visualizzazioni</h1>
        
        <!-- KPI Cards -->
        <?php get_template_part('templates/parts/analytics/kpi-widget'); ?>
        
        <!-- Filters -->
        <div class="analytics-filters">
            <select id="filter-tipo" class="select-field">
                <option value="">Tutti i Tipi</option>
                <option value="protocollo">Protocolli</option>
                <option value="modulo">Moduli</option>
            </select>
            
            <select id="filter-udo" class="select-field">
                <option value="">Tutte le UDO</option>
                <?php
                $udos = get_terms('unita_offerta');
                foreach ($udos as $udo):
                ?>
                <option value="<?php echo $udo->term_id; ?>"><?php echo $udo->name; ?></option>
                <?php endforeach; ?>
            </select>
            
            <input type="date" id="filter-date-from" class="input-field">
            <input type="date" id="filter-date-to" class="input-field">
            
            <button id="apply-filters" class="btn btn-primary">Applica Filtri</button>
        </div>
        
        <!-- Documents Table -->
        <?php get_template_part('templates/parts/analytics/documents-table'); ?>
        
        <!-- Document Detail (se query param) -->
        <?php if (isset($_GET['view']) && $_GET['view'] === 'document'): ?>
            <?php get_template_part('templates/parts/analytics/document-detail'); ?>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>
```

---

## 🤖 Checklist per IA

Quando crei template pagine:

- [ ] Usa `get_template_part()` per riutilizzabilità
- [ ] Sempre `get_header()` e `get_footer()`
- [ ] Wrap content in `.content-wrapper` (per bottom nav)
- [ ] Check permissions prima di render contenuti sensibili
- [ ] Escape output: `esc_html()`, `esc_url()`
- [ ] Sanitize input: `sanitize_text_field()`, `intval()`
- [ ] Alpine.js per interattività (tabs, filters, modals)
- [ ] Responsive: test mobile-first
- [ ] Pagination: usa `the_posts_pagination()`
- [ ] Loading states per AJAX

---

**📄 Struttura pagine completa e modulare.**
