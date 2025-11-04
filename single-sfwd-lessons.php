<?php
/**
 * Template Name: Singola Lezione LearnDash
 * Description: Template per visualizzare una lezione LearnDash
 *
 * @package Meridiana Child
 */

if (!defined('ABSPATH')) exit;

// DEPRECATED - Usa meridiana_quiz_is_completed() da learndash-helpers.php
// function is_quiz_completed_by_user($quiz_id, $user_id) {
//     $completed = get_user_meta($user_id, '_completed_quiz_' . $quiz_id, true);
//     return !empty($completed);
// }

get_header();

$lesson_id = get_the_ID();
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// ============================================
// LESSON DATA
// ============================================

$lesson = get_post($lesson_id);
$lesson_title = get_the_title($lesson_id);
$lesson_content = get_post_field('post_content', $lesson_id);

// Get course this lesson belongs to
$course_id = get_post_meta($lesson_id, 'course_id', true);
$course = $course_id ? get_post($course_id) : null;

// Check if user is enrolled in course using LearnDash native
$is_enrolled = meridiana_user_is_enrolled($user_id, $course_id);

// Check if lesson is completed
$is_completed = meridiana_lesson_is_completed($user_id, $lesson_id);

// ============================================
// GET QUIZZES IN THIS LESSON (Direct children via post_parent)
// ============================================

// Get quizzes in this lesson using helper function
$quizzes_in_lesson = $is_enrolled ? meridiana_get_lesson_quizzes($lesson_id) : [];

// ============================================
// GET ALL LESSONS IN COURSE FOR NAVIGATION
// ============================================

// Get all lessons in course using helper function
$all_lessons = $course_id ? meridiana_get_course_lessons($course_id) : [];

$current_lesson_index = 0;
if (!empty($all_lessons)) {
    // Find current lesson index
    foreach ($all_lessons as $idx => $l) {
        if ($l->ID === $lesson_id) {
            $current_lesson_index = $idx;
            break;
        }
    }
}

// ============================================
// GET ALL LESSONS IN COURSE + DETERMINE PROGRESSION
// ============================================

$all_course_lessons = array();
$lesson_index = 0;
$is_last_lesson = false;
$next_lesson = null;

if ($course_id) {
    $lessons_query = new WP_Query(array(
        'post_type' => 'sfwd-lessons',
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'meta_key' => 'course_id',
        'meta_value' => $course_id,
    ));
    $all_course_lessons = $lessons_query->posts;
    wp_reset_postdata();

    // Find current lesson position
    foreach ($all_course_lessons as $idx => $l) {
        if ($l->ID === $lesson_id) {
            $lesson_index = $idx;
            $is_last_lesson = ($idx === count($all_course_lessons) - 1);
            // Get next lesson if not last
            if (!$is_last_lesson) {
                $next_lesson = $all_course_lessons[$idx + 1];
            } else {
                // If last lesson, look for final quiz by slug "quizzo"
                $final_quiz = get_page_by_path('quizzo', OBJECT, 'sfwd-quiz');
                if ($final_quiz) {
                    $next_lesson = $final_quiz;
                }
            }
            break;
        }
    }
}

// ============================================
// CALCULATE LESSON PROGRESS (based on quizzes only)
// ============================================

$lesson_progress = 0;
$total_items = count($quizzes_in_lesson);
$completed_items = 0;

if ($total_items > 0) {
    foreach ($quizzes_in_lesson as $quiz) {
        if (is_quiz_completed_by_user($quiz->ID, $user_id)) {
            $completed_items++;
        }
    }
    $lesson_progress = round(($completed_items / $total_items) * 100);
}

?>

<div class="content-wrapper">
    <?php
    // Navigation
    get_template_part('templates/parts/navigation/mobile-bottom-nav');
    get_template_part('templates/parts/navigation/desktop-sidebar');
    ?>

    <main class="page-single-lesson single-lesson-page"
          data-user-id="<?php echo esc_attr($user_id); ?>"
          data-lesson-id="<?php echo esc_attr($lesson_id); ?>"
          data-course-id="<?php echo esc_attr($course_id); ?>"
          data-nonce="<?php echo esc_attr(wp_create_nonce('wp_rest')); ?>"
          data-rest-url="<?php echo esc_attr('/wp-json/learnDash/v1/'); ?>">
        <div class="single-lesson-container page-container">

            <!-- BREADCRUMB / BACK BUTTON -->
            <div class="back-link-wrapper">
                <?php if ($course): ?>
                    <a href="<?php echo esc_url(get_permalink($course_id)); ?>" class="back-link">
                        <i data-lucide="arrow-left"></i>
                        <span>Torna al Corso: <?php echo esc_html($course->post_title); ?></span>
                    </a>
                <?php else: ?>
                    <a href="<?php echo esc_url(home_url('/corsi')); ?>" class="back-link">
                        <i data-lucide="arrow-left"></i>
                        <span>Torna ai Corsi</span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- LESSON HEADER -->
            <header class="single-lesson__header">
                <div class="single-lesson__header-content">
                    <h1 class="single-lesson__title"><?php echo esc_html($lesson_title); ?></h1>
                    <?php if ($course): ?>
                    <p class="single-lesson__course">
                        <i data-lucide="book"></i>
                        Parte di: <a href="<?php echo esc_url(get_permalink($course_id)); ?>"><?php echo esc_html($course->post_title); ?></a>
                    </p>
                    <?php endif; ?>
                </div>
            </header>

            <!-- MAIN LAYOUT: Content + Sidebar -->
            <div class="single-lesson__layout">

                <!-- MAIN CONTENT -->
                <main class="single-lesson__content"
                      data-lesson-id="<?php echo $lesson_id; ?>"
                      data-course-id="<?php echo $course_id; ?>"
                      data-user-id="<?php echo $user_id; ?>"
                      data-nonce="<?php echo wp_create_nonce('learnDash_lesson_nonce'); ?>"
                      data-rest-url="<?php echo rest_url('learnDash/v1/'); ?>">

                    <!-- LESSON DESCRIPTION -->
                    <div class="single-lesson__description wysiwyg-content">
                        <?php
                        if ($is_enrolled) {
                            echo wp_kses_post($lesson_content);
                        } else {
                            echo '<div class="lesson-not-enrolled">';
                            echo '<p>Non sei iscritto al corso per visualizzare questa lezione.</p>';
                            echo '<a href="' . esc_url(get_permalink($course_id)) . '" class="btn btn-primary">Vai al Corso</a>';
                            echo '</div>';
                        }
                        ?>
                    </div>

                    <!-- QUIZZES SECTION -->
                    <?php if (!empty($quizzes_in_lesson) && $is_enrolled): ?>
                    <section class="single-lesson__quizzes">
                        <h2 class="single-lesson__section-title">
                            <i data-lucide="help-circle"></i>
                            Quiz (<?php echo count($quizzes_in_lesson); ?>)
                        </h2>

                        <div class="lesson-quizzes-list">
                            <?php
                            $quiz_index = 1;
                            foreach ($quizzes_in_lesson as $quiz):
                                $quiz_id = $quiz->ID;
                                $quiz_completed = is_quiz_completed_by_user($quiz_id, $user_id);
                            ?>
                            <div class="quiz-item <?php echo $quiz_completed ? 'quiz-item--completed' : ''; ?>">
                                <div class="quiz-item__icon">
                                    <?php if ($quiz_completed): ?>
                                        <i data-lucide="check-square" class="quiz-completed-icon"></i>
                                    <?php else: ?>
                                        <i data-lucide="help-circle" class="quiz-pending-icon"></i>
                                    <?php endif; ?>
                                </div>

                                <div class="quiz-item__content">
                                    <h3 class="quiz-item__title">
                                        <a href="<?php echo esc_url(get_permalink($quiz_id)); ?>">
                                            <?php echo esc_html($quiz->post_title); ?>
                                        </a>
                                    </h3>
                                    <?php if ($quiz->post_excerpt): ?>
                                    <p class="quiz-item__description"><?php echo esc_html($quiz->post_excerpt); ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="quiz-item__action">
                                    <a href="<?php echo esc_url(get_permalink($quiz_id)); ?>" class="btn btn-sm btn-outline">
                                        <i data-lucide="arrow-right"></i>
                                        <?php echo $quiz_completed ? 'Rivedi' : 'Accedi'; ?>
                                    </a>
                                </div>
                            </div>
                            <?php
                                $quiz_index++;
                            endforeach;
                            ?>
                        </div>
                    </section>
                    <?php endif; ?>

                </main>

                <!-- SIDEBAR -->
                <aside class="single-lesson__sidebar">

                    <!-- LESSON STATUS WIDGET -->
                    <div class="single-lesson__widget">
                        <h3 class="single-lesson__widget-title">
                            <i data-lucide="activity"></i>
                            Stato Lezione
                        </h3>

                        <?php if ($is_enrolled): ?>
                            <div class="lesson-status">
                                <?php if ($total_items > 0): ?>
                                    <p class="lesson-status__label">Progresso:</p>
                                    <div class="progress-bar">
                                        <div class="progress-bar__fill" style="width: <?php echo $lesson_progress; ?>%"></div>
                                    </div>
                                    <p class="lesson-status__percentage"><?php echo $lesson_progress; ?>%</p>
                                    <p class="lesson-status__details">
                                        <?php echo $completed_items; ?> / <?php echo $total_items; ?> elementi completati
                                    </p>
                                <?php endif; ?>

                                <?php if ($is_completed): ?>
                                <div class="lesson-completed-badge">
                                    <i data-lucide="award"></i>
                                    <span>Lezione Completata!</span>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <p class="lesson-status__message">Non sei iscritto a questo corso</p>
                        <?php endif; ?>
                    </div>

                    <!-- LESSON INFO WIDGET -->
                    <div class="single-lesson__widget">
                        <h3 class="single-lesson__widget-title">
                            <i data-lucide="info"></i>
                            Informazioni
                        </h3>

                        <?php if (!empty($quizzes_in_lesson)): ?>
                        <div class="single-lesson__info-item">
                            <strong>Quiz:</strong>
                            <span><?php echo count($quizzes_in_lesson); ?></span>
                        </div>
                        <?php endif; ?>

                        <div class="single-lesson__info-item">
                            <strong>Data Pubblicazione:</strong>
                            <span><?php echo get_the_date('d M Y'); ?></span>
                        </div>
                    </div>

                    <!-- LESSON NAVIGATION WIDGET (Procedural: only show completed and current lesson) -->
                    <?php if (!empty($all_course_lessons) && count($all_course_lessons) > 1): ?>
                    <div class="single-lesson__widget">
                        <h3 class="single-lesson__widget-title">
                            <i data-lucide="list"></i>
                            Progresso Corso
                        </h3>

                        <div class="lessons-navigation">
                            <?php
                            // Helper function to check if lesson is completed
                            $check_lesson_completed = function($l_id) use ($user_id) {
                                return !empty(get_user_meta($user_id, '_completed_lesson_' . $l_id, true));
                            };

                            foreach ($all_course_lessons as $idx => $l):
                                $is_completed = $check_lesson_completed($l->ID);
                                $is_current = ($l->ID === $lesson_id);
                            ?>
                            <div class="lessons-navigation__item <?php echo $is_current ? 'active' : ''; echo $is_completed ? 'completed' : ''; ?>">
                                <span class="lessons-navigation__number"><?php echo $idx + 1; ?></span>
                                <span class="lessons-navigation__title"><?php echo esc_html($l->post_title); ?></span>
                                <?php if ($is_completed): ?>
                                    <i data-lucide="check" class="lessons-navigation__checkmark"></i>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- LESSON ACTIONS WIDGET -->
                    <?php if ($is_enrolled): ?>
                    <div class="single-lesson__widget">
                        <h3 class="single-lesson__widget-title">
                            <i data-lucide="zap"></i>
                            Azioni
                        </h3>

                        <!-- LESSON ACTIONS: Button to mark complete and auto-navigate -->
                        <div x-data="lessonCompleteHandler({
                            isCompleted: <?php echo $is_completed ? 'true' : 'false'; ?>,
                            hasQuizzes: <?php echo $total_items > 0 ? 'true' : 'false'; ?>,
                            isLastLesson: <?php echo $is_last_lesson ? 'true' : 'false'; ?>,
                            nextLessonUrl: '<?php echo esc_js($next_lesson ? get_permalink($next_lesson->ID) : get_permalink($course_id)); ?>'
                        })">

                            <!-- Main Action Button with Lock Icon Badge -->
                            <div class="lesson-action-wrapper">
                                <button class="btn btn-primary btn-block"
                                        type="button"
                                        @click="handleClick()"
                                        :disabled="isLoading || (hasQuizzes && !isCompleted)"
                                        :class="{ 'is-loading': isLoading }">
                                    <i data-lucide="check"></i>
                                    <span x-text="getButtonText()"></span>
                                </button>

                                <!-- Open Lock Icon Badge (top right) -->
                                <div class="lock-icon-badge" :class="{ 'visible': isCompleted }">
                                    <i data-lucide="unlock"></i>
                                </div>
                            </div>

                            <!-- Error Message (if any) -->
                            <template x-if="errorMessage">
                                <div class="lesson-error-message" style="margin-top: 10px; color: #dc3545;">
                                    <i data-lucide="alert-circle"></i>
                                    <span x-text="errorMessage"></span>
                                </div>
                            </template>

                            <!-- Quiz Warning (shows when quizzes exist but not completed) -->
                            <template x-if="hasQuizzes && !isCompleted">
                                <div class="lesson-quiz-warning" style="margin-top: 10px; padding: 10px; background-color: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                                    <i data-lucide="alert-circle" style="color: #ffc107;"></i>
                                    <p style="margin: 5px 0 0 0; font-size: 0.875rem;">Completa i <?php echo $total_items; ?> quiz prima di procedere</p>
                                </div>
                            </template>

                        </div>

                    </div>
                    <?php endif; ?>

                </aside>

            </div>

        </div>
    </main>
</div>

<style>
/* Lesson Action Wrapper with Lock Badge */
.lesson-action-wrapper {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.lesson-action-btn {
    position: relative;
    transition: all 0.3s ease;
}

.lesson-action-btn.btn-locked {
    background-color: var(--color-primary);
    cursor: not-allowed;
    opacity: 0.7;
}

.lesson-action-btn.btn-unlocked {
    background-color: var(--color-success);
}

.lesson-action-btn.is-loading {
    opacity: 0.6;
    cursor: wait;
}

/* Lock Icon Badge - Top Right */
.lock-icon-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 36px;
    height: 36px;
    background-color: #9CA3AF;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    opacity: 0;
    transform: scale(0);
    transition: all 0.3s ease;
    pointer-events: none;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.lock-icon-badge.visible {
    opacity: 1;
    transform: scale(1);
    animation: lockOpenAppear 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.lock-icon-badge i {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
}

.btn-icon-wrapper {
    display: inline-flex;
    align-items: center;
    position: relative;
}

.btn-icon {
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    display: inline-block;
}

.btn-icon.hidden {
    display: none;
}

.lock-icon:not(.hidden) {
    animation: lockBounce 0.6s ease-out;
}

.unlock-icon:not(.hidden) {
    animation: unlockOpen 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes lockBounce {
    0% {
        transform: scale(1) rotate(0deg);
        opacity: 1;
    }
    50% {
        transform: scale(1.1) rotate(-10deg);
    }
    100% {
        transform: scale(1) rotate(0deg);
        opacity: 1;
    }
}

@keyframes unlockOpen {
    0% {
        transform: scale(0) rotate(-90deg);
        opacity: 0;
    }
    50% {
        transform: scale(1.1) rotate(10deg);
    }
    100% {
        transform: scale(1) rotate(0deg);
        opacity: 1;
    }
}

@keyframes lockOpenAppear {
    0% {
        transform: scale(0) rotate(-90deg);
        opacity: 0;
    }
    50% {
        transform: scale(1.15) rotate(10deg);
    }
    100% {
        transform: scale(1) rotate(0deg);
        opacity: 1;
    }
}

.lesson-completed-badge {
    margin-top: 15px;
    padding: 12px;
    background-color: rgba(16, 185, 129, 0.1);
    border-left: 4px solid var(--color-success);
    border-radius: 4px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--color-success);
    font-weight: 600;
}

.lesson-completed-badge i {
    width: 24px;
    height: 24px;
}
</style>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('lessonCompleteHandler', (initialState = {}) => ({
        isCompleted: initialState.isCompleted || false,
        hasQuizzes: initialState.hasQuizzes || false,
        isLastLesson: initialState.isLastLesson || false,
        nextLessonUrl: initialState.nextLessonUrl || '',
        isLoading: false,
        errorMessage: '',

        getButtonText() {
            if (this.isLoading) {
                return 'Elaborazione...';
            }
            if (this.isLastLesson) {
                return 'Vai al Quiz';
            }
            return 'Completa Lezione';
        },

        async handleClick() {
            console.log('Button clicked! hasQuizzes:', this.hasQuizzes, 'isCompleted:', this.isCompleted);

            // Se ha quiz ma non completati, non fare nulla
            if (this.hasQuizzes && !this.isCompleted) {
                console.log('Quiz exist but not completed - returning');
                return;
            }

            const mainElement = document.querySelector('[data-lesson-id]');
            const lessonId = parseInt(mainElement?.dataset.lessonId || 0);
            const courseId = parseInt(mainElement?.dataset.courseId || 0);

            console.log('Extracted lessonId:', lessonId, 'courseId:', courseId);

            if (!lessonId || !courseId) {
                this.errorMessage = 'Errore: ID lezione o corso non trovati';
                console.error('Missing IDs:', { lessonId, courseId });
                return;
            }

            await this.markLessonComplete(lessonId, courseId);
        },

        async markLessonComplete(lessonId, courseId) {
            this.isLoading = true;
            this.errorMessage = '';

            try {
                const mainElement = document.querySelector('[data-lesson-id]');
                const userId = parseInt(mainElement?.dataset.userId || 0);
                const nonce = mainElement?.dataset.nonce || '';
                const restUrl = mainElement?.dataset.restUrl || '/wp-json/learnDash/v1/';

                console.log('Making API call:', { userId, nonce: nonce.substring(0, 10) + '...', restUrl, lessonId });

                // Mark lesson as complete via user meta
                const response = await fetch(
                    `${restUrl}lessons/${lessonId}/mark-viewed`,
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': nonce,
                        },
                        body: JSON.stringify({ duration: 0 }),
                    }
                );

                const data = await response.json();

                console.log('API Response:', { status: response.status, ok: response.ok, data });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${data.message || data.code || 'Unknown error'}`);
                }

                console.log('Lesson marked complete! nextLessonUrl:', this.nextLessonUrl);

                // Redirect immediately to next lesson or quiz
                if (this.nextLessonUrl) {
                    console.log('Redirecting to:', this.nextLessonUrl);
                    window.location.href = this.nextLessonUrl;
                } else {
                    console.warn('No nextLessonUrl provided');
                }

            } catch (error) {
                console.error('Error marking lesson complete:', error);
                this.errorMessage = 'Errore: ' + error.message;
                this.isLoading = false;
            }
        }
    }));
});
</script>

<?php
get_footer();
?>
