<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UndoDatabaseSeeder extends Seeder
{
    public function run()
    {
        // Temporarily disable foreign key checks to avoid constraint issues
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Delete all seeded records (but keep tables intact)
        DB::table('harvest_records')->truncate();
        DB::table('crop_status_history')->truncate();
        DB::table('planting_records')->truncate();
        DB::table('crops')->truncate();
        DB::table('fields')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('🗑️ Seeded data removed successfully!');
    }
}
