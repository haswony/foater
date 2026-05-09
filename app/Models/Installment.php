<?php
namespace App\Models;

use App\Core\Database;
use App\Core\Tenant;

class Installment extends Model
{
    protected static string $table = 'installments';

    public static function forDebt(int $debtId): array
    {
        return Database::fetchAll(
            'SELECT * FROM installments WHERE debt_id = ? AND tenant_id = ? ORDER BY seq ASC',
            [$debtId, Tenant::id()]
        );
    }

    /** توليد جدول الأقساط بناءً على التكرار والعدد */
    public static function generate(int $debtId, float $totalAmount, string $startDate, string $freq, int $count): void
    {
        if ($count < 1) return;
        $tid = Tenant::id();
        $perInstallment = round($totalAmount / $count, 2);
        $remaining = $totalAmount;
        $date = strtotime($startDate);
        for ($i = 1; $i <= $count; $i++) {
            $amount = ($i === $count) ? $remaining : $perInstallment;
            $remaining -= $amount;
            $due = date('Y-m-d', $date);
            Database::execute(
                'INSERT INTO installments (tenant_id, debt_id, seq, amount, due_date) VALUES (?,?,?,?,?)',
                [$tid, $debtId, $i, $amount, $due]
            );
            $date = strtotime($freq === 'weekly' ? '+1 week' : '+1 month', $date);
        }
    }

    public static function refreshStatus(int $installmentId): void
    {
        $i = self::find($installmentId);
        if (!$i) return;
        $paid = (float)$i['paid_amount'];
        $amount = (float)$i['amount'];
        $today = date('Y-m-d');
        if ($paid >= $amount - 0.001) $status = 'paid';
        elseif ($paid > 0)            $status = 'partial';
        elseif ($i['due_date'] < $today) $status = 'overdue';
        else                          $status = 'pending';
        Database::execute(
            'UPDATE installments SET status = ? WHERE id = ? AND tenant_id = ?',
            [$status, $installmentId, Tenant::id()]
        );
    }

    public static function upcomingForReminder(int $daysBefore): array
    {
        $until = date('Y-m-d', strtotime("+$daysBefore days"));
        return Database::fetchAll(
            "SELECT i.*, d.customer_id, c.name AS customer_name, c.phone
             FROM installments i
             JOIN debts d ON d.id = i.debt_id
             JOIN customers c ON c.id = d.customer_id
             WHERE i.tenant_id = ?
               AND i.status IN ('pending','partial','overdue')
               AND i.due_date <= ?
             ORDER BY i.due_date ASC",
            [Tenant::id(), $until]
        );
    }
}
