<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffPerformanceNote extends Model
{
    protected $fillable = [
        'note_no',
        'staff_id',
        'review_date',
        'next_review_date',
        'review_type',
        'title',
        'rating',
        'strengths',
        'improvements',
        'goals',
        'notes',
        'attachment',
        'status',
        'reviewed_by',
    ];

    protected $casts = [
        'review_date' => 'date',
        'next_review_date' => 'date',
        'rating' => 'decimal:1',
    ];

    protected static function booted(): void
    {
        static::creating(function (StaffPerformanceNote $note): void {
            if (blank($note->reviewed_by) && auth()->check()) {
                $note->reviewed_by = auth()->id();
            }

            $note->review_date ??= today();
            $note->status ??= 'Draft';
        });

        static::created(function (StaffPerformanceNote $note): void {
            if (blank($note->note_no)) {
                $note->updateQuietly([
                    'note_no' => 'PN-' . str_pad(
                        (string) $note->id,
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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}