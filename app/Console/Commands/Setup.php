<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class Setup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setting up app for first time usage.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting setup....');
        $this->info('Copying assets ...');
        $this->info('Seeding ...');
        Artisan::call('db:seed');
        $this->info('Done !');
    }
}
