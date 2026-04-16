# bitfloo/webfloo — lang

Package-level JSON translations loaded via Laravel's native
`loadJsonTranslationsFrom()`. Default locale = Polish (native keys).
Other locales provide mappings from the Polish key.

## Usage

```php
__('Edytuj')         // PL default (locale=pl) — returns 'Edytuj'
__('Edytuj')         // EN (locale=en) — returns value under key "Edytuj" in en.json
```

## Files

- `pl.json` — PL is the source-of-truth locale. Keys are PL strings. Typically empty (keys self-identify).
- `en.json` — EN translations. Keys match PL strings, values are EN equivalents.
- Add more locales as new files (e.g. `de.json`).

## Host override

Host projects can publish these files:

```bash
php artisan vendor:publish --tag=bitfloo-lang
```

Published to `lang/vendor/bitfloo/{locale}.json`. Laravel merges automatically — host values override package values per key.

## Migration plan

Phase 1.5h (current): infra only — empty files, loader wired up.
Phase 1.5i: migrate Filament Resource / PageSettings labels (first wave).
Phase 1.5j: migrate forms/tables column labels (second wave, top-3 resources).
