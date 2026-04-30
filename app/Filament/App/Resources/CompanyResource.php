<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\CompanyResource\Pages;
use App\Models\Company;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationLabel = 'My Company';

    protected static ?string $modelLabel = 'Company';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make('Company Information')
                    ->components([
                        Forms\Components\TextInput::make('name')
                            ->label('Company Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Acme Sdn Bhd'),
                        Forms\Components\TextInput::make('registration_number')
                            ->label('Company Registration Number (SSM)')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('202301234567'),
                        Forms\Components\TextInput::make('tin_number')
                            ->label('Tax Identification Number (TIN)')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('C1234567890'),
                    ])->columns(3),

                Forms\Components\Section::make('Contact Details')
                    ->components([
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->placeholder('company@example.com'),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->maxLength(255)
                            ->prefix('+60')
                            ->placeholder('123456789'),
                    ])->columns(2),

                Forms\Components\Section::make('Company Address')
                    ->components([
                        Forms\Components\TextInput::make('address_line_1')
                            ->label('Street Address 1')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('No. 123, Jalan Example')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('address_line_2')
                            ->label('Street Address 2 (Optional)')
                            ->maxLength(255)
                            ->placeholder('Unit 5-1')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('city')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Kuala Lumpur'),
                        Forms\Components\Select::make('state_id')
                            ->label('State')
                            ->required()
                            ->relationship('state', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Select state'),
                        Forms\Components\TextInput::make('postcode')
                            ->label('Postal Code')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('50000'),
                        Forms\Components\TextInput::make('country')
                            ->required()
                            ->maxLength(255)
                            ->default('Malaysia')
                            ->disabled(),
                    ])->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Company Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('registration_number')
                    ->label('SSM Number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('state.name')
                    ->label('State')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->emptyStateHeading('No company yet')
            ->emptyStateDescription('Create your company to get started')
            ->emptyStateActions([
                CreateAction::make(),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                // Only show the user's own company
                return $query->whereHas('users', function (Builder $query) {
                    $query->where('users.id', auth()->id());
                });
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
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'view' => Pages\ViewCompany::route('/{record}'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        // Check if user already has a company
        return ! auth()->user()->company_id;
    }

    public static function getEloquentQuery(): Builder
    {
        // Only show the user's own company
        return parent::getEloquentQuery()
            ->whereHas('users', function (Builder $query) {
                $query->where('users.id', auth()->id());
            });
    }
}
