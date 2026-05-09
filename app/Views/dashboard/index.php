<div class="page-header">
  <h1><i class="bi bi-speedometer2"></i> لوحة التحكم</h1>
  <div class="text-muted small"><?= e(date('l، Y-m-d')) ?></div>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card d-flex justify-content-between align-items-center">
      <div>
        <div class="stat-label">إجمالي الديون</div>
        <div class="stat-value"><?= money($totals['total_debt']) ?></div>
      </div>
      <div class="stat-icon primary"><i class="bi bi-wallet2"></i></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card d-flex justify-content-between align-items-center">
      <div>
        <div class="stat-label">إجمالي المدفوع</div>
        <div class="stat-value"><?= money($totals['total_paid']) ?></div>
      </div>
      <div class="stat-icon success"><i class="bi bi-cash-stack"></i></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card d-flex justify-content-between align-items-center">
      <div>
        <div class="stat-label">المتبقي</div>
        <div class="stat-value text-warning"><?= money($remaining) ?></div>
      </div>
      <div class="stat-icon warning"><i class="bi bi-hourglass-split"></i></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card d-flex justify-content-between align-items-center">
      <div>
        <div class="stat-label">عدد المتأخرين</div>
        <div class="stat-value text-danger"><?= e($overdueCount) ?></div>
      </div>
      <div class="stat-icon danger"><i class="bi bi-exclamation-triangle"></i></div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="stat-card d-flex justify-content-between align-items-center">
      <div>
        <div class="stat-label">دفعات هذا الشهر</div>
        <div class="stat-value text-success"><?= money($monthly['month_paid']) ?></div>
      </div>
      <div class="stat-icon success"><i class="bi bi-graph-up-arrow"></i></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card d-flex justify-content-between align-items-center">
      <div>
        <div class="stat-label">ديون جديدة هذا الشهر</div>
        <div class="stat-value"><?= e($monthly['month_debts']) ?></div>
      </div>
      <div class="stat-icon info"><i class="bi bi-plus-circle"></i></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card d-flex justify-content-between align-items-center">
      <div>
        <div class="stat-label">عدد الزبائن</div>
        <div class="stat-value"><?= e($customersCount) ?></div>
      </div>
      <div class="stat-icon primary"><i class="bi bi-people"></i></div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header bg-white"><strong><i class="bi bi-bar-chart"></i> الدفعات (آخر 6 أشهر)</strong></div>
      <div class="card-body">
        <canvas id="paymentsChart" height="120"></canvas>
      </div>
    </div>

    <div class="card mt-3">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong><i class="bi bi-exclamation-octagon text-danger"></i> الزبائن المتأخرون</strong>
        <a href="<?= url('/customers') ?>" class="small">عرض الكل</a>
      </div>
      <div class="table-responsive">
        <table class="table mb-0">
          <thead><tr><th>الزبون</th><th>الهاتف</th><th>أقدم تأخر</th><th></th></tr></thead>
          <tbody>
          <?php if (empty($overdueCustomers)): ?>
            <tr><td colspan="4" class="text-center text-muted py-3">لا يوجد زبائن متأخرون 🎉</td></tr>
          <?php else: foreach ($overdueCustomers as $c): ?>
            <tr class="row-late">
              <td><?= e($c['name']) ?></td>
              <td><?= e($c['phone']) ?></td>
              <td><?= e($c['earliest_overdue'] ?? '-') ?></td>
              <td><a href="<?= url('/customers/' . $c['id']) ?>" class="btn btn-sm btn-outline-danger">عرض</a></td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card">
      <div class="card-header bg-white"><strong><i class="bi bi-bell"></i> تذكيرات أقساط قادمة</strong></div>
      <div class="list-group list-group-flush" style="max-height:300px;overflow:auto">
        <?php if (empty($reminders)): ?>
          <div class="text-center text-muted py-3">لا تذكيرات حالياً</div>
        <?php else: foreach ($reminders as $r):
            $diff = days_diff($r['due_date']);
            $cls = $diff > 0 ? 'text-danger' : ($diff === 0 ? 'text-warning' : 'text-muted'); ?>
          <a href="<?= url('/debts/' . $r['debt_id']) ?>" class="list-group-item list-group-item-action">
            <div class="d-flex justify-content-between">
              <div>
                <strong><?= e($r['customer_name']) ?></strong>
                <div class="small text-muted">القسط #<?= e($r['seq']) ?> - <?= money($r['amount']) ?></div>
              </div>
              <div class="text-end">
                <div class="<?= $cls ?>"><?= e($r['due_date']) ?></div>
                <div class="small <?= $cls ?>">
                  <?php if ($diff > 0): ?>متأخر <?= $diff ?> يوم
                  <?php elseif ($diff === 0): ?>اليوم
                  <?php else: ?>بعد <?= -$diff ?> يوم<?php endif; ?>
                </div>
              </div>
            </div>
          </a>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <div class="card mt-3">
      <div class="card-header bg-white"><strong><i class="bi bi-receipt"></i> أحدث الدفعات</strong></div>
      <div class="list-group list-group-flush">
        <?php foreach ($recentPayments as $p): ?>
          <a href="<?= url('/payments/' . $p['id'] . '/receipt') ?>" class="list-group-item list-group-item-action d-flex justify-content-between">
            <div>
              <strong><?= e($p['customer_name']) ?></strong>
              <div class="small text-muted"><?= e(fmt_datetime($p['paid_at'])) ?></div>
            </div>
            <span class="text-success fw-bold"><?= money($p['amount']) ?></span>
          </a>
        <?php endforeach; ?>
        <?php if (empty($recentPayments)): ?>
          <div class="text-center text-muted py-3">لا توجد دفعات حتى الآن</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
const chartData = <?= json_encode($chart, JSON_UNESCAPED_UNICODE) ?>;
const labels = chartData.map(r => r.m);
const data   = chartData.map(r => parseFloat(r.s));
new Chart(document.getElementById('paymentsChart'), {
  type: 'bar',
  data: { labels, datasets: [{
    label: 'إجمالي الدفعات',
    data,
    backgroundColor: 'rgba(14,165,233,.6)',
    borderColor: '#0284c7',
    borderWidth: 2,
    borderRadius: 6,
  }] },
  options: { responsive: true, plugins: { legend: { display: false } } }
});
</script>
