<?php
declare(strict_types=1);

/* ============================================================
   View & URL helpers — small pure functions used by every page
   and component. Kept dependency-free so components stay simple.
   ============================================================ */

if (!function_exists('e')) {
    /** HTML-escape for safe output. */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('url')) {
    /**
     * Absolute URL within the pos-cafe app.
     *
     * Supports both old-style paths (e.g. 'pages/dashboard/index.php')
     * and clean paths (e.g. 'dashboard', 'products/edit/5').
     *
     * Old-style paths are auto-converted to clean URLs so the
     * transition is backward-compatible.
     */
    function url(string $path = ''): string
    {
        $path = ltrim($path, '/');

        // Auto-convert old-style pages/XXX/index.php → XXX
        $path = preg_replace('#^pages/(\w+)/index\.php(\?.*)?$#', '$1$2', $path);
        // Auto-convert old-style pages/XXX/YYY.php → XXX/YYY  (e.g. products/edit)
        $path = preg_replace('#^pages/(\w+)/(\w+)\.php(\?.*)?$#', '$1/$2$3', $path);

        return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('root_url')) {
    /** Absolute URL within the legacy root app (for not-yet-migrated pages). */
    function root_url(string $path = ''): string
    {
        return rtrim(ROOT_URL, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /** URL to a file under pos-cafe/assets/. */
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('component')) {
    /**
     * Render a component partial from components/, passing $data as
     * local variables. Example: component('products/product-card', ['p' => $row]).
     */
    function component(string $name, array $data = []): void
    {
        $__file = POS_ROOT . '/components/' . trim($name, '/') . '.php';
        if (!is_file($__file)) {
            echo "<!-- missing component: {$name} -->";
            return;
        }
        extract($data, EXTR_SKIP);
        require $__file;
    }
}

if (!function_exists('money')) {
    /** Format a USD amount. */
    function money(float|int|string $amount): string
    {
        return '$' . number_format((float) $amount, 2);
    }
}

if (!function_exists('old')) {
    /** Repopulate a form field after a validation error. */
    function old(string $key, mixed $default = ''): string
    {
        return e($_SESSION['_old'][$key] ?? $default);
    }
}

if (!function_exists('flash')) {
    /**
     * Set (with $message) or read-and-clear (without) a one-shot flash message.
     * Types: success | error | info | warning.
     */
    function flash(?string $message = null, string $type = 'success'): ?array
    {
        if ($message !== null) {
            $_SESSION['_flash'] = ['message' => $message, 'type' => $type];
            return null;
        }
        $f = $_SESSION['_flash'] ?? null;
        unset($_SESSION['_flash']);
        return $f;
    }
}

if (!function_exists('redirect')) {
    /** Redirect and stop. */
    function redirect(string $to): never
    {
        header('Location: ' . $to);
        exit;
    }
}

if (!function_exists('is_spa_request')) {
    /** True when the current request came through the SPA router
     *  or via the legacy ?embed=1 page-loader protocol. */
    function is_spa_request(): bool
    {
        return ($_SERVER['HTTP_X_SPA'] ?? '') === '1'
            || isset($_GET['embed']);
    }
}

if (!function_exists('is_active_nav')) {
    /** True when the current script matches one of the given basenames. */
    function is_active_nav(string ...$names): bool
    {
        $current = basename($_SERVER['SCRIPT_NAME'] ?? '');
        $dir     = basename(dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        foreach ($names as $n) {
            if ($n === $current || $n === $dir) {
                return true;
            }
        }
        return false;
    }
}
