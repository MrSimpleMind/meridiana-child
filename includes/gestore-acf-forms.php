<?php
/**
 * Gestore ACF Forms - Dashboard Gestore
 * Rendering forms + handler di salvataggio
 */

if (!defined('ABSPATH')) exit;

// ============================================
// RENDER DOCUMENTO FORM
// ============================================

function meridiana_render_documento_form($action = 'new', $post_id = null, $requested_post_type = 'protocollo') {
    $allowed_types = ['protocollo', 'modulo'];
    $post_type = in_array($requested_post_type, $allowed_types, true) ? $requested_post_type : 'protocollo';

    if ($action === 'edit' && $post_id) {
        $detected_type = get_post_type($post_id);
        if (!in_array($detected_type, $allowed_types, true)) {
            return null;
        }
        $post_type = $detected_type;
    }

    $post_data = null;
    if ($action === 'edit' && $post_id) {
        $post_data = get_post($post_id);
    }

    ob_start();
    ?>
    <form data-gestore-form="1" data-document-type="<?php echo esc_attr($post_type); ?>" data-form-mode="<?php echo esc_attr($action); ?>" @submit.prevent="submitForm">
        
        <div class="acf-form-fields">
            <!-- Post Title -->
            <div class="acf-field acf-field-text">
                <div class="acf-label">
                    <label for="post_title">Titolo <span class="required">*</span></label>
                </div>
                <div class="acf-input">
                    <input type="text" id="post_title" name="post_title" value="<?php echo esc_attr($post_data ? $post_data->post_title : ''); ?>" required />
                </div>
            </div>
        </div>

        <!-- ACF Fields MANUAL RENDER -->
        <?php meridiana_render_acf_fields_for_post($post_type, $post_id, $action); ?>

        <!-- Taxonomy Fields -->
        <?php meridiana_render_documento_taxonomy_fields_html($post_type, $post_id); ?>

        <!-- Hidden fields -->
        <input type="hidden" name="post_type" value="documenti" />
        <input type="hidden" name="cpt" value="<?php echo esc_attr($post_type); ?>" />
        <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id ?: 0); ?>" />

        <!-- Submit Button -->
        <button type="submit" class="button button-primary">
            <?php echo $action === 'new' ? 'Pubblica Documento' : 'Aggiorna Documento'; ?>
        </button>
    </form>
    <?php
    return ob_get_clean();
}

// ============================================
// RENDER ACF FIELDS - MANUAL (no acf_render_field)
// ============================================

function meridiana_render_acf_fields_for_post($post_type, $post_id = 0, $action = 'new') {
    $field_group_key = $post_type === 'protocollo' ? 'group_protocollo' : 'group_modulo';
    
    // Get field group structure
    $pdf_field_key = $post_type === 'protocollo' ? 'field_pdf_protocollo' : 'field_pdf_modulo';
    $pdf_field_name = $post_type === 'protocollo' ? 'pdf_protocollo' : 'pdf_modulo';
    
    $current_pdf_id = 0;
    $current_pdf_url = '';
    $current_pdf_name = 'Nessun file selezionato';
    
    if ($action === 'edit' && $post_id) {
        $current_pdf_id = get_field($pdf_field_key, $post_id);
        if ($current_pdf_id) {
            $current_pdf_url = wp_get_attachment_url($current_pdf_id);
            $current_pdf_name = basename($current_pdf_url);
        }
    }

    $current_riassunto = '';
    if ($action === 'edit' && $post_id) {
        $current_riassunto = get_field('field_riassunto_protocollo', $post_id) ?: '';
    }

    $current_ats = 0;
    if ($action === 'edit' && $post_id) {
        $current_ats = intval(get_field('field_pianificazione_ats', $post_id) ?: 0);
    }

    ?>
    <div class="acf-form-fields">

        <!-- 1. PDF FILE UPLOAD -->
        <div class="acf-field acf-field-file" style="margin-bottom: 20px;">
            <div class="acf-label">
                <label>PDF Documento <span class="required">*</span></label>
                <p class="description">Carica il file PDF del documento</p>
            </div>
            <div class="acf-input">
                <div class="file-upload-wrapper" style="display: flex; gap: 10px; align-items: center;">
                    <input type="hidden" name="acf[<?php echo esc_attr($pdf_field_key); ?>]" id="pdf_file_id" value="<?php echo esc_attr($current_pdf_id); ?>" />
                    <button type="button" class="button" id="btn_select_pdf" onclick="meridiana_open_media_picker('pdf_file_id', 'pdf')">
                        📁 Aggiungi File
                    </button>
                    <span id="pdf_file_name" style="font-size: 14px; color: #666;">
                        <?php echo esc_html($current_pdf_name); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- 2. RIASSUNTO (textarea) -->
        <div class="acf-field acf-field-textarea" style="margin-bottom: 20px;">
            <div class="acf-label">
                <label for="riassunto">Riassunto</label>
                <p class="description">Breve descrizione del documento</p>
            </div>
            <div class="acf-input">
                <textarea name="acf[field_riassunto_protocollo]" id="riassunto" rows="4" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"><?php echo esc_textarea($current_riassunto); ?></textarea>
            </div>
        </div>

        <!-- 3. TOGGLE ATS (true/false con UI) -->
        <div class="acf-field acf-field-true-false" style="margin-bottom: 20px;">
            <div class="acf-label">
                <label for="ats_flag">Pianificazione ATS</label>
                <p class="description">Flagga se questo documento è relativo alla pianificazione ATS</p>
            </div>
            <div class="acf-input">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <input type="hidden" name="acf[field_pianificazione_ats]" value="0" />
                    <input 
                        type="checkbox" 
                        id="ats_flag" 
                        name="acf[field_pianificazione_ats]" 
                        value="1" 
                        class="acf-switch"
                        <?php checked($current_ats, 1); ?> 
                    />
                    <label for="ats_flag" style="margin: 0; font-weight: 400; color: #666;">
                        <?php echo $current_ats ? 'SÌ, pianificazione ATS' : 'NO, documento standard'; ?>
                    </label>
                </div>
            </div>
        </div>

    </div>
    <?php
}

// ============================================
// JAVASCRIPT: Media Picker
// ============================================

function meridiana_media_picker_script() {
    ?>
    <script>
    function meridiana_open_media_picker(field_id, file_type = 'pdf') {
        if (typeof wp === 'undefined' || !wp.media) {
            alert('Media library not available');
            return;
        }

        const frame = wp.media({
            title: 'Seleziona File',
            button: { text: 'Usa questo file' },
            library: { type: file_type === 'pdf' ? 'application/pdf' : 'image' },
            multiple: false
        });

        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();
            const fileInput = document.getElementById(field_id);
            const fileNameSpan = document.getElementById(field_id.replace('_id', '_name'));

            if (fileInput) {
                fileInput.value = attachment.id;
                console.log('[MediaPicker] File selezionato:', attachment.id, attachment.filename);
            }

            if (fileNameSpan) {
                fileNameSpan.textContent = attachment.filename || attachment.title || 'File selezionato';
            }
        });

        frame.open();
    }

    // Inizializza toggle ATS con label dinamico
    document.addEventListener('DOMContentLoaded', function() {
        const atsCheckbox = document.getElementById('ats_flag');
        const atsLabel = document.querySelector('#ats_flag + label');

        if (atsCheckbox && atsLabel) {
            atsCheckbox.addEventListener('change', function() {
                atsLabel.textContent = this.checked ? 'SÌ, pianificazione ATS' : 'NO, documento standard';
            });
        }
    });
    </script>
    <?php
}

// Enqueue script nel footer
add_action('wp_footer', 'meridiana_media_picker_script');

// ============================================
// RENDER DOCUMENTO TAXONOMY FIELDS
// ============================================

function meridiana_render_documento_taxonomy_fields_html($post_type, $post_id = 0) {
    $taxonomies = [
        'unita-offerta' => [
            'label' => 'Unità di Offerta',
            'description' => 'Seleziona una o più unità di offerta pertinenti.',
            'multiple' => true,
        ],
        'profilo-professionale' => [
            'label' => 'Profilo Professionale',
            'description' => 'Indica i profili coinvolti.',
            'multiple' => true,
        ],
    ];

    if ($post_type === 'modulo') {
        $taxonomies['area-competenza'] = [
            'label' => 'Aree di Competenza',
            'description' => 'Classifica il modulo per area tematica.',
            'multiple' => true,
        ];
    }

    echo '<div class="acf-form-taxonomies">';

    foreach ($taxonomies as $taxonomy => $config) {
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);

        if (is_wp_error($terms) || empty($terms)) {
            continue;
        }

        $selected = [];
        if ($post_id) {
            $selected = wp_get_post_terms($post_id, $taxonomy, ['fields' => 'ids']);
        }

        $field_name_base = str_replace('-', '_', $taxonomy);
        $field_name = 'tax_' . $field_name_base . ($config['multiple'] ? '[]' : '');
        $multiple_attr = $config['multiple'] ? ' multiple="multiple"' : '';

        ?>
        <div class="acf-field acf-field-select acf-field-taxonomy">
            <div class="acf-label">
                <label><?php echo esc_html($config['label']); ?></label>
                <?php if (!empty($config['description'])): ?>
                    <p class="description"><?php echo esc_html($config['description']); ?></p>
                <?php endif; ?>
            </div>
            <div class="acf-input">
                <select name="<?php echo esc_attr($field_name); ?>" <?php echo $multiple_attr; ?>>
                    <option value="">-- Seleziona --</option>
                    <?php foreach ($terms as $term): 
                        $is_selected = in_array($term->term_id, $selected, true) ? 'selected' : '';
                    ?>
                        <option value="<?php echo esc_attr($term->term_id); ?>" <?php echo $is_selected; ?>>
                            <?php echo esc_html($term->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <?php
    }

    echo '</div>';
}

// ============================================
// RENDER USER FORM
// ============================================

function meridiana_render_user_form($action = 'new', $user_id = null) {
    $user_data = [
        'user_login' => '',
        'user_email' => '',
        'first_name' => '',
        'last_name' => '',
    ];
    
    if ($action === 'edit' && $user_id) {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return null;
        }
        $user_data = [
            'user_login' => $user->user_login,
            'user_email' => $user->user_email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
        ];
    }

    ob_start();
    ?>
    <form data-gestore-form="1" data-form-type="utenti" data-form-mode="<?php echo esc_attr($action); ?>" @submit.prevent="submitForm">
        
        <div class="acf-form-fields">
            <!-- Username -->
            <div class="acf-field acf-field-text">
                <div class="acf-label">
                    <label for="user_login">Username <span class="required">*</span></label>
                </div>
                <div class="acf-input">
                    <?php if ($action === 'new'): ?>
                        <input type="text" id="user_login" name="user_login" value="" required />
                    <?php else: ?>
                        <input type="text" id="user_login" name="user_login" value="<?php echo esc_attr($user_data['user_login']); ?>" readonly />
                    <?php endif; ?>
                </div>
            </div>

            <!-- Email -->
            <div class="acf-field acf-field-email">
                <div class="acf-label">
                    <label for="user_email">Email <span class="required">*</span></label>
                </div>
                <div class="acf-input">
                    <input type="email" id="user_email" name="user_email" value="<?php echo esc_attr($user_data['user_email']); ?>" required />
                </div>
            </div>

            <!-- Password (new only) -->
            <?php if ($action === 'new'): ?>
            <div class="acf-field acf-field-password">
                <div class="acf-label">
                    <label for="user_pass">Password <span class="required">*</span></label>
                </div>
                <div class="acf-input">
                    <input type="password" id="user_pass" name="user_pass" value="" required />
                </div>
            </div>
            <?php endif; ?>

            <!-- First Name -->
            <div class="acf-field acf-field-text">
                <div class="acf-label">
                    <label for="first_name">Nome</label>
                </div>
                <div class="acf-input">
                    <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($user_data['first_name']); ?>" />
                </div>
            </div>

            <!-- Last Name -->
            <div class="acf-field acf-field-text">
                <div class="acf-label">
                    <label for="last_name">Cognome</label>
                </div>
                <div class="acf-input">
                    <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($user_data['last_name']); ?>" />
                </div>
            </div>
        </div>

        <!-- Hidden fields -->
        <input type="hidden" name="post_type" value="utenti" />
        <input type="hidden" name="user_id" value="<?php echo esc_attr($user_id ?: 0); ?>" />

        <!-- Submit Button -->
        <button type="submit" class="button button-primary">
            <?php echo $action === 'new' ? 'Crea Utente' : 'Aggiorna Utente'; ?>
        </button>
    </form>
    <?php
    return ob_get_clean();
}

// ============================================
// SAVE DOCUMENTO FORM
// ============================================

function meridiana_ajax_save_documento() {
    $cpt = isset($_POST['cpt']) ? sanitize_text_field($_POST['cpt']) : 'protocollo';
    if (!in_array($cpt, ['protocollo', 'modulo'])) {
        $cpt = 'protocollo';
    }

    $title = isset($_POST['post_title']) ? sanitize_text_field($_POST['post_title']) : '';
    if (empty($title)) {
        wp_send_json_error(['message' => 'Titolo obbligatorio'], 400);
    }

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

    if ($post_id === 0) {
        $post_data = [
            'post_title' => $title,
            'post_type' => $cpt,
            'post_status' => 'publish',
            'post_content' => '',
        ];
        $post_id = wp_insert_post($post_data);
        if (is_wp_error($post_id)) {
            wp_send_json_error(['message' => 'Errore creazione documento'], 500);
        }
    } else {
        wp_update_post([
            'ID' => $post_id,
            'post_title' => $title,
        ]);
    }

    // Save ACF Fields (file upload)
    meridiana_save_documento_acf_fields($post_id, $cpt);

    // Save taxonomies
    meridiana_save_documento_taxonomies($post_id, $cpt);

    wp_send_json_success([
        'message' => 'Documento salvato con successo',
        'post_id' => $post_id,
    ]);
}

// ============================================
// SAVE DOCUMENTO ACF FIELDS (File Upload + Others)
// ============================================

function meridiana_save_documento_acf_fields($post_id, $post_type = 'protocollo') {
    // Mappa field keys per CPT
    $pdf_field_key = $post_type === 'protocollo' ? 'field_pdf_protocollo' : 'field_pdf_modulo';

    // SAVE PDF FILE
    $pdf_acf_name = 'acf[' . $pdf_field_key . ']';
    if (isset($_POST[$pdf_acf_name])) {
        $file_id = intval($_POST[$pdf_acf_name]);
        if ($file_id > 0) {
            update_field($pdf_field_key, $file_id, $post_id);
            error_log('[Gestore] PDF salvato - Post ID: ' . $post_id . ', File ID: ' . $file_id);
        }
    }

    // SAVE RIASSUNTO (textarea)
    if (isset($_POST['acf[field_riassunto_protocollo]'])) {
        $riassunto = sanitize_textarea_field($_POST['acf[field_riassunto_protocollo]']);
        if (!empty($riassunto)) {
            update_field('field_riassunto_protocollo', $riassunto, $post_id);
        }
    }

    // SAVE ATS FLAG (true_false)
    if (isset($_POST['acf[field_pianificazione_ats]'])) {
        $ats_value = intval($_POST['acf[field_pianificazione_ats]']);
        update_field('field_pianificazione_ats', $ats_value, $post_id);
    }
}

// ============================================
// SAVE DOCUMENTO TAXONOMIES
// ============================================

function meridiana_save_documento_taxonomies($post_id, $post_type = 'protocollo') {
    if (isset($_POST['tax_unita_offerta'])) {
        $terms = is_array($_POST['tax_unita_offerta']) 
            ? array_map('intval', array_filter($_POST['tax_unita_offerta']))
            : [intval($_POST['tax_unita_offerta'])];
        if (!empty($terms)) {
            wp_set_post_terms($post_id, $terms, 'unita-offerta');
        }
    }

    if (isset($_POST['tax_profilo_professionale'])) {
        $terms = is_array($_POST['tax_profilo_professionale'])
            ? array_map('intval', array_filter($_POST['tax_profilo_professionale']))
            : [intval($_POST['tax_profilo_professionale'])];
        if (!empty($terms)) {
            wp_set_post_terms($post_id, $terms, 'profilo-professionale');
        }
    }

    if ($post_type === 'modulo' && isset($_POST['tax_area_competenza'])) {
        $terms = is_array($_POST['tax_area_competenza'])
            ? array_map('intval', array_filter($_POST['tax_area_competenza']))
            : [intval($_POST['tax_area_competenza'])];
        if (!empty($terms)) {
            wp_set_post_terms($post_id, $terms, 'area-competenza');
        }
    }
}

// ============================================
// SAVE USER FORM
// ============================================

function meridiana_ajax_save_user() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Solo admin può gestire utenti'], 403);
    }

    $user_email = isset($_POST['user_email']) ? sanitize_email($_POST['user_email']) : '';
    if (empty($user_email) || !is_email($user_email)) {
        wp_send_json_error(['message' => 'Email non valida'], 400);
    }

    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

    if ($user_id === 0) {
        $user_login = isset($_POST['user_login']) ? sanitize_user($_POST['user_login']) : '';
        $user_pass = isset($_POST['user_pass']) ? $_POST['user_pass'] : '';

        if (empty($user_login)) {
            wp_send_json_error(['message' => 'Username obbligatorio'], 400);
        }
        if (empty($user_pass)) {
            wp_send_json_error(['message' => 'Password obbligatoria'], 400);
        }
        if (username_exists($user_login)) {
            wp_send_json_error(['message' => 'Username già in uso'], 400);
        }

        $user_id = wp_create_user($user_login, $user_pass, $user_email);
        if (is_wp_error($user_id)) {
            wp_send_json_error(['message' => 'Errore creazione utente'], 500);
        }

        $user = new WP_User($user_id);
        $user->set_role('subscriber');
    } else {
        $user_exists = get_user_by('id', $user_id);
        if (!$user_exists) {
            wp_send_json_error(['message' => 'Utente non trovato'], 404);
        }

        wp_update_user([
            'ID' => $user_id,
            'user_email' => $user_email,
        ]);
    }

    $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';

    if (!empty($first_name) || !empty($last_name)) {
        wp_update_user([
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
        ]);
    }

    wp_send_json_success([
        'message' => 'Utente salvato con successo',
        'user_id' => $user_id,
    ]);
}
