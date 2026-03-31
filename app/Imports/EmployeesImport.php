<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EmployeesImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $nip = $row['nip'] ?? $row['NIP'] ?? null;
                $email = $row['email'] ?? $row['Email'] ?? null;
                $nama = $row['nama_lengkap'] ?? $row['Nama Lengkap'] ?? $row['nama'] ?? null;
                $jabatan = $row['jabatan'] ?? $row['Jabatan'] ?? null;

                if (empty($nip) || empty($email)) continue;

                // Create or Update User
                $user = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $nama ?? 'Pegawai Baru',
                        'password' => Hash::make('password'),
                        'role' => 'pegawai'
                    ]
                );

                // Create or Update Employee
                Employee::updateOrCreate(
                    ['nip' => $nip],
                    [
                        'user_id' => $user->id,
                        'full_name' => $nama ?? 'Pegawai Baru',
                        'position' => $jabatan ?? 'Staf'
                    ]
                );
            }
        });
    }
}
