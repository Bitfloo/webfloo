<?php

declare(strict_types=1);

namespace Webfloo\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;

/**
 * Webfloo Filament plugin — wstrzykuje wszystkie Resources, Pages i Widgets
 * do host panelu Filament.
 *
 * Host integration:
 *
 *   // app/Providers/Filament/AdminPanelProvider.php
 *   use Webfloo\Filament\WebflooPanel;
 *
 *   public function panel(Panel $panel): Panel
 *   {
 *       return $panel
 *           ->id('admin')
 *           ->path('admin')
 *           // ... host's own config ...
 *           ->plugins([
 *               WebflooPanel::make(),
 *           ]);
 *   }
 *
 * Plugin auto-discovery ładuje:
 * - src/Filament/Resources/*      (Post, PostCategory, Page, Project, Service,
 *                                  Testimonial, Faq, MenuItem, NewsletterSubscriber,
 *                                  Lead, LeadTag — 11 Resources)
 * - src/Filament/Pages/*          (SiteSettings, ThemeSettings, CrmDashboard +
 *                                  PageSettings/HomePageSettings + ContactPageSettings)
 * - src/Filament/Widgets/*        (LeadStatsOverview + 3 Charts + UpcomingReminders)
 *
 * Każdy Resource / Page ma własne `canAccess()` gate oparte na Shield
 * permissions + `webfloo.features.*` flag — host kontroluje widoczność
 * przez config.
 *
 * Module grouping: patrz `config/webfloo-modules.php` rejestr — każdy
 * Resource jest logicznie przypisany do modułu (blog / crm / newsletter /
 * itd.) z enabled flagą. Flag off = canAccess() zwraca false → Resource
 * niewidoczny w panelu. Rejestr używany też przez
 * `Webfloo\Support\ModuleRegistry` do conditional bindings commands/routes
 * w `WebflooServiceProvider`.
 */
final class WebflooPanel implements Plugin
{
    public static function make(): static
    {
        return app(self::class);
    }

    public function getId(): string
    {
        return 'webfloo';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->discoverResources(
                in: __DIR__.'/Resources',
                for: 'Webfloo\\Filament\\Resources',
            )
            ->discoverPages(
                in: __DIR__.'/Pages',
                for: 'Webfloo\\Filament\\Pages',
            )
            ->discoverWidgets(
                in: __DIR__.'/Widgets',
                for: 'Webfloo\\Filament\\Widgets',
            );
    }

    public function boot(Panel $panel): void
    {
        // No-op — wszystkie wiring zrobione w register().
        // Hook na boot() zostawiony dla przyszłych potrzeb (np. dynamic config
        // readów które muszą wykonać się po container boot).
    }
}
