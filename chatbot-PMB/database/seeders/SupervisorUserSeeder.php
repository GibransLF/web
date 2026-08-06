<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SupervisorUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'supervisior1@test.com'],
            [
                'name' => 'supervisior 1',
                'password' => Hash::make('plokijuh.'),
                'role' => UserRole::SUPERVISOR,
                'email_verified_at' => now(),
            ]
        );
    }
}
