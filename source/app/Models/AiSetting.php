<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiSetting extends Model
{
    protected $fillable = [
        'key',
        'provider',
        'model',
        'external_enabled',
        'fallback_enabled',
        'timeout_seconds',
        'max_output_characters',
        'options',
        'updated_by',
    ];

    protected $casts = [
        'external_enabled' =>
            'boolean',

        'fallback_enabled' =>
            'boolean',

        'timeout_seconds' =>
            'integer',

        'max_output_characters' =>
            'integer',

        'options' =>
            'array',

        'updated_by' =>
            'integer',
    ];

    public static function current(): self
    {
        return self::query()->firstOrCreate(
            [
                'key' =>
                    'default',
            ],
            self::defaults(),
        );
    }

    public static function defaults(): array
    {
        return [
            'provider' =>
                'local',

            'model' =>
                null,

            'external_enabled' =>
                false,

            'fallback_enabled' =>
                true,

            'timeout_seconds' =>
                10,

            'max_output_characters' =>
                4000,

            'options' =>
                null,

            'updated_by' =>
                null,
        ];
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by',
        );
    }
}
