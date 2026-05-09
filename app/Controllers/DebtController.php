<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Tenant;
use App\Models\Customer;
use App\Models\Debt;
use App\Models\Installment;
use App\Models\Payment;

class DebtController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $status = (string)$this->input('status', '');
        $debts = Debt::listAll($status);
        $this->view('debts/index', [
            'title' => 'الديون',
            'debts' => $debts,
            'status' => $status,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $customers = Customer::all('name ASC');
        $preselected = (int)$this->input('customer_id', 0);
        $this->view('debts/form', [
            'title' => 'إضافة دين',
            'customers' => $customers,
            'preselected' => $preselected,
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        $customer_id   = (int)$this->input('customer_id', 0);
        $amount        = (float)$this->input('amount', 0);
        $debt_date     = $this->input('debt_date', date('Y-m-d'));
        $due_date      = $this->input('due_date', null) ?: null;
        $payment_type  = $this->input('payment_type', 'full');
        $freq          = $this->input('installment_freq', null);
        $count         = (int)$this->input('installment_count', 0);
        $description   = $this->input('description', '');

        if ($customer_id <= 0 || $amount <= 0) {
            $this->flash('danger', 'الرجاء اختيار الزبون وإدخال مبلغ صحيح');
            $this->redirect('/debts/create');
        }
        if ($payment_type === 'installment' && (!$freq || $count < 1)) {
            $this->flash('danger', 'الأقساط تتطلب اختيار تكرار وعدد الأقساط');
            $this->redirect('/debts/create');
        }

        Database::pdo()->beginTransaction();
        try {
            $debtId = Debt::create([
                'customer_id'       => $customer_id,
                'amount'            => $amount,
                'debt_date'         => $debt_date,
                'due_date'          => $due_date,
                'payment_type'      => $payment_type,
                'installment_freq'  => $payment_type === 'installment' ? $freq : null,
                'installment_count' => $payment_type === 'installment' ? $count : 0,
                'description'       => $description,
                'created_by'        => current_user()['id'] ?? null,
            ]);
            if ($payment_type === 'installment') {
                $startDate = $due_date ?: date('Y-m-d', strtotime($freq === 'weekly' ? '+1 week' : '+1 month'));
                Installment::generate($debtId, $amount, $startDate, $freq, $count);
            }
            Database::pdo()->commit();
            $this->flash('success', 'تمت إضافة الدين بنجاح');
            $this->redirect('/debts/' . $debtId);
        } catch (\Throwable $e) {
            Database::pdo()->rollBack();
            $this->flash('danger', 'فشل الإضافة: ' . $e->getMessage());
            $this->redirect('/debts/create');
        }
    }

    public function show(array $params): void
    {
        $this->requireAuth();
        $id = (int)$params['id'];
        $debt = Debt::withCustomer($id);
        if (!$debt) { $this->redirect('/debts'); }
        $paid = Debt::paidAmount($id);
        $remaining = max(0.0, (float)$debt['amount'] - $paid);
        $installments = Installment::forDebt($id);
        $payments = Payment::forDebt($id);

        // تحديث حالة الأقساط (overdue)
        foreach ($installments as $i) Installment::refreshStatus((int)$i['id']);
        $installments = Installment::forDebt($id); // إعادة الجلب بعد التحديث

        $this->view('debts/show', [
            'title' => 'تفاصيل دين #' . $id,
            'debt' => $debt,
            'paid' => $paid,
            'remaining' => $remaining,
            'installments' => $installments,
            'payments' => $payments,
        ]);
    }

    public function destroy(array $params): void
    {
        $this->requireAuth('admin');
        $this->validateCsrf();
        $id = (int)$params['id'];
        $debt = Debt::find($id);
        Debt::delete($id);
        $this->flash('success', 'تم حذف الدين');
        $this->redirect('/customers/' . ($debt['customer_id'] ?? ''));
    }

    /** عقد دين قابل للطباعة (HTML -> PDF عبر متصفح) */
    public function contract(array $params): void
    {
        $this->requireAuth();
        $debt = Debt::withCustomer((int)$params['id']);
        if (!$debt) { $this->redirect('/debts'); }
        $installments = Installment::forDebt((int)$debt['id']);
        $shop = $this->shopInfo();
        $this->view('debts/contract', [
            'debt' => $debt,
            'installments' => $installments,
            'shop' => $shop,
        ], 'print');
    }

    private function shopInfo(): array
    {
        $rows = Database::fetchAll("SELECT key,value FROM settings WHERE tenant_id = ?", [Tenant::id()]);
        $out = [];
        foreach ($rows as $r) $out[$r['key']] = $r['value'];
        return $out;
    }
}
