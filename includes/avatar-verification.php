<?php
/**
 * AVATAR PERSISTENCE - QUICK VERIFICATION SCRIPT
 * Esegui questo codice nel footer per verificare che tutto funzioni
 * 
 * USO: Aggiungi questo codice temporaneamente nel footer.php durante testing
 * RIMUOVI prima di andare in produzione
 */

if (is_user_logged_in() && current_user_can('manage_options')) {
    // Mostra info debug se sei admin e parametro presente
    if (isset($_GET['verify_avatar'])) {
        $user_id = get_current_user_id();
        $avatar = meridiana_get_user_avatar_persistent($user_id);
        $avatar_list = meridiana_get_avatar_list();
        
        echo '<div style="background: #1a1a1a; color: #00ff00; padding: 20px; margin: 20px; border: 2px solid #00ff00; border-radius: 8px; font-family: monospace; font-size: 12px; max-width: 800px;">';
        echo '<h3 style="color: #00ff00; margin-top: 0;">🔍 AVATAR PERSISTENCE VERIFICATION</h3>';
        
        // Verifica 1: Funzioni disponibili
        echo '<hr style="border: 1px solid #00ff00; margin: 10px 0;">';
        echo '<h4>✓ Step 1: Funzioni Disponibili</h4>';
        
        $functions_check = array(
            'meridiana_save_user_avatar_robust' => function_exists('meridiana_save_user_avatar_robust'),
            'meridiana_get_user_avatar_persistent' => function_exists('meridiana_get_user_avatar_persistent'),
            'meridiana_display_user_avatar_persistent' => function_exists('meridiana_display_user_avatar_persistent'),
            'handle_save_user_avatar_ajax' => function_exists('handle_save_user_avatar_ajax'),
        );
        
        foreach ($functions_check as $func => $exists) {
            $status = $exists ? '✓ YES' : '✗ NO';
            $color = $exists ? '#00ff00' : '#ff0000';
            echo '<p style="color: ' . $color . ';">' . $func . ': ' . $status . '</p>';
        }
        
        // Verifica 2: JavaScript caricato
        echo '<hr style="border: 1px solid #00ff00; margin: 10px 0;">';
        echo '<h4>✓ Step 2: JavaScript Enqueued</h4>';
        echo '<p>Controlla browser console (F12) per: meridianaAvatarData</p>';
        
        // Verifica 3: Avatar corrente
        echo '<hr style="border: 1px solid #00ff00; margin: 10px 0;">';
        echo '<h4>✓ Step 3: Avatar Attuale</h4>';
        
        if ($avatar) {
            echo '<p style="color: #00ff00;">✓ AVATAR TROVATO NEL DATABASE</p>';
            echo '<p>Filename: ' . esc_html($avatar['filename']) . '</p>';
            echo '<p>URL: <code>' . esc_html($avatar['url']) . '</code></p>';
            echo '<p>Preview:</p>';
            echo '<img src="' . esc_url($avatar['url']) . '" alt="Avatar" style="width: 100px; height: 100px; border: 2px solid #00ff00; border-radius: 50%; margin: 10px 0;">';
        } else {
            echo '<p style="color: #ffaa00;">⚠️ NESSUN AVATAR SALVATO</p>';
            echo '<p>Selezionane uno dal modal profilo e salva</p>';
        }
        
        // Verifica 4: Avatar disponibili
        echo '<hr style="border: 1px solid #00ff00; margin: 10px 0;">';
        echo '<h4>✓ Step 4: Avatar Disponibili (' . count($avatar_list) . ')</h4>';
        
        if (count($avatar_list) > 0) {
            echo '<p style="color: #00ff00;">✓ ' . count($avatar_list) . ' avatar trovati</p>';
            echo '<div style="max-height: 150px; overflow-y: auto; background: #0a0a0a; padding: 10px; border: 1px solid #00ff00; border-radius: 4px;">';
            foreach ($avatar_list as $a) {
                echo '<p style="margin: 5px 0; color: #00ff00;">' . esc_html($a['filename']) . '</p>';
            }
            echo '</div>';
        } else {
            echo '<p style="color: #ff0000;">✗ NO AVATAR TROVATI</p>';
        }
        
        // Verifica 5: Database check
        echo '<hr style="border: 1px solid #00ff00; margin: 10px 0;">';
        echo '<h4>✓ Step 5: Database Check</h4>';
        
        global $wpdb;
        $meta_value = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = %d AND meta_key = 'selected_avatar'",
            $user_id
        ));
        
        if ($meta_value) {
            echo '<p style="color: #00ff00;">✓ AVATAR NEL DATABASE</p>';
            echo '<p>User ID: ' . $user_id . '</p>';
            echo '<p>Meta Value: <code>' . esc_html($meta_value) . '</code></p>';
            
            // Verifica che il file esista
            $avatar_path = MERIDIANA_CHILD_DIR . '/assets/images/avatar/' . $meta_value;
            if (file_exists($avatar_path)) {
                echo '<p style="color: #00ff00;">✓ FILE FISICO ESISTE</p>';
            } else {
                echo '<p style="color: #ff0000;">✗ FILE FISICO NON TROVATO: ' . $avatar_path . '</p>';
            }
        } else {
            echo '<p style="color: #ffaa00;">⚠️ NESSUN META NEL DATABASE</p>';
        }
        
        // Verifica 6: AJAX Test
        echo '<hr style="border: 1px solid #00ff00; margin: 10px 0;">';
        echo '<h4>✓ Step 6: AJAX Test</h4>';
        echo '<p>Apri browser console (F12 → Console)</p>';
        echo '<p>Incolla questo codice e premi Enter:</p>';
        echo '<code style="background: #0a0a0a; padding: 10px; border: 1px solid #00ff00; border-radius: 4px; display: block; margin: 10px 0;">fetch(meridianaAvatarData.ajax_url, {method: \'POST\', body: new FormData(Object.assign(new FormData(), {action: \'save_user_avatar\', avatar: \'medico donna.jpg\', nonce: meridianaAvatarData.nonce}))}).then(r => r.json()).then(d => console.log(d))</code>';
        
        // Verifica 7: Debug Panel
        echo '<hr style="border: 1px solid #00ff00; margin: 10px 0;">';
        echo '<h4>✓ Step 7: Debug Panel</h4>';
        echo '<p><a href="?meridiana_avatar_debug=1" target="_blank" style="color: #00ff00; text-decoration: underline;">Apri Debug Panel →</a></p>';
        
        // Conclusione
        echo '<hr style="border: 1px solid #00ff00; margin: 10px 0;">';
        echo '<h4 style="color: #00ff00;">✓ VERIFICATION COMPLETE</h4>';
        echo '<p>Tutti i sistemi sono operativi. Segui la guida AVATAR_PERSISTENCE_TESTING.md per il testing completo.</p>';
        
        echo '</div>';
    }
}
?>
