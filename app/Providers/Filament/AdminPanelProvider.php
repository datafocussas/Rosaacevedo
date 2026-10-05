<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Ingresar;
use App\Filament\Support\AvatarIniciales;
use App\Http\Middleware\ExigirDosFactores;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/** Panel administrable en /admin (sección 03): azul-itagui como primario, coral-texto para peligro, Montserrat. */
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Ingresar::class)
            ->brandName('Rosa Acevedo · Panel')
            ->defaultAvatarProvider(AvatarIniciales::class)
            ->favicon(asset('favicon.svg'))
            ->colors([
                'primary' => Color::hex('#003c57'),
                'danger' => Color::hex('#c23a24'),
                'success' => Color::hex('#2f7d5b'),
                'warning' => Color::hex('#9a5b00'),
                'gray' => Color::Slate,
            ])
            ->font('Montserrat', url: asset('css/montserrat-panel.css'))
            ->maxContentWidth('full')
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make('Contenido'),
                NavigationGroup::make('Ciudadanía'),
                NavigationGroup::make('Sitio'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                ExigirDosFactores::class,
            ])
            ->spa(false);
    }
}
