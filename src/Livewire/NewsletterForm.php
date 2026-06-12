<?php

declare(strict_types=1);

namespace Webfloo\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;
use Webfloo\Models\NewsletterSubscriber;

class NewsletterForm extends Component
{
    /** Public so tests can target the limiter without re-stating the key. */
    public const RATE_LIMITER_PREFIX = 'webfloo-newsletter:';

    private const MAX_ATTEMPTS_PER_MINUTE = 3;

    public string $email = '';

    /** Honeypot — must stay empty; bots auto-fill it. */
    public string $website = '';

    /** Where the form is embedded (footer/blog/popup/landing). */
    public string $source = 'footer';

    public bool $submitted = false;

    /**
     * @return array<string, string>
     */
    protected function rules(): array
    {
        return [
            'email' => 'required|email|max:255',
        ];
    }

    public function subscribe(): void
    {
        $this->validate();

        // Honeypot filled = bot. Pretend success, store nothing.
        if ($this->website !== '') {
            $this->submitted = true;

            return;
        }

        $key = self::RATE_LIMITER_PREFIX.request()->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS_PER_MINUTE)) {
            $this->addError('form', __('Zbyt wiele prob. Sprobuj ponownie za chwile.'));

            return;
        }

        RateLimiter::hit($key);

        // Existing address: report success without writing — the response
        // must not reveal whether an e-mail is already subscribed.
        if (! NewsletterSubscriber::query()->where('email', $this->email)->exists()) {
            NewsletterSubscriber::create([
                'email' => $this->email,
                'is_active' => true,
                'subscribed_at' => now(),
                'ip_address' => request()->ip(),
                'source' => $this->source,
            ]);
        }

        $this->reset('email');
        $this->submitted = true;
    }

    public function render(): View
    {
        return view('webfloo::livewire.newsletter-form');
    }
}
