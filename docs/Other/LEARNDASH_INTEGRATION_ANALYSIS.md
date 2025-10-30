# LEARNDASH INTEGRATION ANALYSIS

**Data Analisi**: 30 Ottobre 2025
**Status**: Knowledge Base - Non vincolante
**Raccomandazione**: MEDIA INTEGRATION (Opzionale)

> ⚠️ NOTA: Questo documento è una knowledge base per futura implementazione. Non è un requisito obbligatorio o un mandato. Consultare come riferimento quando/se si decide di integrare LearnDash.

---

## EXECUTIVE SUMMARY

**RACCOMANDAZIONE: MEDIA INTEGRATION LEVEL** (se implementata)

- **Livello Ottimale**: Media Integration (60% effort sviluppo)
- **Timeline**: 3-4 settimane
- **Costo**: ~$5K setup + $199/anno licenza
- **ROI**: Reporting compliance unificato, audit trails, tracciamento certificati
- **Status**: Pronto per implementazione (phased rollout possibile)

---

## DISCOVERIE CRITICHE

### 1. Codice Già Scritto per LearnDash

In `docs/07_Notifiche_Automazioni.md` (linee 398-483):

**Funzione già implementata**: `send_certificati_alerts()`
- Queries corsi completati via `learndash_user_get_completed_courses()`
- Invia alert 7 giorni prima scadenza certificato
- Invia alert alla scadenza
- Auto re-enrollment certificati scaduti via `ld_update_course_access()`
- Integrato con Brevo email

**Funzione auto-enrollment**: `autoenroll_corsi_obbligatori()`
- Hook su `user_register`
- Assegna automaticamente corsi marcati "obbligatori-interni"
- Usa LearnDash API

**Implicazione**: 70% dell'infrastruttura di notifica è già codificata.

### 2. Timing Pre-Launch (CRITICO)

- Piattaforma: 76% completa
- Contenuto: 250+ documenti (gestibile)
- Utenti: 300 (scale manageable)
- **Momento ideale**: ORA (pre-lancio)
- **Post-lancio**: 2-3x harder da retrofittare

### 3. User Role System Perfetto

| Ruolo | Corrente | Per Corsi |
|-------|----------|-----------|
| Administrator | Full WP access | Tutto LearnDash |
| Gestore Piattaforma | Frontend dashboard | Crea/modifica/assegna corsi |
| Subscriber (Utente) | View-only content | Segue corsi, vede certificati |

**Conclusion**: Già allineato, zero modifiche necessarie.

### 4. Infrastruttura Notifiche 70% Implementata

**OneSignal**:
- Push notifications attive
- Segmentazione per role/UDO implementata
- Trigger system per content type esistente

**Brevo Email**:
- Welcome emails attivi
- Weekly digest attivo (lunedì 9am)
- Template system pronto
- List sync con metadata utenti

**Gap**: Solo aggiungere hooks per corso events (enroll, complete, expiry)

### 5. Taxonomies Allineate

Già presenti e riutilizzabili:
- `unita_offerta` (Reparti/UDO)
- `profilo_professionale` (Ruoli professionali)
- `area_competenza` (Skill areas)

Perfect per filtraggio e assegnazione corsi.

### 6. Analytics Extensibile

**Tabella Custom**: `wp_document_views`
- Progettata per scalabilità
- Columns: user_id, document_id, timestamp, duration
- Può extend per corsi (lesson_id, quiz_id, score)

---

## TRE OPZIONI ANALIZZATE

### Option A: BASE INTEGRATION (1 settimana, 20% effort)

**Cosa include**:
- Creazione corsi basic
- Auto-enrollment obbligatori
- Certificati basic
- Zero notifiche avanzate

**Verdict**: Insufficiente per contesto sanitario/compliance

---

### Option B: MEDIA INTEGRATION ⭐ RACCOMANDATO

**Cosa include**:
- Corsi completi (lezioni, quiz, certificati 12 mesi)
- 3 tipi corso (obbligatori-interni/esterni, facoltativi)
- Auto + manual enrollment
- OneSignal + Brevo integration (entrambe)
- Dashboard gestore per gestione corsi (no WP admin)
- Analytics unificati (doc + course + certificate tracking)
- CSV export compliance
- Certificate expiry automation

**Timeline**: 3-4 settimane | **Effort**: 16-19 giorni dev

**Verdict**: OPTIMAL - Giusto balance features/timing/effort

---

### Option C: FULL INTEGRATION (6+ settimane, 100% effort)

**Cosa aggiunge vs. Media**:
- Advanced grading rubrics
- Drip-feed content (scheduled release)
- Prerequisiti avanzati
- Social learning (forum/discussion)
- SCORM export
- Learner portfolios

**Verdict**: OVERKILL per caso d'uso sanitario/compliance

---

## SE IMPLEMENTATA - ROADMAP

### Fase 1: Setup & Config (3-4 giorni)
- Install LearnDash ($199)
- Certificati templates (12 month validity)
- Taxonomy: `tipologia_corso`
- ACF field group: course metadata
- Sample course test

### Fase 2: Template Development (5 giorni)
- `page-corsi.php` (4 tabs: In progress, Completati, Facoltativi, Certificati)
- `single-sfwd-courses.php` (lesson list, progress bar, certificate)
- Course card component
- `_corsi.scss` (responsive design)
- Alpine.js for interactions

### Fase 3: Gestore Dashboard (4 giorni)
- Tab "Corsi" in `/dashboard-gestore/`
- CRUD forms (create/edit/delete)
- Manual enrollment modal
- AJAX handlers (same pattern as existing Protocolli)

### Fase 4: Notifiche (3 giorni)
- Activate `send_certificati_alerts()` (already coded)
- Hook LearnDash events → OneSignal
- Hook LearnDash events → Brevo
- Extend weekly digest con sezione corsi
- Test all delivery

### Fase 5: Analytics (3 giorni)
- Course progress tracking table
- Enrollment/completion rate queries
- Dashboard analytics (KPI + charts + export)
- Integrate in `/analitiche/` page

### Fase 6: Testing & Launch (3 giorni)
- Full testing suite
- Performance testing (300 users)
- Responsive testing (mobile/tablet/desktop)
- Documentation & training

**Total**: 21 giorni = ~4 settimane

---

## NUOVI FILE DA CREARE (SE IMPLEMENTATA)

```
page-corsi.php (400 lines)
single-sfwd-courses.php (300 lines)
includes/notifications-courses.php (150 lines)
includes/analytics-courses.php (150 lines)
includes/corsi-enqueue.php (50 lines)
templates/parts/gestore/tab-corsi.php (300 lines)
templates/parts/analytics/courses-analytics.php (200 lines)
assets/css/src/pages/_corsi.scss (200 lines)
assets/js/src/corsi.js (150 lines)

TOTAL: ~2,000 linee codice nuovo
```

**File modificati**: 6 files, ~500 linee

---

## RISK & MITIGATIONS

| Risk | Likelihood | Mitigation |
|------|-----------|-----------|
| Performance impact | Medium | Load test, caching, CDN |
| Code conflicts | Low | Use only hooks, test thoroughly |
| User confusion | Medium | Clear labels, onboarding email |
| Certificate logic | Low | Codice già scritto, just activate |

---

## SUCCESS METRICS (6 mesi, se implementata)

- Completion rate: >70%
- Adoption: >80% users (≥1 corso)
- On-time renewals: >95%
- Load time: <2s
- Support issues: <5/month

---

## QUANDO IMPLEMENTARE

**Ideale**: Settimana prossima (pre-lancio)
**Accettabile**: Entro 2 settimane
**Difficile**: Post-lancio (retrofitting)

---

## CONCLUSIONE

**PRONTO PER IMPLEMENTAZIONE SE RICHIESTO**

Livello raccomandato se fatto: **MEDIA INTEGRATION**

Timing: **ORAORA** (pre-launch window closing)

Prossimi step se decidi di implementare:
1. Stakeholder approval
2. Team allocation (2 dev + 1 QA, 4 settimane)
3. Budget approval (~€5K)
4. Kickoff Phase 1

---

**Documento**: Knowledge Base - Non Vincolante
**Consultare quando**: Decidere se/come implementare LearnDash
**Mantener aggiornato**: Se circostanze cambiano (team size, timeline, budget)

