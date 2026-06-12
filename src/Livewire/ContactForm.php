<?php

declare(strict_types=1);

namespace Webfloo\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;
use Webfloo\Events\LeadCreated;
use Webfloo\Models\Lead;

class ContactForm extends Component
{
    /** Public so tests can target the limiter without re-stating the key. */
    public const RATE_LIMITER_PREFIX = 'webfloo-contact:';

    private const MAX_ATTEMPTS_PER_MINUTE = 3;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $message = '';

    public bool $consent = false;

    /** Honeypot — must stay empty; bots auto-fill it. */
    public string $website = '';

    public bool $submitted = false;

    /**
     * @return array<string, string>
     */
    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'message' => 'required|string|max:5000',
            'consent' => 'accepted',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'consent.accepted' => __('Zgoda na przetwarzanie danych jest wymagana.'),
        ];
    }

    public function submit(): void
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

        $lead = Lead::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone !== '' ? $this->phone : null,
            'message' => $this->message,
            'consent_at' => now(),
            'source' => Lead::SOURCE_CONTACT_FORM,
            'status' => Lead::STATUS_NEW,
        ]);

        $lead->activities()->create([
            'type' => 'created',
            'title' => 'Lead z formularza kontaktowego',
        ]);

        event(new LeadCreated($lead));

        $this->reset('name', 'email', 'phone', 'message', 'consent');
        $this->submitted = true;
    }

    public function render(): View
    {
        return view('webfloo::livewire.contact-form');
    }
}
