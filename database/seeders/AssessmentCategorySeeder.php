<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\AssessmentCategory;

class AssessmentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Siswa (Dinilai oleh Guru)
            ['name' => 'Etika & Sopan Santun', 'description' => 'Sikap siswa terhadap guru dan teman selama sesi berlangsung.', 'type' => 'siswa', 'is_active' => true],
            ['name' => 'Kerapian & Atribut', 'description' => 'Ketaatan pada seragam dan kelengkapan alat tulis/belajar.', 'type' => 'siswa', 'is_active' => true],
            ['name' => 'Partisipasi Aktif', 'description' => 'Keaktifan siswa dalam bertanya, menjawab, atau berdiskusi.', 'type' => 'siswa', 'is_active' => true],
            ['name' => 'Tanggung Jawab Tugas', 'description' => 'Keseriusan dan ketepatan waktu dalam menyelesaikan tugas.', 'type' => 'siswa', 'is_active' => true],
            ['name' => 'Ketaatan Aturan', 'description' => 'Kepatuhan terhadap aturan khusus (tidak main HP, tidak makan).', 'type' => 'siswa', 'is_active' => true],
            
            // Guru (Dinilai oleh Admin)
            ['name' => 'Kedisiplinan Sesi', 'description' => 'Ketepatan waktu guru dalam membuka sesi absen.', 'type' => 'guru', 'is_active' => true],
            ['name' => 'Kualitas Jurnal Mengajar', 'description' => 'Kedalaman dan kejelasan informasi yang diinput pada jurnal.', 'type' => 'guru', 'is_active' => true],
            ['name' => 'Ketertiban Administrasi', 'description' => 'Konsistensi guru dalam menutup sesi absen tepat waktu.', 'type' => 'guru', 'is_active' => true],
            ['name' => 'Kepatuhan Penilaian', 'description' => 'Kedisiplinan guru dalam memberikan penilaian karakter.', 'type' => 'guru', 'is_active' => true],
            ['name' => 'Profesionalisme Kerja', 'description' => 'Responsivitas guru dan konsistensi kehadiran secara global.', 'type' => 'guru', 'is_active' => true],
        ];

        foreach ($categories as $category) {
            AssessmentCategory::firstOrCreate($category);
        }
    }
}
