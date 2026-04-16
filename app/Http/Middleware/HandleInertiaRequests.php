<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

use App\Models\Cms\Category;

class HandleInertiaRequests extends Middleware
{
    // ... existing code ...

    public function share(Request $request): array
    {
        // #region agent log
        try {
            file_put_contents(
                '/home/hungp0722/development/DVT/web-vsen/.cursor/debug-44995f.log',
                json_encode([
                    'sessionId' => '44995f',
                    'runId' => 'inertia-share-check',
                    'hypothesisId' => 'H3',
                    'location' => 'app/Http/Middleware/HandleInertiaRequests.php:18',
                    'message' => 'inertia_share_enter',
                    'data' => [
                        'path' => $request->path(),
                        'db_default' => config('database.default'),
                        'categories_table_exists' => Schema::hasTable('categories'),
                    ],
                    'timestamp' => (int) round(microtime(true) * 1000),
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
                FILE_APPEND
            );
        } catch (\Throwable $e) {
        }
        // #endregion

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'locale' => app()->getLocale(),
            'translations' => is_file(lang_path(app()->getLocale() . '.json')) 
                ? json_decode(file_get_contents(lang_path(app()->getLocale() . '.json')), true)
                : [],
            'categories' => Category::where('is_active', true)->get()->map(fn($c) => [
                'name' => $c->name,
                'slug' => $c->slug
            ]),
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
