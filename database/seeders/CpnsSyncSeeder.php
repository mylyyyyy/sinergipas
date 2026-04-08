<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Squad;
use App\Models\ScheduleType;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CpnsSyncSeeder extends Seeder
{
    public function run(): void
    {
        // 1. RUPAM Type
        $rupamType = ScheduleType::where('code', 'RUPAM')->first();

        // Data from PDF
        $rupamSquads = [
            'Regu I' => [
                ['name' => 'Irvan Firmansyah', 'nip' => '199001012024011', 'role' => 'Karupam'],
                ['name' => 'Arafat Syaikhu', 'nip' => '199001022024011', 'role' => 'Wakarupam'],
                ['name' => 'M. Fahmi', 'nip' => '199801012026011', 'role' => 'Anggota (CPNS)'],
            ],
            'Regu II' => [
                ['name' => 'Moch. Machfud', 'nip' => '199001032024011', 'role' => 'Karupam'],
                ['name' => 'Ali Wafak', 'nip' => '199001042024011', 'role' => 'Anggota'],
            ],
            'Regu III' => [
                ['name' => 'Heri Wibowo', 'nip' => '199001052024011', 'role' => 'Karupam'],
            ],
            'Regu IV' => [
                ['name' => 'Arie Setyawan', 'nip' => '199001062024011', 'role' => 'Karupam'],
            ]
        ];

        if ($rupamType) {
            foreach ($rupamSquads as $squadName => $members) {
                // Create Squad
                $squad = Squad::updateOrCreate(
                    ['name' => $squadName, 'schedule_type_id' => $rupamType->id],
                    ['description' => 'Regu Pengamanan ' . $squadName]
                );

                // Create and assign members
                foreach ($members as $memberData) {
                    // Try to find user or create dummy user
                    $user = User::firstOrCreate(
                        ['email' => strtolower(str_replace(' ', '.', $memberData['name']) . '@lapas.go.id')],
                        [
                            'name' => $memberData['name'],
                            'password' => Hash::make('password'),
                            'role' => 'pegawai'
                        ]
                    );

                    Employee::updateOrCreate(
                        ['nip' => $memberData['nip']],
                        [
                            'user_id' => $user->id,
                            'full_name' => $memberData['name'],
                            'position' => 'Petugas RUPAM',
                            'employee_type' => 'regu_jaga',
                            'squad_id' => $squad->id,
                            'role_in_squad' => $memberData['role'],
                        ]
                    );
                }
            }
        }
    }
}
