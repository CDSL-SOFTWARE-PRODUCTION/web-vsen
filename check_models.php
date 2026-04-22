<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$models = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__.'/app/Models')
);
foreach ($iterator as $file) {
    if ($file->isDir()) continue;
    $path = $file->getPathname();
    $class = 'App\\Models\\' . str_replace('/', '\\', substr($path, strlen(__DIR__.'/app/Models/'), -4));
    if (class_exists($class)) {
        try {
            $reflection = new ReflectionClass($class);
            if (!$reflection->isInstantiable() || !$reflection->isSubclassOf(Illuminate\Database\Eloquent\Model::class)) continue;
            
            $model = new $class;
            $table = $model->getTable();
            if (!Illuminate\Support\Facades\Schema::hasTable($table)) {
                echo "Model $class has no table '$table'\n";
            }
        } catch (Exception $e) {}
    }
}
