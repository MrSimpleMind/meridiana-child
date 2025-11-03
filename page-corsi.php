<?php
/**
 * Template Name: Pagina Corsi
 *
 * @package Meridiana Child
 */

// Verifica permessi
if (!is_user_logged_in()) {
    wp_redirect(home_url('/login'));
    exit;
}

get_header();

$current_user = wp_get_current_user();
$user_id = $current_user->ID;
?>

<div class="content-wrapper">
    <?php get_template_part('templates/parts/navigation/desktop-sidebar'); ?>

    <main class="page-corsi">
        <div class="corsi-dashboard"
             data-user-id="<?php echo esc_attr($user_id); ?>"
             data-ajax-url="<?php echo esc_attr(admin_url('admin-ajax.php')); ?>"
             data-rest-url="<?php echo esc_attr(rest_url('learnDash/v1/')); ?>"
             data-nonce="<?php echo esc_attr(wp_create_nonce('wp_rest')); ?>"
             x-data="corsiDashboard()"
             x-cloak>

            <!-- TAB NAVIGATION -->
            <div class="corsi-tabs-container">
                <div class="container">
                    <div class="corsi-tabs">
                        <button type="button"
                                class="corsi-tabs__item"
                                :class="{ 'active': activeTab === 'courses' }"
                                @click="setTab('courses')">
                            <i data-lucide="play-circle"></i>
                            <span>Corsi</span>
                            <span class="corsi-tabs__badge" x-text="coursesCount" x-show="coursesCount > 0"></span>
                        </button>
                        <button type="button"
                                class="corsi-tabs__item"
                                :class="{ 'active': activeTab === 'completed' }"
                                @click="setTab('completed')">
                            <i data-lucide="check-circle"></i>
                            <span>Completati</span>
                            <span class="corsi-tabs__badge" x-text="completedCount" x-show="completedCount > 0"></span>
                        </button>
                        <button type="button"
                                class="corsi-tabs__item"
                                :class="{ 'active': activeTab === 'certificates' }"
                                @click="setTab('certificates')">
                            <i data-lucide="award"></i>
                            <span>Certificati</span>
                            <span class="corsi-tabs__badge" x-text="certificatesCount" x-show="certificatesCount > 0"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- TAB CONTENT -->
            <div class="corsi-content-container">
                <div class="container">
                    <div class="corsi-content">

                        <!-- TAB: COURSES (disponibili + in progress) -->
                        <div class="corsi-tab-pane" x-show="activeTab === 'courses'" x-cloak>
                            <div class="corsi-header">
                                <h2 class="corsi-header__title">Corsi Disponibili</h2>
                                <p class="corsi-header__subtitle">Iscriviti ai corsi o continua quelli in corso</p>
                            </div>

                            <template x-if="courses.length > 0">
                                <div class="corsi-grid">
                                    <template x-for="course in courses" :key="course.id">
                                        <div class="course-card">
                                            <div class="course-card__header">
                                                <h3 class="course-card__title" x-text="course.title"></h3>
                                                <template x-if="course.is_enrolled">
                                                    <span class="course-card__status course-card__status--in-progress">In Corso</span>
                                                </template>
                                                <template x-if="!course.is_enrolled">
                                                    <span class="course-card__status course-card__status--available">Disponibile</span>
                                                </template>
                                            </div>

                                            <div class="course-card__body">
                                                <p class="course-card__description" x-text="course.description"></p>

                                                <!-- Mostra progress solo se iscritto -->
                                                <template x-if="course.is_enrolled">
                                                    <div class="course-card__progress">
                                                        <div class="progress-bar">
                                                            <div class="progress-bar__fill" :style="{ width: course.progress + '%' }"></div>
                                                        </div>
                                                        <span class="course-card__progress-text" x-text="course.progress + '%'"></span>
                                                    </div>
                                                </template>
                                            </div>

                                            <div class="course-card__footer">
                                                <!-- Se iscritto: bottone Continua -->
                                                <template x-if="course.is_enrolled">
                                                    <a :href="course.url" class="btn btn-primary btn-sm">
                                                        <i data-lucide="arrow-right"></i>
                                                        Continua
                                                    </a>
                                                </template>
                                                <!-- Se non iscritto: bottone Iscriviti -->
                                                <template x-if="!course.is_enrolled">
                                                    <button @click="enrollCourse(course.id)" class="btn btn-primary btn-sm">
                                                        <i data-lucide="plus-circle"></i>
                                                        Iscriviti
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="courses.length === 0">
                                <div class="corsi-empty">
                                    <i data-lucide="inbox"></i>
                                    <h3>Nessun corso disponibile</h3>
                                    <p>Non ci sono corsi disponibili al momento.</p>
                                </div>
                            </template>
                        </div>

                        <!-- TAB: COMPLETED -->
                        <div class="corsi-tab-pane" x-show="activeTab === 'completed'" x-cloak>
                            <div class="corsi-header">
                                <h2 class="corsi-header__title">Corsi Completati</h2>
                                <p class="corsi-header__subtitle">Congratulazioni per i tuoi successi!</p>
                            </div>

                            <template x-if="completedCourses.length > 0">
                                <div class="corsi-grid">
                                    <template x-for="course in completedCourses" :key="course.id">
                                        <div class="course-card course-card--completed">
                                            <div class="course-card__header">
                                                <h3 class="course-card__title" x-text="course.title"></h3>
                                                <span class="course-card__status course-card__status--completed">Completato</span>
                                            </div>

                                            <div class="course-card__body">
                                                <p class="course-card__description" x-text="course.description"></p>
                                                <div class="course-card__completed-date">
                                                    <i data-lucide="calendar"></i>
                                                    <span x-text="'Completato il: ' + course.completedDate"></span>
                                                </div>
                                            </div>

                                            <div class="course-card__footer">
                                                <a :href="course.url" class="btn btn-secondary btn-sm">
                                                    <i data-lucide="external-link"></i>
                                                    Visualizza
                                                </a>
                                                <button @click="downloadCertificate(course.id)" class="btn btn-outline btn-sm">
                                                    <i data-lucide="download"></i>
                                                    Certificato
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="completedCourses.length === 0">
                                <div class="corsi-empty">
                                    <i data-lucide="inbox"></i>
                                    <h3>Nessun corso completato</h3>
                                    <p>Completa un corso per vederlo qui.</p>
                                </div>
                            </template>
                        </div>

                        <!-- TAB: CERTIFICATES -->
                        <div class="corsi-tab-pane" x-show="activeTab === 'certificates'" x-cloak>
                            <div class="corsi-header">
                                <h2 class="corsi-header__title">I Tuoi Certificati</h2>
                                <p class="corsi-header__subtitle">Certificati di completamento conseguiti</p>
                            </div>

                            <template x-if="certificates.length > 0">
                                <div class="certificates-grid">
                                    <template x-for="cert in certificates" :key="cert.id">
                                        <div class="certificate-card">
                                            <div class="certificate-card__icon">
                                                <i data-lucide="award"></i>
                                            </div>

                                            <div class="certificate-card__content">
                                                <h3 class="certificate-card__title" x-text="cert.courseName"></h3>
                                                <p class="certificate-card__date">
                                                    <i data-lucide="calendar"></i>
                                                    <span x-text="'Conseguito il: ' + cert.issuedDate"></span>
                                                </p>
                                                <p class="certificate-card__validity" x-show="cert.expiryDate">
                                                    <i data-lucide="alert-circle"></i>
                                                    <span x-text="'Validità fino al: ' + cert.expiryDate"></span>
                                                </p>
                                            </div>

                                            <div class="certificate-card__actions">
                                                <button @click="downloadCertificateFile(cert.id)" class="btn btn-outline btn-sm">
                                                    <i data-lucide="download"></i>
                                                    Scarica
                                                </button>
                                                <button @click="shareCertificate(cert.id)" class="btn btn-secondary btn-sm">
                                                    <i data-lucide="share-2"></i>
                                                    Condividi
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="certificates.length === 0">
                                <div class="corsi-empty">
                                    <i data-lucide="inbox"></i>
                                    <h3>Nessun certificato</h3>
                                    <p>Completa i corsi per ottenere i tuoi certificati.</p>
                                </div>
                            </template>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<?php get_footer(); ?>
