<?php

/**
 * Laragon (et certains hôtes) injectent DB_DATABASE dans l'environnement système,
 * ce qui empêche le .env de prendre le dessus. On force la lecture depuis .env.
 */
$envFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';

if (! is_readable($envFile)) {
    return;
}

foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);

    if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
        continue;
    }

    [$key, $value] = explode('=', $line, 2);
    $key = trim($key);

    if ($key !== 'DB_DATABASE') {
        continue;
    }

    $value = trim($value, " \t\"'");

    putenv("DB_DATABASE={$value}");
    $_ENV['DB_DATABASE'] = $value;
    $_SERVER['DB_DATABASE'] = $value;

    break;
}
