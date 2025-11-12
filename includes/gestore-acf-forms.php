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

                    <div class="media-preview" data-media-preview></div>

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

                        <?php

                        $moduli_count = count($available_moduli);

                        $moduli_select_size = max(4, min(8, $moduli_count));

                        ?>

                        <select id="moduli_allegati" name="acf[field_moduli_allegati][]" class="select2-enable" multiple size="<?php echo esc_attr($moduli_select_size); ?>">

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

                    <div class="checkbox-field">

                        <input type="hidden" name="acf[field_pianificazione_ats]" value="0" />

                        <label class="checkbox-inline">

                            <input

                                type="checkbox"

                                id="ats_flag"

                                name="acf[field_pianificazione_ats]"

                                value="1"

                                <?php checked($ats_value, 1); ?>

                            />

                            <span data-ats-label><?php echo $ats_value ? esc_html__('Sì, pianificazione ATS', 'meridiana-child') : esc_html__('NO, documento standard', 'meridiana-child'); ?></span>

                        </label>

                    </div>

                </div>

            </div>

        <?php endif; ?>

        <!-- NOTIFICHE MANUALI - PROTOCOLLI E MODULI -->
        <?php if ($is_protocollo || $is_modulo): ?>

            <div class="acf-field acf-field-true-false">

                <div class="acf-label">

                    <label for="send_notification"><?php esc_html_e('Invia Notifiche', 'meridiana-child'); ?></label>

                    <p class="description"><?php esc_html_e('Abilita questa opzione per inviare notifiche agli utenti quando il documento viene pubblicato', 'meridiana-child'); ?></p>

                </div>

                <div class="acf-input">

                    <div class="checkbox-field">

                        <input type="hidden" name="send_notification" value="0" />

                        <label class="checkbox-inline">

                            <input

                                type="checkbox"

                                id="send_notification"

                                name="send_notification"

                                value="1"

                                onchange="document.querySelector('.notification-segmentation-fields').style.display = this.checked ? 'block' : 'none'"

                            />

                            <span><?php esc_html_e('Sì, abilita notifiche', 'meridiana-child'); ?></span>

                        </label>

                    </div>

                </div>

            </div>

            <!-- CAMPI DI SEGMENTAZIONE (mostrati se notifiche abilitate) -->
            <div class="notification-segmentation-fields" style="display: none;">
                <?php

                // Prepara i dati di tassonomia già presenti nel form
                $post_taxonomy_data = [];

                if (($is_protocollo || $is_modulo) && $post_id) {
                    $profiles = wp_get_post_terms($post_id, 'profilo-professionale', ['fields' => 'ids']);
                    $udos = wp_get_post_terms($post_id, 'unita-offerta', ['fields' => 'ids']);

                    $post_taxonomy_data = [
                        'profiles' => $profiles ?: [],
                        'udos' => $udos ?: [],
                    ];
                }

                meridiana_render_notification_segmentation_fields($post_taxonomy_data);

                ?>
            </div>

        <?php endif; ?>

    </div>

    <?php

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

    // ============================================
    // Recupera corsi iscritti (LearnDash Nativo)
    // ============================================
    $tutti_corsi_value = false;
    $corsi_assegnati = [];

    if ($action === 'edit' && $user_id) {
        // Recupera corsi iscritti da LearnDash nativo
        $corsi_assegnati = meridiana_get_user_enrolled_course_ids($user_id);
        // Se l'utente è iscritto a TUTTI i corsi disponibili, mostra il checkbox spuntato
        $all_course_ids = array_map(function($c) { return $c->ID; }, meridiana_get_all_courses());
        $tutti_corsi_value = count($corsi_assegnati) === count($all_course_ids) && !empty($corsi_assegnati);
    }

    // ============================================
    // Recupera TUTTI i corsi disponibili
    // ============================================
    $all_courses = get_posts([
        'post_type'      => 'sfwd-courses',
        'numberposts'    => -1,
        'orderby'        => 'post_title',
        'order'          => 'ASC',
        'post_status'    => 'publish',
    ]);

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

            <!-- Tutti i Corsi -->
            <div class="acf-field acf-field-true-false">
                <div class="acf-label">
                    <label for="tutti_corsi">Tutti i Corsi</label>
                    <p class="description">Spunta per enrollare l'utente in TUTTI i corsi disponibili</p>
                </div>
                <div class="acf-input">
                    <input type="checkbox" id="tutti_corsi" name="user_acf[field_tutti_corsi]" value="1" <?php echo $tutti_corsi_value ? 'checked' : ''; ?> />
                </div>
            </div>

            <!-- Corsi Specifici - Multi Select -->
            <div class="acf-field acf-field-post-object" id="corsi-specifici-field" style="<?php echo $tutti_corsi_value ? 'display:none;' : ''; ?>">
                <div class="acf-label">
                    <label for="corsi_assegnati">Corsi</label>
                    <p class="description">Seleziona i corsi specifici da assegnare all'utente (Ctrl+click per selezionare più corsi)</p>
                </div>
                <div class="acf-input">
                    <select id="corsi_assegnati" name="corsi_assegnati[]" multiple size="8" style="height: auto; padding: 5px;">
                        <?php if (!empty($all_courses)): ?>
                            <?php foreach ($all_courses as $course):
                                $course_id = $course->ID;
                                $is_selected = !empty($corsi_assegnati) && array_filter($corsi_assegnati, function($c) use ($course_id) {
                                    return (is_object($c) && $c->ID == $course_id) || (is_int($c) && $c == $course_id);
                                });
                            ?>
                            <option value="<?php echo esc_attr($course_id); ?>" <?php echo $is_selected ? 'selected' : ''; ?>>
                                <?php echo esc_html($course->post_title); ?>
                            </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option disabled>Nessun corso disponibile</option>
                        <?php endif; ?>
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

    <script>
    (function() {
        const tuttiCorsiCheckbox = document.getElementById('tutti_corsi');
        const corsiSpecificiField = document.getElementById('corsi-specifici-field');

        if (tuttiCorsiCheckbox && corsiSpecificiField) {
            // Gestisci il cambio dello stato del checkbox
            tuttiCorsiCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    corsiSpecificiField.style.display = 'none';
                    // Deseleziona tutti i checkbox dei corsi
                    const checkboxes = corsiSpecificiField.querySelectorAll('input[type="checkbox"]');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = false;
                    });
                } else {
                    corsiSpecificiField.style.display = '';
                }
            });
        }
    })();
    </script>
    <?php
    return ob_get_clean();
}






function meridiana_render_comunicazione_form($action = 'new', $post_id = null) {

    $post_data = null;



    if ($action === 'edit' && $post_id) {

        $post_data = get_post($post_id);

        if (!$post_data || $post_data->post_type !== 'post') {

            return null;

        }

    }



    $title = $post_data ? $post_data->post_title : '';

    $excerpt = $post_data ? $post_data->post_excerpt : '';

    $content = $post_data ? $post_data->post_content : '';

    $status = $post_data ? $post_data->post_status : 'draft';



    $allowed_status = [

        'publish' => __('Pubblicato', 'meridiana-child'),

        'draft'   => __('Bozza', 'meridiana-child'),

    ];

    if (!array_key_exists($status, $allowed_status)) {

        $status = 'draft';

    }



    $featured_image_id = $post_data ? intval(get_post_thumbnail_id($post_id)) : 0;

    $featured_info = meridiana_get_attachment_info($featured_image_id);

    $featured_placeholder = __('Nessuna immagine selezionata', 'meridiana-child');

    if (empty($featured_info['name'])) {

        $featured_info['name'] = $featured_placeholder;

    }



    $categories = get_terms([

        'taxonomy'   => 'category',

        'hide_empty' => false,

        'orderby'    => 'name',

        'order'      => 'ASC',

    ]);

    $selected_categories = $post_data ? wp_get_post_terms($post_id, 'category', ['fields' => 'ids']) : [];

    if (!is_array($selected_categories)) {

        $selected_categories = [];

    }

    $categories_count = is_array($categories) ? count($categories) : 0;

    $category_select_size = max(5, min(10, $categories_count));



    $editor_id = 'gestore_comunicazione_content_' . ($post_id ?: 'new');

    $editor_settings = [

        'tinymce'      => true,

        'quicktags'    => true,

        'mediaButtons' => true,

    ];

    $editor_settings_attr = esc_attr(wp_json_encode($editor_settings));



    ob_start();

    ?>

    <form data-gestore-form="1" data-form-type="comunicazioni" data-form-mode="<?php echo esc_attr($action); ?>" @submit.prevent="submitForm">

        <div class="acf-form-fields">

            <div class="acf-field acf-field-text">

                <div class="acf-label">

                    <label for="post_title">Titolo <span class="required">*</span></label>

                </div>

                <div class="acf-input">

                    <input type="text" id="post_title" name="post_title" value="<?php echo esc_attr($title); ?>" required />

                </div>

            </div>



            <div class="acf-field acf-field-textarea">

                <div class="acf-label">

                    <label for="post_excerpt"><?php esc_html_e('Riassunto', 'meridiana-child'); ?></label>

                    <p class="description"><?php esc_html_e('Breve abstract della comunicazione', 'meridiana-child'); ?></p>

                </div>

                <div class="acf-input">

                    <textarea id="post_excerpt" name="post_excerpt" rows="3" style="width: 100%;"><?php echo esc_textarea($excerpt); ?></textarea>

                </div>

            </div>



            <div class="acf-field acf-field-wysiwyg">

                <div class="acf-label">

                    <label for="<?php echo esc_attr($editor_id); ?>"><?php esc_html_e('Contenuto', 'meridiana-child'); ?> <span class="required">*</span></label>

                </div>

                <div class="acf-input">

                    <textarea

                        id="<?php echo esc_attr($editor_id); ?>"

                        name="post_content"

                        class="wysiwyg-editor"

                        data-wysiwyg="1"

                        data-editor-settings="<?php echo $editor_settings_attr; ?>"

                        rows="10"

                    ><?php echo esc_textarea($content); ?></textarea>

                </div>

            </div>



            <div class="acf-field acf-field-file">

                <div class="acf-label">

                    <label><?php esc_html_e('Immagine in evidenza', 'meridiana-child'); ?></label>

                    <p class="description"><?php esc_html_e('Opzionale. Mostrata nelle anteprime della comunicazione.', 'meridiana-child'); ?></p>

                </div>

                <div class="acf-input">

                    <div

                        class="media-field"

                        data-media-field

                        data-media-type="image"

                        data-media-placeholder="<?php echo esc_attr($featured_placeholder); ?>"

                    >

                        <input type="hidden" name="featured_image_id" value="<?php echo esc_attr($featured_info['id']); ?>" />

                        <button type="button" class="button media-picker"><?php esc_html_e('Seleziona immagine', 'meridiana-child'); ?></button>

                        <button type="button" class="button button-secondary media-clear" <?php echo $featured_info['id'] ? '' : 'hidden'; ?>><?php esc_html_e('Rimuovi', 'meridiana-child'); ?></button>

                        <span class="media-file-name" data-media-file-name><?php echo esc_html($featured_info['name']); ?></span>

                        <div class="media-preview" data-media-preview>

                            <?php if (!empty($featured_info['thumbnail'])): ?>

                                <img src="<?php echo esc_url($featured_info['thumbnail']); ?>" alt="" />

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </div>



            <div class="acf-field acf-field-select">

                <div class="acf-label">

                    <label for="post_status"><?php esc_html_e('Stato', 'meridiana-child'); ?></label>

                </div>

                <div class="acf-input">

                    <select id="post_status" name="post_status">

                        <?php foreach ($allowed_status as $status_key => $label): ?>

                            <option value="<?php echo esc_attr($status_key); ?>" <?php selected($status, $status_key); ?>>

                                <?php echo esc_html($label); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

            </div>

        </div>



        <div class="acf-form-taxonomies">

            <div class="acf-field acf-field-select acf-field-taxonomy">

                <div class="acf-label">

                    <label for="post_categories"><?php esc_html_e('Categorie', 'meridiana-child'); ?></label>

                    <p class="description"><?php esc_html_e('Seleziona una o più categorie per la comunicazione', 'meridiana-child'); ?></p>

                </div>

                <div class="acf-input">

                    <?php if (!empty($categories) && !is_wp_error($categories)): ?>

                        <select id="post_categories" class="taxonomy-select" name="post_categories[]" multiple size="<?php echo esc_attr($category_select_size); ?>">

                            <?php foreach ($categories as $category):

                                $selected = in_array($category->term_id, $selected_categories, true) ? 'selected' : '';

                            ?>

                                <option value="<?php echo esc_attr($category->term_id); ?>" <?php echo $selected; ?>>

                                    <?php echo esc_html($category->name); ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    <?php else: ?>

                        <p class="description"><?php esc_html_e('Nessuna categoria disponibile. Creane una dal backend di WordPress.', 'meridiana-child'); ?></p>

                    <?php endif; ?>

                </div>

            </div>

        </div>

        <!-- NOTIFICHE MANUALI - COMUNICAZIONI -->
        <div class="acf-field acf-field-true-false">
            <div class="acf-label">
                <label for="send_notification"><?php esc_html_e('Invia Notifiche', 'meridiana-child'); ?></label>
                <p class="description"><?php esc_html_e('Abilita questa opzione per inviare notifiche agli utenti quando la comunicazione viene pubblicata', 'meridiana-child'); ?></p>
            </div>
            <div class="acf-input">
                <div class="checkbox-field">
                    <input type="hidden" name="send_notification" value="0" />
                    <label class="checkbox-inline">
                        <input type="checkbox" id="send_notification" name="send_notification" value="1" onchange="document.querySelector('.notification-segmentation-fields').style.display = this.checked ? 'block' : 'none'" />
                        <span><?php esc_html_e('Sì, abilita notifiche', 'meridiana-child'); ?></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- CAMPI DI SEGMENTAZIONE -->
        <div class="notification-segmentation-fields" style="display: none;">
            <?php meridiana_render_notification_segmentation_fields([]); ?>
        </div>

        <input type="hidden" name="post_type" value="comunicazioni" />

        <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id ?: 0); ?>" />



        <button type="submit" class="button button-primary">

            <?php echo $action === 'new' ? __('Pubblica comunicazione', 'meridiana-child') : __('Aggiorna comunicazione', 'meridiana-child'); ?>

        </button>

    </form>

    <?php

    return ob_get_clean();

}










function meridiana_render_category_multiselect($selected_ids = [], $label = '', $description = '') {
    $categories = get_terms([
        'taxonomy'   => 'category',
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ]);

    if (!is_array($selected_ids)) {
        $selected_ids = [];
    }
    $selected_ids = array_map('intval', $selected_ids);

    $category_count = (!is_wp_error($categories) && is_array($categories)) ? count($categories) : 0;
    $select_size = max(5, min(10, $category_count));

    $label_text = $label !== '' ? $label : __('Categorie', 'meridiana-child');
    $description_text = $description !== '' ? $description : __('Seleziona una o più categorie pertinenti', 'meridiana-child');

    ob_start();
    ?>
    <div class="acf-form-taxonomies">
        <div class="acf-field acf-field-select acf-field-taxonomy">
            <div class="acf-label">
                <label for="post_categories"><?php echo esc_html($label_text); ?></label>
                <?php if (!empty($description_text)): ?>
                    <p class="description"><?php echo esc_html($description_text); ?></p>
                <?php endif; ?>
            </div>
            <div class="acf-input">
                <?php if (!empty($categories) && !is_wp_error($categories)): ?>
                    <select id="post_categories" class="taxonomy-select select2-enable" name="post_categories[]" multiple size="<?php echo esc_attr($select_size); ?>">
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo esc_attr($category->term_id); ?>" <?php selected(in_array($category->term_id, $selected_ids, true)); ?>>
                                <?php echo esc_html($category->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <p class="description"><?php esc_html_e('Nessuna categoria disponibile. Creane una dal backend di WordPress.', 'meridiana-child'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}


function meridiana_render_convenzione_form($action = 'new', $post_id = null) {
    $post_data = null;

    if ($action === 'edit' && $post_id) {
        $post_data = get_post($post_id);
        if (!$post_data || $post_data->post_type !== 'convenzione') {
            return null;
        }
    }

    $title = $post_data ? $post_data->post_title : '';
    $status = $post_data ? $post_data->post_status : 'draft';

    $allowed_status = [
        'publish' => __('Pubblicato', 'meridiana-child'),
        'draft'   => __('Bozza', 'meridiana-child'),
    ];
    if (!isset($allowed_status[$status])) {
        $status = 'draft';
    }

    $is_active = $post_data ? (bool) get_field('convenzione_attiva', $post_id) : true;
    $descrizione = $post_data ? (string) get_field('descrizione', $post_id) : '';
    $contatti = $post_data ? (string) get_field('contatti', $post_id) : '';

    $image_id = $post_data ? intval(get_field('immagine_evidenza', $post_id)) : 0;
    $image_info = meridiana_get_attachment_info($image_id);
    $image_placeholder = __('Nessuna immagine selezionata', 'meridiana-child');
    if (empty($image_info['name'])) {
        $image_info['name'] = $image_placeholder;
    }

    $selected_categories = $post_data ? wp_get_post_terms($post_id, 'category', ['fields' => 'ids']) : [];
    if (!is_array($selected_categories)) {
        $selected_categories = [];
    }

    $allegati_rows = [];
    $existing_attachments = $post_data ? get_field('allegati', $post_id) : [];
    if (!is_array($existing_attachments)) {
        $existing_attachments = [];
    }

    foreach ($existing_attachments as $row) {
        $file_value = $row['file'] ?? null;
        $file_id = 0;
        if (is_array($file_value)) {
            $file_id = intval($file_value['ID'] ?? ($file_value['id'] ?? 0));
        } else {
            $file_id = intval($file_value);
        }
        $file_info = meridiana_get_attachment_info($file_id);
        if (empty($file_info['name'])) {
            $file_info['name'] = __('Nessun file selezionato', 'meridiana-child');
        }
        $allegati_rows[] = [
            'file_id' => $file_id,
            'file_info' => $file_info,
            'description' => isset($row['descrizione']) ? sanitize_text_field($row['descrizione']) : '',
        ];
    }

    $descr_editor_id = 'gestore_convenzione_descrizione_' . ($post_id ?: 'new');
    $descr_editor_settings = [
        'tinymce'      => true,
        'quicktags'    => true,
        'mediaButtons' => true,
    ];
    $descr_editor_attr = esc_attr(wp_json_encode($descr_editor_settings));

    $contatti_editor_id = 'gestore_convenzione_contatti_' . ($post_id ?: 'new');
    $contatti_editor_settings = [
        'tinymce'      => true,
        'quicktags'    => true,
        'mediaButtons' => false,
    ];
    $contatti_editor_attr = esc_attr(wp_json_encode($contatti_editor_settings));

    $allegato_placeholder = __('Nessun file selezionato', 'meridiana-child');
    ob_start();
    ?>
    <form data-gestore-form="1" data-form-type="convenzioni" data-form-mode="<?php echo esc_attr($action); ?>" @submit.prevent="submitForm">
        <div class="acf-form-fields">
            <div class="acf-field acf-field-text">
                <div class="acf-label">
                    <label for="post_title"><?php esc_html_e('Titolo', 'meridiana-child'); ?> <span class="required">*</span></label>
                </div>
                <div class="acf-input">
                    <input type="text" id="post_title" name="post_title" value="<?php echo esc_attr($title); ?>" required />
                </div>
            </div>

            <div class="acf-field acf-field-true-false">
                <div class="acf-label">
                    <label for="convenzione_attiva"><?php esc_html_e('Convenzione attiva', 'meridiana-child'); ?></label>
                    <p class="description"><?php esc_html_e('Indica se la convenzione è attualmente attiva o scaduta.', 'meridiana-child'); ?></p>
                </div>
                <div class="acf-input checkbox-field">
                    <label class="checkbox-inline">
                        <input type="checkbox" id="convenzione_attiva" name="convenzione_attiva" value="1" <?php checked($is_active); ?> />
                        <span><?php echo $is_active ? esc_html__('Attiva', 'meridiana-child') : esc_html__('Scaduta', 'meridiana-child'); ?></span>
                    </label>
                </div>
            </div>

            <div class="acf-field acf-field-wysiwyg">
                <div class="acf-label">
                    <label for="<?php echo esc_attr($descr_editor_id); ?>"><?php esc_html_e('Descrizione', 'meridiana-child'); ?> <span class="required">*</span></label>
                    <p class="description"><?php esc_html_e('Descrizione completa della convenzione.', 'meridiana-child'); ?></p>
                </div>
                <div class="acf-input">
                    <textarea
                        id="<?php echo esc_attr($descr_editor_id); ?>"
                        name="convenzione_descrizione"
                        class="wysiwyg-editor"
                        data-wysiwyg="1"
                        data-editor-settings="<?php echo $descr_editor_attr; ?>"
                        rows="10"
                    ><?php echo esc_textarea($descrizione); ?></textarea>
                </div>
            </div>

            <div class="acf-field acf-field-file">
                <div class="acf-label">
                    <label><?php esc_html_e('Immagine in evidenza', 'meridiana-child'); ?></label>
                    <p class="description"><?php esc_html_e('Immagine principale della convenzione.', 'meridiana-child'); ?></p>
                </div>
                <div class="acf-input">
                    <div
                        class="media-field"
                        data-media-field
                        data-media-type="image"
                        data-media-placeholder="<?php echo esc_attr($image_placeholder); ?>"
                    >
                        <input type="hidden" name="convenzione_featured_id" value="<?php echo esc_attr($image_info['id']); ?>" />
                        <button type="button" class="button media-picker"><?php esc_html_e('Seleziona immagine', 'meridiana-child'); ?></button>
                        <button type="button" class="button button-secondary media-clear" <?php echo $image_info['id'] ? '' : 'hidden'; ?>><?php esc_html_e('Rimuovi', 'meridiana-child'); ?></button>
                        <span class="media-file-name" data-media-file-name><?php echo esc_html($image_info['name']); ?></span>
                        <div class="media-preview" data-media-preview>
                            <?php if (!empty($image_info['thumbnail'])): ?>
                                <img src="<?php echo esc_url($image_info['thumbnail']); ?>" alt="" />
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="acf-field acf-field-wysiwyg">
                <div class="acf-label">
                    <label for="<?php echo esc_attr($contatti_editor_id); ?>"><?php esc_html_e('Contatti', 'meridiana-child'); ?></label>
                    <p class="description"><?php esc_html_e('Informazioni di contatto per la convenzione.', 'meridiana-child'); ?></p>
                </div>
                <div class="acf-input">
                    <textarea
                        id="<?php echo esc_attr($contatti_editor_id); ?>"
                        name="convenzione_contatti"
                        class="wysiwyg-editor"
                        data-wysiwyg="1"
                        data-editor-settings="<?php echo $contatti_editor_attr; ?>"
                        rows="6"
                    ><?php echo esc_textarea($contatti); ?></textarea>
                </div>
            </div>

            <div class="acf-field acf-field-select">
                <div class="acf-label">
                    <label for="convenzione_status"><?php esc_html_e('Stato', 'meridiana-child'); ?></label>
                </div>
                <div class="acf-input">
                    <select id="convenzione_status" name="post_status">
                        <?php foreach ($allowed_status as $status_key => $label): ?>
                            <option value="<?php echo esc_attr($status_key); ?>" <?php selected($status, $status_key); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="acf-field acf-field-repeater" data-repeater="allegati">
            <div class="acf-label">
                <label><?php esc_html_e('Allegati', 'meridiana-child'); ?></label>
                <p class="description"><?php esc_html_e('Aggiungi eventuali file allegati alla convenzione.', 'meridiana-child'); ?></p>
            </div>
            <div class="acf-input">
                <div class="repeater-rows" data-repeater-rows>
                    <?php foreach ($allegati_rows as $index => $row): ?>
                        <div class="repeater-row" data-repeater-row>
                            <div class="repeater-row__body">
                                <div
                                    class="media-field"
                                    data-media-field
                                    data-media-type="file"
                                    data-media-placeholder="<?php echo esc_attr($allegato_placeholder); ?>"
                                >
                                    <input type="hidden" name="allegati[<?php echo esc_attr($index); ?>][file_id]" value="<?php echo esc_attr($row['file_id']); ?>" />
                                    <button type="button" class="button media-picker"><?php esc_html_e('Seleziona file', 'meridiana-child'); ?></button>
                                    <button type="button" class="button button-secondary media-clear" <?php echo $row['file_id'] ? '' : 'hidden'; ?>><?php esc_html_e('Rimuovi', 'meridiana-child'); ?></button>
                                    <span class="media-file-name" data-media-file-name><?php echo esc_html($row['file_info']['name']); ?></span>
                                </div>
                                <div class="acf-field acf-field-text">
                                    <div class="acf-label">
                                        <label><?php esc_html_e('Descrizione', 'meridiana-child'); ?></label>
                                    </div>
                                    <div class="acf-input">
                                        <input type="text" name="allegati[<?php echo esc_attr($index); ?>][descrizione]" value="<?php echo esc_attr($row['description']); ?>" placeholder="<?php esc_attr_e('Descrivi l\'allegato', 'meridiana-child'); ?>" />
                                    </div>
                                </div>
                            </div>
                            <div class="repeater-row__footer">
                                <button type="button" class="button button-secondary" data-repeater-remove><?php esc_html_e('Rimuovi allegato', 'meridiana-child'); ?></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="button button-secondary" data-repeater-add="allegati"><?php esc_html_e('Aggiungi allegato', 'meridiana-child'); ?></button>
                <template data-repeater-template="allegati">
                    <div class="repeater-row" data-repeater-row>
                        <div class="repeater-row__body">
                            <div
                                class="media-field"
                                data-media-field
                                data-media-type="file"
                                data-media-placeholder="<?php echo esc_attr($allegato_placeholder); ?>"
                            >
                                <input type="hidden" name="allegati[__index__][file_id]" value="" />
                                <button type="button" class="button media-picker"><?php esc_html_e('Seleziona file', 'meridiana-child'); ?></button>
                                <button type="button" class="button button-secondary media-clear" hidden><?php esc_html_e('Rimuovi', 'meridiana-child'); ?></button>
                                <span class="media-file-name" data-media-file-name><?php echo esc_html($allegato_placeholder); ?></span>
                            </div>
                            <div class="acf-field acf-field-text">
                                <div class="acf-label">
                                    <label><?php esc_html_e('Descrizione', 'meridiana-child'); ?></label>
                                </div>
                                <div class="acf-input">
                                    <input type="text" name="allegati[__index__][descrizione]" value="" placeholder="<?php esc_attr_e('Descrivi l\'allegato', 'meridiana-child'); ?>" />
                                </div>
                            </div>
                        </div>
                        <div class="repeater-row__footer">
                            <button type="button" class="button button-secondary" data-repeater-remove><?php esc_html_e('Rimuovi allegato', 'meridiana-child'); ?></button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <?php echo meridiana_render_category_multiselect($selected_categories, __('Categorie', 'meridiana-child'), __('Seleziona una o più categorie per la convenzione.', 'meridiana-child')); ?>

        <!-- NOTIFICHE MANUALI - CONVENZIONI -->
        <div class="acf-field acf-field-true-false">
            <div class="acf-label">
                <label for="send_notification"><?php esc_html_e('Invia Notifiche', 'meridiana-child'); ?></label>
                <p class="description"><?php esc_html_e('Abilita questa opzione per inviare notifiche agli utenti quando la convenzione viene pubblicata', 'meridiana-child'); ?></p>
            </div>
            <div class="acf-input">
                <div class="checkbox-field">
                    <input type="hidden" name="send_notification" value="0" />
                    <label class="checkbox-inline">
                        <input type="checkbox" id="send_notification" name="send_notification" value="1" onchange="document.querySelector('.notification-segmentation-fields').style.display = this.checked ? 'block' : 'none'" />
                        <span><?php esc_html_e('Sì, abilita notifiche', 'meridiana-child'); ?></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- CAMPI DI SEGMENTAZIONE -->
        <div class="notification-segmentation-fields" style="display: none;">
            <?php meridiana_render_notification_segmentation_fields([]); ?>
        </div>

        <input type="hidden" name="post_type" value="convenzioni" />
        <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id ?: 0); ?>" />

        <button type="submit" class="button button-primary">
            <?php echo $action === 'new' ? __('Pubblica convenzione', 'meridiana-child') : __('Aggiorna convenzione', 'meridiana-child'); ?>
        </button>
    </form>
    <?php
    return ob_get_clean();
}


function meridiana_render_salute_form($action = 'new', $post_id = null) {
    $post_data = null;

    if ($action === 'edit' && $post_id) {
        $post_data = get_post($post_id);
        if (!$post_data || $post_data->post_type !== 'salute-e-benessere-l') {
            return null;
        }
    }

    $title = $post_data ? $post_data->post_title : '';
    $status = $post_data ? $post_data->post_status : 'draft';

    $allowed_status = [
        'publish' => __('Pubblicato', 'meridiana-child'),
        'draft'   => __('Bozza', 'meridiana-child'),
    ];
    if (!isset($allowed_status[$status])) {
        $status = 'draft';
    }

    $contenuto = $post_data ? (string) get_field('contenuto', $post_id) : '';
    $selected_categories = $post_data ? wp_get_post_terms($post_id, 'category', ['fields' => 'ids']) : [];
    if (!is_array($selected_categories)) {
        $selected_categories = [];
    }

    $risorse_rows = [];
    $existing_risorse = $post_data ? get_field('risorse', $post_id) : [];
    if (!is_array($existing_risorse)) {
        $existing_risorse = [];
    }

    foreach ($existing_risorse as $row) {
        $file_value = $row['file'] ?? null;
        $file_id = 0;
        if (is_array($file_value)) {
            $file_id = intval($file_value['ID'] ?? ($file_value['id'] ?? 0));
        } else {
            $file_id = intval($file_value);
        }
        $file_info = meridiana_get_attachment_info($file_id);
        if (empty($file_info['name'])) {
            $file_info['name'] = __('Nessun file selezionato', 'meridiana-child');
        }
        $risorse_rows[] = [
            'tipo' => isset($row['tipo']) ? sanitize_text_field($row['tipo']) : 'link',
            'titolo' => isset($row['titolo']) ? sanitize_text_field($row['titolo']) : '',
            'url' => isset($row['url']) ? esc_url($row['url']) : '',
            'file_id' => $file_id,
            'file_info' => $file_info,
        ];
    }

    $contenuto_editor_id = 'gestore_salute_contenuto_' . ($post_id ?: 'new');
    $contenuto_editor_settings = [
        'tinymce'      => true,
        'quicktags'    => true,
        'mediaButtons' => true,
    ];
    $contenuto_editor_attr = esc_attr(wp_json_encode($contenuto_editor_settings));

    $risorsa_placeholder = __('Nessun file selezionato', 'meridiana-child');

    ob_start();
    ?>
    <form data-gestore-form="1" data-form-type="salute" data-form-mode="<?php echo esc_attr($action); ?>" @submit.prevent="submitForm">
        <div class="acf-form-fields">
            <div class="acf-field acf-field-text">
                <div class="acf-label">
                    <label for="post_title"><?php esc_html_e('Titolo', 'meridiana-child'); ?> <span class="required">*</span></label>
                </div>
                <div class="acf-input">
                    <input type="text" id="post_title" name="post_title" value="<?php echo esc_attr($title); ?>" required />
                </div>
            </div>

            <div class="acf-field acf-field-wysiwyg">
                <div class="acf-label">
                    <label for="<?php echo esc_attr($contenuto_editor_id); ?>"><?php esc_html_e('Contenuto', 'meridiana-child'); ?> <span class="required">*</span></label>
                    <p class="description"><?php esc_html_e('Contenuto principale dell\'articolo.', 'meridiana-child'); ?></p>
                </div>
                <div class="acf-input">
                    <textarea
                        id="<?php echo esc_attr($contenuto_editor_id); ?>"
                        name="salute_contenuto"
                        class="wysiwyg-editor"
                        data-wysiwyg="1"
                        data-editor-settings="<?php echo $contenuto_editor_attr; ?>"
                        rows="10"
                    ><?php echo esc_textarea($contenuto); ?></textarea>
                </div>
            </div>

            <div class="acf-field acf-field-select">
                <div class="acf-label">
                    <label for="salute_status"><?php esc_html_e('Stato', 'meridiana-child'); ?></label>
                </div>
                <div class="acf-input">
                    <select id="salute_status" name="post_status">
                        <?php foreach ($allowed_status as $status_key => $label): ?>
                            <option value="<?php echo esc_attr($status_key); ?>" <?php selected($status, $status_key); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="acf-field acf-field-repeater" data-repeater="risorse">
            <div class="acf-label">
                <label><?php esc_html_e('Risorse correlate', 'meridiana-child'); ?></label>
                <p class="description"><?php esc_html_e('Aggiungi link o file utili di approfondimento.', 'meridiana-child'); ?></p>
            </div>
            <div class="acf-input">
                <div class="repeater-rows" data-repeater-rows>
                    <?php foreach ($risorse_rows as $index => $row): ?>
                        <div class="repeater-row" data-repeater-row>
                            <div class="repeater-row__body" data-risorsa-row>
                                <div class="acf-field acf-field-select">
                                    <div class="acf-label">
                                        <label><?php esc_html_e('Tipo risorsa', 'meridiana-child'); ?></label>
                                    </div>
                                    <div class="acf-input">
                                        <select name="risorse[<?php echo esc_attr($index); ?>][tipo]" data-risorsa-type>
                                            <option value="link" <?php selected($row['tipo'], 'link'); ?>><?php esc_html_e('Link esterno', 'meridiana-child'); ?></option>
                                            <option value="file" <?php selected($row['tipo'], 'file'); ?>><?php esc_html_e('File da scaricare', 'meridiana-child'); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="acf-field acf-field-text">
                                    <div class="acf-label">
                                        <label><?php esc_html_e('Titolo', 'meridiana-child'); ?> <span class="required">*</span></label>
                                    </div>
                                    <div class="acf-input">
                                        <input type="text" name="risorse[<?php echo esc_attr($index); ?>][titolo]" value="<?php echo esc_attr($row['titolo']); ?>" required />
                                    </div>
                                </div>
                                <div class="acf-field acf-field-url" data-risorsa-field="link" <?php echo $row['tipo'] === 'link' ? '' : 'hidden'; ?>>
                                    <div class="acf-label">
                                        <label><?php esc_html_e('URL', 'meridiana-child'); ?></label>
                                    </div>
                                    <div class="acf-input">
                                        <input type="url" name="risorse[<?php echo esc_attr($index); ?>][url]" value="<?php echo esc_attr($row['url']); ?>" placeholder="https://" />
                                    </div>
                                </div>
                                <div class="acf-field acf-field-file" data-risorsa-field="file" <?php echo $row['tipo'] === 'file' ? '' : 'hidden'; ?>>
                                    <div class="acf-label">
                                        <label><?php esc_html_e('File', 'meridiana-child'); ?></label>
                                    </div>
                                    <div class="acf-input">
                                        <div
                                            class="media-field"
                                            data-media-field
                                            data-media-type="file"
                                            data-media-placeholder="<?php echo esc_attr($risorsa_placeholder); ?>"
                                        >
                                            <input type="hidden" name="risorse[<?php echo esc_attr($index); ?>][file_id]" value="<?php echo esc_attr($row['file_id']); ?>" />
                                            <button type="button" class="button media-picker"><?php esc_html_e('Seleziona file', 'meridiana-child'); ?></button>
                                            <button type="button" class="button button-secondary media-clear" <?php echo $row['file_id'] ? '' : 'hidden'; ?>><?php esc_html_e('Rimuovi', 'meridiana-child'); ?></button>
                                            <span class="media-file-name" data-media-file-name><?php echo esc_html($row['file_info']['name']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="repeater-row__footer">
                                <button type="button" class="button button-secondary" data-repeater-remove><?php esc_html_e('Rimuovi risorsa', 'meridiana-child'); ?></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="button button-secondary" data-repeater-add="risorse"><?php esc_html_e('Aggiungi risorsa', 'meridiana-child'); ?></button>
                <template data-repeater-template="risorse">
                    <div class="repeater-row" data-repeater-row>
                        <div class="repeater-row__body" data-risorsa-row>
                            <div class="acf-field acf-field-select">
                                <div class="acf-label">
                                    <label><?php esc_html_e('Tipo risorsa', 'meridiana-child'); ?></label>
                                </div>
                                <div class="acf-input">
                                    <select name="risorse[__index__][tipo]" data-risorsa-type>
                                        <option value="link" selected><?php esc_html_e('Link esterno', 'meridiana-child'); ?></option>
                                        <option value="file"><?php esc_html_e('File da scaricare', 'meridiana-child'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="acf-field acf-field-text">
                                <div class="acf-label">
                                    <label><?php esc_html_e('Titolo', 'meridiana-child'); ?> <span class="required">*</span></label>
                                </div>
                                <div class="acf-input">
                                    <input type="text" name="risorse[__index__][titolo]" value="" required />
                                </div>
                            </div>
                            <div class="acf-field acf-field-url" data-risorsa-field="link">
                                <div class="acf-label">
                                    <label><?php esc_html_e('URL', 'meridiana-child'); ?></label>
                                </div>
                                <div class="acf-input">
                                    <input type="url" name="risorse[__index__][url]" value="" placeholder="https://" />
                                </div>
                            </div>
                            <div class="acf-field acf-field-file" data-risorsa-field="file" hidden>
                                <div class="acf-label">
                                    <label><?php esc_html_e('File', 'meridiana-child'); ?></label>
                                </div>
                                <div class="acf-input">
                                    <div
                                        class="media-field"
                                        data-media-field
                                        data-media-type="file"
                                        data-media-placeholder="<?php echo esc_attr($risorsa_placeholder); ?>"
                                    >
                                        <input type="hidden" name="risorse[__index__][file_id]" value="" />
                                        <button type="button" class="button media-picker"><?php esc_html_e('Seleziona file', 'meridiana-child'); ?></button>
                                        <button type="button" class="button button-secondary media-clear" hidden><?php esc_html_e('Rimuovi', 'meridiana-child'); ?></button>
                                        <span class="media-file-name" data-media-file-name><?php echo esc_html($risorsa_placeholder); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="repeater-row__footer">
                            <button type="button" class="button button-secondary" data-repeater-remove><?php esc_html_e('Rimuovi risorsa', 'meridiana-child'); ?></button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <?php echo meridiana_render_category_multiselect($selected_categories, __('Categorie', 'meridiana-child'), __('Scegli le categorie associate al contenuto.', 'meridiana-child')); ?>

        <!-- NOTIFICHE MANUALI - SALUTE & BENESSERE -->
        <div class="acf-field acf-field-true-false">
            <div class="acf-label">
                <label for="send_notification"><?php esc_html_e('Invia Notifiche', 'meridiana-child'); ?></label>
                <p class="description"><?php esc_html_e('Abilita questa opzione per inviare notifiche agli utenti quando il contenuto viene pubblicato', 'meridiana-child'); ?></p>
            </div>
            <div class="acf-input">
                <div class="checkbox-field">
                    <input type="hidden" name="send_notification" value="0" />
                    <label class="checkbox-inline">
                        <input type="checkbox" id="send_notification" name="send_notification" value="1" onchange="document.querySelector('.notification-segmentation-fields').style.display = this.checked ? 'block' : 'none'" />
                        <span><?php esc_html_e('Sì, abilita notifiche', 'meridiana-child'); ?></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- CAMPI DI SEGMENTAZIONE -->
        <div class="notification-segmentation-fields" style="display: none;">
            <?php meridiana_render_notification_segmentation_fields([]); ?>
        </div>

        <input type="hidden" name="post_type" value="salute" />
        <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id ?: 0); ?>" />

        <button type="submit" class="button button-primary">
            <?php echo $action === 'new' ? __('Pubblica contenuto', 'meridiana-child') : __('Aggiorna contenuto', 'meridiana-child'); ?>
        </button>
    </form>
    <?php
    return ob_get_clean();
}


function meridiana_render_documento_taxonomy_fields_html($post_type, $post_id = 0) {

    $taxonomies = [

        'unita-offerta' => [

            'label' => __('Unità di Offerta', 'meridiana-child'),

            'description' => __('Seleziona una o più unità di offerta pertinenti.', 'meridiana-child'),

            'multiple' => true,

        ],

        'profilo-professionale' => [

            'label' => __('Profilo Professionale', 'meridiana-child'),

            'description' => __('Indica i profili coinvolti.', 'meridiana-child'),

            'multiple' => true,

        ],

    ];



    if ($post_type === 'modulo') {

        $taxonomies['area-competenza'] = [

            'label' => __('Aree di Competenza', 'meridiana-child'),

            'description' => __('Classifica il modulo per area tematica.', 'meridiana-child'),

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

        $select_attributes = $multiple_attr;



        if ($config['multiple']) {

            $options_count = count($terms);

            $select_size = max(5, min(10, $options_count));

            $select_attributes .= ' size="' . esc_attr($select_size) . '"';

        }



        ?>

        <div class="acf-field acf-field-select acf-field-taxonomy">

            <div class="acf-label">

                <label><?php echo esc_html($config['label']); ?></label>

                <?php if (!empty($config['description'])): ?>

                    <p class="description"><?php echo esc_html($config['description']); ?></p>

                <?php endif; ?>

            </div>

            <div class="acf-input">

                <select class="taxonomy-select select2-enable" name="<?php echo esc_attr($field_name); ?>" <?php echo $select_attributes; ?>>

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
// SAVE DOCUMENTO FORM
// ============================================





function meridiana_ajax_save_comunicazione() {
    if (!current_user_can('manage_platform') && !current_user_can('edit_posts') && !current_user_can('manage_options')) {

        wp_send_json_error(['message' => __('Permessi insufficienti', 'meridiana-child')], 403);

    }



    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;



    $title = isset($_POST['post_title']) ? sanitize_text_field(wp_unslash($_POST['post_title'])) : '';

    if (empty($title)) {

        wp_send_json_error(['message' => __('Titolo obbligatorio', 'meridiana-child')], 400);

    }



    $status = isset($_POST['post_status']) ? sanitize_text_field(wp_unslash($_POST['post_status'])) : 'draft';

    $allowed_status = ['publish', 'draft'];

    if (!in_array($status, $allowed_status, true)) {

        $status = 'draft';

    }



    $excerpt = isset($_POST['post_excerpt']) ? sanitize_textarea_field(wp_unslash($_POST['post_excerpt'])) : '';

    $content_raw = isset($_POST['post_content']) ? wp_unslash($_POST['post_content']) : '';

    $content = wp_kses_post($content_raw);



    $post_data = [

        'post_title'   => $title,

        'post_content' => $content,

        'post_excerpt' => $excerpt,

        'post_status'  => $status,

        'post_type'    => 'post',

    ];



    if ($post_id === 0) {

        $post_id = wp_insert_post($post_data, true);

        if (is_wp_error($post_id)) {

            wp_send_json_error(['message' => __('Errore creazione comunicazione', 'meridiana-child')], 500);

        }

    } else {

        $post_data['ID'] = $post_id;

        $updated = wp_update_post($post_data, true);

        if (is_wp_error($updated)) {

            wp_send_json_error(['message' => __('Errore aggiornamento comunicazione', 'meridiana-child')], 500);

        }

    }



    $categories_input = isset($_POST['post_categories']) ? (array) $_POST['post_categories'] : [];

    $category_ids = array_map('intval', array_map('wp_unslash', $categories_input));

    wp_set_post_terms($post_id, $category_ids, 'category', false);



    if (array_key_exists('featured_image_id', $_POST)) {

        $thumbnail_id = intval($_POST['featured_image_id']);

        if ($thumbnail_id > 0) {

            set_post_thumbnail($post_id, $thumbnail_id);

        } else {

            delete_post_thumbnail($post_id);

        }

    }

    // Gestisci notifiche manuali se attive
    meridiana_handle_document_notification($post_id, 'post');

    wp_send_json_success([

        'message' => __('Comunicazione salvata con successo', 'meridiana-child'),

        'post_id' => $post_id,

    ]);

}








function meridiana_ajax_save_convenzione() {
    if (!current_user_can('manage_platform') && !current_user_can('edit_posts') && !current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Permessi insufficienti', 'meridiana-child')], 403);
    }

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $title = isset($_POST['post_title']) ? sanitize_text_field(wp_unslash($_POST['post_title'])) : '';

    if ($title === '') {
        wp_send_json_error(['message' => __('Titolo obbligatorio', 'meridiana-child')], 400);
    }

    $status = isset($_POST['post_status']) ? sanitize_text_field(wp_unslash($_POST['post_status'])) : 'draft';
    $allowed_status = ['publish', 'draft'];
    if (!in_array($status, $allowed_status, true)) {
        $status = 'draft';
    }

    $descrizione_raw = isset($_POST['convenzione_descrizione']) ? wp_unslash($_POST['convenzione_descrizione']) : '';
    $descrizione = wp_kses_post($descrizione_raw);
    if (trim(wp_strip_all_tags($descrizione)) === '') {
        wp_send_json_error(['message' => __('Inserisci una descrizione', 'meridiana-child')], 400);
    }

    $contatti_raw = isset($_POST['convenzione_contatti']) ? wp_unslash($_POST['convenzione_contatti']) : '';
    $contatti = wp_kses_post($contatti_raw);

    $is_active = isset($_POST['convenzione_attiva']) ? 1 : 0;
    $image_id = isset($_POST['convenzione_featured_id']) ? intval($_POST['convenzione_featured_id']) : 0;

    $post_args = [
        'post_title'   => $title,
        'post_status'  => $status,
        'post_type'    => 'convenzione',
        'post_content' => $descrizione,
    ];

    if ($post_id === 0) {
        $post_id = wp_insert_post($post_args, true);
        if (is_wp_error($post_id)) {
            wp_send_json_error(['message' => __('Errore creazione convenzione', 'meridiana-child')], 500);
        }
    } else {
        $existing = get_post($post_id);
        if (!$existing || $existing->post_type !== 'convenzione') {
            wp_send_json_error(['message' => __('Convenzione non trovata', 'meridiana-child')], 404);
        }
        $post_args['ID'] = $post_id;
        $updated = wp_update_post($post_args, true);
        if (is_wp_error($updated)) {
            wp_send_json_error(['message' => __('Errore aggiornamento convenzione', 'meridiana-child')], 500);
        }
    }

    $allegati_rows = [];
    if (isset($_POST['allegati']) && is_array($_POST['allegati'])) {
        foreach ($_POST['allegati'] as $row) {
            if (!is_array($row)) {
                continue;
            }
            $file_id = isset($row['file_id']) ? intval($row['file_id']) : 0;
            $description = isset($row['descrizione']) ? sanitize_text_field(wp_unslash($row['descrizione'])) : '';
            if ($file_id <= 0) {
                continue;
            }
            $allegati_rows[] = [
                'field_allegato_file' => $file_id,
                'field_allegato_descrizione' => $description,
            ];
        }
    }

    if (function_exists('update_field')) {
        update_field('field_convenzione_attiva', $is_active ? 1 : 0, $post_id);
        update_field('field_descrizione_convenzione', $descrizione, $post_id);
        update_field('field_contatti_convenzione', $contatti, $post_id);

        if ($image_id > 0) {
            update_field('field_immagine_convenzione', $image_id, $post_id);
        } elseif (function_exists('delete_field')) {
            delete_field('field_immagine_convenzione', $post_id);
        } else {
            delete_post_meta($post_id, 'immagine_evidenza');
        }

        if (!empty($allegati_rows)) {
            update_field('field_allegati_convenzione', $allegati_rows, $post_id);
        } elseif (function_exists('delete_field')) {
            delete_field('field_allegati_convenzione', $post_id);
        } else {
            delete_post_meta($post_id, 'allegati');
        }
    } else {
        update_post_meta($post_id, 'convenzione_attiva', $is_active ? 1 : 0);
        update_post_meta($post_id, 'descrizione', $descrizione);
        update_post_meta($post_id, 'contatti', $contatti);
        if ($image_id > 0) {
            update_post_meta($post_id, 'immagine_evidenza', $image_id);
        } else {
            delete_post_meta($post_id, 'immagine_evidenza');
        }
        update_post_meta($post_id, 'allegati', $allegati_rows);
    }

    if ($image_id > 0) {
        set_post_thumbnail($post_id, $image_id);
    } else {
        delete_post_thumbnail($post_id);
    }

    $categories_input = isset($_POST['post_categories']) ? (array) $_POST['post_categories'] : [];
    $categories_clean = [];
    foreach ($categories_input as $cat) {
        $categories_clean[] = intval(wp_unslash($cat));
    }
    wp_set_post_terms($post_id, $categories_clean, 'category', false);

    // Gestisci notifiche manuali se attive
    meridiana_handle_document_notification($post_id, 'convenzione');

    wp_send_json_success([
        'message' => __('Convenzione salvata con successo', 'meridiana-child'),
        'post_id' => $post_id,
    ]);
}


function meridiana_ajax_save_salute() {
    if (!current_user_can('manage_platform') && !current_user_can('edit_posts') && !current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Permessi insufficienti', 'meridiana-child')], 403);
    }

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $title = isset($_POST['post_title']) ? sanitize_text_field(wp_unslash($_POST['post_title'])) : '';

    if ($title === '') {
        wp_send_json_error(['message' => __('Titolo obbligatorio', 'meridiana-child')], 400);
    }

    $status = isset($_POST['post_status']) ? sanitize_text_field(wp_unslash($_POST['post_status'])) : 'draft';
    $allowed_status = ['publish', 'draft'];
    if (!in_array($status, $allowed_status, true)) {
        $status = 'draft';
    }

    $contenuto_raw = isset($_POST['salute_contenuto']) ? wp_unslash($_POST['salute_contenuto']) : '';
    $contenuto = wp_kses_post($contenuto_raw);
    if (trim(wp_strip_all_tags($contenuto)) === '') {
        wp_send_json_error(['message' => __('Inserisci il contenuto principale', 'meridiana-child')], 400);
    }

    $post_args = [
        'post_title'   => $title,
        'post_status'  => $status,
        'post_type'    => 'salute-e-benessere-l',
        'post_content' => $contenuto,
    ];

    if ($post_id === 0) {
        $post_id = wp_insert_post($post_args, true);
        if (is_wp_error($post_id)) {
            wp_send_json_error(['message' => __('Errore creazione contenuto', 'meridiana-child')], 500);
        }
    } else {
        $existing = get_post($post_id);
        if (!$existing || $existing->post_type !== 'salute-e-benessere-l') {
            wp_send_json_error(['message' => __('Contenuto non trovato', 'meridiana-child')], 404);
        }
        $post_args['ID'] = $post_id;
        $updated = wp_update_post($post_args, true);
        if (is_wp_error($updated)) {
            wp_send_json_error(['message' => __('Errore aggiornamento contenuto', 'meridiana-child')], 500);
        }
    }

    $risorse_rows = [];
    if (isset($_POST['risorse']) && is_array($_POST['risorse'])) {
        $allowed_types = ['link', 'file'];
        foreach ($_POST['risorse'] as $row_index => $row) {
            if (!is_array($row)) {
                continue;
            }
            $tipo = isset($row['tipo']) ? sanitize_text_field(wp_unslash($row['tipo'])) : 'link';
            if (!in_array($tipo, $allowed_types, true)) {
                $tipo = 'link';
            }

            $titolo = isset($row['titolo']) ? sanitize_text_field(wp_unslash($row['titolo'])) : '';
            if ($titolo === '') {
                wp_send_json_error(['message' => sprintf(__('Il titolo della risorsa %d è obbligatorio', 'meridiana-child'), $row_index + 1)], 400);
            }

            $url_value = isset($row['url']) ? wp_unslash($row['url']) : '';
            $url = $url_value !== '' ? esc_url_raw($url_value) : '';
            $file_id = isset($row['file_id']) ? intval($row['file_id']) : 0;

            if ($tipo === 'link') {
                if ($url === '') {
                    wp_send_json_error(['message' => sprintf(__('Inserisci un URL valido per la risorsa %d', 'meridiana-child'), $row_index + 1)], 400);
                }
                $risorse_rows[] = [
                    'field_risorsa_tipo'   => 'link',
                    'field_risorsa_titolo' => $titolo,
                    'field_risorsa_url'    => $url,
                    'field_risorsa_file'   => 0,
                ];
            } else {
                if ($file_id <= 0) {
                    wp_send_json_error(['message' => sprintf(__('Seleziona un file per la risorsa %d', 'meridiana-child'), $row_index + 1)], 400);
                }
                $risorse_rows[] = [
                    'field_risorsa_tipo'   => 'file',
                    'field_risorsa_titolo' => $titolo,
                    'field_risorsa_url'    => '',
                    'field_risorsa_file'   => $file_id,
                ];
            }
        }
    }

    if (function_exists('update_field')) {
        update_field('field_contenuto_salute', $contenuto, $post_id);
        if (!empty($risorse_rows)) {
            update_field('field_risorse_salute', $risorse_rows, $post_id);
        } elseif (function_exists('delete_field')) {
            delete_field('field_risorse_salute', $post_id);
        } else {
            delete_post_meta($post_id, 'risorse');
        }
    } else {
        update_post_meta($post_id, 'contenuto', $contenuto);
        update_post_meta($post_id, 'risorse', $risorse_rows);
    }

    $categories_input = isset($_POST['post_categories']) ? (array) $_POST['post_categories'] : [];
    $categories_clean = [];
    foreach ($categories_input as $cat) {
        $categories_clean[] = intval(wp_unslash($cat));
    }
    wp_set_post_terms($post_id, $categories_clean, 'category', false);

    // Gestisci notifiche manuali se attive
    meridiana_handle_document_notification($post_id, 'salute-e-benessere-l');

    wp_send_json_success([
        'message' => __('Contenuto salvato con successo', 'meridiana-child'),
        'post_id' => $post_id,
    ]);
}


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

            'post_title'   => $title,

            'post_type'    => $cpt,

            'post_status'  => 'publish',

            'post_content' => '',

        ];

        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {

            wp_send_json_error(['message' => 'Errore creazione documento'], 500);

        }

    } else {

        wp_update_post([

            'ID'         => $post_id,

            'post_title' => $title,

        ]);

    }



    meridiana_save_documento_acf_fields($post_id, $cpt);

    meridiana_save_documento_taxonomies($post_id, $cpt);



    if ($cpt === 'modulo' && array_key_exists('featured_image_id', $_POST)) {

        $thumbnail_id = intval($_POST['featured_image_id']);

        if ($thumbnail_id > 0) {

            set_post_thumbnail($post_id, $thumbnail_id);

        } else {

            delete_post_thumbnail($post_id);

        }

    }

    // Gestisci notifiche manuali se attive
    meridiana_handle_document_notification($post_id, $cpt);

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

    // Cattura il vecchio PDF prima di aggiornare
    $old_pdf_value = intval(get_field($pdf_field_key, $post_id)) ?: 0;

    $pdf_value = 0;
    if (isset($_POST['acf'][$pdf_field_key])) {
        $pdf_raw = $_POST['acf'][$pdf_field_key];
        if (is_array($pdf_raw)) {
            $pdf_raw = end($pdf_raw);
        }
        $pdf_value = intval($pdf_raw);
    }

    // Se il PDF è stato cambiato (edit) o rimosso, archivia il vecchio file
    if ($old_pdf_value && $old_pdf_value !== $pdf_value && function_exists('meridiana_archive_replaced_document')) {
        meridiana_archive_replaced_document($post_id, $old_pdf_value, 'edit_document');
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
            wp_send_json_error(['message' => 'Email giÃ  presente nel database'], 400);
        }

        $created_user_id = wp_create_user($user_login, $user_pass, $user_email);
        if (is_wp_error($created_user_id)) {
            if ($created_user_id->get_error_code() === 'existing_user_email') {
                wp_send_json_error(['message' => 'Email giÃ  presente nel database'], 400);
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
            wp_send_json_error(['message' => 'Email giÃ  presente nel database'], 400);
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

    // ============================================
    // GESTISCI ENROLLMENT CORSI (LearnDash Nativo)
    // ============================================

    // Recupera i valori dei corsi dal form
    $tutti_corsi = isset($_POST['user_acf']['field_tutti_corsi']) && $_POST['user_acf']['field_tutti_corsi'] === '1';
    $corsi_selezionati = isset($_POST['corsi_assegnati']) && is_array($_POST['corsi_assegnati'])
        ? array_map('intval', $_POST['corsi_assegnati'])
        : [];

    // Recupera tutti i corsi disponibili
    $all_available_courses = get_posts([
        'post_type'      => 'sfwd-courses',
        'numberposts'    => -1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
    ]);

    // Determina quali corsi iscrivere
    $courses_to_enroll = [];
    if ($tutti_corsi) {
        // Se "Tutti i Corsi" è spuntato, usa tutti i corsi
        $courses_to_enroll = $all_available_courses;
    } else {
        // Altrimenti, usa solo i corsi selezionati
        $courses_to_enroll = $corsi_selezionati;
    }

    // Recupera i corsi attualmente iscritti (da LearnDash nativo)
    $currently_enrolled = meridiana_get_user_enrolled_course_ids($user_id);

    // Rimuovi iscrizioni che non sono più selezionate
    foreach ($currently_enrolled as $enrolled_course_id) {
        if (!in_array($enrolled_course_id, $courses_to_enroll)) {
            meridiana_unenroll_user($user_id, $enrolled_course_id);
        }
    }

    // Aggiungi nuove iscrizioni
    if (!empty($courses_to_enroll)) {
        foreach ($courses_to_enroll as $course_id) {
            if (!in_array($course_id, $currently_enrolled)) {
                meridiana_enroll_user($user_id, $course_id);
            }
        }
    }

    wp_send_json_success([
        'message' => 'Utente salvato con successo',
        'user_id' => $user_id,
    ]);
}

// ============================================
// GESTIONE NOTIFICHE MANUALI
// ============================================

/**
 * Salva una notifica nel database locale
 *
 * @param int $post_id ID del post/documento
 * @param string $post_type Tipo di post
 * @param int $sender_id ID dell'utente che ha creato la notifica
 * @param array $notification_data Dati della notifica (title, message, profiles, udos, send_to_all)
 * @return int|bool ID della notifica o false se fallisce
 */
function meridiana_save_notification_to_db($post_id, $post_type, $sender_id, $notification_data) {
    global $wpdb;

    $post = get_post($post_id);
    if (!$post) {
        return false;
    }

    $title = !empty($notification_data['title']) ? $notification_data['title'] : $post->post_title;
    $message = !empty($notification_data['message']) ? $notification_data['message'] :
               (!empty($post->post_excerpt) ? $post->post_excerpt : substr(wp_strip_all_tags($post->post_content), 0, 150));

    $table_name = $wpdb->prefix . 'meridiana_notifications';

    $inserted = $wpdb->insert(
        $table_name,
        [
            'post_id' => $post_id,
            'post_type' => $post_type,
            'sender_id' => $sender_id,
            'title' => $title,
            'message' => $message,
            'notification_type' => 'push',
            'segmentation_type' => !empty($notification_data['send_to_all']) ? 'all' : 'custom',
            'send_email' => 0,
            'created_at' => current_time('mysql'),
            'published_at' => current_time('mysql'),
        ],
        ['%d', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s']
    );

    if (!$inserted) {
        error_log('DB Error: Impossibile salvare notifica nel database. ' . $wpdb->last_error);
        return false;
    }

    $notification_id = $wpdb->insert_id;

    // Determina i destinatari
    $user_ids = [];

    if (!empty($notification_data['send_to_all'])) {
        // Manda a TUTTI gli utenti
        $all_users = get_users(['fields' => 'ID', 'number' => -1]);
        $user_ids = array_map('intval', $all_users);
    } else {
        // Filtra per Profilo e UDO
        $selected_profiles = !empty($notification_data['profiles']) ? (array)$notification_data['profiles'] : [];
        $selected_udos = !empty($notification_data['udos']) ? (array)$notification_data['udos'] : [];

        $selected_profiles = array_map('intval', array_filter($selected_profiles));
        $selected_udos = array_map('intval', array_filter($selected_udos));

        $user_ids = meridiana_get_users_by_segmentation($selected_profiles, $selected_udos);
    }

    // PROTEZIONE DUPLICATI: Deduplicare i user_ids (caso raro ma possibile)
    $user_ids = array_unique(array_map('intval', $user_ids));
    error_log('[Notification] User IDs after deduplication: ' . count($user_ids) . ' unique users');

    // Salva i destinatari nella tabella recipients
    $recipients_table = $wpdb->prefix . 'meridiana_notification_recipients';

    $inserted_count = 0;
    foreach ($user_ids as $user_id) {
        $result = $wpdb->insert(
            $recipients_table,
            [
                'notification_id' => $notification_id,
                'user_id' => $user_id,
                'read_at' => null,
                'email_sent' => 0,
                'created_at' => current_time('mysql'),
            ],
            ['%d', '%d', null, '%d', '%s']
        );

        // Se fallisce per UNIQUE constraint, ignora (utente già ha questa notifica)
        if ($result) {
            $inserted_count++;
        } elseif (strpos($wpdb->last_error, 'Duplicate entry') !== false) {
            error_log('[Notification] Skipped duplicate recipient: notification_id=' . $notification_id . ', user_id=' . $user_id);
        } else {
            error_log('[Notification] ERROR inserting recipient: ' . $wpdb->last_error);
        }
    }

    error_log('[Notification] Inserted ' . $inserted_count . ' recipients out of ' . count($user_ids) . ' users');

    return $notification_id;
}

/**
 * Recupera gli ID degli utenti filtrati per Profilo e UDO
 *
 * @param array $profile_ids Array di term IDs dei profili
 * @param array $udo_ids Array di term IDs delle UDO
 * @return array Array di user IDs
 */
function meridiana_get_users_by_segmentation($profile_ids = [], $udo_ids = []) {
    $user_ids = [];

    error_log('=== SEGMENTAZIONE DEBUG ===');
    error_log('Profile IDs: ' . implode(', ', $profile_ids));
    error_log('UDO IDs: ' . implode(', ', $udo_ids));

    // OPTIMIZZAZIONE: Se non ci sono filtri, ritorna tutti gli utenti
    if (empty($profile_ids) && empty($udo_ids)) {
        error_log('No filters applied - returning all users');
        $users = get_users(['fields' => 'ID', 'number' => -1]);
        $user_ids = array_map('intval', wp_list_pluck($users, 'ID'));
        error_log('Total users returned: ' . count($user_ids));
        return $user_ids;
    }

    // OPTIMIZZAZIONE: Usa meta_query instead di get_field() loop
    // Questo riduce il tempo da 3-4 secondi a 200ms!
    $meta_query = ['relation' => 'AND'];

    if (!empty($profile_ids)) {
        $profile_ids = array_map('intval', array_filter($profile_ids));
        if (!empty($profile_ids)) {
            $meta_query[] = [
                'key' => 'profilo_professionale',
                'value' => $profile_ids,
                'compare' => 'IN',
                'type' => 'NUMERIC'
            ];
            error_log('Added profile filter: ' . count($profile_ids) . ' profiles');
        }
    }

    if (!empty($udo_ids)) {
        $udo_ids = array_map('intval', array_filter($udo_ids));
        if (!empty($udo_ids)) {
            $meta_query[] = [
                'key' => 'udo_riferimento',
                'value' => $udo_ids,
                'compare' => 'IN',
                'type' => 'NUMERIC'
            ];
            error_log('Added UDO filter: ' . count($udo_ids) . ' UDOs');
        }
    }

    // Se non ci sono filtri validi dopo sanitization, non ritornare nulla
    if (count($meta_query) <= 1) {
        error_log('No valid filters after sanitization');
        return [];
    }

    // Query singola al database - MOLTO più veloce!
    error_log('Executing optimized WP_User_Query with meta_query');
    $start_time = microtime(true);

    $user_query = new WP_User_Query([
        'fields' => 'ID',
        'number' => -1,
        'meta_query' => $meta_query,
    ]);

    $elapsed = microtime(true) - $start_time;
    error_log('Query completed in ' . round($elapsed * 1000, 2) . 'ms');

    $user_ids = array_map('intval', $user_query->get_results());

    error_log('Total users matching criteria: ' . count($user_ids));

    return $user_ids;
}

/**
 * Gestisce l'invio di notifiche manuali per documenti (Protocolli/Moduli/ecc)
 *
 * @param int $post_id ID del documento pubblicato
 * @param string $post_type Tipo di post
 */
function meridiana_handle_document_notification($post_id, $post_type = 'protocollo') {
    // LOG: Inizio processamento notifiche
    error_log('=== NOTIFICA DEBUG: Inizio processamento ===');
    error_log('Post ID: ' . $post_id . ', Post Type: ' . $post_type);
    error_log('POST data keys: ' . implode(', ', array_keys($_POST)));
    error_log('Full POST data: ' . json_encode($_POST, JSON_PRETTY_PRINT));

    // Verifica se la notifica è abilitata nel form
    $send_notification = isset($_POST['send_notification']) ? intval($_POST['send_notification']) : 0;
    error_log('send_notification value: ' . $send_notification);
    error_log('send_notification isset: ' . (isset($_POST['send_notification']) ? 'TRUE' : 'FALSE'));

    if (!$send_notification) {
        error_log('Notifiche disabilitate, return');
        return; // Notifica disabilitata
    }

    $post = get_post($post_id);
    if (!$post) {
        return;
    }

    // Recupera i dati della notifica
    $selected_profiles = isset($_POST['notification_profiles']) ? $_POST['notification_profiles'] : [];
    $selected_udos = isset($_POST['notification_udos']) ? $_POST['notification_udos'] : [];
    $send_to_all = isset($_POST['notification_send_to_all']) ? intval($_POST['notification_send_to_all']) : 0;

    if (!is_array($selected_profiles)) {
        $selected_profiles = $selected_profiles ? [$selected_profiles] : [];
    }
    if (!is_array($selected_udos)) {
        $selected_udos = $selected_udos ? [$selected_udos] : [];
    }

    // Sanitizza
    $selected_profiles = array_map('intval', array_filter($selected_profiles));
    $selected_udos = array_map('intval', array_filter($selected_udos));

    // Prepara i dati della notifica
    $notification_title = sprintf('Nuovo %s: %s', ucfirst($post_type), $post->post_title);
    $notification_message = !empty($post->post_excerpt) ? $post->post_excerpt : substr(wp_strip_all_tags($post->post_content), 0, 150);

    $notification_data = [
        'title' => $notification_title,
        'message' => $notification_message,
        'profiles' => $selected_profiles,
        'udos' => $selected_udos,
        'send_to_all' => $send_to_all,
    ];

    // Salva nel database locale
    error_log('Dati notifica da salvare:');
    error_log('  Title: ' . $notification_data['title']);
    error_log('  Selected Profiles: ' . implode(', ', $selected_profiles));
    error_log('  Selected UDOs: ' . implode(', ', $selected_udos));
    error_log('  Send to All: ' . $notification_data['send_to_all']);

    $notification_id = meridiana_save_notification_to_db($post_id, $post_type, get_current_user_id(), $notification_data);

    if (!$notification_id) {
        error_log('Notifica: Errore salvataggio nel database locale');
        return;
    }

    error_log('Notifica salvata con ID: ' . $notification_id);

    // Le notifiche esterne (push) sono gestite dal plugin PushNotifications.io
    // Qui salviamo solo nel DB per la campanella interna
}

/**
 * Renderizza i campi di segmentazione per le notifiche (Profilo + UDO + Manda a Tutti)
 *
 * @param array $post_taxonomy_data Dati di tassonomia già presenti nel form (per default)
 *        Es: ['profiles' => [1, 2], 'udos' => [3, 4]]
 */
function meridiana_render_notification_segmentation_fields($post_taxonomy_data = []) {
    $default_profiles = isset($post_taxonomy_data['profiles']) ? (array)$post_taxonomy_data['profiles'] : [];
    $default_udos = isset($post_taxonomy_data['udos']) ? (array)$post_taxonomy_data['udos'] : [];

    // Recupera tutti i profili professionali
    $all_profiles = get_terms([
        'taxonomy' => 'profilo-professionale',
        'hide_empty' => false,
        'number' => 0,
    ]);

    // Recupera tutte le UDO
    $all_udos = get_terms([
        'taxonomy' => 'unita-offerta',
        'hide_empty' => false,
        'number' => 0,
    ]);

    ?>
    <div class="notification-segmentation-fields" style="border-top: 1px solid #ddd; padding-top: 20px; margin-top: 20px;">

        <!-- PROFILI PROFESSIONALI -->
        <div class="acf-field acf-field-select">
            <div class="acf-label">
                <label><?php esc_html_e('Profili Professionali Destinatari', 'meridiana-child'); ?></label>
                <p class="description"><?php esc_html_e('Seleziona uno o più profili. Lascia vuoto per inviare a TUTTI.', 'meridiana-child'); ?></p>
            </div>
            <div class="acf-input">
                <?php if (!empty($all_profiles) && !is_wp_error($all_profiles)): ?>
                    <select
                        id="notification_profiles"
                        name="notification_profiles[]"
                        class="select2-enable"
                        multiple
                        data-placeholder="<?php esc_attr_e('Lascia vuoto per TUTTI i profili', 'meridiana-child'); ?>"
                    >
                        <?php foreach ($all_profiles as $profile): ?>
                            <option
                                value="<?php echo esc_attr($profile->term_id); ?>"
                                <?php selected(in_array($profile->term_id, $default_profiles, true)); ?>
                            >
                                <?php echo esc_html($profile->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <p class="description"><?php esc_html_e('Nessun profilo professionale disponibile.', 'meridiana-child'); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- UDO (UNITÀ D'OFFERTA) -->
        <div class="acf-field acf-field-select">
            <div class="acf-label">
                <label><?php esc_html_e('Unità d\'Offerta Destinatarie', 'meridiana-child'); ?></label>
                <p class="description"><?php esc_html_e('Seleziona una o più UDO. Lascia vuoto per inviare a TUTTI.', 'meridiana-child'); ?></p>
            </div>
            <div class="acf-input">
                <?php if (!empty($all_udos) && !is_wp_error($all_udos)): ?>
                    <select
                        id="notification_udos"
                        name="notification_udos[]"
                        class="select2-enable"
                        multiple
                        data-placeholder="<?php esc_attr_e('Lascia vuoto per TUTTE le UDO', 'meridiana-child'); ?>"
                    >
                        <?php foreach ($all_udos as $udo): ?>
                            <option
                                value="<?php echo esc_attr($udo->term_id); ?>"
                                <?php selected(in_array($udo->term_id, $default_udos, true)); ?>
                            >
                                <?php echo esc_html($udo->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <p class="description"><?php esc_html_e('Nessuna UDO disponibile.', 'meridiana-child'); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- MANDA A TUTTI -->
        <div class="acf-field acf-field-true-false">
            <div class="acf-label">
                <label><?php esc_html_e('Manda a TUTTI gli Utenti', 'meridiana-child'); ?></label>
                <p class="description"><?php esc_html_e('Abilita questa opzione per ignorare i filtri di profilo/UDO e mandare a TUTTI gli utenti', 'meridiana-child'); ?></p>
            </div>
            <div class="acf-input">
                <div class="checkbox-field">
                    <input type="hidden" name="notification_send_to_all" value="0" />
                    <label class="checkbox-inline">
                        <input
                            type="checkbox"
                            id="notification_send_to_all"
                            name="notification_send_to_all"
                            value="1"
                        />
                        <span><?php esc_html_e('Sì, manda a TUTTI', 'meridiana-child'); ?></span>
                    </label>
                </div>
            </div>
        </div>

    </div>
    <?php
}

/**
 * Recupera gli ID degli utenti con determinati profili professionali
 *
 * @param array $profile_ids Array di term IDs dei profili
 * @return array Array di user IDs
 */
function meridiana_get_users_by_profiles($profile_ids) {
    if (empty($profile_ids) || !is_array($profile_ids)) {
        return [];
    }

    $user_ids = [];

    // Recupera gli utenti che hanno uno dei profili selezionati
    $users = get_users([
        'fields' => 'ID',
        'number' => -1,
    ]);

    foreach ($users as $user_id) {
        $user_profile = get_field('profilo_professionale', 'user_' . $user_id);

        if ($user_profile) {
            $profile_id = is_array($user_profile) ? $user_profile['term_id'] : $user_profile;

            if (in_array($profile_id, $profile_ids)) {
                $user_ids[] = $user_id;
            }
        }
    }

    return $user_ids;
}

// Funzione meridiana_send_onesignal_notification rimossa
// Le notifiche push esterne sono gestite dal plugin PushNotifications.io



