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
 * Con Bell Icon customizzabile
 */
add_action('wp_enqueue_scripts', function() {
    // Carica OneSignal SDK solo per utenti loggati che non sono admin
    if (is_user_logged_in() && !current_user_can('manage_options')) {
        $app_id = get_field('meridiana_onesignal_app_id', 'option');
        $current_user = wp_get_current_user();

        error_log('[OneSignal Frontend] User: ' . $current_user->user_login . ' | App ID: ' . ($app_id ? 'CONFIGURATO (' . substr($app_id, 0, 8) . '...)' : 'NON CONFIGURATO'));

        if ($app_id) {
            wp_enqueue_script(
                'onesignal-sdk',
                'https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js',
                array(),
                null,
                false  // Carica in head
            );

            // Script inline per inizializzare OneSignal (bell icon custom)
            wp_add_inline_script('onesignal-sdk', '
                console.log("[OneSignal] SDK Caricato, inizializzando...");
                window.OneSignalDeferred = window.OneSignalDeferred || [];
                window.OneSignalDeferred.push(function(OneSignal) {
                    console.log("[OneSignal] Initiating with app_id: ' . esc_attr($app_id) . '");
                    OneSignal.init({
                        appId: "' . esc_attr($app_id) . '",
                        allowLocalhostAsSecureOrigin: true,
                        bell: {
                            enabled: false  // Disabilita la bell di default, usiamo la nostra custom
                        }
                    });
                    console.log("[OneSignal] Initialization complete");

                    // Mostra il popup quando clicco il bottone
                    window.OneSignalBellClick = function() {
                        console.log("[OneSignal] Bell clicked, showing notifications...");
                        // OneSignal v16 - usa Slidedown.show() invece di showSlidedownPrompt()
                        if (OneSignal.Slidedown) {
                            OneSignal.Slidedown.show();
                            console.log("[OneSignal] Slidedown shown");
                        } else {
                            console.warn("[OneSignal] Slidedown not available");
                        }
                    };

                    // Listener per aggiornare il badge quando arrivano notifiche
                    OneSignal.on("notificationDisplay", function(event) {
                        updateNotificationBadge();
                    });

                    // Funzione per aggiornare il badge con il conteggio
                    function updateNotificationBadge() {
                        OneSignal.getNotifications().then(function(notifications) {
                            var count = notifications ? notifications.length : 0;
                            var badge = document.getElementById("notification-count");

                            if (count > 0) {
                                badge.textContent = count > 99 ? "99+" : count;
                                badge.style.display = "flex";
                            } else {
                                badge.style.display = "none";
                            }
                        });
                    }

                    // Aggiorna il badge al caricamento
                    updateNotificationBadge();
                });
            ', 'after');

            // CSS per customizzare il bottone campanella header
            wp_add_inline_style('wp-admin', '
                /* Bottone campanella nel header */
                .top-header__notifications-bell {
                    position: relative;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #333;
                    transition: all 0.3s ease;
                }

                .top-header__notifications-bell:hover {
                    color: #007bff;
                    transform: scale(1.1);
                }

                /* Badge notifiche */
                #notification-count {
                    position: absolute;
                    top: -8px;
                    right: -8px;
                    background-color: #dc3545;
                    color: white;
                    border-radius: 50%;
                    width: 20px;
                    height: 20px;
                    font-size: 11px;
                    font-weight: bold;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
                }

                /* OneSignal slidedown styling */
                .onesignal-slidedown {
                    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
                    border-radius: 8px !important;
                    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3) !important;
                }
            ');
        }
    }
});

/**
 * Registra l\'utente con OneSignal quando è loggato
 */
add_action('wp_footer', function() {
    if (is_user_logged_in() && !current_user_can('manage_options')) {
        $app_id = get_field('meridiana_onesignal_app_id', 'option');
        $current_user = wp_get_current_user();

        error_log('[OneSignal Registrazione] User ID: ' . $current_user->ID . ' | App ID disponibile: ' . ($app_id ? 'SI' : 'NO'));

        if ($app_id) {
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
