<?php
/**
 * User Profile Modal
 * Modal per modificare informazioni profilo utente
 */

$current_user = wp_get_current_user();
$user_meta = get_user_meta($current_user->ID);
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
            <form id="userProfileForm">
                <?php wp_nonce_field('update_user_profile', 'profile_nonce'); ?>
                
                <!-- Avatar Upload -->
                <div class="user-avatar-upload">
                    <div class="user-avatar-large" id="avatarPreview">
                        <?php 
                        $avatar_url = get_avatar_url($current_user->ID, array('size' => 200));
                        if ($avatar_url): 
                        ?>
                        <img src="<?php echo esc_url($avatar_url); ?>" alt="Avatar">
                        <?php else: ?>
                        <i data-lucide="user"></i>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn-upload-avatar" onclick="document.getElementById('avatarUpload').click()">
                        <i data-lucide="upload"></i>
                        <span>Carica foto profilo</span>
                    </button>
                    <input type="file" id="avatarUpload" accept="image/*" style="display:none;" onchange="handleAvatarUpload(event)">
                    <p class="profile-form-hint">Formati supportati: JPG, PNG. Max 2MB</p>
                </div>
                
                <!-- Nome -->
                <div class="profile-form-group">
                    <label for="first_name" class="profile-form-label">Nome</label>
                    <input 
                        type="text" 
                        id="first_name" 
                        name="first_name" 
                        class="profile-form-input"
                        value="<?php echo esc_attr($current_user->first_name); ?>"
                        required>
                </div>
                
                <!-- Cognome -->
                <div class="profile-form-group">
                    <label for="last_name" class="profile-form-label">Cognome</label>
                    <input 
                        type="text" 
                        id="last_name" 
                        name="last_name" 
                        class="profile-form-input"
                        value="<?php echo esc_attr($current_user->last_name); ?>"
                        required>
                </div>
                
                <!-- Email (read-only) -->
                <div class="profile-form-group">
                    <label for="user_email" class="profile-form-label">Email</label>
                    <div class="profile-readonly-field">
                        <?php echo esc_html($current_user->user_email); ?>
                    </div>
                    <span class="profile-form-hint">L'email non può essere modificata da qui. Contatta l'amministratore.</span>
                </div>
                
                <!-- Telefono -->
                <div class="profile-form-group">
                    <label for="user_phone" class="profile-form-label">Telefono</label>
                    <input 
                        type="tel" 
                        id="user_phone" 
                        name="user_phone" 
                        class="profile-form-input"
                        value="<?php echo esc_attr(get_user_meta($current_user->ID, 'user_phone', true)); ?>"
                        placeholder="+39 333 123 4567">
                </div>
                
                <!-- Unità di Offerta (read-only) -->
                <?php 
                $udo = get_user_meta($current_user->ID, 'unita_di_offerta', true);
                if ($udo): 
                ?>
                <div class="profile-form-group">
                    <label class="profile-form-label">Unità di Offerta</label>
                    <div class="profile-readonly-field">
                        <?php echo esc_html($udo); ?>
                    </div>
                    <span class="profile-form-hint">Assegnata dall'amministratore</span>
                </div>
                <?php endif; ?>
                
                <!-- Profilo Professionale (read-only) -->
                <?php 
                $profilo = get_user_meta($current_user->ID, 'profilo_professionale', true);
                if ($profilo): 
                ?>
                <div class="profile-form-group">
                    <label class="profile-form-label">Profilo Professionale</label>
                    <div class="profile-readonly-field">
                        <?php echo esc_html($profilo); ?>
                    </div>
                    <span class="profile-form-hint">Assegnato dall'amministratore</span>
                </div>
                <?php endif; ?>
                
                <!-- Cambio Password Section -->
                <div class="profile-form-group" style="margin-top: var(--space-6); padding-top: var(--space-6); border-top: 1px solid var(--color-border);">
                    <label class="profile-form-label">Cambia Password</label>
                    <p class="profile-form-hint" style="margin-bottom: var(--space-3);">Lascia vuoto per non modificare la password</p>
                    
                    <div style="margin-bottom: var(--space-4);">
                        <label for="current_password" class="profile-form-label" style="font-weight: normal; font-size: var(--font-size-sm);">Password attuale</label>
                        <input 
                            type="password" 
                            id="current_password" 
                            name="current_password" 
                            class="profile-form-input"
                            autocomplete="current-password">
                    </div>
                    
                    <div style="margin-bottom: var(--space-4);">
                        <label for="new_password" class="profile-form-label" style="font-weight: normal; font-size: var(--font-size-sm);">Nuova password</label>
                        <input 
                            type="password" 
                            id="new_password" 
                            name="new_password" 
                            class="profile-form-input"
                            autocomplete="new-password">
                    </div>
                    
                    <div>
                        <label for="confirm_password" class="profile-form-label" style="font-weight: normal; font-size: var(--font-size-sm);">Conferma nuova password</label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            class="profile-form-input"
                            autocomplete="new-password">
                    </div>
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

// Handle avatar upload preview
function handleAvatarUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    // Validazione
    if (!file.type.match('image.*')) {
        alert('Per favore seleziona un\'immagine valida (JPG, PNG)');
        return;
    }
    
    if (file.size > 2 * 1024 * 1024) {
        alert('L\'immagine è troppo grande. Max 2MB');
        return;
    }
    
    // Preview
    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById('avatarPreview');
        if (preview) {
            preview.innerHTML = '<img src="' + e.target.result + '" alt="Avatar">';
        }
    };
    reader.readAsDataURL(file);
}

// Salva profilo
function saveUserProfile() {
    const form = document.getElementById('userProfileForm');
    const loading = document.getElementById('profileLoading');
    
    if (!form) return;
    
    // Validazione password
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const currentPassword = document.getElementById('current_password').value;
    
    if (newPassword || confirmPassword) {
        if (!currentPassword) {
            alert('Inserisci la password attuale per cambiarla');
            return;
        }
        if (newPassword !== confirmPassword) {
            alert('Le password non corrispondono');
            return;
        }
        if (newPassword.length < 8) {
            alert('La nuova password deve essere di almeno 8 caratteri');
            return;
        }
    }
    
    // Mostra loading
    if (loading) loading.classList.add('active');
    
    // Prepara FormData
    const formData = new FormData(form);
    formData.append('action', 'update_user_profile');
    
    // Upload avatar se presente
    const avatarInput = document.getElementById('avatarUpload');
    if (avatarInput && avatarInput.files[0]) {
        formData.append('avatar', avatarInput.files[0]);
    }
    
    // AJAX Request
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (loading) loading.classList.remove('active');
        
        if (data.success) {
            alert('Profilo aggiornato con successo!');
            closeUserProfileModal();
            // Ricarica pagina per aggiornare UI
            location.reload();
        } else {
            alert('Errore: ' + (data.data || 'Impossibile salvare le modifiche'));
        }
    })
    .catch(error => {
        if (loading) loading.classList.remove('active');
        console.error('Error:', error);
        alert('Si è verificato un errore. Riprova.');
    });
}

// Close modal con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeUserProfileModal();
    }
});
</script>
