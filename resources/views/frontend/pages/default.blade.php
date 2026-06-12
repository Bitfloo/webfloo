<x-webfloo-layout :seo="$page->getSeoData()" :canonical="url($page->url)">
    <article class="container mx-auto max-w-3xl px-4 py-16">
        @if (count($page->breadcrumbs) > 1)
            <nav class="breadcrumbs mb-6 text-sm" aria-label="Breadcrumb">
                <ul>
                    @foreach ($page->breadcrumbs as $crumb)
                        <li>
                            @if ($loop->last)
                                <span>{{ $crumb['title'] }}</span>
                            @else
                                <a href="{{ url($crumb['url']) }}">{{ $crumb['title'] }}</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </nav>
        @endif

        <x-webfloo-heading :level="1">{{ $page->title }}</x-webfloo-heading>

        <div class="prose mt-8 max-w-none">
            {!! $page->contentHtml() !!}
        </div>
    </article>
</x-webfloo-layout>
