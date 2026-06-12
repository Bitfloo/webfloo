<x-webfloo-layout :seo="['title' => __('Strona nie znaleziona'), 'description' => null, 'image' => null, 'no_index' => true]">
    <div class="container mx-auto flex max-w-2xl flex-col items-center px-4 py-32 text-center">
        <p class="text-7xl font-bold text-primary">404</p>
        <x-webfloo-heading :level="1" class="mt-4">{{ __('Strona nie znaleziona') }}</x-webfloo-heading>
        <x-webfloo-text color="muted" class="mt-4">
            {{ __('Strona, ktorej szukasz, nie istnieje lub zostala przeniesiona.') }}
        </x-webfloo-text>
        <a href="{{ url('/') }}" class="btn btn-primary mt-8">{{ __('Wroc na strone glowna') }}</a>
    </div>
</x-webfloo-layout>
