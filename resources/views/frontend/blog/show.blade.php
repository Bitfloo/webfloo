<x-webfloo-layout :seo="$post->getSeoData()" :canonical="url($post->url)" seo-type="article">
    <article class="container mx-auto max-w-3xl px-4 py-16">
        <header>
            @if ($post->category)
                <x-webfloo-badge>{{ $post->category->name }}</x-webfloo-badge>
            @endif

            <x-webfloo-heading :level="1" class="mt-4">{{ $post->title }}</x-webfloo-heading>

            <div class="mt-4 flex flex-wrap items-center gap-4 text-sm text-base-content/60">
                @if ($post->published_at)
                    <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('d.m.Y') }}</time>
                @endif
                @if ($post->reading_time)
                    <span>{{ $post->reading_time }} min czytania</span>
                @endif
            </div>
        </header>

        @if ($post->featured_image_url)
            <figure class="mt-8">
                <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" class="w-full rounded-lg" />
            </figure>
        @endif

        <div class="prose prose-lg mt-8 max-w-none">
            {!! clean($post->content, 'webfloo') !!}
        </div>

        <footer class="mt-12">
            <a href="{{ route('webfloo.blog.index') }}" class="btn btn-ghost">Wroc do bloga</a>
        </footer>
    </article>
</x-webfloo-layout>
