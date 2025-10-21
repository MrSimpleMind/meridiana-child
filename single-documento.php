<?php
/*
Template Name: Singolo Documento (Protocollo/Modulo)
Description: Template unificato per visualizzare Protocolli e Moduli
*/

get_header();

$post_id = get_the_ID();
$post_type = get_post_type();
$is_protocollo = ($post_type === 'protocollo');

// ============================================
// FIELD MAPPING - Nomi esatti dai ACF JSON
// ============================================

// PDF Field - Dinamico per tipo
$pdf_field_name = $is_protocollo ? 'pdf_protocollo' : 'pdf_modulo';
$pdf_file = get_field($pdf_field_name, $post_id);
$pdf_url = $pdf_file ? wp_get_attachment_url($pdf_file) : '';

// Riassunto - Solo Protocollo
$riassunto = $is_protocollo ? get_field('riassunto', $post_id) : false;

// Moduli Allegati - Solo Protocollo
$moduli_allegati_ids = $is_protocollo ? get_field('moduli_allegati', $post_id) : false;

// Pianificazione ATS - Solo Protocollo
$pianificazione_ats = $is_protocollo ? get_field('pianificazione_ats', $post_id) : false;

// Taxonomies - Entrambi
$profilo_terms = get_the_terms($post_id, 'profilo-professionale');
$udo_terms = get_the_terms($post_id, 'unita-offerta');

// Aree Competenza - Solo Modulo
$aree_terms = !$is_protocollo ? get_the_terms($post_id, 'area-competenza') : false;

// ============================================
// VARIABILI DI VISUALIZZAZIONE
// ============================================

$badge_color = $is_protocollo ? 'blue' : 'green';
$badge_letter = $is_protocollo ? 'P' : 'M';
$type_label = $is_protocollo ? 'Protocollo' : 'Modulo';

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
                    
                    <!-- RIASSUNTO - SOLO PROTOCOLLO -->
                    <?php if ($is_protocollo && $riassunto): ?>
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

                    <!-- PDF EMBED - ENTRAMBI -->
                    <?php if ($pdf_url): ?>
                    <section class="single-documento__section">
                        <div class="single-documento__pdf-embed">
                            <?php echo do_shortcode('[pdf-embedder url="' . esc_url($pdf_url) . '"]'); ?>
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
                    
                    <!-- WIDGET PDF AZIONI - SOLO MODULO -->
                    <?php if (!$is_protocollo && $pdf_url): ?>
                    <div class="single-documento__widget">
                        <h3 class="single-documento__widget-title">
                            <i data-lucide="download"></i>
                            Azioni
                        </h3>
                        
                        <!-- PDF Scarica Button -->
                        <a href="<?php echo esc_url($pdf_url); ?>" class="btn btn-primary btn-block" download>
                            <i data-lucide="download"></i>
                            Scarica
                        </a>
                        
                        <!-- PDF Stampa Button -->
                        <button class="btn btn-secondary btn-block" onclick="window.print()">
                            <i data-lucide="printer"></i>
                            Stampa
                        </button>
                    </div>
                    <?php endif; ?>

                    <!-- WIDGET INFORMAZIONI -->
                    <div class="single-documento__widget">
                        <h3 class="single-documento__widget-title">
                            <i data-lucide="info"></i>
                            Informazioni
                        </h3>
                        
                        <!-- Tipo -->
                        <div class="single-documento__info-item">
                            <strong>Tipo:</strong>
                            <span class="badge badge-<?php echo esc_attr($badge_color); ?>">
                                <?php echo esc_html($type_label); ?>
                            </span>
                        </div>

                        <!-- Data Pubblicazione -->
                        <div class="single-documento__info-item">
                            <strong>Pubblicato:</strong>
                            <span><?php echo get_the_date('d M Y'); ?></span>
                        </div>

                        <!-- Profilo Professionale -->
                        <?php if (!empty($profilo_terms) && !is_wp_error($profilo_terms)): ?>
                        <div class="single-documento__info-item">
                            <strong>Profilo:</strong>
                            <div class="single-documento__info-tags">
                                <?php foreach ($profilo_terms as $term): ?>
                                    <span class="single-documento__info-tag">
                                        <?php echo esc_html($term->name); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Unità d'Offerta -->
                        <?php if (!empty($udo_terms) && !is_wp_error($udo_terms)): ?>
                        <div class="single-documento__info-item">
                            <strong>UDO:</strong>
                            <div class="single-documento__info-tags">
                                <?php foreach ($udo_terms as $term): ?>
                                    <span class="single-documento__info-tag">
                                        <?php echo esc_html($term->name); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Aree di Competenza - SOLO MODULO -->
                        <?php if (!$is_protocollo && !empty($aree_terms) && !is_wp_error($aree_terms)): ?>
                        <div class="single-documento__info-item">
                            <strong>Aree di Competenza:</strong>
                            <div class="single-documento__info-tags">
                                <?php foreach ($aree_terms as $term): ?>
                                    <span class="single-documento__info-tag">
                                        <?php echo esc_html($term->name); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Pianificazione ATS - SOLO PROTOCOLLO -->
                        <?php if ($is_protocollo): ?>
                        <div class="single-documento__info-item">
                            <strong>Pianificazione ATS:</strong>
                            <span><?php echo $pianificazione_ats ? 'Sì' : 'No'; ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- WIDGET MODULI ALLEGATI - SOLO PROTOCOLLO -->
                    <?php if ($is_protocollo && !empty($moduli_allegati_ids)): 
                        $moduli_query = get_posts(array(
                            'post_type' => 'modulo',
                            'post__in' => (array)$moduli_allegati_ids,
                            'orderby' => 'post__in',
                            'posts_per_page' => -1,
                        ));
                        
                        if (!empty($moduli_query)):
                    ?>
                    <div class="single-documento__widget">
                        <h3 class="single-documento__widget-title">
                            <i data-lucide="link"></i>
                            Moduli Correlati
                        </h3>
                        <ul class="single-documento__related-list">
                            <?php foreach ($moduli_query as $modulo): ?>
                            <li>
                                <a href="<?php echo esc_url(get_permalink($modulo->ID)); ?>">
                                    <?php echo esc_html($modulo->post_title); ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php 
                        endif;
                        wp_reset_postdata();
                    endif; 
                    ?>

                </aside>

            </div>

        </div>
    </div>
</div>

<?php
get_footer();
?>
