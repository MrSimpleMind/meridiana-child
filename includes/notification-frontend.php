<?php
/**
 * Frontend Notification Integration
 * Carica OneSignal SDK e registra gli utenti per ricevere notifiche push
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue OneSignal SDK per gli utenti loggati (non admin)
 */
add_action('wp_enqueue_scripts', function() {
    // Carica OneSignal SDK solo per utenti loggati che non sono admin
    if (is_user_logged_in() && !current_user_can('manage_options')) {
        $app_id = get_field('meridiana_onesignal_app_id', 'option');

        if ($app_id) {
            wp_enqueue_script(
                'onesignal-sdk',
                'https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js',
                array(),
                null,
                false  // Carica in head
            );

            // Script inline per inizializzare OneSignal
            wp_add_inline_script('onesignal-sdk', '
                window.OneSignalDeferred = window.OneSignalDeferred || [];
                window.OneSignalDeferred.push(function(OneSignal) {
                    OneSignal.init({
                        appId: "' . esc_attr($app_id) . '",
                        allowLocalhostAsSecureOrigin: true,
                    });
                });
            ', 'after');
        }
    }
});

/**
 * Registra l\'utente con OneSignal quando è loggato
 */
add_action('wp_footer', function() {
    if (is_user_logged_in() && !current_user_can('manage_options')) {
        $app_id = get_field('meridiana_onesignal_app_id', 'option');

        if ($app_id) {
            $current_user = wp_get_current_user();
            ?>
            <script>
            if (typeof OneSignal !== 'undefined') {
                OneSignal.push(function() {
                    // Registra l'utente con l'ID di WordPress come external user ID
                    OneSignal.setExternalUserId('<?php echo absint($current_user->ID); ?>');

                    // Event: Notifica cliccata
                    OneSignal.on('notificationClick', function(event) {
                        var data = event.notification.data;
                        if (data && data.post_id) {
                            window.location.href = '<?php echo home_url(); ?>/?' +
                                (data.post_type ? 'post_type=' + data.post_type + '&' : '') +
                                'p=' + data.post_id;
                        }
                    });

                    // Event: Notifica mostrata
                    OneSignal.on('notificationDisplay', function(event) {
                        console.log('Notifica mostrata:', event.notification.heading);
                    });
                });
            }
            </script>
            <?php
        }
    }
});
