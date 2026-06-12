<x-webfloo-layout :seo="['title' => setting('blog.title', 'Blog'), 'description' => setting('blog.description'), 'image' => null, 'no_index' => false]" :canonical="url('/blog')">
    <x-slot:head>
        <link rel="alternate" type="application/rss+xml" title="{{ setting('blog.title', 'Blog') }}" href="{{ url(route('webfloo.blog.feed', [], false)) }}">
    </x-slot:head>
    <div class="container mx-auto px-4 py-16">
        <div class="mb-12 flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
            <div>
                <x-webfloo-heading :level="1">{{ setting('blog.title', 'Blog') }}</x-webfloo-heading>
                @if (setting('blog.description'))
                    <x-webfloo-text size="lg" color="muted" class="mt-2">{{ setting('blog.description') }}</x-webfloo-text>
                @endif
            </div>

            <form method="get" action="{{ route('webfloo.blog.index') }}" class="flex gap-2">
                <input
                    type="search"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Szukaj..."
                    class="input input-bordered w-full md:w-64"
                />
                <button type="submit" class="btn btn-primary">Szukaj</button>
            </form>
        </div>

        @if ($categories->isNotEmpty())
            <div class="mb-8 flex flex-wrap gap-2">
                <a href="{{ route('webfloo.blog.index') }}" class="badge badge-lg {{ request('category') ? 'badge-outline' : 'badge-primary' }}">
                    Wszystkie
                </a>
                @foreach ($categories as $category)
                    <a
                        href="{{ route('webfloo.blog.index', ['category' => $category->slug]) }}"
                        class="badge badge-lg {{ request('category') === $category->slug ? 'badge-primary' : 'badge-outline' }}"
                    >
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        @endif

        @if ($posts->isEmpty())
            <x-webfloo-text color="muted">Brak wpisow do wyswietlenia.</x-webfloo-text>
        @else
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <article class="card bg-base-200 transition hover:shadow-lg">
                        @if ($post->featured_image_url)
                            <figure>
                                <a href="{{ $post->url }}">
                                    <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" class="h-48 w-full object-cover" loading="lazy" />
                                </a>
                            </figure>
                        @endif
                        <div class="card-body">
                            @if ($post->category)
                                <x-webfloo-badge>{{ $post->category->name }}</x-webfloo-badge>
                            @endif
                            <h2 class="card-title">
                                <a href="{{ $post->url }}">{{ $post->title }}</a>
                            </h2>
                            @if ($post->excerpt)
                                <p class="text-base-content/70">{{ $post->excerpt }}</p>
                            @endif
                            <div class="card-actions mt-2 items-center justify-between text-sm text-base-content/60">
                                <span>{{ $post->published_at?->format('d.m.Y') }}</span>
                                @if ($post->reading_time)
                                    <span>{{ $post->reading_time }} min</span>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-12">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</x-webfloo-layout>
