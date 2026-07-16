<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffMember extends Model
{
    protected $fillable = [
        'employee_no',
        'name',
        'father_name',
        'cnic',
        'mobile',
        'whatsapp',
        'email',
        'designation',
        'department',
        'joining_date',
        'leaving_date',
        'basic_salary',
        'payment_method',
        'bank_name',
        'bank_account_no',
        'address',
        'status',
        'notes',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'leaving_date' => 'date',
        'basic_salary' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (StaffMember $staff): void {
            if (blank($staff->employee_no)) {
                $nextId = ((int) self::max('id')) + 1;
                $staff->employee_no = 'EMP-' . str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(StaffAttendance::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(StaffPayroll::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->employee_no . ' - ' . $this->name;
    }
}