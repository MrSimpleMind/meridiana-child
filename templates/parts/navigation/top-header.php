<?php
/**
 * Top Header - Sticky Global Header
 * Header persistente visibile su tutte le pagine
 * Include: profilo utente, saluto, notifiche, dark mode toggle
 */

?>

<header class="top-header"
        x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
        x-init="$watch('darkMode', value => {
            localStorage.setItem('darkMode', value);
            document.documentElement.classList.toggle('dark-mode', value);
        });
        if (darkMode) document.documentElement.classList.add('dark-mode');"
        role="banner">
    <div class="top-header__container">
        <!-- Actions -->
        <div class="top-header__actions">
            <!-- Notifications Bell (Alpine.js Component) -->
            <?php get_template_part('templates/parts/notification-bell'); ?>

            <!-- Dark Mode Toggle -->
            <button class="btn-icon top-header__theme-toggle"
                    @click="darkMode = !darkMode"
                    :aria-label="darkMode ? 'Attiva tema chiaro' : 'Attiva tema scuro'"
                    :title="darkMode ? 'Tema chiaro' : 'Tema scuro'">
                <i :data-lucide="darkMode ? 'sun' : 'moon'"></i>
            </button>
        </div>
    </div>
</header>
