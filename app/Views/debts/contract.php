<div class="print-card">
  <div class="text-center mb-3">
    <h2 style="margin:0"><?= e($shop['shop_name'] ?? 'محلي') ?></h2>
    <?php if (!empty($shop['shop_address'])): ?>
      <div class="text-muted small"><?= e($shop['shop_address']) ?></div>
    <?php endif; ?>
    <?php if (!empty($shop['shop_phone'])): ?>
      <div class="text-muted small">هاتف: <?= e($shop['shop_phone']) ?></div>
    <?php endif; ?>
  </div>

  <hr>
  <h3 class="text-center mb-4">عقد دين رقم #<?= e($debt['id']) ?></h3>

  <p>أُبرم هذا العقد بتاريخ <strong><?= e($debt['debt_date']) ?></strong> بين كل من:</p>

  <div class="row mb-3">
    <div class="col-6">
      <strong>الطرف الأول (الدائن):</strong><br>
      <?= e($shop['shop_name'] ?? 'المحل') ?>
    </div>
    <div class="col-6">
      <strong>الطرف الثاني (المدين):</strong><br>
      الاسم: <?= e($debt['customer_name']) ?><br>
      الهاتف: <?= e($debt['customer_phone'] ?: '-') ?><br>
      العنوان: <?= e($debt['customer_address'] ?: '-') ?>
    </div>
  </div>

  <div class="alert alert-light border">
    <p>اعترف الطرف الثاني بأنه مدين للطرف الأول بمبلغ قدره:</p>
    <h4 class="text-center"><?= money($debt['amount']) ?></h4>
    <?php if (!empty($debt['description'])): ?>
      <p>وذلك عن: <?= e($debt['description']) ?></p>
    <?php endif; ?>
    <p>
      <strong>طريقة السداد:</strong>
      <?= $debt['payment_type']==='installment' ? 'على أقساط ' . ($debt['installment_freq']==='weekly'?'أسبوعية':'شهرية') . ' (' . e($debt['installment_count']) . ' أقساط)' : 'دفعة واحدة كاملة' ?>
    </p>
    <?php if ($debt['due_date']): ?>
      <p><strong>موعد السداد النهائي:</strong> <?= e($debt['due_date']) ?></p>
    <?php endif; ?>
  </div>

  <?php if (!empty($installments)): ?>
  <h5>جدول الأقساط:</h5>
  <table class="table table-bordered">
    <thead><tr><th>#</th><th>المبلغ</th><th>تاريخ الاستحقاق</th><th>التوقيع</th></tr></thead>
    <tbody>
      <?php foreach ($installments as $i): ?>
        <tr>
          <td><?= e($i['seq']) ?></td>
          <td><?= money($i['amount']) ?></td>
          <td><?= e($i['due_date']) ?></td>
          <td>&nbsp;</td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>

  <p class="mt-4">يُقرّ الطرف الثاني بالتزامه بالسداد في المواعيد المحددة، وحرر هذا العقد من نسختين.</p>

  <div class="row mt-5">
    <div class="col-6 text-center">
      <hr style="width:70%;margin:0 auto">
      <strong>توقيع الطرف الأول</strong>
    </div>
    <div class="col-6 text-center">
      <hr style="width:70%;margin:0 auto">
      <strong>توقيع الطرف الثاني</strong>
    </div>
  </div>

  <div class="text-muted small text-center mt-4">
    تم إصدار هذا العقد بتاريخ: <?= e(date('Y-m-d H:i')) ?>
  </div>
</div>
