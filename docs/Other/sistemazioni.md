# Piano di Miglioramento del Codice

Questo documento descrive tre interventi consigliati per migliorare la qualità, la manutenibilità e le performance della codebase del tema `meridiana-child`.

---

## 1. Eliminazione Stili Inline da `functions.php`

**Situazione Attuale:**
Nel file `functions.php`, la funzione `meridiana_add_inline_styles()` inietta un grande blocco di CSS direttamente nell'`<head>` del sito.

**Criticità:**
- **Manutenibilità Ridotta**: Il CSS è mescolato con il PHP, rendendo difficile la gestione e il debugging.
- **Performance**: Il CSS non può essere messo in cache in modo efficiente dal browser come un file esterno.
- **Inconsistenza**: Bypassa la pipeline di compilazione SCSS → Webpack, che è stata impostata per gestire tutto lo stile.

**Azione Correttiva:**
Spostare tutto il CSS inline nei rispettivi file SCSS nella cartella `assets/css/src/`.

**Passaggi:**

1.  **Crea i file SCSS necessari** se non esistono già. Ad esempio:
    *   `assets/css/src/pages/_archive.scss` (per `.archive-page__title`, `.convenzioni-grid`, etc.)
    *   `assets/css/src/components/_cards.scss` (per `.convenzione-card`, `.salute-card`, etc.)
    *   `assets/css/src/layout/_navigation.scss` (per `.bottom-nav-overlay`, etc.)

2.  **Copia e incolla** le regole CSS dal blocco `<style>` in `functions.php` nei file SCSS appropriati.

3.  **Importa i nuovi file** (o quelli modificati) nel file principale `assets/css/src/main.scss` per assicurarti che vengano compilati.

4.  **Rimuovi completamente** la funzione `meridiana_add_inline_styles()` e il relativo `add_action('wp_head', 'meridiana_add_inline_styles', 99);` dal file `functions.php`.

---

## 2. Ottimizzazione del Versioning dei File CSS

**Situazione Attuale:**
Il file CSS principale viene registrato con `time()` come versione, forzando il browser a scaricarlo a ogni caricamento di pagina.

```php
// In functions.php
$css_version = time();
wp_enqueue_style(
    'meridiana-child-style',
    MERIDIANA_CHILD_URI . '/assets/css/dist/main.css',
    array('blocksy-parent-style'),
    $css_version
);
```

**Criticità:**
- **Performance Negative**: L'uso di `time()` annulla i benefici della cache del browser, aumentando il tempo di caricamento e il consumo di banda a ogni visita.

**Azione Correttiva:**
Utilizzare la data di ultima modifica del file (`filemtime`) per generare il numero di versione. La versione cambierà solo quando il file viene effettivamente modificato.

**Passaggi:**

1.  **Modifica la logica di versioning** in `functions.php` come segue:

```php
// In functions.php -> meridiana_enqueue_styles()

// Rimuovi: $css_version = time();

// Aggiungi questa logica:
$css_file_path = MERIDIANA_CHILD_DIR . '/assets/css/dist/main.css';
$css_version = file_exists($css_file_path) ? filemtime($css_file_path) : MERIDIANA_CHILD_VERSION;

wp_enqueue_style(
    'meridiana-child-style',
    MERIDIANA_CHILD_URI . '/assets/css/dist/main.css',
    array('blocksy-parent-style'),
    $css_version // Usa la nuova versione dinamica
);
```
*Nota: Una logica simile è già correttamente implementata per i file JS e va semplicemente replicata per il CSS.*

---

## 3. Centralizzazione della Gestione JavaScript con Webpack

**Situazione Attuale:**
Diversi file JavaScript vengono accodati separatamente in `functions.php` tramite `wp_enqueue_script`.

```php
// In functions.php
wp_enqueue_script('meridiana-comunicazioni-filter', ...);
wp_enqueue_script('meridiana-archive-articoli', ...);
```

**Criticità:**
- **Richieste HTTP Multiple**: Ogni file `wp_enqueue_script` separato risulta in una richiesta HTTP aggiuntiva, rallentando il caricamento iniziale della pagina.
- **Gestione Frammentata**: La logica JavaScript è sparsa in più file, rendendo più difficile la gestione delle dipendenze e l'ottimizzazione.

**Azione Correttiva:**
Sfruttare Webpack per creare un unico "bundle" JavaScript, importando tutti i moduli necessari nel file di ingresso principale.

**Passaggi:**

1.  **Apri il file di ingresso principale**: `assets/js/src/index.js`.

2.  **Importa gli altri script** all'inizio del file. Ad esempio:
    ```javascript
    // assets/js/src/index.js

    // Importa i moduli esistenti
    import './archive-articoli.js';
    import './gestore-dashboard.js';
    // Aggiungi qui altri import necessari...

    // Il resto del codice di index.js...
    ```
    *Nota: Assicurati che i file come `comunicazioni-filter.js` e `avatar-persistence.js` siano anch'essi importati o che la loro logica sia integrata dove serve.*

3.  **Rimuovi le chiamate `wp_enqueue_script`** superflue da `functions.php`, lasciando solo quella per il file `main.min.js` generato da Webpack.

4.  **Esegui `npm run build`** per ricompilare il bundle JavaScript unificato.
