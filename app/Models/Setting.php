<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'description'];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        return \Illuminate\Support\Facades\Cache::rememberForever("setting.{$key}", function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            return match ($setting->type) {
                'integer' => (int) $setting->value,
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'json' => json_decode($setting->value, true),
                default => $setting->value,
            };
        });
    }

    public static function setValue(string $key, mixed $value, string $type = 'string', string $description = null): void
    {
        $setting = self::firstOrNew(['key' => $key]);
        $setting->value = is_array($value) ? json_encode($value) : (string) $value;
        $setting->type = $type;
        if ($description !== null) {
            $setting->description = $description;
        }
        $setting->save();

        \Illuminate\Support\Facades\Cache::forget("setting.{$key}");
    }

    protected static function booted()
    {
        static::saved(function ($setting) {
            \Illuminate\Support\Facades\Cache::forget("setting.{$setting->key}");
        });
        
        static::deleted(function ($setting) {
            \Illuminate\Support\Facades\Cache::forget("setting.{$setting->key}");
        });
    }
}
