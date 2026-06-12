<?php

namespace Webfloo;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Routing\Router;
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

        $this->registerPurifierProfile();
    }

    /**
     * Register the "webfloo" purifier profile used by every clean() call in
     * the package. The mews/purifier "default" profile strips rich-text
     * markup the admin RichEditor produces (h1-h6, blockquote, pre/code,
     * tables), so sanitizing with it silently butchers published content.
     * Hosts override by defining purifier.settings.webfloo themselves.
     */
    protected function registerPurifierProfile(): void
    {
        if (config('purifier.settings.webfloo') !== null) {
            return;
        }

        config(['purifier.settings.webfloo' => [
            'HTML.Allowed' => 'h1,h2,h3,h4,h5,h6,p[style],br,hr,b,strong,i,em,u,s,del,ins,sub,sup,'
                .'a[href|title|rel|target],ul,ol[start],li,blockquote,pre,code,span[style],div,'
                .'img[src|alt|width|height|title],figure,figcaption,'
                .'table,thead,tbody,tfoot,tr,th[colspan|rowspan|style],td[colspan|rowspan|style]',
            'CSS.AllowedProperties' => 'text-align,color,background-color,'
                .'font-weight,font-style,text-decoration,padding-left',
            'Attr.AllowedFrameTargets' => ['_blank'],
            'AutoFormat.RemoveEmpty' => false,
        ]]);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'webfloo');
        $this->loadJsonTranslationsFrom(__DIR__.'/../lang');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->registerBladeComponents();
        $this->registerLivewireComponents();
        $this->registerRoutes();
        $this->registerEventListeners();
        $this->registerSchedule();
        $this->registerRedirects();

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

            $this->publishes([
                __DIR__.'/../resources/views/errors' => resource_path('views/errors'),
            ], 'webfloo-error-pages');

            $this->publishes([
                __DIR__.'/../dist' => public_path('vendor/webfloo'),
            ], 'webfloo-assets');
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
            Components\Organisms\CookieConsent::class,

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

    protected function registerLivewireComponents(): void
    {
        if (! ModuleRegistry::isEnabled('frontend')) {
            return;
        }

        \Livewire\Livewire::component('webfloo-contact-form', Livewire\ContactForm::class);

        if (ModuleRegistry::isEnabled('newsletter')) {
            \Livewire\Livewire::component('webfloo-newsletter-form', Livewire\NewsletterForm::class);
        }
    }

    protected function registerRoutes(): void
    {
        if (ModuleRegistry::isEnabled('crm')) {
            Route::prefix('api')
                ->middleware('api')
                ->group(__DIR__.'/../routes/api.php');
        }

        if (ModuleRegistry::isEnabled('frontend')) {
            Route::middleware('web')
                ->group(__DIR__.'/../routes/frontend.php');
        }
    }

    protected function registerEventListeners(): void
    {
        if (ModuleRegistry::isEnabled('crm')) {
            Event::listen(LeadCreated::class, SendNewLeadNotification::class);
        }
    }

    /**
     * 404-rescue middleware + slug-change observers for the redirects module.
     * Middleware is pushed after the app boots so the host's web group is
     * fully configured first.
     *
     * Direct config read with a false default — NOT ModuleRegistry, whose
     * missing-key fallback is true: a stale published config/webfloo.php
     * (features array predating this module) must keep an opt-in module off.
     */
    protected function registerRedirects(): void
    {
        if ((bool) config('webfloo.features.redirects', false) === false) {
            return;
        }

        $this->app->booted(function (): void {
            $this->app->make(Router::class)
                ->pushMiddlewareToGroup('web', Http\Middleware\HandleRedirects::class);
        });

        Models\Page::observe(Observers\SlugChangeObserver::class);
        Models\Post::observe(Observers\SlugChangeObserver::class);
        Models\Project::observe(Observers\SlugChangeObserver::class);
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
