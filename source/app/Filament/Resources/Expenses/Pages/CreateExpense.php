<?php

namespace App\Filament\Resources\Expenses\Pages;

use App\Filament\Resources\Expenses\ExpenseResource;
use App\Models\Expense;
use Filament\Resources\Pages\CreateRecord;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $last = Expense::max('id') + 1;

        $data['expense_no'] = 'EXP-' . str_pad($last, 5, '0', STR_PAD_LEFT);

        return $data;
    }

    protected function fillForm(): void
    {
        parent::fillForm();

        $last = Expense::max('id') + 1;

        $this->form->fill([
            'expense_no' => 'EXP-' . str_pad($last, 5, '0', STR_PAD_LEFT),
        ]);
    }
}
