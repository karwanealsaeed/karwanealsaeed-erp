<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'annual_quota',
        'is_paid',
        'attachment_required_after_days',
        'status',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'annual_quota' => 'decimal:2',
        'is_paid' => 'boolean',
        'attachment_required_after_days' => 'integer',
        'sort_order' => 'integer',
    ];

    public function staffLeaves(): HasMany
    {
        return $this->hasMany(StaffLeave::class);
    }
}