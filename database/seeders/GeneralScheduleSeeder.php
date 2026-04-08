<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Schedule;
use App\Models\ScheduleType;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class GeneralScheduleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Initial Shifts for General Purpose
        $officeShift = Shift::where('name', 'like', '%Dinas Pagi%')
                            ->orWhere('name', 'like', '%Kantor%')
                            ->first();
        
        if (!$officeShift) {
            $officeShift = Shift::create([
                'name' => 'Dinas Pagi (K)',
                'start_time' => '07:30:00',
                'end_time' => '14:30:00',
            ]);
        }

        // Standard 4-day shifts if not exist
        $pagi = Shift::where('name', 'like', '%Pagi%')->where('name', 'not like', '%Dinas%')->first() ?: Shift::create(['name' => 'Pagi (P)', 'start_time' => '07:00:00', 'end_time' => '14:00:00']);
        $siang = Shift::where('name', 'like', '%Siang%')->first() ?: Shift::create(['name' => 'Siang (S)', 'start_time' => '14:00:00', 'end_time' => '21:00:00']);
        $malam = Shift::where('name', 'like', '%Malam%')->where('name', 'not like', '%CPNS%')->first() ?: Shift::create(['name' => 'Malam (M)', 'start_time' => '21:00:00', 'end_time' => '07:00:00', 'is_next_day' => true]);

        // 2. Setup Patterns for General Types
        $rupam = ScheduleType::where('code', 'regu-pengamanan-rupam')->first();
        if ($rupam) {
            $rupam->update(['pattern' => [$pagi->id, $malam->id, 'I', $siang->id]]);
        }

        $p2u = ScheduleType::where('code', 'petugas-p2u')->first();
        if ($p2u) {
            $p2u->update(['pattern' => [$pagi->id, $malam->id, 'I', $siang->id]]);
        }

        $staf = ScheduleType::where('code', 'staf-administrasi-umum')->first();
        if ($staf) {
            // 7-day pattern for office hours
            $staf->update(['pattern' => [$officeShift->id, $officeShift->id, $officeShift->id, $officeShift->id, $officeShift->id, 'I', 'I']]);
        }

        // 3. Generate General Schedule for ALL employees for April 2026 (as requested "jadwal nya juga yang umum")
        // We'll use April 2026 as a target month
        $month = Carbon::create(2026, 4, 1);
        $daysInMonth = $month->daysInMonth;
        $startDate = Carbon::create(2026, 4, 1);

        $employees = Employee::with('squad')->get();

        foreach ($employees as $emp) {
            $type = null;
            if ($emp->squad && $emp->squad->schedule_type_id) {
                $type = $emp->squad->schedule_type;
            } else {
                // Default to Staff if no squad
                $type = $staf;
            }

            if (!$type || empty($type->pattern)) continue;

            $pattern = array_values($type->pattern);
            $count = count($pattern);

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $currentDate = $month->copy()->day($d);
                $diff = $startDate->diffInDays($currentDate, false);
                $index = $diff % $count;
                if ($index < 0) $index += $count;

                $shiftVal = $pattern[$index];
                if ($shiftVal && $shiftVal !== 'I') {
                    Schedule::updateOrCreate(
                        [
                            'employee_id' => $emp->id,
                            'date' => $currentDate->toDateString(),
                            'schedule_type_id' => $type->id,
                        ],
                        ['shift_id' => $shiftVal]
                    );
                }
            }
        }
    }
}
