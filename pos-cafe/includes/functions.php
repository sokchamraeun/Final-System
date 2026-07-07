<?php
declare(strict_types=1);

/* ============================================================
   General-purpose functions shared across modules.
   Business/domain logic belongs in classes/ — keep this file for
   small, reusable, stateless utilities only.
   ============================================================ */

if (!function_exists('json_response')) {
    /** Emit a JSON payload and stop (used by api/ endpoints). */
    function json_response(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

if (!function_exists('input')) {
    /** Read a request value (POST first, then GET), trimmed. */
    function input(string $key, mixed $default = ''): mixed
    {
        $v = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($v) ? trim($v) : $v;
    }
}

if (!function_exists('csrf_token')) {
    /** Get (creating if needed) the session CSRF token. */
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /** Hidden input carrying the CSRF token. */
    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('csrf_verify')) {
    /** Constant-time CSRF check of a submitted token. */
    function csrf_verify(?string $token): bool
    {
        return is_string($token)
            && !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('str_slug')) {
    /** Lowercase, hyphenated slug. */
    function str_slug(string $text): string
    {
        $s = strtolower(trim($text));
        $s = preg_replace('/[^a-z0-9]+/', '-', $s) ?? '';
        return trim($s, '-');
    }
}

if (!function_exists('time_ago')) {
    /** Human "3m ago" style relative time. */
    function time_ago(string $datetime): string
    {
        $diff = time() - strtotime($datetime);
        if ($diff < 60)    return $diff . 's ago';
        if ($diff < 3600)  return floor($diff / 60) . 'm ago';
        if ($diff < 86400) return floor($diff / 3600) . 'h ago';
        return floor($diff / 86400) . 'd ago';
    }
}
