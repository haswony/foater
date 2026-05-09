<?php
// دوال مساعدة عامة

function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_HTML5, 'UTF-8'); }

function url(string $path = ''): string {
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    if ($base === '/' || $base === '\\') $base = '';
    return $base . '/' . ltrim($path, '/');
}

function csrf_token(): string {
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function old(string $key, $default = ''): string {
    return e($_SESSION['_old'][$key] ?? $default);
}

function flash_messages(): array {
    $msgs = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $msgs;
}

function money($amount): string {
    $cfg = $GLOBALS['APP_CONFIG'];
    return number_format((float)$amount, 0, '.', ',') . ' ' . $cfg['currency'];
}

function fmt_date($date): string {
    if (!$date) return '-';
    $ts = is_numeric($date) ? (int)$date : strtotime($date);
    return $ts ? date('Y-m-d', $ts) : '-';
}

function fmt_datetime($date): string {
    if (!$date) return '-';
    $ts = is_numeric($date) ? (int)$date : strtotime($date);
    return $ts ? date('Y-m-d H:i', $ts) : '-';
}

function days_diff(string $date): int {
    $a = strtotime(date('Y-m-d'));
    $b = strtotime(date('Y-m-d', strtotime($date)));
    return (int) (($a - $b) / 86400);
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function is_admin(): bool {
    return (current_user()['role'] ?? '') === 'admin';
}

function active_if(string $segment): string {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    return strpos($uri, $segment) !== false ? 'active' : '';
}
