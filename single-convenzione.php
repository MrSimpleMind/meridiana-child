<?php
/**
 * Template: Single Convenzione
 * Visualizza dettaglio completo di una convenzione
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
    
    <main class="single-convenzione-page">
        <?php while (have_posts()): the_post(); 
            // Get ACF fields
            $descrizione = get_field('descrizione'); // WYSIWYG
            $contatti = get_field('contatti'); // WYSIWYG
            $allegati = get_field('allegati'); // Repeater
            $immagine_id = get_field('immagine_evidenza'); // Image ID
            $attiva = get_field('convenzione_attiva'); // True/False
        ?>
        
        <div class="single-container">
            <!-- Header con Torna Indietro -->
            <div class="single-header">
                <a href="<?php echo get_post_type_archive_link('convenzione'); ?>" class="back-link">
                    <i data-lucide="arrow-left"></i>
                    <span>Torna indietro</span>
                </a>
            </div>
            
            <!-- Content -->
            <article class="single-content">
                
                <!-- Titolo -->
                <h1 class="single-title"><?php the_title(); ?></h1>
                
                <!-- Badge Stato (solo se scaduta) -->
                <?php if (!$attiva): ?>
                <div class="status-badge status-badge--error">
                    <i data-lucide="alert-circle"></i>
                    Convenzione Scaduta
                </div>
                <?php endif; ?>
                
                <!-- Descrizione -->
                <?php if ($descrizione): ?>
                <div class="single-body wysiwyg-content">
                    <?php echo wp_kses_post($descrizione); ?>
                </div>
                <?php endif; ?>
                
                <!-- Allegati -->
                <?php if ($allegati && count($allegati) > 0): ?>
                <section class="content-section">
                    <h2 class="section-heading">Allegati</h2>
                    <div class="resource-list">
                        <?php foreach ($allegati as $allegato): 
                            $file = $allegato['file'];
                            $descrizione_file = $allegato['descrizione'];
                            
                            if ($file):
                                $file_url = $file['url'];
                                $file_name = $descrizione_file ?: $file['filename'];
                                $file_size = size_format($file['filesize']);
                                $file_icon = 'file-text';
                                
                                $extension = pathinfo($file_url, PATHINFO_EXTENSION);
                                if ($extension === 'pdf') $file_icon = 'file-text';
                                elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) $file_icon = 'image';
                        ?>
                        <a href="<?php echo esc_url($file_url); ?>" class="resource-card" target="_blank" rel="noopener">
                            <div class="resource-card__icon">
                                <i data-lucide="<?php echo esc_attr($file_icon); ?>"></i>
                            </div>
                            <div class="resource-card__content">
                                <span class="resource-card__title"><?php echo esc_html($file_name); ?></span>
                                <span class="resource-card__meta"><?php echo esc_html($file_size); ?></span>
                            </div>
                            <div class="resource-card__arrow">
                                <i data-lucide="arrow-right"></i>
                            </div>
                        </a>
                        <?php 
                            endif;
                        endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>
                
                <!-- Contatti -->
                <?php if ($contatti): ?>
                <section class="content-section">
                    <h2 class="section-heading">Contatti</h2>
                    <div class="contact-box wysiwyg-content">
                        <?php echo wp_kses_post($contatti); ?>
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
