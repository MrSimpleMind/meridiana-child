<?php
/**
 * Template Name: Singolo Corso LearnDash
 * Description: Template per visualizzare un corso LearnDash con lezioni, quiz e progresso
 *
 * @package Meridiana Child
 */

if (!defined('ABSPATH')) exit;

get_header();

$course_id = get_the_ID();
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// ============================================
// COURSE DATA
// ============================================

$course = get_post($course_id);
$course_title = get_the_title($course_id);
$course_content = get_post_field('post_content', $course_id);
$course_excerpt = get_post_field('post_excerpt', $course_id);

// LearnDash Course Meta
$course_meta = get_post_meta($course_id);

// LearnDash course settings (via settings key)
$course_settings = get_post_meta($course_id, '_sfwd-courses', true);

// Get course status for current user
$user_course_status = learndash_user_get_course_access_list($user_id, array($course_id));
$is_enrolled = !empty($user_course_status);

// Get course progress
$course_progress = 0;
$lessons_completed = 0;
$total_lessons = 0;

if ($is_enrolled) {
    // Get all lessons in this course
    $lessons = new WP_Query(array(
        'post_type' => 'sfwd-lessons',
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'meta_key' => 'course_id',
        'meta_value' => $course_id,
        'fields' => 'ids',
    ));

    $total_lessons = $lessons->post_count;

    if ($total_lessons > 0) {
        // Count completed lessons for user
        foreach ($lessons->posts as $lesson_id) {
            if (learndash_lesson_completed_by_user($lesson_id, $user_id)) {
                $lessons_completed++;
            }
        }

        $course_progress = $total_lessons > 0 ? round(($lessons_completed / $total_lessons) * 100) : 0;
    }

    wp_reset_postdata();
}

// Get all lessons
$all_lessons_query = new WP_Query(array(
    'post_type' => 'sfwd-lessons',
    'posts_per_page' => -1,
    'orderby' => 'menu_order',
    'order' => 'ASC',
    'meta_key' => 'course_id',
    'meta_value' => $course_id,
));

$all_lessons = $all_lessons_query->posts;

// Get course featured image
$featured_image = get_the_post_thumbnail_url($course_id, 'large');

?>

<div class="content-wrapper">
    <?php
    // Navigation
    get_template_part('templates/parts/navigation/mobile-bottom-nav');
    get_template_part('templates/parts/navigation/desktop-sidebar');
    ?>

    <main class="page-single-course">
        <div class="single-course-container">

            <!-- BREADCRUMB -->
            <?php meridiana_render_breadcrumb(); ?>

            <!-- BACK BUTTON -->
            <div class="back-link-wrapper">
                <a href="<?php echo esc_url(get_post_type_archive_link('sfwd-courses')); ?>" class="back-link">
                    <i data-lucide="arrow-left"></i>
                    <span>Torna ai Corsi</span>
                </a>
            </div>

            <!-- COURSE HEADER -->
            <header class="single-course__header">
                <div class="single-course__header-content">
                    <h1 class="single-course__title"><?php echo esc_html($course_title); ?></h1>

                    <?php if ($course_excerpt): ?>
                    <p class="single-course__excerpt"><?php echo esc_html($course_excerpt); ?></p>
                    <?php endif; ?>
                </div>

                <!-- FEATURED IMAGE -->
                <?php if ($featured_image): ?>
                <div class="single-course__featured-image">
                    <img src="<?php echo esc_url($featured_image); ?>"
                         alt="<?php the_title_attribute(); ?>"
                         loading="lazy">
                </div>
                <?php endif; ?>
            </header>

            <!-- MAIN LAYOUT: Content + Sidebar -->
            <div class="single-course__layout">

                <!-- MAIN CONTENT -->
                <main class="single-course__content">

                    <!-- COURSE DESCRIPTION -->
                    <?php if ($course_content): ?>
                    <section class="single-course__section">
                        <h2 class="single-course__section-title">
                            <i data-lucide="book-open"></i>
                            Descrizione Corso
                        </h2>
                        <div class="single-course__description wysiwyg-content">
                            <?php echo wp_kses_post($course_content); ?>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- LESSONS SECTION -->
                    <?php if (!empty($all_lessons)): ?>
                    <section class="single-course__section">
                        <h2 class="single-course__section-title">
                            <i data-lucide="list-check"></i>
                            Lezioni (<?php echo count($all_lessons); ?>)
                        </h2>

                        <div class="course-lessons-list">
                            <?php
                            $lesson_index = 1;
                            foreach ($all_lessons as $lesson):
                                $lesson_id = $lesson->ID;
                                $lesson_title = $lesson->post_title;
                                $lesson_completed = $is_enrolled ? learndash_lesson_completed_by_user($lesson_id, $user_id) : false;
                                $lesson_url = get_permalink($lesson_id);

                                // Get quizzes in this lesson
                                $quizzes = get_post_meta($lesson_id, 'quiz_list', true);
                                $quiz_count = is_array($quizzes) ? count($quizzes) : 0;
                            ?>
                            <div class="lesson-item <?php echo $lesson_completed ? 'lesson-item--completed' : ''; ?>">
                                <div class="lesson-item__icon">
                                    <?php if ($lesson_completed): ?>
                                        <i data-lucide="check-circle" class="lesson-completed-icon"></i>
                                    <?php else: ?>
                                        <i data-lucide="circle" class="lesson-pending-icon"></i>
                                    <?php endif; ?>
                                </div>

                                <div class="lesson-item__content">
                                    <h3 class="lesson-item__title">
                                        <a href="<?php echo esc_url($lesson_url); ?>">
                                            <?php echo esc_html($lesson_title); ?>
                                        </a>
                                    </h3>

                                    <?php if ($quiz_count > 0): ?>
                                    <p class="lesson-item__meta">
                                        <i data-lucide="help-circle"></i>
                                        <span><?php echo $quiz_count; ?> quiz<?php echo $quiz_count !== 1 ? 'ze' : ''; ?></span>
                                    </p>
                                    <?php endif; ?>
                                </div>

                                <div class="lesson-item__action">
                                    <?php if ($is_enrolled): ?>
                                        <a href="<?php echo esc_url($lesson_url); ?>" class="btn btn-sm btn-outline">
                                            <i data-lucide="arrow-right"></i>
                                            <?php echo $lesson_completed ? 'Rivedi' : 'Inizia'; ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="lesson-item__locked">
                                            <i data-lucide="lock"></i>
                                            Bloccato
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php
                                $lesson_index++;
                            endforeach;
                            ?>
                        </div>
                    </section>
                    <?php endif; ?>

                </main>

                <!-- SIDEBAR -->
                <aside class="single-course__sidebar">

                    <!-- ENROLLMENT STATUS & PROGRESS WIDGET -->
                    <div class="single-course__widget">
                        <h3 class="single-course__widget-title">
                            <i data-lucide="activity"></i>
                            Stato Corso
                        </h3>

                        <?php if ($is_enrolled): ?>
                            <div class="course-status-enrolled">
                                <p class="course-status__label">Progresso:</p>
                                <div class="progress-bar">
                                    <div class="progress-bar__fill" style="width: <?php echo $course_progress; ?>%"></div>
                                </div>
                                <p class="course-status__percentage"><?php echo $course_progress; ?>%</p>
                                <p class="course-status__details">
                                    <?php echo $lessons_completed; ?> / <?php echo $total_lessons; ?> lezioni completate
                                </p>

                                <?php if ($course_progress === 100): ?>
                                <div class="course-status__completed-badge">
                                    <i data-lucide="award"></i>
                                    <span>Corso Completato!</span>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="course-status-not-enrolled">
                                <p class="course-status__message">Non sei iscritto a questo corso</p>
                                <button class="btn btn-primary btn-block" @click="enrollCourse(<?php echo $course_id; ?>)">
                                    <i data-lucide="plus"></i>
                                    Iscriviti al Corso
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- COURSE INFO WIDGET -->
                    <div class="single-course__widget">
                        <h3 class="single-course__widget-title">
                            <i data-lucide="info"></i>
                            Informazioni
                        </h3>

                        <div class="single-course__info-item">
                            <strong>Lezioni:</strong>
                            <span><?php echo count($all_lessons); ?></span>
                        </div>

                        <div class="single-course__info-item">
                            <strong>Stato Iscrizione:</strong>
                            <span class="course-status-badge <?php echo $is_enrolled ? 'enrolled' : 'not-enrolled'; ?>">
                                <?php echo $is_enrolled ? 'Iscritto' : 'Non Iscritto'; ?>
                            </span>
                        </div>

                        <div class="single-course__info-item">
                            <strong>Data Pubblicazione:</strong>
                            <span><?php echo get_the_date('d M Y'); ?></span>
                        </div>
                    </div>

                    <!-- COURSE ACTIONS WIDGET -->
                    <?php if ($is_enrolled): ?>
                    <div class="single-course__widget">
                        <h3 class="single-course__widget-title">
                            <i data-lucide="zap"></i>
                            Azioni
                        </h3>

                        <?php if (!empty($all_lessons)): ?>
                        <a href="<?php echo esc_url(get_permalink($all_lessons[0]->ID)); ?>" class="btn btn-primary btn-block">
                            <i data-lucide="arrow-right"></i>
                            <?php echo $lessons_completed > 0 ? 'Continua' : 'Inizia Lezione'; ?>
                        </a>
                        <?php endif; ?>

                        <?php if ($course_progress === 100): ?>
                        <button class="btn btn-secondary btn-block" @click="downloadCourseCertificate(<?php echo $course_id; ?>)">
                            <i data-lucide="download"></i>
                            Scarica Certificato
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                </aside>

            </div>

        </div>
    </main>
</div>

<?php
get_footer();
?>
