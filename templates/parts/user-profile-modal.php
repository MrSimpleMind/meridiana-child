<?php
/**
 * User Profile Modal
 * Modal per modificare informazioni profilo utente
 * 
 * LOGICA DI SICUREZZA:
 * - Avatar: Salva SENZA password (facile e veloce) - AUTO-SAVE AL CLICK
 * - Dati personali + cambio password: Richiede password attuale (CRITICO) - SALVA MANUAL
 * 
 * Struttura:
 * 1. Avatar Selection (in alto) - SALVA AUTOMATICO, NESSUN CLICK NEEDED
 * 2. Informazioni profilo in sola lettura (Profilo, UDO, Email)
 * 3. Dati personali modificabili (Nome, Cognome, Codice Fiscale, Telefono)
 * 4. Cambio password (sezione separata)
 * 5. Conferma password (in basso, SOLO per dati personali)
 */

$current_user = wp_get_current_user();
$user_meta = get_user_meta($current_user->ID);
$selected_avatar = meridiana_get_user_selected_avatar($current_user->ID);
?>

<div class="user-profile-modal" id="userProfileModal">
    <div class="user-profile-modal__backdrop" onclick="closeUserProfileModal()"></div>
    
    <div class="user-profile-modal__content">
        <!-- Loading overlay -->
        <div class="user-profile-modal__loading" id="profileLoading">
            <div class="spinner"></div>
        </div>
        
        <!-- Header -->
        <div class="user-profile-modal__header">
            <h2>Modifica Profilo</h2>
            <button type="button" class="btn-close-modal" onclick="closeUserProfileModal()" aria-label="Chiudi">
                <i data-lucide="x"></i>
            </button>
        </div>
        
        <!-- Body -->
        <div class="user-profile-modal__body">
            <!-- FORM AVATAR - SALVA SENZA PASSWORD (AUTO-SAVE) -->
            <form id="userAvatarForm">
                <?php wp_nonce_field('update_user_profile', 'avatar_nonce'); ?>
                
                <!-- AVATAR SELECTION - PRIMO ELEMENTO -->
                <div class="profile-avatar-section">
                    <p class="profile-form-hint" style="margin-bottom: var(--space-3); font-size: var(--font-size-xs);">
                        <i data-lucide="info" style="display: inline-block; width: 14px; height: 14px; vertical-align: middle; margin-right: 4px;"></i>
                        L'avatar viene salvato automaticamente al cambio
                    </p>
                    <?php echo meridiana_render_avatar_selector_html($current_user->ID); ?>
                </div>
            </form>
            
            <!-- FORM DATI PERSONALI - RICHIEDE PASSWORD -->
            <form id="userProfileForm">
                <?php wp_nonce_field('update_user_profile', 'profile_nonce'); ?>
                
                <!-- Store original values per comparazione -->
                <input type="hidden" id="original_first_name" value="<?php echo esc_attr($current_user->first_name); ?>">
                <input type="hidden" id="original_last_name" value="<?php echo esc_attr($current_user->last_name); ?>">
                <input type="hidden" id="original_codice_fiscale" value="<?php echo esc_attr(get_user_meta($current_user->ID, 'codice_fiscale', true)); ?>">
                <input type="hidden" id="original_user_phone" value="<?php echo esc_attr(get_user_meta($current_user->ID, 'user_phone', true)); ?>">
                
                <!-- SEZIONE INFORMAZIONI PROFILO IN SOLA LETTURA -->
                <div class="profile-section-divider">
                    <h3 class="profile-section-title">Informazioni Profilo</h3>
                </div>
                
                <!-- Profilo Professionale (read-only) -->
                <?php 
                $profilo_term_id = get_field('profilo_professionale', 'user_' . $current_user->ID);
                if ($profilo_term_id): 
                    $profilo_term = get_term($profilo_term_id);
                    $profilo_nome = $profilo_term ? $profilo_term->name : 'N/A';
                else:
                    $profilo_nome = 'Non assegnato';
                endif;
                ?>
                <div class="profile-form-group">
                    <div class="profile-form-label" style="cursor: default;">
                        <i data-lucide="briefcase" style="display: inline-block; width: 16px; height: 16px; margin-right: 6px; vertical-align: middle;"></i>
                        Profilo Professionale
                    </div>
                    <div class="profile-readonly-field">
                        <?php echo esc_html($profilo_nome); ?>
                    </div>
                </div>
                
                <!-- Unità di Offerta (read-only) -->
                <?php 
                $udo_term_id = get_field('udo_riferimento', 'user_' . $current_user->ID);
                if ($udo_term_id): 
                    $udo_term = get_term($udo_term_id);
                    $udo_nome = $udo_term ? $udo_term->name : 'N/A';
                else:
                    $udo_nome = 'Non assegnata';
                endif;
                ?>
                <div class="profile-form-group">
                    <div class="profile-form-label" style="cursor: default;">
                        <i data-lucide="building" style="display: inline-block; width: 16px; height: 16px; margin-right: 6px; vertical-align: middle;"></i>
                        Unità di Offerta
                    </div>
                    <div class="profile-readonly-field">
                        <?php echo esc_html($udo_nome); ?>
                    </div>
                </div>
                
                <!-- Email (read-only) -->
                <div class="profile-form-group">
                    <div class="profile-form-label" style="cursor: default;">
                        <i data-lucide="mail" style="display: inline-block; width: 16px; height: 16px; margin-right: 6px; vertical-align: middle;"></i>
                        Email
                    </div>
                    <div class="profile-readonly-field">
                        <?php echo esc_html($current_user->user_email); ?>
                    </div>
                </div>
                
                <!-- SEZIONE DATI MODIFICABILI -->
                <div class="profile-section-divider">
                    <h3 class="profile-section-title">Dati Personali</h3>
                </div>
                
                <!-- Nome -->
                <div class="profile-form-group">
                    <label for="first_name" class="profile-form-label">Nome</label>
                    <input 
                        type="text" 
                        id="first_name" 
                        name="first_name" 
                        class="profile-form-input"
                        autocomplete="given-name"
                        value="<?php echo esc_attr($current_user->first_name); ?>">
                </div>
                
                <!-- Cognome -->
                <div class="profile-form-group">
                    <label for="last_name" class="profile-form-label">Cognome</label>
                    <input 
                        type="text" 
                        id="last_name" 
                        name="last_name" 
                        class="profile-form-input"
                        autocomplete="family-name"
                        value="<?php echo esc_attr($current_user->last_name); ?>">
                </div>
                
                <!-- Codice Fiscale -->
                <div class="profile-form-group">
                    <label for="codice_fiscale" class="profile-form-label">Codice Fiscale</label>
                    <input 
                        type="text" 
                        id="codice_fiscale" 
                        name="codice_fiscale" 
                        class="profile-form-input"
                        autocomplete="off"
                        value="<?php echo esc_attr(get_user_meta($current_user->ID, 'codice_fiscale', true)); ?>"
                        placeholder="RSSMRA80A01H501U"
                        maxlength="16"
                        pattern="[A-Za-z0-9]{16}"
                        title="Inserisci un codice fiscale valido (16 caratteri)">
                    <span class="profile-form-hint">Formato: 16 caratteri (es. RSSMRA80A01H501U)</span>
                </div>
                
                <!-- Telefono -->
                <div class="profile-form-group">
                    <label for="user_phone" class="profile-form-label">Telefono</label>
                    <input 
                        type="tel" 
                        id="user_phone" 
                        name="user_phone" 
                        class="profile-form-input"
                        autocomplete="tel"
                        value="<?php echo esc_attr(get_user_meta($current_user->ID, 'user_phone', true)); ?>"
                        placeholder="+39 333 123 4567">
                </div>
                
                <!-- SEZIONE CAMBIO PASSWORD -->
                <div class="profile-section-divider">
                    <h3 class="profile-section-title">Cambio Password</h3>
                </div>
                
                <p class="profile-form-hint" style="margin-bottom: var(--space-4);">Lascia vuoto per non modificare la password</p>
                
                <!-- Nuova Password -->
                <div class="profile-form-group">
                    <label for="new_password" class="profile-form-label">Nuova Password</label>
                    <input 
                        type="password" 
                        id="new_password" 
                        name="new_password" 
                        class="profile-form-input"
                        autocomplete="new-password">
                </div>
                
                <!-- Conferma Nuova Password -->
                <div class="profile-form-group">
                    <label for="confirm_new_password" class="profile-form-label">Conferma Nuova Password</label>
                    <input 
                        type="password" 
                        id="confirm_new_password" 
                        name="confirm_new_password" 
                        class="profile-form-input"
                        autocomplete="new-password">
                </div>
                
                <!-- SEZIONE CONFERMA PASSWORD ATTUALE - IN BASSO (CRITICO) -->
                <div class="profile-section-divider">
                    <h3 class="profile-section-title">Conferma Identità</h3>
                </div>
                
                <div class="profile-form-group">
                    <label for="confirm_password_required" class="profile-form-label">
                        <i data-lucide="lock" style="display: inline-block; width: 16px; height: 16px; margin-right: 6px; vertical-align: middle;"></i>
                        Password Attuale
                    </label>
                    <p class="profile-form-hint" style="margin-bottom: var(--space-3);">Per motivi di sicurezza, inserisci la tua password attuale prima di salvare le modifiche ai dati personali.</p>
                    <input 
                        type="password" 
                        id="confirm_password_required" 
                        name="confirm_password_required" 
                        class="profile-form-input"
                        autocomplete="current-password"
                        placeholder="Inserisci la tua password attuale">
                </div>
                
            </form>
        </div>
        
        <!-- Footer -->
        <div class="user-profile-modal__footer">
            <button type="button" class="btn btn-secondary" onclick="closeUserProfileModal()">
                Annulla
            </button>
            <button type="button" class="btn btn-primary" onclick="saveUserProfile()">
                <i data-lucide="save"></i>
                <span>Salva modifiche</span>
            </button>
        </div>
    </div>
</div>

<script>
// Apri modal
function openUserProfileModal() {
    const modal = document.getElementById('userProfileModal');
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Re-initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
}

// Chiudi modal
function closeUserProfileModal() {
    const modal = document.getElementById('userProfileModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Salva avatar (SENZA password) - AUTO-SAVE AL CLICK
function saveAvatarOnly() {
    const avatarForm = document.getElementById('userAvatarForm');
    if (!avatarForm) return;
    
    // Seleziona avatar se cambiato
    const selectedAvatarRadio = avatarForm.querySelector('.avatar-selector__radio:checked');
    if (!selectedAvatarRadio) {
        return; // Nessun avatar selezionato
    }
    
    // Crea FormData con SOLO l'avatar
    const formData = new FormData();
    formData.append('action', 'update_user_avatar_only');
    formData.append('user_avatar', selectedAvatarRadio.value);
    formData.append('avatar_nonce', avatarForm.querySelector('input[name="avatar_nonce"]').value);
    
    // AJAX Request
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('✅ Avatar salvato con successo!');
        } else {
            console.error('❌ Errore salvataggio avatar:', data.data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Rileva cambio avatar e salva automaticamente (NESSUN CLICK NEEDED)
document.addEventListener('DOMContentLoaded', function() {
    const avatarRadios = document.querySelectorAll('.avatar-selector__radio');
    avatarRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            saveAvatarOnly();
        });
    });
});

// Controlla se i dati personali sono stati modificati
function hasPersonalDataChanges() {
    const origFirstName = document.getElementById('original_first_name').value;
    const origLastName = document.getElementById('original_last_name').value;
    const origCodiceFiscale = document.getElementById('original_codice_fiscale').value;
    const origUserPhone = document.getElementById('original_user_phone').value;
    
    const currentFirstName = document.getElementById('first_name').value;
    const currentLastName = document.getElementById('last_name').value;
    const currentCodiceFiscale = document.getElementById('codice_fiscale').value;
    const currentUserPhone = document.getElementById('user_phone').value;
    
    const newPassword = document.getElementById('new_password').value;
    const confirmNewPassword = document.getElementById('confirm_new_password').value;
    
    // Ritorna TRUE se c'è almeno una modifica
    return (
        origFirstName !== currentFirstName ||
        origLastName !== currentLastName ||
        origCodiceFiscale !== currentCodiceFiscale ||
        origUserPhone !== currentUserPhone ||
        newPassword !== '' ||
        confirmNewPassword !== ''
    );
}

// Salva profilo (DATI PERSONALI + PASSWORD OBBLIGATORIA)
function saveUserProfile() {
    const form = document.getElementById('userProfileForm');
    const loading = document.getElementById('profileLoading');
    
    if (!form) return;
    
    // Controlla se ci sono modifiche ai dati personali
    if (!hasPersonalDataChanges()) {
        alert('⚠️ Nessuna modifica ai dati personali da salvare. L\'avatar è già stato salvato automaticamente.');
        return;
    }
    
    // **VALIDAZIONE CRITICA**: Password attuale obbligatoria se ci sono modifiche ai dati
    const confirmPasswordRequired = document.getElementById('confirm_password_required').value;
    
    if (!confirmPasswordRequired || confirmPasswordRequired.trim() === '') {
        alert('⚠️ Per motivi di sicurezza, devi inserire la tua password attuale prima di salvare le modifiche ai dati personali.');
        document.getElementById('confirm_password_required').focus();
        return;
    }
    
    // Validazione dati
    const firstName = document.getElementById('first_name').value;
    const lastName = document.getElementById('last_name').value;
    const codiceFiscale = document.getElementById('codice_fiscale').value;
    const newPassword = document.getElementById('new_password').value;
    const confirmNewPassword = document.getElementById('confirm_new_password').value;
    
    if (!firstName || !lastName) {
        alert('❌ Nome e cognome sono obbligatori.');
        return;
    }
    
    // Validazione password cambio (se viene effettuato)
    if (newPassword || confirmNewPassword) {
        if (newPassword !== confirmNewPassword) {
            alert('❌ Le nuove password non corrispondono');
            return;
        }
        if (newPassword.length < 8) {
            alert('❌ La nuova password deve essere di almeno 8 caratteri');
            return;
        }
    }
    
    // Validazione Codice Fiscale se presente
    if (codiceFiscale && codiceFiscale.length > 0) {
        if (!/^[A-Za-z0-9]{16}$/.test(codiceFiscale)) {
            alert('❌ Codice Fiscale non valido. Deve contenere 16 caratteri.');
            return;
        }
    }
    
    // Mostra loading
    if (loading) loading.classList.add('active');
    
    // Prepara FormData
    const formData = new FormData(form);
    formData.append('action', 'update_user_profile');
    
    // AJAX Request
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (loading) loading.classList.remove('active');
        
        if (data.success) {
            alert('✅ Profilo aggiornato con successo!');
            closeUserProfileModal();
            location.reload();
        } else {
            alert('❌ Errore: ' + (data.data || 'Impossibile salvare le modifiche'));
        }
    })
    .catch(error => {
        if (loading) loading.classList.remove('active');
        console.error('Error:', error);
        alert('❌ Si è verificato un errore. Riprova.');
    });
}

// Close modal con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeUserProfileModal();
    }
});
</script>
