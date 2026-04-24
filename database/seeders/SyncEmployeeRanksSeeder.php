<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Rank;
use Illuminate\Database\Seeder;

class SyncEmployeeRanksSeeder extends Seeder
{
    public function run(): void
    {
        $ranks = Rank::all();
        $employees = Employee::all();

        foreach ($employees as $emp) {
            if ($emp->rank_class) {
                // Find rank that matches the class string
                $rank = $ranks->where('name', $emp->rank_class)->first();
                if ($rank) {
                    $emp->update(['rank_id' => $rank->id]);
                }
            }
        }
    }
}
