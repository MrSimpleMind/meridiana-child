# Setup Notifiche OneSignal - Guida Completa

## 📋 Panoramica

Il sistema è **100% gestibile da ACF, tutto in una pagina**:
- ✅ **Options Pages** - Crei tu manualmente in ACF Pro
- ✅ **Field Groups** - Sincronizzati da JSON ACF (completamente editabili)
- ✅ **Segmentazioni** - Repeater field nella pagina Configurazione Notifiche
- ✅ **Core System** - PHP che legge tutto da ACF (zero hardcoding)

### Architettura

```
Notifiche (Options Page)
├─ OneSignal Setup (Sub-page)
│  └─ Field Group: Credenziali OneSignal
│
└─ Configurazione Notifiche (Main page)
   ├─ Field Group: Segmentazioni (Repeater)
   └─ Field Group: Trigger Notifiche (Repeater)

acf-json/ (Sincronizzati automaticamente)
├─ group_notification_onesignal_setup.json
├─ group_notification_segmentazioni.json
└─ group_notification_triggers.json

includes/ (Core system)
├─ notification-system.php
└─ notification-frontend.php
```

---

## 🔄 Step 1: Creare le Options Pages in ACF Pro

Hai già creato:
- ✅ **Configurazione Notifiche** (menu slug: `configurazione-notifiche`)
- ✅ **Configurazione OneSignal** (menu slug: `configurazione-onesignal`, sub-page)

Se non le hai ancora create, vai a **ACF Pro → Pagine opzioni** e crea come spiegato precedentemente.

---

## 🔄 Step 2: Sincronizzare i Field Groups da ACF

1. Vai a **ACF Pro → Tools**
2. Dovresti vedere 3 field groups da sincronizzare:
   - `group_notification_onesignal_setup`
   - `group_notification_segmentazioni`
   - `group_notification_triggers`
3. Importa tutti e 3

**Risultato:**
- Field Group OneSignal Setup → collegato a `configurazione-onesignal`
- Field Group Segmentazioni → collegato a `configurazione-notifiche`
- Field Group Trigger Notifiche → collegato a `configurazione-notifiche`

---

## 🚀 Step 3: Usare il Sistema

### 1️⃣ Configura OneSignal

1. Vai a **Notifiche → OneSignal Setup**
2. Incolla **App ID** da OneSignal
3. Incolla **REST API Key** da OneSignal
4. Clicca **Salva**

### 2️⃣ Crea Segmentazioni

1. Vai a **Notifiche → Configurazione Notifiche**
2. Scorri a **"Segmentazioni"** (repeater)
3. Clicca **"Aggiungi Segmentazione"**
4. Compila:
   - **Titolo Segmentazione**: Es: "Tutti i Subscriber"
   - **Tipo Regola**: Seleziona il criterio
   - (Campi aggiuntivi appaiono in base alla scelta)
5. Clicca **Aggiungi Segmentazione** per altre

### 3️⃣ Crea Trigger

1. Stessa pagina **Configurazione Notifiche**
2. Scorri a **"Trigger Notifiche"** (repeater)
3. Clicca **"Aggiungi Trigger"**
4. Compila:
   - **Trigger ID**: Es: `trigger_new_protocol`
   - **Tipo Post**: Es: `protocollo`
   - **Abilitato**: ✅
   - **Titolo Template**: Es: `📄 Nuovo {{post_type}}: {{title}}`
   - **Messaggio Template**: Es: `Pubblicato da {{author}}`
   - **Emoji/Icon**: Es: `📄`
   - **Regola Segmentazione**: Digita il **titolo esatto** della segmentazione (Es: "Tutti i Subscriber")

### 4️⃣ Testa

1. Vai a **Protocolli → Aggiungi Nuovo Protocollo**
2. Compila il form e **Pubblica**
3. Verifica che la notifica appaia sulla PWA

---

## 📚 Campi Disponibili

### OneSignal Setup
- **App ID** (text)
- **REST API Key** (password)

### Segmentazioni (Repeater in Configurazione Notifiche)
- **Titolo Segmentazione** (text) - Identificativo univoco
- **Tipo Regola** (select):
  - Tutti i Subscriber
  - Per Profilo Professionale
  - Per UDO
  - Per Stato Utente
  - Per Profilo + UDO
  - Query Custom
- **Profilo Professionale** (taxonomy) - Opzionale
- **UDO** (taxonomy) - Opzionale
- **Stato Utente** (select: attivo/sospeso/licenziato) - Opzionale
- **Classe Query Custom** (text) - Opzionale
- **Descrizione** (textarea)

### Trigger Notifiche (Repeater in Configurazione Notifiche)
- **Trigger ID** (text) - Identificatore univoco
- **Tipo Post** (select) - Quale post type attiva
- **Abilitato** (true/false)
- **Titolo Template** (textarea)
- **Messaggio Template** (textarea)
- **Emoji/Icon** (text)
- **Regola Segmentazione** (text) - Digita il titolo della segmentazione

---

## 📝 Template Placeholders

```
{{post_type}}   → Tipo di post singolare (es: "Protocollo")
{{title}}       → Titolo del post
{{author}}      → Nome dell'autore
{{date}}        → Data (dd/mm/yyyy)
{{excerpt}}     → Prime 20 parole
```

---

## 🎯 Tipi di Segmentazione

- **Tutti i Subscriber** - Invia a tutti gli utenti
- **Per Profilo Professionale** - Filtra per profilo
- **Per UDO** - Filtra per unità offerta
- **Per Stato Utente** - Filtra per stato
- **Per Profilo + UDO** - Combinazione di profilo E UDO
- **Query Custom** - Query PHP personalizzata

---

## 🔐 Sicurezza

- ✅ REST API Key crittografata (password field)
- ✅ Solo admin può creare trigger/segmentazioni
- ✅ OneSignal SDK solo per utenti loggati

---

## 📖 Come Funziona Internamente

1. Quando pubblichi un Protocollo/Modulo
2. WordPress attiva l'hook `publish_protocollo` / `publish_modulo`
3. `notification-system.php` legge i trigger configurati
4. Trova il trigger corrispondente
5. Legge il titolo della segmentazione dal trigger
6. Cerca nel repeater `notification_segmentazioni` quella segmentazione
7. Applica la logica di filtraggio (all_subscribers, by_profilo, ecc.)
8. Ottiene la lista di user IDs
9. Invia via OneSignal API
10. La notifica appare sulla PWA degli utenti

---

## 🐛 Troubleshooting

**Notifiche non arrivano?**
- Verifica OneSignal Setup sia compilato
- Verifica trigger sia "Abilitato"
- Verifica il titolo della segmentazione sia scritto **esattamente uguale**
- Controlla che la segmentazione abbia utenti

**Field Groups non appaiono?**
- Sincronizza ACF di nuovo
- Svuota il cache

**Segmentazione non filtra correttamente?**
- Verifica che gli utenti abbiano i meta_key corretti:
  - `profilo_professionale`
  - `udo_riferimento`
  - `stato_utente`
