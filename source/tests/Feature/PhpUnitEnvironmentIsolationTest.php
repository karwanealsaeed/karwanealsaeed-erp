<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PhpUnitEnvironmentIsolationTest extends TestCase
{
    public function test_phpunit_is_forced_away_from_the_local_erp_database(): void
    {
        $this->assertSame(
            'testing',
            getenv(
                'APP_ENV',
            ),
        );

        $this->assertSame(
            'testing',
            $_ENV['APP_ENV']
                ?? null,
        );

        $this->assertSame(
            'testing',
            $_SERVER['APP_ENV']
                ?? null,
        );

        $this->assertTrue(
            app()->environment(
                'testing',
            ),
        );

        $this->assertSame(
            'sqlite',
            getenv(
                'DB_CONNECTION',
            ),
        );

        $this->assertSame(
            'sqlite',
            $_ENV['DB_CONNECTION']
                ?? null,
        );

        $this->assertSame(
            'sqlite',
            $_SERVER['DB_CONNECTION']
                ?? null,
        );

        $this->assertSame(
            ':memory:',
            getenv(
                'DB_DATABASE',
            ),
        );

        $this->assertSame(
            'sqlite',
            config(
                'database.default',
            ),
        );

        $this->assertSame(
            ':memory:',
            config(
                'database.connections.sqlite.database',
            ),
        );

        $this->assertSame(
            'sqlite',
            DB::connection()->getDriverName(),
        );

        $this->assertNotSame(
            'karwan_erp',
            DB::connection()->getDatabaseName(),
        );

        $this->assertSame(
            'array',
            config(
                'session.driver',
            ),
        );

        $this->assertSame(
            'array',
            config(
                'cache.default',
            ),
        );

        $this->assertSame(
            'sync',
            config(
                'queue.default',
            ),
        );

        $this->assertFalse(
            isset(
                $_ENV['DB_URL'],
                $_SERVER['DB_URL'],
            ),
        );

        $this->assertFalse(
            isset(
                $_ENV['DATABASE_URL'],
                $_SERVER['DATABASE_URL'],
            ),
        );

        $this->assertStringEndsWith(
            'bootstrap/cache/config-phpunit.php',
            str_replace(
                '\\',
                '/',
                (string) getenv(
                    'APP_CONFIG_CACHE',
                ),
            ),
        );
    }
}
