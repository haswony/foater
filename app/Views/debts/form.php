<div class="page-header">
  <h1><i class="bi bi-plus-circle"></i> إضافة دين جديد</h1>
  <a href="<?= url('/debts') ?>" class="btn btn-outline-secondary">رجوع</a>
</div>

<div class="card">
  <div class="card-body">
    <form method="post" action="<?= url('/debts/store') ?>">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">الزبون <span class="text-danger">*</span></label>
          <select name="customer_id" class="form-select" required>
            <option value="">-- اختر زبون --</option>
            <?php foreach ($customers as $c): ?>
              <option value="<?= e($c['id']) ?>" <?= $c['id']==$preselected?'selected':'' ?>>
                <?= e($c['name']) ?> <?= $c['phone'] ? '('.e($c['phone']).')' : '' ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">مبلغ الدين <span class="text-danger">*</span></label>
          <div class="input-group">
            <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required>
            <span class="input-group-text"><?= e($GLOBALS['APP_CONFIG']['currency']) ?></span>
          </div>
        </div>
        <div class="col-md-6">
          <label class="form-label">تاريخ الدين</label>
          <input type="date" name="debt_date" class="form-control" value="<?= e(date('Y-m-d')) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">موعد السداد</label>
          <input type="date" name="due_date" class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label">نوع السداد <span class="text-danger">*</span></label>
          <select name="payment_type" class="form-select" data-toggle-installment="#installmentFields" required>
            <option value="full">كامل (دفعة واحدة)</option>
            <option value="installment">أقساط</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">الوصف / السبب</label>
          <input type="text" name="description" class="form-control">
        </div>

        <div class="col-12" id="installmentFields" style="display:none">
          <div class="alert alert-info">
            <strong>إعدادات الأقساط:</strong> سيتم توليد جدول أقساط تلقائياً عند الحفظ.
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">تكرار السداد</label>
              <select name="installment_freq" class="form-select">
                <option value="monthly">شهري</option>
                <option value="weekly">أسبوعي</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">عدد الأقساط</label>
              <input type="number" min="1" max="60" name="installment_count" class="form-control" value="3">
            </div>
          </div>
        </div>
      </div>

      <div class="mt-3 d-flex gap-2">
        <button class="btn btn-primary"><i class="bi bi-check-lg"></i> حفظ الدين</button>
        <a href="<?= url('/debts') ?>" class="btn btn-outline-secondary">إلغاء</a>
      </div>
    </form>
  </div>
</div>
