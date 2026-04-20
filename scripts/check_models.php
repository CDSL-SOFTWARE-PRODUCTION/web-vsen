<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Finder\Finder;

$modelsPath = app_path('Models');
if (! is_dir($modelsPath)) {
    echo "No Models directory found.\n";
    exit;
}

$finder = new Finder;
$finder->files()->name('*.php')->in($modelsPath);

$models = [];
$tablesByModels = [];
$modelDetails = [];

// Tables to ignore for the orphan check
$ignoreTables = [
    'migrations', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks',
    'jobs', 'job_batches', 'failed_jobs', 'sqlite_sequence',
];

foreach ($finder as $file) {
    // Generate class name based on file path
    $namespace = 'App\\Models\\';
    $relativePath = $file->getRelativePath();
    $classPath = $relativePath ? str_replace('/', '\\', $relativePath).'\\' : '';
    $className = $namespace.$classPath.$file->getFilenameWithoutExtension();

    if (class_exists($className) && is_subclass_of($className, 'Illuminate\Database\Eloquent\Model')) {
        $reflection = new ReflectionClass($className);

        // Ignore abstract classes
        if ($reflection->isAbstract()) {
            continue;
        }

        try {
            $instance = new $className;
            $table = $instance->getTable();
            $models[] = $className;
            $tablesByModels[] = $table;

            $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
            $relations = [];
            foreach ($methods as $method) {
                if ($method->class !== $className) {
                    continue; // Skip inherited methods
                }

                if ($method->getNumberOfParameters() == 0) {
                    try {
                        $returnType = $method->getReturnType();
                        if ($returnType) {
                            $typeName = $returnType->getName();
                            if (str_contains($typeName, 'Relations') || str_contains($typeName, 'Illuminate\Database\Eloquent\Relations\\')) {
                                $relations[] = $method->getName();
                            }
                        } else {
                            // Heuristic approach if no return type defined
                            $docComment = $method->getDocComment();
                            if ($docComment && str_contains($docComment, '@return \Illuminate\Database\Eloquent\Relations')) {
                                $relations[] = $method->getName();
                            }
                        }
                    } catch (Throwable $e) {
                        // ignore
                    }
                }
            }

            $modelDetails[$className] = [
                'table' => $table,
                'relations' => $relations,
                'table_exists' => Schema::hasTable($table),
            ];
        } catch (Throwable $e) {
            echo "Error loading model $className: ".$e->getMessage()."\n";
        }
    }
}

// 1. Missing Tables (Model exists, but table is missing)
$missingTables = [];
foreach ($modelDetails as $model => $detail) {
    if (! $detail['table_exists']) {
        $missingTables[] = "$model (Expected table: {$detail['table']})";
    }
}

// 2. Orphan Tables (Table exists, but no model corresponds to it)
$dbTables = array_map(function ($table) {
    if (str_starts_with($table, 'public.')) {
        return substr($table, 7);
    }

    return $table;
}, Schema::getTableListing());
$orphanTables = array_diff($dbTables, $tablesByModels, $ignoreTables);

// 3. Isolated Models (No relations defined)
$isolatedModels = [];
foreach ($modelDetails as $model => $detail) {
    // Excluding some obvious standalone like User (if they somehow have no relations)
    if (empty($detail['relations'])) {
        $isolatedModels[] = $model;
    }
}

echo "========================================\n";
echo "📊 DATA MODEL HEALTH CHECK REPORT \n";
echo "========================================\n\n";

echo 'Total Models found: '.count($models)."\n";
echo 'Total Tables found (excluding system): '.(count($dbTables) - count($ignoreTables))."\n\n";

echo "🚨 1. Models without correspondings Tables:\n";
if (empty($missingTables)) {
    echo "   ✅ PERFECT: All models have corresponding database tables.\n";
} else {
    foreach ($missingTables as $m) {
        echo "   ❌ $m\n";
    }
}
echo "\n";

echo "🚨 2. Tables without corresponding Models ('thừa thãi'):\n";
if (empty($orphanTables)) {
    echo "   ✅ PERFECT: No unused/orphan tables found.\n";
} else {
    foreach ($orphanTables as $t) {
        // Exclude pivot tables heuristically
        if (str_contains($t, '_') && ! str_ends_with($t, 's')) {
            echo "   ⚠️ $t (possibly a pivot or relation table)\n";
        } else {
            echo "   ❌ $t\n";
        }
    }
}
echo "\n";

echo "🚨 3. Models without defined Relationships ('rời rạc / thiếu liên kết'):\n";
if (empty($isolatedModels)) {
    echo "   ✅ PERFECT: All models have defined relationships.\n";
} else {
    foreach ($isolatedModels as $m) {
        echo "   ⚠️ $m (May not be an issue if standalone, but worth checking)\n";
    }
}
echo "\n";

echo "========================================\n";
echo "💡 To view a specific model's details, run: php artisan model:show ModelName\n";
