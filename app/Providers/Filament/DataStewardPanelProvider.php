<?php

namespace App\Providers\Filament;

use App\Filament\DataSteward\Pages\DataStewardDashboard;
use App\Filament\DataSteward\Pages\DocumentVaultWorkspace;
use App\Filament\DataSteward\Pages\RequirementMappingWorkspace;
use App\Filament\Ops\Resources\MasterData\CanonicalProductResource;
use App\Filament\Ops\Resources\MasterData\MedicalDeviceDeclarationResource;
use App\Filament\Ops\Resources\MasterData\RequirementResource;
use App\Http\Middleware\SetLocale;
use Filament\Http\Middleware\Authenticate;
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
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class DataStewardPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('data-steward')
            ->path('data-steward')
            ->homeUrl(fn (): string => DataStewardDashboard::getUrl())
            ->login()
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
                Authenticate::class,
            ])
            ->renderHook(
                'panels::user-menu.before',
                fn (): string => Blade::render('
                    <div class="flex items-center gap-x-3 mr-3">
                        <a href="{{ route(\'language.switch\', [\'locale\' => \'vi\']) }}" class="text-sm font-medium {{ app()->getLocale() === \'vi\' ? \'text-primary-600 underline dark:text-primary-400\' : \'text-gray-500 dark:text-gray-400\' }}">
                            VI
                        </a>
                        <span class="text-gray-300 dark:text-gray-600">|</span>
                        <a href="{{ route(\'language.switch\', [\'locale\' => \'en\']) }}" class="text-sm font-medium {{ app()->getLocale() === \'en\' ? \'text-primary-600 underline dark:text-primary-400\' : \'text-gray-500 dark:text-gray-400\' }}">
                            EN
                        </a>
                    </div>
                '),
            );
    }
}
