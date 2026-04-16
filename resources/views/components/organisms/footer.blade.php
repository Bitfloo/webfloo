<footer {{ $attributes->merge(['class' => 'bg-neutral text-neutral-content']) }}>
    {{-- Main Footer --}}
    <div class="footer bg-neutral p-10 max-w-7xl mx-auto">
        {{-- Brand + Newsletter Column --}}
        <aside class="gap-4 max-w-xs">
            <a href="/" class="flex items-center gap-2 text-xl font-bold hover:opacity-80 transition-opacity">
                <span class="icon-[tabler--code] size-7 text-primary" aria-hidden="true"></span>
                <span>{{ setting('site_name', 'Bitfloo') }}</span>
            </a>
            <p class="text-neutral-content/60 text-sm leading-relaxed">
                {{ setting('site_description', 'Nowoczesne rozwiązania IT dla Twojej firmy') }}
            </p>
            {{-- Newsletter --}}
            <fieldset class="mt-2">
                <label class="text-neutral-content/60 text-sm mb-2 block">Newsletter</label>
                {{-- Newsletter form provided via Inertia (Vue) --}}
            </fieldset>
        </aside>

        {{-- Navigation Columns --}}
        @if($footerNav = $getNavigation())
            @foreach($footerNav as $section)
                <nav class="text-neutral-content">
                    <h6 class="footer-title opacity-100">{{ $section['title'] }}</h6>
                    @foreach($section['links'] ?? [] as $link)
                        <a href="{{ $link['href'] }}" class="link link-hover text-neutral-content/60">{{ $link['label'] }}</a>
                    @endforeach
                </nav>
            @endforeach
        @endif

        {{-- Contact Column --}}
        <nav class="text-neutral-content">
            <h6 class="footer-title opacity-100">{{ __('Kontakt') }}</h6>
            @if($email = setting('contact_email'))
                <a href="mailto:{{ $email }}" class="link link-hover text-neutral-content/60 flex items-center gap-2">
                    <span class="icon-[tabler--mail] size-4 text-primary" aria-hidden="true"></span>
                    {{ $email }}
                </a>
            @endif
            @if($phone = setting('contact_phone'))
                <a href="tel:{{ $phone }}" class="link link-hover text-neutral-content/60 flex items-center gap-2">
                    <span class="icon-[tabler--phone] size-4 text-primary" aria-hidden="true"></span>
                    {{ $phone }}
                </a>
            @endif
            @if($address = setting('contact_address'))
                <span class="text-neutral-content/60 flex items-start gap-2">
                    <span class="icon-[tabler--map-pin] size-4 text-primary mt-0.5" aria-hidden="true"></span>
                    <span class="leading-relaxed">{!! nl2br(e(str_replace('\n', "\n", $address))) !!}</span>
                </span>
            @endif
        </nav>
    </div>

    {{-- Logo Cloud - Technologies & Partners --}}
    <div class="border-t border-neutral-content/10 py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section header --}}
        <div class="text-center mb-8">
            <p class="text-xs font-medium uppercase tracking-widest text-neutral-content/40 mb-1">
                {{ __('Technologie & Partnerzy') }}
            </p>
            <h3 class="text-base font-medium text-neutral-content/60">
                {{ __('Budujemy z najlepszymi narzędziami') }}
            </h3>
        </div>

        {{-- Logo cloud - organic layout --}}
        <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-4 sm:gap-x-8 sm:gap-y-5 md:gap-x-12 md:gap-y-6 mb-10 max-w-5xl mx-auto">
            {{-- Laravel - hero size --}}
            <a href="https://laravel.com" target="_blank" rel="noopener noreferrer"
               class="group flex items-center justify-center p-2 opacity-40 grayscale hover:opacity-100 hover:grayscale-0 transition-all duration-300 hover:scale-110"
               title="Laravel">
                <svg class="h-9 w-auto" viewBox="0 0 50 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M49.626 11.564a.809.809 0 01.028.209v10.972a.8.8 0 01-.402.694l-9.209 5.302V39.25c0 .286-.152.55-.4.694L20.42 51.01c-.044.025-.092.041-.14.058-.018.006-.035.017-.054.022a.805.805 0 01-.41 0c-.022-.006-.042-.018-.063-.026-.044-.016-.09-.03-.132-.054L.402 39.944A.801.801 0 010 39.25V6.334c0-.072.01-.142.028-.21.006-.023.02-.044.028-.067.015-.042.029-.085.051-.124.015-.026.037-.047.055-.071.023-.032.044-.065.071-.093.023-.023.053-.04.079-.06.029-.024.055-.05.088-.069h.001l9.61-5.533a.802.802 0 01.8 0l9.61 5.533h.002c.032.02.059.045.088.068.026.02.055.038.078.06.028.029.048.062.072.094.017.024.04.045.054.071.023.04.036.082.052.124.008.023.022.044.028.068a.809.809 0 01.028.209v20.559l8.008-4.611V11.773c0-.072.01-.142.028-.21.007-.024.02-.045.028-.068.016-.042.03-.085.052-.124.015-.026.037-.047.054-.071.024-.032.044-.065.072-.093.023-.023.052-.04.078-.06.03-.024.056-.05.088-.069h.001l9.611-5.533a.801.801 0 01.8 0l9.61 5.533c.034.02.06.045.09.068.025.02.054.038.077.06.028.029.048.062.072.094.018.024.04.045.054.071.023.039.036.082.052.124.009.023.022.044.028.068zm-1.574 10.718V12.99l-3.365 1.938-4.643 2.673v9.293l8.008-4.612zm-9.61 16.505v-9.302l-4.57 2.611-12.85 7.344v9.397l17.42-10.05zM1.602 7.267v31.529L19.022 48.85v-9.397l-9.205-5.208-.003-.002-.004-.002c-.031-.018-.057-.044-.086-.066-.025-.02-.052-.035-.074-.057l-.002-.003c-.026-.025-.044-.056-.066-.084-.02-.027-.044-.05-.06-.078l-.001-.003c-.018-.03-.029-.066-.042-.1-.013-.03-.03-.058-.038-.09v-.001c-.01-.038-.012-.078-.016-.117-.004-.03-.012-.06-.012-.09v-21.5L4.968 9.205 1.602 7.267zm8.81-5.994L2.405 5.535l8.005 4.609 8.006-4.61-8.006-4.26zm4.164 28.764l4.645-2.674V7.267l-3.363 1.938-4.646 2.673v20.096l3.364-1.937zM39.243 7.164l-8.006 4.609 8.006 4.609 8.005-4.61-8.005-4.608zm-.801 10.605l-4.644-2.673-3.364-1.938v9.293l4.644 2.674 3.364 1.937v-9.293zm-18.219 20.15L32.19 30.676l5.022-2.87-8.003-4.607-9.208 5.302-8.179 4.71 8.2 4.708z" fill="#FF2D20"/>
                </svg>
            </a>

            {{-- PHP - larger --}}
            <a href="https://php.net" target="_blank" rel="noopener noreferrer"
               class="group flex items-center justify-center p-2 opacity-40 grayscale hover:opacity-100 hover:grayscale-0 transition-all duration-300 hover:scale-110"
               title="PHP">
                <svg class="h-8 w-auto" viewBox="0 0 100 52" xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="50" cy="26" rx="50" ry="26" fill="#8892BF"/>
                    <path d="M30.5 17h5.8c4.2 0 6.2 2 5.5 5.6-.9 4.7-3.8 7.2-8.2 7.2h-3.1l-1.2 6.2h-4.4l5.6-19zm3.2 9.2h1.9c2.2 0 3.6-1.1 4-3.2.3-1.8-.6-2.7-2.7-2.7h-2l-1.2 5.9zM50 17h4.4l-.8 4h3.2c2.5 0 4.1.5 4.8 1.4.7 1 .8 2.5.4 4.6l-1.5 8h-4.5l1.4-7.4c.2-1 .2-1.7 0-2-.2-.4-.7-.5-1.5-.5h-2.7l-2 9.9h-4.4L50 17zM68.5 17h5.8c4.2 0 6.2 2 5.5 5.6-.9 4.7-3.8 7.2-8.2 7.2h-3.1l-1.2 6.2h-4.4l5.6-19zm3.2 9.2h1.9c2.2 0 3.6-1.1 4-3.2.3-1.8-.6-2.7-2.7-2.7h-2l-1.2 5.9z" fill="#fff"/>
                </svg>
            </a>

            {{-- JavaScript --}}
            <a href="https://developer.mozilla.org/en-US/docs/Web/JavaScript" target="_blank" rel="noopener noreferrer"
               class="group flex items-center justify-center p-2 opacity-40 grayscale hover:opacity-100 hover:grayscale-0 transition-all duration-300 hover:scale-110"
               title="JavaScript">
                <svg class="h-7 w-auto" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                    <rect width="48" height="48" rx="4" fill="#F7DF1E"/>
                    <path d="M12.5 38.8l3.7-2.3c.7 1.3 1.4 2.3 2.9 2.3 1.5 0 2.4-.6 2.4-2.8V21h4.6v15.1c0 4.6-2.7 6.7-6.7 6.7-3.6 0-5.7-1.9-6.8-4.1m16.1-.4l3.7-2.2c1 1.6 2.2 2.8 4.4 2.8 1.9 0 3.1-.9 3.1-2.2 0-1.5-1.2-2.1-3.3-3l-1.1-.5c-3.3-1.4-5.5-3.2-5.5-6.9 0-3.4 2.6-6.1 6.7-6.1 2.9 0 5 1 6.5 3.6l-3.5 2.3c-.8-1.4-1.6-2-2.9-2-1.3 0-2.2.9-2.2 2 0 1.4.9 2 2.8 2.9l1.1.5c3.9 1.7 6.1 3.4 6.1 7.2 0 4.1-3.2 6.4-7.6 6.4-4.2 0-7-2-8.4-4.6" fill="#000"/>
                </svg>
            </a>

            {{-- TypeScript --}}
            <a href="https://typescriptlang.org" target="_blank" rel="noopener noreferrer"
               class="group flex items-center justify-center p-2 opacity-40 grayscale hover:opacity-100 hover:grayscale-0 transition-all duration-300 hover:scale-110"
               title="TypeScript">
                <svg class="h-7 w-auto" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                    <rect width="48" height="48" rx="4" fill="#3178C6"/>
                    <path d="M11 26h6v2h-1.8v10h-2.4V28H11v-2zm8.8 0h7.4v2h-2.5v10h-2.4V28h-2.5v-2zm18.5 3.5c0-.8-.3-1.4-.8-1.8-.5-.4-1.4-.8-2.6-1.1-1.6-.4-2.9-1-3.8-1.7-.9-.7-1.4-1.7-1.4-2.9 0-1.3.5-2.3 1.5-3.1 1-.8 2.3-1.2 3.9-1.2 1.7 0 3.1.4 4.1 1.3 1 .9 1.5 2 1.4 3.3h-2.5c0-.7-.2-1.3-.7-1.7-.5-.4-1.2-.6-2.2-.6-.9 0-1.6.2-2.1.5-.5.4-.7.8-.7 1.4 0 .5.3 1 .9 1.4.6.4 1.5.7 2.8 1 1.6.4 2.8.9 3.6 1.7.9.8 1.3 1.8 1.3 3 0 1.3-.5 2.4-1.6 3.1-1 .8-2.4 1.1-4.1 1.1-1.7 0-3.2-.5-4.3-1.4-1.1-.9-1.7-2.1-1.6-3.6h2.5c0 1 .3 1.7.9 2.2.6.4 1.4.7 2.5.7.9 0 1.7-.2 2.2-.5.5-.4.8-.9.8-1.6z" fill="#fff"/>
                </svg>
            </a>

            {{-- Tailwind CSS - larger --}}
            <a href="https://tailwindcss.com" target="_blank" rel="noopener noreferrer"
               class="group flex items-center justify-center p-2 opacity-40 grayscale hover:opacity-100 hover:grayscale-0 transition-all duration-300 hover:scale-110"
               title="Tailwind CSS">
                <svg class="h-7 w-auto" viewBox="0 0 54 33" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M27 0c-7.2 0-11.7 3.6-13.5 10.8 2.7-3.6 5.85-4.95 9.45-4.05 2.054.514 3.522 2.004 5.147 3.653C30.744 13.09 33.808 16.2 40.5 16.2c7.2 0 11.7-3.6 13.5-10.8-2.7 3.6-5.85 4.95-9.45 4.05-2.054-.514-3.522-2.004-5.147-3.653C36.756 3.11 33.692 0 27 0zM13.5 16.2C6.3 16.2 1.8 19.8 0 27c2.7-3.6 5.85-4.95 9.45-4.05 2.054.514 3.522 2.004 5.147 3.653C17.244 29.29 20.308 32.4 27 32.4c7.2 0 11.7-3.6 13.5-10.8-2.7 3.6-5.85 4.95-9.45 4.05-2.054-.514-3.522-2.004-5.147-3.653C23.256 19.31 20.192 16.2 13.5 16.2z" fill="#06B6D4"/>
                </svg>
            </a>

            {{-- Vue.js --}}
            <a href="https://vuejs.org" target="_blank" rel="noopener noreferrer"
               class="group flex items-center justify-center p-2 opacity-40 grayscale hover:opacity-100 hover:grayscale-0 transition-all duration-300 hover:scale-110"
               title="Vue.js">
                <svg class="h-7 w-auto" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                    <path d="M29.4 4H39l-15 26L9 4h14.7l.3.5.3-.5h5.1z" fill="#41B883"/>
                    <path d="M24 17.3L17.7 7H9l15 26 15-26h-8.7L24 17.3z" fill="#41B883"/>
                    <path d="M24 17.3L29.4 7h-10.8L24 17.3z" fill="#35495E"/>
                    <path d="M29.4 4L24 13.3 18.6 4h-4.2L24 21l9.6-17h-4.2z" fill="#35495E"/>
                </svg>
            </a>

            {{-- React --}}
            <a href="https://react.dev" target="_blank" rel="noopener noreferrer"
               class="group flex items-center justify-center p-2 opacity-40 grayscale hover:opacity-100 hover:grayscale-0 transition-all duration-300 hover:scale-110"
               title="React">
                <svg class="h-7 w-auto" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="24" cy="24" r="4" fill="#61DAFB"/>
                    <ellipse cx="24" cy="24" rx="20" ry="8" stroke="#61DAFB" stroke-width="2" fill="none"/>
                    <ellipse cx="24" cy="24" rx="20" ry="8" stroke="#61DAFB" stroke-width="2" fill="none" transform="rotate(60 24 24)"/>
                    <ellipse cx="24" cy="24" rx="20" ry="8" stroke="#61DAFB" stroke-width="2" fill="none" transform="rotate(120 24 24)"/>
                </svg>
            </a>

            {{-- Filament - hero size --}}
            <a href="https://filamentphp.com" target="_blank" rel="noopener noreferrer"
               class="group flex items-center justify-center p-2 opacity-40 grayscale hover:opacity-100 hover:grayscale-0 transition-all duration-300 hover:scale-110"
               title="Filament">
                <svg class="h-9 w-auto" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M24 4C12.954 4 4 12.954 4 24s8.954 20 20 20 20-8.954 20-20S35.046 4 24 4z" fill="#FDAE4B"/>
                    <path d="M24 10c-7.732 0-14 6.268-14 14s6.268 14 14 14 14-6.268 14-14-6.268-14-14-14z" fill="#F59E0B"/>
                    <path d="M24 16c-4.418 0-8 3.582-8 8s3.582 8 8 8 8-3.582 8-8-3.582-8-8-8z" fill="#fff"/>
                </svg>
            </a>

            {{-- Livewire --}}
            <a href="https://livewire.laravel.com" target="_blank" rel="noopener noreferrer"
               class="group flex items-center justify-center p-2 opacity-40 grayscale hover:opacity-100 hover:grayscale-0 transition-all duration-300 hover:scale-110"
               title="Livewire">
                <svg class="h-7 w-auto" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M25 2C12.297 2 2 12.297 2 25s10.297 23 23 23 23-10.297 23-23S37.703 2 25 2z" fill="#FB70A9"/>
                    <path d="M20 15c-2.761 0-5 4.477-5 10s2.239 10 5 10 5-4.477 5-10-2.239-10-5-10zM32 15c-2.761 0-5 4.477-5 10s2.239 10 5 10 5-4.477 5-10-2.239-10-5-10z" fill="#fff"/>
                    <circle cx="20" cy="22" r="2" fill="#4E56A6"/>
                    <circle cx="32" cy="22" r="2" fill="#4E56A6"/>
                </svg>
            </a>

            {{-- Alpine.js --}}
            <a href="https://alpinejs.dev" target="_blank" rel="noopener noreferrer"
               class="group flex items-center justify-center p-2 opacity-40 grayscale hover:opacity-100 hover:grayscale-0 transition-all duration-300 hover:scale-110"
               title="Alpine.js">
                <svg class="h-7 w-auto" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                    <path d="M24 4L4 24l10 10L24 24l10 10 10-10L24 4z" fill="#77C1D2"/>
                    <path d="M24 24L14 34l10 10 10-10-10-10z" fill="#2D3441"/>
                </svg>
            </a>

            {{-- MySQL --}}
            <a href="https://mysql.com" target="_blank" rel="noopener noreferrer"
               class="group flex items-center justify-center p-2 opacity-40 grayscale hover:opacity-100 hover:grayscale-0 transition-all duration-300 hover:scale-110"
               title="MySQL">
                <svg class="h-7 w-auto" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="24" cy="24" rx="20" ry="12" fill="#00758F"/>
                    <path d="M12 24c0-4.4 5.4-8 12-8s12 3.6 12 8" stroke="#F29111" stroke-width="3" fill="none"/>
                    <text x="24" y="28" text-anchor="middle" fill="#fff" font-size="10" font-weight="bold">SQL</text>
                </svg>
            </a>

            {{-- PostgreSQL --}}
            <a href="https://postgresql.org" target="_blank" rel="noopener noreferrer"
               class="group flex items-center justify-center p-2 opacity-40 grayscale hover:opacity-100 hover:grayscale-0 transition-all duration-300 hover:scale-110"
               title="PostgreSQL">
                <svg class="h-7 w-auto" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                    <path d="M36 16c0-8-5.4-12-12-12S12 8 12 16c0 6 3 10 3 16 0 2-1 4-1 6h20c0-2-1-4-1-6 0-6 3-10 3-16z" fill="#336791"/>
                    <ellipse cx="24" cy="14" rx="8" ry="6" fill="#fff"/>
                    <circle cx="24" cy="14" r="3" fill="#336791"/>
                </svg>
            </a>

            {{-- Redis --}}
            <a href="https://redis.io" target="_blank" rel="noopener noreferrer"
               class="group flex items-center justify-center p-2 opacity-40 grayscale hover:opacity-100 hover:grayscale-0 transition-all duration-300 hover:scale-110"
               title="Redis">
                <svg class="h-7 w-auto" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M46 22c0 4-9.4 8-22 8S2 26 2 22s9.4-8 22-8 22 4 22 8z" fill="#912626"/>
                    <path d="M24 8C11.4 8 2 12 2 16v8c0 4 9.4 8 22 8s22-4 22-8v-8c0-4-9.4-8-22-8z" fill="#C6302B"/>
                    <ellipse cx="24" cy="16" rx="22" ry="8" fill="#912626"/>
                </svg>
            </a>

            {{-- Docker - larger --}}
            <a href="https://docker.com" target="_blank" rel="noopener noreferrer"
               class="group flex items-center justify-center p-2 opacity-40 grayscale hover:opacity-100 hover:grayscale-0 transition-all duration-300 hover:scale-110"
               title="Docker">
                <svg class="h-8 w-auto" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                    <path d="M44 20c-.8-3-3.2-4.4-6.4-4.4-.6 0-1.2.1-1.8.2-.4-1.4-1.4-2.6-2.8-3.4l-.6-.4-.4.6c-.8 1.2-1.2 2.6-1.2 4 0 .8.1 1.6.4 2.4-1.8.8-3.8.8-5.6.8H4c-.4 2.6-.4 5.4.4 8 1 3 3 5.6 5.8 7.2 3 1.8 7 2.6 10.6 2.6 9.4 0 17-4.2 20.4-13.2 2 0 4.4 0 5.8-2l.4-.6-.4-.6c-1-.8-2.4-1.2-3-1.2z" fill="#2496ED"/>
                    <g fill="#fff">
                        <rect x="20" y="20" width="4" height="4"/>
                        <rect x="26" y="20" width="4" height="4"/>
                        <rect x="32" y="20" width="4" height="4"/>
                        <rect x="20" y="14" width="4" height="4"/>
                        <rect x="26" y="14" width="4" height="4"/>
                        <rect x="32" y="14" width="4" height="4"/>
                        <rect x="14" y="20" width="4" height="4"/>
                        <rect x="26" y="8" width="4" height="4"/>
                    </g>
                </svg>
            </a>

            {{-- Git --}}
            <a href="https://git-scm.com" target="_blank" rel="noopener noreferrer"
               class="group flex items-center justify-center p-2 opacity-40 grayscale hover:opacity-100 hover:grayscale-0 transition-all duration-300 hover:scale-110"
               title="Git">
                <svg class="h-7 w-auto" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                    <path d="M46.3 21.8L26.2 1.7c-.9-.9-2.4-.9-3.3 0L18 6.6l4.2 4.2c1-.3 2.1-.1 2.9.7.8.8 1 2 .6 3l4 4c1-.4 2.2-.2 3 .6 1.2 1.2 1.2 3 0 4.2-1.2 1.2-3 1.2-4.2 0-.9-.9-1.1-2.1-.6-3.1l-3.8-3.8v10c.3.1.5.3.7.5 1.2 1.2 1.2 3 0 4.2-1.2 1.2-3 1.2-4.2 0-1.2-1.2-1.2-3 0-4.2.3-.3.6-.5.9-.6V15.9c-.3-.1-.6-.3-.9-.6-.9-.9-1.1-2.2-.5-3.2l-4.1-4.1-11 11c-.9.9-.9 2.4 0 3.3l20.1 20.1c.9.9 2.4.9 3.3 0l19.9-19.9c.9-.9.9-2.4 0-3.3" fill="#F05032"/>
                </svg>
            </a>

            {{-- n8n - larger (automation hero) --}}
            <a href="https://n8n.io" target="_blank" rel="noopener noreferrer"
               class="group flex items-center justify-center p-2 opacity-40 grayscale hover:opacity-100 hover:grayscale-0 transition-all duration-300 hover:scale-110"
               title="n8n">
                <svg class="h-8 w-auto" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="48" height="48" rx="8" fill="#EA4B71"/>
                    <path d="M14 14h4c3 0 5 2 5 5v15h-4V20c0-1-.5-2-2-2h-3v-4z" fill="#fff"/>
                    <path d="M26 24c0-5 3.5-10 9-10v4c-3 0-5 2.5-5 6s2 6 5 6v4c-5.5 0-9-5-9-10z" fill="#fff"/>
                </svg>
            </a>

            {{-- Make --}}
            <a href="https://make.com" target="_blank" rel="noopener noreferrer"
               class="group flex items-center justify-center p-2 opacity-40 grayscale hover:opacity-100 hover:grayscale-0 transition-all duration-300 hover:scale-110"
               title="Make">
                <svg class="h-8 w-auto" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="48" height="48" rx="8" fill="#6D00CC"/>
                    <circle cx="24" cy="24" r="8" stroke="#fff" stroke-width="3" fill="none"/>
                    <circle cx="24" cy="24" r="3" fill="#fff"/>
                    <path d="M24 10v4M24 34v4M10 24h4M34 24h4" stroke="#fff" stroke-width="2"/>
                </svg>
            </a>

            {{-- Zapier --}}
            <a href="https://zapier.com" target="_blank" rel="noopener noreferrer"
               class="group flex items-center justify-center p-2 opacity-40 grayscale hover:opacity-100 hover:grayscale-0 transition-all duration-300 hover:scale-110"
               title="Zapier">
                <svg class="h-8 w-auto" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="48" height="48" rx="8" fill="#FF4A00"/>
                    <path d="M24 8v12l10-6-10-6zM24 40V28l-10 6 10 6zM8 24h12l-6-10-6 10zM40 24H28l6 10 6-10z" fill="#fff"/>
                    <circle cx="24" cy="24" r="6" fill="#FF4A00" stroke="#fff" stroke-width="2"/>
                </svg>
            </a>

            {{-- AWS --}}
            <a href="https://aws.amazon.com" target="_blank" rel="noopener noreferrer"
               class="group flex items-center justify-center p-2 opacity-40 grayscale hover:opacity-100 hover:grayscale-0 transition-all duration-300 hover:scale-110"
               title="AWS">
                <svg class="h-7 w-auto" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14 26l-2 6h-2l3-9h2l3 9h-2l-2-6zm8 6V23h4c2 0 3 1.5 3 3s-1 3-3 3h-2v3h-2zm2-5h2c.6 0 1-.4 1-1s-.4-1-1-1h-2v2zm7 5V23h2l2 6 2-6h2v9h-2v-6l-2 6h-1l-2-6v6h-1z" fill="#232F3E"/>
                    <path d="M6 32c6 4 16 4 22 0M42 28c-2 1-4 2-6 2-6 0-11-4-11-4" stroke="#FF9900" stroke-width="2" fill="none"/>
                    <path d="M40 26l3 3-3 2" stroke="#FF9900" stroke-width="2" fill="none"/>
                </svg>
            </a>
        </div>

        {{-- CTA Button --}}
        <div class="text-center pt-2">
            <a href="#contact"
               class="btn btn-outline border-neutral-content/30 text-neutral-content/70 hover:bg-neutral-content/10 hover:border-neutral-content/50 hover:text-neutral-content gap-2">
                <span class="icon-[tabler--message-circle] size-5"></span>
                {{ __('Porozmawiajmy o Twoim projekcie') }}
            </a>
        </div>
    </div>

    {{-- Bottom Bar --}}
    <div class="footer bg-neutral border-t border-neutral-content/10 px-10 py-4 max-w-7xl mx-auto">
        <div class="flex w-full items-center justify-between">
            <aside class="grid-flow-col items-center">
                <p class="text-neutral-content/50 text-sm">{{ $getCopyright() }}</p>
            </aside>
            <div class="flex items-center gap-4">
                {{-- Legal Links --}}
                <a href="/polityka-prywatnosci" class="link link-hover text-neutral-content/50 text-sm">{{ __('Polityka prywatności') }}</a>
                <a href="/regulamin" class="link link-hover text-neutral-content/50 text-sm">{{ __('Regulamin') }}</a>
                {{-- Social Links --}}
                @if($socialLinks = $getSocialLinks())
                    <span class="w-px h-4 bg-neutral-content/20"></span>
                    @foreach($socialLinks as $social)
                        <a href="{{ $social['href'] }}" target="_blank" rel="noopener noreferrer"
                           class="link text-neutral-content/50 hover:text-primary"
                           aria-label="{{ $social['label'] }}">
                            @switch($social['icon'])
                                @case('facebook')
                                    <span class="icon-[tabler--brand-facebook] size-5" aria-hidden="true"></span>
                                    @break
                                @case('linkedin')
                                    <span class="icon-[tabler--brand-linkedin] size-5" aria-hidden="true"></span>
                                    @break
                                @case('instagram')
                                    <span class="icon-[tabler--brand-instagram] size-5" aria-hidden="true"></span>
                                    @break
                                @case('github')
                                    <span class="icon-[tabler--brand-github] size-5" aria-hidden="true"></span>
                                    @break
                                @case('twitter')
                                @case('x')
                                    <span class="icon-[tabler--brand-x] size-5" aria-hidden="true"></span>
                                    @break
                                @default
                                    <span class="icon-[tabler--link] size-5" aria-hidden="true"></span>
                            @endswitch
                        </a>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</footer>
