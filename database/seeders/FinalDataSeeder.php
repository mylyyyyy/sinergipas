<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\ScheduleType;
use App\Models\Squad;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FinalDataSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure regular RUPAM/P2U Leaders are set up correctly
        $rupamType = ScheduleType::where('code', 'rupam')->first();
        $p2uType = ScheduleType::where('code', 'p2u')->first();
        $cpnsType = ScheduleType::where('code', 'cpns_ramadhan')->first();

        // 1. RUPAM Mapping (based on PDF 2 P.3)
        $rupamMapping = [
            'Regu A' => ['leader' => 'Wawan Siswanto', 'role' => 'KARUPAM'],
            'Regu B' => ['leader' => 'Srianto', 'role' => 'KARUPAM'],
            'Regu C' => ['leader' => 'Bambang Yatmono', 'role' => 'KARUPAM'],
            'Regu D' => ['leader' => 'Supriyanto', 'role' => 'KARUPAM'],
        ];

        foreach ($rupamMapping as $sName => $data) {
            $squad = Squad::updateOrCreate(
                ['name' => $sName, 'schedule_type_id' => $rupamType->id],
                ['description' => 'Regu Pengamanan ' . $sName]
            );

            Employee::where('full_name', 'like', '%' . $data['leader'] . '%')->update([
                'squad_id' => $squad->id,
                'role_in_squad' => $data['role'],
                'employee_type' => 'regu_jaga'
            ]);
        }

        // 2. P2U Mapping (based on PDF 2 P.4)
        $p2uMapping = [
            'Regu A' => ['leader' => 'Achmad Agung Novianto', 'role' => 'KOMANDAN'],
            'Regu B' => ['leader' => 'Muhammad Choirul Anam', 'role' => 'KOMANDAN'],
            'Regu C' => ['leader' => 'Taufik Nur Wibowo', 'role' => 'KOMANDAN'],
            'Regu D' => ['leader' => 'Dwi Hanis Efendi', 'role' => 'KOMANDAN'],
        ];

        foreach ($p2uMapping as $sName => $data) {
            $squad = Squad::updateOrCreate(
                ['name' => $sName, 'schedule_type_id' => $p2uType->id],
                ['description' => 'Regu P2U ' . $sName]
            );

            Employee::where('full_name', 'like', '%' . $data['leader'] . '%')->update([
                'squad_id' => $squad->id,
                'role_in_squad' => $data['role'],
                'employee_type' => 'regu_jaga'
            ]);
        }

        // 3. CPNS Ramadhan Groups (based on PDF 1)
        // Grouping some CPNS into Orientasi units
        $cpnsNames = [
            'M. Fahmi', 'Arafat Syaikhu', 'Irvan Firmansyah', 
            'Dimas Wahyu', 'Zidan', 'Rangga', 'Haikal'
        ];

        $orientasiSquad = Squad::updateOrCreate(
            ['name' => 'Orientasi CPNS', 'schedule_type_id' => $cpnsType->id],
            ['description' => 'Grup Orientasi CPNS Ramadhan']
        );

        foreach ($cpnsNames as $name) {
            Employee::where('full_name', 'like', '%' . $name . '%')->update([
                'squad_id' => $orientasiSquad->id,
                'employee_type' => 'regu_jaga' // Set to regu_jaga so they appear in schedule tabs
            ]);
        }
    }
}
