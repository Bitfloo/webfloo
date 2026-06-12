<?php

use Illuminate\Foundation\Auth\User as AuthUser;
use Webfloo\Models\Setting;

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
