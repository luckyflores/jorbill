<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';
    public    $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'value', 'description'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = Cache::remember("setting.{$key}", 300, fn () => static::query()->where('key', $key)->value('value'));
        if ($value === null) return $default;
        $decoded = json_decode($value, true);
        return $decoded ?? $value;
    }

    public static function put(string $key, mixed $value, ?string $description = null): void
    {
        $encoded = is_scalar($value) ? (string) $value : json_encode($value);
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $encoded] + ($description ? ['description' => $description] : []),
        );
        Cache::forget("setting.{$key}");
    }
}
