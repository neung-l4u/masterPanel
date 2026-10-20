<?php
/**
 * Sanitizer — XSS Protection Utility
 * Include this file and use shortcut functions: esc(), escAttr(), escUrl(), escCookie(), clean()
 */

class Sanitizer
{
    /**
     * Escape for HTML content
     */
    public static function esc($value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }

    /**
     * Escape for HTML attribute (e.g. value="...", title="...")
     */
    public static function escAttr($value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Escape for URL (prevent javascript: protocol injection)
     */
    public static function escUrl($url): string
    {
        $url = trim($url ?? '');
        if (empty($url) || $url === '#') return $url;

        $scheme = parse_url($url, PHP_URL_SCHEME);
        $allowed = ['http', 'https', 'mailto', 'tel'];
        if ($scheme !== null && !in_array(strtolower($scheme), $allowed, true)) {
            return '#';
        }

        return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Ensure URL is absolute so the browser does not treat it as a relative path.
     * DB values are often stored without a scheme (e.g. "www.example.com/wp-admin"),
     * which the browser would otherwise resolve against the current page path.
     */
    public static function ensureAbsoluteUrl($url): string
    {
        $url = trim($url ?? '');
        if (empty($url)) return $url;
        if (preg_match('~^(https?://|mailto:|tel:|//)~i', $url)) return $url;
        return 'https://' . $url;
    }

    /**
     * Remove a trailing /wp-admin (or /wp-admin/) to get the public site URL
     */
    public static function stripWpAdmin($url): string
    {
        $url = trim($url ?? '');
        if (empty($url)) return $url;
        return preg_replace('~/wp-admin/?$~i', '', $url);
    }

    /**
     * Sanitize cookie value before output
     */
    public static function escCookie($name): string
    {
        $val = $_COOKIE[$name] ?? '';
        return self::escAttr($val);
    }

    /**
     * Strip HTML tags + escape (for input that should never contain HTML)
     */
    public static function clean($value): string
    {
        return htmlspecialchars(strip_tags($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

// Shortcut functions
if (!function_exists('esc')) {
    function esc($v)       { return Sanitizer::esc($v); }
}
if (!function_exists('escAttr')) {
    function escAttr($v)   { return Sanitizer::escAttr($v); }
}
if (!function_exists('escUrl')) {
    function escUrl($v)    { return Sanitizer::escUrl($v); }
}
if (!function_exists('ensureAbsoluteUrl')) {
    function ensureAbsoluteUrl($v) { return Sanitizer::ensureAbsoluteUrl($v); }
}
if (!function_exists('stripWpAdmin')) {
    function stripWpAdmin($v) { return Sanitizer::stripWpAdmin($v); }
}
if (!function_exists('escCookie')) {
    function escCookie($n) { return Sanitizer::escCookie($n); }
}
if (!function_exists('clean')) {
    function clean($v)     { return Sanitizer::clean($v); }
}
