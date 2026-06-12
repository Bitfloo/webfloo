## Webfloo (bitfloo/webfloo)

- Webfloo is a reusable Composer package providing Blade components (Atomic Design), Filament v5 admin resources, models, and a settings system. It must work for any client project — never hardcode client-specific content into it.
- Views use the `webfloo::` prefix. Blade components register as flat `<x-webfloo-name />` tags (no colons or dots in the component name).
- Site content comes from the `setting('key')` helper backed by the `Setting` model — use it instead of hardcoded strings, with generic defaults.
- Admin features are gated by `config('webfloo.features.*')` flags and spatie/laravel-permission abilities (e.g. `view_any_faq`). Check both before assuming a resource is visible.

### Blade component props

Props are camelCase in the PHP component class and kebab-case in Blade usage. Passing the wrong shape (array to a string prop, or an undeclared prop name) lands in `$attributes` and causes `trim(): array given` errors.

@verbatim
<code-snippet name="Webfloo component prop formats" lang="blade">
{{-- string props --}}
<x-webfloo-hero title="Welcome" cta-text="Start" cta-href="/" />

{{-- array props need the : prefix and exact names (e.g. :items, not :faqs) --}}
<x-webfloo-faq :items="[['question' => '...', 'answer' => '...']]" />
<x-webfloo-cta title="Ready?" :primary-cta="['text' => 'Contact', 'href' => '#contact']" />
</code-snippet>
@endverbatim

Before using any `<x-webfloo-*>` component, read its PHP class in `vendor/bitfloo/webfloo/src/Components/` for exact prop names and types.

### Models and migrations

- Webfloo models use `$fillable`, `$casts`, `@property` PHPDoc, and the scopes `scopeActive()` / `scopeOrdered()` (standard columns: `is_active` default true, `sort_order` default 0).
- Translatable fields use spatie/laravel-translatable v6 (JSON columns, `pl` + `en` locales).
- All migrations include a working `down()` method.

### Filament admin

- Resources live in `Webfloo\Filament\Resources`; per-page settings pages extend `AbstractPageSettings` (locale-aware `getSetting('prefix.key')`, `nonTranslatableKeys()` for files/booleans/numbers).
- Follow Filament v5 API exactly — unified `form(Schema $schema): Schema`, layout components from `Filament\Schemas\Components`, actions from `Filament\Actions`, `Heroicon` enum navigation icons. The package source is the authoritative reference: `vendor/bitfloo/webfloo/src/Filament/`.
- After adding resources or policies in the host app, regenerate permissions: `php artisan shield:generate --all --panel=admin`.
