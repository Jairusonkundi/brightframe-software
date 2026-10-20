<?php
/**
 * Shared application bootstrap — environment, logging, and database access.
 *
 * Every entry point should require this file instead of opening a database
 * connection directly:
 *
 *     require_once __DIR__ . '/config/functions.php';
 *     $pdo = db();
 *
 * Credentials come from the project-root .env file (gitignored; see
 * .env.example). Nothing here hardcodes a host, user, or password.
 *
 * Database access is deliberately non-blocking for the public site. When
 * MySQL is unreachable, db() logs the real reason to error.log and returns
 * a quiet stand-in connection whose reads come back empty, so pages still
 * render instead of dying with "something went wrong connecting to the
 * database". Code paths that write data must call db_available() first and
 * fail honestly — the form handlers and the admin area do exactly that.
 */

if (!defined('BF_ROOT')) {
    define('BF_ROOT', dirname(__DIR__));
}
if (!defined('BF_ERROR_LOG')) {
    define('BF_ERROR_LOG', BF_ROOT . '/error.log');
}

/**
 * Load key=value pairs from a .env file into the process environment.
 * Existing environment variables always win (useful for real deployments).
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
        if (str_starts_with($line, 'export ')) {
            $line = trim(substr($line, 7));
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

$envFile = BF_ROOT . '/.env';
if (!is_readable($envFile)) {
    // Keep a fresh checkout from failing hard before .env exists.
    $envFile = BF_ROOT . '/.env.example';
}
load_env($envFile);

/**
 * Read a value from .env (or the real process environment).
 */
function env(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    if ($value === false) {
        $value = $_ENV[$key] ?? null;
    }
    return $value !== null && $value !== '' ? $value : $default;
}

/**
 * Boolean-ish env helper: true/1/yes/on are true; everything else false.
 */
function env_bool(string $key, bool $default = false): bool
{
    $value = env($key);
    if ($value === null) {
        return $default;
    }
    return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
}

date_default_timezone_set(env('APP_TIMEZONE', 'Africa/Nairobi'));
error_reporting(E_ALL);

// Route PHP's own errors and every error_log()/bf_log() call to error.log.
ini_set('log_errors', '1');
ini_set('error_log', BF_ERROR_LOG);

if (PHP_SAPI !== 'cli') {
    // Raw errors stay out of visitors' browsers unless explicitly debugging.
    $bfDebug = env_bool('APP_DEBUG');
    ini_set('display_errors', $bfDebug ? '1' : '0');

    if (!$bfDebug) {
        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            bf_log(sprintf('%s in %s:%d', $message, $file, $line), 'php');
            return true;
        });
    }

    set_exception_handler(static function (Throwable $e): void {
        bf_log(sprintf(
            'Uncaught %s: %s in %s:%d',
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        ));

        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
        }

        $wantsJson = stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false
            || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

        if ($wantsJson) {
            echo json_encode([
                'success' => false,
                'message' => 'Sorry, something went wrong. Please try again later.',
            ]);
        } else {
            echo '<!doctype html><html lang="en"><head><meta charset="utf-8">'
                . '<meta name="viewport" content="width=device-width, initial-scale=1">'
                . '<title>Something went wrong</title></head>'
                . '<body style="font-family:system-ui,-apple-system,sans-serif;max-width:40rem;margin:12vh auto;padding:0 1.5rem;color:#122533">'
                . '<h1 style="font-size:1.5rem">Sorry, something went wrong.</h1>'
                . '<p style="color:#4b5f6b;line-height:1.6">We hit an unexpected error. It has been logged — please try again in a few minutes.</p>'
                . '</body></html>';
        }
    });
}

/**
 * Write a timestamped line to error.log.
 */
function bf_log(string $message, string $level = 'error'): void
{
    error_log(sprintf('[%s] %s: %s', date('Y-m-d H:i:s'), strtoupper($level), rtrim($message)));
}

/**
 * Minimal stand-in for a prepared statement when the database is offline.
 * Reads return empty results; nothing is ever written.
 */
final class BrightframeDbResult
{
    public function execute(array $params = []): bool
    {
        return false;
    }

    public function fetch(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): mixed
    {
        return false;
    }

    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        return [];
    }

    public function fetchColumn(int $column = 0): mixed
    {
        return false;
    }

    public function fetchObject(?string $class = 'stdClass', array $constructorArgs = []): mixed
    {
        return false;
    }

    public function rowCount(): int
    {
        return 0;
    }

    public function closeCursor(): bool
    {
        return true;
    }
}

/**
 * Quiet stand-in for PDO when the database cannot be reached.
 */
final class BrightframeDbFallback
{
    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): BrightframeDbResult
    {
        return new BrightframeDbResult();
    }

    public function prepare(string $query, array $options = []): BrightframeDbResult
    {
        return new BrightframeDbResult();
    }

    public function exec(string $statement): int
    {
        return 0;
    }

    public function lastInsertId(?string $name = null): string
    {
        return '0';
    }

    public function beginTransaction(): bool
    {
        return false;
    }

    public function inTransaction(): bool
    {
        return false;
    }

    public function commit(): bool
    {
        return false;
    }

    public function rollBack(): bool
    {
        return false;
    }

    public function quote(string $string, int $type = PDO::PARAM_STR): string
    {
        return "'" . str_replace("'", "''", $string) . "'";
    }
}

/**
 * Lazily open (and reuse) the PDO connection.
 *
 * Returns a real PDO when .env is configured and MySQL is reachable; logs
 * the failure and returns BrightframeDbFallback otherwise.
 */
function db(): PDO|BrightframeDbFallback
{
    static $connection = null;
    static $attempted  = false;

    if ($attempted) {
        return $connection;
    }
    $attempted = true;

    $host    = env('DB_HOST', 'localhost');
    $name    = env('DB_NAME', 'brightframe_db');
    $user    = env('DB_USER', 'root');
    $pass    = env('DB_PASS', '');
    $port    = env('DB_PORT', '3306');
    $timeout = (int) env('DB_TIMEOUT', '5');

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
    if ($timeout > 0) {
        $dsn .= ";connect_timeout={$timeout}";
    }

    try {
        $connection = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (Throwable $e) {
        // Log the reason (never the credentials) and keep the site up.
        bf_log(sprintf(
            'Database connection failed: %s (host=%s, database=%s)',
            $e->getMessage(),
            $host,
            $name
        ));
        $GLOBALS['bf_db_error'] = $e->getMessage();
        $connection = new BrightframeDbFallback();
    }

    return $connection;
}

/**
 * True when a real PDO connection is available.
 */
function db_available(): bool
{
    return db() instanceof PDO;
}

/**
 * The last database connection error, if any (null when connected).
 */
function db_error(): ?string
{
    return $GLOBALS['bf_db_error'] ?? null;
}
