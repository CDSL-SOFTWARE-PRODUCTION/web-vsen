<?php

namespace App\Providers\Filament;

use App\Http\Middleware\FilamentAuthenticateRedirectToLogin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class CmsPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('cms')
            ->path('cms')
            ->profile()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Cms/Resources'), for: 'App\\Filament\\Cms\\Resources')
            ->discoverPages(in: app_path('Filament/Cms/Pages'), for: 'App\\Filament\\Cms\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Cms/Widgets'), for: 'App\\Filament\\Cms\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                \App\Http\Middleware\SetLocale::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                FilamentAuthenticateRedirectToLogin::class,
            ])
            ->renderHook(
                'panels::user-menu.before',
                fn (): string => \Illuminate\Support\Facades\Blade::render('
                    <div class="flex items-center gap-x-3 mr-3">
                        <a href="{{ route(\'language.switch\', [\'locale\' => \'vi\']) }}" class="text-sm font-medium {{ app()->getLocale() === \'vi\' ? \'text-primary-600 underline\' : \'text-gray-500\' }}">
                            VI
                        </a>
                        <span class="text-gray-300">|</span>
                        <a href="{{ route(\'language.switch\', [\'locale\' => \'en\']) }}" class="text-sm font-medium {{ app()->getLocale() === \'en\' ? \'text-primary-600 underline\' : \'text-gray-500\' }}">
                            EN
                        </a>
                    </div>
                '),
            );
    }
}
