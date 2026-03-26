<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value', 'description'];

    /**
     * Get a setting value by key.
     */
    public static function getValue(string $key, $default = null): ?string
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function setValue(string $key, string $value, ?string $description = null): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'description' => $description]
        );
    }

    /**
     * Get the elevated registration code.
     */
    public static function getElevatedCode(): ?string
    {
        return static::getValue('elevated_registration_code', 'CREAM2025');
    }

    /**
     * Set the elevated registration code.
     */
    public static function setElevatedCode(string $code): void
    {
        static::setValue(
            'elevated_registration_code',
            $code,
            'Code required for elevated role registration (Admin, Staff, Adviser, Priest)'
        );
    }
}
