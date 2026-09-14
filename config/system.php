<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Deploy/Maintenance Token
    |--------------------------------------------------------------------------
    |
    | Gates the public /system/optimize-clear route — a workaround for the
    | fact that free-tier hosts like Render don't offer SSH/one-off jobs to
    | run artisan commands after a deploy. Null disables the route entirely.
    |
    */

    'deploy_token' => env('DEPLOY_TOKEN', '879f823b8d34a6f52c643d4c22dfa93131ef2e1d3c4f5e8b'),

];
