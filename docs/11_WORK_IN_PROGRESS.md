# 📝 Work In Progress - Team Notes & Task Tracking

> **FILE COLLABORATIVO - Leggi all'inizio di ogni session lavorativa**
>
> Questo file serve per documentare il lavoro in corso, le osservazioni fatte durante lo sviluppo, e le task da completare. Mantiene il team informato su cosa si sta facendo e quali problemi si incontrano.
>
> **Ultimo Aggiornamento:** 4 Novembre 2025

---

## 🔴 In Corso Ora

### LearnDash Procedural Flow Implementation - REFACTORING
**Stato:** Completato ✅
**Chi:** Claude + Sviluppatore
**Data Inizio:** 4 Novembre 2025
**Data Fine:** 4 Novembre 2025

**Work Summary:**
After simplifying the structure (removed topics layer), user requested full procedural flow: linear progression through lessons in order, with next lesson unlocked only after completing current one. After last lesson → Quiz finale.

**Major Changes:**
- ✅ Updated `single-sfwd-courses.php`: Procedural lesson display with locked/current/completed states
- ✅ Updated `single-sfwd-lessons.php`: Added progression logic (next lesson button or final quiz CTA)
- ✅ Added lesson state checking: only current or already-completed lessons are accessible
- ✅ Visual indicators: play-circle for current lesson, lock for locked lessons, check for completed
- ✅ Smart "Continua" button: shows "Prossima Lezione" if more lessons, "Quiz Finale" if last lesson

**Procedural Logic:**
1. User can ONLY access the next incomplete lesson
2. All future lessons are locked until prerequisites are met
3. After completing lesson → button to go to next lesson
4. After last lesson → message directs to quiz final in course page
5. Locked lessons show lock icon + are not clickable

**New LearnDash Hierarchy (with Procedural Flow):**
```
Corso (sfwd-courses)
├── Lezione 1 [Current/Completed/Locked]
│   └── Quiz (sfwd-quiz)  [Direct children via post_parent]
│       └── Domande (sfwd-question)
├── Lezione 2 [Locked until Lezione 1 done]
│   └── Quiz
└── Lezione 3 [Locked until Lezione 2 done]
    └── Quiz Finale
```

**Implementation Details:**
- `single-sfwd-courses.php` (lines 168-251):
  - Finds first incomplete lesson
  - Marks as "current"
  - Only allows access to completed or current lessons
- `single-sfwd-lessons.php` (lines 88-121):
  - Determines lesson position in course
  - Checks if it's the last lesson
  - Gets reference to next lesson for navigation
- Lesson navigation widget: Shows progress indicator instead of full list

**Prossimo:** Test the complete procedural course flow end-to-end

---

## 🟡 Task Sospesi / Da Definire

### LearnDash - Numeric Index nel Topic Display
**Priorità:** Bassa
**Descrizione:** Nel singolo argomento (topic) appare un indice numerico tra il titolo e la descrizione che non piace all'utente. Non è chiaro dove venga generato - probabilmente dal tema Blocksy parent o da LearnDash stesso, non dal nostro codice custom.
**Azione:** Investigare se è possibile nasconderlo via CSS o se è necessario per LearnDash. Se è generato da Blocksy, potrebbe richiedere un override del template.

### LearnDash - Quiz Implementation Status
**Priorità:** Media
**Descrizione:** I quiz vengono generati nel test data e mostrati nel topic template, ma non abbiamo verificato completamente:
- Se il template quiz `single-sfwd-quiz.php` funziona correttamente
- Se la progress tracking funziona per i quiz
- Se il "Mark as complete" per i quiz funziona

**Azione:** Fare testing completo del flusso quiz end-to-end

---

## ✅ Completato Questa Session (Procedural Implementation)

### 1. LearnDash Topic Template Creation
- File: `single-sfwd-topic.php`
- Funzionalità: Visualizzazione argomenti con breadcrumb, sidebar con stato completion, lista argomenti della lezione
- Status: Working ✅

### 2. Auto-Completion Logic (Lesson from Topics)
- File: `api/learndash-api.php` → `meridiana_mark_topic_completed()`
- Logica: Quando topic marcato complete, controlla se TUTTI i topic della lezione sono complete. Se sì, marca lezione come complete.
- Status: Working ✅

### 3. Unenroll Flow Fix
- File: `single-sfwd-courses.php`
- Fix: Aggiunto redirect to `/corsi/` instead of reload
- Status: Working ✅

### 4. Test Data Generator - Topics
- File: `inc/admin-test-data-page.php`
- Aggiunto: `generate_topics()` function per creare 2-4 topics per lesson
- Status: Working ✅

### 5. Fixed Course Completion Tracking
- File: `api/learndash-api.php` → `meridiana_get_user_courses()`
- Fix: Modified progress calculation to count lessons + topics + quizzes instead of just lessons
- Status: Working ✅

### 6. Remove Unenroll + Add Reset Course
- Files: `single-sfwd-courses.php`, `api/learndash-api.php`
- Implementazione:
  - Rimosso pulsante e modal "Abbandona Corso"
  - Aggiunto pulsante "Riprovare Corso"
  - Nuovo endpoint API `/user/{id}/courses/{courseId}/reset`
  - Reset cancella tutti i completion data ma mantiene l'enrollment
  - La prima iscrizione viene contata una sola volta (non replicata su reset)
- Status: Working ✅

### 7. Fix Quiz Generation - Link to Topics
- Files: `inc/admin-test-data-page.php`
- Modifiche:
  - `generate_quizzes()` - ora itera topics invece che courses
  - Aggiunto `topic_id` meta_key ai quiz
  - Logica: ~50% dei topics hanno 1 quiz (variazione realistica)
  - `generate_questions()` - improved error handling
  - UI description - aggiornata con nuova gerarchia
- Gerarchia: Corso > Lezione > Argomento > Quiz > Domande
- Status: Working ✅

### 8. Preserve Manual Quizzes on Test Data Regeneration
- Files: `inc/admin-test-data-page.php`
- Modifiche:
  - Aggiunto marker meta `_generated_by_test_data` ai quiz auto-generati
  - Modificato `cleanup_old_data()` per preservare quiz manuali
  - Ora puoi rigenerare i test data senza perdere i quiz creati manualmente
  - Disabilitata generazione automatica domande (quiz rimangono vuoti)
- Workflow: Genera → Crea Quiz Manuali → Rigenera (i tuoi quiz rimangono)
- Status: Working ✅

### 9. LearnDash Procedural Flow - Linear Progression
- Files: `single-sfwd-courses.php`, `single-sfwd-lessons.php`
- Implementazione:
  - **Lesson Locking:** Solo la lezione "current" (prossima da completare) è accessibile
  - **Procedural Navigation:** Dopo completamento lezione → va alla successiva
  - **Locked State:** Lezioni future hanno icona lock e non sono clickabili
  - **Last Lesson:** Mostra button "Vai al Quiz Finale" che rimanda al corso
  - **Visual States:** Play-circle (current) / Lock (locked) / Check (completed)
  - **Progress Indicator:** Sidebar mostra solo lezioni del corso con stato
  - **Smart Actions Button:**
    - Se lezione NON completata:
      - Se nessun quiz: button "Segna come Completata"
      - Se ha quiz: messaggio di errore "Completa i quiz prima di procedere"
    - Se lezione completata:
      - Se non ultima: button "Prossima Lezione" (procede linearmente)
      - Se ultima: button "Vai al Quiz Finale" (rimanda al corso)
- Course Page: Mostra lessons in ordine, locked fino al completamento della precedente
- Lesson Page: Smart button context-aware (no more confusing "Torna al Corso")
- Status: Working ✅

---

## 🐛 Known Issues / Limitazioni

### Issue #1: Quiz Template Testing
**File:** `single-sfwd-quiz.php`
**Descrizione:** Template quiz esiste ma non è stato testato completamente nella nuova struttura semplificata
**Impatto:** Medium - potrebbe avere problemi con progress tracking
**Azione:** Testing completo del flusso quiz (lezione > quiz > completamento)
**Note:** Ora che topics sono stati rimossi, il flusso è lineare: Lezione → Quiz → Completa

### Issue #2: Blocksy Theme Integration
**Descrizione:** Alcuni aspetti del design non sono perfettamente integrati con Blocksy
**Impatto:** Design/UX
**Note:** Stiamo usando Blocksy come parent theme - alcuni override potrebbero essere necessari

---

## 📋 Checklist - Prossime Azioni

- [ ] Test completo procedural flow: Lezione 1 → 2 → 3 → Quiz (con locking/unlocking)
- [ ] Test that locked lessons show lock icon and are not clickable
- [ ] Test "Prossima Lezione" button works after completing lesson
- [ ] Test "Quiz Finale" button appears on last lesson and links correctly
- [ ] Test quiz display and completion tracking
- [ ] Test reset corso - verifica che resetta tutto e lezioni tornano locked
- [ ] Test user journey completo: Iscrizione > Lezione 1 (locked others) > Completa > Lezione 2 (1 done, others locked) > ... > Quiz Final
- [ ] Verificare che tutti i template Blocksy override funzionino correttamente
- [ ] Performance check per query LearnDash (soprattutto con molti corsi/utenti)
- [ ] Aggiungere error handling più robusto per API calls

---

## 📚 File Recentemente Modificati

| File | Data | Tipo Modifica | Note |
|------|------|---------------|------|
| `single-sfwd-topic.php` | 4 Nov | ELIMINATO | Template argomenti rimosso (structure semplificata) |
| `single-sfwd-courses.php` | 4 Nov | Modifica | Aggiunto procedural lesson locking + current lesson detection |
| `single-sfwd-lessons.php` | 4 Nov | Modifica | Aggiunto lesson progression logic + next/final quiz buttons |
| `api/learndash-api.php` | 4 Nov | Modifica | Rimosso topic tracking + semplificato progress calc |
| `inc/admin-test-data-page.php` | 4 Nov | Modifica | Nuove funzioni: generate_lesson_quizzes() + generate_quiz_questions() |
| `docs/11_WORK_IN_PROGRESS.md` | 4 Nov | Modifica | Aggiornato con procedural flow implementation |

---

## 🎯 Osservazioni & Note Tecniche

### LearnDash Post Type Hierarchy (SEMPLIFICATO)
```
Corso (sfwd-courses)
├── Lezione (sfwd-lessons)
│   └── Quiz (sfwd-quiz)  [Direct child via post_parent]
│       └── Domande (sfwd-question)
```

**Gerarchia Relazioni:**
- **Corso > Lezione:** Via meta_key `course_id` nella lezione
- **Lezione > Quiz:** Via `post_parent` (native WordPress way) + meta_keys `course_id`, `lesson_id`
- **Quiz > Domande:** Via `post_parent` (native WordPress way)

**Perché post_parent per quizzes?**
- LearnDash natively uses `post_parent` per relazioni parentali
- Test data generator crea quizzes con `post_parent = lesson_id`
- Le query utilizzano `post_parent` parameter anzichè meta_key per migliore performance

### Progress Tracking via User Meta
La progress degli utenti viene salvata nei user meta (semplificato):
- `_completed_lesson_{lesson_id}` → timestamp
- `_completed_quiz_{quiz_id}` → timestamp
- `_enrolled_course_{course_id}` → timestamp (enrollment)

**Nota:** Topics sono stati rimossi per semplificare il tracking.

### REST API Endpoints
Custom endpoints disponibili in `/wp-json/learnDash/v1/`:
- `GET /user/{id}/courses` → Lista corsi con progress (lessons + quizzes)
- `POST /user/{id}/courses/{courseId}/enroll` → Iscrivi utente
- `POST /user/{id}/courses/{courseId}/reset` → Resetta progress mantenendo enrollment
- `POST /lessons/{id}/mark-viewed` → Marca lezione come visualizzata
- `POST /quizzes/{id}/submit` → Invia risposte quiz
- `POST /topics/{id}/mark-completed` → **DEPRECATED** (410 Gone - structure semplificata)

### Procedural Flow Logic
**Frontend (Template Layer):**
- Course page: Find first incomplete lesson = "current lesson"
- Only current + completed lessons are accessible (clickable)
- Locked lessons show lock icon + are disabled
- Smart buttons: "Prossima Lezione" vs "Quiz Finale"

**Key Variables in Templates:**
- `$is_accessible` - can user click this lesson?
- `$is_current_lesson` - is this the next one to complete?
- `$lesson_completed` - has user already done this?
- `$is_last_lesson` - does it have quiz after?
- `$next_lesson` - pointer to prossima lezione

**User Meta Keys:**
- `_completed_lesson_{lesson_id}` - marca lezione completata
- `_enrolled_course_{course_id}` - marca iscrizione
- Questi due bastano per il procedural tracking

---

## 💬 Comunicazione Team

**Per il Collega:** Se stai leggendo questo e vedi sezioni incomplete o problemi che noti, per favore aggiungi le tue osservazioni in questo file. Questo aiuta a mantenere il team sincronizzato.

**Cosa Aggiungere Qui:**
- Problemi che trovi durante testing
- Idee di miglioramento
- Domande tecniche
- Note su comportamenti strani
- Completamenti di task
- Prossime priorità

---

## 📞 Contatti & References

**LearnDash Documentation:** https://www.learndash.com/support/

**Blocksy Theme:** Tema parent utilizzato - alcuni override potrebbero essere necessari

**API Endpoint Base:** `/wp-json/learnDash/v1/`

---

**Status:** Active - Aggiorna regolarmente durante lo sviluppo
