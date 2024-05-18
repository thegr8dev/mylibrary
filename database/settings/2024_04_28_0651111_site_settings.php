<?php

use App\Enums\SiteColors;
use App\Enums\SiteFonts;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('global.site_title', 'Library Master - Manage with ease');
        $this->migrator->add('global.currency', 'INR');
        $this->migrator->add('global.light_logo', 'site_assets/defaultLightModeLogo.png');
        $this->migrator->add('global.dark_logo', 'site_assets/defaultDarkModeLogo.png');
        $this->migrator->add('global.favicon', 'site_assets/favicon.png');
        $this->migrator->add('global.primary_color', ucfirst(SiteColors::DEFAULT));
        $this->migrator->add('global.font', SiteFonts::DEFAULT);
        $this->migrator->add('global.copyright_text', __('All rights reserved'));
    }
};
