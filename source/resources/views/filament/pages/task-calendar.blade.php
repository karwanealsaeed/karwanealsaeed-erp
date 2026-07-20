<x-filament-panels::page>
    <style>
        .kes-calendar-shell {
            display: grid;
            gap: 18px;
        }

        .kes-calendar-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px;
            border: 1px solid rgba(7, 81, 61, .13);
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 10px 32px rgba(6, 55, 42, .06);
        }

        .kes-calendar-toolbar h2 {
            margin: 0;
            color: #123f32;
            font-size: 24px;
            font-weight: 900;
        }

        .kes-calendar-toolbar p {
            margin: 5px 0 0;
            color: #6b7d77;
            font-size: 13px;
        }

        .kes-calendar-toolbar-actions,
        .kes-calendar-month-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .kes-calendar-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            padding: 8px 13px;
            border: 1px solid rgba(7, 81, 61, .2);
            border-radius: 10px;
            color: #07513d;
            background: #fff;
            font-size: 12px;
            font-weight: 900;
            text-decoration: none;
        }

        .kes-calendar-btn.is-primary {
            border-color: #07513d;
            color: #fff;
            background: #07513d;
        }

        .kes-calendar-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 14px;
            padding: 13px 17px;
            border: 1px solid rgba(7, 81, 61, .1);
            border-radius: 13px;
            color: #60736c;
            background: #f8fbfa;
            font-size: 12px;
            font-weight: 700;
        }

        .kes-calendar-legend span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .kes-calendar-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #1769aa;
        }

        .kes-calendar-dot.is-urgent {
            background: #c62f27;
        }

        .kes-calendar-dot.is-high {
            background: #c58308;
        }

        .kes-calendar-dot.is-normal {
            background: #1769aa;
        }

        .kes-calendar-dot.is-low {
            background: #75817d;
        }

        .kes-calendar-wrapper {
            overflow-x: auto;
            border: 1px solid rgba(7, 81, 61, .13);
            border-radius: 17px;
            background: #fff;
            box-shadow: 0 12px 34px rgba(6, 55, 42, .06);
        }

        .kes-calendar-grid {
            display: grid;
            grid-template-columns:
                repeat(7, minmax(150px, 1fr));
            min-width: 1050px;
        }

        .kes-calendar-weekday {
            padding: 13px 10px;
            border-right: 1px solid rgba(7, 81, 61, .08);
            border-bottom: 1px solid rgba(7, 81, 61, .1);
            color: #597068;
            background: #f6faf8;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .05em;
            text-align: center;
            text-transform: uppercase;
        }

        .kes-calendar-weekday:nth-child(7) {
            border-right: 0;
        }

        .kes-calendar-day {
            min-height: 155px;
            padding: 10px;
            border-right: 1px solid rgba(7, 81, 61, .08);
            border-bottom: 1px solid rgba(7, 81, 61, .08);
            background: #fff;
        }

        .kes-calendar-day:nth-child(7n) {
            border-right: 0;
        }

        .kes-calendar-day.is-outside {
            background: #fafcfb;
        }

        .kes-calendar-day.is-outside
        .kes-calendar-day-number {
            color: #a2ada9;
        }

        .kes-calendar-day.is-today {
            background: #f3fbf7;
            box-shadow: inset 0 0 0 2px rgba(7, 81, 61, .25);
        }

        .kes-calendar-day-number {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 9px;
            color: #264c40;
            font-size: 12px;
            font-weight: 900;
        }

        .kes-calendar-today-label {
            padding: 3px 7px;
            border-radius: 999px;
            color: #fff;
            background: #07513d;
            font-size: 9px;
            text-transform: uppercase;
        }

        .kes-calendar-events {
            display: grid;
            gap: 6px;
        }

        .kes-calendar-event {
            display: block;
            padding: 7px 8px;
            border-left: 4px solid #1769aa;
            border-radius: 7px;
            color: #24473d;
            background: #edf6fb;
            text-decoration: none;
        }

        .kes-calendar-event.is-urgent {
            border-left-color: #c62f27;
            background: #fff0ef;
        }

        .kes-calendar-event.is-high {
            border-left-color: #c58308;
            background: #fff7e6;
        }

        .kes-calendar-event.is-low {
            border-left-color: #75817d;
            background: #f2f4f3;
        }

        .kes-calendar-event.is-overdue {
            box-shadow: inset 0 0 0 1px rgba(198, 47, 39, .3);
        }

        .kes-calendar-event-title {
            display: block;
            overflow: hidden;
            font-size: 11px;
            font-weight: 900;
            line-height: 1.35;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .kes-calendar-event-meta {
            display: flex;
            justify-content: space-between;
            gap: 5px;
            margin-top: 4px;
            color: #667a73;
            font-size: 9px;
            font-weight: 700;
        }

        .kes-calendar-empty {
            color: #a1aca8;
            font-size: 10px;
        }

        @media (max-width: 800px) {
            .kes-calendar-toolbar {
                display: block;
            }

            .kes-calendar-toolbar-actions {
                margin-top: 14px;
            }
        }
    </style>

    <div class="kes-calendar-shell">
        <header class="kes-calendar-toolbar">
            <div>
                <h2>{{ $monthLabel }}</h2>

                <p>
                    Tasks are displayed using their due date,
                    or start date when no due date exists.
                </p>
            </div>

            <div class="kes-calendar-toolbar-actions">
                @if (
                    auth()->user()?->isAdmin()
                    || auth()->user()?->hasPermission('tasks.create')
                )
                    <a
                        href="{{ route(
                            'filament.admin.resources.tasks.create'
                        ) }}"
                        class="kes-calendar-btn is-primary"
                    >
                        + New Task
                    </a>
                @endif

                <a
                    href="{{ route(
                        'filament.admin.resources.tasks.index'
                    ) }}"
                    class="kes-calendar-btn"
                >
                    List View
                </a>
            </div>
        </header>

        <div class="kes-calendar-toolbar">
            <div class="kes-calendar-month-actions">
                <a
                    href="{{ route(
                        'filament.admin.pages.task-calendar',
                        ['month' => $previousMonth],
                    ) }}"
                    class="kes-calendar-btn"
                >
                    ← Previous
                </a>

                <a
                    href="{{ route(
                        'filament.admin.pages.task-calendar',
                        ['month' => $currentMonth],
                    ) }}"
                    class="kes-calendar-btn"
                >
                    Current Month
                </a>

                <a
                    href="{{ route(
                        'filament.admin.pages.task-calendar',
                        ['month' => $nextMonth],
                    ) }}"
                    class="kes-calendar-btn"
                >
                    Next →
                </a>
            </div>
        </div>

        <div class="kes-calendar-legend">
            <span>
                <i class="kes-calendar-dot is-urgent"></i>
                Urgent
            </span>

            <span>
                <i class="kes-calendar-dot is-high"></i>
                High
            </span>

            <span>
                <i class="kes-calendar-dot is-normal"></i>
                Normal
            </span>

            <span>
                <i class="kes-calendar-dot is-low"></i>
                Low
            </span>
        </div>

        <div class="kes-calendar-wrapper">
            <div class="kes-calendar-grid">
                @foreach ($weekDays as $weekDay)
                    <div class="kes-calendar-weekday">
                        {{ $weekDay }}
                    </div>
                @endforeach

                @foreach ($calendarDays as $day)
                    <section
                        class="
                            kes-calendar-day
                            {{ $day['is_current_month']
                                ? ''
                                : 'is-outside' }}
                            {{ $day['is_today']
                                ? 'is-today'
                                : '' }}
                        "
                    >
                        <div class="kes-calendar-day-number">
                            <span>
                                {{ $day['date']->format('d') }}
                            </span>

                            @if ($day['is_today'])
                                <span class="kes-calendar-today-label">
                                    Today
                                </span>
                            @endif
                        </div>

                        <div class="kes-calendar-events">
                            @forelse ($day['tasks'] as $task)
                                @php
                                    $priorityClass = match (
                                        $task->priority
                                    ) {
                                        \App\Models\Task::PRIORITY_URGENT =>
                                            'is-urgent',
                                        \App\Models\Task::PRIORITY_HIGH =>
                                            'is-high',
                                        \App\Models\Task::PRIORITY_LOW =>
                                            'is-low',
                                        default => '',
                                    };

                                    $taskDate =
                                        $task->due_at
                                        ?? $task->starts_at;
                                @endphp

                                <a
                                    href="{{ route(
                                        'filament.admin.resources.tasks.view',
                                        ['record' => $task],
                                    ) }}"
                                    class="
                                        kes-calendar-event
                                        {{ $priorityClass }}
                                        {{ $task->is_overdue
                                            ? 'is-overdue'
                                            : '' }}
                                    "
                                    title="{{ $task->title }}"
                                >
                                    <span class="kes-calendar-event-title">
                                        {{ $task->title }}
                                    </span>

                                    <span class="kes-calendar-event-meta">
                                        <span>
                                            {{ $taskDate?->format('h:i A') }}
                                        </span>

                                        <span>
                                            {{ $task->status }}
                                        </span>
                                    </span>

                                    @if ($task->customer)
                                        <span class="kes-calendar-event-meta">
                                            {{ $task->customer->name }}
                                        </span>
                                    @endif
                                </a>
                            @empty
                                <span class="kes-calendar-empty">
                                    No tasks
                                </span>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </div>
</x-filament-panels::page>