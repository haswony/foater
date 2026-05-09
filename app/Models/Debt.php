<?php
namespace App\Models;

use App\Core\Database;
use App\Core\Tenant;

class Debt extends Model
{
    protected static string $table = 'debts';

    public static function withCustomer(int $id): ?array
    {
        return Database::fetch(
            'SELECT d.*, c.name AS customer_name, c.phone AS customer_phone, c.address AS customer_address
             FROM debts d JOIN customers c ON c.id = d.customer_id
             WHERE d.id = ? AND d.tenant_id = ?',
            [$id, Tenant::id()]
        );
    }

    public static function listAll(string $status = ''): array
    {
        $where = 'd.tenant_id = ?';
        $params = [Tenant::id()];
        if ($status !== '') {
            $where .= ' AND d.status = ?';
            $params[] = $status;
        }
        return Database::fetchAll(
            "SELECT d.*, c.name AS customer_name, c.phone AS customer_phone,
                    COALESCE((SELECT SUM(p.amount) FROM payments p WHERE p.debt_id = d.id),0) AS paid_amount
             FROM debts d JOIN customers c ON c.id = d.customer_id
             WHERE $where
             ORDER BY d.id DESC",
            $params
        );
    }

    public static function forCustomer(int $customerId): array
    {
        return Database::fetchAll(
            'SELECT d.*,
                    COALESCE((SELECT SUM(p.amount) FROM payments p WHERE p.debt_id = d.id),0) AS paid_amount
             FROM debts d WHERE d.customer_id = ? AND d.tenant_id = ? ORDER BY d.id DESC',
            [$customerId, Tenant::id()]
        );
    }

    public static function paidAmount(int $debtId): float
    {
        $r = Database::fetch(
            'SELECT COALESCE(SUM(amount),0) AS s FROM payments WHERE debt_id = ? AND tenant_id = ?',
            [$debtId, Tenant::id()]
        );
        return (float)$r['s'];
    }

    public static function remaining(int $debtId): float
    {
        $d = self::find($debtId);
        if (!$d) return 0.0;
        return max(0.0, (float)$d['amount'] - self::paidAmount($debtId));
    }

    /** تحديث حالة الدين تلقائياً عند اكتمال السداد */
    public static function refreshStatus(int $debtId): void
    {
        $remaining = self::remaining($debtId);
        $status = $remaining <= 0.001 ? 'paid' : 'active';
        Database::execute(
            'UPDATE debts SET status = ? WHERE id = ? AND tenant_id = ?',
            [$status, $debtId, Tenant::id()]
        );
    }
}
