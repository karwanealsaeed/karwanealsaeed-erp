<?php

namespace App\Filament\Pages;

use App\Models\Task;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Throwable;

class TaskCalendar extends Page
{
    protected static string | \BackedEnum | null $navigationIcon =
        'heroicon-o-calendar-days';

    protected static string | \UnitEnum | null $navigationGroup =
        'CRM & Follow-ups';

    protected static ?string $navigationLabel =
        'Task Calendar';

    protected static ?string $title =
        'Task Calendar';

    protected static ?int $navigationSort = 11;

    protected string $view = 'filament.pages.task-calendar';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return ($user?->isAdmin() ?? false)
            || ($user?->hasPermission('tasks.view') ?? false);
    }

    public static function getNavigationBadge(): ?string
    {
        if (! static::canAccess()) {
            return null;
        }

        $count = static::visibleTasksQuery()
            ->overdue()
            ->count();

        return $count > 0
            ? (string) $count
            : null;
    }

    protected function getViewData(): array
    {
        $month = $this->resolveMonth();

        $calendarStart = $month
            ->copy()
            ->startOfMonth()
            ->startOfWeek(Carbon::MONDAY)
            ->startOfDay();

        $calendarEnd = $month
            ->copy()
            ->endOfMonth()
            ->endOfWeek(Carbon::SUNDAY)
            ->endOfDay();

        $tasks = static::visibleTasksQuery()
            ->with([
                'assignedTo:id,name',
                'customer:id,name',
            ])
            ->where(
                function (Builder $query) use (
                    $calendarStart,
                    $calendarEnd,
                ): void {
                    $query
                        ->whereBetween(
                            'due_at',
                            [
                                $calendarStart,
                                $calendarEnd,
                            ],
                        )
                        ->orWhere(
                            function (Builder $query) use (
                                $calendarStart,
                                $calendarEnd,
                            ): void {
                                $query
                                    ->whereNull('due_at')
                                    ->whereBetween(
                                        'starts_at',
                                        [
                                            $calendarStart,
                                            $calendarEnd,
                                        ],
                                    );
                            },
                        );
                },
            )
            ->orderBy('due_at')
            ->orderBy('starts_at')
            ->orderBy('priority')
            ->get();

        $tasksByDate = $tasks->groupBy(
            function (Task $task): string {
                return (
                    $task->due_at
                    ?? $task->starts_at
                )->toDateString();
            },
        );

        $calendarDays = collect(
            CarbonPeriod::create(
                $calendarStart,
                '1 day',
                $calendarEnd,
            ),
        )->map(
            function (Carbon $date) use (
                $month,
                $tasksByDate,
            ): array {
                return [
                    'date' => $date->copy(),
                    'is_current_month' =>
                        $date->month === $month->month
                        && $date->year === $month->year,
                    'is_today' => $date->isToday(),
                    'tasks' => $tasksByDate->get(
                        $date->toDateString(),
                        collect(),
                    ),
                ];
            },
        );

        return [
            'month' => $month,
            'monthLabel' => $month->format('F Y'),
            'previousMonth' => $month
                ->copy()
                ->subMonth()
                ->format('Y-m'),
            'nextMonth' => $month
                ->copy()
                ->addMonth()
                ->format('Y-m'),
            'currentMonth' => now()->format('Y-m'),
            'calendarDays' => $calendarDays,
            'weekDays' => [
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday',
                'Sunday',
            ],
        ];
    }

    private function resolveMonth(): Carbon
    {
        $requestedMonth = trim(
            request()->string('month')->toString(),
        );

        if ($requestedMonth === '') {
            return now()->startOfMonth();
        }

        try {
            return Carbon::createFromFormat(
                '!Y-m',
                $requestedMonth,
            )->startOfMonth();
        } catch (Throwable) {
            return now()->startOfMonth();
        }
    }

    private static function visibleTasksQuery(): Builder
    {
        $query = Task::query();
        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(
            function (Builder $query) use ($user): void {
                $query
                    ->where(
                        'assigned_to_id',
                        $user->id,
                    )
                    ->orWhere(
                        'created_by_id',
                        $user->id,
                    );
            },
        );
    }
}