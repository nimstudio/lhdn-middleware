<?php

namespace App\Filament\App\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class GettingStartedWidget extends Widget
{
    protected string $view = 'filament.app.widgets.getting-started-widget';

    protected int|string|array $columnSpan = 'full';

    public function getSteps(): array
    {
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        $steps = [];

        // Step 1: Email Verification
        $steps[] = [
            'title' => 'Verify Your Email',
            'description' => 'Confirm your email address to secure your account',
            'completed' => $user->hasVerifiedEmail(),
            'url' => $user->hasVerifiedEmail() ? '#' : route('verification.notice'),
            'button' => $user->hasVerifiedEmail() ? 'Verified ✓' : 'Verify Email',
            'icon' => 'heroicon-o-envelope',
            'disabled' => false,
            'priority' => 1,
        ];

        // Step 2: Company Setup
        $steps[] = [
            'title' => 'Setup Company Information',
            'description' => 'Add your company details and business information',
            'completed' => (bool) $user->company_id,
            'url' => $user->company_id
                ? route('filament.app.resources.companies.edit', ['record' => $user->company_id])
                : route('filament.app.resources.companies.create'),
            'button' => $user->company_id ? 'Edit Company' : 'Create Company',
            'icon' => 'heroicon-o-building-office-2',
            'disabled' => false,
            'priority' => 2,
        ];

        // Step 3: LHDN Credentials
        $steps[] = [
            'title' => 'Configure LHDN Credentials',
            'description' => 'Add your LHDN MyInvois API credentials for invoice submission',
            'completed' => (bool) $user->company?->lhdnCredential,
            'url' => $user->company?->lhdnCredential
                ? route('filament.app.resources.lhdn-credentials.edit', ['record' => $user->company->lhdnCredential->id])
                : route('filament.app.resources.lhdn-credentials.create'),
            'button' => $user->company?->lhdnCredential ? 'Edit Credentials' : 'Add Credentials',
            'icon' => 'heroicon-o-key',
            'disabled' => ! $user->company_id,
            'priority' => 3,
        ];

        // Step 4: Subscription Check
        if ($user->company_id) {
            $steps[] = [
                'title' => 'Check Subscription Status',
                'description' => 'Ensure your subscription is active for invoice submissions',
                'completed' => $user->subscription_status === 'active',
                'url' => $user->subscription_status === 'active' ? '#' : route('filament.app.pages.dashboard'),
                'button' => $user->subscription_status === 'active' ? 'Active ✓' : 'Check Status',
                'icon' => 'heroicon-o-credit-card',
                'disabled' => ! $user->company_id,
                'priority' => 4,
            ];
        }

        // Sort by priority
        usort($steps, fn ($a, $b) => $a['priority'] <=> $b['priority']);

        return $steps;
    }

    public function getCompletionPercentage(): int
    {
        $steps = $this->getSteps();

        if (empty($steps)) {
            return 0;
        }

        $completed = collect($steps)->where('completed', true)->count();

        return (int) round(($completed / count($steps)) * 100);
    }

    public function getNextStep(): ?array
    {
        $steps = $this->getSteps();

        return collect($steps)->first(fn ($step) => ! $step['completed'] && ! ($step['disabled'] ?? false));
    }

    public function isOnboardingComplete(): bool
    {
        $user = Auth::user();

        return $user &&
               $user->hasVerifiedEmail() &&
               $user->company_id &&
               $user->company?->lhdnCredential &&
               $user->subscription_status === 'active';
    }

    public static function canView(): bool
    {
        // For testing - always show the widget
        return true;
    }
}
