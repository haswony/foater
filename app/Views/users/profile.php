<?php $u = current_user(); ?>
<div class="page-header">
  <h1><i class="bi bi-person-circle"></i> ملفي الشخصي</h1>
</div>

<div class="row g-3">
  <div class="col-md-5">
    <div class="card">
      <div class="card-body text-center">
        <i class="bi bi-person-circle" style="font-size:5rem;color:#0ea5e9"></i>
        <h4 class="mt-2"><?= e($u['full_name']) ?></h4>
        <div class="text-muted">@<?= e($u['username']) ?></div>
        <span class="badge bg-<?= $u['role']==='admin'?'primary':'secondary' ?> mt-2">
          <?= $u['role']==='admin'?'مدير':'موظف' ?>
        </span>
        <hr>
        <div class="small text-muted">آخر دخول: <?= e(fmt_datetime($u['last_login'] ?? null)) ?></div>
      </div>
    </div>
  </div>

  <div class="col-md-7">
    <div class="card">
      <div class="card-header bg-white"><strong><i class="bi bi-shield-lock"></i> تغيير كلمة المرور</strong></div>
      <div class="card-body">
        <form method="post" action="<?= url('/profile/password') ?>">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label">كلمة المرور الحالية</label>
            <input type="password" name="current" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">كلمة المرور الجديدة (6 أحرف على الأقل)</label>
            <input type="password" name="new" class="form-control" required minlength="6">
          </div>
          <button class="btn btn-primary"><i class="bi bi-check-lg"></i> تحديث</button>
        </form>
      </div>
    </div>
  </div>
</div>
