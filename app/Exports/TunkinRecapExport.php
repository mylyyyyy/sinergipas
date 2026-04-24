<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TunkinRecapExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $employees;
    protected $month;

    public function __construct($employees, $month)
    {
        $this->employees = $employees;
        $this->month = $month;
    }

    public function collection()
    {
        return $this->employees;
    }

    public function headings(): array
    {
        return [
            ['REKAPITULASI TUNJANGAN KINERJA DAN UANG MAKAN'],
            ['Periode: ' . $this->month],
            [''],
            ['NO', 'NIP', 'NAMA PEGAWAI', 'GRADE', 'JABATAN', 'KEHADIRAN (HARI)', 'UANG MAKAN', 'TUNKIN (BASE)', 'POTONGAN', 'TOTAL TERIMA']
        ];
    }

    public function map($emp): array
    {
        static $no = 1;
        return [
            $no++,
            $emp->nip,
            $emp->full_name,
            $emp->tunkin->grade ?? '-',
            $emp->position,
            $emp->total_attendance . ' Hari',
            $emp->meal_allowance,
            $emp->base_tunkin,
            $emp->potongan,
            $emp->grand_total
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            4 => ['font' => ['bold' => true]],
        ];
    }
}
