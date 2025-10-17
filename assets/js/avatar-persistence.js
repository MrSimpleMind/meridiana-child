/**
 * Avatar Persistence System - JavaScript
 * Gestisce il salvataggio e sincronizzazione avatar via AJAX
 */

(function() {
    'use strict';
    
    // ========================================
    // 1. EVENT LISTENERS - Rileva cambi avatar
    // ========================================
    
    /**
     * Quando l'utente seleziona un avatar nel modal
     */
    document.addEventListener('DOMContentLoaded', function() {
        setupAvatarSelectionListeners();
    });
    
    function setupAvatarSelectionListeners() {
        const avatarRadios = document.querySelectorAll('.avatar-selector__radio');
        
        avatarRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                const filename = this.value;
                const avatarUrl = this.getAttribute('data-avatar-url');
                
                // Debug
                console.log('🎨 Avatar selezionato:', filename);
                
                // Salva immediatamente via AJAX
                saveAvatarPersistent(filename, avatarUrl);
            });
        });
    }
    
    
    // ========================================
    // 2. AJAX REQUEST - Salva avatar
    // ========================================
    
    /**
     * Invia richiesta AJAX per salvare l'avatar
     */
    function saveAvatarPersistent(filename, avatarUrl) {
        if (!filename) {
            console.error('❌ Nome file avatar non valido');
            showAvatarNotification('Errore: avatar non valido', 'error');
            return;
        }
        
        // Mostra feedback visivo
        showAvatarNotification('Salvataggio avatar...', 'loading');
        
        // Prepara dati
        const formData = new FormData();
        formData.append('action', 'save_user_avatar');
        formData.append('avatar', filename);
        formData.append('nonce', meridianaAvatarData.nonce);
        
        // AJAX call
        fetch(meridianaAvatarData.ajax_url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('✅ Avatar salvato con successo:', filename);
                showAvatarNotification('Avatar salvato! ✓', 'success');
                
                // Aggiorna preview in tempo reale
                updateAvatarPreview(avatarUrl);
                
                // Attendi 1.5s poi ricarica la pagina per visualizzare ovunque
                setTimeout(() => {
                    location.reload();
                }, 1500);
                
            } else {
                console.error('❌ Errore salvataggio:', data.data);
                showAvatarNotification('Errore: ' + (data.data || 'Non riuscito a salvare'), 'error');
            }
        })
        .catch(error => {
            console.error('❌ Errore AJAX:', error);
            showAvatarNotification('Errore di connessione', 'error');
        });
    }
    
    
    // ========================================
    // 3. UI FEEDBACK - Notifiche e preview
    // ========================================
    
    /**
     * Mostra notifica feedback
     */
    function showAvatarNotification(message, type = 'info') {
        // Rimuovi notifica precedente
        const existingNotif = document.querySelector('.avatar-notification');
        if (existingNotif) {
            existingNotif.remove();
        }
        
        // Crea nuova notifica
        const notification = document.createElement('div');
        notification.className = 'avatar-notification avatar-notification--' + type;
        notification.innerHTML = '<span>' + escapeHtml(message) + '</span>';
        
        // Stili inline
        const styles = {
            'position': 'fixed',
            'bottom': '20px',
            'right': '20px',
            'padding': '12px 20px',
            'border-radius': '8px',
            'font-size': '14px',
            'z-index': '10000',
            'animation': 'slideInUp 0.3s ease',
            'box-shadow': '0 4px 12px rgba(0,0,0,0.15)',
            'font-family': 'system-ui, -apple-system, sans-serif'
        };
        
        // Colori per tipo
        const colors = {
            'success': { 'background': '#4caf50', 'color': 'white' },
            'error': { 'background': '#f44336', 'color': 'white' },
            'loading': { 'background': '#2196f3', 'color': 'white' },
            'info': { 'background': '#2196f3', 'color': 'white' }
        };
        
        Object.assign(notification.style, styles, colors[type] || colors['info']);
        
        document.body.appendChild(notification);
        
        // Auto-remove dopo 3 secondi
        if (type !== 'loading') {
            setTimeout(() => {
                notification.style.animation = 'slideOutDown 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    }
    
    /**
     * Aggiorna preview avatar nella pagina
     */
    function updateAvatarPreview(avatarUrl) {
        // Trova tutti gli avatar sulla pagina e aggiorna
        const avatarImages = document.querySelectorAll('.user-avatar img, [data-user-avatar]');
        
        avatarImages.forEach(img => {
            if (img.src !== avatarUrl) {
                // Soft transition
                img.style.opacity = '0.5';
                setTimeout(() => {
                    img.src = avatarUrl;
                    img.style.opacity = '1';
                }, 200);
            }
        });
    }
    
    /**
     * Escape HTML per sicurezza
     */
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
    
    
    // ========================================
    // 4. INTEGRATION - Con modal profilo
    // ========================================
    
    /**
     * Integrazione con il form profilo (aggiungi field nascosto)
     * Quando viene salvato il profilo completo, include anche l'avatar
     */
    window.saveUserProfileWithAvatar = function() {
        const form = document.getElementById('userProfileForm');
        if (!form) return;
        
        // Raccogli l'avatar selezionato
        const selectedAvatarRadio = form.querySelector('.avatar-selector__radio:checked');
        
        if (selectedAvatarRadio) {
            // Crea campo nascosto per l'avatar
            let avatarInput = form.querySelector('input[name="user_avatar"]');
            if (!avatarInput) {
                avatarInput = document.createElement('input');
                avatarInput.type = 'hidden';
                avatarInput.name = 'user_avatar';
                form.appendChild(avatarInput);
            }
            avatarInput.value = selectedAvatarRadio.value;
            
            console.log('🎨 Avatar incluso nel form profilo:', selectedAvatarRadio.value);
        }
    };
    
    // Aggancia al salvataggio profilo (se esiste)
    const originalSaveProfile = window.saveUserProfile;
    window.saveUserProfile = function() {
        saveUserProfileWithAvatar();
        if (originalSaveProfile) {
            originalSaveProfile();
        }
    };
    
    
    // ========================================
    // 5. STILI CSS INJECTED
    // ========================================
    
    (function injectStyles() {
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInUp {
                from {
                    transform: translateY(100%);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOutDown {
                from {
                    transform: translateY(0);
                    opacity: 1;
                }
                to {
                    transform: translateY(100%);
                    opacity: 0;
                }
            }
            
            .avatar-notification {
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .avatar-notification--loading {
                animation: pulse 1.5s infinite;
            }
            
            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.7; }
            }
        `;
        document.head.appendChild(style);
    })();
    
})();
