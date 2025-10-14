# ACF JSON Sync Folder

Questa cartella contiene i file JSON auto-generati da ACF Pro per:
- Field Groups
- Custom Post Types
- Taxonomies

## Come Funziona

Quando modifichi qualcosa in ACF Pro UI (Field Groups, CPT, Taxonomies), ACF salva automaticamente un file JSON in questa cartella.

Questo permette di:
- ✅ Versionare le configurazioni ACF su Git
- ✅ Sincronizzare tra ambienti (locale → staging → production)
- ✅ Backup automatico configurazioni
- ✅ Evitare di committare il database

## Workflow

1. **Locale**: Modifichi un Field Group in ACF UI
2. **Auto-Save**: ACF salva `group_xxx.json` in questa cartella
3. **Git Commit**: Committi il file JSON
4. **Production**: Pull del repo, ACF legge il JSON e aggiorna automaticamente

## File Types

- `group_*.json` - ACF Field Groups
- `post-type_*.json` - Custom Post Types
- `taxonomy_*.json` - Taxonomies

## Importante

⚠️ Non modificare manualmente questi file JSON!  
Usa sempre ACF Pro UI per le modifiche.

## Sync Manual (se necessario)

Se su production vedi "Sync available" in ACF:
1. Vai in ACF → Field Groups
2. Clicca tab "Sync"
3. Seleziona i field groups da sincronizzare
4. Clicca "Sync"

Questo importa le modifiche dai file JSON nel database.
