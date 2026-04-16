@php
    $headerId = 'header-' . uniqid();
    $mobileMenuId = 'mobile-menu-' . uniqid();
@endphp

<header
    {{ $attributes->merge(['class' => 'navbar bg-base-100/70 backdrop-blur-[3.5px] border-b border-white/5 ' . ($sticky ? 'fixed top-0 left-0 right-0 z-50' : '')]) }}
>
    <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-12 w-full">
            {{-- Logo --}}
            <div class="navbar-start">
                <a href="/" class="flex-shrink-0">
                    @if($logo = setting('logo'))
                        <img src="{{ $logo }}" alt="{{ setting('site_name', 'Bitfloo') }}" class="h-7">
                    @else
                        <span class="text-lg font-bold text-primary">{{ setting('site_name', 'Bitfloo') }}</span>
                    @endif
                </a>
            </div>

            {{-- Desktop Navigation --}}
            <nav class="navbar-center hidden lg:flex">
                <ul class="flex items-center gap-1">
                    @foreach($navigation as $item)
                        <li>
                            <a href="{{ $item['href'] ?? '#' }}" class="nav-link">
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            {{-- CTA & Mobile Toggle --}}
            <div class="navbar-end gap-2">
                @if($ctaText && $ctaHref)
                    <x-webfloo-button :href="$ctaHref" variant="primary" size="sm" class="hidden lg:inline-flex">
                        {{ $ctaText }}
                    </x-webfloo-button>
                @endif

                {{-- Mobile Menu Button --}}
                <button
                    type="button"
                    class="btn btn-square btn-ghost btn-sm lg:hidden"
                    aria-haspopup="dialog"
                    aria-expanded="false"
                    aria-controls="{{ $mobileMenuId }}"
                    data-overlay="#{{ $mobileMenuId }}"
                    aria-label="{{ __('Otwórz menu') }}"
                >
                    <span class="icon-[tabler--menu-2] size-5" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
</header>

{{-- Mobile Menu Drawer --}}
<div
    id="{{ $mobileMenuId }}"
    class="overlay modal overlay-open:opacity-100 hidden"
    role="dialog"
    tabindex="-1"
>
    <div class="modal-dialog overlay-open:opacity-100 overlay-open:translate-x-0 translate-x-full fixed right-0 top-0 h-full w-80 max-w-full bg-base-100 transition-all duration-300">
        <div class="modal-content h-full flex flex-col">
            {{-- Header --}}
            <div class="flex items-center justify-between p-4 border-b border-base-content/10">
                <span class="text-lg font-semibold">{{ __('Menu') }}</span>
                <button
                    type="button"
                    class="btn btn-text btn-circle btn-sm"
                    aria-label="{{ __('Zamknij') }}"
                    data-overlay="#{{ $mobileMenuId }}"
                >
                    <span class="icon-[tabler--x] size-5" aria-hidden="true"></span>
                </button>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto p-4">
                <ul class="menu">
                    @foreach($navigation as $item)
                        <li>
                            <a
                                href="{{ $item['href'] ?? '#' }}"
                                class="text-base-content hover:text-primary py-3"
                                data-overlay="#{{ $mobileMenuId }}"
                            >
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                @if($ctaText && $ctaHref)
                    <div class="mt-6">
                        <x-webfloo-button
                            :href="$ctaHref"
                            variant="primary"
                            class="w-full"
                            data-overlay="#{{ $mobileMenuId }}"
                        >
                            {{ $ctaText }}
                        </x-webfloo-button>
                    </div>
                @endif
            </nav>
        </div>
    </div>
</div>
