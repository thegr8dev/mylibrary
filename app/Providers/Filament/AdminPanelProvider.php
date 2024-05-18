<?php

namespace App\Providers\Filament;

use App\Filament\Pages\EditProfile as PagesEditProfile;
use App\Settings\SiteSettings;
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
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Schema::hasTable('settings') ? constant("Filament\Support\Colors\Color::".ucfirst(app(config('settings.settings.site_settings'))->primary_color)) : Color::Amber,
            ])
            ->brandLogo(Schema::hasTable('settings') ? asset('storage/'.app(config('settings.settings.site_settings'))?->light_logo) : '')
            ->darkModeBrandLogo(Schema::hasTable('settings') ? asset('storage/'.app(config('settings.settings.site_settings'))?->dark_logo) : '')
            ->brandLogoHeight('3.5rem')
            ->font(Schema::hasTable('settings') ? app(SiteSettings::class)->font : 'Inter')
            ->favicon(Schema::hasTable('settings') ? asset('storage/'.app(config('settings.settings.site_settings'))?->favicon) : '')
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
            ->spa(Schema::hasTable('settings') ? app(SiteSettings::class)->spa_mode : true)
            ->topNavigation(Schema::hasTable('settings') ? app(SiteSettings::class)->top_navigation : false)
            ->userMenuItems([
                'profile' => MenuItem::make()->url(fn (): string => PagesEditProfile::getUrl())->icon('heroicon-o-user'),
            ])
            ->plugin(SimpleLightBoxPlugin::make());
    }
}
