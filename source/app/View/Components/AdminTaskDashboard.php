<?php

namespace App\View\Components;

use App\Models\Task;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class AdminTaskDashboard extends Component
{
    public int $openCount = 0;

    public int $overdueCount = 0;

    public int $dueTodayCount = 0;

    public int $reminderDueCount = 0;

    public Collection $tasks;

    public function __construct()
    {
        $baseQuery = $this->visibleTasksQuery();

        $this->openCount = (clone $baseQuery)
            ->open()
            ->count();

        $this->overdueCount = (clone $baseQuery)
            ->overdue()
            ->count();

        $this->dueTodayCount = (clone $baseQuery)
            ->dueToday()
            ->count();

        $this->reminderDueCount = (clone $baseQuery)
            ->reminderPending()
            ->count();

        $this->tasks = $this->visibleTasksQuery()
            ->with([
                'assignedTo:id,name',
                'customer:id,name',
            ])
            ->open()
            ->orderByRaw(
                'CASE
                    WHEN due_at IS NULL THEN 1
                    ELSE 0
                END',
            )
            ->orderBy('due_at')
            ->orderByRaw(
                "CASE priority
                    WHEN 'Urgent' THEN 1
                    WHEN 'High' THEN 2
                    WHEN 'Normal' THEN 3
                    WHEN 'Low' THEN 4
                    ELSE 5
                END",
            )
            ->limit(8)
            ->get();
    }

    public function shouldRender(): bool
    {
        $user = auth()->user();

        return ($user?->isAdmin() ?? false)
            || ($user?->hasPermission('tasks.view') ?? false);
    }

    public function render(): View
    {
        return view('components.admin-task-dashboard');
    }

    private function visibleTasksQuery(): Builder
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
                    ->where('assigned_to_id', $user->id)
                    ->orWhere('created_by_id', $user->id);
            },
        );
    }
}