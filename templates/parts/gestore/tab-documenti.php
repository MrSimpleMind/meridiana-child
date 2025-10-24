<?php
$documenti_query = new WP_Query(['post_type' => ['protocollo', 'modulo'], 'posts_per_page' => 50, 'orderby' => 'date', 'order' => 'DESC']);
?>
<div class="tab-header">
    <h2>Documentazione</h2>
    <div style="display: flex; gap: 12px;">
        <button class="btn btn-primary" @click="openFormModal('documenti', 'new', 0, 'modulo')" style="flex: 1;"><i data-lucide="plus"></i> Nuovo Modulo</button>
        <button class="btn btn-primary" @click="openFormModal('documenti', 'new', 0, 'protocollo')" style="flex: 1;"><i data-lucide="plus"></i> Nuovo Protocollo</button>
    </div>
</div>
<?php if ($documenti_query->have_posts()): ?>
<div class="tab-table-wrapper">
    <table class="dashboard-table">
        <thead><tr><th>Titolo</th><th>Tipo</th><th>Data</th><th>Status</th><th>Azioni</th></tr></thead>
        <tbody>
            <?php while ($documenti_query->have_posts()): $documenti_query->the_post(); ?>
            <tr>
                <td class="title-cell"><strong><?php the_title(); ?></strong></td>
                <td>
                    <?php
                    $post_type = get_post_type();
                    $is_ats = ($post_type === 'protocollo') && get_field('pianificazione_ats', get_the_ID());

                    // Mostra sempre il badge del tipo principale
                    $type_label = $post_type === 'protocollo' ? 'Protocollo' : 'Modulo';
                    echo meridiana_get_badge($post_type, $type_label);

                    // Se è un protocollo ATS, aggiungi il badge ATS
                    if ($is_ats) {
                        echo meridiana_get_badge('ats', 'ATS');
                    }
                    ?>
                </td>
                <td class="date-cell"><?php echo get_the_date('d/m/Y'); ?></td>
                <td><span class="badge <?php echo get_post_status() === 'publish' ? 'badge-success' : 'badge-warning'; ?>"><?php echo ucfirst(get_post_status()); ?></span></td>
                <td class="actions-cell">
                    <button class="btn-icon" @click="openFormModal('documenti', 'edit', <?php echo get_the_ID(); ?>, '<?php echo get_post_type(); ?>')" title="Modifica"><i data-lucide="edit-2"></i></button>
                    <button class="btn-icon" @click="deletePost(<?php echo get_the_ID(); ?>)" title="Elimina"><i data-lucide="trash-2"></i></button>
                    <a href="<?php the_permalink(); ?>" class="btn-icon" title="Visualizza" target="_blank"><i data-lucide="eye"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="no-content"><i data-lucide="inbox"></i><p>Nessun documento trovato</p></div>
<?php endif; wp_reset_postdata(); ?>
