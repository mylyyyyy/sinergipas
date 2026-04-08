<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Schedule;
use App\Models\ScheduleType;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RamadhanScheduleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Upsert Shifts specifically for Ramadhan
        $shiftsData = [
            ['name' => 'Pagi (P)', 'start_time' => '06:00:00', 'end_time' => '16:00:00'],
            ['name' => 'Siang (S)', 'start_time' => '08:00:00', 'end_time' => '20:00:00'],
            ['name' => 'Malam (M)', 'start_time' => '20:00:00', 'end_time' => '06:00:00', 'is_next_day' => true],
            ['name' => 'Orientasi (L)', 'start_time' => '08:00:00', 'end_time' => '16:30:00'],
            ['name' => 'Orientasi (P)', 'start_time' => '06:30:00', 'end_time' => '16:00:00'],
            ['name' => 'Piket Blok W (Pi)', 'start_time' => '06:00:00', 'end_time' => '16:00:00'],
            ['name' => 'Istirahat (I)', 'start_time' => '00:00:00', 'end_time' => '00:00:00', 'is_off' => true],
        ];

        $shifts = [];
        foreach ($shiftsData as $sd) {
            if ($sd['name'] === 'Istirahat (I)') {
                $shifts['I'] = null; // Mark as off-duty
                continue;
            }
            $shifts[substr($sd['name'], strpos($sd['name'], '(')+1, 1)] = Shift::updateOrCreate(
                ['name' => $sd['name']],
                [
                    'start_time' => $sd['start_time'],
                    'end_time' => $sd['end_time'],
                    'is_next_day' => $sd['is_next_day'] ?? false,
                ]
            );
        }

        // 2. Setup Schedule Types with Patterns
        $typeRamadhan = ScheduleType::updateOrCreate(
            ['code' => 'cpns-ramadhan'],
            [
                'name' => 'CPNS Ramadhan',
                'description' => 'Pola P-M-I-S sesuai Surat Perintah Orientasi Ramadhan 2026',
                'pattern' => ['P', 'M', 'I', 'S'],
                'uses_squads' => false,
                'is_active' => true,
            ]
        );

        $typeOrientasiStaf = ScheduleType::updateOrCreate(
            ['code' => 'cpns-orientasi-staf'],
            [
                'name' => 'CPNS Orientasi Staf',
                'description' => 'Pola Orientasi-I-P sesuai Surat Perintah Orientasi Ramadhan 2026',
                'pattern' => ['L', 'I', 'P'],
                'uses_squads' => false,
                'is_active' => true,
            ]
        );

        $typeOrientasiWanita = ScheduleType::updateOrCreate(
            ['code' => 'cpns-orientasi-wanita'],
            [
                'name' => 'CPNS Orientasi Wanita (Blok W)',
                'description' => 'Pola Orientasi-I-P-i sesuai Surat Perintah Orientasi Ramadhan 2026',
                'pattern' => ['P', 'I', 'P', 'i'], // Di PDF Citra dkk: ID -> P -> i -> Orientasi... wait
                'uses_squads' => false,
                'is_active' => true,
            ]
        );

        // 3. Employee Mapping based on PDF
        $mappings = [
            // Regu Jaga CPNS (P-M-I-S) - Group A
            'WILLYAM DARMA SANTOSO' => ['type_id' => $typeRamadhan->id, 'start_offset' => 0],
            'PUJI WICAKSONO' => ['type_id' => $typeRamadhan->id, 'start_offset' => 0],
            // Group B
            'MOCHAMAD ARSYAD NURFAWWAZA' => ['type_id' => $typeRamadhan->id, 'start_offset' => 2], // Start I (Index 2)
            // Group C
            'MOHAMAD ABDUL HAKIM' => ['type_id' => $typeRamadhan->id, 'start_offset' => 3], // Start S (Index 3)
            'M. FAZA ASLIQUN NASIH' => ['type_id' => $typeRamadhan->id, 'start_offset' => 3],
            // Group D
            'RIZAL HIDAYATULLOH' => ['type_id' => $typeRamadhan->id, 'start_offset' => 1], // Start M (Index 1)

            // Orientasi Staf (L-I-P)
            'JHORGI DANOVAN ERITAMA' => ['type_id' => $typeOrientasiStaf->id, 'start_offset' => 1], // Start I (Feb 20)
            'ARDHAN AR RASYID' => ['type_id' => $typeOrientasiStaf->id, 'start_offset' => 2], // Start P (Feb 20)
            
            // Wanita (Orientasi / Blok W)
            'CITRA NARA SUPRIYONO P.' => ['type_id' => $typeOrientasiWanita->id, 'start_offset' => 2], // Start P (Feb 20)
            'JIHAN SALSA BILLAH' => ['type_id' => $typeOrientasiWanita->id, 'start_offset' => 2],
            'MAYA A\'IDA FAUZIYAH' => ['type_id' => $typeOrientasiWanita->id, 'start_offset' => 0], // Start P (Feb 18)
            'ALDORA AUDRY JULIETIKA R.' => ['type_id' => $typeOrientasiWanita->id, 'start_offset' => 0],
        ];

        // 4. Generate Schedules
        $startDate = Carbon::create(2026, 2, 18);
        $endDate = Carbon::create(2026, 3, 19);

        foreach ($mappings as $name => $cfg) {
            $employee = Employee::where('full_name', 'like', "%$name%")->first();
            if (!$employee) continue;

            $pattern = ScheduleType::find($cfg['type_id'])->pattern;
            $currentDate = $startDate->copy();
            $dayIndex = $cfg['start_offset'];

            while ($currentDate <= $endDate) {
                $shiftKey = $pattern[$dayIndex % count($pattern)];
                $shift = $shifts[$shiftKey] ?? null;

                if ($shift && !$shift->is_off) {
                    Schedule::updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'date' => $currentDate->toDateString(),
                        ],
                        [
                            'shift_id' => $shift->id,
                            'schedule_type_id' => $cfg['type_id']
                        ]
                    );
                }

                $currentDate->addDay();
                $dayIndex++;
            }
        }
    }
}
