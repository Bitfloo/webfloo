@php
    $tag = $isClickable() ? 'a' : 'div';
@endphp

<{{ $tag }}
    {{ $attributes->merge(['class' => 'card bg-base-100 shadow-sm hover:shadow-xl transition-all duration-500 group overflow-hidden']) }}
    @if($isClickable()) href="{{ $url }}" @endif
    @if($category) data-category="{{ $category }}" @endif
>
    {{-- Image Container --}}
    <figure class="relative aspect-[16/10] overflow-hidden">
        @if($hasImage())
            {{-- Skeleton placeholder (shown until image loads) --}}
            <div class="absolute inset-0 skeleton"></div>
            <img
                src="{{ $image }}"
                alt="{{ $title }}"
                class="relative w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-110"
                loading="lazy"
                onload="this.previousElementSibling.remove()"
            >
        @else
            {{-- Placeholder for missing image --}}
            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-base-200 to-base-300">
                <span class="icon-[tabler--photo] size-16 text-base-content/20" aria-hidden="true"></span>
            </div>
        @endif

        {{-- Hover Overlay --}}
        <div class="absolute inset-0 bg-gradient-to-t from-neutral/90 via-neutral/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
            {{-- Centered View Button --}}
            @if($isClickable())
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="btn btn-sm bg-base-100/95 text-base-content transform translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-500 delay-100">
                        <span>{{ $linkText }}</span>
                        <span class="icon-[tabler--arrow-right] size-4" aria-hidden="true"></span>
                    </span>
                </div>
            @endif

            {{-- Technologies on overlay (bottom) --}}
            @if($hasTechnologies())
                <div class="absolute bottom-4 left-4 right-4">
                    <div class="flex flex-wrap gap-1.5 transform translate-y-2 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-500 delay-150">
                        @foreach(array_slice($technologies, 0, 4) as $tech)
                            <span class="badge badge-sm bg-base-100/20 text-neutral-content border-0 backdrop-blur-sm">
                                {{ $tech }}
                            </span>
                        @endforeach
                        @if(count($technologies) > 4)
                            <span class="badge badge-sm bg-base-100/20 text-neutral-content border-0 backdrop-blur-sm">
                                +{{ count($technologies) - 4 }}
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Category Badge (always visible) --}}
        @if($category)
            <div class="absolute top-4 left-4">
                <span class="badge badge-primary shadow-md">
                    {{ $category }}
                </span>
            </div>
        @endif
    </figure>

    {{-- Content Area --}}
    <div class="card-body">
        <h3 class="card-title text-lg group-hover:text-primary transition-colors duration-300 line-clamp-1">
            {{ $title }}
        </h3>

        @if($excerpt)
            <p class="text-base-content/70 text-sm leading-relaxed line-clamp-2">
                {{ $excerpt }}
            </p>
        @endif

        {{-- Technologies (visible when not hovering, mobile-friendly) --}}
        @if($hasTechnologies())
            <div class="mt-3 flex flex-wrap gap-1.5 md:hidden">
                @foreach(array_slice($technologies, 0, 3) as $tech)
                    <span class="badge badge-sm badge-soft {{ \Webfloo\Components\Molecules\ProjectCard::getTechColor($tech) }}">
                        {{ $tech }}
                    </span>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Slot for additional content --}}
    {{ $slot }}
</{{ $tag }}>
