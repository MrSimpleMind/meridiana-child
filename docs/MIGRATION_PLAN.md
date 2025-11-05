# 🚀 MIGRATION PLAN - La Meridiana to Staging/Live

**Data**: 5 Novembre 2025
**Scopo**: Step-by-step guide per migrare sito da Local a Siteground staging/live
**Risk Level**: 🟢 LOW (Zero hardcoded elements)

---

## 📋 EXECUTIVE SUMMARY

```
Current State:      La Meridiana su Local by Flywheel ✅
Target State:       La Meridiana su Siteground hosting ✅
Migration Method:   Free migration service di Siteground
Timeline:           2-3 settimane
Effort (your):      ~3-4 ore total
Risk:               LOW - no hardcoded elements
Cost:               €11/mese hosting + €12/anno dominio

Status: READY TO MIGRATE ✅
```

---

## 🎯 FASE 1: PREPARAZIONE (Giorni 1-2)

### STEP 1.1: Backup Locale Completo
**Tempo**: 30 minuti
**Fatto da**: Te

```
☐ Backup tema (meridiana-child) - ZIP
  Location: C:\Users\utente\Desktop\meridiana-child-v1.0.0-backup.zip
  Status: ✅ ALREADY DONE

☐ Backup database - Export SQL
  Via: Local by Flywheel > Database > Export
  Destination: Desktop/meridiana-db-backup-YYYYMMDD.sql
  Time: 5-10 min

☐ Backup media (uploads folder)
  Location: \app\public\wp-content\uploads\
  Size: ? MB (da misurare)
  Time: 5-10 min via file explorer

Verification:
- [ ] Ho 3 backup (tema, database, media)
- [ ] Backup salvati in posto sicuro
- [ ] Ho copia anche su cloud (opzionale ma consigliato)
```

### STEP 1.2: Inventory Contenuti
**Tempo**: 15 minuti
**Fatto da**: Te

```
Via WordPress Admin (http://nuova-formazione.local/wp-admin):

☐ Conta Corsi
  Menu: LearnDash > Courses
  Total: ___ corsi

☐ Conta Lezioni
  Menu: LearnDash > Lessons
  Total: ___ lezioni

☐ Conta Quiz
  Menu: LearnDash > Quizzes
  Total: ___ quiz

☐ Conta Utenti
  Menu: Utenti > Tutti gli utenti
  Total: ___ utenti attivi

☐ Misura Media
  Menu: Media > Libreria > Sort by size
  Total uploads size: ___ MB

☐ Conta Post Generici
  Menu: Post
  Total: ___ post
```

### STEP 1.3: Pre-Migration Optimization (Opzionale ma consigliato)
**Tempo**: 1 ora
**Fatto da**: Te (opzionale)

```
☐ Rimuovi node_modules (non serve in produzione)
  Cartella: /assets/node_modules/
  Riduci: 60 MB
  Via: Elimina cartella (è solo build tools)

☐ Pulisci database
  Plugin: WP-Optimize o simile
  Rimuovi: Post revisions, transient scaduti
  Riduzione: 5-10% database size

☐ Disabilita debug mode
  File: wp-config.php
  Cambia: WP_DEBUG = false
  Rimuovi: wp-content/debug.log

RISULTATO: Sito più leggero per migrazione (-60+ MB possibile)
```

### STEP 1.4: Verifica Pre-Migration
**Tempo**: 15 minuti
**Fatto da**: Te

```
☐ Test sito funziona localmente
  URL: https://nuova-formazione.local/
  Verifica: Homepage carica ✓

☐ Test corsi caricate
  URL: https://nuova-formazione.local/corsi/
  Verifica: Almeno 1 corso visibile ✓

☐ Test wp-admin accesso
  URL: https://nuova-formazione.local/wp-admin/
  Verifica: Login funziona ✓

☐ Verifica URL corretto
  Settings > General > Indirizzo sito
  Valore: https://nuova-formazione.local/
  Status: ✓ CORRECT

☐ Test REST API
  URL: https://nuova-formazione.local/wp-json/
  Verifica: API index carica ✓
```

---

## 🎯 FASE 2: HOSTING SELECTION (Giorno 3)

### STEP 2.1: Scegli Provider
**Tempo**: 30 minuti
**Fatto da**: Te

**RACCOMANDAZIONE: SITEGROUND GrowBig**

```
Perché Siteground:
✅ Free migration service (loro fanno il lavoro)
✅ 24/7 WordPress support (Italian available)
✅ Daily automatic backups included
✅ Free SSL certificate (Let's Encrypt)
✅ CDN Cloudflare included
✅ Good performance (SSD hosting)
✅ ~€11/mese (buon prezzo)

Alternative:
- Kinsta (€35+/mese - premium, overkill)
- Bluehost (€6-9/mese - budget, basic support)

SCELTA: Siteground GrowBig
```

### STEP 2.2: Registra Dominio (Se necessario)
**Tempo**: 15 minuti
**Fatto da**: Te

```
Se non hai dominio:
☐ Scegli dominio (tuodominio.com)
☐ Registra su Siteground o registrar favorite
☐ Costo: ~€12/anno

Se hai dominio:
☐ Note: Siteground can help transfer
☐ O: Keep at current registrar + update nameservers
```

### STEP 2.3: Compra Siteground Hosting
**Tempo**: 30 minuti
**Fatto da**: Te

```
1. Vai a: https://www.siteground.com/
2. Scegli: GrowBig plan (~€11/mese)
3. Dominio: Usa nuovo o transfer da registrar
4. Pagamento: Metodo preferito
5. Attiva account

Result: Email con login credentials
- Hosting URL: es. nuova-formazione.com
- cPanel access
- FTP/SFTP access
- Database credentials
```

### STEP 2.4: Contatta Siteground per Migrazione
**Tempo**: 15 minuti
**Fatto da**: Te

```
Via cPanel (dopo aver attivato hosting):

1. cPanel > Migrations > Migrate a Website
2. Click: "Migrate Website to Siteground"
3. Form:
   - Source site URL: https://nuova-formazione.local/
   - O: Backup file path (se preferisci upload backup)
   - Email: Your email
   - Message: "La Meridiana WordPress site with LearnDash"

4. Submit

Siteground farà:
✓ Backup del sito locale (oppure usa tuo backup)
✓ Importa nel nuovo hosting
✓ Setup database
✓ Configura wp-config.php
✓ Email quando finito (30-60 min)
```

---

## 🎯 FASE 3: MIGRAZIONE TECNICA (Giorni 4-5)

### STEP 3.1: Aspetta Siteground
**Tempo**: 1 ora
**Fatto da**: Siteground

```
Siteground fa tutto automaticamente:
- Copia file system
- Migra database
- Setup nuova installazione WordPress
- Configura wp-config.php con nuove credenziali
- Email di conferma quando pronto

Tu aspetti email "Migration Complete"
```

### STEP 3.2: Verifica Migrazione Iniziale
**Tempo**: 15 minuti
**Fatto da**: Te

```
Dopo email da Siteground:

☐ Test URL temporaneo
  URL: http://[temporary-ip-or-url]/
  O: https://tuodominio.com (se DNS già puntato)

☐ Homepage carica?
  Visual check: Home page shows correctly ✓

☐ Accedi wp-admin
  URL: https://tuodominio.com/wp-admin/
  Username: Stesso di prima
  Password: Stesso di prima
  Status: Login funziona? ✓

Se problemi:
→ Contact Siteground support (24/7)
→ Loro risolvono gratuitamente
```

### STEP 3.3: Configura DNS (Se nuovo dominio)
**Tempo**: 30 minuti
**Fatto da**: Te + registrar

```
Se usando dominio nuovo registrato via Siteground:
☐ Nameservers already configured
☐ Attendi propagazione (1-48 ore)
☐ Test: https://tuodominio.com/ carica

Se trasferendo dominio da registrar:
☐ Vai a registrar attuale
☐ Update Nameservers a Siteground:
   ns1.siteground.net
   ns2.siteground.net
   ns3.siteground.net
☐ Attendi propagazione (24-48 ore)
☐ Test: https://tuodominio.com/ carica

Se mantieni dominio da registrar diverso:
☐ Update A record a IP di Siteground
☐ Oppure: Update CNAME record
☐ Siteground fornisce istruzioni via email
```

---

## 🎯 FASE 4: TESTING COMPLETO (Giorni 6-8)

### STEP 4.1: Test Funzionalità Core
**Tempo**: 1 ora
**Fatto da**: Te

```
☐ Homepage carica e looks good
  URL: https://tuodominio.com/
  Visual: Logo, menu, content ✓

☐ Navigazione funziona
  Test: Click links, menus work ✓

☐ Cerca funziona
  Search box: Search per "test" funziona ✓

☐ Mobile responsive
  Test: Apri da mobile o resize browser ✓
```

### STEP 4.2: Test LearnDash
**Tempo**: 1.5 ore
**Fatto da**: Te

```
☐ Corsi caricate
  URL: https://tuodominio.com/corsi/
  Verifica: Almeno 1 corso visibile ✓

☐ Lezioni caricate
  Click: 1 corso → lezioni appaiono ✓

☐ Quiz funziona
  Vai a: Quiz page
  Complete: Quiz fino alla fine ✓
  Salva: Score registrato ✓

☐ User progress salvo
  Login: Come student
  Completa: 1 lezione
  Verifica: Progress registrato ✓

☐ Certificati generati
  Complete: Un corso intero (opzionale se hai)
  Verifica: Certificato generato ✓
```

### STEP 4.3: Test Utenti
**Tempo**: 30 minuti
**Fatto da**: Te

```
☐ Utenti migrati
  wp-admin > Utenti > Tutti
  Verifica: Numero utenti = locale ✓

☐ Role preserved
  Controlla: admin, instructor, student roles OK ✓

☐ User data intatto
  Login: Con account existing
  Verifica: Profilo dati OK ✓

☐ Enrollment preserved
  Admin check: Utenti assegnati a corsi ✓
  Student check: Corsi visibili in dashboard ✓
```

### STEP 4.4: Test API & Features
**Tempo**: 30 minuti
**Fatto da**: Te

```
☐ REST API funziona
  URL: https://tuodominio.com/wp-json/
  Verifica: API index carica ✓

☐ LearnDash API endpoints
  URL: https://tuodominio.com/wp-json/learnDash/v1/
  Verifica: Endpoints respond ✓

☐ Custom endpoints
  URL: https://tuodominio.com/wp-json/piattaforma/v1/
  Verifica: Custom endpoints work ✓

☐ OneSignal (Se implementato)
  Verifica: Push notifications sent (test)

☐ Email funziona
  Trigger: Contact form o user notification
  Verifica: Email arriva ✓
```

### STEP 4.5: Test Performance
**Tempo**: 30 minuti
**Fatto da**: Te

```
☐ Load time accettabile
  Tool: Google PageSpeed Insights
  Target: < 3 secondi homepage
  Result: _________ secondi

☐ No console errors
  Open: Browser DevTools > Console
  Verifica: No red errors ✓

☐ No broken images
  Visual check: Tutte immagini caricate ✓

☐ CSS/JS caricati
  DevTools > Network tab
  Verifica: main.css e main.js caricate ✓
```

### STEP 4.6: Test Sicurezza Iniziale
**Tempo**: 15 minuti
**Fatto da**: Te

```
☐ HTTPS funziona
  URL: https://tuodominio.com/
  Green lock: Sì ✓

☐ Certificato valido
  Click lock > View Certificate
  Verifica: Valido, not expired ✓

☐ wp-admin protetto
  URL: https://tuodominio.com/wp-admin/
  Chiede login: Sì ✓

☐ Old URL reindirizza
  URL: https://nuova-formazione.local/
  Result: Doesn't work (expected, local only)
```

---

## 🎯 FASE 5: GO-LIVE (Giorno 9)

### STEP 5.1: Final Pre-Go-Live Checklist
**Tempo**: 30 minuti
**Fatto da**: Te

```
TESTING CHECKLIST (da FASE 4):
☐ Homepage funziona
☐ LearnDash corsi caricate
☐ Utenti migrati
☐ REST API funziona
☐ HTTPS works
☐ Performance OK

BACKUP CHECKLIST:
☐ Backup locale salvato (3 copie)
☐ Siteground automatic backups attive
☐ Database backup recente

COMUNICAZIONI:
☐ Messaggio pronto per utenti
☐ Email notifica pronta
☐ Social media post pronto (opzionale)
```

### STEP 5.2: Comunicare Cambio URL agli Utenti
**Tempo**: 30 minuti
**Fatto da**: Te

```
Prepara messaggio tipo:

"Caro utente,

La Meridiana si è trasferita su un nuovo server!

🆕 Nuovo URL: https://tuodominio.com/
(Il vecchio link non funzionerà più)

✅ Tutto rimane uguale:
- I tuoi corsi sono ancora lì
- Il tuo progresso è salvato
- Tutte le credenziali rimangono le stesse

Se hai problemi di accesso:
- Pulisci cache del browser (Ctrl+Shift+Del)
- Prova incognito window
- Contattami se ancora problemi

Grazie,
[Il tuo nome]"

Invia:
☐ Email a tutti gli utenti
☐ Post su sito (se announcement section)
☐ Message in-app (se notifica system)
```

### STEP 5.3: Attiva Sicurezza Post-Migrazione
**Tempo**: 1 ora
**Fatto da**: Te

```
Vedi: SECURITY_POST_MIGRATION.md per checklist completa

Quick security setup:

☐ Update WordPress
  wp-admin > Dashboard > Updates
  Click: Update WordPress core

☐ Update all plugins
  wp-admin > Plugins > Updates
  Click: Update all plugins

☐ Strong admin password
  wp-admin > Users > Your account
  Generate: Strong password (16+ chars)

☐ Install Wordfence (security plugin)
  wp-admin > Plugins > Add new
  Search: Wordfence
  Install & Activate

☐ Configure Wordfence
  Wordfence > Firewall
  Enable: All features

☐ Setup backups
  Siteground: Verify automatic daily backups active
```

### STEP 5.4: Setup Monitoring
**Tempo**: 30 minuti
**Fatto da**: Te

```
☐ Uptime monitoring
  Tool: UptimeRobot (free)
  Setup: Monitor https://tuodominio.com/
  Alert: Via email se down

☐ Security scanning
  Tool: Wordfence (già installato)
  Schedule: Daily scan
  Alert: Email se problemi

☐ Performance monitoring
  Tool: Google Search Console (free)
  Setup: Connetti sito
  Monitor: Performance metrics
```

---

## 🚨 DISASTER RECOVERY

### Se Qualcosa Va Male

```
PROBLEMA: Sito non carica
SOLUZIONE:
1. Check DNS propagation (può richiedere 24-48h)
2. Check wp-config.php database credentials
3. Contact Siteground support (24/7)
4. Rollback: Ripristina backup locale e riprova

PROBLEMA: Database non migrato
SOLUZIONE:
1. Contact Siteground support
2. Loro ripete migrazione
3. Sono esperti - risolveranno

PROBLEMA: Utenti non riescono ad accedere
SOLUZIONE:
1. Pulisci browser cache
2. Prova incognito window
3. Reset password se necessario
4. Contact support

PROBLEMA: Corsi/lezioni non visibili
SOLUZIONE:
1. Verifica database migrato (vai wp-admin > LearnDash)
2. Se missing: Contact Siteground per re-migrate
3. Hai backup locale per restore

IMPORTANTE: Non hai perso nulla!
✅ Hai backup locale completo
✅ Siteground ha automatic backups
✅ Puoi sempre tornare indietro
```

---

## 📊 TIMELINE SUMMARY

```
GIORNO 1-2:    Backup + Inventory + Optimization
GIORNO 3:      Hosting selection + Siteground signup
GIORNO 4-5:    Siteground migrazione
GIORNO 6-8:    Testing completo
GIORNO 9:      Go-live + Comunicazione
GIORNO 10+:    Monitoraggio + Ottimizzazione

TOTALE: ~2 settimane
TUO LAVORO: 3-4 ore total
SITEGROUND: 40 minuti (migrazione automatica)
```

---

## 💰 COSTI

```
Hosting (Siteground GrowBig):     €11/mese
Dominio (se nuovo):               €12/anno
Setup fee (Siteground):           €0 (free migration)
Total Year 1:                     €144/anno
Total Year 2+:                    €132/anno
```

Vedi: COST_AND_TIME_ANALYSIS.md per breakdown completo

---

## ✅ AFTER MIGRAZIONE

```
Immediate (Day 1):
☐ Security hardening (Wordfence, updates)
☐ Monitoring setup (UptimeRobot)
☐ Backup verification

Week 1:
☐ Performance optimization (Phase 1 quick wins)
☐ Security audit (Wordfence scans)
☐ User feedback collection

Week 2-4:
☐ Performance optimization (Phase 2-3)
☐ Additional security (2FA, advanced hardening)
☐ Monitoring & maintenance routine
```

Vedi: SECURITY_POST_MIGRATION.md e PERFORMANCE_OPTIMIZATION.md

---

**Plan Created**: 5 Novembre 2025
**Status**: ✅ READY TO EXECUTE
**Next Step**: Leggi HARDCODED_ELEMENTS_AUDIT.md
**Then**: Inizia FASE 1 quando pronto

