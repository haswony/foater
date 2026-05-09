<?php $cfg = $GLOBALS['APP_CONFIG']; ?>
<div class="auth-card">
  <div class="text-center mb-3">
    <i class="bi bi-cash-coin" style="font-size:3rem;color:#0ea5e9"></i>
  </div>
  <h1 class="text-center"><?= e($cfg['app_name']) ?></h1>
  <p class="sub text-center">سجّل الدخول للمتابعة</p>
  <form method="post" action="<?= url('/login') ?>">
    <?= csrf_field() ?>
    <div class="mb-3">
      <label class="form-label">اسم المستخدم</label>
      <input type="text" name="username" class="form-control" required autofocus>
    </div>
    <div class="mb-3">
      <label class="form-label">كلمة المرور</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <button class="btn btn-primary w-100"><i class="bi bi-box-arrow-in-right"></i> دخول</button>
  </form>
</div>
