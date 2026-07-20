<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class SendTaskReminders extends Command
{
    protected $signature =
        'tasks:send-reminders
        {--dry-run : Display reminders without sending}';

    protected $description =
        'Send due Task and Follow-up reminders to staff.';

    public function handle(): int
    {
        $tasks = Task::query()
            ->with([
                'assignedTo',
                'creator',
                'customer',
            ])
            ->reminderPending()
            ->orderBy('reminder_at')
            ->get();

        if ($tasks->isEmpty()) {
            $this->info(
                'No pending task reminders found.',
            );

            return self::SUCCESS;
        }

        $sent = 0;
        $skipped = 0;

        foreach ($tasks as $task) {
            $recipient = $this->recipientFor($task);

            if (! $recipient) {
                $this->warn(
                    "Skipped Task #{$task->id}: "
                    .'no active recipient.',
                );

                $skipped++;

                continue;
            }

            if ($this->option('dry-run')) {
                $this->line(
                    "Would notify {$recipient->name}: "
                    .$task->title,
                );

                continue;
            }

            $recipient->notifyNow(
                Notification::make()
                    ->title(
                        'Task Reminder: '.$task->title,
                    )
                    ->body(
                        $this->notificationBody($task),
                    )
                    ->icon('heroicon-o-bell-alert')
                    ->color(
                        $task->is_overdue
                            ? 'danger'
                            : 'warning',
                    )
                    ->actions([
                        Action::make('view')
                            ->label('View Task')
                            ->button()
                            ->url(
                                route(
                                    'filament.admin.resources.tasks.view',
                                    ['record' => $task],
                                ),
                            )
                            ->markAsRead(),
                    ])
                    ->toDatabase(),
            );

            $task->forceFill([
                'reminder_sent_at' => now(),
            ])->saveQuietly();

            $this->info(
                "Reminder sent to {$recipient->name}: "
                .$task->title,
            );

            $sent++;
        }

        if ($this->option('dry-run')) {
            $this->info(
                'Dry run completed. No reminders were sent.',
            );

            return self::SUCCESS;
        }

        $this->info(
            "Completed: {$sent} sent, {$skipped} skipped.",
        );

        return self::SUCCESS;
    }

    private function recipientFor(Task $task): ?User
    {
        if ($task->assignedTo?->is_active) {
            return $task->assignedTo;
        }

        if ($task->creator?->is_active) {
            return $task->creator;
        }

        return null;
    }

    private function notificationBody(Task $task): string
    {
        $details = [];

        if ($task->customer) {
            $details[] =
                'Customer: '.$task->customer->name;
        }

        if ($task->due_at) {
            $details[] =
                'Due: '
                .$task->due_at->format(
                    'd M Y h:i A',
                );
        }

        if ($task->is_overdue) {
            $details[] =
                'This task is overdue.';
        }

        if ($details === []) {
            return 'A Task or Follow-up reminder is due.';
        }

        return implode(' | ', $details);
    }
}
