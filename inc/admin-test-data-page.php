<?php
/**
 * Admin Page for Test Data Generation
 *
 * Location: wp-admin/tools.php?page=meridiana-test-data
 * Description: Browser-based test data generator for development
 *
 * FEATURES:
 * - Generate 100 test users with full ACF fields
 * - Generate documents with repeater fields, relationships, images
 * - Generate 2000-3000 simulated document views
 * - Auto-cleanup on re-run (deletes old test data)
 * - ERASE ALL feature (delete everything except admin accounts)
 * - Real-time progress output
 * - Error handling and validation
 *
 * VERSION: 3.0 (Enhanced with full ACF support)
 */

if (!defined('ABSPATH')) {
    exit;
}

// Register admin page
add_action('admin_menu', function() {
    add_submenu_page(
        'tools.php',
        'Meridiana Test Data',
        'Test Data Generator',
        'manage_options',
        'meridiana-test-data',
        'meridiana_render_test_data_page'
    );
});

/**
 * Render the admin page
 */
function meridiana_render_test_data_page() {
    // Nonce verification for security
    $action_requested = isset($_POST['meridiana_action']) ? sanitize_text_field($_POST['meridiana_action']) : '';

    if ($action_requested && !wp_verify_nonce($_POST['_wpnonce'] ?? '', 'meridiana_test_data')) {
        wp_die('Security check failed');
    }

    ?>
    <div class="wrap">
        <h1>Meridiana Test Data Generator v3</h1>
        <p style="background: #fff3cd; padding: 12px; border: 1px solid #ffc107; border-radius: 4px; margin: 15px 0;">
            <strong>⚠ Development Tool:</strong> This generates realistic test data for development/testing.
            <br><strong>Auto-cleanup:</strong> Running "Generate" again will delete and replace all previously generated test data.
            <br><strong>ERASE ALL:</strong> Complete database wipe (all users/posts except admin accounts).
            <br><strong>Security:</strong> Only accessible to admins. Delete this file when moving to production.
        </p>

        <?php
        // Check if generation was requested
        if ($action_requested === 'generate') {
            echo '<div id="test-data-output" style="background: #f5f5f5; padding: 15px; border: 1px solid #ddd; border-radius: 4px; font-family: monospace; overflow-y: auto; max-height: 600px; white-space: pre-wrap; word-wrap: break-word; line-height: 1.5;">';
            echo "Generating test data... Please wait.\n\n";

            // Capture output from generator
            ob_start();
            $generator = new MeridianaTestDataGenerator();
            $result = $generator->run();
            $output = ob_get_clean();

            echo htmlspecialchars($output);

            if ($result['success']) {
                echo "\n\n✓ Test data generation COMPLETED SUCCESSFULLY!";
                echo "\n\n📚 CONTENUTI E DOCUMENTI:";
                echo "\n  • Utenti: " . $result['users'];
                echo "\n  • Protocolli: " . $result['protocolli'];
                echo "\n  • Moduli: " . $result['moduli'];
                echo "\n  • Convenzioni: " . $result['convenzioni'];
                echo "\n  • Salute & Benessere: " . $result['salute'];
                echo "\n  • Comunicazioni: " . $result['comunicazioni'];
                echo "\n  • Organigrammi: " . $result['organigrammi'];
                echo "\n\n🎓 CORSI LEARNDASH (Gerarchia: Corso > Lezione > Argomento > Quiz):";
                echo "\n  • Corsi: " . $result['courses'];
                echo "\n  • Lezioni: " . $result['lessons'];
                echo "\n  • Argomenti (Topics): " . $result['topics'];
                echo "\n  • Quiz (legati ai topics): " . $result['quizzes'];
                echo "\n  • Domande Quiz: " . $result['questions'];
                echo "\n  • Iscrizioni Utenti: " . $result['enrollments'];
                echo "\n  • Corsi Completati: " . ($result['progress']['completed'] ?? 0);
                echo "\n  • Corsi in Corso: " . ($result['progress']['in_progress'] ?? 0);
                echo "\n\n📊 ANALYTICS:";
                echo "\n  • Document Views: " . $result['views'];
                echo "\n\n⏱️  Tempo totale: " . $result['elapsed'] . "s";
            } else {
                echo "\n\n✗ Generation FAILED with errors";
            }
            echo '</div>';
        } elseif ($action_requested === 'erase_all') {
            echo '<div id="test-data-output" style="background: #f5f5f5; padding: 15px; border: 1px solid #ddd; border-radius: 4px; font-family: monospace; overflow-y: auto; max-height: 600px; white-space: pre-wrap; word-wrap: break-word; line-height: 1.5;">';
            echo "Erasing all data (except admin accounts)... Please wait.\n\n";

            // Capture output from eraser
            ob_start();
            $generator = new MeridianaTestDataGenerator();
            $result = $generator->erase_all_data();
            $output = ob_get_clean();

            echo htmlspecialchars($output);

            if ($result['success']) {
                echo "\n\n✓ Database COMPLETELY WIPED!";
                echo "\n\nErased:";
                echo "\n  • Users: " . $result['users_deleted'];
                echo "\n  • Posts/CPT: " . $result['posts_deleted'];
                echo "\n  • Comments: " . $result['comments_deleted'];
                echo "\n  • Document Views: " . $result['views_deleted'];
            } else {
                echo "\n\n✗ Erase FAILED with errors";
            }
            echo '</div>';
        } else {
            ?>
            <form method="POST" style="margin: 20px 0;">
                <?php wp_nonce_field('meridiana_test_data'); ?>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <!-- GENERATE SECTION -->
                    <div style="border: 1px solid #ccc; padding: 20px; border-radius: 4px;">
                        <h2>Generate Test Data</h2>
                        <p>Creates realistic test data for development (v4.0 - With LearnDash Courses):</p>

                        <h3 style="color: #333; margin-top: 15px; font-size: 13px;">📚 Documents & Content:</h3>
                        <ul style="line-height: 1.8; font-size: 14px; margin: 10px 0;">
                            <li><strong>100 users</strong> with profilo, UDO, stato, codice fiscale</li>
                            <li><strong>25 protocolli</strong> with PDF, moduli allegati, taxonomy</li>
                            <li><strong>25 moduli</strong> with PDF, area-competenza taxonomy</li>
                            <li><strong>25 convenzioni</strong> with images, repeater attachments</li>
                            <li><strong>25 salute articles</strong> with repeater resources</li>
                            <li><strong>25 comunicazioni</strong> (posts)</li>
                            <li><strong>20 organigrammi</strong> with contacts</li>
                        </ul>

                        <h3 style="color: #333; margin-top: 15px; font-size: 13px;">🎓 LearnDash Courses (Simplified Structure):</h3>
                        <ul style="line-height: 1.8; font-size: 14px; margin: 10px 0;">
                            <li><strong>6 courses</strong> with titles, descriptions, featured images</li>
                            <li><strong>3-5 lessons per course</strong> with lorem ipsum content</li>
                            <li><strong>1 quiz per lesson</strong> (procedural: Lesson 1 → Quiz 1, Lesson 2 → Quiz 2, etc.)</li>
                            <li><strong>1 final quiz per course</strong> (completes the course)</li>
                            <li><strong>Course enrollments</strong> - random users enrolled to courses</li>
                            <li><strong>Course progress</strong> - 2 completed, 2 in progress, 2 optional</li>
                            <li><strong>Certificates enabled</strong> - with 1-year expiration</li>
                            <li><strong>Note:</strong> Topics/Arguments layer removed for simplicity</li>
                        </ul>

                        <h3 style="color: #333; margin-top: 15px; font-size: 13px;">📊 Analytics:</h3>
                        <ul style="line-height: 1.8; font-size: 14px; margin: 10px 0;">
                            <li><strong>2000-3000 document views</strong> with realistic user distribution</li>
                        </ul>

                        <p style="background: #e8f5e9; padding: 10px; border-radius: 3px; font-size: 13px;">
                            🔄 Auto-cleanup: Running this again deletes old test data and replaces it.
                        </p>

                        <button type="submit" name="meridiana_action" value="generate" class="button button-primary button-large"
                                onclick="return confirm('Generate test data? Previous test data will be deleted.');">
                            Generate Test Data
                        </button>
                    </div>

                    <!-- ERASE ALL SECTION -->
                    <div style="border: 2px solid #d32f2f; padding: 20px; border-radius: 4px; background: #ffebee;">
                        <h2 style="color: #d32f2f;">Erase All Data</h2>
                        <p><strong>⚠ WARNING: This will completely erase the database!</strong></p>
                        <ul style="line-height: 1.8; font-size: 14px;">
                            <li>Deletes ALL users except admin accounts</li>
                            <li>Deletes ALL posts from all CPTs (protocolli, moduli, convenzioni, comunicazioni, ecc)</li>
                            <li>Deletes ALL comments</li>
                            <li>Deletes ALL document view records</li>
                            <li><strong style="color: #2e7d32;">✓ PAGES ARE PROTECTED - Never deleted</strong></li>
                            <li>CANNOT BE UNDONE (without backup)</li>
                        </ul>

                        <p style="background: #ffcccc; padding: 10px; border-radius: 3px; font-size: 13px; color: #c62828;">
                            ✋ Use this to start completely fresh. Admin accounts and Pages are always preserved.
                        </p>

                        <button type="submit" name="meridiana_action" value="erase_all" class="button button-secondary" style="background-color: #d32f2f; color: white; border-color: #b71c1c;"
                                onclick="return confirm('⚠️ THIS WILL ERASE EVERYTHING!\n\nAre you absolutely sure? Type YES to confirm.');">
                            Erase All Data
                        </button>
                    </div>
                </div>
            </form>
            <?php
        }
        ?>
    </div>
    <?php
}

/**
 * Main Test Data Generator Class
 */
class MeridianaTestDataGenerator {

    // Data sources
    private $profili = ['addetto_manutenzione', 'asa_oss', 'assistente_sociale', 'coordinatore', 'educatore', 'fkt', 'impiegato_amministrativo', 'infermiere', 'logopedista', 'medico', 'psicologa', 'receptionista', 'terapista_occupazionale', 'volontari'];

    private $udos = ['ambulatori', 'ap', 'cdi', 'cure_domiciliari', 'hospice', 'paese', 'r20', 'rsa', 'rsa_aperta', 'rsd'];

    private $stati = ['attivo', 'sospeso', 'licenziato'];

    private $aree_competenza = ['diagnostica', 'riabilitazione', 'psicosociale', 'assistenza-base', 'amministrativa', 'organizzativa', 'igiene-sicurezza'];

    private $cognomi = ['Rossi', 'Bianchi', 'Ferrari', 'Russo', 'Romano', 'Colombo', 'Ricci', 'Marino', 'Greco', 'Bruno', 'Gallo', 'Conti', 'De Luca', 'Giordano', 'Barbieri', 'Costa', 'Giunta', 'Ferraro', 'Ferrara', 'Lombardi'];

    private $nomi = ['Marco', 'Luigi', 'Giovanni', 'Antonio', 'Giuseppe', 'Paolo', 'Michele', 'Andrea', 'Francesco', 'Carlo', 'Maria', 'Anna', 'Francesca', 'Laura', 'Giovanna', 'Rosa', 'Angela', 'Teresa', 'Sandra', 'Barbara'];

    private $ruoli_organigramma = ['Coordinatore RSA', 'Responsabile CDI', 'Medico di Base', 'Infermiere Senior', 'Caposala', 'ASA Coordinatore', 'Responsabile Ambulatori', 'Direttore Sanitario', 'Responsabile Amministrativo', 'Educatore Senior', 'Terapista Occupazionale Senior', 'Logopedista', 'Fisioterapista', 'Psiciologa', 'Assistente Sociale Senior', 'Receptionist Capo', 'Manutenzione Responsabile', 'Volontariato Coordinatore', 'Responsabile Qualità', 'Direttore Generale'];

    private $documento_titoli_protocolli = [
        'Protocollo di Gestione dei Pazienti Diabetici',
        'Percorso Clinico Ipertensione',
        'Protocollo di Assistenza Post Operatoria',
        'Gestione degli Accessi Venosi Periferici',
        'Protocollo di Prevenzione Cadute',
        'Protocollo di Terapia del Dolore',
        'Gestione della Documentazione Sanitaria',
        'Protocollo di Sterilizzazione Strumenti',
        'Procedure di Contenimento Meccanico',
        'Protocollo di Gestione della Demenza',
        'Procedura di Assunzione Farmaci',
        'Protocollo di Isolamento Infettivo',
        'Gestione dell\'Incontinenza Urinaria',
        'Protocollo di Nutrizione Artificiale',
        'Procedura di Cateterismi Urinari',
        'Protocollo di Prevenzione Decubito',
        'Gestione dell\'Agitazione Psicomotoria',
        'Protocollo di Controllo Infezioni',
        'Procedura di Medicazioni Avanzate',
        'Protocollo di Triage',
        'Gestione del Paziente Terminale',
        'Protocollo di Malnutrizione',
        'Procedura di Comunicazione Critica',
        'Protocollo di Opiodi',
        'Gestione della Delirium'
    ];

    private $documento_titoli_moduli = [
        'Modulo di Consenso Informato',
        'Modulo di Anamnesi Paziente',
        'Modulo di Valutazione Iniziale',
        'Modulo di Piano di Assistenza',
        'Modulo di Valutazione ADL',
        'Modulo di Registrazione Presenze',
        'Modulo di Richiesta Ferie',
        'Modulo di Incident Report',
        'Modulo di Riconsegna Chiavi',
        'Modulo di Trasferimento Paziente',
        'Modulo di Autorizzazione Terapia',
        'Modulo di Consenso Foto/Video',
        'Modulo di Valutazione Dolore',
        'Modulo di Controllo Pressione',
        'Modulo di Registrazione Temperatura',
        'Modulo di Valutazione Nutrizione',
        'Modulo di Monitoraggio Glicemia',
        'Modulo di Registrazione Assunzione Farmaci',
        'Modulo di Valutazione Mobilità',
        'Modulo di Esclusione Allergie',
        'Modulo di Autorizzazione Trasporto',
        'Modulo di Visita Medica',
        'Modulo di Valutazione Rischi',
        'Modulo di Autorizzazione Genitori',
        'Modulo di Dimissione'
    ];

    private $documento_titoli_convenzioni = [
        'Convenzione con Ospedale Civile di',
        'Convenzione Laboratorio Analisi',
        'Accordo Servizio Trasporto Pazienti',
        'Convenzione Farmacia Comunale',
        'Accordo Servizio Manutenzione Strutture',
        'Convenzione Pulizie Professionali',
        'Accordo Lavandieria Industriale',
        'Convenzione Catering Nutrizionale',
        'Accordo Servizio Informatico',
        'Convenzione Formazione Professionale',
        'Accordo Vigilanza Notturna',
        'Convenzione Consulente Legale',
        'Accordo Audit Qualità',
        'Convenzione Psicologo Consulente',
        'Accordo Personalista Generico',
        'Convenzione Specialisti Medici',
        'Accordo Pronto Intervento Tecnico',
        'Convenzione Agenzia Collocamento',
        'Accordo Fornitura Materiale Sanitario',
        'Convenzione Smaltimento Rifiuti'
    ];

    private $documento_titoli_salute = [
        'La Gestione dello Stress nei Caregivers',
        'Alimentazione Consapevole per la Longevità',
        'Esercizio Fisico Adattato all\'Età',
        'Prevenzione dell\'Osteoporosi',
        'Gestione del Sonno negli Anziani',
        'Benessere Psicologico e Qualità della Vita',
        'Prevenzione delle Cadute Domestiche',
        'Nutrizione Consapevole nella Terza Età',
        'Mindfulness per il Benessere Personale',
        'La Memoria: Allenamento e Prevenzione',
        'Salute Cardiovascolare: Prevenzione Primaria',
        'Attività Motoria Dolce Yoga',
        'Gestione del Peso Corporeo',
        'Salute Dentale negli Anziani',
        'Prevenzione del Diabete di Tipo 2',
        'La Visione: Prevenzione Patologie Oculari',
        'Udito: Screening e Correzione',
        'Igiene Personale e Prevenzione Infezioni',
        'Depressione negli Anziani: Riconoscimento e Aiuto',
        'Longevità Attiva e Consapevole',
        'Resilienza e Ottimismo nella Terza Età',
        'Wellness Psicofisico',
        'Nutrizione Anti-Invecchiamento',
        'Programmi di Riabilitazione Motoria',
        'Prevenzione Cognitiva'
    ];

    private $documento_titoli_comunicazioni = [
        'Comunicato: Variazioni Orari Apertura',
        'Importante: Aggiornamento Tariffe Servizi',
        'Novità: Nuova Procedura Prenotazioni',
        'Comunicazione: Cambio Personale',
        'Avviso: Manutenzione Straordinaria',
        'Notifica: Aggiornamento Software Sistema',
        'Circolare: Cambio Orari Sportello',
        'Comunicato: Chiusura Festiva Estesa',
        'Avviso: Raccolta Ferie Estive',
        'Comunicazione: Nuovo Responsabile Area',
        'Novità: Procedura Autorizzazioni Semplificata',
        'Circolare: Obbligo Corso Sicurezza',
        'Comunicato: Aggiornamento Polizze Assicurative',
        'Avviso: Controllo Qualità Strutture',
        'Notifica: Nuova Piattaforma Digitale',
        'Comunicazione: Audit Interno Programmato',
        'Circolare: Vaccinazioni Consigliate',
        'Comunicato: Riorganizzazione Servizi',
        'Avviso: Scadenza Documenti Personale',
        'Notifica: Aggiornamento GDPR Procedures',
        'Comunicazione: Elezioni Rappresentanti',
        'Circolare: Modifica Regolamento Interno',
        'Comunicato: Partnership Nuovo Provider',
        'Avviso: Controllo Estintori Programmato',
        'Notifica: Disponibilità Nuovi Servizi'
    ];

    // ===== CORSI =====

    private $titoli_corsi = [
        'Introduzione ai Protocolli Aziendali',
        'Sicurezza e Igiene sul Lavoro',
        'GDPR e Privacy nel Contesto Sanitario',
        'Comunicazione Efficace in Equipe',
        'Gestione delle Emergenze',
        'Excel Avanzato per Gestionali',
        'Public Speaking e Presentazioni',
        'Problem Solving Avanzato',
        'Gestione dello Stress e Benessere',
        'Leadership e Gestione del Team'
    ];

    private $titoli_lezioni = [
        'Introduzione ai Concetti Base',
        'Struttura e Organizzazione',
        'Principi Fondamentali',
        'Applicazione Pratica',
        'Casi Studio e Scenari',
        'Best Practices',
        'Strumenti e Risorse',
        'Valutazione e Monitoraggio',
        'Problematiche Comuni',
        'Conclusioni e Prospettive'
    ];

    // Track course states during generation (for enrollment distribution)
    private $course_states = [];  // array of ['course_id' => 'in-progress'|'completed'|'optional']

    private $domande_quiz = [
        [
            'domanda' => 'Quale è lo scopo principale di questo argomento?',
            'risposte' => [
                'A' => ['testo' => 'Migliorare l\'efficienza lavorativa', 'corretta' => true],
                'B' => ['testo' => 'Ridurre i costi operativi', 'corretta' => false],
                'C' => ['testo' => 'Aumentare il numero di dipendenti', 'corretta' => false],
                'D' => ['testo' => 'Nessuna delle precedenti', 'corretta' => false],
            ]
        ],
        [
            'domanda' => 'Quali sono i benefici principali della pratica descritta?',
            'risposte' => [
                'A' => ['testo' => 'Maggiore sicurezza e qualità', 'corretta' => true],
                'B' => ['testo' => 'Riduzione del tempo di lavoro', 'corretta' => false],
                'C' => ['testo' => 'Aumento della complessità', 'corretta' => false],
                'D' => ['testo' => 'Nessun vantaggio', 'corretta' => false],
            ]
        ],
        [
            'domanda' => 'Come si applica correttamente il procedimento?',
            'risposte' => [
                'A' => ['testo' => 'Seguendo rigorosamente i passaggi documentati', 'corretta' => true],
                'B' => ['testo' => 'In modo casuale e improvisato', 'corretta' => false],
                'C' => ['testo' => 'Solo quando conveniente', 'corretta' => false],
                'D' => ['testo' => 'Non è importante come si applica', 'corretta' => false],
            ]
        ],
        [
            'domanda' => 'Quali figure professionali sono coinvolte?',
            'risposte' => [
                'A' => ['testo' => 'Infermieri, Medici e Coordinatori', 'corretta' => true],
                'B' => ['testo' => 'Solo il Direttore', 'corretta' => false],
                'C' => ['testo' => 'Nessuno in particolare', 'corretta' => false],
                'D' => ['testo' => 'Dipende dalla situazione', 'corretta' => false],
            ]
        ],
        [
            'domanda' => 'Quali sono i rischi se non si segue la procedura?',
            'risposte' => [
                'A' => ['testo' => 'Compromissione della sicurezza e qualità', 'corretta' => true],
                'B' => ['testo' => 'Nessun rischio particolare', 'corretta' => false],
                'C' => ['testo' => 'Solo lievi inconvenienti', 'corretta' => false],
                'D' => ['testo' => 'Miglioramento della situazione', 'corretta' => false],
            ]
        ],
    ];

    /**
     * Erase ALL data except admin accounts
     */
    public function erase_all_data() {
        global $wpdb;
        echo "\n========================================\n";
        echo "COMPLETE DATABASE ERASE\n";
        echo "========================================\n\n";

        $start_time = microtime(true);

        // Delete all non-admin users
        echo "== Eliminazione Utenti ==\n";
        $users_deleted = 0;
        $users = get_users(['number' => -1]);
        foreach ($users as $user) {
            // Keep only admin users
            if (!user_can($user->ID, 'manage_options')) {
                wp_delete_user($user->ID);
                $users_deleted++;
            }
        }
        echo "  ✓ Eliminati $users_deleted utenti non-admin\n\n";

        // Delete all posts and CPT (EXCLUDING PAGES - they are protected template pages!)
        echo "== Eliminazione Post/CPT ==\n";
        $posts_deleted = 0;
        // NOTE: 'page' is deliberately EXCLUDED - pages are permanent structural templates!
        $post_types = ['post', 'protocollo', 'modulo', 'convenzione', 'salute-e-benessere-l', 'organigramma', 'sfwd-courses', 'sfwd-lessons', 'sfwd-quiz', 'sfwd-question'];
        foreach ($post_types as $cpt) {
            $posts = get_posts(['post_type' => $cpt, 'numberposts' => -1, 'post_status' => 'any']);
            foreach ($posts as $post) {
                wp_delete_post($post->ID, true);
                $posts_deleted++;
            }
        }
        echo "  ✓ Eliminati $posts_deleted post/CPT\n";
        echo "  ℹ  PAGINE PROTETTE: Le pagine non sono state eliminate!\n\n";

        // Delete all comments
        echo "== Eliminazione Commenti ==\n";
        $comments_deleted = $wpdb->query("DELETE FROM {$wpdb->prefix}comments WHERE comment_type != 'webhook'");
        $wpdb->query("DELETE FROM {$wpdb->prefix}commentmeta WHERE comment_id NOT IN (SELECT comment_ID FROM {$wpdb->prefix}comments)");
        echo "  ✓ Eliminati $comments_deleted commenti\n\n";

        // Delete document views
        echo "== Eliminazione Visualizzazioni Documenti ==\n";
        $views_deleted = $wpdb->query("DELETE FROM {$wpdb->prefix}document_views");
        echo "  ✓ Eliminate $views_deleted visualizzazioni\n\n";

        $elapsed = round(microtime(true) - $start_time, 2);

        echo "========================================\n";
        echo "✓ DATABASE COMPLETAMENTE CANCELLATO IN {$elapsed}s\n";
        echo "========================================\n\n";

        return [
            'success' => true,
            'elapsed' => $elapsed,
            'users_deleted' => $users_deleted,
            'posts_deleted' => $posts_deleted,
            'comments_deleted' => $comments_deleted,
            'views_deleted' => $views_deleted,
        ];
    }

    /**
     * Clean up old test data before generating new data
     * IMPORTANT: Only deletes test data (test_user_* users and posts with "Test" in title)
     * NEVER touches pages or real content!
     */
    private function cleanup_old_data() {
        global $wpdb;
        echo "== Pulizia Dati di Test Precedenti ==\n";

        $cleaned_users = 0;
        $deleted_posts = 0;
        $deleted_views = 0;

        // Delete ONLY test users (test_user_*)
        $users = get_users(['search' => 'test_user_', 'number' => -1]);
        foreach ($users as $user) {
            wp_delete_user($user->ID);
            $cleaned_users++;
        }
        if ($cleaned_users > 0) {
            echo "  ✓ Eliminati $cleaned_users utenti test (test_user_*)\n";
        } else {
            echo "  ℹ  Nessun utente test da eliminare\n";
        }

        // Delete ONLY test posts (those with "Test" in title, from test CPT only)
        // NOTE: 'page' is NEVER touched here!
        // NOTE: LearnDash courses are COMPLETELY REGENERATED (all deleted, not just "Test" ones)
        $test_cpts = ['protocollo', 'modulo', 'convenzione', 'salute-e-benessere-l', 'post', 'organigramma'];

        foreach ($test_cpts as $cpt) {
            $posts = get_posts(['post_type' => $cpt, 'numberposts' => -1, 'post_status' => 'any']);
            foreach ($posts as $post) {
                // Only delete if title contains "Test" (our test data marker)
                if (strpos($post->post_title, 'Test') !== false) {
                    wp_delete_post($post->ID, true);
                    $deleted_posts++;
                }
            }
        }

        // Delete ALL LearnDash courses, lessons, topics, questions BUT PRESERVE manually created quizzes
        echo "== Eliminazione Corsi LearnDash Precedenti ==\n";

        // Delete courses, lessons, topics, and questions (simplified structure)
        $learndash_cpts_to_delete = ['sfwd-courses', 'sfwd-lessons', 'sfwd-topic', 'sfwd-question'];
        foreach ($learndash_cpts_to_delete as $cpt) {
            $posts = get_posts(['post_type' => $cpt, 'numberposts' => -1, 'post_status' => 'any']);
            foreach ($posts as $post) {
                wp_delete_post($post->ID, true);
                $deleted_posts++;
            }
        }
        echo "  ✓ Eliminati tutti i corsi/lezioni/argomenti/domande LearnDash precedenti\n";

        // Delete ONLY auto-generated quizzes (preserve manually created ones)
        echo "== Eliminazione Quiz Generati Automaticamente ==\n";
        $quizzes = get_posts(['post_type' => 'sfwd-quiz', 'numberposts' => -1, 'post_status' => 'any']);
        $quizzes_deleted = 0;
        foreach ($quizzes as $quiz) {
            // Only delete if it has the auto-generated marker
            $is_auto_generated = get_post_meta($quiz->ID, '_generated_by_test_data', true);
            if ($is_auto_generated) {
                wp_delete_post($quiz->ID, true);
                $quizzes_deleted++;
                $deleted_posts++;
            }
        }
        echo "  ✓ Eliminati $quizzes_deleted quiz generati automaticamente\n";
        echo "  ℹ  Quiz creati manualmente: PRESERVATI ✓\n";
        if ($deleted_posts > 0) {
            echo "  ✓ Eliminati $deleted_posts post di test (con 'Test' nel titolo)\n";
        } else {
            echo "  ℹ  Nessun post di test da eliminare\n";
        }

        // Delete orphaned course enrollments (for courses that were deleted)
        echo "== Pulizia Iscrizioni Corsi ==\n";
        $deleted_enrollments = 0;

        // Get all test courses that still exist (to preserve non-test courses)
        $test_courses = get_posts([
            'post_type' => 'sfwd-courses',
            'numberposts' => -1,
            'post_status' => 'any',
            's' => 'Test'  // Only get courses with "Test" in title
        ]);
        $test_course_ids = wp_list_pluck($test_courses, 'ID');

        // Delete enrollments for deleted courses (courses NOT in the list)
        if (!empty($test_course_ids)) {
            $placeholders = implode(',', array_fill(0, count($test_course_ids), '%d'));
            $deleted_enrollments = $wpdb->query($wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}learndash_user_course_access
                 WHERE course_id NOT IN ($placeholders)
                 AND course_id IN (
                     SELECT ID FROM {$wpdb->prefix}posts
                     WHERE post_type = 'sfwd-courses' AND post_title LIKE '%Test%'
                 )",
                ...$test_course_ids
            ));
        }

        // Actually, simpler approach: delete enrollments for courses that don't exist
        $deleted_enrollments = $wpdb->query(
            "DELETE FROM {$wpdb->prefix}learndash_user_course_access
             WHERE course_id NOT IN (
                 SELECT ID FROM {$wpdb->prefix}posts
                 WHERE post_type = 'sfwd-courses'
             )"
        );

        if ($deleted_enrollments > 0) {
            echo "  ✓ Eliminate $deleted_enrollments iscrizioni orfane (corsi non esistenti)\n";
        } else {
            echo "  ℹ  Nessuna iscrizione orfana\n";
        }

        // DO NOT DELETE document views here - they are preserved across regenerations
        // Views are only deleted when user explicitly clicks "Erase All Data"
        echo "  ℹ  Visualizzazioni: PRESERVATE (non eliminate)\n";

        echo "✓ Pulizia selettiva completata (solo dati test, visualizzazioni preservate)\n\n";
    }

    /**
     * Genera 100 utenti di test
     */
    public function generate_users() {
        echo "== Generazione 100 Utenti ==\n";

        $created = 0;
        $skipped = 0;

        for ($i = 1; $i <= 100; $i++) {
            $username = sprintf('test_user_%03d', $i);

            if (username_exists($username)) {
                $skipped++;
                continue;
            }

            $profilo = $this->profili[array_rand($this->profili)];
            $udo = $this->udos[array_rand($this->udos)];

            // Distribution: 80% active, 15% suspended, 5% fired
            $rand = rand(1, 100);
            $stato = ($rand <= 80) ? 'attivo' : (($rand <= 95) ? 'sospeso' : 'licenziato');

            $user_data = [
                'user_login'    => $username,
                'user_email'    => sprintf('test.user.%03d@meridiana.test', $i),
                'user_pass'     => 'TestPass2025!',
                'first_name'    => $this->nomi[array_rand($this->nomi)],
                'last_name'     => $this->cognomi[array_rand($this->cognomi)],
                'role'          => 'subscriber',
            ];

            $user_id = wp_insert_user($user_data);

            if (is_wp_error($user_id)) {
                echo "  ✗ Errore: {$username}\n";
                continue;
            }

            // Set ACF fields
            update_field('profilo_professionale', $profilo, "user_$user_id");
            update_field('udo_riferimento', $udo, "user_$user_id");
            update_field('stato_utente', $stato, "user_$user_id");
            update_field('codice_fiscale', $this->generate_codice_fiscale(), "user_$user_id");

            if (rand(1, 100) <= 20) {
                update_field('link_autologin_esterno', 'https://example.com/autologin/' . $user_id, "user_$user_id");
            }

            $created++;

            if ($created % 10 === 0) {
                echo "  ✓ {$created} utenti creati\n";
            }
        }

        echo "✓ Utenti: $created creati, $skipped saltati\n\n";
        return $created;
    }

    /**
     * Genera 25 protocolli con relazioni e tassonomie
     */
    public function generate_protocolli() {
        echo "== Generazione 25 Protocolli ==\n";

        $created = 0;
        $pdf_id = $this->create_placeholder_pdf('Protocollo');
        $moduli = get_posts(['post_type' => 'modulo', 'numberposts' => -1]);

        if (!$pdf_id) {
            echo "⚠ Warning: PDF creation failed, continuing without PDFs\n";
        }

        for ($i = 1; $i <= 25; $i++) {
            $title = $this->documento_titoli_protocolli[array_rand($this->documento_titoli_protocolli)] . ' - Test ' . $i;

            // Protocolli: empty content (only riassunto matters)
            $post_id = wp_insert_post([
                'post_title'   => $title,
                'post_content' => '',
                'post_type'    => 'protocollo',
                'post_status'  => 'publish',
                'post_author'  => 1,
            ]);

            if (is_wp_error($post_id)) {
                echo "  ✗ Errore creazione protocollo: " . $post_id->get_error_message() . "\n";
                continue;
            }

            // Set ACF fields
            if ($pdf_id) {
                update_field('pdf_protocollo', $pdf_id, $post_id);
            }
            update_field('riassunto', $this->generate_lorem(200), $post_id);
            update_field('pianificazione_ats', (rand(1, 100) <= 10) ? 1 : 0, $post_id);

            // Relationship: moduli allegati (2-4 random moduli)
            if (!empty($moduli)) {
                $random_moduli = array_slice($moduli, 0, rand(2, min(4, count($moduli))));
                $moduli_ids = wp_list_pluck($random_moduli, 'ID');
                update_field('moduli_allegati', $moduli_ids, $post_id);
            }

            // Assign taxonomies
            $this->assign_random_profili_taxonomy($post_id);
            $this->assign_random_unita_offerta_taxonomy($post_id);

            $created++;

            if ($created % 5 === 0) {
                echo "  ✓ {$created} protocolli creati\n";
            }
        }

        echo "✓ Protocolli: $created creati\n\n";
        return $created;
    }

    /**
     * Genera 25 moduli
     */
    public function generate_moduli() {
        echo "== Generazione 25 Moduli ==\n";

        $created = 0;
        $pdf_id = $this->create_placeholder_pdf('Modulo');

        if (!$pdf_id) {
            echo "⚠ Warning: PDF creation failed, continuing without PDFs\n";
        }

        for ($i = 1; $i <= 25; $i++) {
            $title = $this->documento_titoli_moduli[array_rand($this->documento_titoli_moduli)] . ' - Test ' . $i;

            // Moduli: empty content (only PDF matters)
            $post_id = wp_insert_post([
                'post_title'   => $title,
                'post_content' => '',
                'post_type'    => 'modulo',
                'post_status'  => 'publish',
                'post_author'  => 1,
            ]);

            if (is_wp_error($post_id)) {
                echo "  ✗ Errore creazione modulo: " . $post_id->get_error_message() . "\n";
                continue;
            }

            // Set ACF fields
            if ($pdf_id) {
                update_field('pdf_modulo', $pdf_id, $post_id);
            }

            // Assign taxonomies
            $this->assign_random_profili_taxonomy($post_id);
            $this->assign_random_area_competenza_taxonomy($post_id);

            $created++;

            if ($created % 5 === 0) {
                echo "  ✓ {$created} moduli creati\n";
            }
        }

        echo "✓ Moduli: $created creati\n\n";
        return $created;
    }

    /**
     * Genera 25 convenzioni con repeater e immagini
     */
    public function generate_convenzioni() {
        echo "== Generazione 25 Convenzioni ==\n";

        $created = 0;

        for ($i = 1; $i <= 25; $i++) {
            $title = $this->documento_titoli_convenzioni[array_rand($this->documento_titoli_convenzioni)] . ' ' . $this->cognomi[array_rand($this->cognomi)] . ' - Test ' . $i;

            $post_id = wp_insert_post([
                'post_title'   => $title,
                'post_content' => '',
                'post_type'    => 'convenzione',
                'post_status'  => 'publish',
                'post_author'  => 1,
            ]);

            if (is_wp_error($post_id)) {
                continue;
            }

            // Set featured image
            $image_id = $this->create_placeholder_image('Convenzione');
            if ($image_id) {
                set_post_thumbnail($post_id, $image_id);
                update_field('immagine_evidenza', $image_id, $post_id);
            }

            // Set ACF fields
            update_field('convenzione_attiva', (rand(1, 100) <= 80) ? 1 : 0, $post_id);
            update_field('descrizione', '<p>' . $this->generate_lorem(400) . '</p>', $post_id);
            update_field('contatti', '<p>Tel: +39 0' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT) . ' ' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT) . '<br/>Email: info@' . strtolower($title) . '.it</p>', $post_id);

            // Repeater: allegati (2-3 files)
            $allegati = [];
            for ($j = 0; $j < rand(1, 3); $j++) {
                $file_id = $this->create_placeholder_pdf('Allegato');
                if ($file_id) {
                    $allegati[] = [
                        'file' => $file_id,
                        'descrizione' => 'Allegato documento ' . ($j + 1)
                    ];
                }
            }
            if (!empty($allegati)) {
                update_field('allegati', $allegati, $post_id);
            }

            // Assign all profili
            $this->assign_all_profili_taxonomy($post_id);

            $created++;

            if ($created % 5 === 0) {
                echo "  ✓ {$created} convenzioni create\n";
            }
        }

        echo "✓ Convenzioni: $created create\n\n";
        return $created;
    }

    /**
     * Genera 25 articoli Salute & Benessere con repeater risorse
     */
    public function generate_salute_benessere() {
        echo "== Generazione 25 Salute & Benessere ==\n";

        $created = 0;

        for ($i = 1; $i <= 25; $i++) {
            $title = $this->documento_titoli_salute[array_rand($this->documento_titoli_salute)] . ' - Test ' . $i;

            $post_id = wp_insert_post([
                'post_title'   => $title,
                'post_content' => '',
                'post_type'    => 'salute-e-benessere-l',
                'post_status'  => 'publish',
                'post_author'  => 1,
            ]);

            if (is_wp_error($post_id)) {
                continue;
            }

            // Set ACF fields
            update_field('contenuto', '<p>' . $this->generate_lorem(500) . '</p>', $post_id);

            // Repeater: risorse (2-3 links/files)
            $risorse = [];
            for ($j = 0; $j < rand(2, 3); $j++) {
                $tipo = rand(0, 1) ? 'link' : 'file';
                $risorsa = [
                    'tipo' => $tipo,
                    'titolo' => 'Risorsa ' . ($j + 1)
                ];

                if ($tipo === 'link') {
                    $risorsa['url'] = 'https://example.com/risorsa-' . ($j + 1);
                } else {
                    $file_id = $this->create_placeholder_pdf('Risorsa');
                    if ($file_id) {
                        $risorsa['file'] = $file_id;
                    }
                }
                $risorse[] = $risorsa;
            }
            if (!empty($risorse)) {
                update_field('risorse', $risorse, $post_id);
            }

            // Assign all profili
            $this->assign_all_profili_taxonomy($post_id);

            $created++;

            if ($created % 5 === 0) {
                echo "  ✓ {$created} articoli creati\n";
            }
        }

        echo "✓ Salute & Benessere: $created articoli creati\n\n";
        return $created;
    }

    /**
     * Genera 25 comunicazioni (post)
     */
    public function generate_comunicazioni() {
        echo "== Generazione 25 Comunicazioni ==\n";

        $created = 0;

        for ($i = 1; $i <= 25; $i++) {
            $title = $this->documento_titoli_comunicazioni[array_rand($this->documento_titoli_comunicazioni)] . ' - Test ' . $i;

            $post_id = wp_insert_post([
                'post_title'   => $title,
                'post_content' => $this->generate_lorem(300),
                'post_type'    => 'post',
                'post_status'  => 'publish',
                'post_author'  => 1,
            ]);

            if (is_wp_error($post_id)) {
                continue;
            }

            $created++;

            if ($created % 5 === 0) {
                echo "  ✓ {$created} comunicazioni create\n";
            }
        }

        echo "✓ Comunicazioni: $created create\n\n";
        return $created;
    }

    /**
     * Genera 20 entry Organigramma
     */
    public function generate_organigramma() {
        echo "== Generazione 20 Organigrammi ==\n";

        $created = 0;

        for ($i = 1; $i <= 20; $i++) {
            $nome = $this->nomi[array_rand($this->nomi)];
            $cognome = $this->cognomi[array_rand($this->cognomi)];
            $title = "$nome $cognome - Test Org $i";

            $post_id = wp_insert_post([
                'post_title'   => $title,
                'post_content' => '',
                'post_type'    => 'organigramma',
                'post_status'  => 'publish',
                'post_author'  => 1,
            ]);

            if (is_wp_error($post_id)) {
                continue;
            }

            // Set ACF fields
            $ruolo = $this->ruoli_organigramma[array_rand($this->ruoli_organigramma)];
            $udo = $this->udos[array_rand($this->udos)];

            update_field('ruolo', $ruolo, $post_id);
            update_field('udo_riferimento', $udo, $post_id);
            update_field('email_aziendale', strtolower($nome) . '.' . strtolower($cognome) . '@meridiana.coop', $post_id);
            update_field('telefono_aziendale', '+39 0' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT) . ' ' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT), $post_id);

            // Assign unita-offerta taxonomy
            $this->assign_random_unita_offerta_taxonomy($post_id);

            $created++;

            if ($created % 5 === 0) {
                echo "  ✓ {$created} organigrammi creati\n";
            }
        }

        echo "✓ Organigramma: $created entry create\n\n";
        return $created;
    }

    /**
     * Genera 8-10 corsi LearnDash con distribuzione di stati
     * ~30% In Corso, ~20% Completati, ~50% Facoltativi
     */
    public function generate_courses() {
        echo "== Generazione 6 Corsi LearnDash ==\n";

        $created = 0;
        $num_courses = 6;  // Esattamente 6 corsi per test

        // Reset course states for this generation
        $this->course_states = [];

        // Distribute courses evenly for testing
        $num_in_progress = 2;    // 2 corsi in corso
        $num_completed = 2;      // 2 corsi completati
        $num_optional = 2;       // 2 corsi facoltativi (da cui iscriversi)

        $state_distribution = array_merge(
            array_fill(0, $num_in_progress, 'in-progress'),
            array_fill(0, $num_completed, 'completed'),
            array_fill(0, $num_optional, 'optional')
        );
        shuffle($state_distribution);

        for ($i = 1; $i <= $num_courses; $i++) {
            $title = $this->titoli_corsi[array_rand($this->titoli_corsi)] . ' - Test ' . $i;
            $course_state = $state_distribution[$i - 1]; // Get state for this course

            $course_id = wp_insert_post([
                'post_title'   => $title,
                'post_content' => '<p>' . $this->generate_lorem(400) . '</p>',
                'post_type'    => 'sfwd-courses',
                'post_status'  => 'publish',
                'post_author'  => 1,
            ]);

            if (is_wp_error($course_id)) {
                echo "  ✗ Errore creazione corso\n";
                continue;
            }

            // Store state for later use in enrollment
            $this->course_states[$course_id] = $course_state;

            // LearnDash course meta
            update_post_meta($course_id, 'course_prerequisite', ''); // No prerequisite
            update_post_meta($course_id, 'course_lesson_count', 0); // Will be updated
            update_post_meta($course_id, 'course_lesson_order', 'menu_order'); // Lesson order

            // IMPORTANT: Save course state to database (for API and regenerations)
            update_post_meta($course_id, '_course_test_state', $course_state); // 'in-progress', 'completed', 'optional'

            // Certificate settings - ONLY for completed/in-progress courses (not optional)
            if ($course_state !== 'optional') {
                update_post_meta($course_id, 'course_certificate', 1); // Enable certificate
                update_post_meta($course_id, 'course_certificate_expiration_enabled', 1); // Certificate expiration
                update_post_meta($course_id, 'course_certificate_expiration', 365); // 1 year expiration
            } else {
                update_post_meta($course_id, 'course_certificate', 0); // No certificate for optional
            }

            // Course settings
            update_post_meta($course_id, 'course_lesson_progression', 1); // Sequential
            update_post_meta($course_id, 'course_access_list', ''); // Everyone can access

            // Set featured image
            $image_id = $this->create_placeholder_image('Corso');
            if ($image_id) {
                set_post_thumbnail($course_id, $image_id);
            }

            $created++;
        }

        echo "  ✓ Distribuzione corsi:\n";
        echo "    - In Corso: $num_in_progress\n";
        echo "    - Completati: $num_completed\n";
        echo "    - Facoltativi: $num_optional\n";
        echo "✓ Corsi: $created creati\n\n";
        return $created;
    }

    /**
     * Genera lezioni per ogni corso
     */
    public function generate_lessons() {
        echo "== Generazione Lezioni ==\n";

        $created = 0;
        $courses = get_posts(['post_type' => 'sfwd-courses', 'numberposts' => -1]);

        foreach ($courses as $course) {
            $num_lessons = rand(3, 5);

            for ($i = 1; $i <= $num_lessons; $i++) {
                $lesson_title = $this->titoli_lezioni[array_rand($this->titoli_lezioni)] . ' - Lezione ' . $i;

                $lesson_id = wp_insert_post([
                    'post_title'   => $lesson_title,
                    'post_content' => '<p>' . $this->generate_lorem(600) . '</p>',
                    'post_type'    => 'sfwd-lessons',
                    'post_status'  => 'publish',
                    'post_parent'  => $course->ID, // Link to course
                    'post_author'  => 1,
                    'menu_order'   => $i, // Lesson order
                ]);

                if (!is_wp_error($lesson_id)) {
                    // LearnDash lesson meta
                    update_post_meta($lesson_id, 'course_id', $course->ID);
                    update_post_meta($lesson_id, 'lesson_progression', 'on');
                    update_post_meta($lesson_id, 'lesson_completion_default_on_view', 'on'); // Auto-mark complete

                    $created++;
                }
            }
        }

        echo "✓ Lezioni: $created create\n\n";
        return $created;
    }

    /**
     * Genera topics (argomenti) per ogni lezione
     */
    public function generate_topics() {
        echo "== Generazione Argomenti (Topics) ==\n";

        $created = 0;
        $lessons = get_posts(['post_type' => 'sfwd-lessons', 'numberposts' => -1]);

        foreach ($lessons as $lesson) {
            $lesson_id = $lesson->ID;
            $course_id = get_post_meta($lesson_id, 'course_id', true);

            if (!$course_id) {
                continue;
            }

            $num_topics = rand(2, 4); // 2-4 topics per lesson

            for ($i = 1; $i <= $num_topics; $i++) {
                $topic_title = 'Argomento ' . $i . ' - ' . $lesson->post_title;

                $topic_id = wp_insert_post([
                    'post_title'   => $topic_title,
                    'post_content' => '<p>' . $this->generate_lorem(400) . '</p>',
                    'post_type'    => 'sfwd-topic',
                    'post_status'  => 'publish',
                    'post_parent'  => $lesson->ID, // Link to lesson
                    'post_author'  => 1,
                    'menu_order'   => $i, // Topic order
                ]);

                if (!is_wp_error($topic_id)) {
                    // LearnDash topic meta
                    update_post_meta($topic_id, 'course_id', $course_id);
                    update_post_meta($topic_id, 'lesson_id', $lesson_id);

                    $created++;
                }
            }
        }

        echo "✓ Argomenti (Topics): $created creati\n\n";
        return $created;
    }

    /**
     * DEPRECATED: Old quiz generation (topics-based)
     * Kept for backwards compatibility only
     * Use generate_lesson_quizzes() instead
     */
    public function generate_quizzes() {
        echo "== Generazione Quiz ==\n";

        $created = 0;
        $topics = get_posts(['post_type' => 'sfwd-topic', 'numberposts' => -1]);

        if (empty($topics)) {
            echo "⚠ Nessun argomento (topic) trovato\n\n";
            return 0;
        }

        foreach ($topics as $topic) {
            $topic_id = $topic->ID;

            // Get lesson and course from topic meta
            $lesson_id = get_post_meta($topic_id, 'lesson_id', true);
            $course_id = get_post_meta($topic_id, 'course_id', true);

            if (!$lesson_id || !$course_id) {
                continue;
            }

            // 50% of topics have 1 quiz, 50% have 0 quizzes
            $num_quizzes = rand(0, 1);

            for ($i = 1; $i <= $num_quizzes; $i++) {
                $quiz_title = 'Quiz Argomento - ' . $topic->post_title;

                $quiz_id = wp_insert_post([
                    'post_title'   => $quiz_title,
                    'post_content' => '<p>Quiz per verificare la comprensione di questo argomento.</p>',
                    'post_type'    => 'sfwd-quiz',
                    'post_status'  => 'publish',
                    'post_parent'  => $topic->ID, // Link to TOPIC (not course!)
                    'post_author'  => 1,
                    'menu_order'   => $i,
                ]);

                if (!is_wp_error($quiz_id)) {
                    // LearnDash quiz meta - Link to topic, lesson, and course
                    update_post_meta($quiz_id, 'course_id', $course_id);
                    update_post_meta($quiz_id, 'lesson_id', $lesson_id);
                    update_post_meta($quiz_id, 'topic_id', $topic_id);  // NEW: Link to topic

                    update_post_meta($quiz_id, 'quiz_pro_enabled', 'on');
                    update_post_meta($quiz_id, 'quiz_passing_percentage', '70'); // 70% to pass
                    update_post_meta($quiz_id, 'quiz_question_count', 0); // Will be updated
                    update_post_meta($quiz_id, 'quiz_randomize_questions', 'off');
                    update_post_meta($quiz_id, 'quiz_show_score', 'on');
                    update_post_meta($quiz_id, 'quiz_show_answers', 'on');

                    // IMPORTANT: Mark this quiz as auto-generated (for cleanup purposes)
                    // When cleanup runs, it will preserve manually created quizzes
                    update_post_meta($quiz_id, '_generated_by_test_data', 1);

                    $created++;
                }
            }
        }

        echo "✓ Quiz: $created creati (legati ai topics)\n\n";
        return $created;
    }

    /**
     * Genera domande per ogni quiz
     *
     * NOTA: Temporaneamente DISABILITATO per testing della UI
     * Le domande devono essere create via LearnDash UI (nativo) per compatibilità massima
     *
     * Per riabilitare: decommentare il codice sotto
     */
    public function generate_questions() {
        echo "== Generazione Domande Quiz ==\n";
        echo "⚠️  TEMPORANEAMENTE DISABILITATA per testing\n";
        echo "    I quiz sono stati creati vuoti per permetterti di testare la UI\n";
        echo "    Crea le domande manualmente via LearnDash Admin\n\n";

        // Placeholder return
        return 0;

        /*
        // ============================================
        // CODICE DISABILITATO - DECOMMENTARE PER RIABILITARE
        // ============================================

        $created = 0;
        // Get all quizzes (now linked to topics via generate_quizzes)
        $quizzes = get_posts(['post_type' => 'sfwd-quiz', 'numberposts' => -1]);

        if (empty($quizzes)) {
            echo "⚠ Nessun quiz trovato\n\n";
            return 0;
        }

        foreach ($quizzes as $quiz) {
            $num_questions = rand(3, 5);

            for ($i = 0; $i < $num_questions; $i++) {
                // Random question from pool
                $question_data = $this->domande_quiz[array_rand($this->domande_quiz)];
                $question_text = $question_data['domanda'] . ' (' . ($i + 1) . ')';

                $question_id = wp_insert_post([
                    'post_title'   => $question_text,
                    'post_content' => $question_text,
                    'post_type'    => 'sfwd-question',
                    'post_status'  => 'publish',
                    'post_parent'  => $quiz->ID, // Link to quiz (which is linked to topic)
                    'post_author'  => 1,
                    'menu_order'   => $i + 1,
                ]);

                if (!is_wp_error($question_id)) {
                    // LearnDash question meta - Question Type: Multiple Choice
                    update_post_meta($question_id, 'question_type', 'single');
                    update_post_meta($question_id, 'question_points', 10);
                    update_post_meta($question_id, 'question_show_explanation', 'on');

                    // Build answers array
                    $answers = [];
                    foreach ($question_data['risposte'] as $letter => $risposta) {
                        $answers[] = [
                            'text' => $risposta['testo'],
                            'correct' => $risposta['corretta'] ? 'on' : '',
                            'sort' => count($answers) + 1,
                        ];
                    }

                    // Save answers as post meta
                    update_post_meta($question_id, 'question_answer_type', 'single'); // Single choice
                    update_post_meta($question_id, 'question_answers', $answers);
                    update_post_meta($question_id, 'question_explanation', $this->generate_lorem(200));

                    $created++;
                }
            }
        }

        echo "✓ Domande: $created create (associate ai quiz per i topics)\n\n";
        return $created;
        */
    }

    /**
     * Iscrivere utenti ai corsi (ONLY to in-progress and completed, NOT optional)
     */
    public function generate_course_enrollments() {
        global $wpdb;
        echo "== Iscrizione Utenti ai Corsi ==\n";

        $enrolled = 0;
        $users = get_users(['number' => -1, 'role' => 'subscriber']);
        $courses = get_posts(['post_type' => 'sfwd-courses', 'numberposts' => -1]);

        if (empty($users) || empty($courses)) {
            echo "⚠ Mancano utenti o corsi\n\n";
            return 0;
        }

        // Filter courses: only enroll to non-optional courses (read state from database)
        $enrollable_courses = array_filter($courses, function($course) {
            $course_state = get_post_meta($course->ID, '_course_test_state', true);
            return !empty($course_state) && $course_state !== 'optional';
        });

        if (empty($enrollable_courses)) {
            echo "⚠ Nessun corso non-facoltativo per le iscrizioni\n\n";
            return 0;
        }

        // Get first user (admin or test user)
        $first_user = isset($users[0]) ? $users[0] : null;

        // Enroll users to courses based on state
        foreach ($enrollable_courses as $course) {
            $course_state = get_post_meta($course->ID, '_course_test_state', true) ?: 'optional';

            // Always enroll first user to test courses (especially completed ones)
            if ($first_user) {
                // Mark first user as enrolled via user meta (for API to read)
                update_user_meta($first_user->ID, '_enrolled_course_' . $course->ID, current_time('timestamp'));
                $enrolled++;
            }

            // Enroll additional random users for completed courses
            if ($course_state === 'completed') {
                $num_enrollments = rand(2, 3);
            } else {
                $num_enrollments = rand(1, 2);
            }

            $shuffled_users = $users;
            shuffle($shuffled_users);
            $users_to_enroll = array_slice($shuffled_users, 0, min($num_enrollments, count($shuffled_users)));

            foreach ($users_to_enroll as $user) {
                // Mark user as enrolled via user meta (for API to read)
                update_user_meta($user->ID, '_enrolled_course_' . $course->ID, current_time('timestamp'));
                $enrolled++;
            }
        }

        echo "✓ Iscrizioni: $enrolled utenti iscritti ai corsi non-facoltativi\n\n";
        return $enrolled;
    }

    /**
     * Simula progresso utenti e completamento corsi basato su course_states
     * in-progress: 30-60% lezioni complete
     * completed: 100% lezioni complete
     */
    public function generate_course_progress() {
        global $wpdb;
        echo "== Simulazione Progresso Corsi ==\n";

        $completed = 0;
        $in_progress = 0;

        // Get all user courses from database
        $user_courses = $wpdb->get_results(
            "SELECT DISTINCT user_id, course_id FROM {$wpdb->prefix}learndash_user_course_access"
        );

        foreach ($user_courses as $uc) {
            $user_id = $uc->user_id;
            $course_id = $uc->course_id;

            // Get course state from database (persistent)
            $course_state = get_post_meta($course_id, '_course_test_state', true) ?: 'optional';

            // Get lessons for this course
            $lessons = get_posts([
                'post_type' => 'sfwd-lessons',
                'numberposts' => -1,
                'meta_key' => 'course_id',
                'meta_value' => $course_id,
                'fields' => 'ids',
            ]);

            if (empty($lessons)) {
                continue;
            }

            // Mark lessons based on course state
            if ($course_state === 'completed') {
                // Mark ALL lessons as complete
                $completed_lessons = $lessons;
                update_user_meta($user_id, '_learndash_course_' . $course_id . '_lessons_completed', $completed_lessons);
                $completed++;
            } elseif ($course_state === 'in-progress') {
                // Mark 30-60% of lessons as complete
                $num_to_complete = rand(ceil(count($lessons) * 0.30), ceil(count($lessons) * 0.60));
                $completed_lessons = array_slice($lessons, 0, min($num_to_complete, count($lessons)));
                update_user_meta($user_id, '_learndash_course_' . $course_id . '_lessons_completed', $completed_lessons);
                $in_progress++;
            }
            // 'optional' courses have no enrolled users, so no progress to track
        }

        echo "✓ Corsi completati: $completed\n";
        echo "✓ Corsi in progress: $in_progress\n\n";
        return ['in_progress' => $in_progress, 'completed' => $completed];
    }

    /**
     * NEW: Genera quiz per ogni lezione (procedural system)
     * Struttura semplificata: Corso > Lezione > Quiz
     */
    public function generate_lesson_quizzes() {
        echo "== Generazione Quiz per Lezioni (NEW PROCEDURAL) ==\n";

        $created = 0;
        $lessons = get_posts(['post_type' => 'sfwd-lessons', 'numberposts' => -1]);

        if (empty($lessons)) {
            echo "⚠ Nessuna lezione trovata\n\n";
            return 0;
        }

        foreach ($lessons as $lesson) {
            $lesson_id = $lesson->ID;
            $course_id = get_post_meta($lesson_id, 'course_id', true);

            if (!$course_id) {
                continue;
            }

            // Create 1 quiz per lesson
            $quiz_title = 'Quiz - ' . $lesson->post_title;

            $quiz_id = wp_insert_post([
                'post_title'   => $quiz_title,
                'post_content' => '<p>Quiz per verificare la comprensione della lezione.</p>',
                'post_type'    => 'sfwd-quiz',
                'post_status'  => 'publish',
                'post_parent'  => $lesson_id,  // Directly linked to lesson
                'post_author'  => 1,
                'menu_order'   => 1,
            ]);

            if (!is_wp_error($quiz_id)) {
                // LearnDash quiz meta
                update_post_meta($quiz_id, 'course_id', $course_id);
                update_post_meta($quiz_id, 'lesson_id', $lesson_id);

                update_post_meta($quiz_id, 'quiz_pro_enabled', 'on');
                update_post_meta($quiz_id, 'quiz_passing_percentage', '70');
                update_post_meta($quiz_id, 'quiz_question_count', 0);
                update_post_meta($quiz_id, 'quiz_randomize_questions', 'off');
                update_post_meta($quiz_id, 'quiz_show_score', 'on');
                update_post_meta($quiz_id, 'quiz_show_answers', 'on');

                // Mark as auto-generated
                update_post_meta($quiz_id, '_generated_by_test_data', 1);

                $created++;
            }
        }

        echo "✓ Quiz per lezioni: $created creati\n\n";
        return $created;
    }

    /**
     * NEW: Genera domande per ogni quiz (simple procedural)
     */
    public function generate_quiz_questions() {
        echo "== Generazione Domande per Quiz ==\n";

        $created = 0;
        $quizzes = get_posts(['post_type' => 'sfwd-quiz', 'numberposts' => -1]);

        if (empty($quizzes)) {
            echo "⚠ Nessun quiz trovato\n\n";
            return 0;
        }

        foreach ($quizzes as $quiz) {
            $num_questions = rand(3, 5);

            for ($i = 0; $i < $num_questions; $i++) {
                $question_data = $this->domande_quiz[array_rand($this->domande_quiz)];
                $question_text = $question_data['domanda'] . ' (' . ($i + 1) . ')';

                $question_id = wp_insert_post([
                    'post_title'   => $question_text,
                    'post_content' => $question_text,
                    'post_type'    => 'sfwd-question',
                    'post_status'  => 'publish',
                    'post_parent'  => $quiz->ID,
                    'post_author'  => 1,
                    'menu_order'   => $i + 1,
                ]);

                if (!is_wp_error($question_id)) {
                    update_post_meta($question_id, 'question_type', 'single');
                    update_post_meta($question_id, 'question_points', 10);
                    update_post_meta($question_id, 'question_show_explanation', 'on');

                    $answers = [];
                    foreach ($question_data['risposte'] as $letter => $risposta) {
                        $answers[] = [
                            'text' => $risposta['testo'],
                            'correct' => $risposta['corretta'] ? 'on' : '',
                            'sort' => count($answers) + 1,
                        ];
                    }

                    update_post_meta($question_id, 'question_answer_type', 'single');
                    update_post_meta($question_id, 'question_answers', $answers);
                    update_post_meta($question_id, 'question_explanation', $this->generate_lorem(200));

                    $created++;
                }
            }
        }

        echo "✓ Domande: $created create\n\n";
        return $created;
    }

    /**
     * Genera visualizzazioni simulate con distribuzione realistica
     * - Alcuni utenti vedono molto (power users)
     * - La maggior parte vede poco (average users)
     * - Alcuni non vedono quasi nulla (passive users)
     */
    public function generate_document_views() {
        echo "== Generazione 2000-3000 Visualizzazioni ==\n";

        global $wpdb;

        $users = get_users(['number' => -1, 'role' => 'subscriber']);
        $protocolli = get_posts(['post_type' => 'protocollo', 'numberposts' => -1]);
        $moduli = get_posts(['post_type' => 'modulo', 'numberposts' => -1]);
        $convenzioni = get_posts(['post_type' => 'convenzione', 'numberposts' => -1]);
        $salute = get_posts(['post_type' => 'salute-e-benessere-l', 'numberposts' => -1]);
        $comunicazioni = get_posts(['post_type' => 'post', 'numberposts' => -1]);

        $all_documents = array_merge($protocolli, $moduli, $convenzioni, $salute, $comunicazioni);

        if (empty($users) || empty($all_documents)) {
            echo "⚠ Mancano utenti o documenti\n\n";
            return 0;
        }

        $inserted = 0;

        foreach ($users as $user) {
            $profilo = get_field('profilo_professionale', "user_{$user->ID}");
            $udo = get_field('udo_riferimento', "user_{$user->ID}");

            // Distribuzione realistica e moderata delle visualizzazioni:
            // - 15% power users: vedono 25-35 documenti
            // - 60% average users: vedono 10-22 documenti
            // - 20% passive users: vedono 3-10 documenti
            // - 5% non vedono nulla
            $rand_distribution = rand(1, 100);

            if ($rand_distribution <= 15) {
                // Power user: visualizza parecchi documenti
                $num_views = rand(25, 35);
            } elseif ($rand_distribution <= 75) {
                // Average user: visualizza una buona quantità
                $num_views = rand(10, 22);
            } elseif ($rand_distribution <= 95) {
                // Passive user: visualizza moderatamente
                $num_views = rand(3, 10);
            } else {
                // Inactive user: non visualizza nulla
                $num_views = 0;
            }

            if ($num_views === 0) {
                continue;
            }

            // Shuffle all documents and select random subset
            $shuffled_docs = $all_documents;
            shuffle($shuffled_docs);
            $docs_to_view = array_slice($shuffled_docs, 0, min($num_views, count($shuffled_docs)));

            foreach ($docs_to_view as $doc) {
                $days_ago = rand(0, 60);
                $timestamp = date('Y-m-d H:i:s', strtotime("-$days_ago days"));

                $wpdb->insert($wpdb->prefix . 'document_views', [
                    'user_id'           => $user->ID,
                    'document_id'       => $doc->ID,
                    'document_type'     => $doc->post_type,
                    'user_profile'      => $profilo ?: 'unknown',
                    'user_udo'          => $udo ?: 'unknown',
                    'document_version'  => $doc->post_modified,
                    'view_timestamp'    => $timestamp,
                    'view_duration'     => rand(10, 120),
                    'ip_address'        => '127.0.0.1',
                    'user_agent'        => 'Mozilla/5.0 (Test Data Generator)'
                ]);

                $inserted++;
            }
        }

        echo "✓ Document Views: $inserted view inserite\n";
        echo "  (Distribuzione moderata: 15% power users, 60% average, 20% passive, 5% inactive)\n\n";
        return $inserted;
    }

    // =========================================================
    // HELPER FUNCTIONS
    // =========================================================

    /**
     * Assign random profili taxonomy
     */
    private function assign_random_profili_taxonomy($post_id) {
        $term_ids = [];
        $count = rand(1, 3);
        $random_profili = array_rand($this->profili, min($count, count($this->profili)));

        if (!is_array($random_profili)) {
            $random_profili = [$random_profili];
        }

        foreach ($random_profili as $idx) {
            $profilo_slug = str_replace('_', '-', $this->profili[$idx]);
            $term = get_term_by('slug', $profilo_slug, 'profilo-professionale');

            if ($term) {
                $term_ids[] = $term->term_id;
            }
        }

        if (!empty($term_ids)) {
            wp_set_object_terms($post_id, $term_ids, 'profilo-professionale');
        }
    }

    /**
     * Assign all profili taxonomy
     */
    private function assign_all_profili_taxonomy($post_id) {
        $term_ids = [];

        foreach ($this->profili as $profilo) {
            $profilo_slug = str_replace('_', '-', $profilo);
            $term = get_term_by('slug', $profilo_slug, 'profilo-professionale');

            if ($term) {
                $term_ids[] = $term->term_id;
            }
        }

        if (!empty($term_ids)) {
            wp_set_object_terms($post_id, $term_ids, 'profilo-professionale');
        }
    }

    /**
     * Assign random unita-offerta taxonomy
     */
    private function assign_random_unita_offerta_taxonomy($post_id) {
        $term_ids = [];
        $count = rand(1, 2);
        $random_udos = array_rand($this->udos, min($count, count($this->udos)));

        if (!is_array($random_udos)) {
            $random_udos = [$random_udos];
        }

        foreach ($random_udos as $idx) {
            $udo_slug = str_replace('_', '-', $this->udos[$idx]);
            $term = get_term_by('slug', $udo_slug, 'unita-offerta');

            if ($term) {
                $term_ids[] = $term->term_id;
            }
        }

        if (!empty($term_ids)) {
            wp_set_object_terms($post_id, $term_ids, 'unita-offerta');
        }
    }

    /**
     * Assign random area-competenza taxonomy
     */
    private function assign_random_area_competenza_taxonomy($post_id) {
        $term_ids = [];
        $count = rand(1, 2);
        $random_areas = array_rand($this->aree_competenza, min($count, count($this->aree_competenza)));

        if (!is_array($random_areas)) {
            $random_areas = [$random_areas];
        }

        foreach ($random_areas as $idx) {
            $area_slug = $this->aree_competenza[$idx];
            $term = get_term_by('slug', $area_slug, 'area-competenza');

            if (!$term) {
                // Create term if it doesn't exist
                $term_result = wp_insert_term($this->aree_competenza[$idx], 'area-competenza', ['slug' => $area_slug]);
                if (!is_wp_error($term_result)) {
                    $term_ids[] = $term_result['term_id'];
                }
            } else {
                $term_ids[] = $term->term_id;
            }
        }

        if (!empty($term_ids)) {
            wp_set_object_terms($post_id, $term_ids, 'area-competenza');
        }
    }

    /**
     * Genera codice fiscale casuale
     */
    private function generate_codice_fiscale() {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $cf = '';
        for ($i = 0; $i < 16; $i++) {
            $cf .= $chars[rand(0, strlen($chars) - 1)];
        }
        return $cf;
    }

    /**
     * Genera lorem ipsum
     */
    private function generate_lorem($length = 300) {
        $lorem = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';

        if (strlen($lorem) >= $length) {
            return substr($lorem, 0, $length) . '...';
        }

        while (strlen($lorem) < $length) {
            $lorem .= ' ' . $lorem;
        }

        return substr($lorem, 0, $length) . '...';
    }

    /**
     * Crea PDF placeholder
     */
    private function create_placeholder_pdf($name = 'Document') {
        $upload_dir = wp_upload_dir();

        if (!isset($upload_dir['path']) || empty($upload_dir['path'])) {
            return null;
        }

        $filename = 'test-' . strtolower($name) . '-' . time() . '-' . rand(1000, 9999) . '.pdf';
        $filepath = $upload_dir['path'] . '/' . $filename;

        // PDF minimo valido
        $pdf_content = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj 2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj 3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R/Resources<<>>>>endobj xref 0 4 0000000000 65535 f 0000000009 00000 n 0000000058 00000 n 0000000115 00000 n trailer<</Size 4/Root 1 0 R>>startxref 253 %%EOF";

        if (!file_put_contents($filepath, $pdf_content)) {
            return null;
        }

        $attachment = [
            'post_mime_type' => 'application/pdf',
            'post_title'     => $name . ' - Test',
            'post_content'   => '',
            'post_status'    => 'inherit'
        ];

        $attachment_id = wp_insert_attachment($attachment, $filepath);
        if (!is_wp_error($attachment_id)) {
            return $attachment_id;
        }

        return null;
    }

    /**
     * Crea immagine placeholder (usando GD se disponibile, altrimenti copia placeholder)
     */
    private function create_placeholder_image($name = 'Image') {
        $upload_dir = wp_upload_dir();

        if (!isset($upload_dir['path']) || empty($upload_dir['path'])) {
            return null;
        }

        $filename = 'test-' . strtolower($name) . '-' . time() . '-' . rand(1000, 9999) . '.png';
        $filepath = $upload_dir['path'] . '/' . $filename;

        // Create a simple 1x1 PNG placeholder
        $image = imagecreatetruecolor(200, 200);
        $color = imagecolorallocate($image, rand(50, 200), rand(50, 200), rand(50, 200));
        imagefill($image, 0, 0, $color);

        if (!imagepng($image, $filepath)) {
            imagedestroy($image);
            return null;
        }
        imagedestroy($image);

        $attachment = [
            'post_mime_type' => 'image/png',
            'post_title'     => $name . ' - Test',
            'post_content'   => '',
            'post_status'    => 'inherit'
        ];

        $attachment_id = wp_insert_attachment($attachment, $filepath);
        if (!is_wp_error($attachment_id)) {
            return $attachment_id;
        }

        return null;
    }

    /**
     * Run complete generation process
     */
    public function run() {
        echo "\n========================================\n";
        echo "MERIDIANA TEST DATA GENERATOR v4.0\n";
        echo "Enhanced with Courses, Lessons & Quizzes\n";
        echo "========================================\n\n";

        $start_time = microtime(true);

        // Clean up old data first
        $this->cleanup_old_data();

        // Generate new data - DOCUMENTI E CONTENUTI
        $users = $this->generate_users();
        $protocolli = $this->generate_protocolli();
        $moduli = $this->generate_moduli();
        $convenzioni = $this->generate_convenzioni();
        $salute = $this->generate_salute_benessere();
        $comunicazioni = $this->generate_comunicazioni();
        $organigrammi = $this->generate_organigramma();

        // Generate new data - CORSI LEARNDASH (SIMPLIFIED: no topics layer)
        $courses = $this->generate_courses();
        $lessons = $this->generate_lessons();
        $topics = 0;  // DISABLED: Topics layer removed for simplicity
        $quizzes = 0;  // DISABLED: Old quiz generation (will use new procedural system)
        $questions = 0;  // DISABLED: Old question generation
        $enrollments = $this->generate_course_enrollments();
        $progress = $this->generate_course_progress();

        // DISABLED: Quiz generation removed - quizzes are created manually in LearnDash
        // Users should add quizzes manually via LearnDash builder
        // $quizzes = $this->generate_lesson_quizzes();
        // $questions = $this->generate_quiz_questions();

        // Generate analytics
        $views = $this->generate_document_views();

        $elapsed = round(microtime(true) - $start_time, 2);

        echo "========================================\n";
        echo "✓ COMPLETATO IN {$elapsed}s\n";
        echo "========================================\n\n";

        return [
            'success' => true,
            'elapsed' => $elapsed,
            'users' => $users,
            'protocolli' => $protocolli,
            'moduli' => $moduli,
            'convenzioni' => $convenzioni,
            'salute' => $salute,
            'comunicazioni' => $comunicazioni,
            'organigrammi' => $organigrammi,
            'courses' => $courses,
            'lessons' => $lessons,
            'topics' => $topics,
            'quizzes' => $quizzes,
            'questions' => $questions,
            'enrollments' => $enrollments,
            'progress' => $progress,
            'views' => $views,
        ];
    }
}
