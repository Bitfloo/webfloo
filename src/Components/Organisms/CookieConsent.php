<?php

declare(strict_types=1);

namespace Webfloo\Components\Organisms;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * GDPR cookie banner. Decision persists in localStorage under
 * "webfloo-cookie-consent" ("accepted" / "declined") — host scripts gate
 * analytics on that key. Requires the published Alpine bundle
 * (webfloo-assets tag); no CDN is ever loaded.
 */
class CookieConsent extends Component
{
    public string $message;

    public string $acceptLabel;

    public string $declineLabel;

    public string $privacyUrl;

    public string $privacyLabel;

    public function __construct()
    {
        $message = setting('cookie_consent.message');
        $this->message = is_string($message) && $message !== ''
            ? $message
            : __('Ta strona używa plików cookie w celu zapewnienia prawidłowego działania oraz analizy ruchu.');

        $accept = setting('cookie_consent.accept_label');
        $this->acceptLabel = is_string($accept) && $accept !== '' ? $accept : __('Akceptuj');

        $decline = setting('cookie_consent.decline_label');
        $this->declineLabel = is_string($decline) && $decline !== '' ? $decline : __('Odrzuć');

        $url = setting('cookie_consent.privacy_url');
        $this->privacyUrl = is_string($url) ? $url : '';

        $label = setting('cookie_consent.privacy_label');
        $this->privacyLabel = is_string($label) && $label !== '' ? $label : __('Polityka prywatności');
    }

    public function render(): View
    {
        return view('webfloo::components.organisms.cookie-consent');
    }
}
