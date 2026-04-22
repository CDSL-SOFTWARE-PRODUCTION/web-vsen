<?php

namespace App\Support\Auth;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

final class FilamentPortalDirectory
{
    /**
     * URL to open after session login. Honors `url.intended` except when it points at `/dashboard`
     * but the user's configured home is elsewhere (e.g. Founder inbox instead of the Breeze hub).
     */
    public static function resolveAfterLoginTarget(Request $request, User $user): string
    {
        $default = self::defaultHomeUrl($user);
        $intended = $request->session()->pull('url.intended');

        if ($intended === null) {
            return self::toAbsoluteUrl($default);
        }

        if (self::urlPathIsDashboard($intended) && ! self::urlPathIsDashboard($default)) {
            return self::toAbsoluteUrl($default);
        }

        return self::toAbsoluteUrl($intended);
    }

    public static function toAbsoluteUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return URL::to($url);
    }

    private static function urlPathIsDashboard(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH);
        if ($path === false || $path === null || $path === '') {
            return str_starts_with($url, '/dashboard');
        }

        return rtrim($path, '/') === '/dashboard';
    }

    /**
     * @return list<array{id: string, url: string, label: string, description: string}>
     */
    public static function portalsFor(User $user): array
    {
        $portals = [];

        foreach (self::panelDefinitions() as $id => $meta) {
            $panel = Filament::getPanel($id);

            if ($user->canAccessPanel($panel)) {
                $path = $panel->getPath();
                $portals[] = [
                    'id' => $id,
                    'url' => URL::to('/'.ltrim($path, '/')),
                    'label' => __($meta['label']),
                    'description' => __($meta['description']),
                ];
            }
        }

        return $portals;
    }

    /**
     * Default route after app login (Breeze). Filament portals are opened from the hub.
     */
    public static function defaultHomeUrl(User $user): string
    {
        return route('dashboard', absolute: false);
    }

    /**
     * @return array<string, array{label: string, description: string}>
     */
    private static function panelDefinitions(): array
    {
        return [
            'ops' => [
                'label' => 'portal.ops_label',
                'description' => 'portal.ops_description',
            ],
            'cms' => [
                'label' => 'portal.cms_label',
                'description' => 'portal.cms_description',
            ],
            'data-steward' => [
                'label' => 'portal.data_steward_label',
                'description' => 'portal.data_steward_description',
            ],
        ];
    }
}
