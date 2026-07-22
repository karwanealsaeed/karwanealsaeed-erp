<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use App\Models\User;
use App\Support\RbacPermissionRegistry;

trait HasRbacPageAuthorization
{
    public static function canAccess(): bool
    {
        $user =
            auth()->user();

        return RbacPermissionRegistry::allowsPage(
            $user instanceof User
                ? $user
                : null,
            static::class,
        );
    }
}
