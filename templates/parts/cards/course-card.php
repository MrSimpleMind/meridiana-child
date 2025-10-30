<?php
/**
 * Card Component: LearnDash Course Card
 * Visualizza una singola card corso con stato, progresso e azioni
 *
 * @package Meridiana Child
 *
 * @param array $course {
 *     @type int      $id              ID del corso
 *     @type string   $title           Titolo del corso
 *     @type string   $description     Descrizione/excerpt
 *     @type string   $url             URL del corso
 *     @type string   $status          Stato ('in-progress', 'completed', 'optional')
 *     @type int      $progress        Percentuale progresso (0-100)
 *     @type int      $lessons_total   Numero lezioni totali
 *     @type int      $lessons_done    Numero lezioni completate
 *     @type string   $featured_image  URL immagine in evidenza
 *     @type bool     $is_enrolled     Se l'utente è iscritto
 *     @type string   $completed_date  Data completamento (se status = 'completed')
 * }
 */

if (!isset($course) || !is_array($course)) {
    return;
}

// Variabili con valori di default
$course_id = $course['id'] ?? 0;
$title = $course['title'] ?? 'Corso Senza Titolo';
$description = $course['description'] ?? '';
$url = $course['url'] ?? '#';
$status = $course['status'] ?? 'optional'; // 'in-progress', 'completed', 'optional'
$progress = intval($course['progress'] ?? 0);
$lessons_total = intval($course['lessons_total'] ?? 0);
$lessons_done = intval($course['lessons_done'] ?? 0);
$featured_image = $course['featured_image'] ?? '';
$is_enrolled = $course['is_enrolled'] ?? false;
$completed_date = $course['completed_date'] ?? '';
$enrolled_count = intval($course['enrolled_count'] ?? 0);

// Determina le classi di stato
$status_class = 'course-card--' . $status;

// Mappa stato -> badge text e colore
$status_labels = array(
    'in-progress' => 'In Corso',
    'completed' => 'Completato',
    'optional' => 'Facoltativo',
);
$status_label = $status_labels[$status] ?? ucfirst($status);

?>

<div class="course-card <?php echo esc_attr($status_class); ?>">

    <!-- FEATURED IMAGE -->
    <?php if ($featured_image): ?>
    <div class="course-card__image" style="background-image: url('<?php echo esc_url($featured_image); ?>');">
        <div class="course-card__image-overlay"></div>
    </div>
    <?php else: ?>
    <div class="course-card__image-placeholder">
        <i data-lucide="book-open"></i>
    </div>
    <?php endif; ?>

    <!-- COURSE HEADER -->
    <div class="course-card__header">
        <h3 class="course-card__title"><?php echo esc_html($title); ?></h3>
        <span class="course-card__status course-card__status--<?php echo esc_attr($status); ?>">
            <?php echo esc_html($status_label); ?>
        </span>
    </div>

    <!-- COURSE BODY -->
    <div class="course-card__body">
        <?php if ($description): ?>
        <p class="course-card__description">
            <?php echo esc_html(wp_trim_words(strip_tags($description), 20)); ?>
        </p>
        <?php endif; ?>

        <!-- PROGRESS BAR - Solo per in-progress -->
        <?php if ($status === 'in-progress' && $lessons_total > 0): ?>
        <div class="course-card__progress">
            <div class="progress-bar">
                <div class="progress-bar__fill" style="width: <?php echo $progress; ?>%"></div>
            </div>
            <span class="course-card__progress-text">
                <?php echo $progress; ?>% • <?php echo $lessons_done; ?>/<?php echo $lessons_total; ?> lezioni
            </span>
        </div>
        <?php endif; ?>

        <!-- COMPLETED DATE - Solo per completed -->
        <?php if ($status === 'completed' && $completed_date): ?>
        <div class="course-card__completed-date">
            <i data-lucide="calendar"></i>
            <span><?php echo esc_html($completed_date); ?></span>
        </div>
        <?php endif; ?>

        <!-- METADATA - Per optional courses -->
        <?php if ($status === 'optional'): ?>
        <div class="course-card__meta">
            <?php if ($lessons_total > 0): ?>
            <span class="course-meta-item">
                <i data-lucide="list-check"></i>
                <span><?php echo $lessons_total; ?> lezioni</span>
            </span>
            <?php endif; ?>

            <?php if ($enrolled_count > 0): ?>
            <span class="course-meta-item">
                <i data-lucide="users"></i>
                <span><?php echo $enrolled_count; ?> iscritti</span>
            </span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- COURSE FOOTER -->
    <div class="course-card__footer">
        <?php if ($status === 'in-progress'): ?>
            <!-- Continua button -->
            <a href="<?php echo esc_url($url); ?>" class="btn btn-primary btn-sm">
                <i data-lucide="arrow-right"></i>
                Continua
            </a>

        <?php elseif ($status === 'completed'): ?>
            <!-- View and Certificate buttons -->
            <a href="<?php echo esc_url($url); ?>" class="btn btn-secondary btn-sm">
                <i data-lucide="external-link"></i>
                Visualizza
            </a>
            <button @click="downloadCourseCertificate(<?php echo intval($course_id); ?>)" class="btn btn-outline btn-sm">
                <i data-lucide="download"></i>
                Certificato
            </button>

        <?php elseif ($status === 'optional'): ?>
            <!-- Enroll button -->
            <button @click="enrollCourse(<?php echo intval($course_id); ?>)" class="btn btn-primary btn-sm">
                <i data-lucide="plus"></i>
                Iscriviti
            </button>
        <?php endif; ?>
    </div>

</div>
