<?php

namespace Database\Seeders;

use App\Models\RedirectLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Redirect::factory(10)
                    ->has(RedirectLog::factory(10))
                    ->create();
    }
}
