<?php

namespace App\Filament\Resources\Testimonials\Tables;

use App\Models\Testimonial;
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

class TestimonialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo_path')
                    ->label('Photo')
                    ->disk('public')
                    ->circular()
                    ->size(52),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->description(
                        fn (Testimonial $record): ?string =>
                            $record->designation
                            ?: $record->location,
                    )
                    ->searchable([
                        'customer_name',
                        'customer_name_ur',
                        'designation',
                        'designation_ur',
                        'company_name',
                        'location',
                        'location_ur',
                    ])
                    ->sortable()
                    ->wrap(),

                TextColumn::make('testimonial')
                    ->label('Review')
                    ->limit(65)
                    ->tooltip(
                        fn (Testimonial $record): string =>
                            $record->testimonial,
                    )
                    ->searchable([
                        'testimonial',
                        'testimonial_ur',
                    ])
                    ->wrap(),

                TextColumn::make('rating')
                    ->label('Rating')
                    ->formatStateUsing(
                        fn (int $state): string =>
                            str_repeat('★', $state)
                            .str_repeat('☆', 5 - $state),
                    )
                    ->sortable(),

                TextColumn::make('service_type')
                    ->label('Service')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('travel_date')
                    ->label('Travel Date')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('-'),

                IconColumn::make('is_video')
                    ->label('Video')
                    ->boolean(),

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

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->numeric()
                    ->sortable(),

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

                TextColumn::make('updated_at')
                    ->label('Updated')
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

                SelectFilter::make('service_type')
                    ->label('Service Type')
                    ->options(
                        fn (): array => Testimonial::query()
                            ->whereNotNull('service_type')
                            ->where('service_type', '!=', '')
                            ->distinct()
                            ->orderBy('service_type')
                            ->pluck(
                                'service_type',
                                'service_type',
                            )
                            ->all(),
                    )
                    ->searchable(),

                SelectFilter::make('rating')
                    ->options([
                        5 => '5 Stars',
                        4 => '4 Stars',
                        3 => '3 Stars',
                        2 => '2 Stars',
                        1 => '1 Star',
                    ]),

                TernaryFilter::make('is_featured')
                    ->label('Featured Testimonials'),
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