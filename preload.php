<?php
/**
 * OPcache Preloading Script
 * This script preloads frequently used classes into memory for better performance
 */

// Only run in production
if (php_sapi_name() !== 'cli') {
    return;
}

// Preload Laravel core files
$preloadFiles = [
    __DIR__ . '/vendor/autoload.php',
];

foreach ($preloadFiles as $file) {
    if (file_exists($file)) {
        opcache_compile_file($file);
    }
}

// Preload Laravel framework classes
$laravelClasses = [
    \Illuminate\Foundation\Application::class,
    \Illuminate\Http\Request::class,
    \Illuminate\Http\Response::class,
    \Illuminate\Routing\Router::class,
    \Illuminate\Database\Eloquent\Model::class,
    \Illuminate\Support\Facades\Facade::class,
    \Illuminate\Support\Collection::class,
];

foreach ($laravelClasses as $class) {
    if (class_exists($class)) {
        opcache_compile_file((new ReflectionClass($class))->getFileName());
    }
}

// Preload application models
$appModels = [
    \App\Models\TransactionHeader::class,
    \App\Models\TransactionBody::class,
];

foreach ($appModels as $model) {
    if (class_exists($model)) {
        opcache_compile_file((new ReflectionClass($model))->getFileName());
    }
}
