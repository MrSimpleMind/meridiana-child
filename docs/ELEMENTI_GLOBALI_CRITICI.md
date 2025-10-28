# 🔴 ELEMENTI CSS GLOBALI CRITICI
## Classi condivise tra più pagine - ATTENZIONE AL SCOPING

---

## CATEGORIA 1: NEWS/SALUTE ITEMS (⚠️ USATI OVUNQUE)
**Sono gli elementi che hai rotto 3 volte**

- `.news-item` + `.news-item__excerpt`, `.news-item__title`, `.news-item__arrow`
  - Usato in: **Homepage** + **Archive Comunicazioni**
  - Danger: Se modifichi senza scoping, rovina ENTRAMBI
  - Solution: Wrap in `.home-section .news-item__excerpt` o `.archive-page .news-item__excerpt`

- `.salute-item` + `.salute-item__excerpt`, `.salute-item__title`, `.salute-item__arrow`
  - Usato in: **Homepage** + **Archive Salute/Benessere**
  - Danger: Se modifichi senza scoping, rovina ENTRAMBI
  - Solution: Wrap in `.home-section .salute-item__excerpt` o `.archive-page .salute-item__excerpt`

---

## CATEGORIA 2: ARCHIVE ITEMS (⚠️ USATI IN 3 ARCHIVI)

- `.archive-item` + `.archive-item__image`, `.archive-item__excerpt`, `.archive-item__title`, `.archive-item__meta`
  - Usato in: **Archive Comunicazioni** + **Archive Convenzioni** + **Archive Salute**
  - Danger: Stessa classe usata in 3 archivi diversi
  - Solution: Se tocchi, tocca TUTTI e 3

---

## CATEGORIA 3: DOCUMENTAZIONE/GESTORE (⚠️ CONDIVISE)

- `.docs-search-container`, `.docs-search-input`, `.docs-search-clear`
  - Usato in: **Documentazione page** + **Gestore Dashboard** (comunicazioni, convenzioni, salute, documenti)
  - Danger: 5 sezioni diverse usano gli stessi selettori

- `.docs-filter-toggle`, `.docs-filters-panel`, `.docs-type-btn`
  - Usato in: **Documentazione page** + **Gestore Dashboard**
  - Danger: Se modifichi, cambiano in TUTTI i gestore

- `.docs-filter-select`
  - Usato in: **Documentazione** + **Gestore** + **Contatti**
  - Danger: 3 sezioni diverse

- `.filter-group`, `.filter-group__label`
  - Usato in: **Documentazione** + **Gestore** + **Contatti**
  - Danger: 3 sezioni diverse usano gli stessi selettori

---

## CATEGORIA 4: CARDS CAROUSEL (⚠️ HOMEPAGE EXCLUSIVE)

- `.convenzione-card` + `.convenzione-card__image`, `.convenzione-card__content`, `.convenzione-card__title`
  - Usato in: **Homepage** (Carousel Convenzioni)
  - Danger: Bassa (è solo homepage)
  - Scoping: Semplice

- `.carousel-control` + `.carousel-control--prev`, `.carousel-control--next`
  - Usato in: **Homepage** (Carousel)
  - Danger: Bassa
  - Scoping: Solo homepage

---

## CATEGORIA 5: COMUNICAZIONI (⚠️ DIFFUSE)

- `.comunicazione-card` + `.comunicazione-card__image`, `.comunicazione-card__title`, `.comunicazione-card__excerpt`
  - Usato in: **Archive Comunicazioni** + **Gestore Comunicazioni** + **Homepage**
  - Danger: 3 sezioni diverse, stessi selettori

---

## CATEGORIA 6: BACK BUTTON (⚠️ UNIVERSALE)

- `.back-link`
  - Usato in: **Tutte le pagine single** (comunicazioni, convenzioni, salute, generiche)
  - Danger: Se modifichi, cambiano in TUTTE
  - Scoping: Difficile, è davvero globale

---

## CATEGORIA 7: STATI VUOTI (✅ SAFE)

- `.no-content`, `.no-results`
  - Usato in: Homepage + Archive + Gestore + Documentazione
  - Danger: Bassa (sono generici)
  - Scoping: Se necessario, wrappare nel padre

---

## CATEGORIA 8: BADGE (✅ GLOBALE MA DESIGN SYSTEM)

- `.badge` + `.badge-primary`, `.badge-success`, `.badge-warning`, `.badge-error`, `.badge-info`
  - Usato in: **OVUNQUE** (design system)
  - Danger: Non toccare mai
  - Scoping: N/A - è parte del design system

---

## CATEGORIA 9: ITEM CARD GESTORE (⚠️ 4 SEZIONI)

- `.item-card` + `.item-card__header`, `.item-card__info`, `.item-card__title`, `.item-card__content`
  - Usato in: **Gestore Comunicazioni** + **Gestore Convenzioni** + **Gestore Salute** + **Gestore Documenti**
  - Danger: 4 sezioni diverse, stessi selettori
  - Scoping: Wrappare in sezione specifica (.gestore-comunicazioni .item-card ecc.)

---

## RIEPILOGO PERICOLOSITÀ

### 🔴 CRITICO (Non toccare senza capire le conseguenze)
- `.news-item` e derivate (2 pagine)
- `.salute-item` e derivate (2 pagine)
- `.archive-item` e derivate (3 archivi)
- `.docs-filter-*` e `.docs-search-*` (5 sezioni)
- `.filter-group` (3 sezioni)
- `.back-link` (tutte le single)
- `.comunicazione-card` (3 sezioni)

### 🟡 ATTENZIONE (Verificare scoping)
- `.convenzione-card` (solo homepage ma ampio)
- `.item-card` (4 sezioni gestore)
- `.no-results`, `.no-content` (varie sezioni)

### 🟢 SAFE
- `.badge` (design system, è fatto apposta per essere globale)
- `.carousel-control` (solo homepage)

---

## CHECKLIST PRIMA DI MODIFICARE

Se devi modificare un elemento dall'elenco sopra:

1. ☐ Qual è la pagina che devo modificare?
2. ☐ L'elemento è nella lista CRITICA?
3. ☐ In quante pagine viene usato?
4. ☐ Se in più di 1 pagina → WRAPPARE IN PARENT SPECIFICO
5. ☐ Verificare visivamente TUTTE le pagine dopo modifica

---

**CREATO**: 28/10/2025
**MOTIVO**: 3 ore perse per aver modificato `.news-item__excerpt` globalmente
**STATUS**: Documento fondamentale - LEGGERE PRIMA DI OGNI MODIFICA CSS
