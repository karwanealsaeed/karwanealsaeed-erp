<section class="kes-task-dashboard">
    <style>
        .kes-task-dashboard {
            margin-top: 24px;
        }

        .kes-task-dashboard-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 16px;
        }

        .kes-task-dashboard-header h2 {
            margin: 0 0 4px;
            color: #123f32;
            font-size: 23px;
            font-weight: 800;
        }

        .kes-task-dashboard-header p {
            margin: 0;
            color: #687a74;
            font-size: 14px;
        }

        .kes-task-dashboard-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .kes-task-dashboard-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            padding: 8px 14px;
            border: 1px solid rgba(7, 81, 61, .18);
            border-radius: 10px;
            color: #07513d;
            background: #fff;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
        }

        .kes-task-dashboard-actions a:first-child {
            border-color: #07513d;
            color: #fff;
            background: #07513d;
        }

        .kes-task-stats-grid {
            display: grid;
            grid-template-columns:
                repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .kes-task-stat {
            position: relative;
            overflow: hidden;
            padding: 18px;
            border: 1px solid rgba(7, 81, 61, .12);
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 10px 30px rgba(6, 55, 42, .06);
        }

        .kes-task-stat::after {
            position: absolute;
            right: -18px;
            bottom: -28px;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(7, 81, 61, .06);
            content: "";
        }

        .kes-task-stat span {
            display: block;
            margin-bottom: 7px;
            color: #667972;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .kes-task-stat strong {
            position: relative;
            z-index: 1;
            color: #123f32;
            font-size: 29px;
            line-height: 1;
        }

        .kes-task-stat.is-danger {
            border-color: rgba(196, 46, 46, .24);
            background: #fff8f8;
        }

        .kes-task-stat.is-danger strong {
            color: #b42318;
        }

        .kes-task-stat.is-warning {
            border-color: rgba(190, 133, 20, .26);
            background: #fffaf0;
        }

        .kes-task-stat.is-warning strong {
            color: #9a6700;
        }

        .kes-task-stat.is-info {
            border-color: rgba(23, 105, 170, .22);
            background: #f7fbff;
        }

        .kes-task-stat.is-info strong {
            color: #1769aa;
        }

        .kes-task-panel {
            overflow: hidden;
            border: 1px solid rgba(7, 81, 61, .12);
            border-radius: 17px;
            background: #fff;
            box-shadow: 0 12px 34px rgba(6, 55, 42, .06);
        }

        .kes-task-panel-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 17px 20px;
            border-bottom: 1px solid rgba(7, 81, 61, .1);
        }

        .kes-task-panel-heading h3 {
            margin: 0;
            color: #123f32;
            font-size: 17px;
            font-weight: 800;
        }

        .kes-task-panel-heading a {
            color: #8a6816;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
        }

        .kes-task-table-wrap {
            overflow-x: auto;
        }

        .kes-task-table {
            width: 100%;
            border-collapse: collapse;
        }

        .kes-task-table th,
        .kes-task-table td {
            padding: 13px 15px;
            border-bottom: 1px solid rgba(7, 81, 61, .08);
            text-align: left;
            vertical-align: middle;
        }

        .kes-task-table th {
            color: #667972;
            background: #f8fbfa;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .04em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .kes-task-table td {
            color: #334f47;
            font-size: 13px;
        }

        .kes-task-table tr:last-child td {
            border-bottom: 0;
        }

        .kes-task-table tr.is-overdue td {
            background: #fff8f8;
        }

        .kes-task-title {
            min-width: 220px;
        }

        .kes-task-title a {
            color: #123f32;
            font-weight: 800;
            text-decoration: none;
        }

        .kes-task-title small {
            display: block;
            margin-top: 4px;
            color: #7a8a85;
        }

        .kes-task-badge {
            display: inline-flex;
            align-items: center;
            min-height: 25px;
            padding: 4px 9px;
            border-radius: 999px;
            color: #35564c;
            background: #eef5f2;
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
        }

        .kes-task-badge.is-danger {
            color: #9f2018;
            background: #feeceb;
        }

        .kes-task-badge.is-warning {
            color: #8a5b00;
            background: #fff1cf;
        }

        .kes-task-badge.is-success {
            color: #176641;
            background: #e7f7ed;
        }

        .kes-task-badge.is-info {
            color: #175f91;
            background: #e7f3fb;
        }

        .kes-task-row-actions {
            display: flex;
            gap: 8px;
            white-space: nowrap;
        }

        .kes-task-row-actions a {
            color: #07513d;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
        }

        .kes-task-empty {
            padding: 34px 20px;
            color: #70817b;
            text-align: center;
        }

        @media (max-width: 1050px) {
            .kes-task-stats-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 700px) {
            .kes-task-dashboard-header {
                display: block;
            }

            .kes-task-dashboard-actions {
                margin-top: 13px;
            }

            .kes-task-stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="kes-task-dashboard-header">
        <div>
            <h2>Tasks & Follow-ups</h2>

            <p>
                Your current work, customer follow-ups and reminders.
            </p>
        </div>

        <div class="kes-task-dashboard-actions">
            @if (
                auth()->user()?->isAdmin()
                || auth()->user()?->hasPermission('tasks.create')
            )
                <a href="{{ route(
                    'filament.admin.resources.tasks.create'
                ) }}">
                    + New Task
                </a>
            @endif

            <a href="{{ route(
                'filament.admin.pages.task-calendar'
            ) }}">
                Calendar
            </a>
            <a href="{{ route(
                'filament.admin.resources.tasks.index'
            ) }}">
                View All Tasks
            </a>
        </div>
    </div>

    <div class="kes-task-stats-grid">
        <article class="kes-task-stat">
            <span>Open Tasks</span>
            <strong>{{ number_format($openCount) }}</strong>
        </article>

        <article class="kes-task-stat is-danger">
            <span>Overdue</span>
            <strong>{{ number_format($overdueCount) }}</strong>
        </article>

        <article class="kes-task-stat is-warning">
            <span>Due Today</span>
            <strong>{{ number_format($dueTodayCount) }}</strong>
        </article>

        <article class="kes-task-stat is-info">
            <span>Reminders Due</span>
            <strong>{{ number_format($reminderDueCount) }}</strong>
        </article>
    </div>

    <div class="kes-task-panel">
        <div class="kes-task-panel-heading">
            <h3>My Upcoming Tasks</h3>

            <a href="{{ route(
                'filament.admin.resources.tasks.index',
                ['tableFilters' => [
                    'my_tasks' => ['isActive' => true],
                ]],
            ) }}">
                Open Task Manager →
            </a>
        </div>

        <div class="kes-task-table-wrap">
            <table class="kes-task-table">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Assigned To</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Due</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($tasks as $task)
                        @php
                            $statusClass = match ($task->status) {
                                \App\Models\Task::STATUS_COMPLETED =>
                                    'is-success',
                                \App\Models\Task::STATUS_IN_PROGRESS =>
                                    'is-info',
                                \App\Models\Task::STATUS_CANCELLED =>
                                    '',
                                default => $task->is_overdue
                                    ? 'is-danger'
                                    : 'is-warning',
                            };

                            $priorityClass = match ($task->priority) {
                                \App\Models\Task::PRIORITY_URGENT =>
                                    'is-danger',
                                \App\Models\Task::PRIORITY_HIGH =>
                                    'is-warning',
                                \App\Models\Task::PRIORITY_LOW =>
                                    '',
                                default => 'is-info',
                            };
                        @endphp

                        <tr class="{{ $task->is_overdue
                            ? 'is-overdue'
                            : '' }}">
                            <td class="kes-task-title">
                                <a href="{{ route(
                                    'filament.admin.resources.tasks.view',
                                    ['record' => $task],
                                ) }}">
                                    {{ $task->title }}
                                </a>

                                <small>
                                    {{ $task->customer?->name
                                        ?: $task->task_type }}
                                </small>
                            </td>

                            <td>
                                {{ $task->assignedTo?->name
                                    ?: 'Unassigned' }}
                            </td>

                            <td>
                                <span class="kes-task-badge {{ $priorityClass }}">
                                    {{ $task->priority }}
                                </span>
                            </td>

                            <td>
                                <span class="kes-task-badge {{ $statusClass }}">
                                    {{ $task->status }}
                                </span>
                            </td>

                            <td>
                                @if ($task->due_at)
                                    <span class="{{ $task->is_overdue
                                        ? 'kes-task-badge is-danger'
                                        : '' }}">
                                        {{ $task->due_at
                                            ->format('d M Y h:i A') }}
                                    </span>
                                @else
                                    No due date
                                @endif
                            </td>

                            <td>
                                <div class="kes-task-row-actions">
                                    <a href="{{ route(
                                        'filament.admin.resources.tasks.view',
                                        ['record' => $task],
                                    ) }}">
                                        View
                                    </a>

                                    @if (
                                        auth()->user()?->isAdmin()
                                        || auth()->user()
                                            ?->hasPermission('tasks.edit')
                                    )
                                        <a href="{{ route(
                                            'filament.admin.resources.tasks.edit',
                                            ['record' => $task],
                                        ) }}">
                                            Edit
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="6"
                                class="kes-task-empty"
                            >
                                No open tasks assigned to you.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>