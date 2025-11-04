<?php
/**
 * Template Name: Singolo Corso LearnDash
 * Description: Template per visualizzare un corso LearnDash con lezioni, quiz e progresso
 *
 * @package Meridiana Child
 */

if (!defined('ABSPATH')) exit;

// DEPRECATED - Usa meridiana_lesson_is_completed() da learndash-helpers.php
// function is_lesson_completed_by_user($lesson_id, $user_id) {
//     $completed_lessons = get_user_meta($user_id, '_completed_lesson_' . $lesson_id, true);
//     return !empty($completed_lessons);
// }

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

// Get course status and progress using LearnDash native functions
$is_enrolled = meridiana_user_is_enrolled($user_id, $course_id);

// Get course progress from LearnDash
$progress_data = meridiana_get_user_course_progress($user_id, $course_id);
$course_progress = $progress_data['percentage'];
$lessons_completed = $progress_data['completed'];
$total_lessons = $progress_data['total'];

// Get all lessons using helper function
$all_lessons = meridiana_get_course_lessons($course_id);

// Get course featured image
$featured_image = get_the_post_thumbnail_url($course_id, 'large');

// ============================================
// GET QUIZ RESULTS (for completed courses)
// ============================================

$quiz_results = null;
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'lezioni';

if ($current_tab === 'risultati' && $course_progress === 100) {
    // Get the final quiz (Quizzo)
    $final_quiz = get_page_by_path('quizzo', OBJECT, 'sfwd-quiz');

    if ($final_quiz) {
        // Get quiz results from user meta
        $quiz_results = get_user_meta($user_id, '_quiz_results_' . $final_quiz->ID, true);
    }
}

?>

<div class="content-wrapper">
    <?php
    // Navigation
    get_template_part('templates/parts/navigation/mobile-bottom-nav');
    get_template_part('templates/parts/navigation/desktop-sidebar');
    ?>

    <main
        class="page-single-course"
        x-data="courseEnroll(<?php echo $course_id; ?>, '<?php echo get_permalink($course_id); ?>')"
        x-cloak
        data-nonce="<?php echo wp_create_nonce('wp_rest'); ?>"
        data-user-id="<?php echo $user_id; ?>"
        data-rest-url="<?php echo esc_url(rest_url('learnDash/v1/')); ?>">
        <div class="single-course-container">

            <!-- BREADCRUMB -->
            <?php meridiana_render_breadcrumb(); ?>

            <!-- BACK BUTTON -->
            <div class="back-link-wrapper">
                <a href="<?php echo esc_url(home_url('/corsi/')); ?>" class="back-link">
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
                            $current_lesson_index = 0;

                            // Trova la prima lezione non completata (current lesson)
                            if ($is_enrolled) {
                                foreach ($all_lessons as $idx => $lesson) {
                                    if (!is_lesson_completed_by_user($lesson->ID, $user_id)) {
                                        $current_lesson_index = $idx;
                                        break;
                                    }
                                }
                            }

                            foreach ($all_lessons as $idx => $lesson):
                                $lesson_id = $lesson->ID;
                                $lesson_title = $lesson->post_title;
                                $lesson_completed = $is_enrolled ? is_lesson_completed_by_user($lesson_id, $user_id) : false;
                                $lesson_url = get_permalink($lesson_id);

                                // PROCEDURAL: lesson is accessible only if:
                                // 1. User is enrolled
                                // 2. It's the current lesson (next to complete) or already completed
                                $is_current_lesson = ($idx === $current_lesson_index);
                                $is_accessible = $is_enrolled && ($lesson_completed || $is_current_lesson);

                                // Get quizzes in this lesson
                                $quizzes = get_post_meta($lesson_id, 'quiz_list', true);
                                $quiz_count = is_array($quizzes) ? count($quizzes) : 0;
                            ?>
                            <div class="lesson-item <?php echo $lesson_completed ? 'lesson-item--completed' : ''; echo $is_current_lesson ? ' lesson-item--current' : ''; echo !$is_accessible ? ' lesson-item--locked' : ''; ?>">
                                <div class="lesson-item__icon">
                                    <?php if ($lesson_completed): ?>
                                        <i data-lucide="check-circle" class="lesson-completed-icon"></i>
                                    <?php elseif ($is_current_lesson): ?>
                                        <i data-lucide="play-circle" class="lesson-current-icon"></i>
                                    <?php else: ?>
                                        <i data-lucide="lock" class="lesson-locked-icon"></i>
                                    <?php endif; ?>
                                </div>

                                <div class="lesson-item__content">
                                    <h3 class="lesson-item__title">
                                        <?php if ($is_accessible): ?>
                                            <a href="<?php echo esc_url($lesson_url); ?>">
                                                <?php echo esc_html($lesson_title); ?>
                                            </a>
                                        <?php else: ?>
                                            <span><?php echo esc_html($lesson_title); ?></span>
                                        <?php endif; ?>
                                    </h3>

                                    <?php if ($is_current_lesson && !$lesson_completed): ?>
                                    <p class="lesson-item__status">
                                        <i data-lucide="arrow-right"></i>
                                        <span>Lezione Corrente</span>
                                    </p>
                                    <?php endif; ?>

                                    <?php if ($quiz_count > 0): ?>
                                    <p class="lesson-item__meta">
                                        <i data-lucide="help-circle"></i>
                                        <span><?php echo $quiz_count; ?> quiz<?php echo $quiz_count !== 1 ? 'ze' : ''; ?></span>
                                    </p>
                                    <?php endif; ?>
                                </div>

                                <div class="lesson-item__action">
                                    <?php if ($is_accessible): ?>
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

                    <!-- QUIZ RESULTS SECTION (for completed courses) -->
                    <?php if ($current_tab === 'risultati' && $course_progress === 100): ?>
                    <section class="single-course__section quiz-results-section">
                        <h2 class="single-course__section-title">
                            <i data-lucide="bar-chart-2"></i>
                            Risultati Quiz
                        </h2>

                        <div class="quiz-results-container">
                            <?php if ($quiz_results): ?>
                                <!-- Results with WP Pro Quiz data -->
                                <div class="quiz-results-content">
                                    <div class="quiz-results-header">
                                        <h3><?php echo esc_html(get_the_title($final_quiz->ID)); ?></h3>
                                        <div class="quiz-results-score">
                                            <span class="score-label">Punteggio Ottenuto:</span>
                                            <span class="score-value"><?php echo isset($quiz_results['score']) ? esc_html($quiz_results['score']) : 'N/A'; ?></span>
                                        </div>
                                    </div>

                                    <!-- Detailed Results - This will be populated by WP Pro Quiz data -->
                                    <div class="quiz-results-details">
                                        <p class="note">I risultati dettagliati vengono gestiti da LearnDash. Puoi rivedere le tue risposte accedendo al quiz.</p>
                                        <a href="<?php echo esc_url(get_permalink($final_quiz->ID)); ?>" class="btn btn-primary">
                                            <i data-lucide="arrow-right"></i>
                                            Rivedi Quiz
                                        </a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- No results yet - guide to take quiz -->
                                <div class="quiz-results-empty">
                                    <i data-lucide="info"></i>
                                    <p>Completa il quiz finale per visualizzare i tuoi risultati.</p>
                                </div>
                            <?php endif; ?>
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

                        <a href="<?php echo esc_url(add_query_arg('tab', 'risultati', get_permalink($course_id))); ?>" class="btn btn-info btn-block">
                            <i data-lucide="bar-chart-2"></i>
                            Risultati Quiz
                        </a>
                        <?php endif; ?>

                        <!-- Reset Course Button -->
                        <button class="btn btn-outline btn-warning btn-block" @click="showResetModal(<?php echo $course_id; ?>)">
                            <i data-lucide="refresh-cw"></i>
                            Riprovare Corso
                        </button>
                    </div>
                    <?php endif; ?>

                    <!-- Reset Course Confirmation Modal -->
                    <div class="reset-modal-wrapper" x-show="isResetModalOpen" style="display: none;">
                        <div class="reset-modal-overlay" @click="closeResetModal()"></div>
                        <div class="reset-modal">
                            <div class="reset-modal__header">
                                <h3 class="reset-modal__title">
                                    <i data-lucide="alert-circle"></i>
                                    Riprovare Corso
                                </h3>
                                <button class="reset-modal__close" @click="closeResetModal()">
                                    <i data-lucide="x"></i>
                                </button>
                            </div>

                            <div class="reset-modal__body">
                                <p>Sei sicuro di voler riprovare questo corso?</p>
                                <p><strong><?php echo esc_html($course_title); ?></strong></p>
                                <div class="reset-modal__warning">
                                    <i data-lucide="alert-triangle"></i>
                                    <p>Riprovando il corso tutti i tuoi progressi verranno azzerati. Rimarrai iscritto al corso e potrai ricominciare da capo.</p>
                                </div>
                            </div>

                            <div class="reset-modal__footer">
                                <button class="btn btn-outline" @click="closeResetModal()">
                                    Annulla
                                </button>
                                <button class="btn btn-warning" @click="confirmReset(<?php echo $course_id; ?>)" :disabled="isResetting">
                                    <span x-text="isResetting ? 'Azzeramento in corso...' : 'Sì, Riprova'"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                </aside>

            </div>

        </div>
    </main>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('courseEnroll', (courseId, courseUrl) => ({
        courseId: courseId,
        courseUrl: courseUrl,
        isLoading: false,
        isResetting: false,
        isResetModalOpen: false,
        errorMessage: '',
        successMessage: '',

        async enrollCourse(id) {
            if (!id) return;

            this.isLoading = true;
            this.errorMessage = '';

            try {
                // Get nonce from page
                const nonce = document.querySelector('[data-nonce]')?.dataset.nonce || '';
                const userId = parseInt(document.querySelector('[data-user-id]')?.dataset.userId || 0);
                const restUrl = document.querySelector('[data-rest-url]')?.dataset.restUrl || '/wp-json/learnDash/v1/';

                const response = await fetch(
                    `${restUrl}user/${userId}/courses/${id}/enroll`,
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': nonce,
                        },
                    }
                );

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                // Redirect immediately after successful enrollment
                window.location.href = this.courseUrl;

            } catch (error) {
                console.error('Error enrolling in course:', error);
                this.errorMessage = 'Errore nell\'iscrizione. Per favore riprova.';
                this.isLoading = false;
            }
        },

        // Modal management
        showResetModal(id) {
            this.isResetModalOpen = true;
        },

        closeResetModal() {
            this.isResetModalOpen = false;
            this.errorMessage = '';
        },

        // Reset course progress
        async confirmReset(id) {
            if (!id) return;

            this.isResetting = true;
            this.errorMessage = '';

            try {
                const nonce = document.querySelector('[data-nonce]')?.dataset.nonce || '';
                const userId = parseInt(document.querySelector('[data-user-id]')?.dataset.userId || 0);
                const restUrl = document.querySelector('[data-rest-url]')?.dataset.restUrl || '/wp-json/learnDash/v1/';

                const response = await fetch(
                    `${restUrl}user/${userId}/courses/${id}/reset`,
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': nonce,
                        },
                    }
                );

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                // Close modal and reload page to reflect new state
                this.isResetModalOpen = false;
                this.successMessage = 'Corso azzerato. Reindirizzamento...';
                setTimeout(() => {
                    window.location.reload();
                }, 1000);

            } catch (error) {
                console.error('Error resetting course:', error);
                this.errorMessage = 'Errore nell\'azzeramento del corso. Per favore riprova.';
                this.isResetting = false;
            }
        },

        // Download certificate
        async downloadCourseCertificate(id) {
            if (!id) return;

            this.isLoading = true;

            try {
                const nonce = document.querySelector('[data-nonce]')?.dataset.nonce || '';
                const restUrl = document.querySelector('[data-rest-url]')?.dataset.restUrl || '/wp-json/learnDash/v1/';

                const response = await fetch(
                    `${restUrl}courses/${id}/certificate`,
                    {
                        method: 'GET',
                        headers: {
                            'X-WP-Nonce': nonce,
                        },
                    }
                );

                if (!response.ok) {
                    throw new Error('Error downloading certificate');
                }

                const data = await response.json();
                if (data.download_url) {
                    const link = document.createElement('a');
                    link.href = data.download_url;
                    link.download = data.filename || 'certificato.pdf';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }

            } catch (error) {
                console.error('Error downloading certificate:', error);
                this.errorMessage = 'Errore nel download del certificato. Per favore riprova.';
            } finally {
                this.isLoading = false;
            }
        }
    }));
});
</script>

<?php
get_footer();
?>
