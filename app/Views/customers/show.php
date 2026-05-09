<div class="page-header">
  <h1><i class="bi bi-person-circle"></i> <?= e($customer['name']) ?></h1>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= url('/debts/create?customer_id=' . $customer['id']) ?>" class="btn btn-success"><i class="bi bi-plus-lg"></i> إضافة دين</a>
    <a href="<?= url('/customers/' . $customer['id'] . '/edit') ?>" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i> تعديل</a>
    <?php if (is_admin()): ?>
    <form method="post" action="<?= url('/customers/' . $customer['id'] . '/delete') ?>" data-confirm="هل أنت متأكد من حذف الزبون وكل ديونه؟" class="d-inline">
      <?= csrf_field() ?>
      <button class="btn btn-outline-danger"><i class="bi bi-trash"></i> حذف</button>
    </form>
    <?php endif; ?>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="rating-card <?= e($analysis['color']) ?>">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <div style="opacity:.85">تقييم الزبون</div>
          <div style="font-size:1.6rem;font-weight:bold"><?= e($analysis['rating']) ?></div>
        </div>
        <i class="bi bi-shield-check" style="font-size:2.5rem;opacity:.6"></i>
      </div>
      <hr style="border-color:rgba(255,255,255,.3)">
      <div class="small"><?= e($analysis['suggestion']) ?></div>
    </div>

    <div class="card mb-3">
      <div class="card-body">
        <h6 class="text-muted mb-3"><i class="bi bi-info-circle"></i> بيانات الاتصال</h6>
        <div class="mb-2"><i class="bi bi-telephone"></i> <?= e($customer['phone'] ?: '-') ?></div>
        <div class="mb-2"><i class="bi bi-geo-alt"></i> <?= e($customer['address'] ?: '-') ?></div>
        <?php if (!empty($customer['notes'])): ?>
          <div class="mt-2 p-2 bg-light rounded small"><?= e($customer['notes']) ?></div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <h6 class="text-muted mb-3"><i class="bi bi-bar-chart"></i> تحليل سلوك الدفع</h6>
        <div class="d-flex justify-content-between"><span>عدد الديون</span><strong><?= e($analysis['debts_count']) ?></strong></div>
        <div class="d-flex justify-content-between"><span>أقساط مدفوعة متأخرة</span><strong class="text-warning"><?= e($analysis['late_payments']) ?></strong></div>
        <div class="d-flex justify-content-between"><span>أقساط متأخرة لم تُدفع</span><strong class="text-danger"><?= e($analysis['unpaid_overdue']) ?></strong></div>
        <div class="d-flex justify-content-between"><span>نسبة التأخر</span><strong><?= e($analysis['lateness_rate']) ?>%</strong></div>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="row g-3 mb-3">
      <div class="col-4"><div class="stat-card text-center">
        <div class="stat-label">إجمالي الدين</div>
        <div class="stat-value text-primary"><?= money($stats['total_debt']) ?></div>
      </div></div>
      <div class="col-4"><div class="stat-card text-center">
        <div class="stat-label">المدفوع</div>
        <div class="stat-value text-success"><?= money($stats['total_paid']) ?></div>
      </div></div>
      <div class="col-4"><div class="stat-card text-center">
        <div class="stat-label">المتبقي</div>
        <div class="stat-value text-danger"><?= money($stats['remaining']) ?></div>
      </div></div>
    </div>

    <div class="card">
      <div class="card-header bg-white"><strong><i class="bi bi-wallet2"></i> ديون الزبون</strong></div>
      <div class="table-responsive">
        <table class="table mb-0">
          <thead><tr><th>#</th><th>المبلغ</th><th>التاريخ</th><th>السداد</th><th>النوع</th><th>الحالة</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($debts as $d):
              $remain = $d['amount'] - $d['paid_amount']; ?>
              <tr>
                <td>#<?= e($d['id']) ?></td>
                <td><?= money($d['amount']) ?></td>
                <td><?= e($d['debt_date']) ?></td>
                <td><?= e($d['due_date'] ?: '-') ?></td>
                <td><?= $d['payment_type']==='installment' ? 'أقساط ('.$d['installment_count'].')' : 'كامل' ?></td>
                <td><span class="badge-status <?= e($d['status']) ?>"><?= e($d['status']==='paid'?'مدفوع':($d['status']==='active'?'نشط':'ملغي')) ?></span></td>
                <td><a href="<?= url('/debts/' . $d['id']) ?>" class="btn btn-sm btn-outline-primary">عرض</a></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($debts)): ?>
              <tr><td colspan="7" class="text-center text-muted py-3">لا توجد ديون لهذا الزبون</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="mt-2 small text-muted">
      <i class="bi bi-clock-history"></i> آخر دفعة: <?= e(fmt_datetime($stats['last_payment'])) ?>
    </div>
  </div>
</div>
