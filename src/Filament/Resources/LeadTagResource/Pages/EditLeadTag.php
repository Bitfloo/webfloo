<?php

declare(strict_types=1);

namespace Webfloo\Filament\Resources\LeadTagResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Webfloo\Filament\Resources\LeadTagResource;

class EditLeadTag extends EditRecord
{
    protected static string $resource = LeadTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
