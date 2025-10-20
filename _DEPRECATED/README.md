🗑️ DEPRECATED - File Obsoleti

Questi file sono stati rimossi dalla directory principale perché:

## page-archivio-convenzioni.php
- Duplicato di `archive-convenzione.php`
- WordPress legge automaticamente i template `archive-{post_type}.php`
- Le page manuali non servono più

## page-archivio-salute.php
- Duplicato di `archive-salute-e-benessere-l.php`
- Stesso motivo di sopra

## compile-scss-php.php
- Build system vecchio (PHP compiler)
- Sostituito da webpack + npm scripts
- `npm run build:scss` usa il sistema nuovo

## comunicazioni-inline.css
- CSS inline enqueued da functions.php
- Contenuto ora integrato in main.css via SCSS
- Rimosso dal build per evitare duplicazioni

## avatar-verification.php
- Include file non usato
- Non è richiesto in functions.php
- Funzionalità avatar gestita da altri file

---
Se in futuro serve recuperare questi file, sono qui conservati.
Ultimo aggiornamento: 20 Ottobre 2025
