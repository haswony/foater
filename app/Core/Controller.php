<?php
namespace App\Core;

class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: $view");
        }
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        if ($layout === null || $layout === '') {
            echo $content;
            return;
        }
        $layoutFile = __DIR__ . '/../Views/layouts/' . $layout . '.php';
        include $layoutFile;
    }

    protected function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function redirect(string $path): void
    {
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        if ($base === '/' || $base === '\\') $base = '';
        header('Location: ' . $base . $path);
        exit;
    }

    protected function input(string $key, $default = null)
    {
        $val = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($val) ? trim($val) : $val;
    }

    protected function validateCsrf(): void
    {
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals($_SESSION['_csrf'] ?? '', (string)$token)) {
            http_response_code(419);
            die('انتهت صلاحية الجلسة، أعد تحميل الصفحة');
        }
    }

    protected function requireAuth(?string $role = null): void
    {
        if (empty($_SESSION['user'])) {
            $this->redirect('/login');
        }
        if ($role !== null && ($_SESSION['user']['role'] ?? '') !== $role) {
            http_response_code(403);
            die('ليست لديك صلاحية للوصول');
        }
    }

    protected function flash(string $type, string $msg): void
    {
        $_SESSION['_flash'][] = ['type' => $type, 'msg' => $msg];
    }
}
