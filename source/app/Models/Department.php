<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function designations(): HasMany
    {
        return $this->hasMany(Designation::class);
    }

    public function staffMembers(): HasMany
    {
        return $this->hasMany(Staff::class);
    }
}