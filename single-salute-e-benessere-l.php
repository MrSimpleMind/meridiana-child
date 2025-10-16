<?php
/**
 * Template: Single Salute e Benessere
 * Visualizza dettaglio completo di un articolo salute e benessere
 * 
 * @package Meridiana Child
 */

if (!defined('ABSPATH')) exit;

get_header();
?>

<div class="content-wrapper">
    <?php 
    // Include navigation (mobile + desktop)
    get_template_part('templates/parts/navigation/mobile-bottom-nav');
    get_template_part('templates/parts/navigation/desktop-sidebar');
    ?>
    
    <main class="single-salute-page">
        <?php while (have_posts()): the_post(); 
            // Get ACF fields
            $contenuto = get_field('contenuto'); // WYSIWYG
            $risorse = get_field('risorse'); // Repeater
        ?>
        
        <div class="single-container">
            <!-- Header con Torna Indietro -->
            <div class="single-header">
                <a href="<?php echo home_url('/'); ?>" class="back-link">
                    <i data-lucide="arrow-left"></i>
                    <span>Torna indietro</span>
                </a>
            </div>
            
            <!-- Content -->
            <article class="single-content">
                
                <!-- Titolo -->
                <h1 class="single-title"><?php the_title(); ?></h1>
                
                <!-- Contenuto -->
                <?php if ($contenuto): ?>
                <div class="single-body wysiwyg-content">
                    <?php echo wp_kses_post($contenuto); ?>
                </div>
                <?php endif; ?>
                
                <!-- Risorse -->
                <?php if ($risorse && count($risorse) > 0): ?>
                <section class="content-section">
                    <h2 class="section-heading">Risorse</h2>
                    <div class="resource-list">
                        <?php foreach ($risorse as $risorsa): 
                            $tipo = $risorsa['tipo']; // 'link' o 'file'
                            $titolo = $risorsa['titolo'];
                            
                            if ($tipo === 'link'):
                                $url = $risorsa['url'];
                                if ($url):
                        ?>
                        <!-- Link Esterno -->
                        <a href="<?php echo esc_url($url); ?>" class="resource-card" target="_blank" rel="noopener">
                            <div class="resource-card__icon">
                                <i data-lucide="external-link"></i>
                            </div>
                            <div class="resource-card__content">
                                <span class="resource-card__title"><?php echo esc_html($titolo); ?></span>
                                <span class="resource-card__meta">Link esterno</span>
                            </div>
                            <div class="resource-card__arrow">
                                <i data-lucide="arrow-right"></i>
                            </div>
                        </a>
                        <?php 
                                endif;
                            elseif ($tipo === 'file'):
                                $file = $risorsa['file'];
                                if ($file):
                                    $file_url = $file['url'];
                                    $file_size = size_format($file['filesize']);
                                    $file_icon = 'file-text';
                                    
                                    $extension = pathinfo($file_url, PATHINFO_EXTENSION);
                                    if ($extension === 'pdf') $file_icon = 'file-text';
                                    elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) $file_icon = 'image';
                        ?>
                        <!-- File Download -->
                        <a href="<?php echo esc_url($file_url); ?>" class="resource-card" target="_blank" rel="noopener" download>
                            <div class="resource-card__icon">
                                <i data-lucide="<?php echo esc_attr($file_icon); ?>"></i>
                            </div>
                            <div class="resource-card__content">
                                <span class="resource-card__title"><?php echo esc_html($titolo); ?></span>
                                <span class="resource-card__meta"><?php echo esc_html($file_size); ?></span>
                            </div>
                            <div class="resource-card__arrow">
                                <i data-lucide="download"></i>
                            </div>
                        </a>
                        <?php 
                                endif;
                            endif;
                        endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>
                
            </article>
        </div>
        
        <?php endwhile; ?>
    </main>
</div>

<?php
get_footer();
