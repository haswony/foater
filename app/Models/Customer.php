<?php
namespace App\Models;

use App\Core\Database;
use App\Core\Tenant;

class Customer extends Model
{
    protected static string $table = 'customers';

    public static function search(string $q, int $limit = 100): array
    {
        $like = '%' . $q . '%';
        return Database::fetchAll(
            'SELECT * FROM customers
             WHERE tenant_id = ? AND (name LIKE ? OR phone LIKE ? OR address LIKE ?)
             ORDER BY id DESC LIMIT ?',
            [Tenant::id(), $like, $like, $like, $limit]
        );
    }

    /** قائمة الزبائن مع إجمالي الديون والمدفوع والمتبقي */
    public static function listWithStats(string $q = ''): array
    {
        $where = 'c.tenant_id = ?';
        $params = [Tenant::id()];
        if ($q !== '') {
            $where .= ' AND (c.name LIKE ? OR c.phone LIKE ?)';
            $like = "%$q%";
            $params[] = $like; $params[] = $like;
        }
        $sql = "
            SELECT c.*,
                   COALESCE(SUM(d.amount),0)                                              AS total_debt,
                   COALESCE((SELECT SUM(p.amount) FROM payments p
                             JOIN debts d2 ON d2.id = p.debt_id WHERE d2.customer_id = c.id),0) AS total_paid,
                   (SELECT MAX(p.paid_at) FROM payments p
                     JOIN debts d3 ON d3.id = p.debt_id WHERE d3.customer_id = c.id)      AS last_payment
            FROM customers c
            LEFT JOIN debts d ON d.customer_id = c.id AND d.status != 'cancelled'
            WHERE $where
            GROUP BY c.id
            ORDER BY c.id DESC
        ";
        return Database::fetchAll($sql, $params);
    }

    public static function stats(int $customerId): array
    {
        $tid = Tenant::id();
        $debt = Database::fetch(
            'SELECT COALESCE(SUM(amount),0) AS total FROM debts WHERE customer_id = ? AND tenant_id = ? AND status != "cancelled"',
            [$customerId, $tid]
        );
        $paid = Database::fetch(
            'SELECT COALESCE(SUM(p.amount),0) AS total FROM payments p
             JOIN debts d ON d.id = p.debt_id WHERE d.customer_id = ? AND d.tenant_id = ?',
            [$customerId, $tid]
        );
        $last = Database::fetch(
            'SELECT MAX(p.paid_at) AS last_payment FROM payments p
             JOIN debts d ON d.id = p.debt_id WHERE d.customer_id = ? AND d.tenant_id = ?',
            [$customerId, $tid]
        );
        $total = (float)$debt['total'];
        $tpaid = (float)$paid['total'];
        return [
            'total_debt' => $total,
            'total_paid' => $tpaid,
            'remaining'  => max(0, $total - $tpaid),
            'last_payment' => $last['last_payment'] ?? null,
        ];
    }
}
