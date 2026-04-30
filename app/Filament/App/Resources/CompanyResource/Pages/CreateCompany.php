<?php

namespace App\Filament\App\Resources\CompanyResource\Pages;

use App\Filament\App\Resources\CompanyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-fill data from user
        $data['status'] = 'active';
        $data['onboarding_completed'] = false;

        return $data;
    }

    protected function afterCreate(): void
    {
        // Link the user to this company
        auth()->user()->update([
            'company_id' => $this->record->id,
        ]);
    }

    protected function getRedirectUrl(): string
    {
        // Redirect to app dashboard after creating company
        return '/app';
    }
}
