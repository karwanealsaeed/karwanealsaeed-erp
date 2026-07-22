<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use App\Models\User;
use App\Support\RbacPermissionRegistry;
use Illuminate\Database\Eloquent\Model;

trait HasRbacResourceAuthorization
{
    public static function canAccess(): bool
    {
        return static::canViewAny();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return static::allowsRbacAction(
            'view',
        );
    }

    public static function canView(Model $record): bool
    {
        return static::allowsRbacAction(
            'view',
        );
    }

    public static function canCreate(): bool
    {
        return static::allowsRbacAction(
            'create',
        );
    }

    public static function canEdit(Model $record): bool
    {
        return static::allowsRbacAction(
            'edit',
        );
    }

    public static function canDelete(Model $record): bool
    {
        return static::allowsRbacAction(
            'delete',
        );
    }

    public static function canDeleteAny(): bool
    {
        return static::allowsRbacAction(
            'delete',
        );
    }

    private static function allowsRbacAction(string $action): bool
    {
        $user =
            auth()->user();

        return RbacPermissionRegistry::allowsResourceAction(
            $user instanceof User
                ? $user
                : null,
            static::class,
            $action,
        );
    }
}
