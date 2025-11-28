<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        // Create Managers
        $manager1 = User::create([
            'name' => 'Manager One',
            'email' => 'manager1@example.com',
            'password' => Hash::make('password'),
        ]);
        $manager1->assignRole('manager');

        $manager2 = User::create([
            'name' => 'Manager Two',
            'email' => 'manager2@example.com',
            'password' => Hash::make('password'),
        ]);
        $manager2->assignRole('manager');

        // Create Agents
        $agents = [
            ['name' => 'Agent One', 'email' => 'agent1@example.com'],
            ['name' => 'Agent Two', 'email' => 'agent2@example.com'],
            ['name' => 'Agent Three', 'email' => 'agent3@example.com'],
            ['name' => 'Agent Four', 'email' => 'agent4@example.com'],
            ['name' => 'Agent Five', 'email' => 'agent5@example.com'],
        ];

        foreach ($agents as $agentData) {
            $agent = User::create([
                'name' => $agentData['name'],
                'email' => $agentData['email'],
                'password' => Hash::make('password'),
            ]);
            $agent->assignRole('agent');
        }
    }
}
