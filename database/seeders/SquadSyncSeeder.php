<?php

namespace Database\Seeders;

use App\Models\Squad;
use App\Models\Employee;
use App\Models\ScheduleType;
use Illuminate\Database\Seeder;

class SquadSyncSeeder extends Seeder
{
    public function run(): void
    {
        $rupamType = ScheduleType::where('code', 'rupam')->first();
        $p2uType = ScheduleType::where('code', 'p2u')->first();

        // 1. RUPAM Squads
        $rupamData = [
            ['name' => 'Regu A', 'karupam_id' => 320], // Wawan Siswanto
            ['name' => 'Regu B', 'karupam_id' => 308], // Srianto
            ['name' => 'Regu C', 'karupam_id' => 234], // Bambang Yatmono
            ['name' => 'Regu D', 'karupam_id' => 311], // Supriyanto
        ];

        foreach ($rupamData as $data) {
            $squad = Squad::updateOrCreate(
                ['name' => $data['name'], 'schedule_type_id' => $rupamType->id],
                ['description' => 'Regu Pengamanan ' . $data['name']]
            );

            // Assign Karupam
            Employee::where('id', $data['karupam_id'])->update([
                'squad_id' => $squad->id,
                'role_in_squad' => 'KARUPAM'
            ]);
        }

        // 2. P2U Squads
        $p2uData = [
            ['name' => 'Regu A', 'komandan_id' => 220], // Achmad Agung N
            ['name' => 'Regu B', 'komandan_id' => 285], // M. Choirul Anam
            ['name' => 'Regu C', 'komandan_id' => 317], // Taufik Nur
            ['name' => 'Regu D', 'komandan_id' => 246], // Dwi Hanis E.
        ];

        foreach ($p2uData as $data) {
            $squad = Squad::updateOrCreate(
                ['name' => $data['name'], 'schedule_type_id' => $p2uType->id],
                ['description' => 'Regu P2U ' . $data['name']]
            );

            // Assign Komandan
            Employee::where('id', $data['komandan_id'])->update([
                'squad_id' => $squad->id,
                'role_in_squad' => 'KOMANDAN'
            ]);
        }
    }
}
