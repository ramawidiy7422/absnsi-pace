<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Pengaturan extends Model
{
    protected $table = 'pengaturan';
    protected $fillable = ['key','value','label','group'];

    public static function get(string $key, $default = null)
    {
        return Cache::remember("pengaturan_{$key}", 3600, function() use ($key, $default) {
            $row = static::where('key', $key)->first();
            return $row ? $row->value : $default;
        });
    }

    public static function set(string $key, $value, string $label = ''): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'label' => $label ?: $key]
        );
        Cache::forget("pengaturan_{$key}");
    }
}
