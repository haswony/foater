<div class="page-header">
  <h1><i class="bi bi-person-plus"></i> إضافة مستخدم جديد</h1>
  <a href="<?= url('/users') ?>" class="btn btn-outline-secondary">رجوع</a>
</div>

<div class="card">
  <div class="card-body">
    <form method="post" action="<?= url('/users/store') ?>">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">اسم المستخدم <span class="text-danger">*</span></label>
          <input type="text" name="username" class="form-control" required autocomplete="off">
        </div>
        <div class="col-md-6">
          <label class="form-label">الاسم الكامل <span class="text-danger">*</span></label>
          <input type="text" name="full_name" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">كلمة المرور (6 أحرف على الأقل) <span class="text-danger">*</span></label>
          <input type="password" name="password" class="form-control" required minlength="6" autocomplete="new-password">
        </div>
        <div class="col-md-6">
          <label class="form-label">الصلاحية</label>
          <select name="role" class="form-select">
            <option value="employee">موظف</option>
            <option value="admin">مدير</option>
          </select>
        </div>
      </div>
      <div class="mt-3">
        <button class="btn btn-primary"><i class="bi bi-check-lg"></i> حفظ</button>
        <a href="<?= url('/users') ?>" class="btn btn-outline-secondary">إلغاء</a>
      </div>
    </form>
  </div>
</div>
