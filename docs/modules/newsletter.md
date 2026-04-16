# Module: Newsletter

**Feature flag:** `webfloo.features.newsletter` (default `true`)
**Scope:** email subscriber list z double opt-in timestamp, source tracking, unsubscribe. **PII scope (GDPR)** — admin-only per ShieldRolesSeeder.

## Public API

### Resources
- `Webfloo\Filament\Resources\NewsletterSubscriberResource` — **admin-only** (editor + viewer NIE mają dostępu)

### Models
- `Webfloo\Models\NewsletterSubscriber` — email, name, is_active, source, subscribed_at, unsubscribed_at, ip_address

### Exports
- `Webfloo\Filament\Exports\NewsletterSubscriberExporter` — CSV (email + opt-in data)

### Traits applied
- `HasActive`

## Migrations

- `*_create_newsletter_subscribers_table` (email UNIQUE, is_active, source, subscribed_at/unsubscribed_at, ip_address)

## Shield permissions

```
view_any_newsletter_subscriber
view_newsletter_subscriber
create_newsletter_subscriber
update_newsletter_subscriber
delete_newsletter_subscriber
```

**ShieldRolesSeeder enforces PII escalation:**
- `super_admin` — all permissions ✓
- `editor` — **brak** permissions do newsletter (usunięty z `EDITOR_RESOURCES`)
- `viewer` — **brak** permissions do newsletter (usunięty z `VIEWER_RESOURCES`)

Dodanie dostępu dla `editor` / `viewer` wymaga legal review (GDPR). Rekomendowana ścieżka: dedicated rola `compliance_officer` / `marketing_lead` z narrow permissions.

## Host integration

### Opt-in forms
Pakiet **nie dostarcza** opt-in forms (ani Blade, ani Inertia/Vue) — host wire'uje:
- Route: `POST /newsletter/subscribe` (custom controller)
- Walidacja: email format + honeypot + `consent` checkbox
- Write: `NewsletterSubscriber::create([...])` z `subscribed_at = now()`, `ip_address = $request->ip()`

Example controller sketch:
```php
NewsletterSubscriber::updateOrCreate(
    ['email' => $request->string('email')],
    [
        'name' => $request->string('name'),
        'is_active' => true,
        'source' => 'footer',
        'subscribed_at' => now(),
        'ip_address' => $request->ip(),
        'unsubscribed_at' => null,
    ],
);
```

### Unsubscribe flow
Set `is_active = false` + `unsubscribed_at = now()`. Pakiet nie ma dedicated endpoint — host dostarcza (np. signed URL w email footer).

### Feature flag scenarios

- `features.newsletter = false`:
  - NewsletterSubscriberResource niewidoczny (nawet dla super_admin).
  - Exports nadal działają programowo (jeśli host wywoła bezpośrednio).
  - **PII w DB nietknięte** — CRITICAL dla GDPR compliance (wyłączenie feature ≠ kasowanie danych).

## Testing

1. Create subscriber przez formular hosta → `NewsletterSubscriber::all()->count() === 1`.
2. Navigate `/admin/newsletter-subscribers` jako `super_admin` — widzi listę.
3. Login jako `editor` → navigation NIE pokazuje newsletter (permission denied).
4. Login jako `viewer` → tak samo (zero dostępu).

## Limitations / known gaps

- **Zero built-in unsubscribe flow** — host responsibility.
- **Zero bounce tracking** — pakiet nie integruje z email service providers (SendGrid, Postmark, itd.).
- **CSV export bez encryption** — host musi zapewnić bezpieczny kanał (download przez authenticated session only).
- **Lack of consent ledger** — tylko `subscribed_at` timestamp, brak historii zmian consent. Compliance-first hosts mogą potrzebować dodatkową tabelę `newsletter_consent_log`.
