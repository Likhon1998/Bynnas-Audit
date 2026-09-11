<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Feature flags
    |--------------------------------------------------------------------------
    |
    | Flip these on/off without removing code. Set MAP_ENABLED=true in .env
    | when you want the map live again, then run: php artisan config:cache
    |
    */

    'map' => (bool) env('MAP_ENABLED', false),

];
