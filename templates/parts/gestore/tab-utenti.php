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

    <!-- CARD LAYOUT - Card List -->
    <div class="users-cards-container laptop-only">
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
            
            $status_key = is_string($stato_value) ? $stato_value : '';
            $status_badge_class = $status_badge_classes[$status_key] ?? 'badge badge-sm badge-secondary';
        ?>
        <div class="user-card" data-user-id="<?php echo $user->ID; ?>">
            <div class="user-card__header" data-toggle="card-<?php echo $user->ID; ?>">
                <div class="user-card__info">
                    <div class="user-card__title"><?php echo esc_html($full_name); ?></div>
                    <div class="user-card__meta">
                        <?php if ($stato_label): ?>
                            <span class="user-card__badge <?php echo esc_attr($status_badge_class); ?>"><?php echo esc_html($stato_label); ?></span>
                        <?php endif; ?>
                        <?php if ($profilo): ?>
                            <span class="user-card__separator">•</span>
                            <span class="user-card__profilo-text"><?php echo esc_html($profilo); ?></span>
                        <?php endif; ?>
                        <?php if ($udo): ?>
                            <span class="user-card__separator">•</span>
                            <span class="user-card__udo-text"><?php echo esc_html($udo); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="user-card__actions-group">
                    <button class="btn-icon" @click.stop="openFormModal('utenti', 'edit', <?php echo $user->ID; ?>)" title="Modifica"><i data-lucide="edit-2"></i></button>
                    <button class="btn-icon" @click.stop="resetUserPassword(<?php echo $user->ID; ?>)" title="Reset Password"><i data-lucide="key"></i></button>
                    <button class="btn-icon" @click.stop="deleteUser(<?php echo $user->ID; ?>)" title="Elimina"><i data-lucide="trash-2"></i></button>
                    <button class="user-card__toggle" aria-label="Espandi dettagli"><i data-lucide="chevron-down"></i></button>
                </div>
            </div>
            <div class="user-card__content" id="card-<?php echo $user->ID; ?>">
                <div class="user-card__row">
                    <span class="user-card__label">Email</span>
                    <span class="user-card__value"><?php echo esc_html($user->user_email); ?></span>
                </div>
                <div class="user-card__row">
                    <span class="user-card__label">Link Autologin</span>
                    <span class="user-card__value">
                        <?php if (!empty($link_autologin)): ?>
                            <a href="<?php echo esc_url($link_autologin); ?>" class="link-status link-status--available" target="_blank" rel="noopener noreferrer">
                                <i data-lucide="arrow-up-right"></i> Apri
                            </a>
                        <?php else: ?>
                            <span class="text-gray-500">Non disponibile</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="user-card__row">
                    <span class="user-card__label">Codice Fiscale</span>
                    <span class="user-card__value"><?php echo $codice_fiscale ? esc_html($codice_fiscale) : 'N/D'; ?></span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

<?php else: ?>
<div class="no-content"><i data-lucide="inbox"></i><p>Nessun utente trovato</p></div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle card content on laptop view
    document.querySelectorAll('.user-card__header').forEach(header => {
        header.addEventListener('click', function() {
            const cardId = this.getAttribute('data-toggle');
            const content = document.getElementById(cardId);
            const toggle = this.querySelector('.user-card__toggle');
            
            if (content && toggle) {
                content.classList.toggle('open');
                toggle.classList.toggle('open');
            }
        });
    });
});
</script>
