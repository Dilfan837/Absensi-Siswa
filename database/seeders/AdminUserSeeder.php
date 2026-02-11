<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Ensure Admin Role exists
        $role = Role::firstOrCreate(['nama_role' => 'admin']);

        // Create Admin User
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'email' => 'admin@school.com',
                'password' => Hash::make('admin123'),
                'nama' => 'Administrator',
                'id_role' => $role->id_role,
            ]
        );

        $this->command->info('Admin user created successfully.');
    }
}
