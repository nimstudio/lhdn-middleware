<?php

namespace App\Filament\App\Resources\LhdnCredentialResource\Pages;

use App\Filament\App\Resources\LhdnCredentialResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLhdnCredentials extends ListRecords
{
    protected static string $resource = LhdnCredentialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
