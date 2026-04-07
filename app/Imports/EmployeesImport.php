<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\User;
use App\Models\Position;
use App\Models\WorkUnit;
use App\Models\Squad;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class EmployeesImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $positions = Position::all()->pluck('id', 'name')->toArray();
        $workUnits = WorkUnit::all()->pluck('id', 'name')->toArray();
        $squads = Squad::all()->pluck('id', 'name')->toArray();

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                // Skip header
                if ($index === 0 && (str_contains(strtolower($row[0]), 'nip') || str_contains(strtolower($row[1]), 'nama'))) {
                    continue;
                }

                // 1. Ambil NIP dan bersihkan secara paksa (Hapus spasi, titik, atau format scientific)
                $nipRaw = trim((string)($row[0] ?? ''));
                if (empty($nipRaw)) continue;
                
                // Konversi scientific notation ke string jika perlu (misal 1.98E+17)
                if (str_contains(strtoupper($nipRaw), 'E+')) {
                    $nip = number_format((float)$nipRaw, 0, '', '');
                } else {
                    $nip = preg_replace('/[^0-9]/', '', $nipRaw); // Hanya ambil angka
                }

                $nama = trim((string)($row[1] ?? ''));
                $jabatanName = trim((string)($row[2] ?? ''));
                $unitName = trim((string)($row[3] ?? ''));
                $email = trim((string)($row[4] ?? ''));
                $nik = trim((string)($row[5] ?? ''));
                $wa = trim((string)($row[6] ?? ''));
                $rankClass = trim((string)($row[7] ?? ''));
                $reguName = trim((string)($row[8] ?? ''));

                if (empty($nip) || empty($email)) continue;

                // Resolve Master Data
                $positionId = $this->resolveId($jabatanName, $positions, Position::class);
                $workUnitId = $this->resolveId($unitName, $workUnits, WorkUnit::class);
                $squadId = null;
                
                if (!empty($reguName) && !in_array(strtolower($reguName), ['staf', 'staff', '-', ''])) {
                    $squadId = $this->resolveId($reguName, $squads, Squad::class);
                }

                $employeeType = ($squadId || str_contains(strtoupper($jabatanName), 'JAGA') || str_contains(strtoupper($jabatanName), 'PENGAMANAN')) 
                                ? 'regu_jaga' : 'non_regu_jaga';

                // 2. Cari Pegawai - Gunakan WHERE LIKE untuk antisipasi spasi di DB
                $employee = Employee::where('nip', $nip)->first();

                if ($employee) {
                    // REPLACE: Update data lama
                    if ($employee->user) {
                        $employee->user->update([
                            'name' => $nama,
                            'email' => $email,
                        ]);
                    }

                    $employee->update([
                        'nik' => $nik,
                        'full_name' => $nama,
                        'phone_number' => $wa,
                        'position' => $jabatanName,
                        'position_id' => $positionId,
                        'work_unit_id' => $workUnitId,
                        'rank_class' => $rankClass,
                        'employee_type' => $employeeType,
                        'squad_id' => $squadId,
                        'picket_regu' => $reguName
                    ]);
                } else {
                    // CREATE: Jika benar-benar baru
                    $user = User::create([
                        'name' => $nama,
                        'email' => $email,
                        'password' => Hash::make($nip),
                        'role' => 'pegawai'
                    ]);

                    Employee::create([
                        'user_id' => $user->id,
                        'nip' => $nip,
                        'nik' => $nik,
                        'full_name' => $nama,
                        'phone_number' => $wa,
                        'position' => $jabatanName,
                        'position_id' => $positionId,
                        'work_unit_id' => $workUnitId,
                        'rank_class' => $rankClass,
                        'employee_type' => $employeeType,
                        'squad_id' => $squadId,
                        'picket_regu' => $reguName
                    ]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function resolveId($name, &$cache, $modelClass)
    {
        if (empty($name)) return null;
        if (isset($cache[$name])) return $cache[$name];

        $record = $modelClass::firstOrCreate(
            ['name' => $name],
            ['slug' => Str::slug($name)]
        );

        $cache[$name] = $record->id;
        return $record->id;
    }
}
