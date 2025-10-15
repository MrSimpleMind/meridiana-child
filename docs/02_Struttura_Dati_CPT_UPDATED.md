# 📦 Struttura Dati e Custom Post Types - CONFIGURAZIONE REALE

> **⚠️ QUESTO DOCUMENTO RIFLETTE LA CONFIGURAZIONE REALE DA ACF JSON**  
> Aggiornato: 15 Ottobre 2025 dopo analisi completa acf-json

---

## 🎯 Custom Post Types - CONFIGURAZIONE EFFETTIVA

### 1. **protocollo**
- **Slug**: `protocollo`
- **Has Archive**: ❌ **false** (da abilitare!)
- **Supports**: title, custom-fields
- **Icon**: dashicons-list-view

### 2. **modulo**
- **Slug**: `modulo`
- **Has Archive**: ❌ **false** (da abilitare!)
- **Supports**: title, thumbnail, custom-fields
- **Icon**: dashicons-edit-large

### 3. **convenzione**
- **Slug**: `convenzione`
- **Has Archive**: ❌ **false** (da abilitare!)
- **Supports**: title, thumbnail, custom-fields
- **Icon**: dashicons-database-export

### 4. **organigramma**
- **Slug**: `organigramma`
- **Has Archive**: ❌ **false** (corretto, non serve archive)
- **Supports**: title, thumbnail, custom-fields
- **Icon**: dashicons-info-outline

### 5. **salute-e-benessere-l** ⚠️
- **Slug**: `salute-e-benessere-l` (CON TRATTINI + L!)
- **Has Archive**: ❌ **false** (da abilitare!)
- **Supports**: title, thumbnail, custom-fields
- **Icon**: dashicons-heart
- **⚠️ NOTA**: Questo è il nome EFFETTIVO, non `salute_benessere`!

---

## 🏷️ Taxonomies - CONFIGURAZIONE EFFETTIVA

### 1. **unita-offerta**
- **Slug**: `unita-offerta` (CON TRATTINO!)
- **Post Types**: protocollo, modulo
- **Hierarchical**: false

### 2. **profilo-professionale**
- **Slug**: `profilo-professionale` (CON TRATTINO!)
- **Post Types**: protocollo, modulo
- **Hierarchical**: false

### 3. **area-competenza**
- **Slug**: `area-competenza` (CON TRATTINO!)
- **Post Types**: modulo
- **Hierarchical**: false

---

## 📋 Custom Fields - PER CPT

### 🔵 PROTOCOLLO

**Group Key**: `group_protocollo`

| Campo | Nome | Tipo | Required |
|-------|------|------|----------|
| PDF Protocollo | `pdf_protocollo` | file | ✅ Sì |
| Riassunto | `riassunto` | textarea | ❌ No |
| Moduli Allegati | `moduli_allegati` | relationship | ❌ No |
| Pianificazione ATS | `pianificazione_ats` | true_false | ❌ No |

**Uso nei template**:
```php
$pdf_id = get_field('pdf_protocollo');
$riassunto = get_field('riassunto');
$moduli = get_field('moduli_allegati'); // array di post IDs
$is_ats = get_field('pianificazione_ats');
```

---

### 🔵 MODULO

**Group Key**: `group_modulo`

| Campo | Nome | Tipo | Required |
|-------|------|------|----------|
| PDF Modulo | `pdf_modulo` | file | ✅ Sì |

**Uso nei template**:
```php
$pdf_id = get_field('pdf_modulo');
```

---

### 🔵 CONVENZIONE

**Group Key**: `group_convenzione`

| Campo | Nome | Tipo | Required | Note |
|-------|------|------|----------|------|
| Convenzione Attiva | `convenzione_attiva` | true_false | ❌ No | Default: true |
| **Descrizione** | **`descrizione`** | **wysiwyg** | **✅ Sì** | **NON `descrizione_breve`!** |
| Immagine in Evidenza | `immagine_evidenza` | image | ❌ No | |
| Contatti | `contatti` | wysiwyg | ❌ No | |
| Allegati | `allegati` | repeater | ❌ No | |

**⚠️ ATTENZIONE**: Il campo si chiama `descrizione` (WYSIWYG completo), NON `descrizione_breve`!

**Uso nei template**:
```php
$attiva = get_field('convenzione_attiva'); // true/false
$descrizione = get_field('descrizione'); // HTML wysiwyg COMPLETO
$immagine_id = get_field('immagine_evidenza');
$contatti = get_field('contatti');
$allegati = get_field('allegati'); // array repeater

// Per estratto breve:
$descrizione_breve = wp_trim_words(strip_tags($descrizione), 20);
```

---

### 🔵 ORGANIGRAMMA

**Group Key**: `group_organigramma`

| Campo | Nome | Tipo | Required | Note |
|-------|------|------|----------|------|
| Ruolo | `ruolo` | text | ✅ Sì | |
| UDO Riferimento | `udo_riferimento` | select | ❌ No | NON taxonomy! |
| Email Aziendale | `email_aziendale` | email | ❌ No | |
| Telefono Aziendale | `telefono_aziendale` | text | ❌ No | |

**⚠️ NOTA**: `udo_riferimento` è un SELECT con choices hardcoded, NON una taxonomy!

**Choices disponibili**:
- ambulatori, ap, cdi, cure_domiciliari, hospice, paese, r20, rsa, rsa_aperta, rsd

**Uso nei template**:
```php
$ruolo = get_field('ruolo');
$udo = get_field('udo_riferimento'); // restituisce value: 'rsa', 'cdi', ecc.
$email = get_field('email_aziendale');
$telefono = get_field('telefono_aziendale');
```

---

### 🔵 SALUTE-E-BENESSERE-L

**Group Key**: `group_salute_benessere`

| Campo | Nome | Tipo | Required | Note |
|-------|------|------|----------|------|
| Contenuto | `contenuto` | wysiwyg | ✅ Sì | Campo CUSTOM, non editor WP! |
| Immagine in Evidenza | `immagine_evidenza` | image | ❌ No | |
| Risorse | `risorse` | repeater | ❌ No | link o file |

**⚠️ IMPORTANTE**: Il contenuto principale è nel campo `contenuto` (WYSIWYG), NON nell'editor standard WordPress!

**Uso nei template**:
```php
$contenuto = get_field('contenuto'); // HTML wysiwyg
$immagine_id = get_field('immagine_evidenza');
$risorse = get_field('risorse'); // array repeater

// Per estratto:
$excerpt = wp_trim_words(strip_tags($contenuto), 30);
```

**Struttura Risorse (repeater)**:
```php
if( have_rows('risorse') ):
    while( have_rows('risorse') ): the_row();
        $tipo = get_sub_field('tipo'); // 'link' o 'file'
        $titolo = get_sub_field('titolo');
        
        if($tipo == 'link'):
            $url = get_sub_field('url');
        elseif($tipo == 'file'):
            $file = get_sub_field('file'); // array
        endif;
    endwhile;
endif;
```

---

### 🔵 USER FIELDS

**Group Key**: `group_user_fields`

| Campo | Nome | Tipo | Required | Note |
|-------|------|------|----------|------|
| Stato Utente | `stato_utente` | radio | ✅ Sì | attivo/sospeso/licenziato |
| Link Autologin | `link_autologin_esterno` | url | ❌ No | |
| Profilo Professionale | `profilo_professionale` | select | ❌ No | NON taxonomy! |
| UDO Riferimento | `udo_riferimento` | select | ❌ No | NON taxonomy! |

**⚠️ NOTA**: Anche nei user fields, `profilo_professionale` e `udo_riferimento` sono SELECT, NON taxonomies!

**Uso nei template**:
```php
$stato = get_field('stato_utente', 'user_' . $user_id); // 'attivo', 'sospeso', 'licenziato'
$autologin = get_field('link_autologin_esterno', 'user_' . $user_id);
$profilo = get_field('profilo_professionale', 'user_' . $user_id);
$udo = get_field('udo_riferimento', 'user_' . $user_id);
```

---

## 🔍 Query Corrette

### Query Convenzioni Attive
```php
$args = array(
    'post_type' => 'convenzione',
    'meta_query' => array(
        array(
            'key' => 'convenzione_attiva',
            'value' => '1',
            'compare' => '='
        )
    )
);
```

### Query Salute e Benessere
```php
$args = array(
    'post_type' => 'salute-e-benessere-l', // CON TRATTINI!
    'posts_per_page' => 3,
    'orderby' => 'date',
    'order' => 'DESC'
);
```

### Query con Taxonomy (Protocolli per UDO)
```php
$args = array(
    'post_type' => 'protocollo',
    'tax_query' => array(
        array(
            'taxonomy' => 'unita-offerta', // CON TRATTINO!
            'field' => 'slug',
            'terms' => 'rsa'
        )
    )
);
```

---

## ⚠️ PROBLEMI DA RISOLVERE

### 1. **Has Archive = false**
Tutti i CPT hanno `has_archive: false`. Questo significa:
- ❌ `get_post_type_archive_link()` restituisce `false`
- ❌ Link "Vedi tutto" NON funzionano
- ❌ URL tipo `/convenzione/` non esistono

**Soluzione**: Abilitare has_archive in ACF UI per:
- convenzione
- salute-e-benessere-l

### 2. **Nome CPT Salute inconsistente**
- **ACF**: `salute-e-benessere-l`
- **Template usano**: `salute_benessere`

**Soluzione**: Aggiornare TUTTI i template per usare `salute-e-benessere-l`

### 3. **Campo Convenzione sbagliato**
- **ACF**: `descrizione` (wysiwyg)
- **Template usano**: `descrizione_breve`

**Soluzione**: Usare `get_field('descrizione')` e fare trim/strip_tags

---

## ✅ Template File Names CORRETTI

Per WordPress, i nomi dei template archive seguono questa regola:
```
archive-{post_type_slug}.php
```

Quindi:
- ✅ `archive-protocollo.php`
- ✅ `archive-modulo.php`
- ✅ `archive-convenzione.php`
- ✅ `archive-salute-e-benessere-l.php` (CON TRATTINI!)

**NON:**
- ❌ `archive-salute_benessere.php`
- ❌ `page-archivio-convenzioni.php` (questo è per pagine manuali)

---

## 📝 Checklist Correzioni

- [ ] Abilitare has_archive per convenzione (ACF UI)
- [ ] Abilitare has_archive per salute-e-benessere-l (ACF UI)
- [ ] Aggiornare page-home.php: `salute_benessere` → `salute-e-benessere-l`
- [ ] Aggiornare convenzioni-carousel.php: `descrizione_breve` → `descrizione` + trim
- [ ] Aggiornare salute-list.php: query + excerpt da campo `contenuto`
- [ ] Salvare Permalink Settings in WP Admin
- [ ] Testare link "Vedi tutto"

---

**📦 Documentazione aggiornata con configurazione REALE da ACF JSON**
