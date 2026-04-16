# Changelog

## [1.0.0] - 2026-04-01

Pierwszy stabilny release core package.

### Funkcjonalnosci
- 14 modeli Eloquent z reuzywalnymi traitami (HasActive, HasFeatured, HasSlug, HasSeo, Publishable, Sortable)
- 10+ Filament Resources (Post, Project, Service, Testimonial, Faq, Page, Lead, MenuItem, PostCategory, NewsletterSubscriber)
- PageSettings system (Home, Contact, Site, Theme) z locale-aware save/load
- ThemeService: HEX-to-OKLCH, CSS variables, 8 base themes, WCAG AA contrast
- CRM: Lead pipeline, activities, reminders, tags, export CSV
- i18n: spatie/laravel-translatable, setting() helper z locale fallback, PluginTranslationRegistry
- SEO: sitemap generator (PL+EN hreflang), HasSeo trait
- Blade components (Atomic Design): Atoms, Molecules, Organisms, Sections
- filament-shield role separation (super_admin + editor)
- GDPR: consent_at na leads

### Infrastruktura
- Filament v5 compatibility
- Laravel 12 support
- PHP 8.4 support
- PHPStan level 10 (0 errors)
- Production Dockerfile (multi-stage, no Xdebug, opcache tuned)

### Usuniete
- Livewire ContactForm i NewsletterForm (zastapione Vue+Inertia)
- Blade blog views (zastapione Inertia pages)
