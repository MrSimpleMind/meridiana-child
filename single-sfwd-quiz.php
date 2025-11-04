<?php
/**
 * Template Name: Singolo Quiz LearnDash
 * Description: Template per visualizzare e completare un quiz LearnDash
 *
 * @package Meridiana Child
 */

if (!defined('ABSPATH')) exit;

get_header();

$quiz_id = get_the_ID();
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// ============================================
// QUIZ DATA
// ============================================

$quiz = get_post($quiz_id);
$quiz_title = get_the_title($quiz_id);
$quiz_content = get_post_field('post_content', $quiz_id);
$quiz_excerpt = get_post_field('post_excerpt', $quiz_id);

// Get parent lesson and course
$lesson_id = get_post_meta($quiz_id, 'lesson_id', true);
$course_id = get_post_meta($quiz_id, 'course_id', true);

$lesson = $lesson_id ? get_post($lesson_id) : null;
$course = $course_id ? get_post($course_id) : null;

// Get topic this quiz belongs to (if any)
$topic_id = get_post_meta($quiz_id, 'topic_id', true);
$topic = $topic_id ? get_post($topic_id) : null;

// Check if user is enrolled in course
$enrolled_meta = get_user_meta($user_id, '_enrolled_course_' . $course_id, true);
$is_enrolled = !empty($enrolled_meta);

// Get quiz settings
$quiz_settings = get_post_meta($quiz_id, '_sfwd-quiz', true);
$quiz_pro_id = get_post_meta($quiz_id, 'quiz_pro', true);

// Get quiz questions (if using custom meta, not WP Pro Quiz)
$questions = array();
if (!$quiz_pro_id) {
    // Fallback: try to get questions from custom meta or post children
    $questions_query = new WP_Query(array(
        'post_type' => 'sfwd-question',
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'meta_key' => 'quiz_id',
        'meta_value' => $quiz_id,
    ));
    $questions = $questions_query->posts;
    wp_reset_postdata();
}

// For WP Pro Quiz integration, we'd need the WP Pro Quiz data
// This is a simplified version for custom meta-based quizzes

?>

<div class="content-wrapper">
    <?php
    // Navigation
    get_template_part('templates/parts/navigation/mobile-bottom-nav');
    get_template_part('templates/parts/navigation/desktop-sidebar');
    ?>

    <main class="page-single-quiz"
          data-user-id="<?php echo esc_attr($user_id); ?>"
          data-quiz-id="<?php echo esc_attr($quiz_id); ?>"
          data-nonce="<?php echo esc_attr(wp_create_nonce('wp_rest')); ?>"
          data-rest-url="<?php echo esc_attr('/wp-json/learnDash/v1/'); ?>"
          x-data="quizTaking(<?php echo esc_attr($quiz_id); ?>)"
          x-cloak>
        <div class="single-quiz-container">

            <!-- BREADCRUMB / BACK BUTTON -->
            <div class="back-link-wrapper">
                <?php if ($topic): ?>
                    <a href="<?php echo esc_url(get_permalink($topic_id)); ?>" class="back-link">
                        <i data-lucide="arrow-left"></i>
                        <span>Torna all'Argomento: <?php echo esc_html($topic->post_title); ?></span>
                    </a>
                <?php elseif ($lesson): ?>
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

            <!-- QUIZ HEADER -->
            <header class="single-quiz__header">
                <div class="single-quiz__header-content">
                    <h1 class="single-quiz__title"><?php echo esc_html($quiz_title); ?></h1>

                    <?php if ($quiz_excerpt): ?>
                    <p class="single-quiz__description"><?php echo esc_html($quiz_excerpt); ?></p>
                    <?php endif; ?>

                    <?php if ($topic): ?>
                    <p class="single-quiz__meta">
                        <i data-lucide="layers"></i>
                        Parte di: <a href="<?php echo esc_url(get_permalink($topic_id)); ?>"><?php echo esc_html($topic->post_title); ?></a>
                    </p>
                    <?php elseif ($lesson): ?>
                    <p class="single-quiz__meta">
                        <i data-lucide="book"></i>
                        Parte di: <a href="<?php echo esc_url(get_permalink($lesson_id)); ?>"><?php echo esc_html($lesson->post_title); ?></a>
                    </p>
                    <?php endif; ?>
                </div>
            </header>

            <!-- MAIN LAYOUT: Content + Sidebar -->
            <div class="single-quiz__layout">

                <!-- MAIN CONTENT -->
                <main class="single-quiz__content">

                    <!-- ENROLLMENT CHECK -->
                    <?php if (!$is_enrolled): ?>
                    <div class="quiz-not-enrolled">
                        <div class="alert alert-warning">
                            <i data-lucide="alert-circle"></i>
                            <p>Non sei iscritto al corso per accedere a questo quiz.</p>
                            <a href="<?php echo esc_url(get_permalink($course_id)); ?>" class="btn btn-primary">Iscriviti al Corso</a>
                        </div>
                    </div>
                    <?php else: ?>

                        <!-- QUIZ DESCRIPTION -->
                        <?php if ($quiz_content): ?>
                        <section class="single-quiz__description wysiwyg-content">
                            <?php echo wp_kses_post($quiz_content); ?>
                        </section>
                        <?php endif; ?>

                        <!-- QUIZ FORM / QUESTIONS -->
                        <section class="single-quiz__questions">
                            <div class="quiz-form-container">

                                <?php if (!empty($questions)): ?>
                                    <!-- Questions from custom meta -->
                                    <form @submit.prevent="submitQuiz" class="quiz-form">
                                        <?php foreach ($questions as $idx => $question): ?>
                                        <div class="quiz-question">
                                            <div class="quiz-question__header">
                                                <h3 class="quiz-question__number">
                                                    Domanda <?php echo $idx + 1; ?> di <?php echo count($questions); ?>
                                                </h3>
                                                <h4 class="quiz-question__title"><?php echo esc_html($question->post_title); ?></h4>
                                            </div>

                                            <div class="quiz-question__content wysiwyg-content">
                                                <?php echo wp_kses_post($question->post_content); ?>
                                            </div>

                                            <div class="quiz-question__answers">
                                                <!-- Answer options would go here -->
                                                <!-- This is a placeholder for dynamic answer rendering -->
                                                <p class="quiz-placeholder">Opzioni di risposta verranno caricate dinamicamente</p>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>

                                        <div class="quiz-form__actions">
                                            <button type="submit" class="btn btn-primary btn-lg" :disabled="isSubmitting">
                                                <i data-lucide="check"></i>
                                                <span x-text="isSubmitting ? 'Invio in corso...' : 'Invia Risposte'"></span>
                                            </button>
                                        </div>
                                    </form>

                                <?php else: ?>
                                    <!-- WP Pro Quiz Notice -->
                                    <?php if ($quiz_pro_id): ?>
                                    <div class="alert alert-info">
                                        <i data-lucide="info"></i>
                                        <p>Questo quiz utilizza WP Pro Quiz.</p>
                                        <p>Le domande verranno caricate tramite l'integrazione WP Pro Quiz di LearnDash.</p>
                                    </div>
                                    <?php else: ?>
                                    <div class="alert alert-warning">
                                        <i data-lucide="alert-triangle"></i>
                                        <p>Nessuna domanda trovata per questo quiz.</p>
                                    </div>
                                    <?php endif; ?>
                                <?php endif; ?>

                            </div>
                        </section>

                    <?php endif; ?>

                </main>

                <!-- SIDEBAR -->
                <aside class="single-quiz__sidebar">

                    <!-- QUIZ INFO WIDGET -->
                    <div class="single-quiz__widget">
                        <h3 class="single-quiz__widget-title">
                            <i data-lucide="info"></i>
                            Informazioni Quiz
                        </h3>

                        <?php if (!empty($questions)): ?>
                        <div class="single-quiz__info-item">
                            <strong>Domande:</strong>
                            <span><?php echo count($questions); ?></span>
                        </div>
                        <?php endif; ?>

                        <div class="single-quiz__info-item">
                            <strong>Tipo:</strong>
                            <span><?php echo $quiz_pro_id ? 'WP Pro Quiz' : 'Custom'; ?></span>
                        </div>

                        <div class="single-quiz__info-item">
                            <strong>Data Pubblicazione:</strong>
                            <span><?php echo get_the_date('d M Y'); ?></span>
                        </div>
                    </div>

                    <!-- QUIZ STATUS WIDGET -->
                    <?php if ($is_enrolled): ?>
                    <div class="single-quiz__widget">
                        <h3 class="single-quiz__widget-title">
                            <i data-lucide="activity"></i>
                            Stato Quiz
                        </h3>

                        <div class="quiz-status">
                            <p class="quiz-status__message">
                                <i data-lucide="play-circle"></i>
                                Pronto a iniziare il quiz?
                            </p>
                            <button class="btn btn-primary btn-block" @click="scrollToQuiz()">
                                <i data-lucide="arrow-down"></i>
                                Vai alle Domande
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- INSTRUCTIONS WIDGET -->
                    <div class="single-quiz__widget">
                        <h3 class="single-quiz__widget-title">
                            <i data-lucide="book-open"></i>
                            Istruzioni
                        </h3>

                        <ul class="quiz-instructions">
                            <li>Leggi attentamente ogni domanda</li>
                            <li>Seleziona la risposta più appropriata</li>
                            <li>Puoi rivedere le tue risposte prima di inviare</li>
                            <li>Una volta inviate, le risposte non possono essere cambiate</li>
                        </ul>
                    </div>

                </aside>

            </div>

        </div>
    </main>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('quizTaking', (quizId) => ({
        quizId: quizId,
        isSubmitting: false,
        errorMessage: '',
        successMessage: '',

        async submitQuiz() {
            this.isSubmitting = true;
            this.errorMessage = '';
            this.successMessage = '';

            try {
                const nonce = document.querySelector('[data-nonce]')?.dataset.nonce || '';
                const restUrl = document.querySelector('[data-rest-url]')?.dataset.restUrl || '/wp-json/learnDash/v1/';

                // Collect form data
                const formData = new FormData(document.querySelector('.quiz-form'));
                const answers = Object.fromEntries(formData);

                // Submit quiz answers
                const response = await fetch(
                    `${restUrl}quizzes/${this.quizId}/submit`,
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': nonce,
                        },
                        body: JSON.stringify({
                            quiz_id: this.quizId,
                            answers: answers,
                        }),
                    }
                );

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    this.successMessage = 'Quiz completato con successo!';
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    this.errorMessage = data.message || 'Errore nell\'invio del quiz.';
                }

            } catch (error) {
                console.error('Error submitting quiz:', error);
                this.errorMessage = 'Errore nell\'invio del quiz. Per favore riprova.';
            } finally {
                this.isSubmitting = false;
            }
        },

        scrollToQuiz() {
            document.querySelector('.single-quiz__questions')?.scrollIntoView({ behavior: 'smooth' });
        }
    }));
});
</script>

<?php
get_footer();
?>
