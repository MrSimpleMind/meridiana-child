# 📊 Analytics e Tracking Visualizzazioni

> **Contesto**: Sistema analytics completo con custom database table, tracking real-time, dashboard report

**Leggi anche**: 
- `02_Struttura_Dati_CPT.md` per documenti trackabili
- `08_Pagine_Templates.md` per dashboard analytics

---

## 🎯 Overview Sistema Analytics

### Obiettivi
- **Track visualizzazioni** documenti (Protocolli, Moduli)
- **Report compliance** per audit
- **Dashboard gestore** con KPI
- **Export CSV** liste utenti

### Stack
- **Custom database table** (non wp_postmeta)
- **REST API** endpoints per tracking
- **Alpine.js** client-side tracking
- **DataTables.js** per dashboard

---

## 🗄 Database Schema

### Custom Table

```php
// includes/analytics.php - Activation hook

function crea_tabella_analytics() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'document_views';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT NOT NULL,
        document_id BIGINT NOT NULL,
        document_type VARCHAR(50) NOT NULL,
        view_timestamp DATETIME NOT NULL,
        view_duration INT DEFAULT NULL COMMENT 'Secondi',
        ip_address VARCHAR(45),
        user_agent VARCHAR(255),
        INDEX user_doc_idx (user_id, document_id),
        INDEX timestamp_idx (view_timestamp),
        INDEX document_idx (document_id, document_type)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'crea_tabella_analytics');
```

### Perché Custom Table?

- ✅ **Performance**: Query dedicate veloci
- ✅ **Scalabilità**: 300 utenti × 200 doc × views = migliaia record
- ✅ **Report**: Aggregazioni SQL native
- ❌ wp_postmeta esploderebbe e rallenterebbe

### Scalabilità a Lungo Termine

**Stima crescita dati:**
- 300 utenti × 200 documenti × 5 visualizzazioni media = **300.000 record/anno**
- Dopo 3 anni: **~900.000 record**

**Strategie di scalabilità:**

#### 1. Archiving Record Vecchi

```php
// includes/analytics.php

function archiving_analytics_vecchi() {
    global $wpdb;
    $table = $wpdb->prefix . 'document_views';
    $archive_table = $wpdb->prefix . 'document_views_archive';
    
    // Crea tabella archive se non esiste
    $wpdb->query("CREATE TABLE IF NOT EXISTS $archive_table LIKE $table");
    
    // Sposta record più vecchi di 2 anni in archive
    $date_threshold = date('Y-m-d', strtotime('-2 years'));
    
    $wpdb->query($wpdb->prepare(
        "INSERT INTO $archive_table SELECT * FROM $table WHERE view_timestamp < %s",
        $date_threshold
    ));
    
    $deleted = $wpdb->query($wpdb->prepare(
        "DELETE FROM $table WHERE view_timestamp < %s",
        $date_threshold
    ));
    
    error_log("Analytics archiving: {$deleted} record spostati in archive");
}

// Cron job trimestrale
if (!wp_next_scheduled('archiving_analytics')) {
    wp_schedule_event(time(), 'monthly', 'archiving_analytics');
}
add_action('archiving_analytics', 'archiving_analytics_vecchi');
```

#### 2. Database Partitioning (Se >1M Record)

```sql
-- Solo se necessario dopo ~3 anni di utilizzo
-- Eseguire via phpMyAdmin o wp-cli

ALTER TABLE wp_document_views 
PARTITION BY RANGE (YEAR(view_timestamp)) (
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p2026 VALUES LESS THAN (2027),
    PARTITION p2027 VALUES LESS THAN (2028),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);
```

**Benefici partitioning:**
- Query su anno corrente: solo 1 partizione scannerizzata
- Performance queries: 5-10x più veloci
- Manutenzione facilitata (drop vecchie partizioni)

#### 3. Monitoring Dimensione Tabella

```php
// Dashboard widget per admin
function analytics_table_size_widget() {
    global $wpdb;
    $table = $wpdb->prefix . 'document_views';
    
    $size = $wpdb->get_var("SELECT 
        ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS size_mb
        FROM information_schema.TABLES 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = '$table'"
    );
    
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
    
    echo "<div class='analytics-size-widget'>";
    echo "<h3>Analytics Database</h3>";
    echo "<p>Record: " . number_format($count) . "</p>";
    echo "<p>Dimensione: {$size} MB</p>";
    
    if ($size > 100) {
        echo "<p style='color:orange;'>⚠️ Considera archiving dei vecchi record</p>";
    }
    
    if ($size > 500) {
        echo "<p style='color:red;'>🚨 Implementa partitioning urgente</p>";
    }
    
    echo "</div>";
}
```

**Quando intervenire:**
- **< 50 MB**: Tutto ok, nessuna azione
- **50-100 MB**: Pianifica archiving entro 6 mesi
- **> 100 MB**: Attiva archiving automatico
- **> 500 MB**: Implementa partitioning

---

## 📡 REST API Endpoints

### Registrazione Endpoints

```php
// api/analytics-api.php

function registra_analytics_endpoints() {
    register_rest_route('piattaforma/v1', '/track-view', array(
        'methods' => 'POST',
        'callback' => 'api_track_view',
        'permission_callback' => 'is_user_logged_in',
    ));
    
    register_rest_route('piattaforma/v1', '/analytics/document/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'api_get_document_analytics',
        'permission_callback' => function() {
            return current_user_can('view_analytics');
        },
    ));
}
add_action('rest_api_init', 'registra_analytics_endpoints');
```

### Track View Endpoint

```php
function api_track_view($request) {
    global $wpdb;
    
    $document_id = intval($request->get_param('document_id'));
    $duration = intval($request->get_param('duration')); // Secondi
    $document_type = get_post_type($document_id);
    
    // Validate document exists
    if (!$document_id || !in_array($document_type, ['protocollo', 'modulo'])) {
        return new WP_Error('invalid_document', 'Documento non valido', array('status' => 400));
    }
    
    // Insert view
    $result = $wpdb->insert(
        $wpdb->prefix . 'document_views',
        array(
            'user_id' => get_current_user_id(),
            'document_id' => $document_id,
            'document_type' => $document_type,
            'view_timestamp' => current_time('mysql'),
            'view_duration' => $duration,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        ),
        array('%d', '%d', '%s', '%s', '%d', '%s', '%s')
    );
    
    if ($result === false) {
        return new WP_Error('db_error', 'Errore database', array('status' => 500));
    }
    
    return rest_ensure_response(array(
        'success' => true,
        'view_id' => $wpdb->insert_id,
    ));
}
```

---

## 🔍 Client-Side Tracking

### Alpine.js Component

```javascript
// assets/js/src/tracking.js

document.addEventListener('alpine:init', () => {
    Alpine.data('documentTracker', (documentId) => ({
        startTime: null,
        documentId: documentId,
        
        init() {
            this.startTime = Date.now();
            
            // Track on page unload
            window.addEventListener('beforeunload', () => {
                this.sendView();
            });
            
            // Track visibility changes (tab switch)
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    this.sendView();
                    this.startTime = null;
                } else {
                    this.startTime = Date.now();
                }
            });
        },
        
        async sendView() {
            if (!this.startTime) return;
            
            const duration = Math.floor((Date.now() - this.startTime) / 1000);
            
            // Only track if viewed for at least 5 seconds
            if (duration < 5) return;
            
            try {
                await fetch('/wp-json/piattaforma/v1/track-view', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': window.meridiana.nonce,
                    },
                    body: JSON.stringify({
                        document_id: this.documentId,
                        duration: duration,
                    }),
                    keepalive: true, // Important for beforeunload
                });
            } catch (error) {
                console.error('Tracking error:', error);
            }
        }
    }));
});
```

### Template Implementation

```php
// single-protocollo.php

<div x-data="documentTracker(<?php echo get_the_ID(); ?>)">
    <!-- Document content here -->
    
    <div class="document-viewer">
        <?php 
        $pdf_id = get_field('pdf_protocollo');
        echo do_shortcode('[pdf-embedder url="' . wp_get_attachment_url($pdf_id) . '"]');
        ?>
    </div>
</div>
```

---

## 📈 Query Analytics

### Get Document Views

```php
// includes/analytics.php

function get_document_views($document_id, $args = array()) {
    global $wpdb;
    
    $defaults = array(
        'unique' => false,
        'date_from' => null,
        'date_to' => null,
    );
    
    $args = wp_parse_args($args, $defaults);
    
    $table = $wpdb->prefix . 'document_views';
    
    if ($args['unique']) {
        $sql = "SELECT COUNT(DISTINCT user_id) as count 
                FROM $table 
                WHERE document_id = %d";
    } else {
        $sql = "SELECT COUNT(*) as count 
                FROM $table 
                WHERE document_id = %d";
    }
    
    if ($args['date_from']) {
        $sql .= $wpdb->prepare(" AND view_timestamp >= %s", $args['date_from']);
    }
    
    if ($args['date_to']) {
        $sql .= $wpdb->prepare(" AND view_timestamp <= %s", $args['date_to']);
    }
    
    return $wpdb->get_var($wpdb->prepare($sql, $document_id));
}
```

### Get Users Who Viewed

```php
function get_users_who_viewed($document_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'document_views';
    
    $sql = "SELECT DISTINCT 
                dv.user_id,
                u.display_name,
                MAX(dv.view_timestamp) as last_view,
                COUNT(*) as view_count,
                SUM(dv.view_duration) as total_duration
            FROM $table dv
            LEFT JOIN {$wpdb->users} u ON dv.user_id = u.ID
            WHERE dv.document_id = %d
            GROUP BY dv.user_id
            ORDER BY last_view DESC";
    
    return $wpdb->get_results($wpdb->prepare($sql, $document_id));
}
```

### Get Users Who NOT Viewed

```php
function get_users_who_not_viewed($document_id, $stato = 'attivo') {
    global $wpdb;
    $table = $wpdb->prefix . 'document_views';
    
    $sql = "SELECT u.ID, u.display_name, u.user_email
            FROM {$wpdb->users} u
            LEFT JOIN $table dv ON u.ID = dv.user_id AND dv.document_id = %d
            WHERE dv.id IS NULL";
    
    if ($stato) {
        $sql .= " AND EXISTS (
            SELECT 1 FROM {$wpdb->usermeta} um
            WHERE um.user_id = u.ID
            AND um.meta_key = 'stato_utente'
            AND um.meta_value = %s
        )";
        
        return $wpdb->get_results($wpdb->prepare($sql, $document_id, $stato));
    }
    
    return $wpdb->get_results($wpdb->prepare($sql, $document_id));
}
```

---

## 📊 Dashboard Analytics

### KPI Widget

```php
// templates/parts/analytics/kpi-widget.php

$total_docs = wp_count_posts('protocollo')->publish + wp_count_posts('modulo')->publish;
$total_views_week = get_total_views_last_week();
$avg_duration = get_average_view_duration();
$active_users = count_active_users_week();
?>

<div class="analytics-kpi">
    <div class="kpi-card">
        <div class="kpi-card__value"><?php echo $total_docs; ?></div>
        <div class="kpi-card__label">Documenti Totali</div>
    </div>
    
    <div class="kpi-card">
        <div class="kpi-card__value"><?php echo $total_views_week; ?></div>
        <div class="kpi-card__label">Visualizzazioni (7gg)</div>
    </div>
    
    <div class="kpi-card">
        <div class="kpi-card__value"><?php echo round($avg_duration / 60, 1); ?>min</div>
        <div class="kpi-card__label">Durata Media</div>
    </div>
    
    <div class="kpi-card">
        <div class="kpi-card__value"><?php echo $active_users; ?></div>
        <div class="kpi-card__label">Utenti Attivi (7gg)</div>
    </div>
</div>
```

### Documents Table

```php
// templates/parts/analytics/documents-table.php

<table class="table table-analytics" id="analytics-table">
    <thead>
        <tr>
            <th>Documento</th>
            <th>Tipo</th>
            <th>Visualizzazioni Uniche</th>
            <th>Visualizzazioni Totali</th>
            <th>Durata Media</th>
            <th>Ultimo Accesso</th>
            <th>Azioni</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $documents = get_all_trackable_documents();
        foreach ($documents as $doc):
            $views_unique = get_document_views($doc->ID, ['unique' => true]);
            $views_total = get_document_views($doc->ID);
            $avg_duration = get_document_avg_duration($doc->ID);
            $last_view = get_document_last_view($doc->ID);
        ?>
        <tr>
            <td><?php echo esc_html($doc->post_title); ?></td>
            <td><span class="badge"><?php echo get_post_type($doc->ID); ?></span></td>
            <td><?php echo $views_unique; ?></td>
            <td><?php echo $views_total; ?></td>
            <td><?php echo round($avg_duration / 60, 1); ?>min</td>
            <td><?php echo $last_view ? wp_date('d/m/Y H:i', strtotime($last_view)) : 'Mai'; ?></td>
            <td>
                <a href="?view=document&id=<?php echo $doc->ID; ?>" class="btn btn-sm">
                    Dettagli
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#analytics-table').DataTable({
        order: [[2, 'desc']],
        pageLength: 25,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/it-IT.json'
        }
    });
});
</script>
```

---

## 📥 Export CSV

### Export Function

```php
// includes/analytics.php

function export_users_csv($document_id, $type = 'viewed') {
    if ($type === 'viewed') {
        $users = get_users_who_viewed($document_id);
        $filename = 'utenti_hanno_visto_' . $document_id . '.csv';
    } else {
        $users = get_users_who_not_viewed($document_id, 'attivo');
        $filename = 'utenti_non_hanno_visto_' . $document_id . '.csv';
    }
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // BOM for Excel UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Headers
    if ($type === 'viewed') {
        fputcsv($output, ['Nome', 'Email', 'Ultima Visualizzazione', 'N. Visualizzazioni', 'Durata Totale (min)']);
        
        foreach ($users as $user) {
            fputcsv($output, [
                $user->display_name,
                get_userdata($user->user_id)->user_email,
                wp_date('d/m/Y H:i', strtotime($user->last_view)),
                $user->view_count,
                round($user->total_duration / 60, 1),
            ]);
        }
    } else {
        fputcsv($output, ['Nome', 'Email', 'UDO', 'Profilo']);
        
        foreach ($users as $user) {
            $udo = get_field('udo_riferimento', 'user_' . $user->ID);
            $profilo = get_field('profilo_professionale', 'user_' . $user->ID);
            
            fputcsv($output, [
                $user->display_name,
                $user->user_email,
                $udo ? get_term($udo)->name : '',
                $profilo ? get_term($profilo)->name : '',
            ]);
        }
    }
    
    fclose($output);
    exit;
}

// Handle export request
function handle_analytics_export() {
    if (!isset($_GET['export_analytics']) || !current_user_can('view_analytics')) {
        return;
    }
    
    $document_id = intval($_GET['document_id']);
    $type = sanitize_text_field($_GET['type']);
    
    export_users_csv($document_id, $type);
}
add_action('init', 'handle_analytics_export');
```

---

## 🔄 Caching

### Cache Reports

```php
function get_cached_analytics($document_id, $cache_key, $callback) {
    $transient_key = 'analytics_' . $cache_key . '_' . $document_id;
    $cached = get_transient($transient_key);
    
    if ($cached !== false) {
        return $cached;
    }
    
    $data = call_user_func($callback, $document_id);
    set_transient($transient_key, $data, 6 * HOUR_IN_SECONDS);
    
    return $data;
}

// Usage
$views = get_cached_analytics($doc_id, 'views_unique', function($id) {
    return get_document_views($id, ['unique' => true]);
});
```

---

## 🤖 Checklist per IA

Quando lavori con analytics:

- [ ] Usa custom table, non wp_postmeta per tracking
- [ ] Track solo se durata > 5 secondi
- [ ] Use `keepalive: true` in fetch per beforeunload
- [ ] Sempre check `current_user_can('view_analytics')`
- [ ] Cache report pesanti (6 ore+)
- [ ] Index database appropriati (user_id, document_id, timestamp)
- [ ] Export CSV: usa BOM UTF-8 per Excel
- [ ] Test con 1000+ record per performance
- [ ] Gestisci timezone correttamente
- [ ] Log errori tracking senza bloccare UI

---

**📊 Sistema analytics completo e performante.**
