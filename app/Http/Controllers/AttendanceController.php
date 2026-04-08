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
        $monthStr = $request->filled('month') ? $request->month : now()->format('Y-m');
        $date = Carbon::parse($monthStr);
        $search = $request->search;

        // Fetch employees for Summary Tab
        $employees = Employee::with(['work_unit', 'squad'])
            ->whereHas('user') // Ensure associated user exists
            ->when($search, function($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('nip', 'like', "%$search%");
            })
            ->with(['attendances' => function($q) use ($date) {
                $q->whereMonth('date', $date->month)->whereYear('date', $date->year);
            }])
            ->orderBy('full_name')
            ->paginate(50)->withQueryString();

        // Fetch detailed logs for Log Tab - Only for existing employees
        $attendanceLogs = Attendance::whereHas('employee')
            ->with('employee')
            ->whereMonth('date', $date->month)
            ->whereYear('date', $date->year)
            ->when($search, function($q) use ($search) {
                $q->whereHas('employee', function($eq) use ($search) {
                    $eq->where('full_name', 'like', "%$search%")
                       ->orWhere('nip', 'like', "%$search%");
                });
            })
            ->orderBy('date', 'desc')
            ->orderBy('check_in', 'asc')
            ->paginate(50, ['*'], 'log_page')->withQueryString();

        // Optimized Summary Calculation - Strict join to prevent orphan counts
        $summary = DB::table('attendances')
            ->join('employees', 'attendances.employee_id', '=', 'employees.id')
            ->leftJoin('ranks', 'employees.rank_id', '=', 'ranks.id')
            ->whereMonth('attendances.date', $date->month)
            ->whereYear('attendances.date', $date->year)
            ->selectRaw('
                COUNT(CASE WHEN attendances.status != "absent" THEN 1 END) as total_present,
                COUNT(CASE WHEN attendances.late_minutes > 0 THEN 1 END) as total_late,
                SUM(COALESCE(ranks.meal_allowance, attendances.allowance_amount, 0)) as total_allowance
            ')->first();

        return view('admin.attendance.index', compact('employees', 'attendanceLogs', 'summary', 'monthStr', 'date'));
    }

    public function import(Request $request)
    {
        set_time_limit(0); // Prevent timeout for large data processing
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

        // Meal Allowance from Rank Model
        $rate = 0;
        if ($employee->rank_relation) {
            $rate = $employee->rank_relation->meal_allowance;
        } else {
            // Fallback to old string-based mapping if rank_relation is not set
            $class = strtoupper((string)$employee->rank_class);
            if (str_contains($class, 'IV')) $rate = Setting::getValue('meal_allowance_iv', 41000);
            elseif (str_contains($class, 'III')) $rate = Setting::getValue('meal_allowance_iii', 37000);
            elseif (str_contains($class, 'II')) $rate = Setting::getValue('meal_allowance_ii', 35000);
        }
        
        $attendance->allowance_amount = $rate;
    }

    public function export(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $filter = $request->filter ?? 'monthly'; // daily, weekly, monthly, individual
        $type = $request->type ?? 'pdf';
        $monthStr = $request->month ?? now()->format('Y-m');
        $date = Carbon::parse($monthStr);
        
        $query = Employee::with(['work_unit', 'squad'])->orderBy('full_name');

        // Individual Filter
        if ($request->filled('employee_id')) {
            $query->where('id', $request->employee_id);
        }
        $employees = $query->get();

        if ($filter === 'daily') {
            $exactDate = $request->filled('exact_date') ? Carbon::parse($request->exact_date) : now();
            $attendances = Attendance::whereDate('date', $exactDate)->get()->keyBy('employee_id');
            $data = $employees->map(function($emp) use ($attendances) {
                $att = $attendances->get($emp->id);
                $currentRate = $emp->rank_relation->meal_allowance ?? 0;
                return (object)[
                    'employee' => $emp,
                    'check_in' => $att ? $att->check_in : null,
                    'check_out' => $att ? $att->check_out : null,
                    'status' => $att ? $att->status : 'absent',
                    'late_minutes' => $att ? $att->late_minutes : 0,
                    'allowance_amount' => $att ? $currentRate : 0, // Dynamic
                ];
            });
            $reportTitle = "LAPORAN ABSENSI HARIAN - " . strtoupper($exactDate->translatedFormat('d F Y'));
            
            if ($type === 'excel') {
                return $this->exportExcelDaily($data, $reportTitle, "absensi-harian-{$exactDate->format('Y-m-d')}.xlsx");
            }
            return Pdf::loadView('admin.attendance.pdf-daily', compact('data', 'reportTitle'))->setPaper('a4', 'landscape')->download("absensi-harian-{$exactDate->format('Y-m-d')}.pdf");

        } elseif ($filter === 'weekly') {
            $start = Carbon::parse($request->start_date ?? now()->startOfWeek());
            $end = Carbon::parse($request->end_date ?? now()->endOfWeek());
            
            $attendances = Attendance::whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->get()->groupBy('employee_id');
            $data = $employees->map(function($emp) use ($attendances) {
                $atts = $attendances->get($emp->id) ?? collect();
                $currentRate = $emp->rank_relation->meal_allowance ?? 0;
                $presentCount = $atts->where('status', '!=', 'absent')->count();
                return (object)[
                    'employee' => $emp,
                    'total_present' => $presentCount,
                    'total_late_minutes' => $atts->sum('late_minutes'),
                    'total_allowance' => $presentCount * $currentRate, // Dynamic
                ];
            });
            $reportTitle = "REKAPITULASI ABSENSI MINGGUAN (" . $start->format('d/m') . " - " . $end->format('d/m/Y') . ")";
            
            if ($type === 'excel') {
                return $this->exportExcelMonthly($data, $reportTitle, "rekap-mingguan.xlsx");
            }
            return Pdf::loadView('admin.attendance.pdf-monthly', compact('data', 'reportTitle'))->setPaper('a4', 'landscape')->download("rekap-mingguan.pdf");

        } elseif ($filter === 'individual') {
            $emp = $employees->first();
            if (!$emp) return back()->with('error', 'Pegawai tidak ditemukan.');
            
            $logs = Attendance::where('employee_id', $emp->id)
                ->whereMonth('date', $date->month)
                ->whereYear('date', $date->year)
                ->orderBy('date', 'asc')
                ->get();
                
            $currentRate = $emp->rank_relation->meal_allowance ?? 0;
            $logs = $logs->map(function($log) use ($currentRate) {
                $log->allowance_amount = $log->status !== 'absent' ? $currentRate : 0;
                return $log;
            });

            $reportTitle = "LAPORAN INDIVIDU - " . strtoupper($emp->full_name) . " ({$date->translatedFormat('F Y')})";
            
            if ($type === 'excel') {
                return $this->exportExcelIndividual($emp, $logs, $reportTitle, "laporan-individu-{$emp->nip}.xlsx");
            }
            return Pdf::loadView('admin.attendance.pdf-individual', compact('emp', 'logs', 'reportTitle', 'date'))->setPaper('a4', 'portrait')->download("laporan-individu-{$emp->nip}.pdf");

        } else {
            // Monthly Recap
            $attendances = Attendance::whereMonth('date', $date->month)->whereYear('date', $date->year)->get()->groupBy('employee_id');
            $data = $employees->map(function($emp) use ($attendances) {
                $atts = $attendances->get($emp->id) ?? collect();
                $currentRate = $emp->rank_relation->meal_allowance ?? 0;
                $presentCount = $atts->where('status', '!=', 'absent')->count();
                return (object)[
                    'employee' => $emp,
                    'total_present' => $presentCount,
                    'total_late_minutes' => $atts->sum('late_minutes'),
                    'total_allowance' => $presentCount * $currentRate, // Dynamic
                ];
            });
            $reportTitle = "REKAPITULASI ABSENSI BULANAN - " . strtoupper($date->translatedFormat('F Y'));

            if ($type === 'excel') {
                return $this->exportExcelMonthly($data, $reportTitle, "rekap-bulanan-{$date->format('Y-m')}.xlsx");
            }
            return Pdf::loadView('admin.attendance.pdf-monthly', compact('data', 'reportTitle'))->setPaper('a4', 'landscape')->download("rekap-bulanan-{$date->format('Y-m')}.pdf");
        }
    }

    private function exportExcelIndividual($emp, $logs, $title, $filename)
    {
        return Excel::download(new class($emp, $logs, $title) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles, \Maatwebsite\Excel\Concerns\WithDrawings, \Maatwebsite\Excel\Concerns\WithCustomStartCell {
            protected $emp, $logs, $title;
            public function __construct($e, $l, $t) { $this->emp = $e; $this->logs = $l; $this->title = $t; }
            public function collection() {
                return $this->logs->map(fn($log, $i) => [
                    $i+1, 
                    Carbon::parse($log->date)->translatedFormat('d F Y'),
                    $log->check_in ? Carbon::parse($log->check_in)->format('H:i') : '--:--',
                    $log->check_out && $log->check_out != $log->check_in ? Carbon::parse($log->check_out)->format('H:i') : '--:--',
                    strtoupper($log->status),
                    $log->late_minutes . ' Menit',
                    $log->allowance_amount
                ]);
            }
            public function headings(): array { return ['NO', 'TANGGAL', 'MASUK', 'PULANG', 'STATUS', 'TERLAMBAT', 'UANG MAKAN']; }
            public function startCell(): string { return 'A7'; }
            public function drawings() {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setPath(public_path('logo1.png'))->setHeight(80)->setCoordinates('A1');
                return $drawing;
            }
            public function styles($sheet) {
                $sheet->mergeCells('B1:G1'); $sheet->setCellValue('B1', Setting::getValue('kop_line_1'));
                $sheet->mergeCells('B2:G2'); $sheet->setCellValue('B2', Setting::getValue('kop_line_2'));
                $sheet->mergeCells('A5:G5'); $sheet->setCellValue('A5', $this->title);
                $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A7:G7')->getFont()->setBold(true);
                $lastRow = $sheet->getHighestRow();
                if ($lastRow >= 7) {
                    $sheet->getStyle("A7:G$lastRow")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                }
                return [];
            }
        }, $filename);
    }

    private function exportExcelDaily($data, $title, $filename)
    {
        return Excel::download(new class($data, $title) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles, \Maatwebsite\Excel\Concerns\WithDrawings, \Maatwebsite\Excel\Concerns\WithCustomStartCell {
            protected $data, $title;
            public function __construct($data, $title) { $this->data = $data; $this->title = $title; }
            public function collection() {
                return $this->data->map(fn($item, $i) => [
                    $i+1, 
                    $item->employee->full_name, 
                    $item->employee->nip, 
                    $item->employee->category_label,
                    $item->check_in ? Carbon::parse($item->check_in)->format('H:i') : '--:--', 
                    $item->check_out && $item->check_out != $item->check_in ? Carbon::parse($item->check_out)->format('H:i') : '--:--', 
                    strtoupper($item->status), 
                    $item->allowance_amount
                ]);
            }
            public function headings(): array { return ['NO', 'NAMA PEGAWAI', 'NIP', 'KATEGORI', 'MASUK', 'PULANG', 'STATUS', 'UANG MAKAN']; }
            public function startCell(): string { return 'A7'; }
            public function drawings() {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setPath(public_path('logo1.png'))->setHeight(80)->setCoordinates('A1');
                return $drawing;
            }
            public function styles($sheet) {
                $sheet->mergeCells('B1:H1'); $sheet->setCellValue('B1', Setting::getValue('kop_line_1'));
                $sheet->mergeCells('B2:H2'); $sheet->setCellValue('B2', Setting::getValue('kop_line_2'));
                $sheet->mergeCells('A5:H5'); $sheet->setCellValue('A5', $this->title);
                $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A7:H7')->getFont()->setBold(true);
                $lastRow = $sheet->getHighestRow();
                if ($lastRow >= 7) {
                    $sheet->getStyle("A7:H$lastRow")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                }
                return [];
            }
        }, $filename);
    }

    private function exportExcelMonthly($data, $title, $filename)
    {
        return Excel::download(new class($data, $title) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles, \Maatwebsite\Excel\Concerns\WithDrawings, \Maatwebsite\Excel\Concerns\WithCustomStartCell {
            protected $data, $title;
            public function __construct($data, $title) { $this->data = $data; $this->title = $title; }
            public function collection() {
                return $this->data->map(fn($item, $i) => [
                    $i+1, 
                    $item->employee->full_name, 
                    $item->employee->nip, 
                    $item->employee->category_label,
                    $item->total_present, 
                    $item->total_late_minutes, 
                    $item->total_allowance
                ]);
            }
            public function headings(): array { return ['NO', 'NAMA PEGAWAI', 'NIP', 'KATEGORI', 'TOTAL HADIR (HARI)', 'TOTAL TELAT (MENIT)', 'TOTAL UANG MAKAN']; }
            public function startCell(): string { return 'A7'; }
            public function drawings() {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setPath(public_path('logo1.png'))->setHeight(80)->setCoordinates('A1');
                return $drawing;
            }
            public function styles($sheet) {
                $sheet->mergeCells('B1:G1'); $sheet->setCellValue('B1', Setting::getValue('kop_line_1'));
                $sheet->mergeCells('B2:G2'); $sheet->setCellValue('B2', Setting::getValue('kop_line_2'));
                $sheet->mergeCells('A5:G5'); $sheet->setCellValue('A5', $this->title);
                $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A7:G7')->getFont()->setBold(true);
                $lastRow = $sheet->getHighestRow();
                if ($lastRow >= 7) {
                    $sheet->getStyle("A7:G$lastRow")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                }
                return [];
            }
        }, $filename);
    }
}
