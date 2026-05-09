<div class="page-header">
  <h1><i class="bi bi-graph-up"></i> التقارير والإحصائيات</h1>
  <div class="d-flex gap-2">
    <a href="<?= url('/reports/export.csv?type=customers') ?>" class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-excel"></i> تصدير الزبائن</a>
    <a href="<?= url('/reports/export.csv?type=debts') ?>" class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-excel"></i> تصدير الديون</a>
    <a href="<?= url('/reports/export.csv?type=payments') ?>" class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-excel"></i> تصدير الدفعات</a>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body">
    <form method="get" class="d-flex gap-2 align-items-end flex-wrap">
      <div>
        <label class="form-label">اختر الشهر</label>
        <input type="month" name="month" value="<?= e($month) ?>" class="form-control">
      </div>
      <button class="btn btn-primary"><i class="bi bi-funnel"></i> عرض</button>
    </form>
  </div>
</div>

<h5 class="text-muted">إحصائيات شهر <?= e($month) ?></h5>
<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="stat-card text-center">
    <div class="stat-label">إجمالي المحصّل</div>
    <div class="stat-value text-success"><?= money($monthlyPaid) ?></div>
  </div></div>
  <div class="col-md-3"><div class="stat-card text-center">
    <div class="stat-label">عدد الديون الجديدة</div>
    <div class="stat-value"><?= e($monthlyDebts) ?></div>
  </div></div>
  <div class="col-md-3"><div class="stat-card text-center">
    <div class="stat-label">قيمة الديون الجديدة</div>
    <div class="stat-value text-primary"><?= money($monthlyDebtsAmount) ?></div>
  </div></div>
  <div class="col-md-3"><div class="stat-card text-center">
    <div class="stat-label">عدد المتأخرين</div>
    <div class="stat-value text-danger"><?= e($overdueCount) ?></div>
  </div></div>
</div>

<h5 class="text-muted">الإجماليات الكلية</h5>
<div class="row g-3 mb-4">
  <div class="col-md-4"><div class="stat-card text-center">
    <div class="stat-label">إجمالي الديون</div>
    <div class="stat-value"><?= money($allTime['total_debt']) ?></div>
  </div></div>
  <div class="col-md-4"><div class="stat-card text-center">
    <div class="stat-label">إجمالي المسدد</div>
    <div class="stat-value text-success"><?= money($allTime['total_paid']) ?></div>
  </div></div>
  <div class="col-md-4"><div class="stat-card text-center">
    <div class="stat-label">إجمالي المتبقي</div>
    <div class="stat-value text-danger"><?= money($totalRemaining) ?></div>
  </div></div>
</div>

<div class="card">
  <div class="card-header bg-white"><strong><i class="bi bi-trophy"></i> أكثر الزبائن مديونية</strong></div>
  <div class="table-responsive">
    <table class="table mb-0">
      <thead><tr><th>#</th><th>الزبون</th><th>إجمالي الدين</th><th>المدفوع</th><th>المتبقي</th></tr></thead>
      <tbody>
      <?php foreach ($topDebtors as $i => $d): $rem = $d['total'] - $d['paid']; ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><a href="<?= url('/customers/'.$d['id']) ?>"><?= e($d['name']) ?></a></td>
          <td><?= money($d['total']) ?></td>
          <td class="text-success"><?= money($d['paid']) ?></td>
          <td class="text-danger fw-bold"><?= money($rem) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($topDebtors)): ?>
        <tr><td colspan="5" class="text-center text-muted py-3">لا توجد بيانات</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
