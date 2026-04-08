<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Rupam', 'color' => 'rose', 'description' => 'Petugas Regu Pengamanan'],
            ['name' => 'P2U', 'color' => 'amber', 'description' => 'Petugas Pintu Utama'],
            ['name' => 'Staff KPLP', 'color' => 'blue', 'description' => 'Kesatuan Pengamanan Lembaga Pemasyarakatan'],
            ['name' => 'CPNS', 'color' => 'emerald', 'description' => 'Calon Pegawai Negeri Sipil'],
            ['name' => 'Bankam', 'color' => 'violet', 'description' => 'Bantuan Keamanan'],
            ['name' => 'Staff Umum', 'color' => 'slate', 'description' => 'Bagian Tata Usaha & Urusan Umum'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name' => $cat['name'],
                    'color' => $cat['color'],
                    'description' => $cat['description']
                ]
            );
        }
    }
}
