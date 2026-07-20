<?php

namespace App\Filament\Resources\Faqs\Tables;

use App\Models\Faq;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class FaqsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question')
                    ->label('Question')
                    ->description(
                        fn (Faq $record): ?string =>
                            $record->category,
                    )
                    ->searchable([
                        'question',
                        'question_ur',
                        'category',
                        'category_ur',
                    ])
                    ->sortable()
                    ->limit(85)
                    ->wrap(),

                TextColumn::make('answer')
                    ->label('Answer')
                    ->searchable([
                        'answer',
                        'answer_ur',
                    ])
                    ->limit(75)
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('category')
                    ->badge()
                    ->color('info')
                    ->searchable()
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

                SelectFilter::make('category')
                    ->options(
                        fn (): array => Faq::query()
                            ->whereNotNull('category')
                            ->where('category', '!=', '')
                            ->distinct()
                            ->orderBy('category')
                            ->pluck('category', 'category')
                            ->all(),
                    )
                    ->searchable(),

                TernaryFilter::make('is_featured')
                    ->label('Featured FAQs'),
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