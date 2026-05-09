<div class="page-header">
  <h1><i class="bi bi-shop"></i> إدارة المتاجر</h1>
  <a href="<?= url('/admin/tenants/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> إضافة متجر جديد</a>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0 align-middle">
      <thead><tr>
        <th>#</th><th>اسم المتجر</th><th>الهاتف</th><th>المستخدمون</th>
        <th>الزبائن</th><th>الديون</th><th>المسدد</th><th>الحالة</th><th></th>
      </tr></thead>
      <tbody>
      <?php foreach ($tenants as $t): ?>
        <tr class="<?= !$t['active'] ? 'text-muted' : '' ?>">
          <td>#<?= e($t['id']) ?></td>
          <td><strong><?= e($t['name']) ?></strong>
              <?php if (!empty($t['address'])): ?>
                <div class="small text-muted"><?= e($t['address']) ?></div>
              <?php endif; ?>
          </td>
          <td><?= e($t['phone'] ?: '-') ?></td>
          <td><?= e($t['users_count']) ?></td>
          <td><?= e($t['customers_count']) ?></td>
          <td><?= money($t['total_debts']) ?></td>
          <td class="text-success"><?= money($t['total_paid']) ?></td>
          <td><?= $t['active'] ? '<span class="badge bg-success">مفعّل</span>' : '<span class="badge bg-secondary">معطّل</span>' ?></td>
          <td>
            <a href="<?= url('/admin/tenants/'.$t['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
            <form method="post" action="<?= url('/admin/tenants/'.$t['id'].'/toggle') ?>" class="d-inline">
              <?= csrf_field() ?>
              <button class="btn btn-sm btn-outline-warning" title="تبديل الحالة"><i class="bi bi-toggle-on"></i></button>
            </form>
            <form method="post" action="<?= url('/admin/tenants/'.$t['id'].'/delete') ?>" class="d-inline" data-confirm="حذف المتجر سيمسح كل بياناته وموظفيه! متأكد؟">
              <?= csrf_field() ?>
              <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($tenants)): ?>
        <tr><td colspan="9" class="text-center text-muted py-4">لا يوجد متاجر بعد</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
