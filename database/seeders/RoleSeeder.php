<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Role::create(['id_role' => 1, 'nama_role' => 'admin']);
        \App\Models\Role::create(['id_role' => 2, 'nama_role' => 'siswa']);
        \App\Models\Role::create(['id_role' => 3, 'nama_role' => 'guru']);
    }
}
