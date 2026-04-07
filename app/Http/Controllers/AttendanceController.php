<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Shift;
use App\Models\Schedule;
use App\Models\Setting;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        // Eager Loading for performance
        $query = Attendance::with(['employee.work_unit']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('nip', 'like', "%$search%");
            });
        }

        $monthStr = $request->filled('month') ? $request->month : now()->format('Y-m');
        $date = Carbon::parse($monthStr);
        $query->whereMonth('date', $date->month)->whereYear('date', $date->year);

        // Fast Paginate
        $attendances = $query->orderBy('date', 'desc')->paginate(50)->withQueryString();
        
        // Optimized Summary Calculation
        $summary = DB::table('attendances')
            ->whereMonth('date', $date->month)
            ->whereYear('date', $date->year)
            ->selectRaw('
                COUNT(CASE WHEN status = "present" THEN 1 END) as total_present,
                COUNT(CASE WHEN late_minutes > 0 THEN 1 END) as total_late,
                SUM(allowance_amount) as total_allowance
            ')->first();

        return view('admin.attendance.index', compact('attendances', 'summary', 'monthStr'));
    }

    public function import(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        $request->validate(['file' => 'required']);

        try {
            $file = $request->file('file');
            $path = $file->getRealPath();

            $inputFileType = IOFactory::identify($path);
            $reader = IOFactory::createReader($inputFileType);
            if ($inputFileType === 'Html') $reader->setReadDataOnly(true);
            
            $spreadsheet = $reader->load($path);
            $data = $spreadsheet->getActiveSheet()->toArray();

            if (count($data) < 2) return back()->with('error', 'File terbaca namun kosong.');

            $importedCount = 0;
            $employees = Employee::all()->keyBy('nip');
            
            $groupedData = [];

            foreach ($data as $index => $row) {
                if ($index === 0 || !isset($row[4]) || !is_numeric($row[4])) {
                    if (isset($row[4]) && strtolower((string)$row[4]) === 'nip') continue;
                    if ($index < 5) continue;
                }

                $nip = trim((string)$row[4]);
                if (empty($nip) || !isset($employees[$nip])) continue;

                try {
                    $date = Carbon::parse($row[1])->format('Y-m-d');
                    $time = Carbon::parse($row[2])->format('H:i:s');
                    $key = $nip . '_' . $date;

                    if (!isset($groupedData[$key])) {
                        $groupedData[$key] = ['emp' => $employees[$nip], 'date' => $date, 'times' => []];
                    }
                    $groupedData[$key]['times'][] = $time;
                } catch (\Exception $e) { continue; }
            }

            DB::beginTransaction();
            foreach ($groupedData as $entry) {
                $emp = $entry['emp'];
                $date = $entry['date'];
                $times = $entry['times'];

                $minTime = min($times);
                $maxTime = max($times);

                // REPLACE Logic: Delete existing if any
                Attendance::where('employee_id', $emp->id)->where('date', $date)->delete();

                $attendance = Attendance::create([
                    'employee_id' => $emp->id,
                    'date' => $date,
                    'check_in' => $minTime,
                    'check_out' => $maxTime,
                    'status' => 'present'
                ]);

                $this->calculateAttendanceMetrics($attendance, $emp);
                $attendance->save();
                $importedCount++;
            }
            DB::commit();

            return back()->with('success', "Berhasil memproses $importedCount data absensi.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    private function calculateAttendanceMetrics($attendance, $employee)
    {
        $schedule = Schedule::where('employee_id', $employee->id)->where('date', $attendance->date)->first();
        $pos = strtoupper((string)$employee->position);
        
        // Identify Regu Staff
        $isRegu = str_contains($pos, 'JAGA') || str_contains($pos, 'PENJAGA') || $employee->squad_id != null;
        
        $shift = null;
        $startTime = null;

        if ($schedule) {
            $shift = $schedule->shift;
            if ($shift) $startTime = Carbon::parse($shift->start_time);
        } elseif (!$isRegu) {
            // Non-Regu uses Admin Setting or Default 07:30
            $threshold = Setting::getValue('office_late_threshold', '07:30');
            $startTime = Carbon::parse($threshold);
        }

        if ($startTime) {
            $checkIn = Carbon::parse($attendance->check_in);
            if ($checkIn->gt($startTime)) {
                $attendance->late_minutes = $checkIn->diffInMinutes($startTime);
                $attendance->status = 'late';
            } else {
                $attendance->late_minutes = 0;
                $attendance->status = 'present';
            }
        }

        // Meal Allowance
        $class = strtoupper((string)$employee->rank_class);
        $rate = 0;
        if (str_contains($class, 'IV')) $rate = Setting::getValue('meal_allowance_iv', 41000);
        elseif (str_contains($class, 'III')) $rate = Setting::getValue('meal_allowance_iii', 37000);
        elseif (str_contains($class, 'II')) $rate = Setting::getValue('meal_allowance_ii', 35000);
        
        $attendance->allowance_amount = $rate;
    }

    public function export(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M'); // Increased for large PDF exports

        $filter = $request->filter ?? 'monthly';
        $date = Carbon::parse($request->month ?? now());
        
        // Use cursor for memory efficiency if data is huge
        $query = Attendance::with(['employee.work_unit'])->orderBy('date', 'asc');

        if ($filter === 'daily') {
            $query->whereDate('date', now());
        } elseif ($filter === 'weekly') {
            $query->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]);
        } else {
            $query->whereMonth('date', $date->month)->whereYear('date', $date->year);
        }

        if ($request->type === 'excel') {
            $attendances = $query->get();
            return $this->exportExcel($attendances, $date, $filter);
        }

        // For PDF, we still need the collection
        $attendances = $query->get();
        $pdf = Pdf::loadView('admin.attendance.pdf', compact('attendances', 'date', 'filter'));
        return $pdf->download("rekap-absensi-{$filter}.pdf");
    }

    private function exportExcel($attendances, $date, $filter)
    {
        return Excel::download(new class($attendances, $date, $filter) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles, \Maatwebsite\Excel\Concerns\WithDrawings, \Maatwebsite\Excel\Concerns\WithCustomStartCell {
            protected $data, $date, $filter;
            public function __construct($data, $date, $filter) { $this->data = $data; $this->date = $date; $this->filter = $filter; }
            public function collection() {
                return $this->data->map(fn($a, $i) => [$i+1, $a->date, $a->employee->full_name, $a->employee->nip, $a->check_in, $a->check_out, $a->status, $a->allowance_amount]);
            }
            public function headings(): array { return ['NO', 'TANGGAL', 'NAMA PEGAWAI', 'NIP', 'MASUK', 'PULANG', 'STATUS', 'UANG MAKAN']; }
            public function startCell(): string { return 'A7'; }
            public function drawings() {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setPath(public_path('logo1.png'))->setHeight(80)->setCoordinates('A1');
                return $drawing;
            }
            public function styles($sheet) {
                $sheet->mergeCells('B1:H1'); $sheet->setCellValue('B1', Setting::getValue('kop_line_1'));
                $sheet->mergeCells('B2:H2'); $sheet->setCellValue('B2', Setting::getValue('kop_line_2'));
                $sheet->mergeCells('A5:H5'); $sheet->setCellValue('A5', "LAPORAN ABSENSI (" . strtoupper($this->filter) . ") - " . $this->date->translatedFormat('F Y'));
                $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A7:H7')->getFont()->setBold(true);
                $lastRow = $sheet->getHighestRow();
                if ($lastRow >= 7) {
                    $sheet->getStyle("A7:H$lastRow")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                }
                return [];
            }
        }, "rekap-absensi-{$filter}.xlsx");
    }
}
