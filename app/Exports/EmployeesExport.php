<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EmployeesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithCustomStartCell, WithTitle
{
    public function collection()
    {
        return Employee::with(['rank_relation', 'work_unit'])->get();
    }

    public function title(): string
    {
        return 'Data Pegawai';
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function headings(): array
    {
        return [
            'NO',
            'NAMA LENGKAP',
            'NIP',
            'NIK',
            'PANGKAT / GOLONGAN',
            'JABATAN',
            'UNIT KERJA',
            'NO. TELEPON',
            'TIPE PEGAWAI'
        ];
    }

    public function map($employee): array
    {
        static $no = 1;
        return [
            $no++,
            strtoupper($employee->full_name),
            "'" . $employee->nip,
            "'" . $employee->nik,
            $employee->rank_relation->name ?? '-',
            strtoupper($employee->position),
            strtoupper($employee->work_unit->name ?? '-'),
            $employee->phone_number ?? '-',
            $employee->employee_type_label
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'KEMENTERIAN HUKUM DAN HAK ASASI MANUSIA RI');
        
        $sheet->mergeCells('A2:I2');
        $sheet->setCellValue('A2', 'LEMBAGA PEMASYARAKATAN KELAS IIB JOMBANG');
        
        $sheet->mergeCells('A3:I3');
        $sheet->setCellValue('A3', 'Jl. KH. Wahid Hasyim No. 151, Jombang, Jawa Timur 61411');
        
        $sheet->mergeCells('A4:I4');
        $sheet->setCellValue('A4', 'DATA INDUK KEPEGAWAIAN');

        $sheet->getStyle('A1:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(16)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E40AF'));
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(10);
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(14)->setUnderline(true);

        $headerRange = 'A6:I6';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F172A'],
            ],
        ]);

        $lastRow = $sheet->getHighestRow();
        $dataRange = 'A6:I' . $lastRow;
        $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        for ($row = 7; $row <= $lastRow; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle('A' . $row . ':I' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
            }
        }

        return [];
    }
}
