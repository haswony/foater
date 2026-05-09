<?php
namespace App\Core;

// Tenant و Database في نفس مساحة الأسماء (App\Core)
use App\Core\Tenant;
use App\Core\Database;

/**
 * تحليل سلوك دفع الزبون وتقييمه:
 *  - ملتزم  | متوسط | خطر
 *  - اقتراح: زبون جيد / لا تعطه دين جديد
 */
class CustomerAnalyzer
{
    public static function analyze(int $customerId): array
    {
        $tid = Tenant::id();
        // جميع الأقساط المنتهية لهذا الزبون
        $installments = Database::fetchAll(
            "SELECT i.* FROM installments i
             JOIN debts d ON d.id = i.debt_id
             WHERE d.customer_id = ? AND d.tenant_id = ?",
            [$customerId, $tid]
        );

        $total = count($installments);
        $latePayments = 0;
        $unpaidOverdue = 0;
        $today = date('Y-m-d');

        foreach ($installments as $i) {
            if ($i['status'] === 'paid') {
                // تم سداده، نتحقق هل تم دفعه قبل أو بعد الاستحقاق
                $lastPay = Database::fetch(
                    'SELECT MAX(paid_at) AS p FROM payments WHERE installment_id = ?',
                    [$i['id']]
                );
                if ($lastPay && $lastPay['p'] && substr($lastPay['p'], 0, 10) > $i['due_date']) {
                    $latePayments++;
                }
            } elseif ($i['due_date'] < $today) {
                $unpaidOverdue++;
            }
        }

        // فحص الديون من نوع full
        $overdueFullDebts = Database::fetch(
            "SELECT COUNT(*) AS c FROM debts d
             WHERE d.customer_id = ? AND d.tenant_id = ? AND d.payment_type = 'full'
               AND d.status = 'active' AND d.due_date IS NOT NULL AND d.due_date < ?",
            [$customerId, $tid, $today]
        );
        $unpaidOverdue += (int)($overdueFullDebts['c'] ?? 0);

        // عدد الديون الكلي
        $debtsCount = (int) (Database::fetch(
            "SELECT COUNT(*) AS c FROM debts WHERE customer_id = ? AND tenant_id = ?",
            [$customerId, $tid]
        )['c'] ?? 0);

        // التقييم
        $rating = 'ملتزم';
        $suggestion = 'زبون جيد - يمكن منحه ديوناً جديدة';
        $color = 'success';

        $latenessRate = $total > 0 ? ($latePayments / $total) : 0;

        if ($unpaidOverdue >= 2 || $latenessRate >= 0.5) {
            $rating = 'خطر';
            $suggestion = '⚠️ لا يُنصح بمنحه ديوناً جديدة';
            $color = 'danger';
        } elseif ($unpaidOverdue === 1 || $latenessRate >= 0.2 || $latePayments >= 2) {
            $rating = 'متوسط';
            $suggestion = 'يُنصح بالحذر عند منحه ديوناً جديدة';
            $color = 'warning';
        }

        if ($debtsCount === 0) {
            $rating = 'جديد';
            $suggestion = 'زبون جديد - لا يوجد سجل سابق';
            $color = 'secondary';
        }

        return [
            'rating'            => $rating,
            'color'             => $color,
            'suggestion'        => $suggestion,
            'late_payments'     => $latePayments,
            'unpaid_overdue'    => $unpaidOverdue,
            'total_installments'=> $total,
            'debts_count'       => $debtsCount,
            'lateness_rate'     => round($latenessRate * 100, 1),
        ];
    }
}
