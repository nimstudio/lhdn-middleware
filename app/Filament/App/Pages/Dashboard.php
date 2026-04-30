<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static ?string $title = 'Dashboard';

    protected static ?int $navigationSort = -2;

    protected static string $routePath = '/';

    protected string $view = 'filament.app.pages.dashboard';

    public function getWidgets(): array
    {
        return [
            \App\Filament\App\Widgets\GettingStartedWidget::class,
        ];
    }
}
