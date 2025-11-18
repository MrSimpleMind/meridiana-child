<?php
/**
 * Template Name: Login Page
 * Description: Modern split-layout login page with Meridiana branding
 */

// Redirect if already logged in
if (is_user_logged_in()) {
    wp_redirect(home_url('/'));
    exit;
}

get_header();
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php bloginfo('name'); ?> - Accedi</title>
    <?php wp_head(); ?>
</head>
<body <?php body_class('login-page-modern'); ?>>

<div class="login-wrapper">
    <!-- Left Section: Branding + Illustration -->
    <div class="login-left">
        <div class="login-branding">
            <svg class="logo-large" viewBox="0 0 200 80" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <style>
                        .logo-text { font-family: Arial, sans-serif; font-size: 32px; font-weight: 900; fill: white; letter-spacing: 2px; }
                    </style>
                </defs>
                <rect width="200" height="80" fill="#ab1120" rx="8"/>
                <text x="100" y="55" text-anchor="middle" class="logo-text">MERIDIANA</text>
            </svg>
            <p class="tagline">Alleanza per la cura delle fragilità</p>
        </div>

        <!-- Illustration -->
        <div class="login-illustration">
            <svg viewBox="0 0 300 400" xmlns="http://www.w3.org/2000/svg">
                <!-- Background gradient circle -->
                <circle cx="150" cy="150" r="120" fill="#ffffff" opacity="0.1"/>

                <!-- Stylized person at desk -->
                <g id="person">
                    <!-- Chair -->
                    <rect x="80" y="180" width="30" height="80" rx="5" fill="#e8e8ff" opacity="0.8"/>
                    <circle cx="95" cy="260" r="8" fill="#d0d0ff"/>

                    <!-- Desk -->
                    <rect x="50" y="150" width="90" height="15" rx="3" fill="#f0f0f0" opacity="0.6"/>

                    <!-- Body -->
                    <ellipse cx="95" cy="120" rx="18" ry="25" fill="#ab1120"/>

                    <!-- Head -->
                    <circle cx="95" cy="85" r="15" fill="#d4a574"/>

                    <!-- Arms -->
                    <line x1="77" y1="115" x2="50" y2="125" stroke="#d4a574" stroke-width="6" stroke-linecap="round"/>
                    <line x1="113" y1="115" x2="140" y2="125" stroke="#d4a574" stroke-width="6" stroke-linecap="round"/>
                </g>

                <!-- Decorative elements -->
                <circle cx="200" cy="100" r="8" fill="#10B981" opacity="0.6"/>
                <circle cx="50" cy="250" r="12" fill="#10B981" opacity="0.4"/>
                <rect x="170" y="200" width="50" height="60" fill="#f0f0f0" opacity="0.4" rx="4"/>
            </svg>
        </div>
    </div>

    <!-- Right Section: Login Form -->
    <div class="login-right">
        <div class="login-card">
            <h1 class="login-title">Benvenuto!</h1>
            <p class="login-subtitle">Accedi con le tue credenziali</p>

            <?php
            // Display error messages if any
            if (isset($_GET['login']) && $_GET['login'] === 'failed') {
                echo '<div class="login-error">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <circle cx="10" cy="10" r="9" stroke="currentColor" stroke-width="1"/>
                        <text x="50%" y="50%" font-size="14" text-anchor="middle">!</text>
                    </svg>
                    <span>Credenziali non valide. Riprova.</span>
                </div>';
            }
            ?>

            <!-- Login Form -->
            <form method="post" action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" class="login-form">
                <!-- Username/Email -->
                <div class="form-group">
                    <label for="user_login" class="form-label">Email o Username</label>
                    <input
                        type="text"
                        name="log"
                        id="user_login"
                        class="form-input"
                        placeholder="Inserisci email o username"
                        required
                        autocomplete="username"
                    />
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="user_pass" class="form-label">Password</label>
                    <div class="password-wrapper">
                        <input
                            type="password"
                            name="pwd"
                            id="user_pass"
                            class="form-input password-input"
                            placeholder="Inserisci password"
                            required
                            autocomplete="current-password"
                        />
                        <button type="button" class="password-toggle-btn" aria-label="Mostra/Nascondi Password">👁️</button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="form-group checkbox-group">
                    <input
                        type="checkbox"
                        name="rememberme"
                        id="rememberme"
                        class="form-checkbox"
                        value="forever"
                    />
                    <label for="rememberme" class="checkbox-label">Ricordami</label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="form-button">Accedi</button>

                <!-- Footer Links -->
                <div class="login-footer">
                    <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="footer-link">
                        Password dimenticata?
                    </a>
                    <span class="footer-divider">•</span>
                    <a href="<?php echo esc_url(home_url()); ?>" class="footer-link">
                        Torna al sito
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php wp_footer(); ?>
<script>
// Password toggle
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.querySelector('.password-toggle-btn');
    const passwordInput = document.querySelector('.password-input');

    if (toggleBtn && passwordInput) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = '👁️';
            }
            passwordInput.focus();
        });
    }
});
</script>

</body>
</html>
