<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmployeesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Employee::select('nip', 'full_name', 'position', 'rank')->get();
    }

    public function headings(): array
    {
        return ['NIP', 'Nama Lengkap', 'Jabatan', 'Pangkat/Golongan'];
    }
}
