<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LckSetting extends Model
{
    protected $table      = 'lck_settings';
    protected $primaryKey = 'key';
    public    $incrementing = false;
    protected $keyType    = 'string';

    protected $fillable = ['key', 'value'];

    // Récupère une valeur avec fallback
    public static function get(string $key, string $default = ''): string
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    // Met à jour ou crée une clé
    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    // Retourne toutes les settings sous forme de tableau associatif ['key' => 'value']
    public static function asMap(): array
    {
        return static::all()->pluck('value', 'key')->toArray();
    }
}
