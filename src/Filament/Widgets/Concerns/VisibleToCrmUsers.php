<?php

declare(strict_types=1);

namespace Webfloo\Filament\Widgets\Concerns;

use Webfloo\Support\ModuleRegistry;

/**
 * CRM widgets expose lead data (names, companies, reminders) — they must
 * never render on a host dashboard for users without CRM access.
 */
trait VisibleToCrmUsers
{
    public static function canView(): bool
    {
        return ModuleRegistry::isEnabled('crm')
            && auth()->user()?->can(webfloo_permission('view', 'crm_dashboard')) === true;
    }
}
