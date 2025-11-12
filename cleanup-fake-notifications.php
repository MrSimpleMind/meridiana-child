<?php
/**
 * Cleanup Script: Elimina le Notifiche Fake
 *
 * COME USARE:
 * 1. Copia questo file in: wp-content/themes/meridiana-child/
 * 2. Accedi a: https://nuova-formazione.local/wp-content/themes/meridiana-child/cleanup-fake-notifications.php
 * 3. Clicca il bottone "Elimina Notifiche Fake"
 * 4. Fatto! Le fake sono eliminate
 */

// Carica WordPress
require_once dirname(__FILE__) . '/../../../../wp-load.php';

// Check se siamo in admin
if (!current_user_can('manage_options')) {
    wp_die('Accesso negato. Devi essere admin.');
}

global $wpdb;

// Se viene POST da form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $notifications_table = $wpdb->prefix . 'meridiana_notifications';
    $recipients_table = $wpdb->prefix . 'meridiana_notification_recipients';

    // Controlla quante notifiche fake ci sono
    $fake_count = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$notifications_table}
         WHERE id IN (1, 2) AND title = '0'"
    );

    if ($fake_count > 0) {
        // Elimina i recipients prima (foreign key)
        $wpdb->query(
            "DELETE FROM {$recipients_table}
             WHERE notification_id IN (1, 2)"
        );

        // Elimina le notifiche fake
        $deleted = $wpdb->query(
            "DELETE FROM {$notifications_table}
             WHERE id IN (1, 2) AND title = '0'"
        );

        $message = "✅ ELIMINATO! $deleted notifiche fake e i loro recipients rimossi.";
        $success = true;
    } else {
        $message = "ℹ️ Nessuna notifica fake trovata. Tutto è già pulito!";
        $success = true;
    }
}

// Controlla stato attuale
$notifications_table = $wpdb->prefix . 'meridiana_notifications';
$fake_count = $wpdb->get_var(
    "SELECT COUNT(*) FROM {$notifications_table}
     WHERE id IN (1, 2) AND title = '0'"
);

$total_count = $wpdb->get_var(
    "SELECT COUNT(*) FROM {$notifications_table}"
);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cleanup Notifiche Fake</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .status {
            margin: 20px 0;
            padding: 15px;
            border-radius: 4px;
            font-size: 14px;
        }
        .status.info {
            background-color: #e3f2fd;
            border-left: 4px solid #2196F3;
            color: #1565c0;
        }
        .status.success {
            background-color: #e8f5e9;
            border-left: 4px solid #4CAF50;
            color: #2e7d32;
        }
        .status.warning {
            background-color: #fff3e0;
            border-left: 4px solid #FF9800;
            color: #e65100;
        }
        button {
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #b71c1c;
        }
        button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        .message {
            margin-top: 20px;
            padding: 15px;
            border-radius: 4px;
            background-color: #e8f5e9;
            border-left: 4px solid #4CAF50;
            color: #2e7d32;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f5f5f5;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧹 Cleanup Notifiche Fake</h1>

        <?php if (isset($message)): ?>
            <div class="message">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="status info">
            <strong>Stato Database:</strong><br>
            📊 Total notifiche: <strong><?php echo $total_count; ?></strong><br>
            🔴 Notifiche fake (id 1,2 con title='0'): <strong><?php echo $fake_count; ?></strong>
        </div>

        <?php if ($fake_count > 0): ?>
            <div class="status warning">
                <strong>⚠️ ATTENZIONE:</strong> Ci sono <strong><?php echo $fake_count; ?></strong> notifiche fake nel database.
                Clicca il bottone sotto per eliminarle.
            </div>

            <form method="POST">
                <button type="submit">🗑️ Elimina Notifiche Fake</button>
            </form>
        <?php else: ?>
            <div class="status success">
                <strong>✅ BENE!</strong> Non ci sono notifiche fake nel database. Tutto è pulito!
            </div>
        <?php endif; ?>

        <h2 style="margin-top: 30px;">📋 Ultime 10 Notifiche nel Database</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Created At</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $recent = $wpdb->get_results(
                    "SELECT id, title, created_at, post_type
                     FROM {$notifications_table}
                     ORDER BY created_at DESC
                     LIMIT 10"
                );

                if ($recent) {
                    foreach ($recent as $notif) {
                        $is_fake = ($notif->id == 1 || $notif->id == 2) && $notif->title === '0';
                        $row_style = $is_fake ? 'style="background-color: #ffebee;"' : '';
                        echo "<tr {$row_style}>";
                        echo "<td>" . esc_html($notif->id) . ($is_fake ? " ❌ FAKE" : "") . "</td>";
                        echo "<td>" . esc_html($notif->title) . "</td>";
                        echo "<td>" . esc_html($notif->created_at) . "</td>";
                        echo "<td>" . esc_html($notif->post_type) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>Nessuna notifica trovata</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
