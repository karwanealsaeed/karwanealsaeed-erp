<?php

declare(strict_types=1);

namespace App\Contracts;

interface KarwanAiProvider
{
    public function name(): string;

    public function answer(
        string $message,
    ): array;
}
