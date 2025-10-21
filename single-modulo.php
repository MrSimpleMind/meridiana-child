<?php
/*
Template Name: Singolo Modulo
*/

get_header();
$post_id = get_the_ID();
$pdf_file = get_field('pdf_modulo', $post_id);
$pdf_url = $pdf_file ? wp_get_attachment_url($pdf_file) : '';
$riassunto = get_field('riassunto', $post_id);
$profilo_terms = get_the_terms($post_id, 'profili_professionali');
$udo_terms = get_the_terms($post_id, 'unita_offerta');
?>

<div class="content-wrapper">
    <div class="container">
        <div class="single-documento">
            
            <!-- BACK BUTTON -->
            <?php meridiana_render_back_button(); ?>

            <!-- BREADCRUMB -->
            <?php meridiana_render_breadcrumb(); ?>

            <!-- HEADER -->
            <header class="single-documento__header">
                <h1 class="single-documento__title">
                    <?php the_title(); ?>
                </h1>
                <div class="single-documento__meta">
                    <span class="single-documento__type">
                        <span class="badge badge-green">M</span>
                        Modulo
                    </span>
                    <span class="single-documento__date">
                        <?php echo get_the_date('d M Y'); ?>
                    </span>
                </div>
            </header>

            <!-- FEATURED IMAGE -->
            <?php if (has_post_thumbnail()): ?>
            <div class="single-documento__featured">
                <?php the_post_thumbnail('large', array('class' => 'single-documento__featured-image')); ?>
            </div>
            <?php endif; ?>

            <!-- LAYOUT GRID: Content + Sidebar -->
            <div class="single-documento__layout">
                
                <!-- MAIN CONTENT -->
                <main class="single-documento__content">
                    
                    <!-- RIASSUNTO -->
                    <?php if ($riassunto): ?>
                    <section class="single-documento__section">
                        <h2 class="single-documento__section-title">
                            <i data-lucide="file-text"></i>
                            Riassunto
                        </h2>
                        <div class="single-documento__riassunto">
                            <?php echo wp_kses_post($riassunto); ?>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- CONTENUTO PRINCIPALE -->
                    <?php if (get_the_content()): ?>
                    <section class="single-documento__section">
                        <h2 class="single-documento__section-title">
                            <i data-lucide="book-open"></i>
                            Contenuto
                        </h2>
                        <div class="single-documento__body">
                            <?php the_content(); ?>
                        </div>
                    </section>
                    <?php endif; ?>

                </main>

                <!-- SIDEBAR -->
                <aside class="single-documento__sidebar">
                    
                    <!-- SCARICA MODULO -->
                    <?php if ($pdf_url): ?>
                    <div class="single-documento__widget">
                        <h3 class="single-documento__widget-title">
                            <i data-lucide="download"></i>
                            Modulo PDF
                        </h3>
                        <a href="<?php echo esc_url($pdf_url); ?>" class="btn btn-primary btn-block" target="_blank">
                            <i data-lucide="eye"></i>
                            Visualizza
                        </a>
                        <a href="<?php echo esc_url($pdf_url); ?>" class="btn btn-secondary btn-block" download>
                            <i data-lucide="download"></i>
                            Scarica
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- INFORMAZIONI -->
                    <div class="single-documento__widget">
                        <h3 class="single-documento__widget-title">
                            <i data-lucide="info"></i>
                            Informazioni
                        </h3>
                        
                        <div class="single-documento__info-item">
                            <strong>Tipo:</strong>
                            <span class="badge badge-green">Modulo</span>
                        </div>

                        <div class="single-documento__info-item">
                            <strong>Pubblicato:</strong>
                            <span><?php echo get_the_date('d M Y'); ?></span>
                        </div>

                        <?php if (!empty($profilo_terms) && !is_wp_error($profilo_terms)): ?>
                        <div class="single-documento__info-item">
                            <strong>Profilo:</strong>
                            <span>
                                <?php 
                                echo implode(', ', array_map(function($term) {
                                    return esc_html($term->name);
                                }, $profilo_terms));
                                ?>
                            </span>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($udo_terms) && !is_wp_error($udo_terms)): ?>
                        <div class="single-documento__info-item">
                            <strong>Unità d'Offerta:</strong>
                            <span>
                                <?php 
                                echo implode(', ', array_map(function($term) {
                                    return esc_html($term->name);
                                }, $udo_terms));
                                ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- MODULI CORRELATI -->
                    <?php
                    $related_args = array(
                        'post_type' => 'modulo',
                        'posts_per_page' => 5,
                        'post__not_in' => array($post_id),
                        'orderby' => 'date',
                        'order' => 'DESC',
                    );
                    $related_query = new WP_Query($related_args);
                    if ($related_query->have_posts()):
                    ?>
                    <div class="single-documento__widget">
                        <h3 class="single-documento__widget-title">
                            <i data-lucide="link"></i>
                            Correlati
                        </h3>
                        <ul class="single-documento__related-list">
                            <?php while ($related_query->have_posts()): $related_query->the_post(); ?>
                            <li>
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_title(); ?>
                                </a>
                            </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                    <?php 
                    endif;
                    wp_reset_postdata();
                    ?>

                </aside>

            </div>

        </div>
    </div>
</div>

<!-- STILE INLINE -->
<style>
.single-documento {
    padding: var(--space-6) 0;
}

.single-documento__header {
    margin-bottom: var(--space-8);
}

.single-documento__title {
    font-size: var(--font-size-3xl);
    font-weight: 700;
    color: var(--color-text-primary);
    margin-bottom: var(--space-4);
    line-height: 1.2;
}

.single-documento__meta {
    display: flex;
    gap: var(--space-4);
    flex-wrap: wrap;
    align-items: center;
}

.single-documento__type,
.single-documento__date {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--font-size-sm);
    color: var(--color-text-secondary);
}

.single-documento__featured {
    margin-bottom: var(--space-8);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.single-documento__featured-image {
    width: 100%;
    height: auto;
    max-height: 400px;
    object-fit: cover;
    display: block;
}

@media (max-width: 768px) {
    .single-documento__featured-image {
        max-height: 250px;
    }
}

.single-documento__layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: var(--space-8);

    @media (max-width: 768px) {
        grid-template-columns: 1fr;
        gap: var(--space-6);
    }
}

.single-documento__content {
    display: flex;
    flex-direction: column;
    gap: var(--space-8);
}

.single-documento__section {
    padding: var(--space-6);
    background-color: var(--color-bg-secondary);
    border-radius: var(--radius-lg);
    border: 1px solid var(--color-border);
}

.single-documento__section-title {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--font-size-lg);
    font-weight: 600;
    color: var(--color-text-primary);
    margin-bottom: var(--space-4);
    margin-top: 0;

    i {
        width: 20px;
        height: 20px;
        color: var(--color-primary);
    }
}

.single-documento__riassunto {
    font-size: var(--font-size-base);
    line-height: 1.6;
    color: var(--color-text-primary);
}

.single-documento__body {
    font-size: var(--font-size-base);
    line-height: 1.7;
    color: var(--color-text-primary);
}

.single-documento__body p {
    margin-bottom: var(--space-4);
}

.single-documento__sidebar {
    display: flex;
    flex-direction: column;
    gap: var(--space-6);
    height: fit-content;
    position: sticky;
    top: var(--space-4);

    @media (max-width: 768px) {
        position: relative;
        top: 0;
    }
}

.single-documento__widget {
    padding: var(--space-5);
    background-color: var(--color-bg-secondary);
    border-radius: var(--radius-lg);
    border: 1px solid var(--color-border);
}

.single-documento__widget-title {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--font-size-base);
    font-weight: 600;
    color: var(--color-text-primary);
    margin-bottom: var(--space-4);
    margin-top: 0;

    i {
        width: 18px;
        height: 18px;
        color: var(--color-primary);
    }
}

.single-documento__info-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: var(--space-2);
    padding: var(--space-3) 0;
    border-bottom: 1px solid var(--color-border);
    font-size: var(--font-size-sm);

    &:last-child {
        border-bottom: none;
    }

    strong {
        color: var(--color-text-primary);
        min-width: 100px;
    }

    span {
        color: var(--color-text-secondary);
    }
}

.single-documento__related-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.single-documento__related-list li {
    margin: 0;
}

.single-documento__related-list a {
    display: block;
    padding: var(--space-2) var(--space-3);
    background-color: var(--color-bg-primary);
    border-radius: var(--radius-md);
    color: var(--color-text-primary);
    text-decoration: none;
    font-size: var(--font-size-sm);
    transition: all 0.2s ease;

    &:hover {
        background-color: var(--color-primary);
        color: white;
    }
}

.btn-block {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2);
    margin-bottom: var(--space-2);

    &:last-child {
        margin-bottom: 0;
    }
}

@media (max-width: 576px) {
    .single-documento__title {
        font-size: var(--font-size-2xl);
    }

    .single-documento__layout {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
get_footer();
?>
