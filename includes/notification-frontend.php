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

            // Script inline per inizializzare OneSignal SOLO per Push Notifications (non per bell) - v16
            wp_add_inline_script('onesignal-sdk', '
                console.log("[OneSignal] SDK Caricato, inizializzando SOLO per push notifications...");
                window.OneSignalDeferred = window.OneSignalDeferred || [];
                window.OneSignalDeferred.push(function(OneSignal) {
                    try {
                        console.log("[OneSignal] Initiating with app_id: ' . esc_attr($app_id) . '");
                        OneSignal.init({
                            appId: "' . esc_attr($app_id) . '",
                            allowLocalhostAsSecureOrigin: true,
                            serviceWorkerParam: { scope: "/" },
                            bell: {
                                enabled: false  // Disabilita completamente la bell di OneSignal - usiamo il sistema interno
                            }
                        }).catch(e => console.warn("[OneSignal] Init error:", e));

                        console.log("[OneSignal] Initialization complete - Push notifications ready");
                        console.log("[OneSignal] Bell is disabled - using custom internal notification system");
                    } catch(err) {
                        console.error("[OneSignal] Fatal error:", err);
                    }
                });
            ', 'after');

            // Aggiorna il badge con il sistema interno (REST API)
            wp_add_inline_script('onesignal-sdk', '
                function updateNotificationBadge() {
                    var endpoint = "' . rest_url('meridiana/v1/notifications/count-unread') . '";
                    var nonce = "' . wp_create_nonce('wp_rest') . '";

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
                                if (count > 0) {
                                    badge.textContent = count > 99 ? "99+" : count;
                                    badge.style.display = "flex";
                                } else {
                                    badge.style.display = "none";
                                }
                            }
                        })
                        .catch(e => console.warn("[Notification Bell] Badge update failed:", e));
                }

                // Aggiorna il badge al caricamento
                if (document.readyState === "loading") {
                    document.addEventListener("DOMContentLoaded", updateNotificationBadge);
                } else {
                    updateNotificationBadge();
                }

                // Aggiorna il badge ogni 10 secondi
                setInterval(updateNotificationBadge, 10000);
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

/**
 * Definisce window.OneSignalBellClick per mostrare le notifiche
 * Funziona sia in locale che su staging
 * Usa sempre la REST API locale (non OneSignal SDK)
 */
add_action('wp_footer', function() {
    if (is_user_logged_in() && !current_user_can('manage_options')) {
        ?>
        <script>
        // Fallback per assicurare che la funzione esista sempre
        window.OneSignalBellClick = function() {
            console.log('[Notification Bell] Bell clicked, loading notifications...');

            var nonce = '<?php echo esc_attr(wp_create_nonce('wp_rest')); ?>';
            var endpoint = '<?php echo esc_url(rest_url('meridiana/v1/notifications')); ?>';

            fetch(endpoint, {
                headers: {
                    'X-WP-Nonce': nonce
                }
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.notifications) {
                        console.log('[Notification Bell] Found ' + data.notifications.length + ' notifications');
                        displayNotificationsModal(data.notifications);
                    } else {
                        console.warn('[Notification Bell] No notifications found');
                    }
                })
                .catch(e => console.error('[Notification Bell] Error loading notifications:', e));
        };

        function displayNotificationsModal(notifications) {
            // Crea una modale semplice per mostrare le notifiche
            var modal = document.createElement('div');
            modal.className = 'notification-modal';
            modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999;';

            var content = document.createElement('div');
            content.style.cssText = 'background: white; border-radius: 8px; padding: 20px; max-width: 500px; max-height: 70vh; overflow-y: auto; box-shadow: 0 4px 12px rgba(0,0,0,0.3);';

            var header = document.createElement('h2');
            header.textContent = 'Notifiche (' + notifications.length + ')';
            header.style.cssText = 'margin: 0 0 15px 0; font-size: 18px;';
            content.appendChild(header);

            if (notifications.length === 0) {
                var empty = document.createElement('p');
                empty.textContent = 'Nessuna notifica';
                empty.style.cssText = 'color: #999;';
                content.appendChild(empty);
            } else {
                var notifIds = [];
                notifications.forEach(notif => {
                    var item = document.createElement('div');
                    item.style.cssText = 'border-left: 4px solid #007bff; padding: 10px; margin-bottom: 10px; background: #f9f9f9;';

                    var title = document.createElement('h4');
                    title.textContent = notif.title;
                    title.style.cssText = 'margin: 0 0 5px 0; font-size: 14px;';
                    item.appendChild(title);

                    var message = document.createElement('p');
                    message.textContent = notif.message;
                    message.style.cssText = 'margin: 0 0 5px 0; font-size: 12px; color: #666;';
                    item.appendChild(message);

                    var date = document.createElement('small');
                    date.textContent = new Date(notif.created_at).toLocaleString();
                    date.style.cssText = 'color: #999;';
                    item.appendChild(date);

                    content.appendChild(item);

                    if (!notif.is_read) {
                        notifIds.push(notif.notification_id);
                    }
                });

                // Marca come lette
                if (notifIds.length > 0) {
                    var readEndpoint = '<?php echo esc_url(rest_url('meridiana/v1/notifications/read')); ?>';
                    fetch(readEndpoint, {
                        method: 'POST',
                        headers: {
                            'X-WP-Nonce': '<?php echo esc_attr(wp_create_nonce('wp_rest')); ?>',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ notification_ids: notifIds })
                    }).then(() => {
                        console.log('[Notification Bell] Marked ' + notifIds.length + ' as read');
                        updateNotificationBadgeLocal();
                    });
                }
            }

            var closeBtn = document.createElement('button');
            closeBtn.textContent = 'Chiudi';
            closeBtn.style.cssText = 'margin-top: 15px; padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;';
            closeBtn.onclick = function() {
                modal.remove();
            };
            content.appendChild(closeBtn);

            modal.appendChild(content);
            modal.onclick = function(e) {
                if (e.target === modal) modal.remove();
            };

            document.body.appendChild(modal);
        }
        </script>
        <?php
    }
});
