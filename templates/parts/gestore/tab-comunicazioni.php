<?php
$comunicazioni_query = new WP_Query([
    'post_type' => 'post',
    'posts_per_page' => 50,
    'orderby' => 'date',
    'order' => 'DESC',
    'post_status' => ['publish', 'draft'],
]);
?>
<div class="tab-header">
    <h2>Comunicazioni</h2>
    <button class="btn btn-primary" @click="openFormModal('comunicazioni', 'new', 0, null)"><i data-lucide="plus"></i> Nuova Comunicazione</button>
</div>

<?php if ($comunicazioni_query->have_posts()): ?>

    <!-- DESKTOP (> 1024px) -->
    <div class="tab-table-wrapper desktop-only">
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

    <!-- LAPTOP (<= 1024px) -->
    <div class="item-cards-container laptop-only">
        <?php 
        $comunicazioni_query->rewind_posts();
        while ($comunicazioni_query->have_posts()): $comunicazioni_query->the_post();
            $categories = get_the_category();
            $status = get_post_status();
        ?>
        <div class="item-card">
            <div class="item-card__header" data-toggle="card-<?php echo get_the_ID(); ?>">
                <div class="item-card__info">
                    <div class="item-card__title"><?php the_title(); ?></div>
                    <div class="item-card__meta">
                        <?php if (!empty($categories)): ?>
                            <?php foreach (array_slice($categories, 0, 1) as $cat): ?>
                                <span class="item-card__type type-post"><?php echo esc_html($cat->name); ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <span class="item-card__divider">•</span>
                        <span class="item-card__date"><?php echo get_the_date('d/m/Y'); ?></span>
                    </div>
                </div>
                <button class="item-card__toggle" aria-label="Espandi"><i data-lucide="chevron-down"></i></button>
            </div>
            <div class="item-card__content" id="card-<?php echo get_the_ID(); ?>">
                <div class="item-card__row">
                    <span class="item-card__label">Status</span>
                    <span class="item-card__value"><?php echo meridiana_get_status_badge(get_the_ID()); ?></span>
                </div>
            </div>
            <div class="item-card__actions">
                <button class="btn-icon" @click="openFormModal('comunicazioni', 'edit', <?php echo get_the_ID(); ?>, null)" title="Modifica"><i data-lucide="edit-2"></i></button>
                <button class="btn-icon" @click="deleteComunicazione(<?php echo get_the_ID(); ?>)" title="Elimina"><i data-lucide="trash-2"></i></button>
                <a href="<?php the_permalink(); ?>" class="btn-icon" title="Visualizza" target="_blank"><i data-lucide="eye"></i></a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

<?php else: ?>
<div class="no-content"><i data-lucide="inbox"></i><p>Nessuna comunicazione trovata</p></div>
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
