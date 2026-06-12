<div
    id="webfloo-cookie-consent"
    x-data="{ decided: localStorage.getItem('webfloo-cookie-consent') !== null }"
    x-show="!decided"
    x-cloak
    class="fixed inset-x-0 bottom-0 z-50 border-t border-base-300 bg-base-200 p-4 shadow-lg"
    role="dialog"
    aria-live="polite"
    aria-label="{{ __('Zgoda na pliki cookie') }}"
>
    <div class="container mx-auto flex flex-col items-start gap-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-base-content">
            {{ $message }}
            @if ($privacyUrl !== '')
                <a href="{{ $privacyUrl }}" class="link link-primary">{{ $privacyLabel }}</a>
            @endif
        </p>
        <div class="flex shrink-0 gap-2">
            <button
                type="button"
                class="btn btn-text btn-sm"
                @click="localStorage.setItem('webfloo-cookie-consent', 'declined'); decided = true"
            >{{ $declineLabel }}</button>
            <button
                type="button"
                class="btn btn-primary btn-sm"
                @click="localStorage.setItem('webfloo-cookie-consent', 'accepted'); decided = true"
            >{{ $acceptLabel }}</button>
        </div>
    </div>
</div>
