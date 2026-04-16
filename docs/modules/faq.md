# Module: FAQ

**Feature flag:** `webfloo.features.faq` (default `true`)
**Scope:** FAQ z kategoriami (hardcoded enum), ikoną, is_active/sort_order.

## Public API

### Resources
- `Webfloo\Filament\Resources\FaqResource`

### Models
- `Webfloo\Models\Faq` — question, answer, category, icon, is_active, sort_order

### Traits applied
- `HasActive`, `Sortable`, `HasTranslations`

## Migrations

- `*_create_faqs_table`
- `*_add_icon_to_faqs_table`

## Shield permissions

```
view_any_faq   view_faq   create_faq   update_faq   delete_faq
```

## Host integration

`Webfloo\Components\Sections\Faq` Blade section renderuje accordion. `:items` prop = `Faq::active()->ordered()->get()->toArray()`.

## Feature flag scenarios

- `features.faq = false` — Resource niewidoczny, section component renders empty.

## Limitations

- Categories hardcoded enum w Faq model (`getCategoryOptions()`). Follow-up: configurable via `config/webfloo.php` albo osobny FaqCategory model (wzorowane na PostCategory).
