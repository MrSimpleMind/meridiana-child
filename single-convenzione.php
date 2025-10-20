<?php
/**
 * Template: Single Convenzione
 * Visualizza dettaglio completo di una convenzione aziendale
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
            $descrizione = get_field('descrizione'); // WYSIWYG
            $azienda = get_field('azienda'); // Text
            $sconto = get_field('sconto'); // Text (es: "20%")
            $sito_web_raw = get_field('sito_web'); // URL
            $sito_web = is_array($sito_web_raw) ? ($sito_web_raw['url'] ?? '') : $sito_web_raw;
            $email = get_field('email'); // Email
            $telefono = get_field('telefono'); // Text
            $allegati = get_field('allegati'); // File
            $immagine_id = get_field('immagine_evidenza'); // Image ID
            $immagine_url = $immagine_id ? wp_get_attachment_image_url($immagine_id, 'large') : '';
        ?>
        
        <div class="single-container">
            <!-- Back Navigation -->
            <div class="back-link-wrapper">
                <a href="#" onclick="history.back(); return false;" class="back-link">
                    <i data-lucide="arrow-left"></i>
                    <span>Torna indietro</span>
                </a>
            </div>
            
            <!-- Header -->
            <header class="single-convenzione__header">
                <h1 class="single-convenzione__title"><?php the_title(); ?></h1>
                
                <?php if ($azienda): ?>
                <p class="single-convenzione__azienda">
                    <i data-lucide="building-2"></i>
                    <span><?php echo esc_html($azienda); ?></span>
                </p>
                <?php endif; ?>
            </header>
            
            <!-- Featured Image -->
            <?php if ($immagine_url): ?>
            <div class="single-convenzione__featured-image">
                <img src="<?php echo esc_url($immagine_url); ?>" alt="<?php the_title_attribute(); ?>" class="single-convenzione__image">
            </div>
            <?php endif; ?>
            
            <!-- Main Content -->
            <article class="single-convenzione__content">
                
                <!-- Descrizione Main -->
                <?php if ($descrizione): ?>
                <div class="single-convenzione__body wysiwyg-content">
                    <?php echo wp_kses_post($descrizione); ?>
                </div>
                <?php endif; ?>
                
                <!-- Highlights -->
                <div class="single-convenzione__highlights">
                    <?php if ($sconto): ?>
                    <div class="highlight-item">
                        <i data-lucide="percent"></i>
                        <div>
                            <strong>Sconto</strong>
                            <span><?php echo esc_html($sconto); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </article>
            
            <!-- Sidebar with Contacts & Downloads -->
            <aside class="single-convenzione__sidebar">
                
                <!-- Contatti Section -->
                <section class="single-convenzione__section">
                    <h2 class="single-convenzione__section-title">
                        <i data-lucide="contact"></i>
                        <span>Contatti</span>
                    </h2>
                    <div class="single-convenzione__contatti-content">
                        <?php if ($sito_web): ?>
                        <p>
                            <strong>Sito Web:</strong><br>
                            <a href="<?php echo esc_url($sito_web); ?>" target="_blank" rel="noopener">
                                <?php echo esc_url($sito_web); ?>
                            </a>
                        </p>
                        <?php endif; ?>
                        
                        <?php if ($email): ?>
                        <p>
                            <strong>Email:</strong><br>
                            <a href="mailto:<?php echo esc_attr($email); ?>">
                                <?php echo esc_html($email); ?>
                            </a>
                        </p>
                        <?php endif; ?>
                        
                        <?php if ($telefono): ?>
                        <p>
                            <strong>Telefono:</strong><br>
                            <a href="tel:<?php echo esc_attr(str_replace(' ', '', $telefono)); ?>">
                                <?php echo esc_html($telefono); ?>
                            </a>
                        </p>
                        <?php endif; ?>
                    </div>
                </section>
                
                <!-- Allegati Section -->
                <?php if ($allegati && is_array($allegati) && count($allegati) > 0): ?>
                <section class="single-convenzione__section">
                    <h2 class="single-convenzione__section-title">
                        <i data-lucide="download"></i>
                        <span>Allegati</span>
                    </h2>
                    <ul class="single-convenzione__allegati-list">
                        <?php foreach ($allegati as $file): 
                            if (!$file) continue;
                            
                            $file_url = is_array($file) ? ($file['url'] ?? '') : $file;
                            if (!$file_url) continue; // Skip if no URL
                            
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
                
            </aside>
        </div>
        
        <?php endwhile; ?>
    </main>
</div>

<?php
get_footer();
