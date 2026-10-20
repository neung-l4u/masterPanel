<?php
/**
 * File cache for the brief's two upstream calls.
 *
 * The dashboard gets refreshed repeatedly, Monday rate-limits, and the gcal
 * endpoint scans every shop's periods, so a short TTL keeps an idle refresh
 * from costing two API calls. A corrupt or unreadable cache always reads as a
 * miss: serving half-parsed JSON would be worse than making the call again.
 */

function arayaCacheDir(): string
{
    $dir = __DIR__ . '/../assets/cache';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    return $dir;
}

/** Path for a cache entry. The name is sanitised: it reaches the filesystem. */
function arayaCachePath(string $name): string
{
    $safe = preg_replace('/[^A-Za-z0-9_-]/', '_', $name);
    return arayaCacheDir() . '/' . $safe . '.json';
}

function arayaCacheGet(string $name, int $ttlSeconds): ?array
{
    if ($ttlSeconds <= 0) {
        return null;
    }
    $path = arayaCachePath($name);
    if (!is_file($path)) {
        return null;
    }
    if (time() - filemtime($path) > $ttlSeconds) {
        return null;
    }
    $raw = file_get_contents($path);
    if ($raw === false || $raw === '') {
        return null;
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}

function arayaCachePut(string $name, array $data): void
{
    $path = arayaCachePath($name);
    // Write then rename, so a concurrent read never sees a half-written file.
    $tmp = $path . '.' . getmypid() . '.tmp';
    file_put_contents($tmp, json_encode($data, JSON_UNESCAPED_UNICODE));
    rename($tmp, $path);
}
