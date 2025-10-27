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

    <!-- CARD LAYOUT - Card List -->
    <div class="item-cards-container laptop-only">
        <?php 
        $salute_query->rewind_posts();
        while ($salute_query->have_posts()): $salute_query->the_post();
            $post_id = get_the_ID();
            $status = get_post_status($post_id);
            $updated_date = get_the_modified_date('d/m/Y');
            $risorse = get_field('risorse', $post_id);
            $risorse_count = is_array($risorse) ? count($risorse) : 0;
        ?>
        <div class="item-card">
            <div class="item-card__header" data-toggle="card-<?php echo $post_id; ?>">
                <div class="item-card__info">
                    <div class="item-card__title"><?php the_title(); ?></div>
                    <div class="item-card__meta">
                        <span class="badge <?php echo $status === 'publish' ? 'badge-success' : 'badge-warning'; ?>"><?php echo $status === 'publish' ? 'Pubblicato' : 'Bozza'; ?></span>
                        <?php
                        $categories = get_the_terms($post_id, 'category');
                        if (!is_wp_error($categories) && !empty($categories)) {
                            foreach (array_slice($categories, 0, 1) as $cat) {
                                echo '<span class="item-card__separator">•</span>';
                                echo '<span class="item-card__category-text">' . esc_html($cat->name) . '</span>';
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="item-card__actions-group">
                    <button class="btn-icon" @click.stop="openFormModal('salute', 'edit', <?php echo $post_id; ?>, null)" title="Modifica"><i data-lucide="edit-2"></i></button>
                    <button class="btn-icon" @click.stop="deleteSalute(<?php echo $post_id; ?>)" title="Elimina"><i data-lucide="trash-2"></i></button>
                    <a href="<?php the_permalink(); ?>" class="btn-icon" title="Visualizza" target="_blank"><i data-lucide="eye"></i></a>
                </div>
            </div>
            <div class="item-card__content" id="card-<?php echo $post_id; ?>">
            </div>
        </div>
        <?php endwhile; ?>
    </div>

<?php else: ?>
<div class="no-content"><i data-lucide="inbox"></i><p><?php esc_html_e('Nessun contenuto trovato', 'meridiana-child'); ?></p></div>
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
