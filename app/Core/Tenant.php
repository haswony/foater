<?php
namespace App\Core;

/**
 * مساعد تنطيق العزل بين المتاجر (Multi-Tenancy)
 */
class Tenant
{
    /** يُرجع tenant_id للمستخدم الحالي (NULL لـ super_admin) */
    public static function id(): ?int
    {
        $tid = $_SESSION['user']['tenant_id'] ?? null;
        return $tid !== null ? (int)$tid : null;
    }

    public static function isSuper(): bool
    {
        return (($_SESSION['user']['role'] ?? '') === 'super_admin');
    }

    /** يفرض وجود tenant_id (يستخدم في الكنترولرز قبل أي إجراء) */
    public static function require(): int
    {
        $id = self::id();
        if ($id === null) {
            http_response_code(403);
            die('هذا القسم خاص بمستخدمي المتاجر.');
        }
        return $id;
    }
}
