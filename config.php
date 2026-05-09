<?php
// إعدادات النظام العامة
return [
    'app_name'      => 'نظام إدارة الديون والأقساط',
    'app_short'     => 'DIMS',
    'currency'      => 'د.ع',
    'timezone'      => 'Asia/Baghdad',
    'db_path'       => __DIR__ . '/database/data.sqlite',
    'base_url'      => '', // اتركه فارغاً للسيرفر المدمج
    'session_name'  => 'DIMS_SESS',
    'late_days_threshold' => 3, // عدد الأيام التي بعدها يعتبر متأخراً

    // ============= إعدادات الإنتاج =============
    // غيّر إلى false عند النشر للإنتاج لإخفاء الأخطاء التقنية من المستخدم
    'debug'         => false,
    // إجبار HTTPS (اضبط على true بعد ربط شهادة SSL)
    'force_https'   => false,
];
