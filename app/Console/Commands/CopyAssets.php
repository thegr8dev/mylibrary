<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class CopyAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copy:assets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will copy default assets to storage folder';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Copying...');

        Storage::createDirectory('public/site_assets');

        File::copy(public_path('default/defaultLightModeLogo.png'), storage_path('app/public/site_assets/defaultLightModeLogo.png'));
        File::copy(public_path('default/defaultDarkModeLogo.png'), storage_path('app/public/site_assets/defaultDarkModeLogo.png'));

        File::copy(public_path('/default/favicon.png'), storage_path('app/public/site_assets/favicon.png'));

        Artisan::call('storage:link');

        $this->info('Done !');
    }
}
