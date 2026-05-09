<?php
namespace App\Models;

use App\Core\Database;
use App\Core\Tenant;

class User extends Model
{
    protected static string $table = 'users';

    /** قائمة المستخدمين الخاصة بالمتجر الحالي (تستثني super_admin) */
    public static function allForTenant(): array
    {
        if (Tenant::isSuper()) {
            return Database::fetchAll('SELECT * FROM users ORDER BY id DESC');
        }
        return Database::fetchAll(
            'SELECT * FROM users WHERE tenant_id = ? ORDER BY id DESC',
            [Tenant::id()]
        );
    }
}
