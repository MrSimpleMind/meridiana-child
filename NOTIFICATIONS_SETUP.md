# 📬 Sistema Notifiche Push - Guida Implementazione

**Data Creazione**: 29 Ottobre 2025
**Versione**: 1.0
**Stato**: Pronto per sincronizzazione ACF

---

## 📋 COSA È STATO CREATO

### File ACF JSON (3 file)
```
acf-json/
├── post_type_notification_segmentation.json     (CPT per segmentazioni)
├── group_notification_segmentation_fields.json  (Field Group segmentazione)
└── group_notification_triggers.json             (Field Group trigger)
```

### File PHP (3 file)
```
includes/notifications/
├── notification-setup.php           (Setup ACF options page)
├── notification-system.php          (Core system engine)
└── notification-triggers.php        (Configurazione OneSignal + API)
```

### File Aggiornato
```
functions.php   (Aggiunto 3 require_once per i nuovi file)
```

---

## 📋 COME FUNZIONA IL SISTEMA

### Flusso Completo:

```
BACKEND (WordPress Admin):
├─ Gestore pubblica Protocollo/Modulo/Comunicazione
├─ Hook PHP: publish_* si attiva
├─ Sistema legge trigger e segmentazione da ACF
├─ OneSignal API riceve richiesta di invio notifiche
└─ OneSignal invia push ai subscriber

FRONTEND (Browser/PWA degli Users):
├─ User accede al sito
├─ OneSignal SDK si carica automaticamente
├─ OneSignal chiede: "Vuoi ricevere notifiche?"
├─ User clicca "Consenti"
├─ OneSignal lo registra come subscriber
├─ User riceve notifica push
├─ User clicca notifica
└─ Apre il documento/articolo nel sito
```

---

## 🚀 STEP 1: Sincronizzazione ACF (5 minuti) ✅ FATTO!

### 1.1 Accedi al Dashboard WordPress
```
http://localhost:site-url/wp-admin
```

### 1.2 Vai a ACF → Sincronizzazione
```
Dashboard → ACF → Sincronizzazione
```

### 1.3 Sincronizza i nuovi Field Groups
Vedrai 3 nuovi elementi:
- ✅ `group_notification_triggers` (Field Group)
- ✅ `group_notification_segmentation_fields` (Field Group)
- ✅ `post_type_notification_segmentation` (Custom Post Type)

**Clicca "Sincronizza" su ognuno.**

### 1.4 Verifica
```
Dashboard → ACF → Field Groups
Dovresti vedere:
  ✅ Configurazione Notifiche
  ✅ Impostazioni Segmentazione
```

---

## 🔑 STEP 2: Setup OneSignal (10 minuti)

### 2.1 Crea Account OneSignal (se non lo hai)
```
https://onesignal.com → Sign Up
```

### 2.2 Crea nuovo Progetto
```
Dashboard OneSignal
→ Create new project
→ Nome: "Meridiana Formazione"
→ Type: "Web"
→ Clicca "Create project"
```

### 2.3 Ottieni Credenziali
Nel dashboard OneSignal:
```
1. Settings → App Settings
2. Sezione "Keys & IDs"
3. Copia: "App ID"
4. Copia: "REST API Key"
```

### 2.4 Configura nel Dashboard WordPress
```
Dashboard → Notifiche → OneSignal Setup
```

Compila:
- **App ID**: [Incolla App ID da OneSignal]
- **REST API Key**: [Incolla REST API Key da OneSignal]

Clicca **"Salva Credenziali"**

### 2.5 Verifica Status
```
Dashboard → Notifiche → OneSignal Setup
Dovresti vedere: ✅ OneSignal è configurato correttamente
```

---

## ⚙️ STEP 3: Crea la Prima Segmentazione (5 minuti)

### 3.1 Vai al Menu Segmentazioni Notifiche
```
Dashboard → Segmentazioni Notifiche
Clicca "Aggiungi Segmentazione"
```

### 3.2 Compila il Form
```
Titolo: "Tutti i Subscriber"

Tipo Regola: [Tutti i Subscriber ▼]

Descrizione: "Invia notifiche a TUTTI gli utenti subscriber"

Salva bozza
```

### 3.3 Verifica
Dovresti vedere il nuovo post nel CPT "Segmentazioni Notifiche"

---

## 📢 STEP 4: Crea il Primo Trigger (5 minuti)

### 4.1 Vai a Dashboard → Notifiche
```
Clicca su "Configurazione Notifiche"
Sezione: "Trigger Notifiche"
Clicca "Aggiungi Trigger"
```

### 4.2 Compila il Form

```
ID Trigger:              trigger_new_protocol
Post Type:               [Protocollo ▼]
Abilitato:               [✓] Checked
Template Titolo:         Nuovo {{post_type}}: {{title}}
Template Messaggio:      Scarica il documento {{title}}
Emoji/Icona:             📄
Regola Segmentazione:    [Tutti i Subscriber ▼]

Salva
```

### 4.3 Ripeti per gli altri Post Types

Aggiungi altri 2 trigger:

**Trigger 2 - Moduli:**
```
ID Trigger:              trigger_new_modulo
Post Type:               [Modulo ▼]
Template Titolo:         Nuovo {{post_type}}: {{title}}
Template Messaggio:      Compila il modulo {{title}}
Emoji/Icona:             📋
Regola Segmentazione:    [Tutti i Subscriber ▼]
```

**Trigger 3 - Comunicazioni:**
```
ID Trigger:              trigger_new_comunicazione
Post Type:               [Comunicazione ▼]
Template Titolo:         {{title}}
Template Messaggio:      Leggi la comunicazione importante
Emoji/Icona:             📢
Regola Segmentazione:    [Tutti i Subscriber ▼]
```

---

## ✅ STEP 5: Test (5 minuti)

### 5.1 Test via Dashboard (Admin)
```
Dashboard → Notifiche

Sezione "Testa Trigger":
  Seleziona: [Protocollo ▼]
  Clicca: [Invia Notifica Test]

Dovresti ricevere una notifica di test sul tuo browser!
```

### 5.2 Test Pubblicando Contenuto Reale
```
1. Crea un nuovo Protocollo
   Dashboard → Protocolli → Aggiungi nuovo
   Titolo: "Test Notifica"
   PDF: [carica file]
   Pubblica

2. Controlla se i subscriber ricevono la notifica
   (Verifica su OneSignal Dashboard → Messages)

3. Se ricevuta ✅, tutto funziona!
```

---

## 🎯 CONFIGURAZIONI AVANZATE

### Segmentazione per Profilo Professionale

```
1. Crea nuova Segmentazione
   Titolo: "Solo Dirigenti"

   Tipo Regola: [Per Profilo Professionale ▼]
   Profilo Professionale: [Dirigente ▼]

   Descrizione: "Solo utenti con profilo Dirigente"
   Salva

2. Usa in un trigger
   Crea trigger per un protocollo riservato
   Regola Segmentazione: [Solo Dirigenti ▼]
```

### Segmentazione per UDO + Profilo

```
1. Crea nuova Segmentazione
   Titolo: "Dirigenti UDO Nord"

   Tipo Regola: [Per Profilo + UDO ▼]
   Profilo: [Dirigente ▼]
   UDO: [Nord ▼]

   Salva

2. Usa in trigger → Solo dirigenti della zona Nord riceveranno la notifica
```

### Segmentazione per Stato Utente

```
1. Crea nuova Segmentazione
   Titolo: "Solo Utenti Attivi"

   Tipo Regola: [Per Stato Utente ▼]
   Stato: [Solo Attivi ▼]

   Salva

2. Usa per garantire che gli utenti sospesi NON ricevano notifiche
```

---

## 🔄 WORKFLOW COMPLETO

Quando pubblichi un **Protocollo**:

```
1. [Crea Protocollo] in WordPress
2. [Compila i campi ACF]
3. [Pubblica]
   ↓
4. Hook PHP: publish_protocollo si attiva
   ↓
5. MeridianaNotificationSystem::trigger_notification()
   - Legge il trigger_new_protocol da ACF
   - Parsa il template: "Nuovo Protocollo: {{title}}"
   - Carica la segmentazione: "Tutti i Subscriber"
   - Queries gli utenti subscriber
   ↓
6. OneSignal API: POST /api/v1/notifications
   - Invia push a tutti gli utenti registrati
   ↓
7. Browser/PWA dei subscriber riceve notifica
```

---

## 📝 PLACEHOLDER DISPONIBILI

Nei template puoi usare:
- `{{post_type}}` → es. "Protocollo", "Modulo", "Comunicazione"
- `{{title}}` → Titolo del contenuto
- `{{author}}` → Nome autore
- `{{date}}` → Data di pubblicazione (dd/mm/yyyy)
- `{{excerpt}}` → Prime 20 parole del contenuto

**Esempi:**
```
Template: "Nuovo {{post_type}}: {{title}}"
Risultato: "Nuovo Protocollo: COVID-19 Safety"

Template: "{{author}} ha pubblicato {{post_type}}"
Risultato: "Mario Rossi ha pubblicato Modulo"
```

---

## 🐛 TROUBLESHOOTING

### "Notifiche non arrivano"
```
1. Verifica che OneSignal sia configurato
   Dashboard → Notifiche → OneSignal Setup
   Status deve essere: ✅ Configurato

2. Verifica che il trigger sia ABILITATO
   Dashboard → Notifiche
   Check: [✓] Abilitato

3. Verifica che almeno 1 utente sia subscriber
   Per ricevere notifiche, l'utente deve:
   - Essere loggato
   - Avere role "subscriber"
   - Aver cliccato "Consenti" al prompt notifiche

4. Controlla i log
   /wp-content/debug.log
   Cercare errori OneSignal
```

### "Credenziali OneSignal errate"
```
1. Vai su OneSignal Dashboard
2. Copia nuovamente App ID e REST API Key
3. Dashboard → Notifiche → OneSignal Setup
4. Salva le nuove credenziali
5. Test di nuovo
```

### "Il trigger si attiva ma con un messaggio di errore"
```
1. Controlla se la Regola Segmentazione è selezionata
   Nel trigger, il campo "Regola Segmentazione" deve avere un valore

2. Verifica che la segmentazione sia un post_type valido
   Dashboard → Segmentazioni Notifiche
   La segmentazione deve essere lì
```

---

## 🛠️ CUSTOMIZZAZIONE: Aggiungere Logica Custom

Se vuoi una segmentazione non standard (es: "Solo utenti che hanno visualizzato il documento X"), crea una classe custom:

```php
// includes/notifications/custom-queries.php

namespace MeridianaNotification;

class CustomQueryViewedDocument {
    public static function get_target_users($post_id) {
        global $wpdb;

        // Query utenti che hanno visualizzato il documento
        $user_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT user_id FROM {$wpdb->prefix}document_views
             WHERE document_id = %d",
            $post_id
        ));

        return get_users(['include' => $user_ids]);
    }
}
```

Poi usa in segmentazione:
```
Tipo Regola: [Query Custom ▼]
Classe Query Custom: MeridianaNotification\\CustomQueryViewedDocument
```

---

## 📚 RIFERIMENTI

- **OneSignal Docs**: https://documentation.onesignal.com
- **OneSignal Dashboard**: https://dashboard.onesignal.com
- **WordPress REST API**: https://developer.wordpress.org/rest-api/
- **ACF Pro**: https://www.advancedcustomfields.com

---

## ✨ PROSSIMI STEP

Una volta che il sistema è funzionante:

1. **Integra nel Dashboard Gestore** (aggiungere bell icon con notifiche non-lette)
2. **Setup PWA Service Worker** (OneSignal funziona anche offline)
3. **Analytics** (tracciare click su notifiche vs visualizzazioni)
4. **Segmentazione Dinamica** (basata su comportamento utente)

---

## 📞 SUPPORTO

Se hai domande o problemi:
1. Controlla i log in `/wp-content/debug.log`
2. Verifica lo status OneSignal su OneSignal Dashboard
3. Consulta la documentazione qui sopra
4. Leggi i commenti nel codice PHP

---

**Creato da**: Claude Code Meridiana
**Compatibile con**: WordPress 5.x+, ACF Pro 6.x+, OneSignal Free Plan
