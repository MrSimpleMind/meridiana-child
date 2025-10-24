<?php
$users_query = new WP_User_Query([
    'orderby' => 'display_name',
    'order' => 'ASC',
    'number' => 50,
]);
$users = $users_query->get_results();

$default_stato_choices = [
    'attivo' => 'Attivo',
    'sospeso' => 'Sospeso',
    'licenziato' => 'Licenziato',
];
$stato_field_def = function_exists('acf_get_field') ? acf_get_field('field_stato_utente') : null;
$stato_choices = is_array($stato_field_def) && !empty($stato_field_def['choices']) ? $stato_field_def['choices'] : $default_stato_choices;

$status_badge_classes = [
    'attivo' => 'badge badge-sm badge-status-active',
    'sospeso' => 'badge badge-sm badge-status-pending',
    'licenziato' => 'badge badge-sm badge-status-expired',
];
?>
<div class="tab-header">
    <h2>Utenti</h2>
    <button class="btn btn-primary" @click="openFormModal('utenti', 'new')"><i data-lucide="plus"></i> Nuovo Utente</button>
</div>
<?php if (!empty($users)): ?>
<div class="tab-table-wrapper">
    <table class="dashboard-table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Stato</th>
                <th>Profilo</th>
                <th>Unita di Offerta</th>
                <th>Link Autologin</th>
                <th>Codice Fiscale</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user):
                $user_meta_key = 'user_' . $user->ID;
                $full_name = trim($user->first_name . ' ' . $user->last_name);
                if ($full_name === '') {
                    $full_name = $user->display_name ?: $user->user_login;
                }
                $stato_field = get_field_object('field_stato_utente', $user_meta_key);
                $stato_value = is_array($stato_field) ? ($stato_field['value'] ?? '') : '';
                $stato_label = $stato_value && isset($stato_field['choices'][$stato_value])
                    ? $stato_field['choices'][$stato_value]
                    : ($stato_value && isset($stato_choices[$stato_value]) ? $stato_choices[$stato_value] : '');
                $profilo = get_field('profilo_professionale', $user_meta_key);
                $udo = get_field('udo_riferimento', $user_meta_key);
                $link_autologin = get_field('link_autologin_esterno', $user_meta_key);
                $codice_fiscale = get_field('codice_fiscale', $user_meta_key);
            ?>
            <tr>
                <td class="title-cell"><strong><?php echo esc_html($full_name); ?></strong></td>
                <td><?php echo esc_html($user->user_email); ?></td>
                <td>
                    <?php if ($stato_label): ?>
                        <?php
                        $status_key = is_string($stato_value) ? $stato_value : '';
                        $status_badge_class = $status_badge_classes[$status_key] ?? 'badge badge-sm badge-secondary';
                        ?>
                        <span class="<?php echo esc_attr($status_badge_class); ?>"><?php echo esc_html($stato_label); ?></span>
                    <?php else: ?>
                        N/D
                    <?php endif; ?>
                </td>
                <td><?php if ($profilo) echo meridiana_get_badge('category', $profilo); ?></td>
                <td><?php if ($udo) echo meridiana_get_badge('category', $udo); ?></td>
                <td class="link-status-cell">
                    <?php if (!empty($link_autologin)): ?>
                        <a href="<?php echo esc_url($link_autologin); ?>" class="link-status link-status--available" target="_blank" rel="noopener noreferrer" aria-label="Apri link autologin">
                            <i data-lucide="arrow-up-right"></i><span class="sr-only">Link autologin</span>
                        </a>
                    <?php else: ?>
                        <span class="link-status link-status--missing" aria-label="Link autologin non disponibile">
                            <i data-lucide="x-circle"></i><span class="sr-only">Nessun link</span>
                        </span>
                    <?php endif; ?>
                </td>
                <td><?php echo $codice_fiscale ? esc_html($codice_fiscale) : 'N/D'; ?></td>
                <td class="actions-cell">
                    <button class="btn-icon" @click="openFormModal('utenti', 'edit', <?php echo $user->ID; ?>)" title="Modifica"><i data-lucide="edit-2"></i></button>
                    <button class="btn-icon" @click="resetUserPassword(<?php echo $user->ID; ?>)" title="Reset Password"><i data-lucide="key"></i></button>
                    <button class="btn-icon" @click="deleteUser(<?php echo $user->ID; ?>)" title="Elimina"><i data-lucide="trash-2"></i></button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="no-content"><i data-lucide="inbox"></i><p>Nessun utente trovato</p></div>
<?php endif; ?>
