<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TestimonialInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Profile')
                    ->schema([
                        ImageEntry::make('photo_path')
                            ->label('Customer Photo')
                            ->disk('public')
                            ->height(180)
                            ->placeholder('-'),

                        TextEntry::make('customer_name')
                            ->label('Customer Name'),

                        TextEntry::make('customer_name_ur')
                            ->label('Customer Name — Urdu')
                            ->placeholder('-'),

                        TextEntry::make('designation')
                            ->placeholder('-'),

                        TextEntry::make('designation_ur')
                            ->label('Designation — Urdu')
                            ->placeholder('-'),

                        TextEntry::make('company_name')
                            ->label('Company Name')
                            ->placeholder('-'),

                        TextEntry::make('location')
                            ->placeholder('-'),

                        TextEntry::make('location_ur')
                            ->label('Location — Urdu')
                            ->placeholder('-'),

                        TextEntry::make('country')
                            ->placeholder('-'),
                    ])
                    ->columns(2),

                Section::make('Customer Review')
                    ->schema([
                        TextEntry::make('rating')
                            ->label('Rating')
                            ->formatStateUsing(
                                fn (int $state): string =>
                                    str_repeat('★', $state)
                                    .str_repeat('☆', 5 - $state),
                            )
                            ->badge()
                            ->color('warning'),

                        TextEntry::make('service_type')
                            ->label('Service Type')
                            ->badge()
                            ->color('info')
                            ->placeholder('-'),

                        TextEntry::make('travel_date')
                            ->label('Travel / Service Date')
                            ->date('d M Y')
                            ->placeholder('-'),

                        IconEntry::make('is_video')
                            ->label('Video Testimonial')
                            ->boolean(),

                        TextEntry::make('testimonial')
                            ->label('Testimonial — English')
                            ->columnSpanFull(),

                        TextEntry::make('testimonial_ur')
                            ->label('Testimonial — Urdu')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('video_url')
                            ->label('Video URL')
                            ->url(
                                fn ($record): ?string =>
                                    $record->video_url,
                                shouldOpenInNewTab: true,
                            )
                            ->placeholder('-')
                            ->visible(
                                fn ($record): bool =>
                                    filled($record->video_url),
                            )
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Publication Information')
                    ->schema([
                        TextEntry::make('status')
                            ->badge()
                            ->color(
                                fn (string $state): string => match ($state) {
                                    'Published' => 'success',
                                    'Archived' => 'gray',
                                    default => 'warning',
                                },
                            ),

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