<?php

declare(strict_types=1);

namespace Webfloo\Filament\Resources\LeadTagResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Webfloo\Filament\Resources\LeadTagResource;

class CreateLeadTag extends CreateRecord
{
    protected static string $resource = LeadTagResource::class;
}
