<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) { $this->redirect('/dashboard'); }
        $this->view('auth/login', ['title' => 'تسجيل الدخول'], 'auth');
    }

    public function login(): void
    {
        $this->validateCsrf();
        $username = $this->input('username', '');
        $password = $this->input('password', '');

        if ($username === '' || $password === '') {
            $this->flash('danger', 'الرجاء تعبئة جميع الحقول');
            $this->redirect('/login');
        }
        if (!Auth::attempt($username, $password)) {
            $this->flash('danger', 'بيانات الدخول غير صحيحة أو المتجر معطّل');
            $this->redirect('/login');
        }
        $this->flash('success', 'مرحباً بك ' . ($_SESSION['user']['full_name'] ?? ''));
        if (($_SESSION['user']['role'] ?? '') === 'super_admin') {
            $this->redirect('/admin/tenants');
        }
        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/login');
    }
}
