<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SiteSettings extends Settings
{
    public string $site_title;
    public string $light_logo;
    public string $dark_logo;
    public string $favicon;
    public string $primary_color;
    public string $font;
    public string $spa_mode;
    public string $top_navigation;
    public string $copyright_text;
    public string $currency;


    public static function group(): string
    {
        return 'global';
    }
}
