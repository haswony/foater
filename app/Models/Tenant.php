<?php
namespace App\Models;

use App\Core\Database;

class Tenant extends Model
{
    protected static string $table = 'tenants';
    protected static bool $tenantScoped = false; // الجدول نفسه ليس معزولاً

    /** قائمة المتاجر مع إحصائيات سريعة */
    public static function listWithStats(): array
    {
        return Database::fetchAll(
            "SELECT t.*,
                    (SELECT COUNT(*) FROM users     WHERE tenant_id = t.id) AS users_count,
                    (SELECT COUNT(*) FROM customers WHERE tenant_id = t.id) AS customers_count,
                    (SELECT COUNT(*) FROM debts     WHERE tenant_id = t.id) AS debts_count,
                    (SELECT COALESCE(SUM(amount),0) FROM debts    WHERE tenant_id = t.id) AS total_debts,
                    (SELECT COALESCE(SUM(amount),0) FROM payments WHERE tenant_id = t.id) AS total_paid
             FROM tenants t ORDER BY t.id DESC"
        );
    }
}
