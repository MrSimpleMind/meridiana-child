/**
 * Password Toggle Button
 * Aggiunge un bottoncino per mostrare/nascondere la password
 */

document.addEventListener('DOMContentLoaded', function() {
    const passwordField = document.getElementById('user_pass');

    if (!passwordField) return;

    // Crea il bottoncino toggle
    const toggleButton = document.createElement('button');
    toggleButton.type = 'button';
    toggleButton.className = 'password-toggle-btn';
    toggleButton.innerHTML = '👁️';
    toggleButton.setAttribute('aria-label', 'Mostra/Nascondi Password');
    toggleButton.setAttribute('title', 'Mostra/Nascondi Password');

    // Aggiungi il bottoncino dopo il campo password
    const passwordWrapper = passwordField.closest('.user-pass-wrap');
    if (passwordWrapper) {
        passwordWrapper.style.position = 'relative';
        passwordWrapper.appendChild(toggleButton);
    }

    // Toggle visibility on button click
    toggleButton.addEventListener('click', function(e) {
        e.preventDefault();

        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleButton.classList.add('active');
            toggleButton.innerHTML = '🙈';
        } else {
            passwordField.type = 'password';
            toggleButton.classList.remove('active');
            toggleButton.innerHTML = '👁️';
        }

        // Focus back to input
        passwordField.focus();
    });

    // Also hide WordPress's built-in toggle if it exists
    const wpToggle = passwordWrapper?.querySelector('.wp-pwd-toggle');
    if (wpToggle) {
        wpToggle.style.display = 'none';
    }
});
