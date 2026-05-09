<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Tenant;
use App\Models\Installment;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        // المدير العام له لوحة منفصلة
        if (Tenant::isSuper()) {
            $this->redirect('/admin/tenants');
        }

        $tid = Tenant::require();
        $today = date('Y-m-d');
        $monthStart = date('Y-m-01');

        $totals = Database::fetch(
            "SELECT
                COALESCE(SUM(d.amount),0) AS total_debt,
                COALESCE((SELECT SUM(amount) FROM payments WHERE tenant_id = ?),0) AS total_paid
             FROM debts d WHERE d.tenant_id = ? AND d.status != 'cancelled'",
            [$tid, $tid]
        );
        $remaining = max(0, (float)$totals['total_debt'] - (float)$totals['total_paid']);

        $monthly = Database::fetch(
            "SELECT
                COALESCE((SELECT SUM(amount) FROM payments WHERE tenant_id = ? AND paid_at >= ?),0) AS month_paid,
                (SELECT COUNT(*) FROM debts WHERE tenant_id = ? AND created_at >= ?) AS month_debts",
            [$tid, $monthStart, $tid, $monthStart]
        );

        $customersCount = (int) Database::fetch("SELECT COUNT(*) c FROM customers WHERE tenant_id = ?", [$tid])['c'];
        $debtsCount     = (int) Database::fetch("SELECT COUNT(*) c FROM debts WHERE tenant_id = ? AND status != 'cancelled'", [$tid])['c'];

        // عدد المتأخرين
        $overdueCount = (int) Database::fetch(
            "SELECT COUNT(DISTINCT d.customer_id) c FROM debts d
             LEFT JOIN installments i ON i.debt_id = d.id
             WHERE d.tenant_id = ? AND d.status = 'active' AND (
                 (d.payment_type='full' AND d.due_date IS NOT NULL AND d.due_date < ?)
                 OR (i.status = 'overdue')
             )", [$tid, $today]
        )['c'];

        // الزبائن المتأخرون (تلوينهم بالأحمر)
        $overdueCustomers = Database::fetchAll(
            "SELECT DISTINCT c.id, c.name, c.phone,
                    (SELECT MIN(i.due_date) FROM installments i JOIN debts d2 ON d2.id=i.debt_id
                       WHERE d2.customer_id=c.id AND i.status='overdue') AS earliest_overdue
             FROM customers c
             JOIN debts d ON d.customer_id = c.id
             LEFT JOIN installments i ON i.debt_id = d.id
             WHERE c.tenant_id = ? AND d.status='active' AND (
                 (d.payment_type='full' AND d.due_date IS NOT NULL AND d.due_date < ?)
                 OR (i.status='overdue')
             )
             LIMIT 20", [$tid, $today]
        );

        // التذكيرات: أقساط قادمة خلال X يوم
        $remindRow = Database::fetch("SELECT value FROM settings WHERE tenant_id = ? AND key='reminder_days_before'", [$tid]);
        $remindDays = (int)($remindRow['value'] ?? 3);
        $reminders = Installment::upcomingForReminder($remindDays);

        // أحدث الدفعات
        $recentPayments = Database::fetchAll(
            "SELECT p.*, c.name AS customer_name FROM payments p
             JOIN debts d ON d.id = p.debt_id
             JOIN customers c ON c.id = d.customer_id
             WHERE p.tenant_id = ?
             ORDER BY p.paid_at DESC LIMIT 8", [$tid]
        );

        // بيانات للرسم البياني (آخر 6 أشهر)
        $chart = Database::fetchAll(
            "SELECT strftime('%Y-%m', paid_at) AS m, SUM(amount) AS s
             FROM payments WHERE tenant_id = ? AND paid_at >= date('now','-6 months')
             GROUP BY m ORDER BY m", [$tid]
        );

        $this->view('dashboard/index', [
            'title'           => 'لوحة التحكم',
            'totals'          => $totals,
            'remaining'       => $remaining,
            'monthly'         => $monthly,
            'customersCount'  => $customersCount,
            'debtsCount'      => $debtsCount,
            'overdueCount'    => $overdueCount,
            'overdueCustomers'=> $overdueCustomers,
            'reminders'       => $reminders,
            'recentPayments'  => $recentPayments,
            'chart'           => $chart,
        ]);
    }
}
