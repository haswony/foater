<?php
namespace App\Models;

use App\Core\Database;
use App\Core\Tenant;

class Payment extends Model
{
    protected static string $table = 'payments';

    public static function forDebt(int $debtId): array
    {
        return Database::fetchAll(
            'SELECT p.*, u.full_name AS received_by_name
             FROM payments p LEFT JOIN users u ON u.id = p.received_by
             WHERE p.debt_id = ? AND p.tenant_id = ? ORDER BY p.paid_at DESC',
            [$debtId, Tenant::id()]
        );
    }

    public static function listAll(int $limit = 200): array
    {
        return Database::fetchAll(
            'SELECT p.*, d.customer_id, c.name AS customer_name, u.full_name AS received_by_name
             FROM payments p
             JOIN debts d ON d.id = p.debt_id
             JOIN customers c ON c.id = d.customer_id
             LEFT JOIN users u ON u.id = p.received_by
             WHERE p.tenant_id = ?
             ORDER BY p.paid_at DESC LIMIT ?',
            [Tenant::id(), $limit]
        );
    }

    public static function withDetails(int $id): ?array
    {
        return Database::fetch(
            'SELECT p.*, d.customer_id, d.amount AS debt_amount,
                    c.name AS customer_name, c.phone AS customer_phone, c.address AS customer_address,
                    u.full_name AS received_by_name
             FROM payments p
             JOIN debts d ON d.id = p.debt_id
             JOIN customers c ON c.id = d.customer_id
             LEFT JOIN users u ON u.id = p.received_by
             WHERE p.id = ? AND p.tenant_id = ?',
            [$id, Tenant::id()]
        );
    }
}
