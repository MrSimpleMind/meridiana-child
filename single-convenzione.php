<?php
/**
 * Template: Single Convenzione
 * Visualizza dettaglio completo di una convenzione aziendale
 * Layout: 1 colonna verticale semplice e pulito
 * Ordine: Immagine → Descrizione → Contatti → Allegati
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
            // ACF Fields
            $descrizione = get_field('descrizione');      // WYSIWYG
            $contatti = get_field('contatti');             // WYSIWYG
            $allegati = get_field('allegati');             // Repeater: file + descrizione
            $immagine_id = get_field('immagine_evidenza'); // Image ID
            $immagine_url = $immagine_id ? wp_get_attachment_image_url($immagine_id, 'large') : '';
        ?>
        
        <div class="single-container">
            <!-- Breadcrumb Navigation -->
            <?php meridiana_render_breadcrumb(); ?>
            
            <!-- Back Navigation -->
            <div class="back-link-wrapper">
                <a href="<?php echo esc_url(meridiana_get_parent_url()); ?>" class="back-link">
                    <i data-lucide="arrow-left"></i>
                    <span><?php echo esc_html(meridiana_get_back_label()); ?></span>
                </a>
            </div>
            
            <!-- Header -->
            <header class="single-convenzione__header">
                <h1 class="single-convenzione__title"><?php the_title(); ?></h1>
            </header>
            
            <!-- Featured Image (16:9 aspect ratio) -->
            <?php if ($immagine_url): ?>
            <div class="single-convenzione__featured-image">
                <img src="<?php echo esc_url($immagine_url); ?>" 
                     alt="<?php the_title_attribute(); ?>" 
                     class="single-convenzione__image" 
                     loading="lazy">
            </div>
            <?php endif; ?>
            
            <!-- Descrizione principale -->
            <?php if ($descrizione): ?>
            <article class="single-convenzione__content">
                <div class="single-convenzione__body wysiwyg-content">
                    <?php echo wp_kses_post($descrizione); ?>
                </div>
            </article>
            <?php endif; ?>
            
            <!-- Contatti Section -->
            <?php if ($contatti): ?>
            <section class="single-convenzione__section">
                <h2 class="single-convenzione__section-title">
                    <i data-lucide="contact"></i>
                    <span>Contatti</span>
                </h2>
                <div class="single-convenzione__contatti-content wysiwyg-content">
                    <?php echo wp_kses_post($contatti); ?>
                </div>
            </section>
            <?php endif; ?>
            
            <!-- Allegati Section -->
            <?php if ($allegati && is_array($allegati) && count($allegati) > 0): ?>
            <section class="single-convenzione__section">
                <h2 class="single-convenzione__section-title">
                    <i data-lucide="download"></i>
                    <span>Allegati</span>
                </h2>
                <ul class="single-convenzione__allegati-list">
                    <?php foreach ($allegati as $item): 
                        if (!is_array($item)) continue;
                        
                        // Struttura repeater: file + descrizione
                        $file_data = $item['file'] ?? '';
                        $descrizione_allegato = $item['descrizione'] ?? '';
                        
                        if (!$file_data) continue; // Skip se no file
                        
                        // File può essere array o string
                        $file_url = is_array($file_data) ? ($file_data['url'] ?? '') : $file_data;
                        if (!$file_url) continue;
                        
                        // Nome allegato: priorità a descrizione, fallback a basename
                        $file_title = $descrizione_allegato ?: basename($file_url);
                        
                        // File size se disponibile
                        $file_size = '';
                        if (is_array($file_data) && isset($file_data['filesize'])) {
                            $file_size = ' (' . size_format($file_data['filesize']) . ')';
                        }
                        
                        // Determina icona based on file extension
                        $extension = strtolower(pathinfo($file_url, PATHINFO_EXTENSION));
                        $file_icon = match($extension) {
                            'pdf' => 'file-text',
                            'doc', 'docx' => 'file-text',
                            'xls', 'xlsx' => 'table-2',
                            'jpg', 'jpeg', 'png', 'gif' => 'image',
                            'zip', 'rar' => 'archive',
                            default => 'file-text'
                        };
                    ?>
                        <li class="single-convenzione__allegato-item">
                            <a href="<?php echo esc_url($file_url); ?>" 
                               class="single-convenzione__allegato-link" 
                               target="_blank" rel="noopener" download>
                                <i data-lucide="<?php echo esc_attr($file_icon); ?>"></i>
                                <span><?php echo esc_html($file_title); ?></span>
                                <?php if ($file_size): ?>
                                <small><?php echo esc_html($file_size); ?></small>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
            <?php endif; ?>
            
        </div>
        
        <?php endwhile; ?>
    </main>
</div>

<?php
get_footer();
