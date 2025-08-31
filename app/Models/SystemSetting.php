<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    protected $casts = [
        'value' => 'string',
    ];

    public static function getValue($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function setValue($key, $value, $type = 'string', $description = null)
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description,
            ]
        );
    }

    public static function getBooleanValue($key, $default = false)
    {
        $value = static::getValue($key);
        return $value ? filter_var($value, FILTER_VALIDATE_BOOLEAN) : $default;
    }

    public static function getIntegerValue($key, $default = 0)
    {
        $value = static::getValue($key);
        return $value ? intval($value) : $default;
    }

    public static function getArrayValue($key, $default = [])
    {
        $value = static::getValue($key);
        return $value ? json_decode($value, true) : $default;
    }
}