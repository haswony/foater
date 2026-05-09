<?php
namespace App\Models;

use App\Core\Database;
use App\Core\Tenant;

abstract class Model
{
    protected static string $table = '';
    /** هل يحتوي الجدول على عمود tenant_id؟ */
    protected static bool $tenantScoped = true;

    /** يُرجع شرط tenant_id للاستعلامات الحالية، أو "1=1" لـ super_admin / الجداول غير المعزولة */
    protected static function scopeWhere(string $alias = ''): array
    {
        if (!static::$tenantScoped || Tenant::isSuper()) {
            return ['1=1', []];
        }
        $col = $alias ? "$alias.tenant_id" : 'tenant_id';
        return ["$col = ?", [Tenant::id()]];
    }

    public static function all(string $orderBy = 'id DESC'): array
    {
        [$where, $params] = self::scopeWhere();
        return Database::fetchAll('SELECT * FROM ' . static::$table . " WHERE $where ORDER BY $orderBy", $params);
    }

    public static function find($id): ?array
    {
        [$where, $params] = self::scopeWhere();
        return Database::fetch(
            'SELECT * FROM ' . static::$table . " WHERE id = ? AND $where",
            array_merge([$id], $params)
        );
    }

    public static function create(array $data): int
    {
        // إضافة tenant_id تلقائياً إن لم يكن موجوداً
        if (static::$tenantScoped && !array_key_exists('tenant_id', $data)) {
            $tid = Tenant::id();
            if ($tid !== null) $data['tenant_id'] = $tid;
        }
        $cols = array_keys($data);
        $placeholders = array_fill(0, count($cols), '?');
        $sql = 'INSERT INTO ' . static::$table . ' (' . implode(',', $cols) . ') VALUES (' . implode(',', $placeholders) . ')';
        Database::execute($sql, array_values($data));
        return Database::lastInsertId();
    }

    public static function update($id, array $data): int
    {
        unset($data['tenant_id']); // لا يسمح بتغيير tenant_id
        [$where, $params] = self::scopeWhere();
        $sets = implode(',', array_map(fn($c) => "$c = ?", array_keys($data)));
        $sql = 'UPDATE ' . static::$table . " SET $sets WHERE id = ? AND $where";
        return Database::execute($sql, array_merge(array_values($data), [$id], $params));
    }

    public static function delete($id): int
    {
        [$where, $params] = self::scopeWhere();
        return Database::execute(
            'DELETE FROM ' . static::$table . " WHERE id = ? AND $where",
            array_merge([$id], $params)
        );
    }
}
