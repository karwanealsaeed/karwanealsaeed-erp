<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiKnowledgeBase extends Model
{
    protected $fillable = [
        'title',
        'category',
        'keywords',
        'answer_english',
        'answer_urdu',
        'answer_roman_urdu',
        'priority',
        'status',
        'created_by',
    ];

    protected $casts = [
        'priority' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (AiKnowledgeBase $knowledge): void {
            if (blank($knowledge->created_by) && auth()->check()) {
                $knowledge->created_by = auth()->id();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}