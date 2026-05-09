<?php
namespace App\Core;

class Auth
{
    public static function attempt(string $username, string $password): bool
    {
        $user = Database::fetch('SELECT * FROM users WHERE username = ? AND active = 1', [$username]);
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }
        // تحقق من تفعيل المتجر (إن لم يكن المستخدم super_admin)
        if (!empty($user['tenant_id'])) {
            $tenant = Database::fetch('SELECT * FROM tenants WHERE id = ?', [$user['tenant_id']]);
            if (!$tenant || (int)$tenant['active'] !== 1) {
                return false;
            }
            $user['tenant_name'] = $tenant['name'];
        } else {
            $user['tenant_name'] = null;
        }
        unset($user['password']);
        $_SESSION['user'] = $user;
        session_regenerate_id(true);
        Database::execute('UPDATE users SET last_login = ? WHERE id = ?', [date('Y-m-d H:i:s'), $user['id']]);
        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function check(): bool
    {
        return !empty($_SESSION['user']);
    }
}
