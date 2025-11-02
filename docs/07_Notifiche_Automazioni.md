# 🔔 Notifiche e Automazioni

> **Ultimo aggiornamento**: 1 Novembre 2025
> **Fonte**: `includes/notification-system.php`, `includes/notification-frontend.php`, `acf-json/`

**Leggi anche**:
- `02_Struttura_Dati_CPT.md` per la configurazione dei trigger e segmenti via ACF
- `03_Sistema_Utenti_Roles.md` per la segmentazione basata sui ruoli e meta-dati utente

---

## 🎯 Architettura del Sistema di Notifiche

Il sistema di notifiche è progettato per essere **interamente configurabile dal backend di WordPress** attraverso **ACF (Advanced Custom Fields)**, eliminando la necessità di modificare il codice per aggiungere o cambiare le regole di notifica.

### Canali

1.  **Push Notifications (OneSignal)**: Per comunicazioni immediate e in tempo reale tramite la PWA.
2.  **Email (Brevo)**: Previsto per future implementazioni (es. digest settimanali, notifiche critiche).

### Componenti Chiave

- **ACF Options Pages**: Una sezione dedicata nel backend ("Notifiche") permette di configurare le credenziali di OneSignal, le regole di segmentazione e i trigger.
- **Classe `MeridianaNotificationSystem`**: Il cuore del sistema, situato in `includes/notification-system.php`. Questa classe legge la configurazione da ACF, si aggancia agli hook di WordPress e invia le notifiche.
- **Integrazione Frontend**: Il file `includes/notification-frontend.php` si occupa di caricare lo SDK di OneSignal e di registrare gli utenti per le notifiche push.

---

## 📱 Push Notifications (OneSignal)

Il sistema è event-driven e si basa su tre concetti principali, tutti gestiti come campi ACF.

### 1. Configurazione (ACF)

**File**: `acf-json/group_notification_onesignal_setup.json`

Nella pagina di opzioni "Notifiche > OneSignal Setup", l'amministratore inserisce:
- **App ID**: L'ID dell'applicazione OneSignal.
- **REST API Key**: La chiave API per l'invio delle notifiche.

Questi valori vengono salvati nelle opzioni di WordPress e utilizzati dalla classe `MeridianaNotificationSystem` per autenticarsi con OneSignal.

### 2. Segmentazione (ACF)

**File**: `acf-json/group_notification_segmentazioni.json`

Nella pagina "Configurazione Notifiche", un campo **Repeater** permette di creare un numero illimitato di **segmenti di pubblico**. Ogni segmento è una regola che definisce un gruppo di utenti target. Tipi di regole supportate:

- **Tutti i Subscriber**: Invia a tutti gli utenti attivi.
- **Per Profilo Professionale**: Filtra gli utenti in base alla tassonomia `profilo-professionale`.
- **Per UDO (Unità di Offerta)**: Filtra gli utenti in base alla tassonomia `unita-offerta`.
- **Per Stato Utente**: Filtra per `stato_utente` (attivo, sospeso, licenziato).
- **Per Profilo + UDO**: Combina due criteri in AND.
- **Query Custom**: Permette di specificare una classe PHP custom per logiche di segmentazione complesse.

### 3. Trigger (ACF)

**File**: `acf-json/group_notification_triggers.json`

Sempre nella stessa pagina, un altro campo **Repeater** definisce i **trigger**, ovvero gli eventi che scatenano l'invio di una notifica.

Ogni trigger è composto da:
- **Trigger ID**: Un identificatore univoco (es. `nuovo_protocollo`).
- **Tipo di Post**: Il CPT che attiva il trigger (es. `protocollo`).
- **Abilitato**: Un toggle per attivare/disattivare il trigger.
- **Template Titolo e Messaggio**: Campi di testo che supportano placeholder come `{{title}}`, `{{post_type}}`, `{{author}}` per personalizzare il messaggio.
- **Regola di Segmentazione**: Un campo di testo dove si inserisce il **titolo esatto** di un segmento creato in precedenza.

### Flusso di Esecuzione

1.  Al caricamento di WordPress, la classe `MeridianaNotificationSystem` legge tutti i trigger abilitati da ACF.
2.  Per ogni trigger, aggancia una funzione all'hook corrispondente (es. `publish_protocollo`).
3.  Quando un Gestore pubblica un nuovo protocollo, l'hook scatta.
4.  La funzione recupera la configurazione del trigger, inclusa la regola di segmentazione associata.
5.  Chiama la funzione `get_segmented_users()`, che interroga il database per ottenere la lista degli ID utente che soddisfano i criteri del segmento.
6.  Prepara il payload della notifica, parsando i template del titolo e del messaggio con i dati del post appena pubblicato.
7.  Invia la notifica a OneSignal tramite API, specificando gli ID degli utenti target (`include_external_user_ids`).

Questo design rende il sistema estremamente flessibile: per creare una nuova notifica (es. per un nuovo CPT "Circolare"), è sufficiente configurare un nuovo trigger e un nuovo segmento nel backend, **senza scrivere una sola riga di codice**.

---

## 🎓 Automazioni dei Corsi (LearnDash)

Le automazioni legate a LearnDash sono gestite tramite cron job di WordPress e funzioni specifiche del plugin.

### Auto-Enrollment per Nuovi Utenti

- **Trigger**: `user_register` hook.
- **Azione**: La funzione `autoenroll_corsi_obbligatori()` (in `includes/user-roles.php`) viene eseguita.
- **Logica**: Cerca tutti i corsi con una tassonomia custom `tipologia_corso` impostata su "Obbligatori Interni" e iscrive il nuovo utente a ciascuno di essi tramite la funzione `ld_update_course_access()`.

### Alert per Scadenza Certificati

- **Trigger**: Un cron job giornaliero (`wp_schedule_event`) che esegue la funzione `send_certificati_alerts()`.
- **Logica**:
  1. Itera su tutti gli utenti della piattaforma.
  2. Per ogni utente, recupera i corsi completati e la data di completamento.
  3. Calcola la data di scadenza (assunta a 1 anno).
  4. Se mancano 7 giorni o meno, invia una notifica push e un'email all'utente.
  5. Se il certificato è già scaduto, re-iscrive automaticamente l'utente al corso e invia una notifica di "Azione Richiesta".

---

## 🤖 Checklist per Sviluppo

- **Non Hard-codare**: Per le notifiche push, evitare di creare trigger manuali nel codice. Utilizzare sempre il sistema di configurazione ACF.
- **Performance dei Segmenti**: Le query per la segmentazione degli utenti possono essere pesanti. Assicurarsi che i meta-dati utente (`profilo_professionale`, `udo_riferimento`) siano indicizzati.
- **Testare i Trigger**: Dopo aver configurato un nuovo trigger in ACF, testarlo pubblicando un contenuto del tipo corrispondente e verificando che la notifica venga inviata al segmento corretto.
- **Gestione Cron Job**: Utilizzare un plugin come "WP Crontrol" per monitorare e fare il debug dei cron job schedulati (`send_weekly_digest`, `check_certificati_scadenza`).
- **API Rate Limits**: Tenere conto dei limiti dei piani gratuiti di OneSignal e Brevo, specialmente per le notifiche di massa.