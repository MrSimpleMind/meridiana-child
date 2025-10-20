<?php
/**
 * Template: Single Salute e Benessere
 * Visualizza dettaglio articolo wellness e salute con design system compliance
 * Struttura allineata a single-convenzione.php per coerenza visuale
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
    
    <main class="single-salute-benessere-page">
        <?php while (have_posts()): the_post(); 
            // ACF Fields
            $contenuto = get_field('contenuto');           // WYSIWYG
            $risorse = get_field('risorse');               // Repeater: tipo, url/file, titolo
            $immagine_id = get_post_thumbnail_id();        // Featured image ID
            $immagine_url = $immagine_id ? wp_get_attachment_image_url($immagine_id, 'large') : '';
        ?>
        
        <div class="single-container">
            <!-- Breadcrumb Navigation (consistente con altre pagine) -->
            <?php meridiana_render_breadcrumb(); ?>
            
            <!-- Back Navigation -->
            <div class="back-link-wrapper">
                <a href="<?php echo esc_url(meridiana_get_parent_url()); ?>" class="back-link">
                    <i data-lucide="arrow-left"></i>
                    <span><?php echo esc_html(meridiana_get_back_label()); ?></span>
                </a>
            </div>
            
            <!-- Header -->
            <header class="single-salute-benessere__header">
                <h1 class="single-salute-benessere__title"><?php the_title(); ?></h1>
            </header>
            
            <!-- Featured Image (16:9 aspect ratio per coerenza con comunicazioni) -->
            <?php if ($immagine_url): ?>
            <div class="single-salute-benessere__featured-image">
                <img src="<?php echo esc_url($immagine_url); ?>" 
                     alt="<?php the_title_attribute(); ?>" 
                     class="single-salute-benessere__image" 
                     loading="lazy">
            </div>
            <?php endif; ?>
            
            <!-- Layout principale con sidebar (grid 2 colonne su desktop) -->
            <div class="single-salute-benessere__layout">
                
                <!-- Main Content -->
                <article class="single-salute-benessere__content">
                    
                    <!-- Contenuto principale WYSIWYG -->
                    <?php if ($contenuto): ?>
                    <div class="single-salute-benessere__body wysiwyg-content">
                        <?php echo wp_kses_post($contenuto); ?>
                    </div>
                    <?php endif; ?>
                    
                </article>
                
                <!-- Sidebar with Resources -->
                <?php if ($risorse && is_array($risorse) && count($risorse) > 0): ?>
                <aside class="single-salute-benessere__sidebar">
                    
                    <section class="single-salute-benessere__section">
                        <h2 class="single-salute-benessere__section-title">
                            <i data-lucide="link-2"></i>
                            <span>Risorse</span>
                        </h2>
                        <ul class="single-salute-benessere__risorse-list">
                            <?php foreach ($risorse as $risorsa): 
                                if (!$risorsa) continue;
                                
                                $tipo = isset($risorsa['tipo']) ? $risorsa['tipo'] : 'link';
                                $titolo = isset($risorsa['titolo']) ? $risorsa['titolo'] : 'Risorsa';
                                
                                // Link case
                                if ($tipo === 'link'):
                                    $url = isset($risorsa['url']) ? $risorsa['url'] : '';
                                    if ($url):
                            ?>
                            <li class="single-salute-benessere__risorsa-item">
                                <a href="<?php echo esc_url($url); ?>" 
                                   class="single-salute-benessere__risorsa-link" 
                                   target="_blank" rel="noopener">
                                    <i data-lucide="external-link"></i>
                                    <span><?php echo esc_html($titolo); ?></span>
                                </a>
                            </li>
                            <?php 
                                    endif;
                                
                                // File case
                                elseif ($tipo === 'file'):
                                    $file = isset($risorsa['file']) ? $risorsa['file'] : '';
                                    if ($file):
                                        $file_url = is_array($file) ? ($file['url'] ?? '') : $file;
                                        if (!$file_url) continue;
                                        
                                        $file_title = is_array($file) ? ($file['name'] ?? basename($file_url)) : basename($file_url);
                                        $file_size = '';
                                        
                                        if (is_array($file) && isset($file['filesize'])) {
                                            $file_size = ' (' . size_format($file['filesize']) . ')';
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
                            <li class="single-salute-benessere__risorsa-item">
                                <a href="<?php echo esc_url($file_url); ?>" 
                                   class="single-salute-benessere__risorsa-link" 
                                   target="_blank" rel="noopener" download>
                                    <i data-lucide="<?php echo esc_attr($file_icon); ?>"></i>
                                    <span><?php echo esc_html($file_title); ?></span>
                                    <?php if ($file_size): ?>
                                    <small><?php echo esc_html($file_size); ?></small>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <?php 
                                    endif;
                                endif;
                            endforeach; ?>
                        </ul>
                    </section>
                    
                </aside>
                <?php endif; ?>
                
            </div>
            
        </div>
        
        <?php endwhile; ?>
    </main>
</div>

<?php
// Inline styles per PROMPT 8 - Temporary until SCSS compiles
get_template_part('templates/parts/inline-styles-salute');

get_footer();
