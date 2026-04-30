<?php

namespace App\Filament\App\Resources\LhdnCredentialResource\Pages;

use App\Filament\App\Resources\LhdnCredentialResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLhdnCredential extends CreateRecord
{
    protected static string $resource = LhdnCredentialResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-link to user's company
        $data['company_id'] = auth()->user()->company_id;
        $data['status'] = 'active';
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        // Redirect to credentials list after creating
        return $this->getResource()::getUrl('index');
    }
}
