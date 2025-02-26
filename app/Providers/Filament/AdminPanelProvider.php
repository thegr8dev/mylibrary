<?php

namespace App\Providers\Filament;

use App\Filament\Pages\EditProfile as PagesEditProfile;
use App\Settings\SiteSettings;
use Exception;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use SolutionForest\FilamentSimpleLightBox\SimpleLightBoxPlugin;

class AdminPanelProvider extends PanelProvider
{
    protected function settingsTableExists()
    {
        try {
            return Schema::hasTable('settings');
        } catch (Exception $e) {
            return false;
        }
    }

    public function panel(Panel $panel): Panel
    {
        $settingsTableExists = $this->settingsTableExists();
        $siteSettings = $settingsTableExists ? app(SiteSettings::class) : null;

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => $settingsTableExists ? constant("Filament\Support\Colors\Color::".ucfirst($siteSettings->primary_color)) : Color::Amber,
            ])
            ->brandLogo($settingsTableExists ? asset('storage/'.$siteSettings?->light_logo) : '')
            ->darkModeBrandLogo($settingsTableExists ? asset('storage/'.$siteSettings?->dark_logo) : '')
            ->brandLogoHeight('3.5rem')
            ->font($settingsTableExists ? $siteSettings->font : 'Inter')
            ->favicon($settingsTableExists ? asset('storage/'.$siteSettings?->favicon) : '')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
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
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Seating Management')
                    ->icon('heroicon-o-square-3-stack-3d'),
                NavigationGroup::make()
                    ->label('Subscription')
                    ->icon('heroicon-o-ticket'),
                NavigationGroup::make()
                    ->label('User and Permissions')
                    ->icon('heroicon-o-user-group'),
                NavigationGroup::make()
                    ->label('Settings')
                    ->icon('heroicon-o-wrench-screwdriver'),
            ])
            ->navigationItems([])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->globalSearchDebounce('750ms')
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->sidebarCollapsibleOnDesktop()
            ->passwordReset()
            ->profile()
            ->spa($settingsTableExists ? $siteSettings->spa_mode : true)
            ->topNavigation($settingsTableExists ? $siteSettings->top_navigation : false)
            ->userMenuItems([
                'profile' => MenuItem::make()->url(fn (): string => PagesEditProfile::getUrl())->icon('heroicon-o-user'),
            ])
            ->plugin(SimpleLightBoxPlugin::make());
    }
}
