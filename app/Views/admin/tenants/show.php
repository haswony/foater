<div class="page-header">
  <h1><i class="bi bi-shop"></i> <?= e($tenant['name']) ?>
    <?= $tenant['active'] ? '<span class="badge bg-success">مفعّل</span>' : '<span class="badge bg-secondary">معطّل</span>' ?>
  </h1>
  <a href="<?= url('/admin/tenants') ?>" class="btn btn-outline-secondary">رجوع</a>
</div>

<div class="row g-3">
  <div class="col-md-4">
    <div class="card mb-3">
      <div class="card-body">
        <h6 class="text-muted mb-3"><i class="bi bi-info-circle"></i> بيانات المتجر</h6>
        <div class="d-flex justify-content-between mb-2"><span>الاسم</span><strong><?= e($tenant['name']) ?></strong></div>
        <div class="d-flex justify-content-between mb-2"><span>الهاتف</span><strong><?= e($tenant['phone'] ?: '-') ?></strong></div>
        <div class="d-flex justify-content-between mb-2"><span>العنوان</span><strong><?= e($tenant['address'] ?: '-') ?></strong></div>
        <div class="d-flex justify-content-between mb-2"><span>تاريخ الإنشاء</span><strong><?= e(fmt_date($tenant['created_at'])) ?></strong></div>
      </div>
    </div>

    <div class="card">
      <div class="card-header bg-white"><strong><i class="bi bi-key"></i> إعادة تعيين كلمة مرور المدير</strong></div>
      <div class="card-body">
        <form method="post" action="<?= url('/admin/tenants/'.$tenant['id'].'/reset-password') ?>" data-confirm="تأكيد إعادة تعيين كلمة مرور مدير المتجر؟">
          <?= csrf_field() ?>
          <input type="text" name="password" class="form-control mb-2" placeholder="كلمة مرور جديدة" required minlength="6">
          <button class="btn btn-warning w-100"><i class="bi bi-arrow-clockwise"></i> إعادة تعيين</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-8">
    <div class="row g-3 mb-3">
      <div class="col-6 col-md-3"><div class="stat-card text-center">
        <div class="stat-label">الزبائن</div><div class="stat-value"><?= e($stats['customers']) ?></div>
      </div></div>
      <div class="col-6 col-md-3"><div class="stat-card text-center">
        <div class="stat-label">الديون</div><div class="stat-value"><?= e($stats['debts']) ?></div>
      </div></div>
      <div class="col-6 col-md-3"><div class="stat-card text-center">
        <div class="stat-label">إجمالي الديون</div><div class="stat-value text-primary"><?= money($stats['total_debt']) ?></div>
      </div></div>
      <div class="col-6 col-md-3"><div class="stat-card text-center">
        <div class="stat-label">المسدد</div><div class="stat-value text-success"><?= money($stats['total_paid']) ?></div>
      </div></div>
    </div>

    <div class="card">
      <div class="card-header bg-white"><strong><i class="bi bi-people"></i> مستخدمو المتجر</strong></div>
      <div class="table-responsive">
        <table class="table mb-0">
          <thead><tr><th>#</th><th>اسم المستخدم</th><th>الاسم الكامل</th><th>الصلاحية</th><th>آخر دخول</th><th>الحالة</th></tr></thead>
          <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?= e($u['id']) ?></td>
              <td><?= e($u['username']) ?></td>
              <td><?= e($u['full_name']) ?></td>
              <td><span class="badge bg-<?= $u['role']==='admin'?'primary':'secondary' ?>"><?= $u['role']==='admin'?'مدير':'موظف' ?></span></td>
              <td><?= e(fmt_datetime($u['last_login'])) ?></td>
              <td><?= $u['active']?'<span class="text-success">مفعّل</span>':'<span class="text-muted">معطّل</span>' ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
