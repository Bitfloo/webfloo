<section {{ $attributes->merge(['class' => 'py-16 lg:py-24 bg-gradient-to-b from-base-100 via-base-200 to-neutral']) }}>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-start">
            {{-- Left: Content --}}
            <div>
                {{-- Header with entrance animation --}}
                <div class="mb-10 intersect:motion-opacity-in-0 intersect:motion-translate-y-in-[20px] intersect-once">
                    @if($subtitle)
                        <p class="text-primary font-semibold mb-3 uppercase tracking-wider text-sm">
                            {{ $subtitle }}
                        </p>
                    @endif

                    <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-base-content font-heading mb-4">
                        {{ $title }}
                    </h2>

                    @if($description)
                        <p class="text-lg text-base-content/70 leading-relaxed">
                            {{ $description }}
                        </p>
                    @endif
                </div>

                {{-- Features Grid with staggered entrance --}}
                <div class="grid sm:grid-cols-2 gap-6 mb-10">
                    @foreach($features as $index => $feature)
                        <div class="flex gap-4 intersect:motion-opacity-in-0 intersect:motion-translate-y-in-[20px] intersect-once"
                             style="--motion-delay: {{ ($index + 1) * 100 }}ms">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
                                    <span class="icon-[tabler--{{ $feature['icon'] ?? 'star' }}] size-6 text-primary"></span>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-base-content mb-1">
                                    {{ $feature['title'] }}
                                </h3>
                                <p class="text-sm text-base-content/60 leading-relaxed">
                                    {{ $feature['description'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- CTA Button with entrance animation --}}
                @if($ctaText && $ctaHref)
                    <a href="{{ $ctaHref }}"
                       class="btn btn-primary btn-lg w-full intersect:motion-opacity-in-0 intersect:motion-scale-in-[0.95] intersect-once"
                       style="--motion-delay: 500ms">
                        {{ $ctaText }}
                        <span class="icon-[tabler--arrow-right] size-5"></span>
                    </a>
                @endif
            </div>

            {{-- Right: Image/Illustration with entrance animation --}}
            <div class="relative intersect:motion-opacity-in-0 intersect:motion-translate-x-in-[40px] intersect-once"
                 style="--motion-delay: 200ms">
                @if($image)
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl">
                        {{-- Skeleton placeholder --}}
                        <div class="absolute inset-0 skeleton skeleton-animated"></div>
                        <img
                            src="{{ $image }}"
                            alt="{{ $title }}"
                            class="relative w-full h-auto"
                            loading="lazy"
                            onload="this.previousElementSibling.remove()"
                        >
                    </div>
                @else
                    {{-- Abstract dashboard illustration - full height --}}
                    <div class="relative rounded-2xl overflow-hidden bg-gradient-to-br from-base-200 to-base-300 p-6 lg:p-8 shadow-2xl h-full min-h-[480px] lg:min-h-[520px]">
                        {{-- Mock dashboard UI --}}
                        <div class="flex flex-col h-full space-y-5">
                            {{-- Header bar --}}
                            <div class="flex items-center gap-3">
                                <div class="flex gap-1.5">
                                    <div class="w-3 h-3 rounded-full bg-error/60"></div>
                                    <div class="w-3 h-3 rounded-full bg-warning/60"></div>
                                    <div class="w-3 h-3 rounded-full bg-success/60"></div>
                                </div>
                                <div class="flex-1 h-5 bg-base-content/10 rounded"></div>
                            </div>

                            {{-- Main content area - flexes to fill space --}}
                            <div class="flex-1 grid grid-cols-3 gap-4">
                                <div class="col-span-2 flex flex-col gap-4">
                                    <div class="flex-1 bg-primary/20 rounded-xl min-h-[100px]"></div>
                                    <div class="h-20 bg-base-content/10 rounded-xl"></div>
                                    <div class="h-16 bg-base-content/5 rounded-xl"></div>
                                </div>
                                <div class="flex flex-col gap-4">
                                    <div class="h-16 bg-accent/30 rounded-xl"></div>
                                    <div class="flex-1 bg-secondary/20 rounded-xl"></div>
                                    <div class="h-14 bg-base-content/10 rounded-xl"></div>
                                    <div class="h-14 bg-primary/10 rounded-xl"></div>
                                </div>
                            </div>

                            {{-- Progress bars --}}
                            <div class="space-y-3">
                                <div class="h-2.5 bg-base-content/10 rounded-full">
                                    <div class="h-full w-4/5 bg-primary/60 rounded-full"></div>
                                </div>
                                <div class="h-2.5 bg-base-content/10 rounded-full">
                                    <div class="h-full w-3/5 bg-accent/60 rounded-full"></div>
                                </div>
                            </div>

                            {{-- Stats row --}}
                            <div class="grid grid-cols-3 gap-4">
                                <div class="text-center p-4 bg-base-content/5 rounded-xl border border-base-content/5">
                                    <div class="text-2xl lg:text-3xl font-bold text-primary">98%</div>
                                    <div class="text-xs text-base-content/50 mt-1">{{ __('Sukces') }}</div>
                                </div>
                                <div class="text-center p-4 bg-base-content/5 rounded-xl border border-base-content/5">
                                    <div class="text-2xl lg:text-3xl font-bold text-accent">24h</div>
                                    <div class="text-xs text-base-content/50 mt-1">{{ __('Czas') }}</div>
                                </div>
                                <div class="text-center p-4 bg-base-content/5 rounded-xl border border-base-content/5">
                                    <div class="text-2xl lg:text-3xl font-bold text-secondary">5★</div>
                                    <div class="text-xs text-base-content/50 mt-1">{{ __('Ocena') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                {{-- Decorative elements --}}
                <div class="absolute -z-10 -top-6 -right-6 w-72 h-72 bg-primary/5 rounded-full blur-3xl"></div>
                <div class="absolute -z-10 -bottom-6 -left-6 w-48 h-48 bg-accent/5 rounded-full blur-2xl"></div>
            </div>
        </div>
    </div>
</section>
