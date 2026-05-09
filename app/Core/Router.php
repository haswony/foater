<?php
namespace App\Core;

class Router
{
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $path, $handler): void { $this->routes['GET'][$this->normalize($path)] = $handler; }
    public function post(string $path, $handler): void { $this->routes['POST'][$this->normalize($path)] = $handler; }

    private function normalize(string $p): string
    {
        $p = '/' . trim($p, '/');
        return $p === '/' ? '/' : rtrim($p, '/');
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        // إزالة بادئة المجلد إن وجدت (لو وضعنا الموقع داخل مجلد)
        $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        if ($scriptDir && strpos($path, $scriptDir) === 0) {
            $path = substr($path, strlen($scriptDir));
        }
        $path = $this->normalize($path);

        $routes = $this->routes[$method] ?? [];

        // مطابقة مباشرة
        if (isset($routes[$path])) {
            $this->call($routes[$path], []);
            return;
        }

        // مطابقة مع باراميترات {id}
        foreach ($routes as $route => $handler) {
            $pattern = '#^' . preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', $route) . '$#';
            if (preg_match($pattern, $path, $m)) {
                $params = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->call($handler, $params);
                return;
            }
        }

        http_response_code(404);
        echo '<div style="font-family:Tahoma;padding:40px;text-align:center;direction:rtl">
                <h1>404 - الصفحة غير موجودة</h1>
                <a href="' . htmlspecialchars($scriptDir ?: '/') . '">العودة للرئيسية</a>
              </div>';
    }

    private function call($handler, array $params): void
    {
        if (is_callable($handler)) { $handler($params); return; }
        if (is_string($handler) && strpos($handler, '@') !== false) {
            [$class, $method] = explode('@', $handler);
            $fqcn = "App\\Controllers\\$class";
            (new $fqcn())->$method($params);
            return;
        }
        throw new \RuntimeException('Invalid handler');
    }
}
