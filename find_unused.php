<?php
$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/app'));
$files = [];
foreach ($dir as $f) {
    if ($f->getExtension() === 'php') {
        $files[] = $f->getPathname();
    }
}

$allContent = '';
$searchDirs = [__DIR__.'/app', __DIR__.'/routes', __DIR__.'/resources', __DIR__.'/config'];
foreach ($searchDirs as $sd) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sd));
    foreach ($it as $f) {
        if ($f->isFile() && in_array($f->getExtension(), ['php', 'json', 'yml', 'yaml', 'blade.php'])) {
            $allContent .= file_get_contents($f->getPathname()) . "\n";
        }
    }
}

echo "Looking for unreferenced classes...\n";
foreach ($files as $file) {
    $content = file_get_contents($file);
    if (preg_match('/class\s+([A-Za-z0-9_]+)/', $content, $matches)) {
        $className = $matches[1];
        // Simple search: does this class name appear elsewhere?
        // Note: this is very naive.
        $count = substr_count($allContent, $className);
        if ($count <= 1) { // 1 for itself
            // Wait, this counts itself. Let's make sure it's really not referenced.
            echo str_replace(__DIR__.'/', '', $file) . "\n";
        }
    }
}
