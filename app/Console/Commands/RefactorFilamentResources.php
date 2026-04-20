<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RefactorFilamentResources extends Command
{
    protected $signature = 'ops:refactor-resources';
    protected $description = 'Reorganize Filament Ops Resources into domain folders based on opsNavigationClusterKey';

    public function handle()
    {
        $resourcesPath = app_path('Filament/Ops/Resources');
        
        if (!File::exists($resourcesPath)) {
            $this->error("Path does not exist: $resourcesPath");
            return;
        }

        $files = File::files($resourcesPath);
        $moves = [];

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php' || !str_ends_with($file->getFilename(), 'Resource.php')) {
                continue;
            }

            $content = File::get($file->getPathname());
            $resourceName = $file->getFilenameWithoutExtension(); // e.g. OrderResource
            
            // Try to extract opsNavigationClusterKey
            if (preg_match('/opsNavigationClusterKey\(\)\s*:\s*string\s*\{\s*return\s+[\'"]([a-zA-Z_]+)[\'"]\s*;/s', $content, $matches)) {
                $clusterKey = $matches[1];
                $folderName = Str::studly($clusterKey); // e.g. 'master_data' -> 'MasterData'
                
                $moves[] = [
                    'resourceName' => $resourceName,
                    'folderName'   => $folderName,
                    'oldFilePath'  => $file->getPathname(),
                    'newFilePath'  => $resourcesPath . '/' . $folderName . '/' . $file->getFilename(),
                    'oldDirPath'   => $resourcesPath . '/' . $resourceName,
                    'newDirPath'   => $resourcesPath . '/' . $folderName . '/' . $resourceName,
                ];
            } else {
                $this->warn("Skipping $resourceName (No opsNavigationClusterKey found)");
            }
        }

        if (empty($moves)) {
            $this->info("No resources to move.");
            return;
        }

        $this->info("Found " . count($moves) . " resources to reorganize.");

        // 1. Move files and directories
        foreach ($moves as $move) {
            $targetDir = app_path('Filament/Ops/Resources/' . $move['folderName']);
            if (!File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }

            $this->info("Moving {$move['resourceName']} to {$move['folderName']}...");

            // Move main Resource class
            File::move($move['oldFilePath'], $move['newFilePath']);

            // Move associated directory (Pages, RelationManagers)
            if (File::exists($move['oldDirPath']) && File::isDirectory($move['oldDirPath'])) {
                File::moveDirectory($move['oldDirPath'], $move['newDirPath'], true);
            }
        }

        // 2. Perform global namespace and usage refactoring
        $allPhpFiles = File::allFiles(app_path());
        // Also include resource views if they contain full class references
        $allViewFiles = File::allFiles(resource_path('views'));
        $filesToProcess = array_merge($allPhpFiles, $allViewFiles);

        $this->info("Updating namespaces and imports in all PHP/Blade files...");

        foreach ($filesToProcess as $file) {
            if ($file->getExtension() !== 'php') {
                continue; // Process both .php and .blade.php
            }

            $filePath = $file->getPathname();
            $originalContent = File::get($filePath);
            $content = $originalContent;

            foreach ($moves as $move) {
                $resourceName = $move['resourceName'];
                $folderName = $move['folderName'];

                $oldNamespaceBase = "App\\Filament\\Ops\\Resources";
                $newNamespaceBase = "App\\Filament\\Ops\\Resources\\{$folderName}";
                
                $oldNamespaceRes = "App\\Filament\\Ops\\Resources\\{$resourceName}";
                $newNamespaceRes = "App\\Filament\\Ops\\Resources\\{$folderName}\\{$resourceName}";

                // Replace namespaces IN the moved resource class itself:
                // namespace App\Filament\Ops\Resources; -> namespace App\Filament\Ops\Resources\Demand;
                if ($filePath === $move['newFilePath']) {
                    $content = preg_replace(
                        "/^namespace\s+App\\\\Filament\\\\Ops\\\\Resources\s*;/m",
                        "namespace App\\Filament\\Ops\\Resources\\{$folderName};",
                        $content
                    );
                }

                // Replace namespaces IN the sub-files (Pages, RelationManagers)
                // e.g. namespace App\Filament\Ops\Resources\Demand\OrderResource\Pages; -> ...\Demand\OrderResource\Pages;
                if (str_starts_with($filePath, $move['newDirPath'])) {
                    $content = preg_replace(
                        "/^namespace\s+App\\\\Filament\\\\Ops\\\\Resources\\\\{$resourceName}(.*?);/m",
                        "namespace {$newNamespaceRes}$1;",
                        $content
                    );

                    // They also use `use App\Filament\Ops\Resources\Demand\OrderResource;` inside Pages
                    // We must replace it too. Below generic replacements will catch it.
                }

                // Generic replacement for imports and string references
                // use App\Filament\Ops\Resources\Demand\OrderResource; -> use App\Filament\Ops\Resources\Demand\OrderResource;
                $content = str_replace(
                    "use {$oldNamespaceBase}\\{$resourceName};", 
                    "use {$newNamespaceBase}\\{$resourceName};", 
                    $content
                );

                // Re-aliased uses: use App\Filament\Ops\Resources\Demand\OrderResource\Pages; -> ...\Demand\...
                $content = str_replace(
                    "use {$oldNamespaceRes}", 
                    "use {$newNamespaceRes}", 
                    $content
                );

                // Also update any static string references or fully qualified calls
                // e.g. \App\Filament\Ops\Resources\Demand\OrderResource::class
                $content = str_replace(
                    "{$oldNamespaceBase}\\{$resourceName}", 
                    "{$newNamespaceBase}\\{$resourceName}", 
                    $content
                );
            }

            if ($content !== $originalContent) {
                File::put($filePath, $content);
            }
        }

        $this->info("Refactoring complete! All resources are now organized by Domain.");
    }
}
