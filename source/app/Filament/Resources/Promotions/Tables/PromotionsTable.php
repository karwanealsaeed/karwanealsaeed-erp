<?php

namespace App\Filament\Resources\Promotions\Tables;

use App\Models\Promotion;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PromotionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make(
                    'featured_image_path',
                )
                    ->label('Image')
                    ->disk('public')
                    ->width(82)
                    ->height(54),

                TextColumn::make('title')
                    ->label('Promotion')
                    ->description(
                        fn (Promotion $record): ?string =>
                            $record->subtitle
                            ?: $record->promotion_type,
                    )
                    ->searchable([
                        'title',
                        'title_ur',
                        'subtitle',
                        'subtitle_ur',
                        'promo_code',
                    ])
                    ->sortable()
                    ->limit(75)
                    ->wrap(),

                TextColumn::make('promotion_type')
                    ->label('Type')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('discount_label')
                    ->label('Discount')
                    ->badge()
                    ->color('warning')
                    ->placeholder('-'),

                TextColumn::make('promo_code')
                    ->label('Promo Code')
                    ->copyable()
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('offer_price')
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
                    )
                    ->sortable(),

                TextColumn::make('starts_at')
                    ->label('Starts')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('ends_at')
                    ->label('Ends')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->badge()
                    ->color(
                        fn (string $state): string => match ($state) {
                            'Published' => 'success',
                            'Archived' => 'gray',
                            default => 'warning',
                        },
                    )
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->numeric()
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: true,
                    ),

                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: true,
                    ),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: true,
                    ),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'Draft' => 'Draft',
                        'Published' => 'Published',
                        'Archived' => 'Archived',
                    ]),

                SelectFilter::make('promotion_type')
                    ->label('Promotion Type')
                    ->options(
                        fn (): array => Promotion::query()
                            ->whereNotNull('promotion_type')
                            ->where('promotion_type', '!=', '')
                            ->distinct()
                            ->orderBy('promotion_type')
                            ->pluck(
                                'promotion_type',
                                'promotion_type',
                            )
                            ->all(),
                    )
                    ->searchable(),

                SelectFilter::make('discount_type')
                    ->label('Discount Type')
                    ->options([
                        'Percentage' => 'Percentage',
                        'Fixed Amount' => 'Fixed Amount',
                        'Special Price' => 'Special Price',
                    ]),

                TernaryFilter::make('is_featured')
                    ->label('Featured Promotions'),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}