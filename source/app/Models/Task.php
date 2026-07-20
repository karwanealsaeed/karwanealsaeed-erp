<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Task extends Model
{
    public const STATUS_PENDING = 'Pending';

    public const STATUS_IN_PROGRESS = 'In Progress';

    public const STATUS_COMPLETED = 'Completed';

    public const STATUS_CANCELLED = 'Cancelled';

    public const PRIORITY_LOW = 'Low';

    public const PRIORITY_NORMAL = 'Normal';

    public const PRIORITY_HIGH = 'High';

    public const PRIORITY_URGENT = 'Urgent';

    protected $fillable = [
        'title',
        'description',
        'task_type',
        'status',
        'priority',
        'assigned_to_id',
        'created_by_id',
        'completed_by_id',
        'customer_id',
        'related_type',
        'related_id',
        'starts_at',
        'due_at',
        'reminder_at',
        'completed_at',
        'is_private',
        'completion_notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'due_at' => 'datetime',
            'reminder_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
            'completed_at' => 'datetime',
            'is_private' => 'boolean',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Task $task): void {
            if (
                blank($task->created_by_id)
                && auth()->check()
            ) {
                $task->created_by_id = auth()->id();
            }
        });

        static::saving(function (Task $task): void {
            if ($task->isDirty('reminder_at')) {
                $task->reminder_sent_at = null;
            }

            if ($task->status === self::STATUS_COMPLETED) {
                $task->completed_at ??= now();

                if (
                    blank($task->completed_by_id)
                    && auth()->check()
                ) {
                    $task->completed_by_id = auth()->id();
                }

                return;
            }

            if ($task->isDirty('status')) {
                $task->completed_at = null;
                $task->completed_by_id = null;
            }
        });
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_to_id',
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by_id',
        );
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'completed_by_id',
        );
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ]);
    }

    public function scopeAssignedTo(
        Builder $query,
        int $userId,
    ): Builder {
        return $query->where(
            'assigned_to_id',
            $userId,
        );
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query
            ->open()
            ->whereNotNull('due_at')
            ->where('due_at', '<', now());
    }

    public function scopeDueToday(Builder $query): Builder
    {
        return $query
            ->open()
            ->whereNotNull('due_at')
            ->whereDate('due_at', today());
    }

    public function scopeUpcoming(
        Builder $query,
        int $days = 7,
    ): Builder {
        return $query
            ->open()
            ->whereNotNull('due_at')
            ->whereBetween('due_at', [
                now(),
                now()->addDays($days),
            ]);
    }


    public function scopeReminderPending(
        Builder $query,
    ): Builder {
        return $query
            ->reminderDue()
            ->whereNull('reminder_sent_at');
    }
    public function scopeReminderDue(
        Builder $query,
    ): Builder {
        return $query
            ->open()
            ->whereNotNull('reminder_at')
            ->where('reminder_at', '<=', now());
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_at !== null
            && $this->due_at->isPast()
            && ! in_array(
                $this->status,
                [
                    self::STATUS_COMPLETED,
                    self::STATUS_CANCELLED,
                ],
                true,
            );
    }

    public function getIsDueTodayAttribute(): bool
    {
        return $this->due_at !== null
            && $this->due_at->isToday()
            && ! in_array(
                $this->status,
                [
                    self::STATUS_COMPLETED,
                    self::STATUS_CANCELLED,
                ],
                true,
            );
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'success',
            self::STATUS_IN_PROGRESS => 'info',
            self::STATUS_CANCELLED => 'gray',
            default => $this->is_overdue
                ? 'danger'
                : 'warning',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            self::PRIORITY_URGENT => 'danger',
            self::PRIORITY_HIGH => 'warning',
            self::PRIORITY_LOW => 'gray',
            default => 'info',
        };
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => self::STATUS_PENDING,
            self::STATUS_IN_PROGRESS =>
                self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED =>
                self::STATUS_COMPLETED,
            self::STATUS_CANCELLED =>
                self::STATUS_CANCELLED,
        ];
    }

    public static function priorityOptions(): array
    {
        return [
            self::PRIORITY_LOW => self::PRIORITY_LOW,
            self::PRIORITY_NORMAL => self::PRIORITY_NORMAL,
            self::PRIORITY_HIGH => self::PRIORITY_HIGH,
            self::PRIORITY_URGENT => self::PRIORITY_URGENT,
        ];
    }

    public static function typeOptions(): array
    {
        return [
            'General' => 'General',
            'Customer Follow-up' => 'Customer Follow-up',
            'Payment Follow-up' => 'Payment Follow-up',
            'Visa Follow-up' => 'Visa Follow-up',
            'Umrah Follow-up' => 'Umrah Follow-up',
            'Flight Follow-up' => 'Flight Follow-up',
            'Hotel Follow-up' => 'Hotel Follow-up',
            'Document Reminder' => 'Document Reminder',
            'Internal Task' => 'Internal Task',
        ];
    }
}