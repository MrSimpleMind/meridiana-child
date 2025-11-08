<?php
/**
 * Top Header - Sticky Global Header
 * Header persistente visibile su tutte le pagine
 * Include: profilo utente, saluto, notifiche, dark mode toggle
 */

$current_user = wp_get_current_user();
$user_first_name = $current_user->first_name ? $current_user->first_name : $current_user->display_name;
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
        <!-- User Info -->
        <div class="top-header__user">
            <div class="top-header__avatar"
                 onclick="openUserProfileModal()"
                 style="cursor: pointer;"
                 role="button"
                 tabindex="0"
                 aria-label="Apri profilo utente">
                <?php echo meridiana_display_user_avatar(get_current_user_id(), 'small'); ?>
            </div>
            <h1 class="top-header__greeting">Ciao <?php echo esc_html($user_first_name); ?></h1>
        </div>

        <!-- Actions -->
        <div class="top-header__actions">
            <!-- Dark Mode Toggle -->
            <button class="btn-icon top-header__theme-toggle"
                    @click="darkMode = !darkMode"
                    :aria-label="darkMode ? 'Attiva tema chiaro' : 'Attiva tema scuro'"
                    :title="darkMode ? 'Tema chiaro' : 'Tema scuro'">
                <i :data-lucide="darkMode ? 'sun' : 'moon'"></i>
            </button>

            <!-- Notifications -->
            <button class="btn-icon top-header__notifications" aria-label="Notifiche">
                <i data-lucide="bell"></i>
                <?php
                // Conteggio notifiche (da implementare)
                $notifiche_count = 0; // TODO: implementare logica conteggio
                if ($notifiche_count > 0):
                ?>
                <span class="badge-count"><?php echo $notifiche_count; ?></span>
                <?php endif; ?>
            </button>
        </div>
    </div>
</header>
