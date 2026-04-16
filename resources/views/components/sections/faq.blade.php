<section {{ $attributes->merge(['class' => 'py-16 lg:py-24 bg-base-100']) }}>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <x-webfloo-section-header
            :title="$title"
            :subtitle="$subtitle"
            :description="$description"
            class="mb-12"
        />

        {{-- FlyonUI Accordion --}}
        <div class="accordion accordion-shadow rounded-box max-w-3xl mx-auto">
            @foreach($items as $index => $item)
                <div class="accordion-item" id="faq-{{ $index }}">
                    <button class="accordion-toggle inline-flex items-center justify-between gap-x-4 w-full px-5 py-4 text-start" aria-controls="faq-collapse-{{ $index }}" aria-expanded="false">
                        <span class="font-semibold text-base-content">{{ $item['question'] }}</span>
                        <span class="icon-[tabler--chevron-down] size-5 shrink-0 text-base-content/60 accordion-item-active:rotate-180 transition-transform duration-300" aria-hidden="true"></span>
                    </button>
                    <div id="faq-collapse-{{ $index }}" class="accordion-content hidden w-full overflow-hidden transition-[height] duration-300" role="region" aria-labelledby="faq-{{ $index }}">
                        <div class="px-5 pb-5">
                            <p class="text-base-content/70 leading-relaxed">{!! $item['answer'] !!}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- CTA Section --}}
        <div class="mt-12 text-center">
            <div class="inline-flex flex-col sm:flex-row items-center gap-4 p-6 rounded-2xl bg-base-200/50 border border-base-content/5">
                <div class="text-center sm:text-left">
                    <p class="font-semibold text-base-content">{{ __('Nie znalazłeś odpowiedzi?') }}</p>
                    <p class="text-sm text-base-content/60">{{ __('Skontaktuj się z nami - chętnie pomożemy.') }}</p>
                </div>
                <a href="#contact" class="btn btn-primary">
                    <span class="icon-[tabler--message-circle] size-5"></span>
                    {{ __('Napisz do nas') }}
                </a>
            </div>
        </div>

        {{-- Slot for additional content --}}
        @if($slot->isNotEmpty())
            <div class="mt-12 text-center">
                {{ $slot }}
            </div>
        @endif
    </div>
</section>
