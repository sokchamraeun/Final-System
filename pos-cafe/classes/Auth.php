<?php
declare(strict_types=1);

/* ============================================================
   Auth — read-only view of the current session user.

   Login/logout themselves still run through the root app
   (login.php / logout.php), which populates $_SESSION. This
   class just exposes that session cleanly to the new UI.
   ============================================================ */

final class Auth
{
    public static function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public static function id(): int
    {
        return (int) ($_SESSION['user_id'] ?? 0);
    }

    public static function name(): string
    {
        return (string) ($_SESSION['username'] ?? 'Guest');
    }

    public static function role(): string
    {
        return (string) ($_SESSION['role'] ?? 'staff');
    }

    /** Human role name + colour from the roles table (for the profile pill). */
    public static function roleMeta(): array
    {
        $slug = self::role();
        $row  = Database::instance()->first(
            "SELECT name, color FROM roles WHERE slug = ? LIMIT 1",
            [$slug]
        );
        return [
            'slug'  => $slug,
            'name'  => $row['name']  ?? ucwords(str_replace('_', ' ', $slug)),
            'color' => $row['color'] ?? '#F59E0B',
        ];
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    /** Employee photo URL (from employees table matched by user_id), or null. */
    public static function photo(): ?string
    {
        $uid = self::id();
        if (!$uid) return null;
        $row = Database::instance()->first(
            "SELECT photo FROM employees WHERE user_id = ? AND photo IS NOT NULL AND photo != '' LIMIT 1",
            [$uid]
        );
        return $row['photo'] ?? null;
    }

    /** Initials for the avatar bubble. */
    public static function initials(): string
    {
        $name  = trim(self::name());
        $parts = preg_split('/\s+/', $name) ?: [];
        $ini   = '';
        foreach (array_slice($parts, 0, 2) as $p) {
            $ini .= mb_strtoupper(mb_substr($p, 0, 1));
        }
        return $ini !== '' ? $ini : 'U';
    }
}
