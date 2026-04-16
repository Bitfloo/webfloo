{{-- Portfolio Section - Same style as blog show page --}}
<section {{ $attributes->merge(['class' => 'py-16 lg:py-24 bg-base-100']) }}>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Section Header --}}
        <x-webfloo-section-header
            :title="$title"
            :subtitle="$subtitle"
            :description="$description"
            class="mb-12 lg:mb-16"
        />

        @if($hasFilters())
            {{-- Category Filters --}}
            <div class="flex flex-wrap justify-center gap-2 mb-10 lg:mb-12" role="tablist" aria-label="{{ __('Filtruj projekty') }}">
                <button
                    type="button"
                    class="btn btn-sm btn-primary rounded-full"
                    data-filter="all"
                >
                    {{ __('Wszystkie') }}
                </button>
                @foreach($categories as $category)
                    <button
                        type="button"
                        class="btn btn-sm btn-ghost rounded-full"
                        data-filter="{{ Str::slug($category) }}"
                    >
                        {{ $category }}
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Projects Grid --}}
        @if(!empty($projects))
            <div
                class="grid gap-8 {{ $gridClasses() }}"
                @if($staggeredAnimation)
                    data-staggered-grid
                    data-grid-variant="{{ $animationVariant }}"
                @endif
            >
                @foreach($projects as $project)
                    @php
                        $projectUrl = $project['url'] ?? (isset($project['slug']) ? '/portfolio/' . $project['slug'] : null);
                        $isClickable = !empty($projectUrl);
                    @endphp

                    <a
                        @if($isClickable) href="{{ $projectUrl }}" @endif
                        class="card card-bordered bg-base-200/50 shadow-none group overflow-hidden levitate-card"
                        @if($staggeredAnimation) data-grid-item @endif
                        @if($project['category'] ?? null) data-category="{{ Str::slug($project['category']) }}" @endif
                    >
                        {{-- Image --}}
                        <figure class="relative aspect-video overflow-hidden">
                            @if(!empty($project['image']))
                                <img
                                    src="{{ $project['image'] }}"
                                    alt="{{ $project['title'] }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                    loading="lazy"
                                >
                            @else
                                <div class="w-full h-full bg-base-300 flex items-center justify-center">
                                    <span class="icon-[tabler--briefcase] size-12 text-base-content/20" aria-hidden="true"></span>
                                </div>
                            @endif

                            {{-- Category Badge --}}
                            @if($project['category'] ?? null)
                                <span class="badge badge-primary badge-soft absolute top-4 left-4">
                                    {{ $project['category'] }}
                                </span>
                            @endif
                        </figure>

                        {{-- Content --}}
                        <div class="card-body">
                            <h3 class="card-title text-lg group-hover:text-primary transition-colors line-clamp-1">
                                {{ $project['title'] }}
                            </h3>

                            @if(!empty($project['excerpt']))
                                <p class="text-sm text-base-content/70 line-clamp-2">
                                    {{ $project['excerpt'] }}
                                </p>
                            @endif

                            {{-- Technologies with color mapping --}}
                            @if(!empty($project['technologies']))
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    @foreach(array_slice($project['technologies'], 0, 3) as $tech)
                                        <span class="badge badge-soft badge-sm {{ \Webfloo\Components\Molecules\ProjectCard::getTechColor($tech) }}">{{ $tech }}</span>
                                    @endforeach
                                    @if(count($project['technologies']) > 3)
                                        <span class="badge badge-soft badge-sm badge-neutral">+{{ count($project['technologies']) - 3 }}</span>
                                    @endif
                                </div>
                            @endif

                            {{-- Link --}}
                            @if($isClickable)
                                <div class="mt-auto pt-3 flex items-center text-primary text-sm font-medium">
                                    <span>{{ __('Zobacz projekt') }}</span>
                                    <span class="icon-[tabler--arrow-right] size-4 ml-1 group-hover:translate-x-1 transition-transform" aria-hidden="true"></span>
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            {{-- Empty State --}}
            <div class="text-center py-12">
                <span class="icon-[tabler--folder-off] size-16 text-base-content/20 mb-4 inline-block" aria-hidden="true"></span>
                <p class="text-base-content/50 text-lg">{{ __('Brak projektów do wyświetlenia.') }}</p>
            </div>
        @endif

        {{-- View All Button --}}
        @if($viewAllUrl && !empty($projects))
            <div class="mt-12 text-center">
                <x-webfloo-button
                    :href="$viewAllUrl"
                    variant="outline"
                    size="lg"
                >
                    {{ __('Zobacz wszystkie projekty') }}
                    <span class="icon-[tabler--arrow-right] size-5 ml-2" aria-hidden="true"></span>
                </x-webfloo-button>
            </div>
        @endif

        {{-- Slot --}}
        @if($slot->isNotEmpty())
            <div class="mt-12 text-center">
                {{ $slot }}
            </div>
        @endif
    </div>
</section>
