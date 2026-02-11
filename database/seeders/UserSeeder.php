<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin User
        User::create([
            'id_role' => 1, // admin
            'username' => 'admin',
            'password' => Hash::make('admin123'),
        ]);

        // Guru User
        User::create([
            'id_role' => 3, // guru
            'username' => 'guru1',
            'password' => Hash::make('guru123'),
        ]);

        // Siswa User
        $siswaUser = User::create([
            'id_role' => 2, // siswa
            'username' => 'siswa1',
            'password' => Hash::make('siswa123'),
        ]);

        // Create Siswa data for siswa1
        // Note: Pastikan sudah ada data kelas di database
        \App\Models\Siswa::create([
            'id_user' => $siswaUser->id_user,
            'nis' => '123456789',
            'nama_siswa' => 'Siswa Demo',
            'id_kelas' => 1, // Sesuaikan dengan id_kelas yang ada
            'jenis_kelamin' => 'L',
            'status_aktif' => true,
        ]);
    }
}
