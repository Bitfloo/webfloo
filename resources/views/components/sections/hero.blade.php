<section {{ $attributes->merge(['class' => 'relative overflow-hidden min-h-screen flex items-center bg-base-100 pt-28 lg:pt-40']) }}>
    {{-- Background --}}
    @if($showDotMatrix)
        <div
            id="hero-dot-matrix"
            data-dot-matrix='{"speed": 0.0003, "waveHeight": 1.1, "mouseInfluence": 0.25, "fontSize": 14, "chroma": 0, "sparkleChance": 0.015, "randomness": 0.15}'
            aria-hidden="true"
            class="absolute inset-0 w-full h-full"
            style="z-index: 0;"
        ></div>
        {{-- Gradient overlays for readability and blending --}}
        <div class="absolute inset-0 bg-gradient-to-r from-base-100/95 via-base-100/70 to-base-100/40" style="z-index: 1;"></div>
        {{-- Bottom fade for smooth transition to next section --}}
        <div class="absolute bottom-0 left-0 right-0 h-40 bg-gradient-to-t from-base-100 to-transparent" style="z-index: 2;"></div>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full relative z-10 pb-16">
        <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">
            {{-- Content (Left) --}}
            <div>
                @if($subtitle)
                    <p class="text-primary font-semibold mb-4 uppercase tracking-wider text-sm">
                        {{ $subtitle }}
                    </p>
                @endif

                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-base-content font-heading leading-tight mb-6">
                    {{ $title }}
                </h1>

                @if($description)
                    <p class="text-lg text-base-content/70 leading-relaxed mb-8">
                        {{ $description }}
                    </p>
                @endif

                @if($ctaText || $secondaryCtaText)
                    <div class="flex flex-wrap gap-4">
                        @if($ctaText && $ctaHref)
                            <x-webfloo-button :href="$ctaHref" variant="primary" size="lg">
                                {{ $ctaText }}
                            </x-webfloo-button>
                        @endif

                        @if($secondaryCtaText && $secondaryCtaHref)
                            <x-webfloo-button :href="$secondaryCtaHref" variant="outline" size="lg">
                                {{ $secondaryCtaText }}
                            </x-webfloo-button>
                        @endif
                    </div>
                @endif

                {{ $slot }}
            </div>

            {{-- Binary 3D Sphere (Right) --}}
            <div class="hidden lg:flex items-center justify-center relative h-[500px]">
                <div
                    data-binary-sphere='{"particleCount": 1000, "rotationSpeed": 0.0015, "fontSize": 13, "shape": "sphere"}'
                    class="absolute inset-0"
                ></div>
            </div>
        </div>
    </div>

    {{-- Scroll indicator --}}
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-10">
        <a href="#services" class="flex flex-col items-center gap-2 text-base-content/50 hover:text-base-content/80 transition-colors">
            <span class="text-xs uppercase tracking-widest">{{ __('Scroll') }}</span>
            <span class="icon-[tabler--chevron-down] size-6 animate-bounce"></span>
        </a>
    </div>
</section>
