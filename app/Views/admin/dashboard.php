<div class="page-header">
  <h1><i class="bi bi-shield-shaded"></i> لوحة المدير العام</h1>
  <a href="<?= url('/admin/tenants/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> إضافة متجر جديد</a>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-3"><div class="stat-card d-flex justify-content-between align-items-center">
    <div><div class="stat-label">عدد المتاجر</div><div class="stat-value"><?= e($stats['tenants_total']) ?></div></div>
    <div class="stat-icon primary"><i class="bi bi-shop"></i></div>
  </div></div>
  <div class="col-6 col-md-3"><div class="stat-card d-flex justify-content-between align-items-center">
    <div><div class="stat-label">المتاجر المفعّلة</div><div class="stat-value text-success"><?= e($stats['tenants_active']) ?></div></div>
    <div class="stat-icon success"><i class="bi bi-check-circle"></i></div>
  </div></div>
  <div class="col-6 col-md-3"><div class="stat-card d-flex justify-content-between align-items-center">
    <div><div class="stat-label">إجمالي المستخدمين</div><div class="stat-value"><?= e($stats['users_total']) ?></div></div>
    <div class="stat-icon info"><i class="bi bi-people"></i></div>
  </div></div>
  <div class="col-6 col-md-3"><div class="stat-card d-flex justify-content-between align-items-center">
    <div><div class="stat-label">إجمالي الزبائن</div><div class="stat-value"><?= e($stats['customers_total']) ?></div></div>
    <div class="stat-icon warning"><i class="bi bi-person-vcard"></i></div>
  </div></div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4"><div class="stat-card d-flex justify-content-between align-items-center">
    <div><div class="stat-label">إجمالي الديون (كل المتاجر)</div><div class="stat-value text-primary"><?= money($stats['debts_amount']) ?></div></div>
    <div class="stat-icon primary"><i class="bi bi-wallet2"></i></div>
  </div></div>
  <div class="col-md-4"><div class="stat-card d-flex justify-content-between align-items-center">
    <div><div class="stat-label">إجمالي المسدد</div><div class="stat-value text-success"><?= money($stats['payments_amount']) ?></div></div>
    <div class="stat-icon success"><i class="bi bi-cash-stack"></i></div>
  </div></div>
  <div class="col-md-4"><div class="stat-card d-flex justify-content-between align-items-center">
    <div><div class="stat-label">عدد الديون الكلي</div><div class="stat-value"><?= e($stats['debts_total']) ?></div></div>
    <div class="stat-icon info"><i class="bi bi-receipt"></i></div>
  </div></div>
</div>

<div class="card">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <strong><i class="bi bi-shop"></i> آخر المتاجر</strong>
    <a href="<?= url('/admin/tenants') ?>" class="small">عرض الكل</a>
  </div>
  <div class="table-responsive">
    <table class="table mb-0">
      <thead><tr><th>#</th><th>المتجر</th><th>الزبائن</th><th>الديون</th><th>المسدد</th><th>الحالة</th><th></th></tr></thead>
      <tbody>
      <?php foreach (array_slice($tenants, 0, 10) as $t): ?>
        <tr>
          <td>#<?= e($t['id']) ?></td>
          <td><strong><?= e($t['name']) ?></strong></td>
          <td><?= e($t['customers_count']) ?></td>
          <td><?= money($t['total_debts']) ?></td>
          <td class="text-success"><?= money($t['total_paid']) ?></td>
          <td><?= $t['active'] ? '<span class="badge bg-success">مفعّل</span>' : '<span class="badge bg-secondary">معطّل</span>' ?></td>
          <td><a href="<?= url('/admin/tenants/'.$t['id']) ?>" class="btn btn-sm btn-outline-primary">تفاصيل</a></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($tenants)): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">لا يوجد متاجر بعد. أضف متجرك الأول!</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
