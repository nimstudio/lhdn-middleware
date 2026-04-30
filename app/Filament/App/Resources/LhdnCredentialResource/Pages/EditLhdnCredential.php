<?php

namespace App\Filament\App\Resources\LhdnCredentialResource\Pages;

use App\Filament\App\Resources\LhdnCredentialResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLhdnCredential extends EditRecord
{
    protected static string $resource = LhdnCredentialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Track who updated the credentials
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        // Redirect to credentials list after updating
        return $this->getResource()::getUrl('index');
    }
}
