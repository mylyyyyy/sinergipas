<?php

namespace Database\Seeders;

use App\Models\ScheduleType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ScheduleTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Regu Pengamanan (RUPAM)',
                'description' => 'Penjagaan rutin blok hunian dan lingkungan lapas oleh regu jaga.',
                'uses_squads' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Petugas P2U',
                'description' => 'Pengamanan Pintu Utama - Pemeriksaan lalin orang dan barang.',
                'uses_squads' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Piket Bantuan Keamanan (Staff KPLP)',
                'description' => 'Bantuan pengamanan oleh staf KPLP pada jam-jam rawan.',
                'uses_squads' => false,
                'is_active' => true,
            ],
            [
                'name' => 'CPNS Ramadhan',
                'description' => 'Piket khusus CPNS selama bulan suci Ramadhan.',
                'uses_squads' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Piket Bantuan Keamanan Tambahan',
                'description' => 'Piket bantuan keamanan di luar jadwal rutin staf.',
                'uses_squads' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Bankam Piket Malam',
                'description' => 'Bantuan keamanan khusus waktu malam hari.',
                'uses_squads' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Staf Administrasi / Umum',
                'description' => 'Tugas administrasi rutin kantor.',
                'uses_squads' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Petugas Blok Wanita',
                'description' => 'Pengamanan khusus area hunian wanita.',
                'uses_squads' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Piket Pengawas (Perwira Piket)',
                'description' => 'Pimpinan pengamanan pada shift berjalan.',
                'uses_squads' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Staff KPLP',
                'description' => 'Personel operasional Kesatuan Pengamanan Lapas.',
                'uses_squads' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Piket Dapur',
                'description' => 'Pengawasan distribusi makanan dan kebersihan dapur.',
                'uses_squads' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Pengawal Pembuangan Sampah',
                'description' => 'Pengawalan warga binaan mengeluarkan sampah.',
                'uses_squads' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Layanan Kunjungan',
                'description' => 'Piket pada jam layanan kunjungan tatap muka/online.',
                'uses_squads' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Pengawas Piket Malam',
                'description' => 'Perwira kontrol keliling pada malam hari.',
                'uses_squads' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Piket Hari Libur',
                'description' => 'Pengamanan ekstra pada hari sabtu, minggu, dan libur nasional.',
                'uses_squads' => false,
                'is_active' => true,
            ],
        ];

        foreach ($types as $index => $type) {
            ScheduleType::updateOrCreate(
                ['code' => Str::slug($type['name'])],
                [
                    'name' => $type['name'],
                    'description' => $type['description'],
                    'uses_squads' => $type['uses_squads'],
                    'is_active' => $type['is_active'],
                    'sort_order' => $index,
                ]
            );
        }
    }
}
