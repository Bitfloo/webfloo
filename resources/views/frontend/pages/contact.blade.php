<x-webfloo-layout :seo="$page->getSeoData()" :canonical="url($page->url)">
    <div class="container mx-auto max-w-3xl px-4 pt-16">
        <x-webfloo-heading :level="1">{{ $page->title }}</x-webfloo-heading>

        @if ($page->contentHtml() !== '')
            <div class="prose mt-8 max-w-none">
                {!! $page->contentHtml() !!}
            </div>
        @endif
    </div>

    <x-webfloo-contact id="contact" />
</x-webfloo-layout>
