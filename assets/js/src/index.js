/**
 * Main entry point per gli script del tema child Meridiana.
 * Mantiene lo spazio per i moduli Alpine/vanilla che verranno aggiunti nelle prossime fasi.
 */

document.addEventListener('DOMContentLoaded', () => {
    document.dispatchEvent(new CustomEvent('meridiana:frontend-ready'));
});
