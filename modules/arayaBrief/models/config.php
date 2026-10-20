<?php
/**
 * Config loading for the Araya daily brief.
 *
 * Throws rather than defaulting, following modules/upgrade-downgrade: a brief
 * that ran without a Monday token would look identical to "Monday returned no
 * shops" and would report the entire customer base as missing.
 */

/**
 * Check a loaded config has every key, with a message naming what is missing.
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
    foreach ($required as $key) {
        if (empty($config[$key])) {
            $missing[] = $key;
        }
    }

    if ($missing) {
        throw new RuntimeException(
            "Config at {$file} is missing: " . implode(', ', $missing)
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
