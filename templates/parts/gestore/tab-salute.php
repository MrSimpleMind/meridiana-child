<?php
$salute_query = new WP_Query([
    'post_type' => 'salute-e-benessere-l',
    'posts_per_page' => 50,
    'orderby' => 'date',
    'order' => 'DESC',
]);
?>
<div class="tab-header">
    <h2>Salute & Benessere</h2>
    <button class="btn btn-primary" @click="openFormModal('salute', 'new', 0, null)"><i data-lucide="plus"></i> Nuovo Articolo</button>
</div>
<?php if ($salute_query->have_posts()): ?>
<div class="tab-table-wrapper">
    <table class="dashboard-table">
        <thead><tr><th>Titolo</th><th>Categorie</th><th>Stato</th><th>Aggiornato</th><th>Risorse</th><th>Azioni</th></tr></thead>
        <tbody>
            <?php while ($salute_query->have_posts()): $salute_query->the_post(); ?>
            <?php
                $post_id = get_the_ID();
                $categories = get_the_terms($post_id, 'category');
                $category_names = !is_wp_error($categories) && !empty($categories) ? wp_list_pluck($categories, 'name') : [];
                $category_display = !empty($category_names) ? implode(', ', $category_names) : '';
                $status = get_post_status($post_id);
                $status_class = $status === 'publish' ? 'badge-success' : 'badge-warning';
                $status_label = $status === 'publish' ? __('Pubblicato', 'meridiana-child') : __('Bozza', 'meridiana-child');
                $updated_date = get_the_modified_date('d/m/Y');
                $risorse = get_field('risorse', $post_id);
                $risorse_count = is_array($risorse) ? count($risorse) : 0;
            ?>
            <tr>
                <td class="title-cell"><strong><?php the_title(); ?></strong></td>
                <td>
                    <?php
                    $categories = get_the_terms($post_id, 'category');
                    if (!is_wp_error($categories) && !empty($categories)) {
                        foreach ($categories as $category) {
                            echo meridiana_get_badge('category', $category->name);
                        }
                    }
                    ?>
                </td>
                <td><?php echo meridiana_get_status_badge($post_id); ?></td>
                <td class="date-cell"><?php echo esc_html($updated_date); ?></td>
                <td><?php echo esc_html(number_format_i18n($risorse_count)); ?></td>
                <td class="actions-cell">
                    <button class="btn-icon" @click="openFormModal('salute', 'edit', <?php echo $post_id; ?>, null)" title="Modifica"><i data-lucide="edit-2"></i></button>
                    <button class="btn-icon" @click="deleteSalute(<?php echo $post_id; ?>)" title="Elimina"><i data-lucide="trash-2"></i></button>
                    <a href="<?php the_permalink(); ?>" class="btn-icon" title="Visualizza" target="_blank"><i data-lucide="eye"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="no-content"><i data-lucide="inbox"></i><p><?php esc_html_e('Nessun contenuto trovato', 'meridiana-child'); ?></p></div>
<?php endif; wp_reset_postdata(); ?>

