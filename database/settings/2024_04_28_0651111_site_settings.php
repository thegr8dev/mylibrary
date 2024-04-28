<?php

use App\Enums\SiteColors;
use App\Enums\SiteFonts;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('global.site_title', 'My Library');
        $this->migrator->add('global.logo', 'site_assets/logo.png');
        $this->migrator->add('global.favicon', 'site_assets/favicon.png');
        $this->migrator->add('global.primary_color', ucfirst(SiteColors::DEFAULT));
        $this->migrator->add('global.font', SiteFonts::DEFAULT);
        $this->migrator->add('global.copyright_text', __('&copy :date All rights reserved', ['date' => date('Y')]));
    }
};
