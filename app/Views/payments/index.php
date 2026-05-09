<div class="page-header">
  <h1><i class="bi bi-cash-stack"></i> سجل الدفعات</h1>
  <a href="<?= url('/reports/export.csv?type=payments') ?>" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel"></i> تصدير Excel</a>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0 align-middle">
      <thead><tr>
        <th>#</th><th>الزبون</th><th>المبلغ</th><th>تاريخ الدفع</th>
        <th>الطريقة</th><th>المستلم</th><th>دين</th><th></th>
      </tr></thead>
      <tbody>
      <?php foreach ($payments as $p): ?>
        <tr>
          <td>#<?= e($p['id']) ?></td>
          <td><a href="<?= url('/customers/'.$p['customer_id']) ?>"><?= e($p['customer_name']) ?></a></td>
          <td class="text-success fw-bold"><?= money($p['amount']) ?></td>
          <td><?= e(fmt_datetime($p['paid_at'])) ?></td>
          <td><?= e(['cash'=>'نقدي','transfer'=>'تحويل','card'=>'بطاقة'][$p['method']] ?? $p['method']) ?></td>
          <td><?= e($p['received_by_name'] ?? '-') ?></td>
          <td><a href="<?= url('/debts/'.$p['debt_id']) ?>">#<?= e($p['debt_id']) ?></a></td>
          <td><a href="<?= url('/payments/'.$p['id'].'/receipt') ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-printer"></i></a></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($payments)): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">لا توجد دفعات مسجلة</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
