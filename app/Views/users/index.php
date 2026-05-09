<div class="page-header">
  <h1><i class="bi bi-person-gear"></i> المستخدمون</h1>
  <a href="<?= url('/users/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> إضافة مستخدم</a>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table mb-0 align-middle">
      <thead><tr>
        <th>#</th><th>اسم المستخدم</th><th>الاسم الكامل</th><th>الصلاحية</th>
        <th>آخر دخول</th><th>الحالة</th><th></th>
      </tr></thead>
      <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= e($u['id']) ?></td>
          <td><?= e($u['username']) ?></td>
          <td><?= e($u['full_name']) ?></td>
          <td><span class="badge bg-<?= $u['role']==='admin'?'primary':'secondary' ?>"><?= $u['role']==='admin'?'مدير':'موظف' ?></span></td>
          <td><?= e(fmt_datetime($u['last_login'])) ?></td>
          <td><?= $u['active'] ? '<span class="badge bg-success">مفعّل</span>' : '<span class="badge bg-secondary">معطّل</span>' ?></td>
          <td>
            <?php if ($u['id'] != current_user()['id']): ?>
              <form method="post" action="<?= url('/users/'.$u['id'].'/toggle') ?>" class="d-inline">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-outline-warning" title="تبديل الحالة"><i class="bi bi-toggle-on"></i></button>
              </form>
              <form method="post" action="<?= url('/users/'.$u['id'].'/delete') ?>" class="d-inline" data-confirm="حذف المستخدم؟">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
              </form>
            <?php else: ?>
              <span class="text-muted small">— أنت —</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
