<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

use App\Models\Cms\Category;

class HandleInertiaRequests extends Middleware
{
    // ... existing code ...

    public function share(Request $request): array
    {
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
