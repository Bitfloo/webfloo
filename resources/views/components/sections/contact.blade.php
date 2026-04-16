<section {{ $attributes->merge(['class' => 'py-16 lg:py-24 bg-base-200']) }} id="contact">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <x-webfloo-section-header
            :title="$title"
            :subtitle="$subtitle"
            :description="$description"
        />

        <div class="grid lg:grid-cols-2 gap-8 lg:gap-12">
            {{-- Contact Info --}}
            <div class="space-y-6">
                {{-- Contact Cards Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @if($email = setting('contact_email'))
                        <a href="mailto:{{ $email }}" class="group card bg-base-100 shadow-sm hover:shadow-md hover:border-primary/20 border border-transparent transition-all duration-300">
                            <div class="card-body p-5">
                                <div class="w-12 h-12 bg-gradient-to-br from-primary/20 to-primary/5 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                                    <span class="icon-[tabler--at] size-6 text-primary" aria-hidden="true"></span>
                                </div>
                                <div class="text-sm text-base-content/50 mb-1">{{ __('Email') }}</div>
                                <div class="font-semibold text-base-content group-hover:text-primary transition-colors break-all">
                                    {{ $email }}
                                </div>
                            </div>
                        </a>
                    @endif

                    @if($phone = setting('contact_phone'))
                        <a href="tel:{{ $phone }}" class="group card bg-base-100 shadow-sm hover:shadow-md hover:border-primary/20 border border-transparent transition-all duration-300">
                            <div class="card-body p-5">
                                <div class="w-12 h-12 bg-gradient-to-br from-primary/20 to-primary/5 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                                    <span class="icon-[tabler--phone] size-6 text-primary" aria-hidden="true"></span>
                                </div>
                                <div class="text-sm text-base-content/50 mb-1">{{ __('Telefon') }}</div>
                                <div class="font-semibold text-base-content group-hover:text-primary transition-colors">
                                    {{ $phone }}
                                </div>
                            </div>
                        </a>
                    @endif

                    @if($address = setting('contact_address'))
                        <div class="card bg-base-100 shadow-sm sm:col-span-2">
                            <div class="card-body p-5">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-primary/20 to-primary/5 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <span class="icon-[tabler--map-pin] size-6 text-primary" aria-hidden="true"></span>
                                    </div>
                                    <div>
                                        <div class="text-sm text-base-content/50 mb-1">{{ __('Lokalizacja') }}</div>
                                        <div class="font-semibold text-base-content">{!! nl2br(e($address)) !!}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Business Hours Card --}}
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body p-5">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-primary/20 to-primary/5 rounded-xl flex items-center justify-center">
                                <span class="icon-[tabler--clock] size-6 text-primary" aria-hidden="true"></span>
                            </div>
                            <h3 class="font-semibold text-base-content">{{ __('Godziny pracy') }}</h3>
                        </div>
                        <div class="space-y-2 text-sm mb-4">
                            <div class="flex justify-between items-center">
                                <span class="text-base-content/70">{{ __('Poniedziałek - Piątek') }}</span>
                                <span class="font-medium text-base-content">9:00 - 17:00</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-base-content/70">{{ __('Sobota - Niedziela') }}</span>
                                <span class="text-base-content/40">{{ __('Zamknięte') }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 text-sm bg-primary/10 text-primary rounded-lg px-3 py-2">
                            <span class="icon-[tabler--bolt] size-4" aria-hidden="true"></span>
                            <span>{{ __('Odpowiadamy w ciągu') }} <strong>24h</strong></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact Form --}}
            @if($showForm)
                <div>
                    <div class="card bg-base-100 shadow-sm h-full">
                        <div class="card-body">
                            <h3 class="card-title text-lg mb-4">{{ __('Wyślij wiadomość') }}</h3>
                            {{-- Contact form provided via Inertia (Vue) --}}
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Map embed --}}
        @if($showMap && $mapUrl = setting('google_maps_url'))
            <div class="mt-12 rounded-box overflow-hidden h-64 shadow-sm">
                <iframe
                    src="{{ $mapUrl }}"
                    width="100%"
                    height="100%"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                ></iframe>
            </div>
        @endif
    </div>
</section>
