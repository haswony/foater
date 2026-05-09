<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Tenant as TenantHelper;
use App\Models\Tenant;
use App\Models\User;

/**
 * متحكم خاص بالمدير العام (super_admin) لإدارة المتاجر.
 */
class SuperAdminController extends Controller
{
    private function ensureSuper(): void
    {
        $this->requireAuth();
        if (!TenantHelper::isSuper()) {
            http_response_code(403);
            die('هذا القسم خاص بالمدير العام للنظام.');
        }
    }

    /** لوحة المدير العام */
    public function dashboard(): void
    {
        $this->ensureSuper();
        $stats = Database::fetch(
            "SELECT
                (SELECT COUNT(*) FROM tenants)                                  AS tenants_total,
                (SELECT COUNT(*) FROM tenants WHERE active = 1)                 AS tenants_active,
                (SELECT COUNT(*) FROM users WHERE role != 'super_admin')        AS users_total,
                (SELECT COUNT(*) FROM customers)                                AS customers_total,
                (SELECT COUNT(*) FROM debts)                                    AS debts_total,
                (SELECT COALESCE(SUM(amount),0) FROM debts WHERE status!='cancelled') AS debts_amount,
                (SELECT COALESCE(SUM(amount),0) FROM payments)                  AS payments_amount"
        );
        $tenants = Tenant::listWithStats();
        $this->view('admin/dashboard', [
            'title'   => 'لوحة المدير العام',
            'stats'   => $stats,
            'tenants' => $tenants,
        ]);
    }

    /** قائمة المتاجر */
    public function index(): void
    {
        $this->ensureSuper();
        $tenants = Tenant::listWithStats();
        $this->view('admin/tenants/index', [
            'title' => 'إدارة المتاجر',
            'tenants' => $tenants,
        ]);
    }

    public function create(): void
    {
        $this->ensureSuper();
        $this->view('admin/tenants/form', ['title' => 'إضافة متجر جديد']);
    }

    /** إنشاء متجر + مستخدم admin له */
    public function store(): void
    {
        $this->ensureSuper();
        $this->validateCsrf();

        $name        = $this->input('name', '');
        $phone       = $this->input('phone', '');
        $address     = $this->input('address', '');
        $username    = $this->input('username', '');
        $password    = $this->input('password', '');
        $admin_name  = $this->input('admin_name', '');

        if ($name === '' || $username === '' || strlen($password) < 6 || $admin_name === '') {
            $this->flash('danger', 'الرجاء تعبئة كل الحقول، كلمة المرور 6 أحرف على الأقل');
            $this->redirect('/admin/tenants/create');
        }
        if (Database::fetch('SELECT 1 FROM users WHERE username = ?', [$username])) {
            $this->flash('danger', 'اسم المستخدم موجود مسبقاً، اختر اسماً آخر');
            $this->redirect('/admin/tenants/create');
        }

        Database::pdo()->beginTransaction();
        try {
            Database::execute(
                'INSERT INTO tenants (name, phone, address) VALUES (?,?,?)',
                [$name, $phone, $address]
            );
            $tenantId = Database::lastInsertId();

            // إنشاء مستخدم admin للمتجر
            Database::execute(
                'INSERT INTO users (tenant_id, username, password, full_name, role) VALUES (?,?,?,?,?)',
                [$tenantId, $username, password_hash($password, PASSWORD_BCRYPT), $admin_name, 'admin']
            );

            // إعدادات افتراضية
            $defaults = [
                'shop_name'            => $name,
                'shop_phone'           => $phone,
                'shop_address'         => $address,
                'reminder_days_before' => '3',
            ];
            foreach ($defaults as $k => $v) {
                Database::execute(
                    'INSERT INTO settings (tenant_id, key, value) VALUES (?,?,?)',
                    [$tenantId, $k, $v]
                );
            }

            Database::pdo()->commit();
            $this->flash('success', "تم إنشاء المتجر \"$name\" بنجاح");
            $this->redirect('/admin/tenants');
        } catch (\Throwable $e) {
            Database::pdo()->rollBack();
            $this->flash('danger', 'فشل الإنشاء: ' . $e->getMessage());
            $this->redirect('/admin/tenants/create');
        }
    }

    /** عرض تفاصيل المتجر */
    public function show(array $params): void
    {
        $this->ensureSuper();
        $id = (int)$params['id'];
        $tenant = Tenant::find($id);
        if (!$tenant) { $this->redirect('/admin/tenants'); }

        $users = Database::fetchAll(
            'SELECT id,username,full_name,role,active,last_login FROM users WHERE tenant_id = ? ORDER BY id',
            [$id]
        );
        $stats = Database::fetch(
            "SELECT
                (SELECT COUNT(*) FROM customers WHERE tenant_id = ?) AS customers,
                (SELECT COUNT(*) FROM debts WHERE tenant_id = ?)     AS debts,
                (SELECT COALESCE(SUM(amount),0) FROM debts    WHERE tenant_id = ? AND status!='cancelled') AS total_debt,
                (SELECT COALESCE(SUM(amount),0) FROM payments WHERE tenant_id = ?) AS total_paid",
            [$id, $id, $id, $id]
        );

        $this->view('admin/tenants/show', [
            'title' => 'متجر: ' . $tenant['name'],
            'tenant' => $tenant,
            'users' => $users,
            'stats' => $stats,
        ]);
    }

    public function toggle(array $params): void
    {
        $this->ensureSuper();
        $this->validateCsrf();
        $t = Tenant::find((int)$params['id']);
        if ($t) {
            Database::execute('UPDATE tenants SET active = ? WHERE id = ?', [$t['active'] ? 0 : 1, $t['id']]);
            $this->flash('success', 'تم تحديث حالة المتجر');
        }
        $this->redirect('/admin/tenants');
    }

    public function destroy(array $params): void
    {
        $this->ensureSuper();
        $this->validateCsrf();
        $id = (int)$params['id'];
        // بسبب CASCADE، سيتم حذف كل البيانات التابعة تلقائياً
        Database::execute('DELETE FROM tenants WHERE id = ?', [$id]);
        $this->flash('success', 'تم حذف المتجر وكل بياناته');
        $this->redirect('/admin/tenants');
    }

    /** إعادة تعيين كلمة مرور admin المتجر */
    public function resetPassword(array $params): void
    {
        $this->ensureSuper();
        $this->validateCsrf();
        $id = (int)$params['id'];
        $newPass = $this->input('password', '');
        if (strlen($newPass) < 6) {
            $this->flash('danger', 'كلمة المرور 6 أحرف على الأقل');
            $this->redirect('/admin/tenants/' . $id);
        }
        // أول admin في المتجر
        $admin = Database::fetch(
            "SELECT id FROM users WHERE tenant_id = ? AND role = 'admin' ORDER BY id LIMIT 1",
            [$id]
        );
        if ($admin) {
            Database::execute(
                'UPDATE users SET password = ? WHERE id = ?',
                [password_hash($newPass, PASSWORD_BCRYPT), $admin['id']]
            );
            $this->flash('success', 'تم تحديث كلمة مرور المدير');
        }
        $this->redirect('/admin/tenants/' . $id);
    }
}
