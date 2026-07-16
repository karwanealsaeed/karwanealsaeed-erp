<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Mail;

class EmailSmtpTest extends Page
{
    protected string $view = 'filament.pages.email-smtp-test';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-envelope';

    protected static string | \UnitEnum | null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'SMTP Email Test';

    protected static ?int $navigationSort = 23;

    protected static ?string $title = 'SMTP Email Test';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'to_email' => auth()->user()?->email,
            'subject' => 'Karwan E Al Saeed ERP - SMTP Test',
            'message' => 'This is a test email from Karwan E Al Saeed ERP.',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                TextInput::make('to_email')
                    ->label('To Email')
                    ->email()
                    ->required(),

                TextInput::make('subject')
                    ->label('Subject')
                    ->required(),

                Textarea::make('message')
                    ->label('Message')
                    ->rows(6)
                    ->required(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send_test_email')
                ->label('Send Test Email')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->action('sendTestEmail'),
        ];
    }

    public function sendTestEmail(): void
    {
        $data = $this->form->getState();

        try {
            Mail::raw($data['message'], function ($message) use ($data): void {
                $message
                    ->to($data['to_email'])
                    ->subject($data['subject']);
            });

            Notification::make()
                ->title('Test email sent successfully.')
                ->success()
                ->send();
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('Email send failed.')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function mailConfig(): array
    {
        return [
            'MAIL_MAILER' => config('mail.default'),
            'MAIL_HOST' => config('mail.mailers.smtp.host'),
            'MAIL_PORT' => config('mail.mailers.smtp.port'),
            'MAIL_FROM_ADDRESS' => config('mail.from.address'),
            'MAIL_FROM_NAME' => config('mail.from.name'),
        ];
    }
}