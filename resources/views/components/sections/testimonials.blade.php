<section {{ $attributes->merge(['class' => 'py-16 lg:py-24 bg-base-200']) }}>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <x-webfloo-section-header
            :title="$title"
            :subtitle="$subtitle"
        />

        {{-- Desktop Grid --}}
        <div class="hidden md:grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($testimonials as $index => $testimonial)
                <article class="card bg-base-100 shadow-sm hover:shadow-lg transition-shadow" role="article">
                    <div class="card-body">
                        {{-- Quote icon --}}
                        <span class="icon-[tabler--quote] size-10 text-primary/20 mb-2" aria-hidden="true"></span>

                        {{-- Content --}}
                        <p class="text-base-content/70 leading-relaxed mb-6">
                            {{ $testimonial['content'] }}
                        </p>

                        {{-- Author --}}
                        <div class="flex items-center gap-4">
                            <div class="avatar">
                                <div class="w-12 rounded-full">
                                    <img
                                        src="{{ $getAvatarUrl($testimonial, 96) }}"
                                        alt="{{ $testimonial['author'] }}"
                                        loading="lazy"
                                    />
                                </div>
                            </div>
                            <div>
                                <div class="font-semibold text-base-content">{{ $testimonial['author'] }}</div>
                                @if(isset($testimonial['role']))
                                    <div class="text-sm text-base-content/50">{{ $testimonial['role'] }}</div>
                                @endif
                            </div>
                        </div>

                        {{-- Rating --}}
                        @if(isset($testimonial['rating']))
                            <div class="rating rating-sm mt-4" role="img" aria-label="{{ __('Ocena') }} {{ $testimonial['rating'] }} {{ __('z 5') }}">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="icon-[tabler--star-filled] size-5 {{ $i <= $testimonial['rating'] ? 'text-warning' : 'text-base-content/20' }}" aria-hidden="true"></span>
                                @endfor
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Mobile Carousel (FlyonUI) --}}
        <div class="md:hidden">
            <div
                class="carousel w-full gap-4"
                data-carousel='{"loadingClasses": "opacity-0"}'
                role="region"
                aria-label="{{ __('Opinie klientów') }}"
            >
                <div class="carousel-body opacity-0">
                    @foreach($testimonials as $index => $testimonial)
                        <div class="carousel-slide">
                            <article class="card bg-base-100 shadow-sm mx-2" role="article">
                                <div class="card-body">
                                    <span class="icon-[tabler--quote] size-8 text-primary/20 mb-2" aria-hidden="true"></span>
                                    <p class="text-base-content/70 leading-relaxed mb-4">{{ $testimonial['content'] }}</p>
                                    <div class="flex items-center gap-3 mt-2">
                                        <div class="avatar">
                                            <div class="w-10 rounded-full">
                                                <img
                                                    src="{{ $getAvatarUrl($testimonial, 80) }}"
                                                    alt="{{ $testimonial['author'] }}"
                                                    loading="lazy"
                                                />
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-base-content">{{ $testimonial['author'] }}</div>
                                            @if(isset($testimonial['role']))
                                                <div class="text-sm text-base-content/50">{{ $testimonial['role'] }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="carousel-pagination flex justify-center gap-2 mt-6" role="tablist" aria-label="{{ __('Nawigacja opinii') }}">
                    @foreach($testimonials as $index => $testimonial)
                        <span
                            class="carousel-bullet size-2 bg-base-content/30 rounded-full cursor-pointer transition-colors [&.active]:bg-primary"
                            role="tab"
                            aria-label="{{ __('Przejdź do opinii') }} {{ $index + 1 }}"
                        ></span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Slot for additional content --}}
        @if($slot->isNotEmpty())
            <div class="mt-12 text-center">
                {{ $slot }}
            </div>
        @endif
    </div>
</section>
