# 🚀 PIANO AZIONE - DASHBOARD GESTORE + ACF FORMS

**Data**: 22 Ottobre 2025  
**Versione**: 1.0  
**Status**: READY FOR DEVELOPMENT  
**Priority**: ALTA - Fase 5 MVP

---

## 📋 CONTESTO & DECISIONI CHIAVE

### Decisioni Utente (da Ricordare)
1. ✅ Loop table columns - OK come proposte
2. ✅ Desktop: Nuova pagina "Dashboard Gestore" nel menu
3. ✅ Mobile: Icona ingranaggio "Gestione" AGGIUNTIVA (no removals)
4. ✅ Priorità tab: Documentazione → Utenti (più complesse)
5. ✅ Leggi ACF JSON per field mapping esatto
6. ✅ Stai attento a classi CSS - non rompere cose esistenti
7. ✅ Best practices: nonce, sanitization, permission checks

### Navigazione Attuale (VERIFICATA)
**Desktop** (da 04_Navigazione_UX.md):
- Home | Documentazione | Corsi | Organigramma | [Dropdown "Altro": Convenzioni, Salute & Benessere]

**Mobile** (bottom nav):
- [Home] [Documenti] [Corsi] [Contatti] [Menu "Altro"]

---

## 🎯 FASE 1: NAVIGAZIONE (Setup Menu + Mobile Icon)

### 1.1 Desktop Navigation
**File**: `templates/parts/navigation/desktop-nav.php`

**Azione**: 
- Aggiungere link "Dashboard Gestore" nel `main-nav`
- Posizionare DOPO il dropdown "Altro"
- Condition: Solo se `current_user_can('manage_platform')` OR `current_user_can('manage_options')`

**Code Pattern**:
```php
<?php if (current_user_can('manage_platform') || current_user_can('manage_options')): ?>
    <a href="<?php echo home_url('/dashboard-gestore/'); ?>" 
       class="main-nav__item main-nav__item--admin">
        <i data-lucide="settings"></i>
        Dashboard Gestore
    </a>
<?php endif; ?>
```

**CSS**: Aggiungere in `_navigation.scss` classe `.main-nav__item--admin` con icona styling

### 1.2 Mobile Navigation (Bottom Nav)
**File**: `templates/parts/navigation/bottom-nav.php`

**Azione**:
- Aggiungere NUOVO bottone con ingranaggio ACCANTO al bottone "Altro"
- Non rimuovere nulla
- Label: "Gestione"
- Condition: Solo se gestore/admin

**Code Pattern**:
```php
<?php if (current_user_can('manage_platform') || current_user_can('manage_options')): ?>
    <a href="<?php echo home_url('/dashboard-gestore/'); ?>" 
       class="bottom-nav__item">
        <i data-lucide="settings"></i>
        <span>Gestione</span>
    </a>
<?php endif; ?>
```

**Nota**: Bottom nav può andare a 6 items se necessario, responsivo in mobile

---

## 🎨 FASE 2: CREARE PAGINA BASE DASHBOARD GESTORE

### 2.1 File Nuovo
- **Nome**: `page-dashboard-gestore.php`
- **Posizione**: `meridiana-child/` (root)
- **URL/Slug**: `/dashboard-gestore/`
- **Page Type**: Standard WordPress page (creare da backend manualmente)

### 2.2 Permission Check (Top of file)
```php
<?php
if (!current_user_can('manage_platform') && !current_user_can('manage_options')) {
    wp_redirect(home_url());
    exit;
}
get_header();
?>
```

### 2.3 Struttura HTML
```
├─ .gestore-dashboard [x-data="gestoreDashboard"]
│  ├─ .dashboard-header
│  │  └─ <h1>Dashboard Gestione Contenuti</h1>
│  │
│  ├─ .dashboard-tabs [Tab Navigation]
│  │  ├─ <button @click="activeTab='documenti'">Documentazione</button>
│  │  ├─ <button @click="activeTab='comunicazioni'">Comunicazioni</button>
│  │  ├─ <button @click="activeTab='convenzioni'">Convenzioni</button>
│  │  ├─ <button @click="activeTab='salute'">Salute & Benessere</button>
│  │  └─ <button @click="activeTab='utenti'">Utenti</button>
│  │
│  ├─ .dashboard-content [Content dinamico per tab]
│  │  └─ Template part per ogni tab
│  │
│  └─ Modal form (vedi FASE 3)
│
└─ Footer
```

### 2.4 Alpine.js Component
**File**: `assets/js/src/gestore-dashboard.js`

```javascript
document.addEventListener('alpine:init', () => {
    Alpine.data('gestoreDashboard', () => ({
        activeTab: 'documenti',
        modalOpen: false,
        modalContent: null,
        selectedPostId: null,
        selectedPostType: null,
        
        async openFormModal(postType, action, postId = null) {
            this.selectedPostType = postType;
            this.selectedPostId = postId;
            this.modalOpen = true;
            
            // Fetch form via AJAX (implementare in FASE 3)
        },
        
        closeModal() {
            this.modalOpen = false;
            this.modalContent = null;
        },
        
        async deletePost(postId) {
            if (!confirm('Sei sicuro di eliminare questo elemento?')) return;
            // AJAX delete (implementare in FASE 3)
        }
    }));
});
```

### 2.5 Styling Base
**File**: `assets/css/src/pages/_gestore-dashboard.scss` (~300 righe)

Creare classi:
- `.gestore-dashboard` - Main wrapper
- `.dashboard-header` - Titolo sezione
- `.dashboard-tabs` - Tab navigation
- `.dashboard-tabs__item` - Tab button
- `.dashboard-tabs__item--active` - Tab button active
- `.dashboard-content` - Content area
- `.dashboard-modal` - Modal wrapper
- `.dashboard-modal__overlay` - Backdrop
- `.dashboard-modal__body` - Modal content

**Importare in**: `assets/css/src/main.scss`

---

## 📊 FASE 3: TAB DOCUMENTAZIONE (PRIMO MVP)

### 3.1 Tab Template File
**File**: `templates/parts/gestore/tab-documenti.php`

**Azione**:
- Query: WP_Query con post_type array ['protocollo', 'modulo']
- Order: ORDER BY post_date DESC
- Limit: 50 posts

**Query Code**:
```php
<?php
$documenti_query = new WP_Query([
    'post_type' => ['protocollo', 'modulo'],
    'posts_per_page' => 50,
    'orderby' => 'date',
    'order' => 'DESC',
]);
?>
```

### 3.2 Tabella + Colonne
**Colonne**: 
1. Titolo (post_title)
2. Tipo (protocollo / modulo via get_post_type())
3. Data (post_date, formato d/m/Y)
4. Status (post_status: publish / draft)
5. Azioni (pulsanti)

**HTML Pattern**:
```html
<table class="dashboard-table">
    <thead>
        <tr>
            <th>Titolo</th>
            <th>Tipo</th>
            <th>Data</th>
            <th>Status</th>
            <th>Azioni</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($documenti_query->have_posts()): $documenti_query->the_post(); ?>
            <tr>
                <td><?php the_title(); ?></td>
                <td><span class="badge badge-info"><?php echo ucfirst(get_post_type()); ?></span></td>
                <td><?php echo get_the_date('d/m/Y'); ?></td>
                <td><span class="badge <?php echo get_post_status() === 'publish' ? 'badge-success' : 'badge-warning'; ?>">
                    <?php echo ucfirst(get_post_status()); ?>
                </span></td>
                <td class="actions-cell">
                    <button class="btn-icon" @click="openFormModal('documenti', 'edit', <?php echo get_the_ID(); ?>)" title="Modifica">
                        ✏️
                    </button>
                    <button class="btn-icon" @click="deletePost(<?php echo get_the_ID(); ?>)" title="Elimina">
                        🗑️
                    </button>
                    <a href="<?php the_permalink(); ?>" class="btn-icon" title="Visualizza">
                        👁️
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
```

### 3.3 Pulsante "+ Nuovo Documento"
```php
<div class="tab-actions">
    <button class="btn btn-primary" @click="openFormModal('documenti', 'new')">
        + Nuovo Documento
    </button>
</div>
```

### 3.4 CSS Tabella Leggera
**Classi**:
- `.dashboard-table` - Tabella base
- `.dashboard-table th` - Header
- `.dashboard-table td` - Cell
- `.actions-cell` - Cell con pulsanti
- `.btn-icon` - Pulsante icona leggero

**Stile**: 
- Mobile: scroll horizontal
- Padding minimo
- No ombra/shadow extra
- Font size ridotto

### 3.5 ACF Form Integration (Modal)
**File**: `includes/gestore-forms.php`

**Function**: `get_acf_form_documenti()` 

```php
function get_acf_form_documenti($action = 'new', $post_id = null) {
    $post_id = $action === 'new' ? 'new_post' : intval($post_id);
    
    $args = [
        'post_id' => $post_id,
        'post_title' => true,
        'post_content' => false,
        'new_post' => [
            'post_type' => 'protocollo', // TODO: gestire dinamico
            'post_status' => 'publish',
        ],
        'field_groups' => ['group_protocollo'],
        'fields' => [], // Leggeremo da ACF JSON
        'submit_value' => $action === 'new' ? 'Pubblica Documento' : 'Aggiorna Documento',
        'updated_message' => 'Documento salvato con successo!',
        'return' => add_query_arg(['action' => 'success'], $_SERVER['REQUEST_URI']),
    ];
    
    ob_start();
    acf_form($args);
    return ob_get_clean();
}
```

**Fields da includere** (leggi da group_protocollo.json):
- PDF file
- Tassonomie (profilo-professionale, unita-offerta, area-competenza)
- Riassunto
- Moduli allegati (se protocollo)
- Status (publish/draft)

### 3.6 File Archiving Hook
**File**: `includes/file-management.php`

**Trigger**: ACF save_post hook

```php
function handle_documento_pdf_update($post_id) {
    if (!in_array(get_post_type($post_id), ['protocollo', 'modulo'])) {
        return;
    }
    
    $field_name = get_post_type($post_id) === 'protocollo' ? 'pdf_protocollo' : 'pdf_modulo';
    $new_file_id = get_field($field_name, $post_id);
    $old_file_id = get_post_meta($post_id, '_previous_pdf_id', true);
    
    // Se file diverso, archivia il vecchio
    if ($old_file_id && $old_file_id !== $new_file_id) {
        archive_old_file($old_file_id, $post_id);
    }
    
    // Salva nuovo file ID per prossima volta
    update_post_meta($post_id, '_previous_pdf_id', $new_file_id);
}
add_action('acf/save_post', 'handle_documento_pdf_update', 20);
```

---

## 👥 FASE 4: TAB UTENTI (SECONDO MVP)

### 4.1 Tab Template File
**File**: `templates/parts/gestore/tab-utenti.php`

**Query**:
```php
<?php
$users_query = new WP_User_Query([
    'role' => '', // Tutti i ruoli
    'orderby' => 'display_name',
    'order' => 'ASC',
    'number' => 50,
]);

$users = $users_query->get_results();
?>
```

### 4.2 Tabella Utenti
**Colonne**:
1. Nome + Cognome
2. Email
3. Profilo Professionale (ACF)
4. Stato (ACF)
5. Azioni

**HTML**:
```php
<table class="dashboard-table">
    <thead>
        <tr>
            <th>Nome</th>
            <th>Email</th>
            <th>Profilo</th>
            <th>Stato</th>
            <th>Azioni</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): 
            $profilo = get_field('profilo_professionale', 'user_' . $user->ID);
            $stato = get_field('stato_utente', 'user_' . $user->ID);
        ?>
            <tr>
                <td><?php echo $user->first_name . ' ' . $user->last_name; ?></td>
                <td><?php echo $user->user_email; ?></td>
                <td><?php echo $profilo ? $profilo : '—'; ?></td>
                <td><?php echo $stato ? $stato : '—'; ?></td>
                <td class="actions-cell">
                    <button class="btn-icon" @click="openFormModal('utenti', 'edit', <?php echo $user->ID; ?>)">
                        ✏️
                    </button>
                    <button class="btn-icon" @click="resetUserPassword(<?php echo $user->ID; ?>)">
                        🔑
                    </button>
                    <button class="btn-icon" @click="deleteUser(<?php echo $user->ID; ?>)">
                        🗑️
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
```

### 4.3 Pulsante "+ Nuovo Utente"
```php
<div class="tab-actions">
    <button class="btn btn-primary" @click="openFormModal('utenti', 'new')">
        + Nuovo Utente
    </button>
</div>
```

### 4.4 User Form (ACF)
**File**: `includes/gestore-forms.php`

**Function**: `get_acf_form_utenti()`

**Fields da includere** (leggi group_user_fields.json):
- Username (new) / Display name (edit)
- Email
- First name
- Last name
- Ruolo (role select)
- Profilo Professionale (ACF)
- Stato (ACF)
- UDO Riferimento (ACF)
- Codice Fiscale (ACF)

---

## 🔧 FASE 5: FILE SYSTEM SETUP

### 5.1 Database Table
**Nome**: `wp_file_archive_log`

**Schema**:
```sql
CREATE TABLE IF NOT EXISTS wp_file_archive_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    file_id BIGINT NOT NULL,
    archived_path VARCHAR(255) NOT NULL,
    archived_date DATETIME NOT NULL,
    user_id BIGINT NOT NULL,
    deleted_date DATETIME DEFAULT NULL,
    INDEX file_id_idx (file_id),
    INDEX archived_date_idx (archived_date)
);
```

**File**: `includes/file-management.php` - Activation hook con `dbDelta()`

### 5.2 Archive Directory
**Path**: `/wp-content/uploads/archive/`

**Setup**:
```php
function ensure_archive_directory() {
    $upload_dir = wp_upload_dir();
    $archive_dir = $upload_dir['basedir'] . '/archive/';
    
    if (!file_exists($archive_dir)) {
        mkdir($archive_dir, 0755, true);
    }
    
    return $archive_dir;
}
```

### 5.3 Naming Convention per File Archiviati
Pattern: `YYYY-MM-DD_HH-ii-ss_original-filename.pdf`

Esempio: `2025-10-22_14-30-45_protocollo-igiene.pdf`

---

## 📁 FILE DA CREARE/MODIFICARE

### ✨ NUOVI FILE (11 file)

1. **`page-dashboard-gestore.php`** - Main page (150 righe)
   - Permission check
   - Tab structure
   - Alpine.js init

2. **`templates/parts/gestore/tab-documenti.php`** - Docs tab (100 righe)
   - Query + loop
   - Table markup

3. **`templates/parts/gestore/tab-comunicazioni.php`** - Comunicazioni tab (stub per ora)

4. **`templates/parts/gestore/tab-convenzioni.php`** - Convenzioni tab (stub)

5. **`templates/parts/gestore/tab-salute.php`** - Salute tab (stub)

6. **`templates/parts/gestore/tab-utenti.php`** - Utenti tab (100 righe)
   - User query + loop
   - Table markup

7. **`templates/parts/gestore/modal-form.php`** - Modal container (50 righe)

8. **`includes/gestore-forms.php`** - ACF forms logic (200 righe)
   - Shortcode/function per forms
   - Field mapping

9. **`includes/file-management.php`** - Archive system (300 righe)
   - Archive function
   - Cron jobs
   - Recovery logic

10. **`assets/js/src/gestore-dashboard.js`** - Alpine.js (200 righe)
    - Modal handling
    - AJAX calls

11. **`assets/css/src/pages/_gestore-dashboard.scss`** - Styling (300 righe)
    - Table styles
    - Modal styles
    - Responsive

### 🔧 MODIFICARE (5 file)

1. **`templates/parts/navigation/desktop-nav.php`**
   - Aggiungere link "Dashboard Gestore" (5 righe)

2. **`templates/parts/navigation/bottom-nav.php`**
   - Aggiungere bottone "Gestione" con ingranaggio (5 righe)

3. **`functions.php`**
   - Include: `gestore-forms.php`, `file-management.php` (3 righe)
   - Register activation hook (1 riga)

4. **`assets/css/src/main.scss`**
   - Import: `@import 'pages/gestore-dashboard'` (1 riga)

5. **`header.php` o `footer.php`**
   - Enqueue: `gestore-dashboard.js` (1 riga in wp_enqueue_script)

---

## 🎯 PRIORIZZAZIONE PER SESSIONI

### Sessione 1 - SETUP BASE (2-3h)
- [ ] Navigazione (desktop + mobile)
- [ ] Page base dashboard
- [ ] Tab structure HTML + Alpine
- [ ] Styling minimo (tabelle)

### Sessione 2 - TAB DOCUMENTAZIONE (2-3h)
- [ ] Query + loop documenti
- [ ] Tabella markup
- [ ] Pulsanti "Nuovo" + "Modifica" (trigger modal)
- [ ] Pulsante "Elimina" AJAX

### Sessione 3 - ACF FORMS INTEGRATION (2-3h)
- [ ] Form ACF new documento
- [ ] Form ACF edit documento
- [ ] Modal rendering
- [ ] File archiving trigger

### Sessione 4 - TAB UTENTI (2-3h)
- [ ] Query + loop utenti
- [ ] Tabella markup
- [ ] User form (new + edit)
- [ ] Reset password AJAX

### Sessione 5 - FILE MANAGEMENT (1-2h)
- [ ] Database table creation
- [ ] Archive directory setup
- [ ] Cron job setup
- [ ] Recovery UI (facoltativo MVP)

### Sessione 6 - TAB RIMANENTI + POLISH (1-2h)
- [ ] Tab Comunicazioni (copy-paste da Docs)
- [ ] Tab Convenzioni
- [ ] Tab Salute & Benessere
- [ ] Testing cross-browser/device

---

## ✅ CHECKLIST BEST PRACTICES

**Security**:
- [ ] Nonce su OGNI form/AJAX
- [ ] Permission check (`current_user_can()`) su TUTTI i template
- [ ] Sanitize input: `sanitize_text_field()`, `sanitize_email()`, `intval()`
- [ ] Validate server-side, non solo client

**ACF**:
- [ ] Leggi ACF JSON per exact field naming
- [ ] Use `get_field()` con user ID per user meta: `get_field('field_name', 'user_' . $user_id)`
- [ ] Check field exists prima di usare

**CSS**:
- [ ] BEM naming convention
- [ ] No inline `<style>` tag
- [ ] Usa design system variables (colors, spacing, shadows)
- [ ] Responsive mobile-first

**UX**:
- [ ] Confirm dialog su delete
- [ ] Success/error messages chiare
- [ ] Loading states su AJAX
- [ ] Keyboard accessible (tab navigation)

**Code Organization**:
- [ ] Una responsabilità per file
- [ ] Evita duplicate queries
- [ ] Riusa template parts
- [ ] Comments per sezioni critiche

---

## 🤖 REMINDERS PER IA

Quando cominci sessione prossima:
1. Leggi il file `02_Struttura_Dati_CPT.md` per field mapping
2. Leggi gli ACF JSON: `group_protocollo.json`, `group_modulo.json`, `group_user_fields.json`
3. Verifica navigazione attuale in `04_Navigazione_UX.md`
4. Non modificare parent theme (Blocksy)
5. Testa con 3 ruoli: subscriber (no vede nulla), gestore, admin
6. Hard refresh browser dopo CSS changes
7. Check console per JS errors

---

## 📞 DOMANDE PER UTENTE PROSSIMA SESSIONE

Prima di iniziare, conferma:
1. CPT Documentazione combina protocollo + modulo? (si nella tab)
2. Form new documento crea quale CPT per default?
3. Delete documento sposta in trash o hard delete?
4. Reset password user resets a temporanea e manda email?
5. Vuoi anche edit campo "Ruolo" in user form?

---

**🚀 PRONTO PER PROSSIMA SESSIONE**  
**Data Creazione**: 22 Ottobre 2025  
**Status**: ✅ COMPLETO E DETTAGLIATO
