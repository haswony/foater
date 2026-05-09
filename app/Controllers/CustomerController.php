<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\CustomerAnalyzer;
use App\Models\Customer;
use App\Models\Debt;
use App\Models\Payment;

class CustomerController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $q = trim((string)$this->input('q', ''));
        $customers = Customer::listWithStats($q);
        $this->view('customers/index', [
            'title' => 'الزبائن',
            'customers' => $customers,
            'q' => $q,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->view('customers/form', [
            'title' => 'إضافة زبون',
            'customer' => null,
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->validateCsrf();
        $data = $this->collect();
        if (!$this->validate($data)) { $this->redirect('/customers/create'); }
        $data['created_by'] = current_user()['id'] ?? null;
        $id = Customer::create($data);
        $this->flash('success', 'تمت إضافة الزبون بنجاح');
        $this->redirect('/customers/' . $id);
    }

    public function show(array $params): void
    {
        $this->requireAuth();
        $id = (int)$params['id'];
        $customer = Customer::find($id);
        if (!$customer) { $this->redirect('/customers'); }
        $stats     = Customer::stats($id);
        $debts     = Debt::forCustomer($id);
        $analysis  = CustomerAnalyzer::analyze($id);

        $this->view('customers/show', [
            'title'    => $customer['name'],
            'customer' => $customer,
            'stats'    => $stats,
            'debts'    => $debts,
            'analysis' => $analysis,
        ]);
    }

    public function edit(array $params): void
    {
        $this->requireAuth();
        $customer = Customer::find((int)$params['id']);
        if (!$customer) { $this->redirect('/customers'); }
        $this->view('customers/form', [
            'title' => 'تعديل زبون',
            'customer' => $customer,
        ]);
    }

    public function update(array $params): void
    {
        $this->requireAuth();
        $this->validateCsrf();
        $id = (int)$params['id'];
        $data = $this->collect();
        if (!$this->validate($data)) { $this->redirect('/customers/' . $id . '/edit'); }
        Customer::update($id, $data);
        $this->flash('success', 'تم تحديث بيانات الزبون');
        $this->redirect('/customers/' . $id);
    }

    public function destroy(array $params): void
    {
        $this->requireAuth('admin');
        $this->validateCsrf();
        Customer::delete((int)$params['id']);
        $this->flash('success', 'تم حذف الزبون');
        $this->redirect('/customers');
    }

    public function apiSearch(): void
    {
        $this->requireAuth();
        $q = (string)$this->input('q', '');
        $rows = $q === '' ? Customer::all('id DESC') : Customer::search($q, 20);
        $this->json(['data' => array_map(function ($r) {
            return ['id'=>$r['id'],'name'=>$r['name'],'phone'=>$r['phone']];
        }, $rows)]);
    }

    private function collect(): array
    {
        return [
            'name'    => $this->input('name', ''),
            'phone'   => $this->input('phone', ''),
            'address' => $this->input('address', ''),
            'notes'   => $this->input('notes', ''),
        ];
    }

    private function validate(array $data): bool
    {
        $_SESSION['_old'] = $data;
        if (trim($data['name']) === '') {
            $this->flash('danger', 'اسم الزبون مطلوب');
            return false;
        }
        unset($_SESSION['_old']);
        return true;
    }
}
