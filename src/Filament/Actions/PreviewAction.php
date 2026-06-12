<?php

declare(strict_types=1);

namespace Webfloo\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Webfloo\Support\ModuleRegistry;

/**
 * "Podgląd" header action for Publishable content — opens a 1h signed
 * preview URL that renders the public frontend template, drafts
 * included. Visible only while the frontend module (which registers the
 * preview routes) is enabled.
 */
class PreviewAction
{
    public static function make(string $routeName, string $parameter): Action
    {
        return Action::make('preview')
            ->label(__('Podgląd'))
            ->icon(Heroicon::Eye)
            ->color('gray')
            ->visible(fn (): bool => ModuleRegistry::isEnabled('frontend'))
            ->url(fn (Model $record): string => URL::temporarySignedRoute(
                $routeName,
                now()->addHour(),
                [$parameter => $record->getKey()],
            ))
            ->openUrlInNewTab();
    }
}
