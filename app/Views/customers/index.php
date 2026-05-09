<div class="page-header">
  <h1><i class="bi bi-people"></i> الزبائن</h1>
  <a href="<?= url('/customers/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> إضافة زبون</a>
</div>

<div class="card mb-3">
  <div class="card-body">
    <form method="get" class="row g-2">
      <div class="col-md-10">
        <input type="text" id="liveSearch" name="q" value="<?= e($q) ?>" class="form-control" placeholder="ابحث بالاسم، الهاتف، أو العنوان...">
      </div>
      <div class="col-md-2">
        <button class="btn btn-outline-primary w-100"><i class="bi bi-search"></i> بحث</button>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0 align-middle">
      <thead>
        <tr>
          <th>#</th><th>الاسم</th><th>الهاتف</th>
          <th>إجمالي الدين</th><th>المدفوع</th><th>المتبقي</th>
          <th>آخر دفعة</th><th></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($customers as $c):
        $remain = max(0, $c['total_debt'] - $c['total_paid']);
        $isLate = false;
        // التحقق إذا الزبون متأخر (دفعة لم تتم في آخر 30 يوم وله متبقي)
        if ($remain > 0 && $c['last_payment']) {
          $isLate = days_diff(substr($c['last_payment'],0,10)) > 45;
        } elseif ($remain > 0 && !$c['last_payment']) {
          $isLate = days_diff($c['created_at']) > 30;
        }
      ?>
        <tr class="<?= $isLate ? 'row-late' : '' ?>">
          <td><?= e($c['id']) ?></td>
          <td><strong><?= e($c['name']) ?></strong></td>
          <td><?= e($c['phone']) ?: '-' ?></td>
          <td><?= money($c['total_debt']) ?></td>
          <td class="text-success"><?= money($c['total_paid']) ?></td>
          <td class="<?= $remain > 0 ? 'text-danger fw-bold' : 'text-muted' ?>"><?= money($remain) ?></td>
          <td><?= e(fmt_date($c['last_payment'])) ?></td>
          <td>
            <a href="<?= url('/customers/' . $c['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
            <a href="<?= url('/customers/' . $c['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($customers)): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">لا يوجد زبائن</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
