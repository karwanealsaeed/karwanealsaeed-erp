<?php

namespace App\Filament\Resources\Promotions\Schemas;

use App\Models\Promotion;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PromotionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Promotion Information')
                    ->schema([
                        ImageEntry::make(
                            'featured_image_path',
                        )
                            ->label('Promotion Image')
                            ->disk('public')
                            ->height(280)
                            ->columnSpanFull(),

                        TextEntry::make('title')
                            ->label('Title — English')
                            ->columnSpanFull(),

                        TextEntry::make('title_ur')
                            ->label('Title — Urdu')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('subtitle')
                            ->label('Subtitle — English')
                            ->placeholder('-'),

                        TextEntry::make('subtitle_ur')
                            ->label('Subtitle — Urdu')
                            ->placeholder('-'),

                        TextEntry::make('slug')
                            ->copyable()
                            ->columnSpanFull(),

                        TextEntry::make('promotion_type')
                            ->label('Promotion Type')
                            ->badge()
                            ->color('info')
                            ->placeholder('-'),

                        TextEntry::make('promo_code')
                            ->label('Promo Code')
                            ->copyable()
                            ->badge()
                            ->color('warning')
                            ->placeholder('-'),

                        TextEntry::make('description')
                            ->label('Description — English')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('description_ur')
                            ->label('Description — Urdu')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Discount and Pricing')
                    ->schema([
                        TextEntry::make('discount_type')
                            ->label('Discount Type')
                            ->placeholder('-'),

                        TextEntry::make('discount_label')
                            ->label('Discount')
                            ->badge()
                            ->color('warning')
                            ->placeholder('-'),

                        TextEntry::make('original_price')
                            ->label('Original Price')
                            ->formatStateUsing(
                                fn (
                                    mixed $state,
                                    Promotion $record,
                                ): string => filled($state)
                                    ? (
                                        $record->currency ?: 'PKR'
                                    ).' '.number_format(
                                        (float) $state,
                                        0,
                                    )
                                    : '-',
                            ),

                        TextEntry::make('offer_price')
                            ->label('Offer Price')
                            ->formatStateUsing(
                                fn (
                                    mixed $state,
                                    Promotion $record,
                                ): string => filled($state)
                                    ? (
                                        $record->currency ?: 'PKR'
                                    ).' '.number_format(
                                        (float) $state,
                                        0,
                                    )
                                    : '-',
                            ),
                    ])
                    ->columns(2),

                Section::make('Call to Action and Terms')
                    ->schema([
                        TextEntry::make('badge_text')
                            ->label('Badge Text — English')
                            ->placeholder('-'),

                        TextEntry::make('badge_text_ur')
                            ->label('Badge Text — Urdu')
                            ->placeholder('-'),

                        TextEntry::make('cta_text')
                            ->label('Button Text — English')
                            ->placeholder('-'),

                        TextEntry::make('cta_text_ur')
                            ->label('Button Text — Urdu')
                            ->placeholder('-'),

                        TextEntry::make('target_url')
                            ->label('Target URL')
                            ->url(
                                fn (Promotion $record): ?string =>
                                    $record->target_url,
                                shouldOpenInNewTab: true,
                            )
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('terms')
                            ->label('Terms — English')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('terms_ur')
                            ->label('Terms — Urdu')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Validity and Publication')
                    ->schema([
                        TextEntry::make('starts_at')
                            ->label('Starts At')
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('-'),

                        TextEntry::make('ends_at')
                            ->label('Ends At')
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('-'),

                        TextEntry::make('status')
                            ->badge()
                            ->color(
                                fn (string $state): string => match ($state) {
                                    'Published' => 'success',
                                    'Archived' => 'gray',
                                    default => 'warning',
                                },
                            ),

                        IconEntry::make('is_active')
                            ->label('Currently Active')
                            ->boolean(),

                        IconEntry::make('is_featured')
                            ->label('Featured')
                            ->boolean(),

                        TextEntry::make('sort_order')
                            ->label('Sort Order')
                            ->numeric(),

                        TextEntry::make('published_at')
                            ->label('Published At')
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('-'),

                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime('d M Y, h:i A'),

                        TextEntry::make('updated_at')
                            ->label('Updated At')
                            ->dateTime('d M Y, h:i A'),
                    ])
                    ->columns(3),
            ]);
    }
}