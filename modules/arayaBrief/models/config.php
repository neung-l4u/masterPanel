<?php
/**
 * Config loading for the Araya daily brief.
 *
 * Throws rather than defaulting, following modules/upgrade-downgrade: a brief
 * that ran without a Monday token would look identical to "Monday returned no
 * shops" and would report the entire customer base as missing.
 */

/**
 * UTF-8-safe trim of ordinary whitespace and non-breaking spaces.
 *
 * Deliberately not trim(): that is byte-based and corrupts UTF-8 (same idiom as
 * modules/upgrade-downgrade/sync/mirrorMatch.php). Returns null on invalid UTF-8.
 */
function arayaTrimUtf8(string $value): ?string
{
    return preg_replace('/^[\s\x{00A0}]+|[\s\x{00A0}]+$/u', '', $value);
}

/**
 * Check a loaded config has every key as a non-empty string, and return it
 * with gcal_base_url normalised (no trailing slash).
 *
 * A whitespace-only token would reach the Monday API, fail to authenticate and
 * look identical to "Monday returned no shops", so empty() is not enough.
 *
 * Never includes a value in the message: these are credentials, and this
 * message reaches logs and stack traces.
 */
function arayaValidateConfig($config, string $file): array
{
    if (!is_array($config)) {
        throw new RuntimeException("Config at {$file} did not return an array.");
    }

    $required = ['gcal_base_url', 'gcal_admin_key', 'monday_token', 'chat_webhook_url'];
    $missing = [];
    $invalid = [];
    foreach ($required as $key) {
        if (!array_key_exists($key, $config) || $config[$key] === null) {
            $missing[] = $key;
            continue;
        }
        $trimmed = is_string($config[$key]) ? arayaTrimUtf8($config[$key]) : null;
        if ($trimmed === null || $trimmed === '') {
            $invalid[] = $key;
        }
    }

    // A later task appends a path to the base URL; a trailing slash would double up.
    if (!in_array('gcal_base_url', $missing, true) && !in_array('gcal_base_url', $invalid, true)) {
        $config['gcal_base_url'] = rtrim($config['gcal_base_url'], '/');
        if ($config['gcal_base_url'] === '') {
            $invalid[] = 'gcal_base_url';
        }
    }

    if ($missing || $invalid) {
        $parts = [];
        if ($missing) {
            $parts[] = 'missing: ' . implode(', ', $missing);
        }
        if ($invalid) {
            $parts[] = 'not a non-empty string: ' . implode(', ', $invalid);
        }
        throw new RuntimeException(
            "Config at {$file} is invalid (" . implode('; ', $parts) . ")"
            . ". Copy assets/config.example.php to assets/config.php and fill it in."
        );
    }

    return $config;
}

/** Load and validate modules/arayaBrief/assets/config.php. */
function arayaLoadConfig(): array
{
    $file = __DIR__ . '/../assets/config.php';

    if (!is_file($file)) {
        throw new RuntimeException(
            "Missing config: {$file}. Copy assets/config.example.php to assets/config.php and fill it in."
        );
    }

    return arayaValidateConfig(include $file, $file);
}
