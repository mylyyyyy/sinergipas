<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            // RUPAM & P2U Shifts
            [
                'name' => 'Pagi (RUPAM)',
                'start_time' => '06:00:00',
                'end_time' => '13:00:00',
                'is_next_day' => false,
            ],
            [
                'name' => 'Siang (RUPAM)',
                'start_time' => '13:00:00',
                'end_time' => '20:00:00',
                'is_next_day' => false,
            ],
            [
                'name' => 'Malam (RUPAM)',
                'start_time' => '20:00:00',
                'end_time' => '06:00:00',
                'is_next_day' => true,
            ],
            // CPNS Ramadhan Shifts
            [
                'name' => 'Pagi (CPNS)',
                'start_time' => '06:00:00',
                'end_time' => '16:00:00',
                'is_next_day' => false,
            ],
            [
                'name' => 'Siang (CPNS)',
                'start_time' => '08:00:00',
                'end_time' => '20:00:00',
                'is_next_day' => false,
            ],
            [
                'name' => 'Malam (CPNS)',
                'start_time' => '20:00:00',
                'end_time' => '06:00:00',
                'is_next_day' => true,
            ],
            [
                'name' => 'Orientasi',
                'start_time' => '06:30:00',
                'end_time' => '16:00:00',
                'is_next_day' => false,
            ],
            // Standard Staf
            [
                'name' => 'Dinas Pagi',
                'start_time' => '07:30:00',
                'end_time' => '14:30:00',
                'is_next_day' => false,
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::updateOrCreate(
                ['name' => $shift['name']],
                $shift
            );
        }
    }
}
