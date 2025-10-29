# ⚡ Quick Start - Notifiche Push (30 minuti)

**Prerequisito**: ACF sincronizzato ✅

---

## 🔑 STEP 2: Configura OneSignal (10 minuti)

### 2.1 Crea account (se non lo hai)
```
https://onesignal.com → Sign Up
```

### 2.2 Crea progetto
```
Dashboard → "Create new project"
Nome: "Meridiana Formazione"
Type: "Web"
→ Create project
```

### 2.3 Copia credenziali
```
Settings → App Settings → "Keys & IDs"

Copia:
- App ID
- REST API Key
```

### 2.4 Incolla in WordPress
```
Dashboard → Notifiche → OneSignal Setup

App ID: [INCOLLA]
REST API Key: [INCOLLA]

Salva Credenziali
```

**Dovresti vedere**: ✅ OneSignal è configurato correttamente

---

## ⚙️ STEP 3: Crea Segmentazione (5 minuti)

```
Dashboard → Segmentazioni Notifiche → Aggiungi

Titolo: Tutti i Subscriber
Tipo Regola: [Tutti i Subscriber ▼]
Salva
```

---

## 📢 STEP 4: Crea 3 Trigger (10 minuti)

### Trigger 1: Protocolli
```
Dashboard → Notifiche → Sezione "Trigger Notifiche" → Aggiungi

ID:              trigger_new_protocol
Post Type:       [Protocollo ▼]
Abilitato:       [✓]
Titolo:          Nuovo {{post_type}}: {{title}}
Messaggio:       Scarica il documento {{title}}
Emoji:           📄
Segmentazione:   [Tutti i Subscriber ▼]

Salva
```

### Trigger 2: Moduli
```
ID:              trigger_new_modulo
Post Type:       [Modulo ▼]
Titolo:          Nuovo {{post_type}}: {{title}}
Messaggio:       Compila il modulo {{title}}
Emoji:           📋
Segmentazione:   [Tutti i Subscriber ▼]

Salva
```

### Trigger 3: Comunicazioni
```
ID:              trigger_new_comunicazione
Post Type:       [Comunicazione ▼]
Titolo:          {{title}}
Messaggio:       Leggi la comunicazione importante
Emoji:           📢
Segmentazione:   [Tutti i Subscriber ▼]

Salva
```

---

## ✅ STEP 5: Test (5 minuti)

### Test 1: Notifica di test
```
Dashboard → Notifiche

Sezione "Testa Trigger":
  Seleziona: [Protocollo ▼]
  Clicca: [Invia Notifica Test]

Dovresti ricevere notifica nel browser ✅
```

### Test 2: Pubblica contenuto reale
```
Dashboard → Protocolli → Aggiungi nuovo

Titolo: "Test Notifica"
PDF: [carica file]
Pubblica

Verifica:
- OneSignal Dashboard → Messages (vedi notifica inviata)
- Browser notifiche (dovresti ricevere push)
```

---

## 🎯 Come Funziona per gli Users

### Quando User accede al sito:
```
1. OneSignal SDK si carica automaticamente
2. OneSignal chiede: "Vuoi ricevere notifiche?"
3. User clicca "Consenti"
4. OneSignal lo registra
5. User riceve notifiche push sui documenti
```

**Non serve fare niente**, è automatico! ✅

---

## 📱 Dove vedranno le notifiche?

| Dispositivo | Dove vede |
|-------------|-----------|
| Computer | Notifica sistema operativo (Windows/Mac) |
| Cellulare | Notifica push del browser |
| PWA | Notifica sistema (anche offline) |

---

## 🐛 Se non funziona?

```
❌ "Non ricevo notifiche"

1. OneSignal configurato?
   Dashboard → Notifiche → OneSignal Setup
   Check: ✅ Configurato correttamente

2. Trigger abilitato?
   Dashboard → Notifiche
   Check: [✓] Abilitato

3. Hai dato il permesso?
   Browser chiede: "Vuoi notifiche?"
   Devi cliccare: "Consenti"

4. Controlla log:
   /wp-content/debug.log
   Cerca: "[Meridiana Notifications]"
```

---

## ✨ PROSSIMI STEP (Opzionali)

1. **Segmentazione Avanzata** - Per profilo, UDO, stato
2. **Bell Icon Integrata** - Mostrare notifiche non-lette
3. **PWA Service Worker** - Notifiche offline
4. **Analytics Custom** - Traccia click

---

**Fatto tutto?** Quando pubblichi un Protocollo/Modulo/Comunicazione, gli utenti riceveranno notifiche automaticamente! 🎉

Vedi il file completo: `NOTIFICATIONS_SETUP.md`
