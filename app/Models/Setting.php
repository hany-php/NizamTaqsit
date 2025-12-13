<?php
namespace App\Models;

/**
 * نموذج الإعداد
 */
class Setting extends Model
{
    protected string $table = 'settings';
    protected array $fillable = ['setting_key', 'setting_value', 'setting_group'];
    
    /**
     * جلب قيمة إعداد
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $row = $this->findWhere('setting_key', $key);
        return $row ? $row['setting_value'] : $default;
    }
    
    /**
     * تعيين قيمة إعداد
     */
    public function set(string $key, mixed $value, string $group = 'general'): bool
    {
        $existing = $this->findWhere('setting_key', $key);
        
        if ($existing) {
            return $this->db->update($this->table, [
                'setting_value' => $value,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'setting_key = :key', ['key' => $key]) > 0;
        }
        
        return $this->create([
            'setting_key' => $key,
            'setting_value' => $value,
            'setting_group' => $group
        ]) > 0;
    }
    
    /**
     * جلب إعدادات مجموعة
     */
    public function getGroup(string $group): array
    {
        $rows = $this->where('setting_group', $group);
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }
    
    /**
     * جلب كل الإعدادات
     */
    public function getAll(): array
    {
        $rows = $this->all('setting_group, setting_key');
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }
    
    /**
     * تحديث مجموعة إعدادات
     */
    public function updateMultiple(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->set($key, $value);
        }
    }
}
