<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Tenant;
use App\Models\User;

class UserController extends Controller
{
    public function index(): void
    {
        $this->requireAuth('admin');
        $users = User::all();
        $this->view('users/index', ['title' => 'المستخدمون', 'users' => $users]);
    }

    public function create(): void
    {
        $this->requireAuth('admin');
        $this->view('users/form', ['title' => 'إضافة مستخدم']);
    }

    public function store(): void
    {
        $this->requireAuth('admin');
        $this->validateCsrf();
        $username = $this->input('username', '');
        $password = $this->input('password', '');
        $full_name= $this->input('full_name', '');
        $role     = $this->input('role', 'employee');

        if ($username === '' || strlen($password) < 6 || $full_name === '') {
            $this->flash('danger', 'الرجاء تعبئة الحقول، كلمة المرور 6 أحرف على الأقل');
            $this->redirect('/users/create');
        }
        if (Database::fetch('SELECT 1 FROM users WHERE username=?', [$username])) {
            $this->flash('danger', 'اسم المستخدم موجود مسبقاً');
            $this->redirect('/users/create');
        }
        User::create([
            'tenant_id'=> Tenant::id(),
            'username' => $username,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'full_name'=> $full_name,
            'role'     => in_array($role, ['admin','employee']) ? $role : 'employee',
        ]);
        $this->flash('success', 'تم إضافة المستخدم');
        $this->redirect('/users');
    }

    public function toggle(array $params): void
    {
        $this->requireAuth('admin');
        $this->validateCsrf();
        $u = User::find((int)$params['id']);
        if ($u && $u['id'] != current_user()['id']) {
            User::update($u['id'], ['active' => $u['active'] ? 0 : 1]);
            $this->flash('success', 'تم تحديث الحالة');
        }
        $this->redirect('/users');
    }

    public function destroy(array $params): void
    {
        $this->requireAuth('admin');
        $this->validateCsrf();
        if ((int)$params['id'] !== (int)(current_user()['id'] ?? 0)) {
            User::delete((int)$params['id']);
            $this->flash('success', 'تم حذف المستخدم');
        } else {
            $this->flash('danger', 'لا يمكنك حذف نفسك');
        }
        $this->redirect('/users');
    }

    public function profile(): void
    {
        $this->requireAuth();
        $this->view('users/profile', ['title' => 'الملف الشخصي']);
    }

    public function updatePassword(): void
    {
        $this->requireAuth();
        $this->validateCsrf();
        $current = $this->input('current', '');
        $new     = $this->input('new', '');
        $u = User::find((int)current_user()['id']);
        if (!$u || !password_verify($current, $u['password'])) {
            $this->flash('danger', 'كلمة المرور الحالية غير صحيحة');
            $this->redirect('/profile');
        }
        if (strlen($new) < 6) {
            $this->flash('danger', 'كلمة المرور الجديدة 6 أحرف على الأقل');
            $this->redirect('/profile');
        }
        User::update($u['id'], ['password' => password_hash($new, PASSWORD_BCRYPT)]);
        $this->flash('success', 'تم تغيير كلمة المرور');
        $this->redirect('/profile');
    }
}
