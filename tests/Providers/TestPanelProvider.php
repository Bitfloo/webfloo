<?php

declare(strict_types=1);

namespace Webfloo\Tests\Providers;

use Filament\Panel;
use Filament\PanelProvider;
use Webfloo\Filament\WebflooPanel;

/**
 * Minimal Filament panel provider for package tests.
 * Registered via TestCase::getPackageProviders() so the panel (and its routes)
 * are registered during app boot — before routes/web.php is loaded.
 */
final class TestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->authGuard('web')
            ->plugins([WebflooPanel::make()]);
    }
}
