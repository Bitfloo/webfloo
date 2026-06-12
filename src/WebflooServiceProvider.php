<?php

namespace Webfloo;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Webfloo\Events\LeadCreated;
use Webfloo\Listeners\SendNewLeadNotification;
use Webfloo\Services\PluginTranslationRegistry;
use Webfloo\Services\ThemeService;
use Webfloo\Support\ModuleRegistry;

class WebflooServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/webfloo.php', 'webfloo');
        $this->mergeConfigFrom(__DIR__.'/../config/webfloo-modules.php', 'webfloo-modules');

        $this->app->singleton(ThemeService::class, fn () => new ThemeService);
        $this->app->singleton(PluginTranslationRegistry::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'webfloo');
        $this->loadJsonTranslationsFrom(__DIR__.'/../lang');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->registerBladeComponents();
        $this->registerRoutes();
        $this->registerEventListeners();
        $this->registerSchedule();

        if ($this->app->runningInConsole()) {
            /*
             * Commands rejestrowane tylko dla modułów enabled — host
             * który wyłączy webfloo.features.crm nie dostaje
             * `webfloo:send-lead-reminders` w php artisan list.
             */
            $this->commands(ModuleRegistry::enabledCommands());

            $this->publishes([
                __DIR__.'/../config/webfloo.php' => config_path('webfloo.php'),
            ], 'webfloo-config');

            $this->publishes([
                __DIR__.'/../config/webfloo-modules.php' => config_path('webfloo-modules.php'),
            ], 'webfloo-modules');

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
            Components\Atoms\Seo::class,
            Components\Atoms\Text::class,

            // Molecules
            Components\Molecules\Card::class,
            Components\Molecules\ServiceCard::class,
            Components\Molecules\ProjectCard::class,
            Components\Molecules\SectionHeader::class,

            // Organisms
            Components\Organisms\Header::class,
            Components\Organisms\Footer::class,

            // Layouts
            'layout' => Components\Layouts\Frontend::class,

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

    protected function registerEventListeners(): void
    {
        if (ModuleRegistry::isEnabled('crm')) {
            Event::listen(LeadCreated::class, SendNewLeadNotification::class);
        }
    }

    protected function registerSchedule(): void
    {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule): void {
            if (! config('webfloo.schedule.enabled', true)) {
                return;
            }

            if (ModuleRegistry::isEnabled('seo')) {
                $schedule->command('sitemap:generate')->weekly();
            }

            if (ModuleRegistry::isEnabled('crm')) {
                $schedule->command('leads:send-reminders')->dailyAt('08:00');
            }
        });
    }
}
