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

    <!-- DESKTOP (> 1024px) -->
    <div class="tab-table-wrapper desktop-only">
        <table class="dashboard-table">
            <thead><tr><th>Titolo</th><th>Categorie</th><th>Stato</th><th>Aggiornata</th><th>Azioni</th></tr></thead>
            <tbody>
                <?php while ($convenzioni_query->have_posts()): $convenzioni_query->the_post(); ?>
                <?php
                    $post_id = get_the_ID();
                    $is_active = (bool) get_field('convenzione_attiva', $post_id);
                    $updated_date = get_the_modified_date('d/m/Y');
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
                    <td><span class="badge <?php echo $is_active ? 'badge-success' : 'badge-warning'; ?>"><?php echo $is_active ? 'Attiva' : 'Scaduta'; ?></span></td>
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

    <!-- LAPTOP (<= 1024px) -->
    <div class="item-cards-container laptop-only">
        <?php 
        $convenzioni_query->rewind_posts();
        while ($convenzioni_query->have_posts()): $convenzioni_query->the_post();
            $post_id = get_the_ID();
            $is_active = (bool) get_field('convenzione_attiva', $post_id);
            $updated_date = get_the_modified_date('d/m/Y');
        ?>
        <div class="item-card">
            <div class="item-card__header" data-toggle="card-<?php echo $post_id; ?>">
                <div class="item-card__info">
                    <div class="item-card__title"><?php the_title(); ?></div>
                    <div class="item-card__meta">
                        <span class="item-card__type type-convenzione"><?php echo $is_active ? 'Attiva' : 'Scaduta'; ?></span>
                        <span class="item-card__divider">•</span>
                        <span class="item-card__date"><?php echo esc_html($updated_date); ?></span>
                    </div>
                </div>
                <button class="item-card__toggle" aria-label="Espandi"><i data-lucide="chevron-down"></i></button>
            </div>
            <div class="item-card__content" id="card-<?php echo $post_id; ?>">
                <div class="item-card__row">
                    <span class="item-card__label">Categorie</span>
                    <span class="item-card__value">
                        <?php
                        $categories = get_the_terms($post_id, 'category');
                        if (!is_wp_error($categories) && !empty($categories)) {
                            echo esc_html(implode(', ', wp_list_pluck($categories, 'name')));
                        } else {
                            echo 'N/D';
                        }
                        ?>
                    </span>
                </div>
            </div>
            <div class="item-card__actions">
                <button class="btn-icon" @click="openFormModal('convenzioni', 'edit', <?php echo $post_id; ?>, null)" title="Modifica"><i data-lucide="edit-2"></i></button>
                <button class="btn-icon" @click="deleteConvenzione(<?php echo $post_id; ?>)" title="Elimina"><i data-lucide="trash-2"></i></button>
                <a href="<?php the_permalink(); ?>" class="btn-icon" title="Visualizza" target="_blank"><i data-lucide="eye"></i></a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

<?php else: ?>
<div class="no-content"><i data-lucide="inbox"></i><p><?php esc_html_e('Nessuna convenzione trovata', 'meridiana-child'); ?></p></div>
<?php endif; wp_reset_postdata(); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.item-card__header').forEach(header => {
        header.addEventListener('click', function() {
            const cardId = this.getAttribute('data-toggle');
            const content = document.getElementById(cardId);
            const toggle = this.querySelector('.item-card__toggle');
            if (content && toggle) {
                content.classList.toggle('open');
                toggle.classList.toggle('open');
            }
        });
    });
});
</script>
