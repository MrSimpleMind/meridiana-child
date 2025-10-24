<?php
$comunicazioni_query = new WP_Query([
    'post_type' => 'post',
    'posts_per_page' => 50,
    'orderby' => 'date',
    'order' => 'DESC',
]);
?>
<div class="tab-header">
    <h2>Comunicazioni</h2>
    <button class="btn btn-primary" @click="openFormModal('comunicazioni', 'new', 0, null)"><i data-lucide="plus"></i> Nuova Comunicazione</button>
</div>
<?php if ($comunicazioni_query->have_posts()): ?>
<div class="tab-table-wrapper">
    <table class="dashboard-table">
        <thead><tr><th>Titolo</th><th>Categoria</th><th>Data</th><th>Status</th><th>Azioni</th></tr></thead>
        <tbody>
            <?php while ($comunicazioni_query->have_posts()): $comunicazioni_query->the_post(); ?>
            <tr>
                <td class="title-cell"><strong><?php the_title(); ?></strong></td>
                <td>
                    <?php 
                    $categories = get_the_category();
                    if (!empty($categories)) {
                        foreach ($categories as $category) {
                            echo meridiana_get_badge('category', $category->name);
                        }
                    }
                    ?>
                </td>
                <td class="date-cell"><?php echo get_the_date('d/m/Y'); ?></td>
                <td><?php echo meridiana_get_status_badge(get_the_ID()); ?></td>
                <td class="actions-cell">
                    <button class="btn-icon" @click="openFormModal('comunicazioni', 'edit', <?php echo get_the_ID(); ?>, null)" title="Modifica"><i data-lucide="edit-2"></i></button>
                    <button class="btn-icon" @click="deleteComunicazione(<?php echo get_the_ID(); ?>)" title="Elimina"><i data-lucide="trash-2"></i></button>
                    <a href="<?php the_permalink(); ?>" class="btn-icon" title="Visualizza" target="_blank"><i data-lucide="eye"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="no-content"><i data-lucide="inbox"></i><p>Nessuna comunicazione trovata</p></div>
<?php endif; wp_reset_postdata(); ?>


