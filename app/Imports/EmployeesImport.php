<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class EmployeesImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    public function model(array $row)
    {
        // Ambil data berdasarkan heading (case-insensitive)
        $nip = $row['nip'] ?? null;
        $email = $row['email'] ?? null;
        $nama = $row['nama_lengkap'] ?? $row['nama'] ?? null;
        $jabatan = $row['jabatan'] ?? $row['position'] ?? null;

        if (empty($nip) || empty($email)) return null;

        // 1. Update atau Create User (Berdasarkan Email)
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $nama ?? 'Pegawai Baru',
                'password' => Hash::make('password'),
                'role' => 'pegawai'
            ]
        );

        // 2. Update atau Create Employee (Berdasarkan NIP)
        // Kita HANYA mengirimkan data detail, BIARKAN ID diatur otomatis oleh database (Auto Increment)
        Employee::updateOrCreate(
            ['nip' => (string)$nip],
            [
                'user_id' => $user->id,
                'full_name' => $nama ?? 'Pegawai Baru',
                'position' => $jabatan ?? 'Staf'
            ]
        );

        return null; // Return null agar library tidak mencoba insert ulang baris yang sama
    }

    public function batchSize(): int { return 50; }
    public function chunkSize(): int { return 50; }
}
