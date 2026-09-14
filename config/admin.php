<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Admin Panel Path
    |--------------------------------------------------------------------------
    |
    | Fallback used when no admin_settings row (or an empty value) exists —
    | e.g. right after a fresh migrate, before a Superadmin has ever set a
    | custom path via the admin UI.
    |
    */

    'default_path' => env('ADMIN_PANEL_PATH', 'admin'),

];
