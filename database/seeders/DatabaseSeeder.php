<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed in order: Roles → Products → Users → Campaigns → Contacts
        $this->call([
            RolePermissionSeeder::class,
            ProductSeeder::class,
            UserSeeder::class,
            CampaignSeeder::class,
            ContactSeeder::class,
        ]);
    }
}
