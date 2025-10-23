<?php
/**
 * Gestore ACF Forms - Dashboard Gestore
 * Rendering forms + handler di salvataggio
 */

if (!function_exists('meridiana_get_attachment_info')) {
    function meridiana_get_attachment_info($attachment_id) {
        $info = [
            'id' => intval($attachment_id),
            'name' => '',
            'url' => '',
            'thumbnail' => '',
        ];

        if (!$attachment_id) {
            return $info;
        }

        $attachment = get_post($attachment_id);
        if ($attachment) {
            $info['name'] = trim($attachment->post_title);
        }

        if (empty($info['name'])) {
            $file_path = get_attached_file($attachment_id);
            if ($file_path) {
                $info['name'] = wp_basename($file_path);
            }
        }

        if (empty($info['name'])) {
            $info['name'] = __('File selezionato', 'meridiana-child');
        }

        $info['url'] = wp_get_attachment_url($attachment_id) ?: '';

        if (wp_attachment_is_image($attachment_id)) {
            $thumbnail = wp_get_attachment_image_src($attachment_id, 'medium');
            if ($thumbnail && !empty($thumbnail[0])) {
                $info['thumbnail'] = $thumbnail[0];
            }
        }

        return $info;
    }
}

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

    $post_title = '';
    if ($action === 'edit' && $post_id) {
        $post_object = get_post($post_id);
        if ($post_object) {
            $post_title = $post_object->post_title;
        }
    }

    ob_start();
    ?>
    <form data-gestore-form="1" data-document-type="<?php echo esc_attr($post_type); ?>" data-form-mode="<?php echo esc_attr($action); ?>" @submit.prevent="submitForm">
        <div class="acf-form-fields">
            <div class="acf-field acf-field-text">
                <div class="acf-label">
                    <label for="post_title">Titolo <span class="required">*</span></label>
                </div>
                <div class="acf-input">
                    <input type="text" id="post_title" name="post_title" value="<?php echo esc_attr($post_title); ?>" required />
                </div>
            </div>
        </div>

        <?php meridiana_render_acf_fields_for_post($post_type, $post_id, $action); ?>

        <?php meridiana_render_documento_taxonomy_fields_html($post_type, $post_id); ?>

        <input type="hidden" name="post_type" value="documenti" />
        <input type="hidden" name="cpt" value="<?php echo esc_attr($post_type); ?>" />
        <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id ?: 0); ?>" />

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

// ============================================
// RENDER ACF FIELDS - MANUAL (no acf_render_field)
// ============================================


function meridiana_render_acf_fields_for_post($post_type, $post_id = 0, $action = 'new') {
    $is_protocollo = ($post_type === 'protocollo');
    $is_modulo = ($post_type === 'modulo');

    $pdf_field_key = $is_protocollo ? 'field_pdf_protocollo' : 'field_pdf_modulo';
    $pdf_placeholder = $is_protocollo
        ? __('Nessun file di protocollo selezionato', 'meridiana-child')
        : __('Nessun file di modulo selezionato', 'meridiana-child');

    $pdf_value = 0;
    if ($post_id) {
        $stored_pdf = get_field($pdf_field_key, $post_id);
        if ($stored_pdf) {
            $pdf_value = intval($stored_pdf);
        }
    }
    $pdf_info = meridiana_get_attachment_info($pdf_value);
    if (empty($pdf_info['name'])) {
        $pdf_info['name'] = $pdf_placeholder;
    }

    $riassunto_value = '';
    if ($is_protocollo && $post_id) {
        $riassunto_raw = get_field('riassunto', $post_id);
        if (is_string($riassunto_raw)) {
            $riassunto_value = $riassunto_raw;
        }
    }

    $ats_value = 0;
    if ($is_protocollo && $post_id) {
        $ats_raw = get_field('pianificazione_ats', $post_id);
        $ats_value = $ats_raw ? 1 : 0;
    }

    $moduli_allegati = [];
    if ($is_protocollo) {
        $stored_relationship = $post_id ? get_field('moduli_allegati', $post_id, false) : [];
        if (is_array($stored_relationship)) {
            $moduli_allegati = array_map('intval', array_filter($stored_relationship));
        }
    }

    $available_moduli = [];
    if ($is_protocollo) {
        $available_moduli = get_posts([
            'post_type'      => 'modulo',
            'post_status'    => ['publish', 'pending', 'draft'],
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'fields'         => 'ids',
        ]);
    }

    $featured_image_id = 0;
    $featured_image_info = meridiana_get_attachment_info(0);
    $featured_placeholder = __('Nessuna immagine selezionata', 'meridiana-child');
    if ($is_modulo && $post_id) {
        $featured_image_id = intval(get_post_thumbnail_id($post_id));
        if ($featured_image_id) {
            $featured_image_info = meridiana_get_attachment_info($featured_image_id);
        }
    }
    if (empty($featured_image_info['name'])) {
        $featured_image_info['name'] = $featured_placeholder;
    }

    ?>
    <div class="acf-form-fields">
        <div class="acf-field acf-field-file">
            <div class="acf-label">
                <label>
                    <?php echo $is_protocollo ? 'PDF Protocollo' : 'PDF Modulo'; ?>
                    <span class="required">*</span>
                </label>
                <p class="description">
                    <?php echo $is_protocollo
                        ? __('Carica il file PDF del protocollo (visualizzabile online)', 'meridiana-child')
                        : __('Carica il file PDF del modulo (scaricabile dal personale)', 'meridiana-child'); ?>
                </p>
            </div>
            <div class="acf-input">
                <div
                    class="media-field"
                    data-media-field
                    data-media-type="pdf"
                    data-media-placeholder="<?php echo esc_attr($pdf_placeholder); ?>"
                    data-required="1"
                >
                    <input type="hidden" name="acf[<?php echo esc_attr($pdf_field_key); ?>]" value="<?php echo esc_attr($pdf_info['id']); ?>" />
                    <button type="button" class="button media-picker"><?php esc_html_e('Seleziona PDF', 'meridiana-child'); ?></button>
                    <span class="media-file-name" data-media-file-name><?php echo esc_html($pdf_info['name']); ?></span>
                    <?php if (!empty($pdf_info['url'])): ?>
                        <a class="button button-secondary" href="<?php echo esc_url($pdf_info['url']); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Apri file corrente', 'meridiana-child'); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($is_protocollo): ?>
            <div class="acf-field acf-field-textarea">
                <div class="acf-label">
                    <label for="riassunto"><?php esc_html_e('Riassunto', 'meridiana-child'); ?></label>
                    <p class="description"><?php esc_html_e('Breve descrizione del documento', 'meridiana-child'); ?></p>
                </div>
                <div class="acf-input">
                    <textarea
                        name="acf[field_riassunto_protocollo]"
                        id="riassunto"
                        rows="4"
                        style="width: 100%;"
                    ><?php echo esc_textarea($riassunto_value); ?></textarea>
                </div>
            </div>

            <div class="acf-field acf-field-relationship">
                <div class="acf-label">
                    <label for="moduli_allegati"><?php esc_html_e('Moduli Allegati', 'meridiana-child'); ?></label>
                    <p class="description"><?php esc_html_e('Associa uno o più moduli a questo protocollo', 'meridiana-child'); ?></p>
                </div>
                <div class="acf-input">
                    <?php if (!empty($available_moduli)): ?>
                        <select id="moduli_allegati" name="acf[field_moduli_allegati][]" multiple size="6">
                            <?php foreach ($available_moduli as $modulo_id):
                                $selected = in_array($modulo_id, $moduli_allegati, true) ? 'selected' : '';
                                $label = get_the_title($modulo_id);
                                if (empty($label)) {
                                    $label = sprintf(__('Modulo #%d', 'meridiana-child'), $modulo_id);
                                }
                            ?>
                                <option value="<?php echo esc_attr($modulo_id); ?>" <?php echo $selected; ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <p class="description"><?php esc_html_e('Non ci sono moduli disponibili da associare.', 'meridiana-child'); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="acf-field acf-field-true-false">
                <div class="acf-label">
                    <label for="ats_flag"><?php esc_html_e('Pianificazione ATS', 'meridiana-child'); ?></label>
                    <p class="description"><?php esc_html_e('Flagga se questo protocollo è relativo alla pianificazione ATS', 'meridiana-child'); ?></p>
                </div>
                <div class="acf-input">
                    <div class="media-switch">
                        <input type="hidden" name="acf[field_pianificazione_ats]" value="0" />
                        <input
                            type="checkbox"
                            id="ats_flag"
                            name="acf[field_pianificazione_ats]"
                            value="1"
                            class="acf-switch"
                            <?php checked($ats_value, 1); ?>
                        />
                        <span class="ats-toggle-label" data-ats-label>
                            <?php echo $ats_value ? 'SÌ, pianificazione ATS' : 'NO, documento standard'; ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($is_modulo): ?>
            <div class="acf-field acf-field-image">
                <div class="acf-label">
                    <label><?php esc_html_e('Immagine in evidenza', 'meridiana-child'); ?></label>
                    <p class="description"><?php esc_html_e('Opzionale. Mostrata nelle anteprime dei moduli.', 'meridiana-child'); ?></p>
                </div>
                <div class="acf-input">
                    <div
                        class="media-field"
                        data-media-field
                        data-media-type="image"
                        data-media-placeholder="<?php echo esc_attr($featured_placeholder); ?>"
                    >
                        <input type="hidden" name="featured_image_id" value="<?php echo esc_attr($featured_image_id); ?>" />
                        <button type="button" class="button media-picker"><?php esc_html_e('Seleziona immagine', 'meridiana-child'); ?></button>
                        <button type="button" class="button button-secondary media-clear" <?php echo $featured_image_id ? '' : 'hidden'; ?>><?php esc_html_e('Rimuovi', 'meridiana-child'); ?></button>
                        <span class="media-file-name" data-media-file-name><?php echo esc_html($featured_image_info['name'] ?: $featured_placeholder); ?></span>
                        <div class="media-preview" data-media-preview>
                            <?php if (!empty($featured_image_info['thumbnail'])): ?>
                                <img src="<?php echo esc_url($featured_image_info['thumbnail']); ?>" alt="" />
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
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

    $acf_field_keys = [
        'stato_utente' => 'field_stato_utente',
        'link_autologin_esterno' => 'field_link_autologin',
        'codice_fiscale' => 'field_68f1eb8305594',
        'profilo_professionale' => 'field_profilo_professionale_user',
        'udo_riferimento' => 'field_udo_riferimento_user',
    ];

    $acf_values = [
        'stato_utente' => 'attivo',
        'link_autologin_esterno' => '',
        'codice_fiscale' => '',
        'profilo_professionale' => '',
        'udo_riferimento' => '',
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

        foreach ($acf_field_keys as $field_name => $field_key) {
            $raw_value = function_exists('get_field') ? get_field($field_name, 'user_' . $user_id, false) : null;
            if ($raw_value !== null && $raw_value !== '') {
                $acf_values[$field_name] = is_array($raw_value) ? '' : (string) $raw_value;
            } elseif ($field_name === 'stato_utente') {
                $acf_values[$field_name] = 'attivo';
            } else {
                $acf_values[$field_name] = '';
            }
        }
    }

    $default_stato_choices = [
        'attivo' => 'Attivo',
        'sospeso' => 'Sospeso',
        'licenziato' => 'Licenziato',
    ];
    $stato_field = function_exists('acf_get_field') ? acf_get_field('field_stato_utente') : null;
    $stato_choices = is_array($stato_field) && !empty($stato_field['choices']) ? $stato_field['choices'] : $default_stato_choices;

    $default_profilo_choices = [
        'addetto_manutenzione' => 'Addetto Manutenzione',
        'asa_oss' => 'ASA/OSS',
        'assistente_sociale' => 'Assistente Sociale',
        'coordinatore' => 'Coordinatore Unita di Offerta',
        'educatore' => 'Educatore',
        'fkt' => 'FKT',
        'impiegato_amministrativo' => 'Impiegato Amministrativo',
        'infermiere' => 'Infermiere',
        'logopedista' => 'Logopedista',
        'medico' => 'Medico',
        'psicologa' => 'Psicologa',
        'receptionista' => 'Receptionista',
        'terapista_occupazionale' => 'Terapista Occupazionale',
        'volontari' => 'Volontari',
    ];
    $profilo_field = function_exists('acf_get_field') ? acf_get_field('field_profilo_professionale_user') : null;
    $profilo_choices = is_array($profilo_field) && !empty($profilo_field['choices']) ? $profilo_field['choices'] : $default_profilo_choices;

    $default_udo_choices = [
        'ambulatori' => 'Ambulatori',
        'ap' => 'AP',
        'cdi' => 'CDI',
        'cure_domiciliari' => 'Cure Domiciliari',
        'hospice' => 'Hospice',
        'paese' => 'Paese',
        'r20' => 'R20',
        'rsa' => 'RSA',
        'rsa_aperta' => 'RSA Aperta',
        'rsd' => 'RSD',
    ];
    $udo_field = function_exists('acf_get_field') ? acf_get_field('field_udo_riferimento_user') : null;
    $udo_choices = is_array($udo_field) && !empty($udo_field['choices']) ? $udo_field['choices'] : $default_udo_choices;

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

            <!-- Stato Utente -->
            <div class="acf-field acf-field-radio">
                <div class="acf-label">
                    <label>Stato Utente <span class="required">*</span></label>
                    <p class="description">Stato corrente dell'utente nella cooperativa</p>
                </div>
                <div class="acf-input acf-input-radio">
                    <?php foreach ($stato_choices as $value => $label): 
                        $input_id = 'stato_' . sanitize_html_class($value);
                        $checked = $acf_values['stato_utente'] === $value ? 'checked' : '';
                    ?>
                    <label for="<?php echo esc_attr($input_id); ?>" class="acf-radio-label">
                        <input type="radio" id="<?php echo esc_attr($input_id); ?>" name="user_acf[field_stato_utente]" value="<?php echo esc_attr($value); ?>" <?php echo $checked; ?> required />
                        <span><?php echo esc_html($label); ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Link Autologin -->
            <div class="acf-field acf-field-url">
                <div class="acf-label">
                    <label for="link_autologin_esterno">Link Autologin Piattaforma Esterna</label>
                    <p class="description">URL per l'autologin alla piattaforma formazione certificata esterna</p>
                </div>
                <div class="acf-input">
                    <input type="url" id="link_autologin_esterno" name="user_acf[field_link_autologin]" value="<?php echo esc_attr($acf_values['link_autologin_esterno']); ?>" placeholder="https://esempio.it/percorso" />
                </div>
            </div>

            <!-- Codice Fiscale -->
            <div class="acf-field acf-field-text">
                <div class="acf-label">
                    <label for="codice_fiscale">Codice Fiscale</label>
                </div>
                <div class="acf-input">
                    <input type="text" id="codice_fiscale" name="user_acf[field_68f1eb8305594]" value="<?php echo esc_attr($acf_values['codice_fiscale']); ?>" />
                </div>
            </div>

            <!-- Profilo Professionale -->
            <div class="acf-field acf-field-select">
                <div class="acf-label">
                    <label for="profilo_professionale">Profilo Professionale</label>
                    <p class="description">Seleziona il profilo professionale dell'utente</p>
                </div>
                <div class="acf-input">
                    <select id="profilo_professionale" name="user_acf[field_profilo_professionale_user]">
                        <option value=""><?php echo esc_html('-- Seleziona --'); ?></option>
                        <?php foreach ($profilo_choices as $value => $label): 
                            $selected = $acf_values['profilo_professionale'] === $value ? 'selected' : '';
                        ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php echo $selected; ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Unita di Offerta -->
            <div class="acf-field acf-field-select">
                <div class="acf-label">
                    <label for="udo_riferimento">Unita di Offerta di Riferimento</label>
                    <p class="description">Seleziona l'unit&agrave; di offerta di riferimento dell'utente</p>
                </div>
                <div class="acf-input">
                    <select id="udo_riferimento" name="user_acf[field_udo_riferimento_user]">
                        <option value=""><?php echo esc_html('-- Seleziona --'); ?></option>
                        <?php foreach ($udo_choices as $value => $label): 
                            $selected = $acf_values['udo_riferimento'] === $value ? 'selected' : '';
                        ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php echo $selected; ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
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

    if ($cpt === 'modulo') {
        $thumbnail_id = isset($_POST['featured_image_id']) ? intval($_POST['featured_image_id']) : 0;
        if ($thumbnail_id > 0) {
            set_post_thumbnail($post_id, $thumbnail_id);
        } else {
            delete_post_thumbnail($post_id);
        }
    }

    wp_send_json_success([
        'message' => 'Documento salvato con successo',
        'post_id' => $post_id,
    ]);
}

// ============================================
// SAVE DOCUMENTO ACF FIELDS (File Upload + Others)
// ============================================


function meridiana_save_documento_acf_fields($post_id, $post_type = 'protocollo') {
    if (!function_exists('update_field')) {
        return;
    }

    $pdf_field_key = $post_type === 'protocollo' ? 'field_pdf_protocollo' : 'field_pdf_modulo';

    $pdf_value = 0;
    if (isset($_POST['acf'][$pdf_field_key])) {
        $pdf_raw = $_POST['acf'][$pdf_field_key];
        if (is_array($pdf_raw)) {
            $pdf_raw = end($pdf_raw);
        }
        $pdf_value = intval($pdf_raw);
    }

    if ($pdf_value > 0) {
        update_field($pdf_field_key, $pdf_value, $post_id);
    } elseif (isset($_POST['acf'][$pdf_field_key]) && function_exists('delete_field')) {
        delete_field($pdf_field_key, $post_id);
    }

    if ($post_type === 'protocollo') {
        $riassunto_value = '';
        if (isset($_POST['acf']['field_riassunto_protocollo'])) {
            $riassunto_value = sanitize_textarea_field($_POST['acf']['field_riassunto_protocollo']);
        }
        update_field('field_riassunto_protocollo', $riassunto_value, $post_id);

        $ats_raw = $_POST['acf']['field_pianificazione_ats'] ?? 0;
        if (is_array($ats_raw)) {
            $ats_raw = end($ats_raw);
        }
        $ats_value = intval($ats_raw) === 1 ? 1 : 0;
        update_field('field_pianificazione_ats', $ats_value, $post_id);

        $relationship_raw = $_POST['acf']['field_moduli_allegati'] ?? [];
        if (!is_array($relationship_raw)) {
            $relationship_raw = [$relationship_raw];
        }
        $relationship_ids = array_map('intval', array_filter($relationship_raw));
        update_field('field_moduli_allegati', $relationship_ids, $post_id);
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
        wp_send_json_error(['message' => 'Solo admin puo gestire utenti'], 403);
    }

    $user_email = isset($_POST['user_email']) ? sanitize_email($_POST['user_email']) : '';
    if (empty($user_email) || !is_email($user_email)) {
        wp_send_json_error(['message' => 'Email non valida'], 400);
    }

    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

    if ($user_id === 0) {
        $user_login = isset($_POST['user_login']) ? sanitize_user($_POST['user_login']) : '';
        $user_pass = isset($_POST['user_pass']) ? (string) $_POST['user_pass'] : '';

        if (empty($user_login)) {
            wp_send_json_error(['message' => 'Username obbligatorio'], 400);
        }
        if (empty($user_pass)) {
            wp_send_json_error(['message' => 'Password obbligatoria'], 400);
        }
        if (username_exists($user_login)) {
            wp_send_json_error(['message' => 'Username gia in uso'], 400);
        }
        if (email_exists($user_email)) {
            wp_send_json_error(['message' => 'Email già presente nel database'], 400);
        }

        $created_user_id = wp_create_user($user_login, $user_pass, $user_email);
        if (is_wp_error($created_user_id)) {
            if ($created_user_id->get_error_code() === 'existing_user_email') {
                wp_send_json_error(['message' => 'Email già presente nel database'], 400);
            }
            wp_send_json_error(['message' => 'Errore creazione utente'], 500);
        }

        $user_id = $created_user_id;
        $created_user = new WP_User($user_id);
        $created_user->set_role('subscriber');
    } else {
        $existing_user = get_user_by('id', $user_id);
        if (!$existing_user) {
            wp_send_json_error(['message' => 'Utente non trovato'], 404);
        }

        $email_owner = email_exists($user_email);
        if ($email_owner && intval($email_owner) !== $user_id) {
            wp_send_json_error(['message' => 'Email già presente nel database'], 400);
        }

        wp_update_user([
            'ID' => $user_id,
            'user_email' => $user_email,
        ]);
    }

    $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';

    if (!empty($first_name) || !empty($last_name)) {
        $display_name = trim($first_name . ' ' . $last_name);
        if ($display_name === '' && !empty($user_email)) {
            $display_name = $user_email;
        }

        wp_update_user([
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => $display_name,
        ]);
    }

    $acf_input = isset($_POST['user_acf']) && is_array($_POST['user_acf']) ? $_POST['user_acf'] : [];

    $default_stato_choices = [
        'attivo' => 'Attivo',
        'sospeso' => 'Sospeso',
        'licenziato' => 'Licenziato',
    ];
    $stato_field = function_exists('acf_get_field') ? acf_get_field('field_stato_utente') : null;
    $stato_choices = is_array($stato_field) && !empty($stato_field['choices']) ? $stato_field['choices'] : $default_stato_choices;
    $stato_value = isset($acf_input['field_stato_utente']) ? sanitize_text_field($acf_input['field_stato_utente']) : '';
    if (empty($stato_value) || !isset($stato_choices[$stato_value])) {
        wp_send_json_error(['message' => 'Seleziona uno stato utente valido'], 400);
    }

    $link_value = '';
    if (!empty($acf_input['field_link_autologin'])) {
        $candidate = esc_url_raw(trim($acf_input['field_link_autologin']));
        if (!empty($candidate) && filter_var($candidate, FILTER_VALIDATE_URL)) {
            $link_value = $candidate;
        } else {
            wp_send_json_error(['message' => 'Inserisci un URL autologin valido'], 400);
        }
    }

    $codice_value = isset($acf_input['field_68f1eb8305594']) ? sanitize_text_field($acf_input['field_68f1eb8305594']) : '';

    $default_profilo_choices = [
        'addetto_manutenzione' => 'Addetto Manutenzione',
        'asa_oss' => 'ASA/OSS',
        'assistente_sociale' => 'Assistente Sociale',
        'coordinatore' => 'Coordinatore Unita di Offerta',
        'educatore' => 'Educatore',
        'fkt' => 'FKT',
        'impiegato_amministrativo' => 'Impiegato Amministrativo',
        'infermiere' => 'Infermiere',
        'logopedista' => 'Logopedista',
        'medico' => 'Medico',
        'psicologa' => 'Psicologa',
        'receptionista' => 'Receptionista',
        'terapista_occupazionale' => 'Terapista Occupazionale',
        'volontari' => 'Volontari',
    ];
    $profilo_field = function_exists('acf_get_field') ? acf_get_field('field_profilo_professionale_user') : null;
    $profilo_choices = is_array($profilo_field) && !empty($profilo_field['choices']) ? $profilo_field['choices'] : $default_profilo_choices;
    $profilo_value = isset($acf_input['field_profilo_professionale_user']) ? sanitize_text_field($acf_input['field_profilo_professionale_user']) : '';
    if ($profilo_value !== '' && !isset($profilo_choices[$profilo_value])) {
        $profilo_value = '';
    }

    $default_udo_choices = [
        'ambulatori' => 'Ambulatori',
        'ap' => 'AP',
        'cdi' => 'CDI',
        'cure_domiciliari' => 'Cure Domiciliari',
        'hospice' => 'Hospice',
        'paese' => 'Paese',
        'r20' => 'R20',
        'rsa' => 'RSA',
        'rsa_aperta' => 'RSA Aperta',
        'rsd' => 'RSD',
    ];
    $udo_field = function_exists('acf_get_field') ? acf_get_field('field_udo_riferimento_user') : null;
    $udo_choices = is_array($udo_field) && !empty($udo_field['choices']) ? $udo_field['choices'] : $default_udo_choices;
    $udo_value = isset($acf_input['field_udo_riferimento_user']) ? sanitize_text_field($acf_input['field_udo_riferimento_user']) : '';
    if ($udo_value !== '' && !isset($udo_choices[$udo_value])) {
        $udo_value = '';
    }

    $user_context = 'user_' . $user_id;

    if (function_exists('update_field')) {
        update_field('field_stato_utente', $stato_value, $user_context);

        if ($link_value !== '') {
            update_field('field_link_autologin', $link_value, $user_context);
        } elseif (function_exists('delete_field')) {
            delete_field('field_link_autologin', $user_context);
        } else {
            delete_user_meta($user_id, 'link_autologin_esterno');
        }

        if ($codice_value !== '') {
            update_field('field_68f1eb8305594', $codice_value, $user_context);
        } elseif (function_exists('delete_field')) {
            delete_field('field_68f1eb8305594', $user_context);
        } else {
            delete_user_meta($user_id, 'codice_fiscale');
        }

        if ($profilo_value !== '') {
            update_field('field_profilo_professionale_user', $profilo_value, $user_context);
        } elseif (function_exists('delete_field')) {
            delete_field('field_profilo_professionale_user', $user_context);
        } else {
            delete_user_meta($user_id, 'profilo_professionale');
        }

        if ($udo_value !== '') {
            update_field('field_udo_riferimento_user', $udo_value, $user_context);
        } elseif (function_exists('delete_field')) {
            delete_field('field_udo_riferimento_user', $user_context);
        } else {
            delete_user_meta($user_id, 'udo_riferimento');
        }
    } else {
        update_user_meta($user_id, 'stato_utente', $stato_value);

        if ($link_value !== '') {
            update_user_meta($user_id, 'link_autologin_esterno', $link_value);
        } else {
            delete_user_meta($user_id, 'link_autologin_esterno');
        }

        if ($codice_value !== '') {
            update_user_meta($user_id, 'codice_fiscale', $codice_value);
        } else {
            delete_user_meta($user_id, 'codice_fiscale');
        }

        if ($profilo_value !== '') {
            update_user_meta($user_id, 'profilo_professionale', $profilo_value);
        } else {
            delete_user_meta($user_id, 'profilo_professionale');
        }

        if ($udo_value !== '') {
            update_user_meta($user_id, 'udo_riferimento', $udo_value);
        } else {
            delete_user_meta($user_id, 'udo_riferimento');
        }
    }

    wp_send_json_success([
        'message' => 'Utente salvato con successo',
        'user_id' => $user_id,
    ]);
}

