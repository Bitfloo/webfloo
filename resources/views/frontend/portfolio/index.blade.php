<x-webfloo-layout :seo="['title' => setting('portfolio.title', 'Portfolio'), 'description' => setting('portfolio.description'), 'image' => null, 'no_index' => false]" :canonical="url('/portfolio')">
    <x-webfloo-portfolio
        :title="(string) setting('portfolio.title', 'Portfolio')"
        :description="setting('portfolio.description')"
        :projects="$projects->map->toCardArray()->all()"
        :limit="0"
    />
</x-webfloo-layout>
