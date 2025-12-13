<?php
namespace App\Models;

/**
 * نموذج المستخدم
 */
class User extends Model
{
    protected string $table = 'users';
    protected array $fillable = [
        'username', 'password_hash', 'full_name', 'phone', 'email', 'role', 'is_active', 'menu_config'
    ];
    
    /**
     * جلب مستخدم بالاسم
     */
    public function findByUsername(string $username): ?array
    {
        return $this->findWhere('username', $username);
    }
    
    /**
     * التحقق من كلمة المرور
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
    
    /**
     * تشفير كلمة المرور
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * تحديث آخر دخول
     */
    public function updateLastLogin(int $id): void
    {
        $this->db->update($this->table, [
            'last_login' => date('Y-m-d H:i:s')
        ], 'id = :id', ['id' => $id]);
    }
    
    /**
     * جلب المستخدمين النشطين
     */
    public function getActive(): array
    {
        return $this->where('is_active', 1);
    }
    
    /**
     * حفظ إعدادات القائمة للمستخدم
     */
    public function saveMenuConfig(int $userId, array $config): bool
    {
        return $this->db->update(
            $this->table,
            ['menu_config' => json_encode($config, JSON_UNESCAPED_UNICODE)],
            'id = :id',
            ['id' => $userId]
        ) > 0;
    }
    
    /**
     * جلب إعدادات القائمة للمستخدم
     */
    public function getMenuConfig(int $userId): ?array
    {
        $user = $this->find($userId);
        if ($user && !empty($user['menu_config'])) {
            return json_decode($user['menu_config'], true);
        }
        return null;
    }
    
    /**
     * حفظ إعدادات المظهر للمستخدم
     */
    public function saveThemeConfig(int $userId, array $config): bool
    {
        return $this->db->update(
            $this->table,
            ['theme_config' => json_encode($config, JSON_UNESCAPED_UNICODE)],
            'id = :id',
            ['id' => $userId]
        ) > 0;
    }
    
    /**
     * جلب إعدادات المظهر للمستخدم
     */
    public function getThemeConfig(int $userId): ?array
    {
        $user = $this->find($userId);
        if ($user && !empty($user['theme_config'])) {
            return json_decode($user['theme_config'], true);
        }
        return null;
    }
}
