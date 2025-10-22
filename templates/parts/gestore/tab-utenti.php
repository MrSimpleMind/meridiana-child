<?php
$users_query = new WP_User_Query(['orderby' => 'display_name', 'order' => 'ASC', 'number' => 50]);
$users = $users_query->get_results();
?>
<div class="tab-header">
    <h2>Utenti</h2>
    <button class="btn btn-primary" @click="openFormModal('utenti', 'new')"><i data-lucide="plus"></i> Nuovo Utente</button>
</div>
<?php if (!empty($users)): ?>
<div class="tab-table-wrapper">
    <table class="dashboard-table">
        <thead><tr><th>Nome</th><th>Email</th><th>Profilo</th><th>Stato</th><th>Azioni</th></tr></thead>
        <tbody>
            <?php foreach ($users as $user): $profilo = get_field('profilo_professionale', 'user_' . $user->ID); $stato = get_field('stato_utente', 'user_' . $user->ID); ?>
            <tr>
                <td class="title-cell"><strong><?php echo esc_html($user->first_name . ' ' . $user->last_name); ?></strong></td>
                <td><?php echo esc_html($user->user_email); ?></td>
                <td><?php echo $profilo ? esc_html($profilo) : '—'; ?></td>
                <td><?php echo $stato ? esc_html($stato) : '—'; ?></td>
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
