<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminSetting extends Model
{
    protected $fillable = [
        'admin_panel_path',
    ];

    public static function current(): self
    {
        return self::firstOrNew(['id' => 1]);
    }

    /**
     * The live "/admin" prefix, used at route-registration time in
     * routes/admin.php. Runs on every request while routes aren't cached
     * (a DB-editable prefix can't be baked into a cached route file until
     * the next `route:cache`), so failures here (e.g. mid-migration, before
     * this table exists) must fall back silently rather than break routing
     * for the entire app.
     */
    public static function panelPath(): string
    {
        try {
            return self::query()->value('admin_panel_path') ?: config('admin.default_path', 'admin');
        } catch (\Throwable) {
            return config('admin.default_path', 'admin');
        }
    }
}
