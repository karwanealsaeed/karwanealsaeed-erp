<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use App\Models\ActivityLog;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
    ->label('Date Time')
    ->dateTime('d M Y h:i A')
    ->timezone('Asia/Karachi')
    ->sortable(),

                TextColumn::make('action')
                    ->label('Action')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Created' => 'success',
                        'Updated' => 'warning',
                        'Deleted' => 'danger',
                        'Downloaded' => 'info',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('module')
                    ->label('Module')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('record_title')
                    ->label('Record')
                    ->searchable()
                    ->wrap()
                    ->placeholder('-'),

                TextColumn::make('changes_summary')
                    ->label('Changes')
                    ->state(fn (ActivityLog $record): string => self::formatChangesSummary($record))
                    ->wrap()
                    ->limit(180)
                    ->placeholder('-'),

                TextColumn::make('user_name')
                    ->label('User')
                    ->searchable()
                    ->placeholder('System'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Details')
                    ->icon('heroicon-o-eye'),
            ])
            ->defaultSort('id', 'desc');
    }

    private static function formatChangesSummary(ActivityLog $record): string
    {
        $oldValues = $record->old_values ?? [];
        $newValues = $record->new_values ?? [];

        if (! is_array($oldValues)) {
            $oldValues = [];
        }

        if (! is_array($newValues)) {
            $newValues = [];
        }

        if ($record->action === 'Created') {
            return 'Record created';
        }

        if ($record->action === 'Deleted') {
            return 'Record deleted';
        }

        if ($record->action === 'Downloaded') {
            return 'Record downloaded';
        }

        $keys = collect(array_keys($oldValues))
            ->merge(array_keys($newValues))
            ->unique()
            ->values();

        $hasDocumentTypes = array_key_exists('document_types', $oldValues) || array_key_exists('document_types', $newValues);

        $changes = [];

        foreach ($keys as $key) {
            if (in_array($key, ['updated_at', 'created_at'], true)) {
                continue;
            }

            if ($key === 'document_type' && $hasDocumentTypes) {
                continue;
            }

            $oldValue = $oldValues[$key] ?? null;
            $newValue = $newValues[$key] ?? null;

            if ($key === 'documents') {
                $oldDocs = self::normalizeDocuments($oldValue);
                $newDocs = self::normalizeDocuments($newValue);

                if (json_encode($oldDocs) === json_encode($newDocs)) {
                    continue;
                }

                $changes[] = 'Documents: ' . count($oldDocs) . ' files → ' . count($newDocs) . ' files';
                continue;
            }

            if ($key === 'document_types') {
                $oldText = self::formatValue($key, $oldValue);
                $newText = self::formatValue($key, $newValue);

                if ($oldText === $newText) {
                    continue;
                }

                $changes[] = 'Document Types: ' . $oldText . ' → ' . $newText;
                continue;
            }

            $oldText = self::formatValue($key, $oldValue);
            $newText = self::formatValue($key, $newValue);

            if ($oldText === $newText) {
                continue;
            }

            $changes[] = self::fieldLabel($key) . ': ' . $oldText . ' → ' . $newText;
        }

        return count($changes) > 0
            ? implode(' | ', $changes)
            : 'No visible changes';
    }

    private static function normalizeDocuments($value): array
    {
        $value = self::decodeValue($value);

        if (! is_array($value)) {
            return [];
        }

        $documents = [];

        foreach ($value as $document) {
            if (! is_array($document)) {
                continue;
            }

            $documents[] = [
                'file_name' => $document['file_name'] ?? '',
                'file_path' => $document['file_path'] ?? '',
                'file_type' => $document['file_type'] ?? '',
                'file_size' => (int) ($document['file_size'] ?? 0),
            ];
        }

        usort($documents, fn ($a, $b) => ($a['file_path'] ?: $a['file_name']) <=> ($b['file_path'] ?: $b['file_name']));

        return $documents;
    }

    private static function formatValue(string $key, $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $decoded = self::decodeValue($value);

        if ($key === 'document_types') {
            if (is_array($decoded)) {
                return implode(', ', $decoded);
            }

            return (string) $decoded;
        }

        if (is_array($decoded)) {
            return collect($decoded)
                ->map(function ($item, $itemKey) {
                    if (is_scalar($item)) {
                        return (string) $item;
                    }

                    return json_encode($item, JSON_UNESCAPED_UNICODE);
                })
                ->implode(', ');
        }

        if (is_bool($decoded)) {
            return $decoded ? 'Yes' : 'No';
        }

        return (string) $decoded;
    }

    private static function decodeValue($value)
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $value;
    }

    private static function fieldLabel(string $key): string
    {
        return str($key)
            ->replace('_id', '')
            ->replace('_', ' ')
            ->title()
            ->toString();
    }
}
