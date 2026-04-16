<section {{ $attributes->merge(['class' => 'py-16 lg:py-24 bg-base-100 relative overflow-hidden']) }}
    @if($showAmbientBg)
        data-ambient-canvas='{"variant": "{{ $ambientVariant }}", "opacity": 0.15, "speed": 0.00008, "scale": 1.5, "blur": 120}'
    @endif
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            {{-- Content --}}
            <div class="{{ $imagePosition === 'left' ? 'lg:order-2' : '' }}">
                @if($subtitle)
                    <p class="text-primary font-semibold mb-4 uppercase tracking-wider text-sm">
                        {{ $subtitle }}
                    </p>
                @endif

                <h2 class="text-3xl md:text-4xl font-bold text-base-content font-heading mb-6">
                    {{ $title }}
                </h2>

                @if($description)
                    <div class="prose prose-lg text-base-content/70 mb-8">
                        {!! $description !!}
                    </div>
                @endif

                {{ $slot }}

                {{-- Features list --}}
                @if(!empty($features))
                    <ul class="space-y-4 mt-8">
                        @foreach($features as $feature)
                            <li class="flex items-start gap-3">
                                <span class="flex-shrink-0 w-6 h-6 bg-primary/10 rounded-full flex items-center justify-center mt-0.5">
                                    <span class="icon-[tabler--check] size-4 text-primary" aria-hidden="true"></span>
                                </span>
                                <span class="text-base-content/80">{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Image with stats overlay --}}
            <div class="{{ $imagePosition === 'left' ? 'lg:order-1' : '' }}">
                <div class="relative">
                    @if($image)
                        <div class="relative">
                            {{-- Skeleton placeholder --}}
                            <div class="absolute inset-0 skeleton skeleton-animated rounded-box"></div>
                            <img
                                src="{{ $image }}"
                                alt="{{ $title }}"
                                class="relative w-full rounded-box shadow-xl"
                                loading="lazy"
                                onload="this.previousElementSibling.remove()"
                            >
                        </div>
                    @else
                        <div class="aspect-[4/3] bg-gradient-to-br from-primary/10 to-secondary/10 rounded-box"></div>
                    @endif

                    {{-- Stats overlay --}}
                    @if(!empty($stats))
                        @php
                            $statsCols = match(count($stats)) {
                                1 => 'grid-cols-1',
                                2 => 'grid-cols-2',
                                3 => 'grid-cols-3',
                                4 => 'grid-cols-4',
                                default => 'grid-cols-3',
                            };
                        @endphp
                        <div class="absolute -bottom-8 left-4 right-4 md:left-8 md:right-8">
                            <div class="bg-base-100 rounded-box shadow-lg p-6 grid {{ $statsCols }} gap-4 text-center">
                                @foreach($stats as $stat)
                                    <div>
                                        <div class="text-2xl md:text-3xl font-bold text-primary">{{ $stat['value'] }}</div>
                                        <div class="text-sm text-base-content/50 mt-1">{{ $stat['label'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Partners Carousel - Pure CSS Infinite Loop --}}
    @if(!empty($partners))
        @php
            $itemWidth = 150;
            $itemCount = count($partners);
            $totalWidth = $itemWidth * $itemCount;
            $duration = max(20, $itemCount * 3);
        @endphp
        <div class="partners-section">
            @if($partnersTitle)
                <div class="text-center mb-10">
                    <p class="text-xs uppercase tracking-widest text-base-content/40 font-medium">{{ __('Technologie') }}</p>
                    <h3 class="text-lg font-medium text-base-content/60">{{ $partnersTitle }}</h3>
                </div>
            @endif

            <div class="partners-marquee">
                <div class="partners-marquee__fade partners-marquee__fade--left"></div>
                <div class="partners-marquee__fade partners-marquee__fade--right"></div>
                <div class="partners-marquee__track">@foreach($partners as $partner)<div class="partners-marquee__item"><img src="{{ $partner['logo'] }}" alt="{{ $partner['name'] }}" loading="eager"></div>@endforeach@foreach($partners as $partner)<div class="partners-marquee__item" aria-hidden="true"><img src="{{ $partner['logo'] }}" alt="" loading="eager"></div>@endforeach</div>
            </div>
        </div>

        <style>
            .partners-section { margin-top: 80px; }
            @media (min-width: 1024px) { .partners-section { margin-top: 110px; } }

            .partners-marquee {
                --item-width: {{ $itemWidth }}px;
                --total-width: {{ $totalWidth }}px;
                --duration: {{ $duration }}s;
                position: relative;
                overflow: hidden;
                height: 70px;
            }
            .partners-marquee__fade {
                position: absolute;
                top: 0;
                bottom: 0;
                width: 80px;
                z-index: 2;
                pointer-events: none;
            }
            .partners-marquee__fade--left {
                left: 0;
                background: linear-gradient(to right, oklch(var(--b1)), transparent);
            }
            .partners-marquee__fade--right {
                right: 0;
                background: linear-gradient(to left, oklch(var(--b1)), transparent);
            }
            .partners-marquee__track {
                display: flex;
                width: max-content;
                animation: marquee-scroll var(--duration) linear infinite;
            }
            .partners-marquee__item {
                width: var(--item-width);
                height: 70px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }
            .partners-marquee__item img {
                max-height: 45px;
                max-width: 110px;
                object-fit: contain;
                opacity: 0.4;
                filter: grayscale(1);
            }
            @media (min-width: 768px) {
                .partners-marquee { height: 90px; }
                .partners-marquee__fade { width: 120px; }
                .partners-marquee__item { height: 90px; }
                .partners-marquee__item img { max-height: 55px; max-width: 130px; }
            }
            @keyframes marquee-scroll {
                0% { transform: translateX(0); }
                100% { transform: translateX(calc(var(--total-width) * -1)); }
            }
        </style>
    @endif
</section>
