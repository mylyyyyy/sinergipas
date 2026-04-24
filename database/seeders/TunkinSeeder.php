<?php

namespace Database\Seeders;

use App\Models\Tunkin;
use Illuminate\Database\Seeder;

class TunkinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['grade' => 17, 'nominal' => 33240000.00],
            ['grade' => 16, 'nominal' => 27577500.00],
            ['grade' => 15, 'nominal' => 19280000.00],
            ['grade' => 14, 'nominal' => 17064000.00],
            ['grade' => 13, 'nominal' => 10936000.00],
            ['grade' => 12, 'nominal' => 9896000.00],
            ['grade' => 11, 'nominal' => 8757600.00],
            ['grade' => 10, 'nominal' => 5979200.00],
            ['grade' => 9, 'nominal' => 5079200.00],
            ['grade' => 8, 'nominal' => 4595150.00],
            ['grade' => 7, 'nominal' => 3915950.00],
            ['grade' => 6, 'nominal' => 3510400.00],
            ['grade' => 5, 'nominal' => 3134250.00],
            ['grade' => 4, 'nominal' => 2985000.00],
            ['grade' => 3, 'nominal' => 2898000.00],
            ['grade' => 2, 'nominal' => 2708250.00],
            ['grade' => 1, 'nominal' => 2531250.00],
        ];

        foreach ($data as $item) {
            Tunkin::updateOrCreate(
                ['grade' => $item['grade']],
                ['nominal' => $item['nominal']]
            );
        }
    }
}
