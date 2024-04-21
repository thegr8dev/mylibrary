<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('settings')->delete();

        \DB::table('settings')->insert(array(
            0 =>
            array(
                'id' => 1,
                'site_title' => 'My Library',
                'logo' => 'site_assets/logo.png',
                'favicon' => 'site_assets/favicon.png',
                'created_at' => now(),
                'updated_at' => now(),
            ),
        ));
    }
}
