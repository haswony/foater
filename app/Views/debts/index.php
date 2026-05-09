<div class="page-header">
  <h1><i class="bi bi-wallet2"></i> الديون</h1>
  <a href="<?= url('/debts/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> إضافة دين</a>
</div>

<div class="card mb-3">
  <div class="card-body">
    <form method="get" class="d-flex gap-2 flex-wrap">
      <select name="status" class="form-select" style="max-width:200px" onchange="this.form.submit()">
        <option value="">كل الحالات</option>
        <option value="active"    <?= $status==='active'?'selected':'' ?>>نشط</option>
        <option value="paid"      <?= $status==='paid'?'selected':'' ?>>مدفوع</option>
        <option value="cancelled" <?= $status==='cancelled'?'selected':'' ?>>ملغي</option>
      </select>
    </form>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0 align-middle">
      <thead><tr>
        <th>#</th><th>الزبون</th><th>المبلغ</th><th>المدفوع</th><th>المتبقي</th>
        <th>تاريخ الدين</th><th>موعد السداد</th><th>النوع</th><th>الحالة</th><th></th>
      </tr></thead>
      <tbody>
      <?php foreach ($debts as $d):
        $remain = max(0, $d['amount'] - $d['paid_amount']);
        $isOverdue = $d['status'] === 'active' && $d['due_date'] && $d['due_date'] < date('Y-m-d');
      ?>
        <tr class="<?= $isOverdue ? 'row-late' : '' ?>">
          <td>#<?= e($d['id']) ?></td>
          <td><a href="<?= url('/customers/' . $d['customer_id']) ?>"><?= e($d['customer_name']) ?></a></td>
          <td><?= money($d['amount']) ?></td>
          <td class="text-success"><?= money($d['paid_amount']) ?></td>
          <td class="<?= $remain>0?'text-danger fw-bold':'text-muted' ?>"><?= money($remain) ?></td>
          <td><?= e($d['debt_date']) ?></td>
          <td><?= e($d['due_date'] ?: '-') ?></td>
          <td><?= $d['payment_type']==='installment' ? 'أقساط' : 'كامل' ?></td>
          <td><span class="badge-status <?= e($d['status']) ?>"><?= e($d['status']==='paid'?'مدفوع':($d['status']==='active'?'نشط':'ملغي')) ?></span></td>
          <td><a href="<?= url('/debts/' . $d['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($debts)): ?>
        <tr><td colspan="10" class="text-center text-muted py-4">لا توجد ديون</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
