<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('global.spa_mode', 0);
        $this->migrator->add('global.top_navigation', 0);
    }
};
