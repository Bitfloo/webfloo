<!DOCTYPE html>
{{-- Deliberately standalone: a 500 page must not touch the DB, components or theme service. --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ __('Wystapil blad serwera') }}</title>
    <style>
        body { margin: 0; font-family: system-ui, -apple-system, sans-serif; background: #111827; color: #f9fafb; display: flex; min-height: 100vh; align-items: center; justify-content: center; }
        .box { text-align: center; padding: 2rem; }
        .code { font-size: 4.5rem; font-weight: 700; margin: 0; }
        p { color: #9ca3af; }
        a { color: #60a5fa; }
    </style>
</head>
<body>
    <div class="box">
        <p class="code">500</p>
        <h1>{{ __('Wystapil blad serwera') }}</h1>
        <p>{{ __('Pracujemy nad rozwiazaniem problemu. Sprobuj ponownie za chwile.') }}</p>
        <a href="{{ url('/') }}">{{ __('Wroc na strone glowna') }}</a>
    </div>
</body>
</html>
