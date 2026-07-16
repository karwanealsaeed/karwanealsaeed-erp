<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    protected $table = 'staff';

    protected $fillable = [
        'department_id',
        'designation_id',

        'staff_code',
        'employee_code',
        'name',
        'father_name',
        'cnic',
        'mobile',
        'whatsapp',
        'email',

        'date_of_birth',
        'gender',
        'marital_status',

        'address',
        'city',
        'country',

        'joining_date',
        'leaving_date',
        'employment_type',

        'basic_salary',
        'salary',
        'allowance',
        'deduction',

        'emergency_contact_name',
        'emergency_contact_phone',

        'photo',
        'status',
        'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'joining_date' => 'date',
        'leaving_date' => 'date',

        'basic_salary' => 'decimal:2',
        'salary' => 'decimal:2',
        'allowance' => 'decimal:2',
        'deduction' => 'decimal:2',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
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
        $code = $this->staff_code ?: $this->employee_code;

        return filled($code)
            ? $code . ' - ' . $this->name
            : (string) $this->name;
    }
public function leaves(): HasMany
{
    return $this->hasMany(StaffLeave::class);
}
public function documents(): HasMany
{
    return $this->hasMany(StaffDocument::class);
}

public function performanceNotes(): HasMany
{
    return $this->hasMany(StaffPerformanceNote::class);
}
public function ledgerEntries(): HasMany
{
    return $this->hasMany(StaffLedgerEntry::class);
}
}