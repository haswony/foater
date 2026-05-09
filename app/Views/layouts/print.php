<?php $cfg = $GLOBALS['APP_CONFIG']; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>طباعة | <?= e($cfg['app_name']) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
<link rel="stylesheet" href="<?= url('assets/style.css') ?>">
<style>
@media print { .no-print{display:none!important} body{background:#fff} }
body{background:#f8f9fa;font-family:'Segoe UI',Tahoma,sans-serif}
.print-card{max-width:780px;margin:30px auto;background:#fff;padding:35px 40px;border-radius:8px;box-shadow:0 4px 14px rgba(0,0,0,.08)}
</style>
</head>
<body>
<div class="no-print text-center my-3">
  <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> طباعة</button>
  <button onclick="history.back()" class="btn btn-secondary">رجوع</button>
</div>
<?= $content ?? '' ?>
</body>
</html>
