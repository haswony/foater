<?php
$isEdit = !empty($customer);
$action = $isEdit ? url('/customers/' . $customer['id'] . '/update') : url('/customers/store');
?>
<div class="page-header">
  <h1><i class="bi bi-person-plus"></i> <?= $isEdit ? 'تعديل زبون' : 'إضافة زبون جديد' ?></h1>
  <a href="<?= url('/customers') ?>" class="btn btn-outline-secondary">رجوع</a>
</div>

<div class="card">
  <div class="card-body">
    <form method="post" action="<?= e($action) ?>">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">اسم الزبون <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control" required value="<?= e($customer['name'] ?? old('name')) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">رقم الهاتف</label>
          <input type="text" name="phone" class="form-control" value="<?= e($customer['phone'] ?? old('phone')) ?>">
        </div>
        <div class="col-12">
          <label class="form-label">العنوان</label>
          <input type="text" name="address" class="form-control" value="<?= e($customer['address'] ?? old('address')) ?>">
        </div>
        <div class="col-12">
          <label class="form-label">ملاحظات</label>
          <textarea name="notes" rows="3" class="form-control"><?= e($customer['notes'] ?? old('notes')) ?></textarea>
        </div>
      </div>
      <div class="mt-3 d-flex gap-2">
        <button class="btn btn-primary"><i class="bi bi-check-lg"></i> حفظ</button>
        <a href="<?= url('/customers') ?>" class="btn btn-outline-secondary">إلغاء</a>
      </div>
    </form>
  </div>
</div>
