<?php
/**
 * Minimal .env loader — no Composer dependency required.
 * Reads config/.env (gitignored) and exposes values through env().
 * Falls back to config/.env.example only to keep local dev from hard-failing
 * when a developer hasn't created their own .env yet.
 */

function load_env(string $path): void
{
    static $loaded = false;
    if ($loaded || !is_readable($path)) {
        return;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        // Strip matching surrounding quotes, if present.
        if (strlen($value) >= 2 && $value[0] === $value[-1] && in_array($value[0], ['"', "'"], true)) {
            $value = substr($value, 1, -1);
        }
        if (getenv($key) === false) {
            putenv("{$key}={$value}");
        }
        $_ENV[$key] = $_ENV[$key] ?? $value;
    }

    $loaded = true;
}

$envFile = __DIR__ . '/.env';
if (!is_readable($envFile)) {
    $envFile = __DIR__ . '/.env.example';
}
load_env($envFile);

function env(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    if ($value === false) {
        $value = $_ENV[$key] ?? null;
    }
    return $value !== null && $value !== '' ? $value : $default;
}
