<section
    {{ $attributes->merge(['class' => 'relative overflow-hidden ' . $backgroundClasses()]) }}
    @if($hasImageBackground())
        style="background-image: url('{{ $backgroundImage }}'); background-size: cover; background-position: center;"
    @endif
    @if($showAmbientBg)
        data-ambient-canvas='{"variant": "{{ $ambientVariant }}", "opacity": 0.2, "colors": ["oklch(90% 0.1 262.88)", "oklch(80% 0.1 162.48)", "oklch(70% 0.05 264.53)"]}'
    @endif
>
    {{-- Image overlay --}}
    @if($hasImageBackground())
        <div class="absolute inset-0 bg-neutral/75"></div>
    @endif

    {{-- Decorative elements --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
        <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-white/5"></div>
        <div class="absolute -bottom-24 -left-24 w-80 h-80 rounded-full bg-white/5"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full max-w-3xl">
            <div class="absolute top-0 right-1/4 w-32 h-32 rounded-full bg-white/5 blur-xl"></div>
            <div class="absolute bottom-1/4 left-1/4 w-24 h-24 rounded-full bg-white/5 blur-xl"></div>
        </div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
        <div class="flex flex-col {{ $alignmentClasses() }} max-w-3xl {{ $alignment === 'center' ? 'mx-auto' : '' }}">
            {{-- Subtitle/Tagline --}}
            @if($subtitle)
                <p class="text-white/80 font-semibold uppercase tracking-wider text-sm mb-4">
                    {{ $subtitle }}
                </p>
            @endif

            {{-- Title --}}
            <x-webfloo-heading
                :level="2"
                size="2xl"
                class="text-white mb-6"
            >
                {{ $title }}
            </x-webfloo-heading>

            {{-- Description --}}
            @if($description)
                <p class="text-lg md:text-xl text-white/90 leading-relaxed mb-8 max-w-2xl {{ $alignment === 'center' ? 'mx-auto' : '' }}">
                    {{ $description }}
                </p>
            @endif

            {{-- Slot for additional content --}}
            {{ $slot }}

            {{-- CTA Buttons --}}
            @if($primaryCta || $secondaryCta)
                <div class="flex flex-wrap gap-4 {{ $alignment === 'center' ? 'justify-center' : '' }} mt-2">
                    @if($primaryCta && isset($primaryCta['text']) && isset($primaryCta['href']))
                        <x-webfloo-button
                            :href="$primaryCta['href']"
                            variant="secondary"
                            size="lg"
                            class="bg-base-100 text-primary hover:bg-base-100/90"
                        >
                            {{ $primaryCta['text'] }}
                        </x-webfloo-button>
                    @endif

                    @if($secondaryCta && isset($secondaryCta['text']) && isset($secondaryCta['href']))
                        <x-webfloo-button
                            :href="$secondaryCta['href']"
                            variant="outline"
                            size="lg"
                            class="border-white text-white hover:bg-white/10"
                        >
                            {{ $secondaryCta['text'] }}
                        </x-webfloo-button>
                    @endif
                </div>
            @endif
        </div>
    </div>
</section>
