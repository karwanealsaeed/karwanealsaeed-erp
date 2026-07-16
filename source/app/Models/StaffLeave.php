<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class StaffLeave extends Model
{
    protected $fillable = [
        'leave_no',
        'staff_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'session',
        'total_days',
        'reason',
        'attachment',
        'status',
        'applied_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'admin_notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_days' => 'decimal:2',
        'applied_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (StaffLeave $leave): void {
            $leave->applied_at ??= now();
            $leave->status ??= 'Pending';
        });

        static::saving(function (StaffLeave $leave): void {
            $leave->calculateTotalDays();
            $leave->validateLeaveDates();
            $leave->validateOverlap();
            $leave->validateAttachmentRequirement();
        });

        static::created(function (StaffLeave $leave): void {
            if (blank($leave->leave_no)) {
                $leave->updateQuietly([
                    'leave_no' => 'LV-' . str_pad(
                        (string) $leave->id,
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

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function usedDaysForYear(): float
    {
        $year = Carbon::parse($this->start_date)->year;

        return (float) static::query()
            ->where('staff_id', $this->staff_id)
            ->where('leave_type_id', $this->leave_type_id)
            ->where('status', 'Approved')
            ->whereYear('start_date', $year)
            ->when(
                $this->exists,
                fn ($query) => $query->whereKeyNot($this->getKey())
            )
            ->sum('total_days');
    }

    public function remainingQuota(): ?float
    {
        $quota = (float) ($this->leaveType?->annual_quota ?? 0);

        if ($quota <= 0) {
            return null;
        }

        return max(0, $quota - $this->usedDaysForYear());
    }

    public function canBeApproved(): bool
    {
        $remaining = $this->remainingQuota();

        if ($remaining === null) {
            return true;
        }

        return (float) $this->total_days <= $remaining;
    }

    private function calculateTotalDays(): void
    {
        if (! $this->start_date || ! $this->end_date) {
            return;
        }

        $start = Carbon::parse($this->start_date)->startOfDay();
        $end = Carbon::parse($this->end_date)->startOfDay();

        if ($end->lessThan($start)) {
            return;
        }

        $days = $start->diffInDays($end) + 1;

        if ($days > 1) {
            $this->session = 'Full Day';
            $this->total_days = $days;

            return;
        }

        $this->total_days = in_array(
            $this->session,
            ['First Half', 'Second Half'],
            true
        ) ? 0.5 : 1;
    }

    private function validateLeaveDates(): void
    {
        if (! $this->start_date || ! $this->end_date) {
            return;
        }

        if (
            Carbon::parse($this->end_date)
                ->lessThan(Carbon::parse($this->start_date))
        ) {
            throw ValidationException::withMessages([
                'end_date' => 'End date start date se pehle nahi ho sakti.',
            ]);
        }
    }

    private function validateOverlap(): void
    {
        if (
            ! $this->staff_id
            || ! $this->start_date
            || ! $this->end_date
            || ! in_array($this->status, ['Pending', 'Approved'], true)
        ) {
            return;
        }

        $overlapExists = static::query()
            ->where('staff_id', $this->staff_id)
            ->whereIn('status', ['Pending', 'Approved'])
            ->whereDate('start_date', '<=', $this->end_date)
            ->whereDate('end_date', '>=', $this->start_date)
            ->when(
                $this->exists,
                fn ($query) => $query->whereKeyNot($this->getKey())
            )
            ->exists();

        if ($overlapExists) {
            throw ValidationException::withMessages([
                'start_date' => 'Is staff member ki selected dates par pehle se Pending ya Approved leave mojood hai.',
            ]);
        }
    }

    private function validateAttachmentRequirement(): void
    {
        $leaveType = $this->leaveType
            ?: LeaveType::find($this->leave_type_id);

        if (! $leaveType) {
            return;
        }

        $requiredAfter = (int) $leaveType->attachment_required_after_days;

        if (
            $requiredAfter > 0
            && (float) $this->total_days >= $requiredAfter
            && blank($this->attachment)
        ) {
            throw ValidationException::withMessages([
                'attachment' => "Is leave type me {$requiredAfter} ya zyada days ke liye attachment required hai.",
            ]);
        }
    }
}