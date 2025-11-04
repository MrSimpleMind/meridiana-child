<?php
/**
 * Template Name: Singolo Argomento LearnDash
 * Description: Template per visualizzare un argomento (topic) LearnDash
 *
 * Gerarchia: Corso > Lezione > Argomento > Quiz
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

$topic_id = get_the_ID();
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// ============================================
// TOPIC DATA
// ============================================

$topic = get_post($topic_id);
$topic_title = get_the_title($topic_id);
$topic_content = get_post_field('post_content', $topic_id);

// Get lesson this topic belongs to
$lesson_id = get_post_meta($topic_id, 'lesson_id', true);
$lesson = $lesson_id ? get_post($lesson_id) : null;

// Get course this lesson belongs to
$course_id = $lesson_id ? get_post_meta($lesson_id, 'course_id', true) : 0;
$course = $course_id ? get_post($course_id) : null;

// Check if user is enrolled in course
$enrolled_meta = get_user_meta($user_id, '_enrolled_course_' . $course_id, true);
$is_enrolled = !empty($enrolled_meta);

// Check if topic is completed
$topic_completed = get_user_meta($user_id, '_completed_topic_' . $topic_id, true);
$is_completed = !empty($topic_completed);

// ============================================
// GET QUIZZES IN THIS TOPIC
// ============================================

$quizzes_in_topic = array();
if ($is_enrolled) {
    $quizzes_query = new WP_Query(array(
        'post_type' => 'sfwd-quiz',
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'meta_key' => 'topic_id',
        'meta_value' => $topic_id,
    ));
    $quizzes_in_topic = $quizzes_query->posts;
    wp_reset_postdata();
}

// ============================================
// GET ALL TOPICS IN LESSON FOR NAVIGATION
// ============================================

$all_topics = array();
$current_topic_index = 0;
if ($lesson_id) {
    $topics_query = new WP_Query(array(
        'post_type' => 'sfwd-topic',
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'meta_key' => 'lesson_id',
        'meta_value' => $lesson_id,
    ));
    $all_topics = $topics_query->posts;
    wp_reset_postdata();

    // Find current topic index
    foreach ($all_topics as $idx => $t) {
        if ($t->ID === $topic_id) {
            $current_topic_index = $idx;
            break;
        }
    }
}

// Note: For topics, no progress calculation needed - just show quiz completion status

?>

<div class="content-wrapper">
    <?php
    // Navigation
    get_template_part('templates/parts/navigation/mobile-bottom-nav');
    get_template_part('templates/parts/navigation/desktop-sidebar');
    ?>

    <main class="page-single-topic"
          data-user-id="<?php echo esc_attr($user_id); ?>"
          data-nonce="<?php echo esc_attr(wp_create_nonce('wp_rest')); ?>"
          data-rest-url="<?php echo esc_attr('/wp-json/learnDash/v1/'); ?>">
        <div class="single-topic-container">

            <!-- BREADCRUMB / BACK BUTTON -->
            <div class="back-link-wrapper">
                <?php if ($lesson): ?>
                    <a href="<?php echo esc_url(get_permalink($lesson_id)); ?>" class="back-link">
                        <i data-lucide="arrow-left"></i>
                        <span>Torna a: <?php echo esc_html($lesson->post_title); ?></span>
                    </a>
                <?php elseif ($course): ?>
                    <a href="<?php echo esc_url(get_permalink($course_id)); ?>" class="back-link">
                        <i data-lucide="arrow-left"></i>
                        <span>Torna al Corso</span>
                    </a>
                <?php else: ?>
                    <a href="<?php echo esc_url(home_url('/corsi')); ?>" class="back-link">
                        <i data-lucide="arrow-left"></i>
                        <span>Torna ai Corsi</span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- TOPIC HEADER -->
            <header class="single-topic__header">
                <div class="single-topic__header-content">
                    <h1 class="single-topic__title"><?php echo esc_html($topic_title); ?></h1>
                    <?php if ($lesson): ?>
                    <p class="single-topic__lesson">
                        <i data-lucide="book"></i>
                        Parte della Lezione: <a href="<?php echo esc_url(get_permalink($lesson_id)); ?>"><?php echo esc_html($lesson->post_title); ?></a>
                    </p>
                    <?php endif; ?>
                    <?php if ($course): ?>
                    <p class="single-topic__course">
                        <i data-lucide="graduation-cap"></i>
                        Corso: <a href="<?php echo esc_url(get_permalink($course_id)); ?>"><?php echo esc_html($course->post_title); ?></a>
                    </p>
                    <?php endif; ?>
                </div>
            </header>

            <!-- MAIN LAYOUT: Content + Sidebar -->
            <div class="single-topic__layout">

                <!-- MAIN CONTENT -->
                <main class="single-topic__content">

                    <!-- TOPIC DESCRIPTION -->
                    <div class="single-topic__description wysiwyg-content">
                        <?php
                        if ($is_enrolled) {
                            echo wp_kses_post($topic_content);
                        } else {
                            echo '<div class="topic-not-enrolled">';
                            echo '<p>Non sei iscritto al corso per visualizzare questo argomento.</p>';
                            echo '<a href="' . esc_url(get_permalink($course_id)) . '" class="btn btn-primary">Vai al Corso</a>';
                            echo '</div>';
                        }
                        ?>
                    </div>

                    <!-- QUIZZES SECTION -->
                    <?php if (!empty($quizzes_in_topic) && $is_enrolled): ?>
                    <section class="single-topic__quizzes">
                        <h2 class="single-topic__section-title">
                            <i data-lucide="help-circle"></i>
                            Quiz (<?php echo count($quizzes_in_topic); ?>)
                        </h2>

                        <div class="topic-quizzes-list">
                            <?php
                            foreach ($quizzes_in_topic as $quiz):
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
                <aside class="single-topic__sidebar">

                    <!-- LESSON STATUS WIDGET -->
                    <div class="single-topic__widget">
                        <h3 class="single-topic__widget-title">
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
                    <div class="single-topic__widget">
                        <h3 class="single-topic__widget-title">
                            <i data-lucide="info"></i>
                            Informazioni
                        </h3>

                        <?php if (!empty($topics_in_lesson)): ?>
                        <div class="single-topic__info-item">
                            <strong>Argomenti:</strong>
                            <span><?php echo count($topics_in_lesson); ?></span>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($quizzes_in_lesson)): ?>
                        <div class="single-topic__info-item">
                            <strong>Quiz:</strong>
                            <span><?php echo count($quizzes_in_lesson); ?></span>
                        </div>
                        <?php endif; ?>

                        <div class="single-topic__info-item">
                            <strong>Data Pubblicazione:</strong>
                            <span><?php echo get_the_date('d M Y'); ?></span>
                        </div>
                    </div>

                    <!-- LESSON NAVIGATION WIDGET -->
                    <?php if (!empty($all_lessons) && count($all_lessons) > 1): ?>
                    <div class="single-topic__widget">
                        <h3 class="single-topic__widget-title">
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
                    <div class="single-topic__widget">
                        <h3 class="single-topic__widget-title">
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
