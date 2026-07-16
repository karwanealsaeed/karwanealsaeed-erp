<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffAttendance extends Model
{
    protected $fillable = [
        'staff_member_id',
        'attendance_date',
        'status',
        'check_in',
        'check_out',
        'working_hours',
        'remarks',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'working_hours' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (StaffAttendance $attendance): void {
            if ($attendance->check_in && $attendance->check_out) {
                $checkIn = Carbon::parse($attendance->check_in);
                $checkOut = Carbon::parse($attendance->check_out);

                if ($checkOut->greaterThan($checkIn)) {
                    $attendance->working_hours = round($checkIn->diffInMinutes($checkOut) / 60, 2);
                }
            }

            if ($attendance->status === 'Absent' || $attendance->status === 'Leave') {
                $attendance->working_hours = 0;
            }
        });
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }
}