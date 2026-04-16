<section {{ $attributes->merge(['class' => 'py-12 lg:py-16 bg-base-100 overflow-hidden']) }}>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        @if($title || $subtitle)
            <div class="text-center mb-10">
                @if($subtitle)
                    <p class="text-primary font-semibold mb-2 uppercase tracking-wider text-sm">
                        {{ $subtitle }}
                    </p>
                @endif
                @if($title)
                    <h2 class="text-2xl md:text-3xl font-bold text-base-content font-heading">
                        {{ $title }}
                    </h2>
                @endif
            </div>
        @endif
    </div>

    {{-- Infinite Carousel --}}
    @if(!empty($partners))
        <div class="relative">
            {{-- Gradient overlays for fade effect --}}
            <div class="absolute left-0 top-0 bottom-0 w-24 md:w-48 bg-gradient-to-r from-base-100 to-transparent z-10 pointer-events-none"></div>
            <div class="absolute right-0 top-0 bottom-0 w-24 md:w-48 bg-gradient-to-l from-base-100 to-transparent z-10 pointer-events-none"></div>

            {{-- Scrolling container --}}
            <div
                class="flex {{ $pauseOnHover ? 'hover:[animation-play-state:paused]' : '' }}"
                style="animation: {{ $direction === 'right' ? 'scroll-right' : 'scroll-left' }} {{ $animationDuration() }} linear infinite;"
            >
                {{-- First set of logos --}}
                @foreach($partners as $partner)
                    <div class="flex-shrink-0 px-6 md:px-10">
                        @if(!empty($partner['url']))
                            <a
                                href="{{ $partner['url'] }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="block grayscale hover:grayscale-0 opacity-60 hover:opacity-100 transition-all duration-300"
                                title="{{ $partner['name'] }}"
                            >
                                <img
                                    src="{{ $partner['logo'] }}"
                                    alt="{{ $partner['name'] }}"
                                    class="h-10 md:h-12 w-auto object-contain"
                                    loading="lazy"
                                >
                            </a>
                        @else
                            <div class="grayscale hover:grayscale-0 opacity-60 hover:opacity-100 transition-all duration-300" title="{{ $partner['name'] }}">
                                <img
                                    src="{{ $partner['logo'] }}"
                                    alt="{{ $partner['name'] }}"
                                    class="h-10 md:h-12 w-auto object-contain"
                                    loading="lazy"
                                >
                            </div>
                        @endif
                    </div>
                @endforeach

                {{-- Duplicate set for seamless loop --}}
                @foreach($partners as $partner)
                    <div class="flex-shrink-0 px-6 md:px-10" aria-hidden="true">
                        @if(!empty($partner['url']))
                            <a
                                href="{{ $partner['url'] }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="block grayscale hover:grayscale-0 opacity-60 hover:opacity-100 transition-all duration-300"
                                tabindex="-1"
                            >
                                <img
                                    src="{{ $partner['logo'] }}"
                                    alt=""
                                    class="h-10 md:h-12 w-auto object-contain"
                                    loading="lazy"
                                >
                            </a>
                        @else
                            <div class="grayscale hover:grayscale-0 opacity-60 hover:opacity-100 transition-all duration-300">
                                <img
                                    src="{{ $partner['logo'] }}"
                                    alt=""
                                    class="h-10 md:h-12 w-auto object-contain"
                                    loading="lazy"
                                >
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{ $slot }}
</section>

<style>
    @keyframes scroll-left {
        0% {
            transform: translateX(0);
        }
        100% {
            transform: translateX(-50%);
        }
    }

    @keyframes scroll-right {
        0% {
            transform: translateX(-50%);
        }
        100% {
            transform: translateX(0);
        }
    }
</style>
