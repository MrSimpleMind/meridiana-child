<?php
$convenzioni_query = new WP_Query([
    'post_type' => 'convenzione',
    'posts_per_page' => 50,
    'orderby' => 'date',
    'order' => 'DESC',
]);
?>
<div class="tab-header">
    <h2>Convenzioni</h2>
    <button class="btn btn-primary" @click="openFormModal('convenzioni', 'new', 0, null)"><i data-lucide="plus"></i> Nuova Convenzione</button>
</div>
<?php if ($convenzioni_query->have_posts()): ?>
<div class="tab-table-wrapper">
    <table class="dashboard-table">
        <thead><tr><th>Titolo</th><th>Categorie</th><th>Stato</th><th>Aggiornata</th><th>Azioni</th></tr></thead>
        <tbody>
            <?php while ($convenzioni_query->have_posts()): $convenzioni_query->the_post(); ?>
            <?php
                $post_id = get_the_ID();
                $is_active = (bool) get_field('convenzione_attiva', $post_id);
                $status_label = $is_active ? __('Attiva', 'meridiana-child') : __('Scaduta', 'meridiana-child');
                $status_class = $is_active ? 'badge-success' : 'badge-warning';
                $categories = get_the_terms($post_id, 'category');
                $category_names = !is_wp_error($categories) && !empty($categories) ? wp_list_pluck($categories, 'name') : [];
                $category_display = !empty($category_names) ? implode(', ', $category_names) : '';
                $updated_date = get_the_modified_date('d/m/Y');
            ?>
            <tr>
                <td class="title-cell"><strong><?php the_title(); ?></strong></td>
                <td>
                    <?php if ($category_display): ?>
                        <?php echo esc_html($category_display); ?>
                    <?php else: ?>
                        <span class="badge badge-info"><?php esc_html_e('Senza categoria', 'meridiana-child'); ?></span>
                    <?php endif; ?>
                </td>
                <td><span class="badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span></td>
                <td class="date-cell"><?php echo esc_html($updated_date); ?></td>
                <td class="actions-cell">
                    <button class="btn-icon" @click="openFormModal('convenzioni', 'edit', <?php echo $post_id; ?>, null)" title="Modifica"><i data-lucide="edit-2"></i></button>
                    <button class="btn-icon" @click="deleteConvenzione(<?php echo $post_id; ?>)" title="Elimina"><i data-lucide="trash-2"></i></button>
                    <a href="<?php the_permalink(); ?>" class="btn-icon" title="Visualizza" target="_blank"><i data-lucide="eye"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="no-content"><i data-lucide="inbox"></i><p><?php esc_html_e('Nessuna convenzione trovata', 'meridiana-child'); ?></p></div>
<?php endif; wp_reset_postdata(); ?>
