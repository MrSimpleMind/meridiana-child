<?php
/**
 * Template Name: Singola Lezione LearnDash
 * Description: Template per visualizzare una lezione LearnDash
 *
 * @package Meridiana Child
 */

if (!defined('ABSPATH')) exit;

get_header();

$lesson_id = get_the_ID();
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Get lesson data
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

            <!-- LESSON CONTENT -->
            <div class="single-lesson__content wysiwyg-content">
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

            <!-- LESSON ACTIONS -->
            <?php if ($is_enrolled): ?>
            <div class="single-lesson__actions">
                <button class="btn btn-success btn-lg"
                        @click="markLessonComplete(<?php echo $lesson_id; ?>, <?php echo $course_id; ?>)"
                        :class="{ 'disabled': isLoading }"
                        x-data="lessonComplete()">
                    <i data-lucide="<?php echo $is_completed ? 'check-circle' : 'play'; ?>"></i>
                    <?php echo $is_completed ? 'Lezione Completata' : 'Segna come Completata'; ?>
                </button>

                <?php if ($course): ?>
                <a href="<?php echo esc_url(get_permalink($course_id)); ?>" class="btn btn-secondary btn-lg">
                    <i data-lucide="arrow-right"></i>
                    Torna al Corso
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

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
