# ⚠️ CLAUDE CODE DEVELOPMENT RULES
## NORME FONDAMENTALI - NON VIOLARE

---

## 1. SCOPING - CRITICALE 🔴
- **Una pagina = una modifica**
- Se lavoro su HOMEPAGE, tocco SOLO `_home.scss`
- Se lavoro su ARCHIVI, tocco SOLO `_archive.scss`
- Se lavoro su DOCUMENTAZIONE, tocco SOLO `_docs-page.scss`
- **VIETATO**: Modificare selettori globali che vengono usati in più pagine

---

## 2. CPT (Custom Post Types) - STILI CONDIVISI MA SCOPED
I CPT hanno lo stesso nome ma usati in contesti diversi:
- `.news-item__excerpt` esiste su HOMEPAGE E negli ARCHIVI
- `.archive-item__excerpt` esiste su ARCHIVI

**REGOLA**: Sempre wrappare in parent specifico:
```scss
// HOMEPAGE ONLY
.home-section .news-item__excerpt {
    // modifiche homepage
}

// ARCHIVI ONLY
.archive-page .archive-item__excerpt {
    // modifiche archivi
}
```

**VIETATO**: Modificare `.news-item__excerpt` senza padre (è globale!)

---

## 3. MODIFICHE STRUTTURALI - SENZA INLINE
- ❌ NO inline styles
- ❌ NO hardcoded values
- ✅ USO: CSS variables, media queries, design system coerente

---

## 4. WORKFLOW PER OGNI MODIFICA
1. Leggi TUTTO il screenshot/problema
2. Identifica QUALE pagina ha il problema
3. Apri SOLO il SCSS di quella pagina
4. Controlla se usi selettori globali (usati altrove?)
5. Se sì → wrappa in parent specifico
6. Modifica SOLO quella sezione
7. Build e test
8. Se tocco 3 pagine diverse, mi fermo e chiedo

---

## 5. ELEMENTI CRITICI DEL SITO (MAP)
| Elemento | File CSS | Pagine | Status |
|----------|----------|--------|--------|
| News/Salute Items | _home.scss | Homepage + Archive | ⚠️ SCOPED |
| Archive Items | _archive.scss | Archive pages | ✅ OK |
| Docs Items | _docs-page.scss | Documentazione | ✅ OK |
| Convenzioni Cards | _home.scss | Homepage carousel | ✅ OK |

---

## 6. PAGINA ARCHIVI - ⚠️ AREA CRITICA
**LA PAGINA ARCHIVI VIENE ROTTA FACILMENTE E SPESSO INVOLONTARIAMENTE**

### Cosa accade:
- Commit accidentali che cambiano il template `archive.php` (es: da `.archive-*` a `.docs-*`)
- Modifiche nel gestore dashboard che usano `.archive-item` (conflitto di scoping)
- Cambio di media queries che rompe il layout mobile/desktop
- Modifiche involontarie a `_archive.scss` durante altri lavori

### Come proteggere gli archivi:
1. **TEMPLATE**: `archive.php` DEVE SEMPRE generare classi `.archive-*` (NON `.docs-*` o altri)
2. **GESTORE**: File `_gestore-dashboard.scss` DEVE usare `.file-item` (NON `.archive-item`!)
3. **SCSS**: File `_archive.scss` DEVE restare intoccato salvo bug grafici CONFERMATI
4. **COMMIT**: Se modifichi archivi, TEST TUTTI E 3: Comunicazioni + Convenzioni + Salute e Benessere

### Template corretto per archive.php:
```php
// CORRETTO - Genera .archive-item
<a href="..." class="archive-item">
    <div class="archive-item__image"></div>
    <div class="archive-item__body">
        <div class="archive-item__content">
            <h3 class="archive-item__title">...</h3>
            <p class="archive-item__excerpt">...</p>
        </div>
        <div class="archive-item__meta">...</div>
    </div>
</a>

// SBAGLIATO - Genera .docs-search-container
<div class="docs-search-container">...</div>
```

### CSS in _gestore-dashboard.scss:
```scss
// CORRETTO - Usa .file-item
.file-item {
    display: grid;
    // ...
}

// SBAGLIATO - NON usare .archive-item nel gestore!
.archive-item {
    display: grid;  // ❌ SOVRASCRIVE gli archivi pubblici!
}
```

---

## 7. ERRORI COMUNI - MEMORIA
- ❌ Ho modificato `.news-item__excerpt` globalmente → ha rotto gli archivi
- ❌ Ho toccato `_docs-page.scss` quando dovevo toccare `_home.scss` → 3 pagine rotte
- ❌ Ho usato `.archive-item` in `_gestore-dashboard.scss` → conflitto selettori (ROTTO GLI ARCHIVI 3 VOLTE!)
- ❌ Ho cambiato il template `archive.php` da `.archive-*` a `.docs-*` → niente stile su archivi
- ❌ Ho modificato media queries in `_archive.scss` → layout mobile/desktop rotto

**SEMPRE**: Verificare dove viene usato un selettore prima di modificarlo

---

## 8. BEFORE EVERY CHANGE - CHECKLIST FONDAMENTALE
```
1. ☐ LEGGI: docs/ELEMENTI_GLOBALI_CRITICI.md
2. ☐ L'elemento che tocco è nella lista CRITICA?
3. ☐ In quante pagine viene usato?
4. ☐ Se tocco ARCHIVI: ho controllato archive.php e _gestore-dashboard.scss?
5. ☐ git status (check what files are modified)
6. ☐ git diff FILENAME (see EXACTLY what changed)
7. ☐ Read full SCSS to understand selector scope
8. ☐ Ask: "Where else is this class used?"
9. ☐ If multiple pages: WRAP IN PARENT SPECIFICO
```

**RICORDA**: Prima di toccare QUALSIASI CSS, leggi ELEMENTI_GLOBALI_CRITICI.md!

---

## 9. CHECKLIST FINALE DOPO MODIFICA
- [ ] Ho letto ELEMENTI_GLOBALI_CRITICI.md?
- [ ] Modifica SOLO il file della pagina richiesta?
- [ ] Selettore è scoped a quella pagina?
- [ ] Ho wrappato in parent specifico se necessario?
- [ ] Nessun inline style?
- [ ] CSS variables usate?
- [ ] Build e test completati?
- [ ] Non ho toccato 3 pagine diverse?
- [ ] Ho testato TUTTE le pagine che usano questo elemento?

---

**CREATO**: 28/10/2025
**MOTIVO**: Rotto archivi 3 volte in 2 prompt - perdite di 3 ore di lavoro
**PRIORIDAD**: CRITICA - RISPETTA SEMPRE
