<?php
$statusLabel = ['active'=>'نشط','paid'=>'مدفوع','cancelled'=>'ملغي'][$debt['status']] ?? $debt['status'];
?>
<div class="page-header">
  <h1><i class="bi bi-receipt"></i> دين #<?= e($debt['id']) ?> - <a href="<?= url('/customers/'.$debt['customer_id']) ?>"><?= e($debt['customer_name']) ?></a></h1>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= url('/debts/' . $debt['id'] . '/contract') ?>" class="btn btn-outline-primary" target="_blank"><i class="bi bi-file-earmark-text"></i> عقد الدين</a>
    <?php if (is_admin()): ?>
    <form method="post" action="<?= url('/debts/' . $debt['id'] . '/delete') ?>" data-confirm="حذف الدين سيحذف كل دفعاته. متأكد؟" class="d-inline">
      <?= csrf_field() ?>
      <button class="btn btn-outline-danger"><i class="bi bi-trash"></i> حذف</button>
    </form>
    <?php endif; ?>
  </div>
</div>

<div class="row g-3">
  <div class="col-md-3"><div class="stat-card text-center">
    <div class="stat-label">المبلغ</div>
    <div class="stat-value"><?= money($debt['amount']) ?></div>
  </div></div>
  <div class="col-md-3"><div class="stat-card text-center">
    <div class="stat-label">المدفوع</div>
    <div class="stat-value text-success"><?= money($paid) ?></div>
  </div></div>
  <div class="col-md-3"><div class="stat-card text-center">
    <div class="stat-label">المتبقي</div>
    <div class="stat-value text-danger"><?= money($remaining) ?></div>
  </div></div>
  <div class="col-md-3"><div class="stat-card text-center">
    <div class="stat-label">الحالة</div>
    <div class="stat-value"><span class="badge-status <?= e($debt['status']) ?>"><?= e($statusLabel) ?></span></div>
  </div></div>
</div>

<div class="row g-3 mt-1">
  <div class="col-lg-7">
    <?php if (!empty($installments)): ?>
    <div class="card">
      <div class="card-header bg-white"><strong><i class="bi bi-calendar-check"></i> جدول الأقساط</strong></div>
      <div class="table-responsive">
        <table class="table mb-0">
          <thead><tr><th>#</th><th>المبلغ</th><th>المدفوع</th><th>المتبقي</th><th>تاريخ الاستحقاق</th><th>الحالة</th></tr></thead>
          <tbody>
            <?php foreach ($installments as $i):
              $instRem = $i['amount'] - $i['paid_amount'];
              $statusMap = ['pending'=>'معلق','partial'=>'جزئي','paid'=>'مدفوع','overdue'=>'متأخر'];
            ?>
              <tr class="<?= $i['status']==='overdue'?'row-late':'' ?>">
                <td><?= e($i['seq']) ?></td>
                <td><?= money($i['amount']) ?></td>
                <td class="text-success"><?= money($i['paid_amount']) ?></td>
                <td><?= money($instRem) ?></td>
                <td><?= e($i['due_date']) ?></td>
                <td><span class="badge-status <?= e($i['status']) ?>"><?= e($statusMap[$i['status']] ?? $i['status']) ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <div class="card mt-3">
      <div class="card-header bg-white"><strong><i class="bi bi-cash-stack"></i> سجل الدفعات</strong></div>
      <div class="table-responsive">
        <table class="table mb-0">
          <thead><tr><th>#</th><th>المبلغ</th><th>تاريخ الدفع</th><th>الطريقة</th><th>المستلم</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($payments as $p): ?>
            <tr>
              <td>#<?= e($p['id']) ?></td>
              <td class="text-success fw-bold"><?= money($p['amount']) ?></td>
              <td><?= e(fmt_datetime($p['paid_at'])) ?></td>
              <td><?= e(['cash'=>'نقدي','transfer'=>'تحويل','card'=>'بطاقة'][$p['method']] ?? $p['method']) ?></td>
              <td><?= e($p['received_by_name'] ?? '-') ?></td>
              <td>
                <a href="<?= url('/payments/'.$p['id'].'/receipt') ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-printer"></i></a>
                <?php if (is_admin()): ?>
                <form method="post" action="<?= url('/payments/'.$p['id'].'/delete') ?>" class="d-inline" data-confirm="حذف الدفعة؟">
                  <?= csrf_field() ?>
                  <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($payments)): ?>
            <tr><td colspan="6" class="text-center text-muted py-3">لم يتم تسجيل أي دفعة</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <?php if ($remaining > 0): ?>
    <div class="card">
      <div class="card-header bg-white"><strong><i class="bi bi-plus-circle text-success"></i> تسجيل دفعة جديدة</strong></div>
      <div class="card-body">
        <form method="post" action="<?= url('/payments/store') ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="debt_id" value="<?= e($debt['id']) ?>">
          <div class="mb-3">
            <label class="form-label">المبلغ <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="number" step="0.01" min="0.01" max="<?= e($remaining) ?>" id="paymentAmount" name="amount" class="form-control" required>
              <span class="input-group-text"><?= e($GLOBALS['APP_CONFIG']['currency']) ?></span>
            </div>
            <div class="form-text">سيتبقى: <span id="remainingDisplay" data-max="<?= e($remaining) ?>"><?= number_format($remaining,0,'.',',') ?></span> <?= e($GLOBALS['APP_CONFIG']['currency']) ?></div>
          </div>
          <?php if (!empty($installments)): ?>
          <div class="mb-3">
            <label class="form-label">سداد قسط محدد (اختياري)</label>
            <select name="installment_id" class="form-select">
              <option value="">-- توزيع تلقائي --</option>
              <?php foreach ($installments as $i): if ($i['status']==='paid') continue; ?>
                <option value="<?= e($i['id']) ?>">القسط #<?= e($i['seq']) ?> - <?= money($i['amount']-$i['paid_amount']) ?> (<?= e($i['due_date']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>
          <div class="row g-2">
            <div class="col-6">
              <label class="form-label">تاريخ الدفع</label>
              <input type="datetime-local" name="paid_at" class="form-control" value="<?= e(date('Y-m-d\TH:i')) ?>">
            </div>
            <div class="col-6">
              <label class="form-label">طريقة الدفع</label>
              <select name="method" class="form-select">
                <option value="cash">نقدي</option>
                <option value="transfer">تحويل</option>
                <option value="card">بطاقة</option>
              </select>
            </div>
          </div>
          <div class="mt-2">
            <label class="form-label">ملاحظات</label>
            <textarea name="notes" rows="2" class="form-control"></textarea>
          </div>
          <button class="btn btn-success w-100 mt-3"><i class="bi bi-check-circle"></i> تسجيل الدفعة</button>
        </form>
      </div>
    </div>
    <?php else: ?>
      <div class="alert alert-success text-center">
        <i class="bi bi-check-circle" style="font-size:3rem"></i>
        <h4 class="mt-2">تم سداد الدين بالكامل</h4>
      </div>
    <?php endif; ?>

    <div class="card mt-3">
      <div class="card-body">
        <h6 class="text-muted mb-3"><i class="bi bi-info-circle"></i> تفاصيل الدين</h6>
        <div class="d-flex justify-content-between"><span>تاريخ الدين</span><strong><?= e($debt['debt_date']) ?></strong></div>
        <div class="d-flex justify-content-between"><span>موعد السداد</span><strong><?= e($debt['due_date'] ?: '-') ?></strong></div>
        <div class="d-flex justify-content-between"><span>النوع</span><strong><?= $debt['payment_type']==='installment'?'أقساط':'كامل' ?></strong></div>
        <?php if (!empty($debt['description'])): ?>
          <hr><div class="text-muted small"><?= e($debt['description']) ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
