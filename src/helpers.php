<?php

use Illuminate\Support\Facades\Cache;

/**
 * Returns a where clause that does an equality check on the day of week of a given date column.
 * Accepts a number from 1 = Monday, etc...
 *
 * @param string $dateColumn
 * @param int $value
 * @return string
 */
function dayOfWeekSqlClause(string $dateColumn, int &$value) {
    $databaseConnection = \Illuminate\Support\Facades\DB::connection();

    if ($databaseConnection instanceof \Illuminate\Database\MySqlConnection) {
        $value--;
        return sprintf('WEEKDAY(%s) = ?', $dateColumn);
    }

    if ($databaseConnection instanceof \Illuminate\Database\SQLiteConnection) {
        return "CAST(strftime('%w', {$dateColumn}) AS INTEGER) = ?";
    }
}

/**
 * Removes instances of multiple forward slashes, so // becomes /, /// becomes /
 *
 * @param string $string
 * @return string
 */
function deslash(string $string)
{
    return preg_replace('#/+#', '/', $string);
}

/**
 * @param string $url
 * @return string
 */
function deslashUrl(string $url)
{
    $parsedUrl = parse_url($url);

    $urlNoHost = $parsedUrl['host'] .
        (isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '') .
        (isset($parsedUrl['path']) ? $parsedUrl['path'] : '') .
        (isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '');

    return $parsedUrl['scheme'] . '://' . deslash($urlNoHost);
}

function frontendUrl($url) {
    return deslashUrl(
        config('app.frontend_url') . '/' . $url
    );
}

function withLock(string $lockKey, int $expires, $callback) {
    $lock = Cache::lock($lockKey, $expires);

    if (!$lock->get()) {
        throw new \Exception('Failed to acquire lock ' . $lockKey);
    }

    try {
        $result = $callback();
    } finally {
        $lock->release();
    }

    return $result;
}

