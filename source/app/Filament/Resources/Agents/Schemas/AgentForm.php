<?php

namespace App\Filament\Resources\Agents\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class AgentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Agent Information')
                    ->description(
                        'B2B travel agent, agency profile, commission aur credit control.',
                    )
                    ->columns(3)
                    ->schema([
                        TextInput::make('customer_code')
                            ->label('Agent Code')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated on save'),

                        TextInput::make('agency_name')
                            ->label('Agency Name')
                            ->maxLength(255)
                            ->columnSpan(2),

                        TextInput::make('name')
                            ->label('Contact Person Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('mobile')
                            ->label('Mobile')
                            ->required()
                            ->tel()
                            ->maxLength(20),

                        TextInput::make('whatsapp')
                            ->label('WhatsApp')
                            ->tel()
                            ->maxLength(20),

                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('cnic')
                            ->label('CNIC')
                            ->maxLength(20),

                        TextInput::make('city')
                            ->maxLength(255),

                        TextInput::make('country')
                            ->default('Pakistan')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('commission_rate')
                            ->label('Commission Rate')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->suffix('%'),

                        TextInput::make('credit_limit')
                            ->label('Credit Limit')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('PKR'),

                        Select::make('status')
                            ->options([
                                'Active' => 'Active',
                                'Inactive' => 'Inactive',
                                'Blocked' => 'Blocked',
                            ])
                            ->default('Active')
                            ->required()
                            ->native(false),

                        Textarea::make('address')
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Agent Portal Access')
                    ->description(
                        'Password optional hai. Active Agent aur valid password ke saath existing Agent Portal login use hoga.',
                    )
                    ->columns(2)
                    ->schema([
                        TextInput::make('portal_password')
                            ->label('Agent Portal Password')
                            ->password()
                            ->revealable()
                            ->helperText(
                                'Edit par blank chhorne se existing password same rahega.',
                            )
                            ->dehydrated(
                                fn (?string $state): bool =>
                                    filled($state),
                            )
                            ->dehydrateStateUsing(
                                fn (string $state): string =>
                                    Hash::make($state),
                            )
                            ->autocomplete('new-password'),

                        TextInput::make('portal_registered_at')
                            ->label('Portal Registered At')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('last_portal_login_at')
                            ->label('Last Portal Login')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
            ]);
    }
}