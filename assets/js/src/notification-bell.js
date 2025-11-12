/**
 * Notification Bell Component
 * Alpine.js component per la campanella notifiche nel header
 */

// Registra il componente quando Alpine è pronto
document.addEventListener('alpine:init', () => {
    Alpine.data('notificationBell', () => ({
        isOpen: false,
        notifications: [],
        unreadCount: 0,
        isLoading: false,
        lastRefresh: null,
        nonce: null,

        async init() {
            // Ottieni il nonce dall'elemento data attribute
            this.nonce = this.$el.dataset.nonce || '';
            console.log('[NotificationBell] Nonce loaded:', this.nonce ? 'YES' : 'NO');

            // Carica il conteggio solo la prima volta (poi solo quando apri la campanella)
            if (this.nonce) {
                await this.loadUnreadCount();
            }

            // NON fare polling automatico per evitare rallentamenti
            // Le notifiche si aggiornano solo quando apri la campanella
        },

        async loadNotifications(skipCache = false) {
            if (this.isLoading) return;

            this.isLoading = true;

            try {
                // Se skipCache = true, aggiunge un timestamp per bypassare il cache
                const cacheParam = skipCache ? `&t=${Date.now()}` : '';
                const response = await fetch(`/wp-json/meridiana/v1/notifications?limit=20${cacheParam}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': this.nonce,
                    },
                    credentials: 'include',
                });

                if (!response.ok) {
                    console.error('[NotificationBell] API Response error:', response.status, response.statusText);
                    this.isLoading = false;
                    return;
                }

                const data = await response.json();
                console.log('[NotificationBell] API Response data:', data);

                if (data.success) {
                    this.notifications = data.notifications || [];
                    console.log('[NotificationBell] Notifiche caricate:', this.notifications.length, 'items');

                    // Log dettaglio di ogni notifica
                    this.notifications.forEach((notif, index) => {
                        console.log(`[NotificationBell] Notifica ${index}:`, {
                            notification_id: notif.notification_id,
                            title: notif.title,
                            is_read: notif.is_read,
                            created_at: notif.created_at,
                        });
                    });

                    this.updateUnreadCount();
                } else {
                    console.error('[NotificationBell] API error:', data);
                }
            } catch (error) {
                console.error('[NotificationBell] Errore caricamento notifiche:', error);
            } finally {
                this.isLoading = false;
                this.lastRefresh = new Date();
            }
        },

        async loadUnreadCount() {
            try {
                const response = await fetch('/wp-json/meridiana/v1/notifications/count-unread', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': this.nonce,
                    },
                    credentials: 'include',
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        this.unreadCount = data.unread_count || 0;
                        console.log('[NotificationBell] Unread count:', this.unreadCount);
                    }
                } else {
                    console.error('[NotificationBell] API Response error:', response.status, response.statusText);
                }
            } catch (error) {
                console.error('[NotificationBell] Errore caricamento numero notifiche non lette:', error);
            }
        },

        updateUnreadCount() {
            this.unreadCount = this.notifications.filter(n => !n.is_read).length;
        },

        toggle() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.loadNotifications();
            }
        },

        close() {
            this.isOpen = false;
        },

        async markAsRead(notificationId) {
            try {
                const response = await fetch('/wp-json/meridiana/v1/notifications/read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': this.nonce,
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        notification_ids: [notificationId],
                    }),
                });

                if (response.ok) {
                    // Aggiorna lo stato locale
                    const notif = this.notifications.find(n => n.notification_id === notificationId);
                    if (notif) {
                        notif.is_read = true;
                        notif.read_at = new Date().toISOString();
                    }
                    this.updateUnreadCount();
                }
            } catch (error) {
                console.error('Errore marcatura notifica:', error);
            }
        },

        async markAllAsRead() {
            const unreadIds = this.notifications
                .filter(n => !n.is_read)
                .map(n => n.notification_id);

            if (unreadIds.length === 0) {
                console.log('[NotificationBell] No unread notifications to mark');
                return;
            }

            this.isLoading = true;
            console.log('[NotificationBell] Marking as read:', unreadIds);

            try {
                const response = await fetch('/wp-json/meridiana/v1/notifications/read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': this.nonce,
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        notification_ids: unreadIds,
                    }),
                });

                console.log('[NotificationBell] Response status:', response.status, response.ok);

                if (!response.ok) {
                    console.error('[NotificationBell] Response not OK:', response.status, response.statusText);
                    this.isLoading = false;
                    return;
                }

                const data = await response.json();
                console.log('[NotificationBell] Mark read response:', data);

                if (data.success) {
                    // Aggiorna solo le notifiche che erano non-lette
                    unreadIds.forEach(id => {
                        const notif = this.notifications.find(n => n.notification_id === id);
                        if (notif) {
                            notif.is_read = true;
                            notif.read_at = new Date().toISOString();
                        }
                    });
                    this.updateUnreadCount();
                    console.log('[NotificationBell] Marked as read successfully. Unread count:', this.unreadCount);

                    // Ricarica le notifiche FRESH dal server (bypassa il cache di 2 minuti)
                    console.log('[NotificationBell] Reloading notifications to refresh UI...');
                    setTimeout(() => {
                        this.loadNotifications(true); // skipCache = true
                    }, 500);
                } else {
                    console.error('[NotificationBell] API returned success: false', data);
                }
            } catch (error) {
                console.error('[NotificationBell] Errore marcatura notifiche:', error);
            } finally {
                this.isLoading = false;
            }
        },

        /**
         * Elimina singola notifica
         * Performance: Rimuove subito dall'array locale per UX veloce
         */
        async deleteNotification(notificationId) {
            if (!confirm('Eliminare questa notifica?')) {
                return;
            }

            try {
                const response = await fetch('/wp-json/meridiana/v1/notifications/delete', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': this.nonce,
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        notification_ids: [notificationId],
                    }),
                });

                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }

                const data = await response.json();

                if (data.success) {
                    // Rimuovi subito dall'array (UX veloce)
                    this.notifications = this.notifications.filter(n => n.notification_id !== notificationId);
                    this.updateUnreadCount();
                    console.log('[NotificationBell] Notifica eliminata:', notificationId);
                } else {
                    throw new Error(data.message || 'Errore sconosciuto');
                }
            } catch (error) {
                console.error('[NotificationBell] Errore delete:', error);
                alert('Errore durante l\'eliminazione');
            }
        },

        /**
         * Elimina tutte le notifiche
         * Performance: Batch delete con una sola richiesta
         */
        async deleteAllNotifications() {
            if (this.notifications.length === 0) {
                return;
            }

            if (!confirm(`Eliminare tutte le ${this.notifications.length} notifiche?`)) {
                return;
            }

            const allIds = this.notifications.map(n => n.notification_id);
            this.isLoading = true;

            try {
                const response = await fetch('/wp-json/meridiana/v1/notifications/delete', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': this.nonce,
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        notification_ids: allIds,
                    }),
                });

                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }

                const data = await response.json();

                if (data.success) {
                    // Svuota array locale
                    this.notifications = [];
                    this.unreadCount = 0;
                    console.log('[NotificationBell] Tutte le notifiche eliminate');
                } else {
                    throw new Error(data.message || 'Errore sconosciuto');
                }
            } catch (error) {
                console.error('[NotificationBell] Errore delete all:', error);
                alert('Errore durante l\'eliminazione');
            } finally {
                this.isLoading = false;
            }
        },

        getTimeAgo(dateString) {
            if (!dateString) return 'n/a';

            // Normalizza il formato data MySQL (2025-11-10 11:58:00) per il browser
            const normalizedDate = dateString.replace(' ', 'T') + 'Z';
            const date = new Date(normalizedDate);

            // Verifica che la data sia valida
            if (isNaN(date.getTime())) {
                console.warn('[NotificationBell] Invalid date:', dateString);
                return dateString;
            }

            const now = new Date();
            const secondsAgo = Math.floor((now - date) / 1000);

            if (secondsAgo < 0) {
                return 'ora';
            } else if (secondsAgo < 60) {
                return 'ora';
            } else if (secondsAgo < 3600) {
                const minutes = Math.floor(secondsAgo / 60);
                return `${minutes}m fa`;
            } else if (secondsAgo < 86400) {
                const hours = Math.floor(secondsAgo / 3600);
                return `${hours}h fa`;
            } else {
                const days = Math.floor(secondsAgo / 86400);
                return `${days}d fa`;
            }
        },

        handleNotificationClick(notification) {
            if (notification.post_link) {
                if (!notification.is_read) {
                    this.markAsRead(notification.notification_id);
                }
                // Apri il link dopo 100ms per permettere l'aggiornamento dello stato
                setTimeout(() => {
                    window.location.href = notification.post_link;
                }, 100);
            }
        },
    }));
});
