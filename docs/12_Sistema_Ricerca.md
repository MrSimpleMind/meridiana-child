# 🔍 Sistema di Ricerca per la Documentazione

> **Ultimo aggiornamento**: 1 Novembre 2025
> **Fonte**: `api/analytics-api.php` (per analogia), `assets/js/src/`

**Obiettivo**: Implementare un sistema di ricerca rapido e funzionale per i documenti (`protocolli` e `moduli`) senza l'aggiunta di plugin a pagamento o dipendenze esterne complesse.

---

## 🎯 Soluzione Raccomandata: AJAX Custom + REST API

Per mantenere il controllo completo sulla logica, garantire la privacy dei dati e sfruttare lo stack tecnologico già in uso (Alpine.js, REST API), si raccomanda un'implementazione custom.

### Pro e Contro

**Pro:**
- ✅ **Nessuna Dipendenza Esterna**: Sfrutta le funzionalità native di WordPress e del tema.
- ✅ **Controllo Totale**: La logica di ricerca e la presentazione dei risultati sono interamente personalizzabili.
- ✅ **Privacy**: I dati non lasciano mai il server della cooperativa.
- ✅ **Performance Controllata**: L'uso del debouncing previene un carico eccessivo sul server.

**Contro:**
- ❌ **No Fuzzy Search Nativa**: Non tollera errori di battitura come soluzioni più avanzate (es. Fuse.js o Algolia).
- ❌ **Latenza di Rete**: Ogni ricerca richiede una chiamata al server, anche se minimizzata dal debouncing.

---

## ⚙️ Implementazione

### 1. Endpoint REST API per la Ricerca

Creare un endpoint che accetti una query di ricerca e restituisca i risultati in formato JSON.

**File**: `api/analytics-api.php` (o un nuovo `api/search-api.php`)

```php
function register_document_search_endpoint() {
    register_rest_route('piattaforma/v1', '/search-documents', array(
        'methods' => 'GET',
        'callback' => 'api_search_documents_callback',
        'permission_callback' => 'is_user_logged_in',
        'args' => array(
            'q' => array(
                'required' => true,
                'validate_callback' => function($param) {
                    return is_string($param) && strlen(trim($param)) >= 2;
                },
            ),
        ),
    ));
}
add_action('rest_api_init', 'register_document_search_endpoint');

function api_search_documents_callback($request) {
    $query_string = sanitize_text_field($request->get_param('q'));

    $args = array(
        'post_type' => array('protocollo', 'modulo'),
        'posts_per_page' => 20,
        's' => $query_string,
        'orderby' => 'relevance',
        'post_status' => 'publish',
    );

    $search_query = new WP_Query($args);

    $results = array();
    if ($search_query->have_posts()) {
        while ($search_query->have_posts()) {
            $search_query->the_post();
            $results[] = array(
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'type' => get_post_type(),
                'excerpt' => get_field('riassunto') ?: wp_trim_words(get_the_content(), 20),
                'url' => get_permalink(),
            );
        }
        wp_reset_postdata();
    }

    return rest_ensure_response(array(
        'results' => $results,
        'total' => $search_query->found_posts,
    ));
}
```

### 2. Componente Alpine.js per la Ricerca Istantanea

Un componente Alpine.js gestirà l'input dell'utente, le chiamate AJAX e la visualizzazione dei risultati.

**File**: `assets/js/src/index.js` (o un nuovo file dedicato)

```javascript
document.addEventListener('alpine:init', () => {
    Alpine.data('instantSearch', () => ({
        query: '',
        results: [],
        isLoading: false,
        debounceTimer: null,

        search() {
            clearTimeout(this.debounceTimer);

            if (this.query.length < 2) {
                this.results = [];
                return;
            }

            this.debounceTimer = setTimeout(async () => {
                this.isLoading = true;
                try {
                    const response = await fetch(
                        `/wp-json/piattaforma/v1/search-documents?q=${encodeURIComponent(this.query)}`,
                        {
                            headers: {
                                'X-WP-Nonce': window.meridiana.nonce,
                            },
                        }
                    );
                    const data = await response.json();
                    this.results = data.results;
                } catch (error) {
                    console.error('Errore durante la ricerca:', error);
                } finally {
                    this.isLoading = false;
                }
            }, 300); // Attendi 300ms dopo l'ultimo input
        },

        highlight(text) {
            if (!this.query) return text;
            const regex = new RegExp(`(${this.query})`, 'gi');
            return text.replace(regex, '<mark>$1</mark>');
        }
    }));
});
```

### 3. Template HTML per la Search Box

Questo markup può essere inserito in un template part (es. `templates/parts/search-box.php`) e incluso dove necessario.

```html
<div class="search-wrapper" x-data="instantSearch()">
    <div class="search-input-group">
        <input type="search" class="search-input" placeholder="Cerca documenti..."
            x-model="query"
            @input.debounce.300ms="search"
            @keydown.escape="query = ''; results = []">
        <i data-lucide="search" class="search-icon"></i>
        <div x-show="isLoading" class="search-loading">
            <i data-lucide="loader" class="animate-spin"></i>
        </div>
    </div>

    <div x-show="results.length > 0" class="search-results" @click.away="results = []">
        <template x-for="result in results" :key="result.id">
            <a :href="result.url" class="search-result-item">
                <div class="result-header">
                    <span x-html="highlight(result.title)" class="result-title"></span>
                    <span :class="result.type === 'protocollo' ? 'badge-protocollo' : 'badge-modulo'" class="badge"></span>
                </div>
                <p x-html="highlight(result.excerpt)" class="result-excerpt"></p>
            </a>
        </template>
    </div>

    <div x-show="query.length >= 2 && results.length === 0 && !isLoading" class="search-no-results">
        Nessun risultato trovato per "<span x-text="query"></span>"
    </div>
</div>
```

---

## 🤖 Checklist di Implementazione

- [ ] **Backend**: Aggiungere il codice PHP per registrare l'endpoint REST.
- [ ] **Frontend**: Aggiungere il componente Alpine.js al file JavaScript principale.
- [ ] **Template**: Creare e includere il template part per la search box.
- [ ] **Styling**: Aggiungere gli stili per `.search-wrapper`, `.search-results`, etc. in un file SCSS dedicato (es. `_search.scss`).
- **Sicurezza**: Assicurarsi che l'endpoint sia protetto e che tutti gli input e output siano sanificati/escapati.
- **Test**: Verificare il funzionamento con query valide, non valide, e con nessun risultato.