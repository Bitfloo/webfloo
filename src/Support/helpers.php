<?php

use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Support\Str;
use Webfloo\Models\Setting;

if (! function_exists('webfloo_permission')) {
    /**
     * Build a Shield v4 permission identifier: {StudlyAction}:{StudlySubject},
     * e.g. webfloo_permission('view_any', 'post') => "ViewAny:Post".
     *
     * Single source of the permission-name format — shared by every
     * canAccess() gate and ShieldRolesSeeder. Must match what
     * `shield:generate` creates on the host.
     */
    function webfloo_permission(string $action, string $subject): string
    {
        return Str::studly($action).':'.Str::studly($subject);
    }
}

if (! function_exists('webfloo_fallback_locale')) {
    /**
     * Single source of truth for the package's fallback locale.
     *
     * Translatable settings store the fallback-locale value under the base
     * key and other locales under "{key}.{locale}" — every reader/writer
     * (setting() helper, AbstractPageSettings) must agree on this locale.
     */
    function webfloo_fallback_locale(): string
    {
        $fallback = config('app.fallback_locale');

        return is_string($fallback) && $fallback !== '' ? $fallback : 'pl';
    }
}

if (! function_exists('webfloo_currency')) {
    /**
     * Single source of the CRM currency code (lead values, exports, stats).
     *
     * Reads webfloo.crm.currency so client projects are not locked to the
     * PLN default; PLN remains the fallback for backwards compatibility.
     */
    function webfloo_currency(): string
    {
        $currency = config('webfloo.crm.currency');

        return is_string($currency) && $currency !== '' ? $currency : 'PLN';
    }
}

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        $locale = app()->getLocale();
        $fallback = webfloo_fallback_locale();

        // Try locale-specific key first (e.g. home.hero_title.en)
        if ($locale !== $fallback) {
            $localized = Setting::get("{$key}.{$locale}");
            if ($localized !== null) {
                return $localized;
            }
        }

        // Fall back to base key (existing PL data)
        return Setting::get($key, $default);
    }
}

if (! function_exists('webfloo_user_model')) {
    /**
     * Resolve the host app User model class for relations in core models.
     *
     * Host User model must extend Illuminate\Foundation\Auth\User so that
     * BelongsTo<User, ...> PHPDocs in Post/Lead/LeadActivity/LeadReminder
     * type-check cleanly against the returned class-string.
     *
     * @return class-string<AuthUser>
     */
    function webfloo_user_model(): string
    {
        $model = config('webfloo.user_model');

        // Fall back to the host's auth provider model so fresh installs
        // (webfloo:install) work before config/webfloo.php is customized.
        if ($model === null) {
            $model = config('auth.providers.users.model');
        }

        if (! is_string($model) || ! class_exists($model)) {
            throw new RuntimeException(
                'webfloo.user_model must be a class-string of an Eloquent User model. '
                .'Set it in config/webfloo.php (published via `php artisan vendor:publish --tag=webfloo-config`).'
            );
        }

        if (! is_subclass_of($model, AuthUser::class)) {
            throw new RuntimeException(
                "webfloo.user_model [{$model}] must extend Illuminate\\Foundation\\Auth\\User."
            );
        }

        /** @var class-string<AuthUser> $model */
        return $model;
    }
}
