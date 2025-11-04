<?php
/**
 * Template Name: Singola Lezione LearnDash
 * Description: Template per visualizzare una lezione LearnDash
 *
 * @package Meridiana Child
 */

if (!defined('ABSPATH')) exit;

// Helper function to check if topic/quiz is completed by user
function is_topic_completed_by_user($topic_id, $user_id) {
    $completed = get_user_meta($user_id, '_completed_topic_' . $topic_id, true);
    return !empty($completed);
}

function is_quiz_completed_by_user($quiz_id, $user_id) {
    $completed = get_user_meta($user_id, '_completed_quiz_' . $quiz_id, true);
    return !empty($completed);
}

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

// Check if user is enrolled in course
$enrolled_meta = get_user_meta($user_id, '_enrolled_course_' . $course_id, true);
$is_enrolled = !empty($enrolled_meta);

// Check if lesson is completed
$lesson_completed = get_user_meta($user_id, '_completed_lesson_' . $lesson_id, true);
$is_completed = !empty($lesson_completed);

// ============================================
// GET TOPICS IN THIS LESSON
// ============================================

$topics_in_lesson = array();
if ($is_enrolled) {
    $topics_query = new WP_Query(array(
        'post_type' => 'sfwd-topic',
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'meta_key' => 'lesson_id',
        'meta_value' => $lesson_id,
    ));
    $topics_in_lesson = $topics_query->posts;
    wp_reset_postdata();
}

// ============================================
// GET QUIZZES IN THIS LESSON
// ============================================

$quizzes_in_lesson = array();
if ($is_enrolled) {
    $quizzes_query = new WP_Query(array(
        'post_type' => 'sfwd-quiz',
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'meta_key' => 'lesson_id',
        'meta_value' => $lesson_id,
    ));
    $quizzes_in_lesson = $quizzes_query->posts;
    wp_reset_postdata();
}

// ============================================
// GET ALL LESSONS IN COURSE FOR NAVIGATION
// ============================================

$all_lessons = array();
$current_lesson_index = 0;
if ($course_id) {
    $lessons_query = new WP_Query(array(
        'post_type' => 'sfwd-lessons',
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'meta_key' => 'course_id',
        'meta_value' => $course_id,
    ));
    $all_lessons = $lessons_query->posts;
    wp_reset_postdata();

    // Find current lesson index
    foreach ($all_lessons as $idx => $l) {
        if ($l->ID === $lesson_id) {
            $current_lesson_index = $idx;
            break;
        }
    }
}

// ============================================
// CALCULATE LESSON PROGRESS
// ============================================

$lesson_progress = 0;
$total_items = count($topics_in_lesson) + count($quizzes_in_lesson);
$completed_items = 0;

if ($total_items > 0) {
    foreach ($topics_in_lesson as $topic) {
        if (is_topic_completed_by_user($topic->ID, $user_id)) {
            $completed_items++;
        }
    }
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

    <main class="page-single-lesson"
          data-user-id="<?php echo esc_attr($user_id); ?>"
          data-nonce="<?php echo esc_attr(wp_create_nonce('wp_rest')); ?>"
          data-rest-url="<?php echo esc_attr('/wp-json/learnDash/v1/'); ?>">
        <div class="single-lesson-container">

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
                <main class="single-lesson__content">

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

                    <!-- TOPICS SECTION -->
                    <?php if (!empty($topics_in_lesson) && $is_enrolled): ?>
                    <section class="single-lesson__topics">
                        <h2 class="single-lesson__section-title">
                            <i data-lucide="layers"></i>
                            Argomenti (<?php echo count($topics_in_lesson); ?>)
                        </h2>

                        <div class="lesson-topics-list">
                            <?php
                            $topic_index = 1;
                            foreach ($topics_in_lesson as $topic):
                                $topic_id = $topic->ID;
                                $topic_completed = is_topic_completed_by_user($topic_id, $user_id);
                            ?>
                            <div class="topic-item <?php echo $topic_completed ? 'topic-item--completed' : ''; ?>">
                                <div class="topic-item__icon">
                                    <?php if ($topic_completed): ?>
                                        <i data-lucide="check-circle" class="topic-completed-icon"></i>
                                    <?php else: ?>
                                        <i data-lucide="circle" class="topic-pending-icon"></i>
                                    <?php endif; ?>
                                </div>

                                <div class="topic-item__content">
                                    <h3 class="topic-item__title">
                                        <a href="<?php echo esc_url(get_permalink($topic_id)); ?>">
                                            <?php echo esc_html($topic->post_title); ?>
                                        </a>
                                    </h3>
                                </div>

                                <div class="topic-item__action">
                                    <a href="<?php echo esc_url(get_permalink($topic_id)); ?>" class="btn btn-sm btn-outline">
                                        <i data-lucide="arrow-right"></i>
                                        <?php echo $topic_completed ? 'Rivedi' : 'Inizia'; ?>
                                    </a>
                                </div>
                            </div>
                            <?php
                                $topic_index++;
                            endforeach;
                            ?>
                        </div>
                    </section>
                    <?php endif; ?>

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

                        <?php if (!empty($topics_in_lesson)): ?>
                        <div class="single-lesson__info-item">
                            <strong>Argomenti:</strong>
                            <span><?php echo count($topics_in_lesson); ?></span>
                        </div>
                        <?php endif; ?>

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

                    <!-- LESSON NAVIGATION WIDGET -->
                    <?php if (!empty($all_lessons) && count($all_lessons) > 1): ?>
                    <div class="single-lesson__widget">
                        <h3 class="single-lesson__widget-title">
                            <i data-lucide="list"></i>
                            Lezioni del Corso
                        </h3>

                        <div class="lessons-navigation">
                            <?php foreach ($all_lessons as $idx => $l): ?>
                            <a href="<?php echo esc_url(get_permalink($l->ID)); ?>"
                               class="lessons-navigation__item <?php echo $l->ID === $lesson_id ? 'active' : ''; ?>">
                                <span class="lessons-navigation__number"><?php echo $idx + 1; ?></span>
                                <span class="lessons-navigation__title"><?php echo esc_html($l->post_title); ?></span>
                            </a>
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

                        <?php if (!$is_completed && $total_items === 0): ?>
                        <button class="btn btn-primary btn-block"
                                @click="markLessonComplete(<?php echo $lesson_id; ?>, <?php echo $course_id; ?>)"
                                x-data="lessonComplete()"
                                :class="{ 'disabled': isLoading }">
                            <i data-lucide="check"></i>
                            Segna come Completata
                        </button>
                        <?php elseif ($is_completed): ?>
                        <div class="lesson-completed-badge">
                            <i data-lucide="award"></i>
                            <span>Lezione Completata!</span>
                        </div>
                        <?php endif; ?>

                        <!-- Navigation Buttons -->
                        <?php if ($current_lesson_index > 0): ?>
                        <a href="<?php echo esc_url(get_permalink($all_lessons[$current_lesson_index - 1]->ID)); ?>" class="btn btn-secondary btn-block btn-sm">
                            <i data-lucide="arrow-left"></i>
                            Lezione Precedente
                        </a>
                        <?php endif; ?>

                        <?php if ($current_lesson_index < count($all_lessons) - 1): ?>
                        <a href="<?php echo esc_url(get_permalink($all_lessons[$current_lesson_index + 1]->ID)); ?>" class="btn btn-secondary btn-block btn-sm">
                            <i data-lucide="arrow-right"></i>
                            Lezione Successiva
                        </a>
                        <?php endif; ?>

                        <?php if ($course): ?>
                        <a href="<?php echo esc_url(get_permalink($course_id)); ?>" class="btn btn-outline btn-block btn-sm">
                            <i data-lucide="arrow-up"></i>
                            Torna al Corso
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                </aside>

            </div>

        </div>
    </main>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('lessonComplete', () => ({
        isLoading: false,
        errorMessage: '',

        async markLessonComplete(lessonId, courseId) {
            if (!lessonId || !courseId) return;

            this.isLoading = true;
            this.errorMessage = '';

            try {
                const userId = parseInt(document.querySelector('[data-user-id]')?.dataset.userId || 0);
                const nonce = document.querySelector('[data-nonce]')?.dataset.nonce || '';
                const restUrl = document.querySelector('[data-rest-url]')?.dataset.restUrl || '/wp-json/learnDash/v1/';

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

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                // Reload page to show updated progress
                window.location.reload();

            } catch (error) {
                console.error('Error marking lesson complete:', error);
                this.errorMessage = 'Errore nel segnare la lezione come completata.';
                this.isLoading = false;
            }
        }
    }));
});
</script>

<?php
get_footer();
?>
