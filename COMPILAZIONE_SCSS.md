# 🔧 GUIDA COMPILAZIONE SCSS

## ⚠️ PROBLEMA ATTUALE

Gli SCSS non sono stati compilati, quindi è stato creato un **CSS hotfix temporaneo** (`assets/css/hotfix-home.css`) che viene caricato automaticamente se `main.min.css` non esiste.

---

## ✅ SOLUZIONE: Compilare gli SCSS

### Metodo 1: NPM (Consigliato)

```bash
cd "C:\Users\utente\Local Sites\nuova-formazione\app\public\wp-content\themes\meridiana-child"

# Compilazione singola (production)
npm run build:scss

# Watch mode per sviluppo (auto-compile al salvataggio)
npm run watch:scss

# Build completo (CSS + JS)
npm run build
```

### Metodo 2: Manuale con Sass CLI

Se npm non funziona, compila manualmente:

```bash
cd "C:\Users\utente\Local Sites\nuova-formazione\app\public\wp-content\themes\meridiana-child"

# Windows PowerShell
.\node_modules\.bin\sass assets/css/src/main.scss assets/css/dist/main.min.css --style=compressed --no-source-map

# Git Bash / Linux / Mac
./node_modules/.bin/sass assets/css/src/main.scss assets/css/dist/main.min.css --style=compressed --no-source-map
```

### Metodo 3: Sass Watch Manuale

```bash
.\node_modules\.bin\sass --watch assets/css/src:assets/css/dist --style=expanded --source-map
```

---

## 📋 Checklist Post-Compilazione

Dopo aver compilato con successo:

1. ✅ Verifica che esista il file: `assets/css/dist/main.min.css`
2. ✅ Ricarica la pagina nel browser (Ctrl+F5 per forzare refresh cache)
3. ✅ Elimina `assets/css/hotfix-home.css` (opzionale, viene ignorato automaticamente)
4. ✅ Verifica che la bottom nav sia orizzontale
5. ✅ Verifica che i link "Vedi tutto" funzionino

---

## 🐛 Troubleshooting

### Errore: "sass: command not found"

Reinstalla le dipendenze:

```bash
npm install
```

### Errore: Permessi negati

Esegui PowerShell come Amministratore.

### Errore: Node/NPM non trovato

1. Scarica Node.js da: https://nodejs.org/
2. Installa versione LTS
3. Riavvia terminale
4. Riprova

### CSS non si aggiorna nel browser

- Svuota cache browser (Ctrl+Shift+Delete)
- Hard refresh (Ctrl+F5)
- Disabilita cache in DevTools (F12 → Network tab → "Disable cache")

---

## 📁 Struttura File CSS

```
assets/css/
├── src/                      # File SCSS sorgente (modifica questi)
│   ├── main.scss            # Entry point
│   ├── _variables.scss
│   ├── _mixins.scss
│   ├── base/
│   ├── components/
│   ├── layout/
│   │   └── _navigation.scss  # Bottom nav styles
│   └── pages/
│       └── _home.scss        # Home page styles
│
├── dist/                     # File CSS compilati (generati automaticamente)
│   └── main.min.css         # CSS finale minificato
│
└── hotfix-home.css          # TEMPORANEO - Eliminare dopo compilazione SCSS
```

---

## 🎯 Prossimi Step

1. **Ora**: Compila SCSS con uno dei metodi sopra
2. **Poi**: Testa la home su mobile reale
3. **Infine**: Crea le pagine archivio per i link "Vedi tutto"

---

## 📞 Note Aggiuntive

- Il file `hotfix-home.css` è identico agli stili in `_home.scss` + `_navigation.scss`
- Una volta compilato correttamente main.min.css, hotfix-home.css viene ignorato
- Gli stili del hotfix sono già ottimizzati per mobile-first

---

**Data creazione**: 15 Ottobre 2025
**Status**: Hotfix attivo - Compilazione SCSS necessaria
