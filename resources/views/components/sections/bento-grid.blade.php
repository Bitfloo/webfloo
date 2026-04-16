<section {{ $attributes->merge(['class' => 'py-16 lg:py-24 bg-base-100 relative overflow-hidden']) }}>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section Header --}}
        @if($title || $subtitle || $description)
            <div class="text-center mb-12 lg:mb-16 intersect:motion-opacity-in-0 intersect:motion-translate-y-in-[20px] intersect-once">
                @if($subtitle)
                    <span class="badge badge-soft badge-primary mb-4">{{ $subtitle }}</span>
                @endif

                @if($title)
                    <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-base-content font-heading mb-4">
                        {{ $title }}
                    </h2>
                @endif

                @if($description)
                    <p class="text-lg text-base-content/70 max-w-2xl mx-auto">
                        {{ $description }}
                    </p>
                @endif
            </div>
        @endif

        {{-- Bento Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
            {{-- Card 1: Stats Card (Projects count with radial progress) --}}
            <div class="card bg-base-200 card-bordered levitate-card intersect:motion-opacity-in-0 intersect:motion-scale-in-[0.95] intersect-once"
                 style="--motion-delay: 100ms">
                <div class="card-body justify-between">
                    <div>
                        <p class="text-sm text-base-content/60 font-medium uppercase tracking-wider mb-2">{{ __('Realizacje') }}</p>
                        <p class="text-5xl lg:text-6xl font-bold text-base-content">{{ $statsCard['value'] }}</p>
                        <p class="text-base-content/70 mt-1">{{ $statsCard['label'] }}</p>
                    </div>
                    {{-- Radial Progress (CSS-only) --}}
                    <div class="flex justify-end mt-4">
                        <div class="relative size-20">
                            <svg class="size-full -rotate-90" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="18" cy="18" r="16" fill="none" class="stroke-current text-base-300" stroke-width="3"></circle>
                                <circle cx="18" cy="18" r="16" fill="none" class="stroke-current text-primary" stroke-width="3"
                                        stroke-dasharray="100" stroke-dashoffset="{{ 100 - ($statsCard['progress'] ?? 85) }}"
                                        stroke-linecap="round"></circle>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-sm font-bold text-primary">{{ $statsCard['progress'] ?? 85 }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 2: Team Card (Avatar stack + experience) --}}
            <div class="card bg-primary/10 card-bordered border-primary/20 levitate-card intersect:motion-opacity-in-0 intersect:motion-scale-in-[0.95] intersect-once"
                 style="--motion-delay: 150ms">
                <div class="card-body justify-between">
                    <div>
                        <p class="text-sm text-primary font-medium uppercase tracking-wider mb-2">{{ __('Zespół') }}</p>
                        <p class="text-2xl lg:text-3xl font-bold text-base-content">{{ $teamCard['title'] }}</p>
                        @if(!empty($teamCard['subtitle']))
                            <p class="text-base-content/70 mt-1">{{ $teamCard['subtitle'] }}</p>
                        @endif
                    </div>
                    <div class="flex items-center justify-between mt-6">
                        {{-- Avatar Stack --}}
                        <div class="avatar-group -space-x-4">
                            @if(!empty($teamCard['avatars']))
                                @foreach(array_slice($teamCard['avatars'], 0, 4) as $avatar)
                                    <div class="avatar">
                                        <div class="size-10 rounded-full ring-2 ring-base-100">
                                            <img src="{{ $avatar }}" alt="Team member" loading="lazy">
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                {{-- Placeholder avatars with initials --}}
                                @foreach(['M', 'A', 'K', 'P'] as $initial)
                                    <div class="avatar placeholder">
                                        <div class="size-10 rounded-full bg-primary text-primary-content ring-2 ring-base-100">
                                            <span class="text-sm font-medium">{{ $initial }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        {{-- Stat badge --}}
                        <span class="badge badge-primary badge-soft">{{ $teamCard['stat'] }}</span>
                    </div>
                </div>
            </div>

            {{-- Card 3: Tech Card (Large, spans 2 cols on lg) --}}
            <div class="card bg-gradient-to-br from-base-200 via-base-200 to-primary/5 card-bordered levitate-card md:col-span-2 lg:col-span-1 lg:row-span-2 intersect:motion-opacity-in-0 intersect:motion-scale-in-[0.95] intersect-once"
                 style="--motion-delay: 200ms">
                <div class="card-body h-full justify-between">
                    <div>
                        <p class="text-sm text-primary font-medium uppercase tracking-wider mb-2">{{ __('Technologie') }}</p>
                        <p class="text-2xl lg:text-3xl font-bold text-base-content">{{ $techCard['title'] }}</p>
                        @if(!empty($techCard['subtitle']))
                            <p class="text-base-content/70 mt-1">{{ $techCard['subtitle'] }}</p>
                        @endif
                    </div>
                    {{-- Tech visualization --}}
                    <div class="flex-1 flex items-center justify-center py-8">
                        @if(!empty($techCard['image']))
                            <img src="{{ $techCard['image'] }}" alt="{{ $techCard['title'] }}" class="max-h-48 object-contain" loading="lazy">
                        @else
                            {{-- Decorative tech grid --}}
                            <div class="relative w-full max-w-xs">
                                <div class="grid grid-cols-3 gap-3">
                                    @foreach(['laravel', 'brand-react', 'brand-vue', 'brand-tailwind', 'brand-php', 'database'] as $icon)
                                        <div class="aspect-square rounded-xl bg-base-100/80 border border-base-300/50 flex items-center justify-center group hover:bg-primary/10 hover:border-primary/30 transition-colors">
                                            <span class="icon-[tabler--{{ $icon }}] size-8 text-base-content/40 group-hover:text-primary transition-colors"></span>
                                        </div>
                                    @endforeach
                                </div>
                                {{-- Decorative glow --}}
                                <div class="absolute inset-0 bg-gradient-to-t from-primary/5 to-transparent rounded-2xl pointer-events-none"></div>
                            </div>
                        @endif
                    </div>
                    {{-- Tech tags --}}
                    <div class="flex flex-wrap gap-2">
                        @foreach(['PHP 8.4', 'Laravel 12', 'Filament v5', 'Livewire 3'] as $tech)
                            <span class="badge badge-outline badge-sm">{{ $tech }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Card 4: Clients Card (Satisfaction rate) --}}
            <div class="card bg-accent/10 card-bordered border-accent/20 levitate-card intersect:motion-opacity-in-0 intersect:motion-scale-in-[0.95] intersect-once"
                 style="--motion-delay: 250ms">
                <div class="card-body">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm text-accent font-medium uppercase tracking-wider mb-2">{{ __('Klienci') }}</p>
                            <p class="text-5xl lg:text-6xl font-bold text-base-content">{{ $clientsCard['value'] }}</p>
                            <p class="text-base-content/70 mt-1">{{ $clientsCard['label'] }}</p>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="size-14 rounded-full bg-accent/20 flex items-center justify-center">
                                <span class="icon-[tabler--heart-filled] size-7 text-accent"></span>
                            </div>
                        </div>
                    </div>
                    {{-- Star rating --}}
                    <div class="flex items-center gap-1 mt-4">
                        @for($i = 0; $i < 5; $i++)
                            <span class="icon-[tabler--star-filled] size-5 text-warning"></span>
                        @endfor
                        <span class="text-sm text-base-content/60 ml-2">{{ __('5.0 na podstawie opinii') }}</span>
                    </div>
                </div>
            </div>

            {{-- Card 5: Brand/Quote Card --}}
            <div class="card bg-base-200 card-bordered levitate-card intersect:motion-opacity-in-0 intersect:motion-scale-in-[0.95] intersect-once"
                 style="--motion-delay: 300ms">
                <div class="card-body justify-center text-center">
                    {{-- Logo or Brand icon --}}
                    @if(!empty($brandCard['logo']))
                        <img src="{{ $brandCard['logo'] }}" alt="Bitfloo" class="h-10 mx-auto mb-4" loading="lazy">
                    @else
                        <div class="flex items-center justify-center gap-2 mb-4">
                            <div class="size-10 rounded-lg bg-primary flex items-center justify-center">
                                <span class="icon-[tabler--code] size-6 text-primary-content"></span>
                            </div>
                            <span class="text-2xl font-bold text-base-content">Bitfloo</span>
                        </div>
                    @endif
                    {{-- Quote --}}
                    <blockquote class="text-lg lg:text-xl font-medium text-base-content/80 italic">
                        "{{ $brandCard['quote'] }}"
                    </blockquote>
                </div>
            </div>

            {{-- Card 6: Growth Card (Simple chart) --}}
            <div class="card bg-base-200 card-bordered levitate-card intersect:motion-opacity-in-0 intersect:motion-scale-in-[0.95] intersect-once"
                 style="--motion-delay: 350ms">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-sm text-base-content/60 font-medium uppercase tracking-wider mb-1">{{ __('Wzrost') }}</p>
                            <p class="text-xl font-bold text-base-content">{{ $growthCard['title'] }}</p>
                        </div>
                        <span class="badge badge-success badge-soft">
                            <span class="icon-[tabler--trending-up] size-4 mr-1"></span>
                            +25%
                        </span>
                    </div>
                    {{-- Simple CSS bar chart --}}
                    <div class="flex items-end gap-1 h-20 mt-auto">
                        @php
                            $chartData = $growthCard['data'] ?? [20, 35, 45, 55, 70, 85, 95];
                            $maxValue = max($chartData);
                        @endphp
                        @foreach($chartData as $index => $value)
                            @php
                                $height = ($value / $maxValue) * 100;
                                $isLast = $index === count($chartData) - 1;
                            @endphp
                            <div class="flex-1 rounded-t transition-all duration-300 {{ $isLast ? 'bg-primary' : 'bg-primary/40' }}"
                                 style="height: {{ $height }}%"></div>
                        @endforeach
                    </div>
                    {{-- Chart labels --}}
                    <div class="flex justify-between text-xs text-base-content/40 mt-2">
                        <span>{{ __('Sty') }}</span>
                        <span>{{ __('Lip') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Additional slot for custom content --}}
        {{ $slot }}
    </div>

    {{-- Decorative background elements --}}
    <div class="absolute -z-10 top-1/4 -left-32 w-64 h-64 bg-primary/5 rounded-full blur-3xl"></div>
    <div class="absolute -z-10 bottom-1/4 -right-32 w-64 h-64 bg-accent/5 rounded-full blur-3xl"></div>
</section>
