<?php

namespace App\Providers\Filament;

use App\Http\Middleware\FilamentAuthenticateRedirectToLogin;
use App\Filament\DataSteward\Pages\DataStewardDashboard;
use App\Filament\DataSteward\Pages\DocumentVaultWorkspace;
use App\Filament\DataSteward\Pages\RequirementMappingWorkspace;
use App\Filament\Ops\Clusters\MasterData\Resources\CanonicalProductResource;
use App\Filament\Ops\Clusters\MasterData\Resources\MedicalDeviceDeclarationResource;
use App\Filament\Ops\Clusters\MasterData\Resources\RequirementResource;
use App\Http\Middleware\SetLocale;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class DataStewardPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('data-steward')
            ->path('data-steward')
            ->homeUrl(fn (): string => route('dashboard'))
            ->profile()
            ->darkMode(true, false)
            ->colors([
                'primary' => Color::Blue,
            ])
            ->resources([
                MedicalDeviceDeclarationResource::class,
                CanonicalProductResource::class,
                RequirementResource::class,
            ])
            ->pages([
                DataStewardDashboard::class,
                RequirementMappingWorkspace::class,
                DocumentVaultWorkspace::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                SetLocale::class,
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
                fn (): string => PanelLocaleSwitcher::render(),
            );
    }
}
