<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Role::firstOrCreate(['id' => 1], ['name' => 'Administrator']);
        Role::firstOrCreate(['id' => 2], ['name' => 'Staff']);
    }
}
