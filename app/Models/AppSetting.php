<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    protected $table = 'app_settings';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'label',
        'group',
        'value',
        'type',
    ];

    protected $casts = [
        'value' => 'string',
    ];

    public static function get(string $key, string $default = ''): string
    {
        return Cache::remember("appsetting:{$key}", 3600, function () use ($key, $default) {
            $setting = static::find($key);
            return $setting ? (string) $setting->value : $default;
        });
    }

    public static function set(string $key, string $value): void
    {
        static::where('key', $key)->update(['value' => $value]);
        Cache::forget("appsetting:{$key}");
    }
}
