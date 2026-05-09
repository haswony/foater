<div class="page-header">
  <h1><i class="bi bi-shop-window"></i> إضافة متجر جديد</h1>
  <a href="<?= url('/admin/tenants') ?>" class="btn btn-outline-secondary">رجوع</a>
</div>

<div class="card">
  <div class="card-body">
    <form method="post" action="<?= url('/admin/tenants/store') ?>">
      <?= csrf_field() ?>

      <h6 class="text-muted mb-3"><i class="bi bi-shop"></i> بيانات المتجر</h6>
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label">اسم المتجر <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">رقم الهاتف</label>
          <input type="text" name="phone" class="form-control">
        </div>
        <div class="col-12">
          <label class="form-label">العنوان</label>
          <input type="text" name="address" class="form-control">
        </div>
      </div>

      <h6 class="text-muted mb-3"><i class="bi bi-person-badge"></i> بيانات مدير المتجر (سيستخدمها للدخول)</h6>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">الاسم الكامل للمدير <span class="text-danger">*</span></label>
          <input type="text" name="admin_name" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">اسم المستخدم <span class="text-danger">*</span></label>
          <input type="text" name="username" class="form-control" required autocomplete="off">
        </div>
        <div class="col-md-6">
          <label class="form-label">كلمة المرور (6 أحرف على الأقل) <span class="text-danger">*</span></label>
          <input type="text" name="password" class="form-control" required minlength="6" autocomplete="new-password">
          <div class="form-text">سجّل هذه البيانات لإعطائها لصاحب المتجر</div>
        </div>
      </div>

      <div class="alert alert-info mt-4">
        <i class="bi bi-info-circle"></i> سيتم إنشاء المتجر ومنحه مدير، وستكون بياناته معزولة تماماً عن باقي المتاجر.
      </div>

      <div class="d-flex gap-2">
        <button class="btn btn-primary"><i class="bi bi-check-lg"></i> إنشاء المتجر</button>
        <a href="<?= url('/admin/tenants') ?>" class="btn btn-outline-secondary">إلغاء</a>
      </div>
    </form>
  </div>
</div>
