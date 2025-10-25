/**
 * Main entry point per gli script del tema child Meridiana.
 * Mantiene lo spazio per i moduli Alpine/vanilla che verranno aggiunti nelle prossime fasi.
 */

import Alpine from 'alpinejs';
import documentTracker from './tracking';

// Espone Alpine globalmente così i moduli legacy (es. gestore-dashboard.js) possono
// agganciarsi all'evento `alpine:init` prima che Alpine venga avviato.
window.Alpine = Alpine;

// Registra i componenti Alpine.js PRIMA di Alpine.start()
Alpine.data('documentTracker', documentTracker);

document.addEventListener('DOMContentLoaded', () => {
    document.dispatchEvent(new CustomEvent('meridiana:frontend-ready'));
    // Avvia Alpine.js qui, dopo che il DOM è pronto e i componenti sono stati definiti
    Alpine.start();
});
