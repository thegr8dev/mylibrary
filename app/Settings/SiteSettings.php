<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SiteSettings extends Settings
{
    public string $site_title;
    public string $logo;
    public string $favicon;
    public string $primary_color;
    public string $font;
    public string $copyright_text;


    public static function group(): string
    {
        return 'global';
    }
}
