<section {{ $attributes->merge(['class' => 'py-16 lg:py-24 bg-base-200']) }}>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section Header --}}
        <x-webfloo-section-header
            :title="$title"
            :subtitle="$subtitle"
            :description="$description"
        />

        @if($hasPosts())
            {{-- Blog Grid --}}
            <div class="grid gap-6 lg:gap-8 {{ $gridClasses() }}">
                @foreach($posts as $post)
                    @php
                        $postUrl = \Illuminate\Support\Facades\Route::has('blog.show')
                            ? route('blog.show', $post)
                            : ($post->url ?? '#');
                    @endphp
                    <article class="card card-bordered bg-base-100 group overflow-hidden levitate-card">
                        <a href="{{ $postUrl }}" class="flex flex-col h-full">
                            {{-- Image --}}
                            <figure class="relative overflow-hidden aspect-video">
                                @if($post->featured_image)
                                    <img
                                        src="{{ Storage::url($post->featured_image) }}"
                                        alt="{{ $post->title }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="w-full h-full bg-base-300 flex items-center justify-center">
                                        <span class="icon-[tabler--article] size-12 text-base-content/20" aria-hidden="true"></span>
                                    </div>
                                @endif

                                {{-- Category Badge --}}
                                @if($post->category)
                                    <span class="badge badge-{{ $post->category->color ?? 'primary' }} badge-soft absolute top-4 left-4">
                                        {{ $post->category->name }}
                                    </span>
                                @endif
                            </figure>

                            {{-- Content --}}
                            <div class="card-body flex-1">
                                {{-- Meta --}}
                                <div class="flex items-center gap-3 text-xs text-base-content/60 mb-2">
                                    @if($post->published_at)
                                        <span class="flex items-center gap-1">
                                            <span class="icon-[tabler--calendar] size-3.5" aria-hidden="true"></span>
                                            {{ $post->published_at->format('d M Y') }}
                                        </span>
                                    @endif
                                    @if($post->reading_time)
                                        <span class="flex items-center gap-1">
                                            <span class="icon-[tabler--clock] size-3.5" aria-hidden="true"></span>
                                            {{ $post->reading_time }} min
                                        </span>
                                    @endif
                                </div>

                                {{-- Title --}}
                                <h3 class="card-title text-lg group-hover:text-primary transition-colors line-clamp-2">
                                    {{ $post->title }}
                                </h3>

                                {{-- Excerpt --}}
                                @if($post->excerpt)
                                    <p class="text-sm text-base-content/70 line-clamp-3 mt-2 flex-1">
                                        {{ $post->excerpt }}
                                    </p>
                                @endif

                                {{-- Read More --}}
                                <div class="mt-4 flex items-center text-primary text-sm font-medium group-hover:gap-2 transition-all">
                                    <span>{{ __('Czytaj więcej') }}</span>
                                    <span class="icon-[tabler--arrow-right] size-4 ml-1 group-hover:translate-x-1 transition-transform" aria-hidden="true"></span>
                                </div>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        @else
            {{-- Empty State --}}
            <div class="text-center py-12">
                <span class="icon-[tabler--article-off] size-16 text-base-content/20 mb-4" aria-hidden="true"></span>
                <p class="text-base-content/50 text-lg">{{ __('Brak artykułów do wyświetlenia.') }}</p>
            </div>
        @endif

        {{-- View All Button --}}
        @if($viewAllUrl && $hasPosts())
            <div class="mt-10 text-center">
                <x-webfloo-button
                    :href="$viewAllUrl"
                    variant="primary"
                    size="lg"
                >
                    {{ $viewAllText }}
                    <span class="icon-[tabler--arrow-right] size-5 ml-2" aria-hidden="true"></span>
                </x-webfloo-button>
            </div>
        @endif

        {{-- Slot for additional content --}}
        @if($slot->isNotEmpty())
            <div class="mt-12 text-center">
                {{ $slot }}
            </div>
        @endif
    </div>
</section>
