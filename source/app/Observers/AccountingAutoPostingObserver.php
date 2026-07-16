<?php

namespace App\Observers;

use App\Services\AccountingAutoPostingService;
use Illuminate\Database\Eloquent\Model;

class AccountingAutoPostingObserver
{
    public function saved(Model $model): void
    {
        app(AccountingAutoPostingService::class)->postFromModel($model);
    }

    public function deleted(Model $model): void
    {
        app(AccountingAutoPostingService::class)->removeForModel($model);
    }
}