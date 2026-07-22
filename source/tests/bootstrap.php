<?php

declare(strict_types=1);

$projectRoot =
    dirname(
        __DIR__,
    );

$testingEnvironment = [
    'APP_ENV' =>
        'testing',
    'APP_CONFIG_CACHE' =>
        'bootstrap/cache/config-phpunit.php',
    'APP_SERVICES_CACHE' =>
        'bootstrap/cache/services-phpunit.php',
    'APP_PACKAGES_CACHE' =>
        'bootstrap/cache/packages-phpunit.php',
    'APP_EVENTS_CACHE' =>
        'bootstrap/cache/events-phpunit.php',
    'APP_ROUTES_CACHE' =>
        'bootstrap/cache/routes-phpunit.php',
    'DB_CONNECTION' =>
        'sqlite',
    'DB_DATABASE' =>
        ':memory:',
    'SESSION_DRIVER' =>
        'array',
    'CACHE_STORE' =>
        'array',
    'QUEUE_CONNECTION' =>
        'sync',
    'MAIL_MAILER' =>
        'array',
    'BCRYPT_ROUNDS' =>
        '4',
    'LOG_CHANNEL' =>
        'null',
    'TELESCOPE_ENABLED' =>
        'false',
    'PULSE_ENABLED' =>
        'false',
    'NIGHTWATCH_ENABLED' =>
        'false',
];

foreach (
    [
        'DB_URL',
        'DATABASE_URL',
    ]
    as $variable
) {
    unset(
        $_ENV[$variable],
        $_SERVER[$variable],
    );

    putenv(
        $variable,
    );
}

foreach ($testingEnvironment as $name => $value) {
    $_ENV[$name] =
        $value;

    $_SERVER[$name] =
        $value;

    putenv(
        $name.'='.$value,
    );
}

require $projectRoot.'/vendor/autoload.php';
