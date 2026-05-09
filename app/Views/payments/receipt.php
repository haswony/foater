<div class="receipt">
  <h3><?= e($shop['shop_name'] ?? 'محلي') ?></h3>
  <?php if (!empty($shop['shop_phone'])): ?>
    <div class="text-center small">📞 <?= e($shop['shop_phone']) ?></div>
  <?php endif; ?>
  <?php if (!empty($shop['shop_address'])): ?>
    <div class="text-center small"><?= e($shop['shop_address']) ?></div>
  <?php endif; ?>
  <div class="line"></div>

  <div class="text-center"><strong>وصل دفع رقم: <?= e($payment['id']) ?></strong></div>
  <div class="line"></div>

  <table>
    <tr><td>التاريخ:</td><td><?= e(fmt_datetime($payment['paid_at'])) ?></td></tr>
    <tr><td>الزبون:</td><td><?= e($payment['customer_name']) ?></td></tr>
    <?php if (!empty($payment['customer_phone'])): ?>
      <tr><td>الهاتف:</td><td><?= e($payment['customer_phone']) ?></td></tr>
    <?php endif; ?>
    <tr><td>دين رقم:</td><td>#<?= e($payment['debt_id']) ?></td></tr>
    <tr><td>طريقة الدفع:</td><td><?= e(['cash'=>'نقدي','transfer'=>'تحويل','card'=>'بطاقة'][$payment['method']] ?? $payment['method']) ?></td></tr>
  </table>

  <div class="line"></div>
  <table>
    <tr><td>إجمالي الدين:</td><td style="text-align:left"><?= money($payment['debt_amount']) ?></td></tr>
    <tr><td><strong>المبلغ المدفوع:</strong></td><td style="text-align:left"><strong><?= money($payment['amount']) ?></strong></td></tr>
    <tr><td>المتبقي:</td><td style="text-align:left"><?= money($remaining) ?></td></tr>
  </table>
  <div class="line"></div>

  <?php if (!empty($payment['notes'])): ?>
    <div class="small">ملاحظات: <?= e($payment['notes']) ?></div>
    <div class="line"></div>
  <?php endif; ?>

  <div class="text-center small">
    استلم: <?= e($payment['received_by_name'] ?? '-') ?><br>
    شكراً لتعاملكم معنا
  </div>
</div>

<script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
