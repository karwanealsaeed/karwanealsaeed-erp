<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffDocument extends Model
{
    protected $fillable = [
        'document_no',
        'staff_id',
        'document_type',
        'title',
        'document_number',
        'issue_date',
        'expiry_date',
        'issuing_authority',
        'file_path',
        'status',
        'notes',
        'uploaded_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (StaffDocument $document): void {
            if (blank($document->uploaded_by) && auth()->check()) {
                $document->uploaded_by = auth()->id();
            }
        });

        static::saving(function (StaffDocument $document): void {
            if ($document->status === 'Archived') {
                return;
            }

            if (
                $document->expiry_date
                && Carbon::parse($document->expiry_date)->isBefore(today())
            ) {
                $document->status = 'Expired';

                return;
            }

            $document->status = 'Active';
        });

        static::created(function (StaffDocument $document): void {
            if (blank($document->document_no)) {
                $document->updateQuietly([
                    'document_no' => 'SD-' . str_pad(
                        (string) $document->id,
                        5,
                        '0',
                        STR_PAD_LEFT
                    ),
                ]);
            }
        });
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        if (! $this->expiry_date || $this->status === 'Archived') {
            return false;
        }

        return $this->expiry_date->between(
            today(),
            today()->addDays(30)
        );
    }
}