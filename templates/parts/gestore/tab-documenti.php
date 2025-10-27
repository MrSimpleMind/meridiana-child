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

    <!-- DESKTOP VIEW (> 1024px) - Tabella -->
    <div class="tab-table-wrapper desktop-only">
        <table class="dashboard-table">
            <thead><tr><th>Titolo</th><th>Tipo</th><th>Data</th><th>Status</th><th>Azioni</th></tr></thead>
            <tbody>
                <?php while ($documenti_query->have_posts()): $documenti_query->the_post(); ?>
                <tr>
                    <td class="title-cell"><strong><?php the_title(); ?></strong></td>
                    <td class="is-center">
                        <?php
                        $post_type = get_post_type();
                        $is_ats = ($post_type === 'protocollo') && get_field('pianificazione_ats', get_the_ID());
                        $type_label = $post_type === 'protocollo' ? 'Protocollo' : 'Modulo';
                        echo meridiana_get_badge($post_type, $type_label);
                        if ($is_ats) {
                            echo meridiana_get_badge('ats', 'ATS');
                        }
                        ?>
                    </td>
                    <td class="is-center"><?php echo get_the_date('d/m/Y'); ?></td>
                    <td class="is-center"><span class="badge <?php echo get_post_status() === 'publish' ? 'badge-success' : 'badge-warning'; ?>"><?php echo ucfirst(get_post_status()); ?></span></td>
                    <td class="is-center">
                        <button class="btn-icon" @click="openFormModal('documenti', 'edit', <?php echo get_the_ID(); ?>, '<?php echo get_post_type(); ?>')" title="Modifica"><i data-lucide="edit-2"></i></button>
                        <button class="btn-icon" @click="deletePost(<?php echo get_the_ID(); ?>)" title="Elimina"><i data-lucide="trash-2"></i></button>
                        <a href="<?php the_permalink(); ?>" class="btn-icon" title="Visualizza" target="_blank"><i data-lucide="eye"></i></a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- LAPTOP VIEW (<= 1024px) - Card Layout -->
    <div class="item-cards-container laptop-only">
        <?php 
        $documenti_query->rewind_posts();
        while ($documenti_query->have_posts()): $documenti_query->the_post();
            $post_type = get_post_type();
            $is_ats = ($post_type === 'protocollo') && get_field('pianificazione_ats', get_the_ID());
            $type_label = $post_type === 'protocollo' ? 'Protocollo' : 'Modulo';
            $status = get_post_status();
            $status_class = $status === 'publish' ? 'badge-success' : 'badge-warning';
        ?>
        <div class="item-card" data-item-id="<?php echo get_the_ID(); ?>">
            <div class="item-card__header" data-toggle="card-<?php echo get_the_ID(); ?>">
                <div class="item-card__info">
                    <div class="item-card__title"><?php the_title(); ?></div>
                    <div class="item-card__meta">
                        <span class="item-card__type <?php echo esc_attr('type-' . $post_type); ?>"><?php echo esc_html($type_label); ?></span>
                        <?php if ($is_ats): ?>
                            <span class="item-card__type type-ats">ATS</span>
                        <?php endif; ?>
                        <span class="item-card__divider">•</span>
                        <span class="item-card__date"><?php echo get_the_date('d/m/Y'); ?></span>
                    </div>
                </div>
                <button class="item-card__toggle" aria-label="Espandi dettagli"><i data-lucide="chevron-down"></i></button>
            </div>
            <div class="item-card__content" id="card-<?php echo get_the_ID(); ?>">
                <div class="item-card__row">
                    <span class="item-card__label">Status</span>
                    <span class="item-card__value"><span class="badge <?php echo esc_attr($status_class); ?>"><?php echo ucfirst($status); ?></span></span>
                </div>
            </div>
            <div class="item-card__actions">
                <button class="btn-icon" @click="openFormModal('documenti', 'edit', <?php echo get_the_ID(); ?>, '<?php echo esc_attr($post_type); ?>')" title="Modifica"><i data-lucide="edit-2"></i></button>
                <button class="btn-icon" @click="deletePost(<?php echo get_the_ID(); ?>)" title="Elimina"><i data-lucide="trash-2"></i></button>
                <a href="<?php the_permalink(); ?>" class="btn-icon" title="Visualizza" target="_blank"><i data-lucide="eye"></i></a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

<?php else: ?>
<div class="no-content"><i data-lucide="inbox"></i><p>Nessun documento trovato</p></div>
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
