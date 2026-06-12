<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $baseTheme() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-webfloo-seo :data="$seo" :canonical="$canonical" :type="$seoType" />
    @if ($faviconUrl())
    <link rel="icon" href="{{ $faviconUrl() }}">
    @endif
    @if (file_exists(public_path('vendor/webfloo/webfloo.css')))
    <link rel="stylesheet" href="{{ asset('vendor/webfloo/webfloo.css') }}">
    @endif
    <style>{!! $cssVariables() !!}</style>
    @if ($customCss() !== '')
    <style>{!! $customCss() !!}</style>
    @endif
    {{ $head ?? '' }}
</head>
<body @class(['min-h-screen flex flex-col bg-base-100 text-base-content', $bodyClass => $bodyClass !== ''])>
    <x-webfloo-header :navigation="$headerNavigation()" />
    <main class="flex-1">
        {{ $slot }}
    </main>
    <x-webfloo-footer />
    @if (config('webfloo.features.cookie_consent'))
    <x-webfloo-cookie-consent />
    @endif
    @if (file_exists(public_path('vendor/webfloo/webfloo.js')))
    <script defer src="{{ asset('vendor/webfloo/webfloo.js') }}"></script>
    @endif
    @if ($customJs() !== '')
    <script>{!! $customJs() !!}</script>
    @endif
    {{ $scripts ?? '' }}
</body>
</html>
