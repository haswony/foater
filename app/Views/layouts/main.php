<?php $cfg = $GLOBALS['APP_CONFIG']; $u = current_user(); $isSuper = ($u['role'] ?? '') === 'super_admin'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(($title ?? '') . ' | ' . $cfg['app_name']) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="<?= url('assets/style.css') ?>">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark sidebar-nav d-lg-none">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?= url('/') ?>"><i class="bi bi-cash-coin"></i> <?= e($cfg['app_name']) ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
      <span class="navbar-toggler-icon"></span>
    </button>
  </div>
</nav>

<div class="layout">
  <aside class="sidebar offcanvas-lg offcanvas-end" id="sidebar">
    <div class="sidebar-header">
      <i class="bi <?= $isSuper ? 'bi-shield-shaded' : 'bi-cash-coin' ?> fs-3 text-info"></i>
      <div>
        <div class="brand"><?= e($isSuper ? 'لوحة المدير العام' : ($u['tenant_name'] ?? $cfg['app_name'])) ?></div>
        <div class="brand-sub"><?= e($u['full_name'] ?? '') ?></div>
      </div>
    </div>
    <nav class="nav flex-column">
      <?php if ($isSuper): ?>
        <a class="nav-link <?= active_if('/admin/tenants') ? '' : (active_if('/admin') ? 'active' : '') ?>" href="<?= url('/admin') ?>"><i class="bi bi-speedometer2"></i> لوحة المدير العام</a>
        <a class="nav-link <?= active_if('/admin/tenants') ?>" href="<?= url('/admin/tenants') ?>"><i class="bi bi-shop"></i> المتاجر</a>
        <a class="nav-link <?= active_if('/admin/tenants/create') ?>" href="<?= url('/admin/tenants/create') ?>"><i class="bi bi-plus-circle"></i> إضافة متجر</a>
      <?php else: ?>
        <a class="nav-link <?= active_if('/dashboard') ?>" href="<?= url('/dashboard') ?>"><i class="bi bi-speedometer2"></i> لوحة التحكم</a>
        <a class="nav-link <?= active_if('/customers') ?>" href="<?= url('/customers') ?>"><i class="bi bi-people"></i> الزبائن</a>
        <a class="nav-link <?= active_if('/debts') ?>" href="<?= url('/debts') ?>"><i class="bi bi-wallet2"></i> الديون</a>
        <a class="nav-link <?= active_if('/payments') ?>" href="<?= url('/payments') ?>"><i class="bi bi-cash-stack"></i> الدفعات</a>
        <a class="nav-link <?= active_if('/reports') ?>" href="<?= url('/reports') ?>"><i class="bi bi-graph-up"></i> التقارير</a>
        <?php if (is_admin()): ?>
          <a class="nav-link <?= active_if('/users') ?>" href="<?= url('/users') ?>"><i class="bi bi-person-gear"></i> المستخدمون</a>
        <?php endif; ?>
      <?php endif; ?>
      <hr class="my-2 border-secondary">
      <a class="nav-link" href="<?= url('/profile') ?>"><i class="bi bi-person-circle"></i> ملفي الشخصي</a>
      <a class="nav-link <?= active_if('/about/developer') ?>" href="<?= url('/about/developer') ?>"><i class="bi bi-code-slash"></i> عن المطور</a>
      <a class="nav-link text-danger" href="<?= url('/logout') ?>"><i class="bi bi-box-arrow-right"></i> تسجيل الخروج</a>
    </nav>
  </aside>

  <main class="content">
    <div class="container-fluid p-3 p-md-4">
      <?php foreach (flash_messages() as $f): ?>
        <div class="alert alert-<?= e($f['type']) ?> alert-dismissible fade show" role="alert">
          <?= e($f['msg']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endforeach; ?>
      <?= $content ?? '' ?>
    </div>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="<?= url('assets/app.js') ?>"></script>
</body>
</html>
