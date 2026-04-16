<?php

namespace Webfloo;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Webfloo\Services\PluginTranslationRegistry;
use Webfloo\Services\ThemeService;

class WebflooServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/webfloo.php', 'webfloo');

        // Register ThemeService as singleton for SSOT theme management
        $this->app->singleton(ThemeService::class, fn () => new ThemeService);

        // Register PluginTranslationRegistry for plugin i18n namespace support
        $this->app->singleton(PluginTranslationRegistry::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'webfloo');
        $this->loadJsonTranslationsFrom(__DIR__.'/../lang');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->registerBladeComponents();
        $this->registerRoutes();

        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\SendLeadReminders::class,
                Console\Commands\GenerateSitemap::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/webfloo.php' => config_path('webfloo.php'),
            ], 'webfloo-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/webfloo'),
            ], 'webfloo-views');

            $this->publishes([
                __DIR__.'/../lang' => lang_path('vendor/webfloo'),
            ], 'webfloo-lang');
        }
    }

    protected function registerBladeComponents(): void
    {
        // Atoms
        $this->loadViewComponentsAs('webfloo', [
            Components\Atoms\Button::class,
            Components\Atoms\Badge::class,
            Components\Atoms\Icon::class,
            Components\Atoms\Heading::class,
            Components\Atoms\Text::class,

            // Molecules
            Components\Molecules\Card::class,
            Components\Molecules\ServiceCard::class,
            Components\Molecules\ProjectCard::class,
            Components\Molecules\SectionHeader::class,

            // Organisms
            Components\Organisms\Header::class,
            Components\Organisms\Footer::class,

            // Sections
            Components\Sections\Hero::class,
            Components\Sections\Services::class,
            Components\Sections\About::class,
            Components\Sections\Partners::class,
            Components\Sections\Faq::class,
            Components\Sections\Contact::class,
            Components\Sections\Testimonials::class,
            Components\Sections\Portfolio::class,
            Components\Sections\Cta::class,
            Components\Sections\Blog::class,
            Components\Sections\FeaturesGrid::class,
            Components\Sections\BentoGrid::class,
        ]);
    }

    protected function registerRoutes(): void
    {
        if (config('webfloo.features.crm', true)) {
            Route::prefix('api')
                ->middleware('api')
                ->group(__DIR__.'/../routes/api.php');
        }
    }
}
