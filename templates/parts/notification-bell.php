<?php
/**
 * Notification Bell Component
 * Campanella notifiche per il header
 */

if (!is_user_logged_in()) {
    return;
}

// Genera un nonce per le richieste REST API
$nonce = wp_create_nonce('wp_rest');
?>

<div x-data="notificationBell()" x-init="init()" class="notification-bell-wrapper" style="position: relative;" data-nonce="<?php echo esc_attr($nonce); ?>">

    <!-- Bottone Campanella -->
    <button
        @click="toggle()"
        class="notification-bell-button"
        type="button"
        aria-label="Notifiche"
        style="
            position: relative;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 24px;
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        "
    >
        🔔

        <!-- Badge numerino rosso -->
        <span
            v-if="unreadCount > 0"
            class="notification-badge"
            style="
                position: absolute;
                top: -5px;
                right: -5px;
                background-color: #e74c3c;
                color: white;
                border-radius: 50%;
                width: 22px;
                height: 22px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 12px;
                font-weight: bold;
                min-width: 22px;
            "
            x-text="unreadCount > 99 ? '99+' : unreadCount"
        ></span>
    </button>

    <!-- Pop-up Notifiche -->
    <div
        x-show="isOpen"
        @click.away="close()"
        class="notification-popup"
        style="
            position: absolute;
            top: 100%;
            right: 0;
            width: 360px;
            max-height: 500px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            margin-top: 10px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        "
    >

        <!-- Header Pop-up -->
        <div style="
            padding: 16px;
            border-bottom: 1px solid #eee;
            background-color: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        ">
            <h3 style="margin: 0; font-size: 16px; font-weight: 600;">Notifiche</h3>
            <button
                @click="markAllAsRead()"
                x-show="unreadCount > 0"
                style="
                    background: none;
                    border: none;
                    color: #0066cc;
                    cursor: pointer;
                    font-size: 13px;
                    text-decoration: underline;
                    padding: 0;
                "
            >
                Segna tutti come letti
            </button>
        </div>

        <!-- Lista Notifiche -->
        <div style="
            flex: 1;
            overflow-y: auto;
        ">
            <template x-if="notifications.length === 0">
                <div style="
                    padding: 24px;
                    text-align: center;
                    color: #999;
                ">
                    <p>Nessuna notifica</p>
                </div>
            </template>

            <template x-if="notifications.length > 0">
                <div>
                    <template x-for="notification in notifications" :key="notification.notification_id">
                        <div
                            @click="handleNotificationClick(notification)"
                            style="
                                padding: 12px 16px;
                                border-bottom: 1px solid #f0f0f0;
                                cursor: pointer;
                                transition: background-color 0.2s;
                                background-color: notification.is_read ? '#fff' : '#f0f8ff';
                            "
                            @mouseenter="$el.style.backgroundColor = notification.is_read ? '#f5f5f5' : '#e6f2ff'"
                            @mouseleave="$el.style.backgroundColor = notification.is_read ? '#fff' : '#f0f8ff'"
                        >
                            <!-- Indicatore non letto -->
                            <div style="display: flex; gap: 8px;">
                                <span
                                    v-if="!notification.is_read"
                                    style="
                                        flex-shrink: 0;
                                        width: 8px;
                                        height: 8px;
                                        background-color: #0066cc;
                                        border-radius: 50%;
                                        margin-top: 5px;
                                    "
                                ></span>
                                <div style="flex: 1; min-width: 0;">
                                    <h4 style="
                                        margin: 0 0 4px 0;
                                        font-size: 13px;
                                        font-weight: 600;
                                        color: #333;
                                        overflow: hidden;
                                        text-overflow: ellipsis;
                                        white-space: nowrap;
                                    " x-text="notification.title"></h4>
                                    <p style="
                                        margin: 0 0 8px 0;
                                        font-size: 12px;
                                        color: #666;
                                        line-height: 1.4;
                                        display: -webkit-box;
                                        -webkit-line-clamp: 2;
                                        -webkit-box-orient: vertical;
                                        overflow: hidden;
                                    " x-text="notification.message"></p>
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <span style="
                                            font-size: 11px;
                                            color: #999;
                                        " x-text="getTimeAgo(notification.created_at)"></span>
                                        <button
                                            @click.stop="markAsRead(notification.notification_id)"
                                            x-show="!notification.is_read"
                                            style="
                                                background: none;
                                                border: none;
                                                color: #0066cc;
                                                cursor: pointer;
                                                font-size: 11px;
                                                text-decoration: underline;
                                                padding: 0;
                                            "
                                        >
                                            Segna come letto
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <!-- Footer Pop-up -->
        <div style="
            padding: 12px 16px;
            border-top: 1px solid #eee;
            background-color: #f8f9fa;
            text-align: center;
        ">
            <button
                @click="close()"
                style="
                    color: #999;
                    text-decoration: none;
                    font-size: 12px;
                    font-weight: 500;
                    background: none;
                    border: none;
                    cursor: pointer;
                "
            >
                Chiudi
            </button>
        </div>
    </div>

</div>
