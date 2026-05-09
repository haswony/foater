<?php
declare(strict_types=1);

// ============================================================
// Debt & Installment Management System (DIMS) - Front Controller
// ============================================================

$ROOT = dirname(__DIR__);
$GLOBALS['APP_CONFIG'] = require $ROOT . '/config.php';
date_default_timezone_set($GLOBALS['APP_CONFIG']['timezone'] ?? 'UTC');

// إعدادات الأخطاء حسب وضع التشغيل
if (!empty($GLOBALS['APP_CONFIG']['debug'])) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', $ROOT . '/database/error.log');
}

// إجبار HTTPS في الإنتاج
if (!empty($GLOBALS['APP_CONFIG']['force_https']) && empty($_SERVER['HTTPS']) && ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') !== 'https') {
    $url = 'https://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '/');
    header('Location: ' . $url, true, 301);
    exit;
}

// Autoloader بسيط (PSR-4 لـ App\)
spl_autoload_register(function ($class) use ($ROOT) {
    if (strpos($class, 'App\\') !== 0) return;
    $rel = str_replace(['App\\', '\\'], ['', '/'], $class);
    $file = $ROOT . '/app/' . $rel . '.php';
    if (file_exists($file)) require $file;
});

require $ROOT . '/app/Core/Helpers.php';

// Sessions آمنة
session_name($GLOBALS['APP_CONFIG']['session_name']);
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// تهيئة قاعدة البيانات
\App\Core\Database::init($GLOBALS['APP_CONFIG']['db_path']);

// إذا لم يتم التثبيت بعد
try {
    \App\Core\Database::query('SELECT 1 FROM users LIMIT 1');
} catch (Throwable $e) {
    die('<div style="font-family:Tahoma;direction:rtl;text-align:center;padding:50px">
            <h1>لم يتم تثبيت النظام بعد</h1>
            <p>الرجاء تشغيل <a href="../install.php">install.php</a> أولاً.</p>
         </div>');
}

// ====================== المسارات ======================
$router = new \App\Core\Router();

// الصفحة الرئيسية
$router->get('/', 'DashboardController@index');

// المصادقة
$router->get ('/login',  'AuthController@showLogin');
$router->post('/login',  'AuthController@login');
$router->get ('/logout', 'AuthController@logout');

// لوحة التحكم
$router->get('/dashboard', 'DashboardController@index');

// الزبائن
$router->get ('/customers',            'CustomerController@index');
$router->get ('/customers/create',     'CustomerController@create');
$router->post('/customers/store',      'CustomerController@store');
$router->get ('/customers/{id}',       'CustomerController@show');
$router->get ('/customers/{id}/edit',  'CustomerController@edit');
$router->post('/customers/{id}/update','CustomerController@update');
$router->post('/customers/{id}/delete','CustomerController@destroy');
$router->get ('/api/customers/search', 'CustomerController@apiSearch');

// الديون
$router->get ('/debts',             'DebtController@index');
$router->get ('/debts/create',      'DebtController@create');
$router->post('/debts/store',       'DebtController@store');
$router->get ('/debts/{id}',        'DebtController@show');
$router->post('/debts/{id}/delete', 'DebtController@destroy');
$router->get ('/debts/{id}/contract','DebtController@contract');

// الدفعات
$router->post('/payments/store',       'PaymentController@store');
$router->get ('/payments/{id}/receipt','PaymentController@receipt');
$router->post('/payments/{id}/delete', 'PaymentController@destroy');
$router->get ('/payments',             'PaymentController@index');

// التقارير
$router->get('/reports',            'ReportController@index');
$router->get('/reports/export.csv', 'ReportController@exportCsv');

// المستخدمون (admin)
$router->get ('/users',              'UserController@index');
$router->get ('/users/create',       'UserController@create');
$router->post('/users/store',        'UserController@store');
$router->post('/users/{id}/toggle',  'UserController@toggle');
$router->post('/users/{id}/delete',  'UserController@destroy');
$router->get ('/profile',            'UserController@profile');
$router->post('/profile/password',   'UserController@updatePassword');

// عن المطور
$router->get('/about/developer', 'AboutController@developer');

// مسارات المدير العام (Super Admin)
$router->get ('/admin',                        'SuperAdminController@dashboard');
$router->get ('/admin/tenants',                'SuperAdminController@index');
$router->get ('/admin/tenants/create',         'SuperAdminController@create');
$router->post('/admin/tenants/store',          'SuperAdminController@store');
$router->get ('/admin/tenants/{id}',           'SuperAdminController@show');
$router->post('/admin/tenants/{id}/toggle',    'SuperAdminController@toggle');
$router->post('/admin/tenants/{id}/delete',    'SuperAdminController@destroy');
$router->post('/admin/tenants/{id}/reset-password', 'SuperAdminController@resetPassword');

// تشغيل الموجه
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
