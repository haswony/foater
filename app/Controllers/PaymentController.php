<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Debt;
use App\Models\Installment;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $payments = Payment::listAll(300);
        $this->view('payments/index', [
            'title' => 'سجل الدفعات',
            'payments' => $payments,
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        $debt_id        = (int)$this->input('debt_id', 0);
        $amount         = (float)$this->input('amount', 0);
        $installment_id = (int)$this->input('installment_id', 0) ?: null;
        $method         = $this->input('method', 'cash');
        $notes          = $this->input('notes', '');
        $paid_at        = $this->input('paid_at', date('Y-m-d H:i:s'));

        $debt = Debt::find($debt_id);
        if (!$debt || $amount <= 0) {
            $this->flash('danger', 'بيانات الدفعة غير صحيحة');
            $this->redirect('/debts/' . $debt_id);
        }

        $remaining = Debt::remaining($debt_id);
        if ($amount - $remaining > 0.001) {
            $this->flash('danger', 'مبلغ الدفعة أكبر من المتبقي (' . money($remaining) . ')');
            $this->redirect('/debts/' . $debt_id);
        }

        Database::pdo()->beginTransaction();
        try {
            $pid = Payment::create([
                'debt_id'        => $debt_id,
                'installment_id' => $installment_id,
                'amount'         => $amount,
                'paid_at'        => $paid_at,
                'method'         => $method,
                'notes'          => $notes,
                'received_by'    => current_user()['id'] ?? null,
            ]);

            // تحديث القسط إن وجد
            if ($installment_id) {
                Database::execute(
                    'UPDATE installments SET paid_amount = paid_amount + ? WHERE id = ?',
                    [$amount, $installment_id]
                );
                Installment::refreshStatus($installment_id);
            } else {
                // توزيع تلقائي على الأقساط المعلقة (الأقدم أولاً)
                $left = $amount;
                $pending = Database::fetchAll(
                    "SELECT * FROM installments WHERE debt_id = ? AND status IN ('pending','partial','overdue') ORDER BY due_date ASC",
                    [$debt_id]
                );
                foreach ($pending as $i) {
                    if ($left <= 0.001) break;
                    $need = (float)$i['amount'] - (float)$i['paid_amount'];
                    if ($need <= 0) continue;
                    $apply = min($need, $left);
                    Database::execute('UPDATE installments SET paid_amount = paid_amount + ? WHERE id = ?', [$apply, $i['id']]);
                    Installment::refreshStatus((int)$i['id']);
                    $left -= $apply;
                }
            }

            Debt::refreshStatus($debt_id);
            Database::pdo()->commit();
            $this->flash('success', 'تم تسجيل الدفعة بنجاح');
            $this->redirect('/payments/' . $pid . '/receipt');
        } catch (\Throwable $e) {
            Database::pdo()->rollBack();
            $this->flash('danger', 'فشل تسجيل الدفعة: ' . $e->getMessage());
            $this->redirect('/debts/' . $debt_id);
        }
    }

    public function destroy(array $params): void
    {
        $this->requireAuth('admin');
        $this->validateCsrf();
        $id = (int)$params['id'];
        $payment = Payment::find($id);
        if (!$payment) { $this->redirect('/payments'); }

        Database::pdo()->beginTransaction();
        try {
            // إعادة توزيع: نطرح المبلغ من القسط
            if ($payment['installment_id']) {
                Database::execute(
                    'UPDATE installments SET paid_amount = MAX(0, paid_amount - ?) WHERE id = ?',
                    [$payment['amount'], $payment['installment_id']]
                );
                Installment::refreshStatus((int)$payment['installment_id']);
            }
            Payment::delete($id);
            Debt::refreshStatus((int)$payment['debt_id']);
            Database::pdo()->commit();
            $this->flash('success', 'تم حذف الدفعة');
        } catch (\Throwable $e) {
            Database::pdo()->rollBack();
            $this->flash('danger', 'فشل الحذف');
        }
        $this->redirect('/debts/' . $payment['debt_id']);
    }

    public function receipt(array $params): void
    {
        $this->requireAuth();
        $payment = Payment::withDetails((int)$params['id']);
        if (!$payment) { $this->redirect('/payments'); }
        $shop = [];
        foreach (Database::fetchAll("SELECT key,value FROM settings WHERE tenant_id = ?", [\App\Core\Tenant::id()]) as $r) $shop[$r['key']] = $r['value'];
        $remaining = Debt::remaining((int)$payment['debt_id']);
        $this->view('payments/receipt', [
            'payment' => $payment,
            'shop' => $shop,
            'remaining' => $remaining,
        ], 'print');
    }
}
