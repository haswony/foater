<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Tenant;

class ReportController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $tid = Tenant::require();
        $month = $this->input('month', date('Y-m'));
        $start = $month . '-01';
        $end   = date('Y-m-t', strtotime($start));

        $monthlyPaid = (float) Database::fetch(
            "SELECT COALESCE(SUM(amount),0) s FROM payments WHERE tenant_id = ? AND paid_at BETWEEN ? AND ?",
            [$tid, $start . ' 00:00:00', $end . ' 23:59:59']
        )['s'];

        $monthlyDebts = (int) Database::fetch(
            "SELECT COUNT(*) c FROM debts WHERE tenant_id = ? AND created_at BETWEEN ? AND ?",
            [$tid, $start, $end . ' 23:59:59']
        )['c'];

        $monthlyDebtsAmount = (float) Database::fetch(
            "SELECT COALESCE(SUM(amount),0) s FROM debts WHERE tenant_id = ? AND created_at BETWEEN ? AND ?",
            [$tid, $start, $end . ' 23:59:59']
        )['s'];

        $today = date('Y-m-d');
        $overdueCount = (int) Database::fetch(
            "SELECT COUNT(DISTINCT d.customer_id) c FROM debts d
             LEFT JOIN installments i ON i.debt_id = d.id
             WHERE d.tenant_id = ? AND d.status = 'active' AND (
                 (d.payment_type='full' AND d.due_date IS NOT NULL AND d.due_date < ?)
                 OR (i.status = 'overdue')
             )", [$tid, $today]
        )['c'];

        // ملخص الزبائن الأكثر ديوناً
        $topDebtors = Database::fetchAll(
            "SELECT c.id, c.name, SUM(d.amount) AS total,
                    COALESCE((SELECT SUM(p.amount) FROM payments p
                             JOIN debts d2 ON d2.id=p.debt_id WHERE d2.customer_id=c.id),0) AS paid
             FROM customers c JOIN debts d ON d.customer_id=c.id
             WHERE c.tenant_id = ? AND d.status != 'cancelled'
             GROUP BY c.id ORDER BY (total - paid) DESC LIMIT 10",
            [$tid]
        );

        // إجمالي
        $allTime = Database::fetch(
            "SELECT
                COALESCE((SELECT SUM(amount) FROM debts WHERE tenant_id = ? AND status != 'cancelled'),0) AS total_debt,
                COALESCE((SELECT SUM(amount) FROM payments WHERE tenant_id = ?),0) AS total_paid",
            [$tid, $tid]
        );
        $totalRemaining = max(0.0, (float)$allTime['total_debt'] - (float)$allTime['total_paid']);

        // أرباح الشهر (مفترضين أن الأرباح هي مجموع الدفعات)
        $this->view('reports/index', [
            'title' => 'التقارير والإحصائيات',
            'month' => $month,
            'monthlyPaid' => $monthlyPaid,
            'monthlyDebts' => $monthlyDebts,
            'monthlyDebtsAmount' => $monthlyDebtsAmount,
            'overdueCount' => $overdueCount,
            'topDebtors' => $topDebtors,
            'allTime' => $allTime,
            'totalRemaining' => $totalRemaining,
        ]);
    }

    public function exportCsv(): void
    {
        $this->requireAuth();
        $tid = Tenant::require();
        $type = $this->input('type', 'customers');

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $type . '_' . date('Ymd_His') . '.csv"');
        $out = fopen('php://output', 'w');
        // BOM لدعم الإكسل بالعربية
        fwrite($out, "\xEF\xBB\xBF");

        if ($type === 'customers') {
            fputcsv($out, ['#', 'الاسم', 'الهاتف', 'العنوان', 'إجمالي الدين', 'المدفوع', 'المتبقي']);
            $rows = Database::fetchAll(
                "SELECT c.id,c.name,c.phone,c.address,
                        COALESCE(SUM(d.amount),0) AS total_debt,
                        COALESCE((SELECT SUM(p.amount) FROM payments p JOIN debts d2 ON d2.id=p.debt_id WHERE d2.customer_id=c.id),0) AS paid
                 FROM customers c LEFT JOIN debts d ON d.customer_id=c.id AND d.status!='cancelled'
                 WHERE c.tenant_id = ?
                 GROUP BY c.id ORDER BY c.id",
                [$tid]
            );
            foreach ($rows as $r) {
                fputcsv($out, [$r['id'], $r['name'], $r['phone'], $r['address'],
                    $r['total_debt'], $r['paid'], max(0, $r['total_debt'] - $r['paid'])]);
            }
        } elseif ($type === 'debts') {
            fputcsv($out, ['#', 'الزبون', 'المبلغ', 'تاريخ الدين', 'موعد السداد', 'النوع', 'الحالة']);
            $rows = Database::fetchAll(
                "SELECT d.*, c.name AS customer_name FROM debts d JOIN customers c ON c.id=d.customer_id WHERE d.tenant_id = ? ORDER BY d.id",
                [$tid]
            );
            foreach ($rows as $r) {
                fputcsv($out, [$r['id'], $r['customer_name'], $r['amount'], $r['debt_date'],
                    $r['due_date'], $r['payment_type'], $r['status']]);
            }
        } else {
            fputcsv($out, ['#', 'الزبون', 'الدين', 'المبلغ', 'تاريخ الدفع', 'الطريقة']);
            $rows = Database::fetchAll(
                "SELECT p.*, c.name AS customer_name FROM payments p
                 JOIN debts d ON d.id=p.debt_id
                 JOIN customers c ON c.id=d.customer_id
                 WHERE p.tenant_id = ? ORDER BY p.id",
                [$tid]
            );
            foreach ($rows as $r) {
                fputcsv($out, [$r['id'], $r['customer_name'], $r['debt_id'], $r['amount'], $r['paid_at'], $r['method']]);
            }
        }
        fclose($out);
        exit;
    }
}
