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

    <!-- CARD LAYOUT - Card List -->
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
                        <span class="item-card__separator">•</span>
                        <span class="badge <?php echo esc_attr($status_class); ?>"><?php echo ucfirst($status); ?></span>
                        <span class="item-card__separator">•</span>
                        <span class="item-card__date"><?php echo get_the_date('d/m/Y'); ?></span>
                    </div>
                </div>
                <div class="item-card__actions-group">
                    <button class="btn-icon" @click.stop="openFormModal('documenti', 'edit', <?php echo get_the_ID(); ?>, '<?php echo esc_attr($post_type); ?>')" title="Modifica"><i data-lucide="edit-2"></i></button>
                    <button class="btn-icon" @click.stop="deletePost(<?php echo get_the_ID(); ?>)" title="Elimina"><i data-lucide="trash-2"></i></button>
                    <a href="<?php the_permalink(); ?>" class="btn-icon" title="Visualizza" target="_blank"><i data-lucide="eye"></i></a>
                    <button class="item-card__toggle" aria-label="Espandi dettagli"><i data-lucide="chevron-down"></i></button>
                </div>
            </div>
            <div class="item-card__content" id="card-<?php echo get_the_ID(); ?>">
                <?php
                // Recupera tutte le ACF field
                $fields = get_fields(get_the_ID());
                if ($fields && is_array($fields)) {
                    foreach ($fields as $field_name => $field_value) {
                        // Salta i campi vuoti e quelli privati (iniziano con _)
                        if (empty($field_value) || strpos($field_name, '_') === 0) {
                            continue;
                        }

                        // Ottieni le info del campo per il label
                        $field_object = get_field_object($field_name, get_the_ID());
                        $field_label = $field_object ? $field_object['label'] : ucwords(str_replace('_', ' ', $field_name));

                        // Formatta il valore a seconda del tipo
                        $display_value = '';
                        if (is_array($field_value)) {
                            $display_value = implode(', ', array_map('esc_html', $field_value));
                        } elseif (is_object($field_value)) {
                            $display_value = isset($field_value->post_title) ? esc_html($field_value->post_title) : esc_html((string)$field_value);
                        } else {
                            $display_value = esc_html($field_value);
                        }

                        if ($display_value) {
                            echo '<div class="item-card__row">';
                            echo '<span class="item-card__label">' . esc_html($field_label) . '</span>';
                            echo '<span class="item-card__value">' . $display_value . '</span>';
                            echo '</div>';
                        }
                    }
                }

                // Recupera tutte le taxonomy
                $taxonomies = get_taxonomies(['object_type' => [$post_type]], 'objects');
                foreach ($taxonomies as $taxonomy) {
                    $terms = get_the_terms(get_the_ID(), $taxonomy->name);
                    if ($terms && !is_wp_error($terms) && !empty($terms)) {
                        $term_names = wp_list_pluck($terms, 'name');
                        echo '<div class="item-card__row">';
                        echo '<span class="item-card__label">' . esc_html($taxonomy->label) . '</span>';
                        echo '<span class="item-card__value">' . esc_html(implode(', ', $term_names)) . '</span>';
                        echo '</div>';
                    }
                }
                ?>
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
