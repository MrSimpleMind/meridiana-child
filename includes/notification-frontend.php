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
    // e SOLO su domini di produzione (non .local, non localhost)
    if (is_user_logged_in() && !current_user_can('manage_options')) {
        $app_id = get_field('meridiana_onesignal_app_id', 'option');
        $host = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field($_SERVER['HTTP_HOST']) : '';

        // Check: se è un dominio locale, disabilita OneSignal
        $is_production = !preg_match('/(\.local|localhost|127\.0\.0\.1)/', $host);

        $current_user = wp_get_current_user();

        error_log('[OneSignal Frontend] User: ' . $current_user->user_login . ' | Host: ' . $host . ' | Production: ' . ($is_production ? 'SI' : 'NO') . ' | App ID: ' . ($app_id ? 'CONFIGURATO' : 'NO'));

        // OneSignal richiede un dominio di produzione - in locale/dev skippiamo
        if ($app_id && $is_production) {
            wp_enqueue_script(
                'onesignal-sdk',
                'https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js',
                array(),
                null,
                false  // Carica in head
            );

            // Script inline per inizializzare OneSignal (bell icon custom) - v16
            wp_add_inline_script('onesignal-sdk', '
                console.log("[OneSignal] SDK Caricato, inizializzando...");
                window.OneSignalDeferred = window.OneSignalDeferred || [];
                window.OneSignalDeferred.push(function(OneSignal) {
                    try {
                        console.log("[OneSignal] Initiating with app_id: ' . esc_attr($app_id) . '");
                        OneSignal.init({
                            appId: "' . esc_attr($app_id) . '",
                            allowLocalhostAsSecureOrigin: true,
                            serviceWorkerParam: { scope: "/" },
                            bell: {
                                enabled: false
                            }
                        }).catch(e => console.warn("[OneSignal] Init error:", e));

                        console.log("[OneSignal] Initialization complete");

                        // Funzione per aggiornare il badge con il conteggio
                        function updateNotificationBadge() {
                            fetch("' . rest_url('meridiana/v1/notifications/count-unread') . '")
                                .then(r => r.json())
                                .then(data => {
                                    var badge = document.getElementById("notification-count");
                                    if (badge) {
                                        var count = data.count || 0;
                                        if (count > 0) {
                                            badge.textContent = count > 99 ? "99+" : count;
                                            badge.style.display = "flex";
                                        } else {
                                            badge.style.display = "none";
                                        }
                                    }
                                })
                                .catch(e => console.warn("[OneSignal] Badge update failed:", e));
                        }

                        // Aggiorna il badge al caricamento
                        updateNotificationBadge();

                        // Aggiorna il badge ogni 10 secondi
                        setInterval(updateNotificationBadge, 10000);
                    } catch(err) {
                        console.error("[OneSignal] Fatal error:", err);
                    }
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
        } else {
            // In locale o senza HTTPS: carica il sistema di notifiche locale senza OneSignal
            wp_enqueue_script(
                'notification-bell-local',
                null,
                array(),
                null,
                true
            );
            wp_add_inline_script('notification-bell-local', '
                // Sistema notifiche locale (senza OneSignal)
                console.log("[Notification Bell] Loading local notification system (no OneSignal)");

                function updateNotificationBadgeLocal() {
                    // Endpoint per contare le notifiche non lette
                    var endpoint = "' . esc_url(rest_url('meridiana/v1/notifications/count-unread')) . '";
                    var nonce = "' . esc_attr(wp_create_nonce('wp_rest')) . '";

                    fetch(endpoint, {
                        headers: {
                            "X-WP-Nonce": nonce
                        }
                    })
                        .then(r => r.json())
                        .then(data => {
                            var badge = document.getElementById("notification-count");
                            if (badge) {
                                var count = data.unread_count || data.count || 0;
                                console.log("[Notification Bell] Unread count:", count);
                                if (count > 0) {
                                    badge.textContent = count > 99 ? "99+" : count;
                                    badge.style.display = "flex";
                                } else {
                                    badge.style.display = "none";
                                }
                            }
                        })
                        .catch(e => console.warn("[Notification Bell] Fetch failed:", e));
                }

                // Aggiorna al caricamento
                if (document.readyState === "loading") {
                    document.addEventListener("DOMContentLoaded", updateNotificationBadgeLocal);
                } else {
                    updateNotificationBadgeLocal();
                }

                // Aggiorna ogni 30 secondi
                setInterval(updateNotificationBadgeLocal, 30000);
            ');
        }
    }
});

/**
 * Registra l'utente con OneSignal quando è loggato (OneSignal SDK v16)
 * Solo su domini di produzione!
 */
add_action('wp_footer', function() {
    if (is_user_logged_in() && !current_user_can('manage_options')) {
        $app_id = get_field('meridiana_onesignal_app_id', 'option');
        $host = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field($_SERVER['HTTP_HOST']) : '';
        $is_production = !preg_match('/(\.local|localhost|127\.0\.0\.1)/', $host);
        $current_user = wp_get_current_user();

        error_log('[OneSignal Registrazione] User ID: ' . $current_user->ID . ' | Host: ' . $host . ' | Production: ' . ($is_production ? 'SI' : 'NO') . ' | App ID: ' . ($app_id ? 'SI' : 'NO'));

        // OneSignal richiede un dominio di produzione - in locale skippiamo completamente
        if ($app_id && $is_production) {
            ?>
            <script>
            window.OneSignalDeferred = window.OneSignalDeferred || [];
            window.OneSignalDeferred.push(function(OneSignal) {
                try {
                    // Registra l'utente con l'ID di WordPress come external user ID
                    if (typeof OneSignal !== 'undefined' && OneSignal.setExternalUserId) {
                        OneSignal.setExternalUserId('<?php echo absint($current_user->ID); ?>');
                        console.log('[OneSignal] User registered with ID: <?php echo absint($current_user->ID); ?>');
                    } else {
                        console.warn('[OneSignal] setExternalUserId not available');
                    }
                } catch(err) {
                    console.warn('[OneSignal] User registration error:', err);
                }
            });
            </script>
            <?php
        }
    }
});
