<?php

namespace Eclipse\Catalogue\Filament\Resources\CategoryResource\Pages;

use Eclipse\Catalogue\Filament\Resources\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
