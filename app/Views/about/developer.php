<?php $cfg = $GLOBALS['APP_CONFIG']; ?>
<div class="page-header">
  <h1><i class="bi bi-code-slash"></i> عن المطور</h1>
</div>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card developer-card">
      <div class="card-body text-center p-5">
        <div class="dev-avatar mx-auto mb-4">
          <i class="bi bi-person-circle"></i>
        </div>

        <h2 class="mb-1">حسين سعد</h2>
        <p class="text-muted mb-4">تم تطوير بواسطة حسين سعد</p>

        <div class="dev-divider"></div>

        <p class="text-muted mt-4 mb-0">
          <i class="bi bi-c-circle"></i>
          جميع الحقوق محفوظة © <?= date('Y') ?> · حسين سعد
        </p>
      </div>
    </div>
  </div>
</div>

<style>
.developer-card {
  border: none;
  border-radius: 20px;
  box-shadow: 0 20px 50px rgba(0,0,0,.08);
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
}
.dev-avatar {
  width: 120px; height: 120px;
  background: linear-gradient(135deg, #0ea5e9, #0f172a);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: #fff;
  font-size: 4rem;
  box-shadow: 0 10px 30px rgba(14,165,233,.4);
}
.dev-divider {
  height: 2px;
  background: linear-gradient(90deg, transparent, #0ea5e9, transparent);
  margin: 0 auto;
  width: 60%;
}
.dev-info-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px;
  background: #fff;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}
.dev-info-item i {
  font-size: 2rem;
}
</style>
