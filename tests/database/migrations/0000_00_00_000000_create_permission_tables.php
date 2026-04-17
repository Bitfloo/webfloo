<?php

// Wraps spatie/laravel-permission stub so the migrator (which requires .php)
// can create the permissions tables in the SQLite :memory: test database.
return require __DIR__.'/../../../vendor/spatie/laravel-permission/database/migrations/create_permission_tables.php.stub';
