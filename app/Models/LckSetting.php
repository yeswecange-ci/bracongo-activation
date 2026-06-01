<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LckSetting extends Model
{
    protected $table = 'lck_settings';

    // 'key' est un mot réservé MySQL — on bypasse l'ORM Eloquent
    // et on utilise DB::table() pour éviter tout conflit de quoting.

    public static function get(string $key, string $default = ''): string
    {
        $row = DB::table('lck_settings')->where('key', $key)->first();
        return $row->value ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        DB::table('lck_settings')->updateOrInsert(
            ['key' => $key],
            ['value' => $value, 'updated_at' => now()]
        );
    }

    // Tableau associatif ['key' => 'value'] pour toutes les settings
    public static function asMap(): array
    {
        return DB::table('lck_settings')->pluck('value', 'key')->toArray();
    }
}
