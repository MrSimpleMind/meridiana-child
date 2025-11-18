<?php
/**
 * Template Name: Offline Page
 * Description: Offline page shown when connection is unavailable
 */

// Don't require login for offline page
// allow_offline_page_for_all_users();

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php bloginfo('name'); ?> - Offline</title>
    <?php wp_head(); ?>
</head>
<body <?php body_class('offline-page'); ?>>

<div class="offline-container">
    <!-- Animated illustration -->
    <div class="offline-illustration">
        <svg viewBox="0 0 300 280" xmlns="http://www.w3.org/2000/svg" class="offline-svg">
            <!-- WiFi signal with X -->
            <g id="wifi-signal">
                <!-- WiFi waves -->
                <path d="M 150 80 Q 100 140 50 200" fill="none" stroke="#ab1120" stroke-width="3" opacity="0.3"/>
                <path d="M 150 70 Q 90 150 30 230" fill="none" stroke="#ab1120" stroke-width="3" opacity="0.2"/>

                <!-- WiFi dot -->
                <circle cx="150" cy="80" r="8" fill="#ab1120"/>

                <!-- Big X across signal -->
                <line x1="80" y1="120" x2="200" y2="240" stroke="#c92a3b" stroke-width="8" stroke-linecap="round"/>
                <line x1="200" y1="120" x2="80" y2="240" stroke="#c92a3b" stroke-width="8" stroke-linecap="round"/>
            </g>

            <!-- Decorative elements -->
            <circle cx="40" cy="60" r="6" fill="#10B981" opacity="0.5"/>
            <circle cx="260" cy="100" r="8" fill="#10B981" opacity="0.4"/>
            <rect x="220" y="200" width="60" height="50" fill="#f0f0f0" opacity="0.3" rx="4"/>
        </svg>
    </div>

    <!-- Content -->
    <div class="offline-content">
        <!-- Logo -->
        <div class="offline-logo">
            <svg viewBox="0 0 200 80" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <style>
                        .logo-text { font-family: Arial, sans-serif; font-size: 32px; font-weight: 900; fill: #ab1120; letter-spacing: 2px; }
                    </style>
                </defs>
                <text x="100" y="55" text-anchor="middle" class="logo-text">MERIDIANA</text>
            </svg>
        </div>

        <!-- Heading -->
        <h1 class="offline-title">
            Nessuna connessione internet
        </h1>

        <!-- Message -->
        <p class="offline-message">
            Sembra che la tua connessione internet sia momentaneamente non disponibile.
        </p>

        <p class="offline-message secondary">
            Controlla la tua connessione WiFi o i dati mobili e riprova.
        </p>

        <!-- Suggestions -->
        <div class="offline-suggestions">
            <div class="suggestion-item">
                <span class="suggestion-icon">📶</span>
                <p class="suggestion-text">Verifica il tuo WiFi o i dati mobili</p>
            </div>
            <div class="suggestion-item">
                <span class="suggestion-icon">🔄</span>
                <p class="suggestion-text">Ricaricare la pagina</p>
            </div>
            <div class="suggestion-item">
                <span class="suggestion-icon">⏱️</span>
                <p class="suggestion-text">Aspetta qualche momento e riprova</p>
            </div>
        </div>

        <!-- Action buttons -->
        <div class="offline-actions">
            <button class="offline-btn primary" onclick="location.reload()">
                Ricaricare
            </button>
            <button class="offline-btn secondary" onclick="goHome()">
                Torna alla home
            </button>
        </div>

        <!-- Footer text -->
        <p class="offline-footer">
            Se il problema persiste, contatta il supporto tecnico.
        </p>
    </div>
</div>

<?php wp_footer(); ?>

<script>
function goHome() {
    window.location.href = '<?php echo esc_url(home_url('/')); ?>';
}

// Check if connection is restored
window.addEventListener('online', function() {
    // Reload the page when connection is restored
    console.log('Connessione ripristinata');
    setTimeout(function() {
        location.reload();
    }, 1000);
});

// Add pulsing animation to SVG
document.addEventListener('DOMContentLoaded', function() {
    const svg = document.querySelector('.offline-svg');
    if (svg) {
        // The animation is defined in CSS, but we can add additional interactivity here if needed
    }
});
</script>

</body>
</html>
