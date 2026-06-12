<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ setting('site_name', config('app.name')) }}</title>
        <link>{{ url(route('webfloo.blog.index', [], false)) }}</link>
        <atom:link href="{{ url(route('webfloo.blog.feed', [], false)) }}" rel="self" type="application/rss+xml" />
        <description>{{ setting('site_description', '') }}</description>
        <language>{{ str_replace('_', '-', app()->getLocale()) }}</language>
        <lastBuildDate>{{ now()->toRssString() }}</lastBuildDate>
        @foreach ($posts as $post)
        <item>
            <title>{{ $post->title }}</title>
            <link>{{ url($post->url) }}</link>
            <guid isPermaLink="true">{{ url($post->url) }}</guid>
            @if ($post->published_at)
            <pubDate>{{ $post->published_at->toRssString() }}</pubDate>
            @endif
            @if (is_string($post->excerpt) && $post->excerpt !== '')
            <description>{{ $post->excerpt }}</description>
            @endif
        </item>
        @endforeach
    </channel>
</rss>
