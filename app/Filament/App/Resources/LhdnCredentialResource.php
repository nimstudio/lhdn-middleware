<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\LhdnCredentialResource\Pages;
use App\Models\LhdnCredential;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LhdnCredentialResource extends Resource
{
    protected static ?string $model = LhdnCredential::class;

    protected static ?string $navigationLabel = 'LHDN Credentials';

    protected static ?string $modelLabel = 'LHDN Credential';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make('LHDN MyInvois API Credentials')
                    ->description('Enter your LHDN MyInvois API credentials to start submitting invoices.')
                    ->schema([
                        Forms\Components\TextInput::make('client_id')
                            ->label('Client ID')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter your LHDN Client ID')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('client_secret')
                            ->label('Client Secret')
                            ->required()
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->placeholder('Enter your LHDN Client Secret')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('mode')
                            ->label('Environment')
                            ->required()
                            ->options([
                                'sandbox' => 'Sandbox (Testing)',
                                'production' => 'Production (Live)',
                            ])
                            ->default('sandbox')
                            ->helperText('Select Sandbox for testing or Production for live submissions')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Additional Information')
                    ->description('This information is managed automatically.')
                    ->schema([
                        Forms\Components\Placeholder::make('status')
                            ->content(fn ($record) => $record?->status ? ucfirst($record->status) : 'Active'),
                        Forms\Components\Placeholder::make('last_token_refresh')
                            ->label('Last Token Refresh')
                            ->content(fn ($record) => $record?->last_token_refresh ? $record->last_token_refresh->format('M d, Y g:i A') : 'Never'),
                        Forms\Components\Placeholder::make('token_expires_at')
                            ->label('Token Expires At')
                            ->content(fn ($record) => $record?->token_expires_at ? $record->token_expires_at->format('M d, Y g:i A') : 'N/A'),
                    ])
                    ->columns(3)
                    ->hidden(fn ($record) => ! $record),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client_id')
                    ->label('Client ID')
                    ->limit(20)
                    ->tooltip(fn ($record) => $record->client_id)
                    ->searchable(),
                Tables\Columns\TextColumn::make('mode')
                    ->label('Environment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sandbox' => 'warning',
                        'production' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'expired' => 'warning',
                        'invalid' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('last_token_refresh')
                    ->label('Last Refresh')
                    ->dateTime('M d, Y g:i A')
                    ->sortable()
                    ->placeholder('Never'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->emptyStateHeading('No LHDN credentials configured')
            ->emptyStateDescription('Configure your LHDN MyInvois API credentials to start submitting invoices')
            ->emptyStateActions([
                CreateAction::make(),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                // Only show credentials for user's company
                return $query->where('company_id', auth()->user()->company_id);
            });
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLhdnCredentials::route('/'),
            'create' => Pages\CreateLhdnCredential::route('/create'),
            'edit' => Pages\EditLhdnCredential::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        // Check if user's company already has credentials
        return ! auth()->user()->company?->lhdnCredential;
    }

    public static function getEloquentQuery(): Builder
    {
        // Only show credentials for user's company
        return parent::getEloquentQuery()
            ->where('company_id', auth()->user()->company_id);
    }
}
