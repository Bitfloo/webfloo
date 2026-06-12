<x-webfloo-layout :seo="$seo" :canonical="url('/portfolio/'.$project->slug)">
    <article class="container mx-auto max-w-4xl px-4 py-16">
        <header>
            @if ($project->category)
                <x-webfloo-badge>{{ $project->category }}</x-webfloo-badge>
            @endif

            <x-webfloo-heading :level="1" class="mt-4">{{ $project->title }}</x-webfloo-heading>

            @if ($project->excerpt)
                <x-webfloo-text size="lg" color="muted" class="mt-4">{{ $project->excerpt }}</x-webfloo-text>
            @endif
        </header>

        @if ($project->image)
            <figure class="mt-8">
                <img src="{{ \Illuminate\Support\Facades\Storage::url($project->image) }}" alt="{{ $project->title }}" class="w-full rounded-lg" />
            </figure>
        @endif

        @if ($project->description)
            <div class="prose prose-lg mt-8 max-w-none">
                {!! $project->description !!}
            </div>
        @endif

        @if ($project->hasCaseStudy())
            <div class="mt-12 grid gap-8 md:grid-cols-3">
                @foreach (['challenge' => 'Wyzwanie', 'solution' => 'Rozwiazanie', 'results' => 'Rezultaty'] as $field => $label)
                    @if ($project->{$field})
                        <div class="card bg-base-200">
                            <div class="card-body">
                                <h2 class="card-title">{{ $label }}</h2>
                                <p>{{ $project->{$field} }}</p>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        @if (! empty($project->technologies))
            <div class="mt-8 flex flex-wrap gap-2">
                @foreach ($project->technologies as $technology)
                    <x-webfloo-badge>{{ $technology }}</x-webfloo-badge>
                @endforeach
            </div>
        @endif

        <footer class="mt-12">
            <a href="{{ route('webfloo.portfolio.index') }}" class="btn btn-ghost">Wroc do portfolio</a>
        </footer>
    </article>
</x-webfloo-layout>
