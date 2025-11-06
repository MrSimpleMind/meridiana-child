/**
 * Main entry point per gli script del tema child Meridiana.
 * Mantiene lo spazio per i moduli Alpine/vanilla che verranno aggiunti nelle prossime fasi.
 */

import Alpine from 'alpinejs';
import documentTracker from './tracking';
import './gestore-dashboard'; // Import gestore-dashboard module

// Espone Alpine globalmente così i moduli legacy possono accedervi
window.Alpine = Alpine;

// Registra i componenti Alpine.js PRIMA di Alpine.start()
Alpine.data('documentTracker', documentTracker);

document.addEventListener('DOMContentLoaded', () => {
    document.dispatchEvent(new CustomEvent('meridiana:frontend-ready'));
    // Avvia Alpine.js qui, dopo che il DOM è pronto e i componenti sono stati definiti
    Alpine.start();
});
