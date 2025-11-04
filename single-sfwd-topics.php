<?php
/**
 * Template Name: Singolo Argomento LearnDash
 * Description: Template per visualizzare un argomento (topic) LearnDash
 *
 * @package Meridiana Child
 */

if (!defined('ABSPATH')) exit;

// Helper function to check if topic is completed by user
function is_topic_completed_by_user($topic_id, $user_id) {
    $completed_topics = get_user_meta($user_id, '_completed_topic_' . $topic_id, true);
    return !empty($completed_topics);
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

// Get parent lesson
$lesson_id = get_post_meta($topic_id, 'lesson_id', true);
$lesson = $lesson_id ? get_post($lesson_id) : null;

// Get parent course (through lesson)
$course_id = $lesson_id ? get_post_meta($lesson_id, 'course_id', true) : null;
$course = $course_id ? get_post($course_id) : null;

// Check if user is enrolled in course
$enrolled_meta = get_user_meta($user_id, '_enrolled_course_' . $course_id, true);
$is_enrolled = !empty($enrolled_meta);

// Check if topic is completed
$topic_completed = get_user_meta($user_id, '_completed_topic_' . $topic_id, true);
$is_completed = !empty($topic_completed);

// Get all topics in this lesson to show navigation
$topics_in_lesson = array();
if ($lesson_id) {
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

// Get current topic index for navigation
$current_topic_index = 0;
foreach ($topics_in_lesson as $index => $t) {
    if ($t->ID === $topic_id) {
        $current_topic_index = $index;
        break;
    }
}

// Get quizzes in this topic
$quizzes_in_topic = array();
$quizzes_query = new WP_Query(array(
    'post_type' => 'sfwd-quiz',
    'posts_per_page' => -1,
    'orderby' => 'menu_order',
    'order' => 'ASC',
    'meta_query' => array(
        array(
            'key' => 'topic_id',
            'value' => $topic_id,
        ),
    ),
));
$quizzes_in_topic = $quizzes_query->posts;
wp_reset_postdata();

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
                        <span>Torna alla Lezione: <?php echo esc_html($lesson->post_title); ?></span>
                    </a>
                <?php elseif ($course): ?>
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

            <!-- TOPIC HEADER -->
            <header class="single-topic__header">
                <div class="single-topic__header-content">
                    <h1 class="single-topic__title"><?php echo esc_html($topic_title); ?></h1>

                    <?php if ($lesson): ?>
                    <p class="single-topic__lesson">
                        <i data-lucide="book"></i>
                        Parte di: <a href="<?php echo esc_url(get_permalink($lesson_id)); ?>"><?php echo esc_html($lesson->post_title); ?></a>
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

                    <!-- TOPIC CONTENT -->
                    <div class="single-topic__body wysiwyg-content">
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

                    <!-- QUIZZES IN THIS TOPIC -->
                    <?php if (!empty($quizzes_in_topic) && $is_enrolled): ?>
                    <section class="single-topic__quizzes">
                        <h2 class="single-topic__section-title">
                            <i data-lucide="help-circle"></i>
                            Quiz di questo Argomento (<?php echo count($quizzes_in_topic); ?>)
                        </h2>

                        <div class="topic-quizzes-list">
                            <?php foreach ($quizzes_in_topic as $quiz): ?>
                            <div class="quiz-item">
                                <div class="quiz-item__icon">
                                    <i data-lucide="help-circle"></i>
                                </div>

                                <div class="quiz-item__content">
                                    <h3 class="quiz-item__title">
                                        <a href="<?php echo esc_url(get_permalink($quiz->ID)); ?>">
                                            <?php echo esc_html($quiz->post_title); ?>
                                        </a>
                                    </h3>
                                    <?php if ($quiz->post_excerpt): ?>
                                    <p class="quiz-item__description"><?php echo esc_html($quiz->post_excerpt); ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="quiz-item__action">
                                    <a href="<?php echo esc_url(get_permalink($quiz->ID)); ?>" class="btn btn-sm btn-outline">
                                        <i data-lucide="arrow-right"></i>
                                        Accedi al Quiz
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php endif; ?>

                </main>

                <!-- SIDEBAR -->
                <aside class="single-topic__sidebar">

                    <!-- TOPIC STATUS WIDGET -->
                    <div class="single-topic__widget">
                        <h3 class="single-topic__widget-title">
                            <i data-lucide="activity"></i>
                            Stato Argomento
                        </h3>

                        <?php if ($is_enrolled): ?>
                            <div class="topic-status">
                                <?php if ($is_completed): ?>
                                    <div class="topic-status__badge topic-status__badge--completed">
                                        <i data-lucide="check-circle"></i>
                                        <span>Completato</span>
                                    </div>
                                <?php else: ?>
                                    <div class="topic-status__badge topic-status__badge--pending">
                                        <i data-lucide="circle"></i>
                                        <span>In Corso</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <p class="topic-status__message">Non sei iscritto a questo corso</p>
                        <?php endif; ?>
                    </div>

                    <!-- TOPIC INFO WIDGET -->
                    <div class="single-topic__widget">
                        <h3 class="single-topic__widget-title">
                            <i data-lucide="info"></i>
                            Informazioni
                        </h3>

                        <div class="single-topic__info-item">
                            <strong>Quiz:</strong>
                            <span><?php echo count($quizzes_in_topic); ?></span>
                        </div>

                        <div class="single-topic__info-item">
                            <strong>Data Pubblicazione:</strong>
                            <span><?php echo get_the_date('d M Y'); ?></span>
                        </div>
                    </div>

                    <!-- TOPIC NAVIGATION WIDGET -->
                    <?php if (!empty($topics_in_lesson)): ?>
                    <div class="single-topic__widget">
                        <h3 class="single-topic__widget-title">
                            <i data-lucide="list"></i>
                            Argomenti della Lezione
                        </h3>

                        <div class="topics-navigation">
                            <?php foreach ($topics_in_lesson as $idx => $t): ?>
                            <a href="<?php echo esc_url(get_permalink($t->ID)); ?>"
                               class="topics-navigation__item <?php echo $t->ID === $topic_id ? 'active' : ''; ?>">
                                <span class="topics-navigation__number"><?php echo $idx + 1; ?></span>
                                <span class="topics-navigation__title"><?php echo esc_html($t->post_title); ?></span>
                                <?php if (is_topic_completed_by_user($t->ID, $user_id)): ?>
                                <span class="topics-navigation__status">
                                    <i data-lucide="check-circle"></i>
                                </span>
                                <?php endif; ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- TOPIC ACTIONS WIDGET -->
                    <?php if ($is_enrolled): ?>
                    <div class="single-topic__widget">
                        <h3 class="single-topic__widget-title">
                            <i data-lucide="zap"></i>
                            Azioni
                        </h3>

                        <?php if (!$is_completed): ?>
                        <button class="btn btn-primary btn-block"
                                @click="markTopicComplete(<?php echo $topic_id; ?>, <?php echo $lesson_id; ?>)"
                                x-data="topicComplete()"
                                :class="{ 'disabled': isLoading }">
                            <i data-lucide="check"></i>
                            Segna come Completato
                        </button>
                        <?php else: ?>
                        <div class="topic-completed-badge">
                            <i data-lucide="award"></i>
                            <span>Argomento Completato!</span>
                        </div>
                        <?php endif; ?>

                        <!-- Navigation Buttons -->
                        <?php if ($current_topic_index > 0): ?>
                        <a href="<?php echo esc_url(get_permalink($topics_in_lesson[$current_topic_index - 1]->ID)); ?>" class="btn btn-secondary btn-block btn-sm">
                            <i data-lucide="arrow-left"></i>
                            Argomento Precedente
                        </a>
                        <?php endif; ?>

                        <?php if ($current_topic_index < count($topics_in_lesson) - 1): ?>
                        <a href="<?php echo esc_url(get_permalink($topics_in_lesson[$current_topic_index + 1]->ID)); ?>" class="btn btn-secondary btn-block btn-sm">
                            <i data-lucide="arrow-right"></i>
                            Argomento Successivo
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
    Alpine.data('topicComplete', () => ({
        isLoading: false,
        errorMessage: '',

        async markTopicComplete(topicId, lessonId) {
            if (!topicId || !lessonId) return;

            this.isLoading = true;
            this.errorMessage = '';

            try {
                const userId = parseInt(document.querySelector('[data-user-id]')?.dataset.userId || 0);
                const nonce = document.querySelector('[data-nonce]')?.dataset.nonce || '';
                const restUrl = document.querySelector('[data-rest-url]')?.dataset.restUrl || '/wp-json/learnDash/v1/';

                // Mark topic as complete via user meta
                const response = await fetch(
                    `${restUrl}topics/${topicId}/mark-completed`,
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': nonce,
                        },
                        body: JSON.stringify({
                            topic_id: topicId,
                            lesson_id: lessonId
                        }),
                    }
                );

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                // Reload page to show updated status
                window.location.reload();

            } catch (error) {
                console.error('Error marking topic complete:', error);
                this.errorMessage = 'Errore nel segnare l\'argomento come completato.';
                this.isLoading = false;
            }
        }
    }));
});
</script>

<?php
get_footer();
?>
