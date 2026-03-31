<?php

namespace Database\Seeders;

use App\Models\WorkUnit;
use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        $units = ['Bagian Umum', 'Kesatuan Pengamanan', 'Seksi Binadik', 'Seksi Giatja', 'Seksi Adm Kamtib'];
        foreach ($units as $u) {
            WorkUnit::updateOrCreate(['name' => $u], ['slug' => Str::slug($u)]);
        }

        $positions = ['Kepala Lapas', 'Ka. KPLP', 'Kasi Binadik', 'Kasubbag Tata Usaha', 'Analis Kepegawaian', 'Staf Administrasi'];
        foreach ($positions as $p) {
            Position::updateOrCreate(['name' => $p], ['slug' => Str::slug($p)]);
        }
    }
}
