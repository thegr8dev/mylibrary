<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SiteSettings extends Settings
{
    public string $site_title = '';

    public string $light_logo = '';

    public string $dark_logo = '';

    public string $favicon = '';

    public string $primary_color = 'Amber';

    public string $font = 'Inter';

    public bool $spa_mode = false;

    public bool $top_navigation = false;

    public string $copyright_text = '';

    public string $currency = 'USD';

    public string $dateFormat;

    public static function group(): string
    {
        return 'global';
    }
}
