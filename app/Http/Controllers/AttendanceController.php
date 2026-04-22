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
        $search = $request->search;
        
        // Default range to current month if not provided
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->endOfMonth()->format('Y-m-d');
        $monthStr = Carbon::parse($startDate)->format('Y-m');

        // Fetch employees for Summary Tab (Paginated)
        $employees = Employee::with(['work_unit', 'squad'])
            ->whereHas('user')
            ->when($search, function($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('nip', 'like', "%$search%");
            })
            ->with(['attendances' => function($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            }])
            ->orderBy('full_name')
            ->paginate(50)->withQueryString();

        // Fetch all employees for manual input modal
        $allEmployees = Employee::whereHas('user')->orderBy('full_name')->get();

        // Fetch detailed logs for Log Tab
        $attendanceLogs = Attendance::whereHas('employee')
            ->with('employee')
            ->whereBetween('date', [$startDate, $endDate])
            ->when($search, function($q) use ($search) {
                $q->whereHas('employee', function($eq) use ($search) {
                    $eq->where('full_name', 'like', "%$search%")
                       ->orWhere('nip', 'like', "%$search%");
                });
            })
            ->orderBy('date', 'desc')
            ->orderBy('check_in', 'asc')
            ->paginate(50, ['*'], 'log_page')->withQueryString();

        // Optimized Summary Calculation
        $summary = DB::table('attendances')
            ->join('employees', 'attendances.employee_id', '=', 'employees.id')
            ->leftJoin('ranks', 'employees.rank_id', '=', 'ranks.id')
            ->whereBetween('attendances.date', [$startDate, $endDate])
            ->selectRaw('
                COUNT(CASE WHEN attendances.status != "absent" THEN 1 END) as total_present,
                COUNT(CASE WHEN attendances.late_minutes > 0 THEN 1 END) as total_late,
                SUM(COALESCE(ranks.meal_allowance, attendances.allowance_amount, 0)) as total_allowance
            ')->first();

        // Pass range title for UI
        $rangeTitle = Carbon::parse($startDate)->translatedFormat('d M') . ' - ' . Carbon::parse($endDate)->translatedFormat('d M Y');

        return view('admin.attendance.index', compact('employees', 'allEmployees', 'attendanceLogs', 'summary', 'startDate', 'endDate', 'rangeTitle', 'monthStr'));
    }

    public function import(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        $request->validate(['file' => 'required']);

        try {
            $file = $request->file('file');
            $path = $file->getRealPath();
            $spreadsheet = IOFactory::load($path);
            $data = $spreadsheet->getActiveSheet()->toArray();

            if (count($data) < 2) return back()->with('error', 'File terbaca namun kosong.');

            $scansByNip = [];
            $allDates = [];
            
            // 1. Pre-collect NIPs and Dates
            foreach ($data as $index => $row) {
                if ($index === 0 || !isset($row[4]) || !is_numeric($row[4])) continue;
                $nip = trim((string)$row[4]);
                $scansByNip[$nip][] = $row[1] . ' ' . $row[2];
                $allDates[] = $row[1];
            }

            if (empty($scansByNip)) return back()->with('error', 'Tidak ada data NIP valid ditemukan.');

            $allDates = array_unique($allDates);
            $minDate = min($allDates);
            $maxDate = max($allDates);
            $nips = array_keys($scansByNip);

            // 2. Bulk Loading
            $employees = Employee::with(['rank_relation', 'squad'])
                ->whereIn('nip', $nips)
                ->get()
                ->keyBy('nip');

            $empIds = $employees->pluck('id')->toArray();
            $squadIds = $employees->pluck('squad_id')->filter()->unique()->toArray();

            $allIndividualSchedules = \App\Models\Schedule::with('shift')
                ->whereIn('employee_id', $empIds)
                ->whereBetween('date', [$minDate, $maxDate])
                ->get()
                ->groupBy(fn($s) => $s->employee_id . '_' . $s->date);

            $allSquadSchedules = \App\Models\SquadSchedule::with('shift')
                ->whereIn('squad_id', $squadIds)
                ->whereBetween('date', [$minDate, $maxDate])
                ->get()
                ->groupBy(fn($s) => $s->squad_id . '_' . $s->date);

            $lateThreshold = Setting::getValue('office_late_threshold', '07:30');
            $now = now();
            $upsertData = [];
            $importedCount = 0;

            // 3. Process data
            foreach ($employees as $nip => $emp) {
                if (!isset($scansByNip[$nip])) continue;

                $scans = collect($scansByNip[$nip])->map(fn($s) => Carbon::parse($s))->sort();
                $empDates = $scans->groupBy(fn($s) => $s->format('Y-m-d'));

                foreach ($empDates as $date => $dayScans) {
                    $checkIn = $dayScans->min();
                    $checkOut = $dayScans->max();
                    if ($checkIn == $checkOut) $checkOut = null;

                    $sched = $allIndividualSchedules->get($emp->id . '_' . $date)?->first();
                    if (!$sched && $emp->squad_id) {
                        $sched = $allSquadSchedules->get($emp->squad_id . '_' . $date)?->first();
                    }

                    $startTime = null;
                    $isPicket = false;

                    if ($sched && $sched->shift) {
                        $startTime = Carbon::parse($date . ' ' . $sched->shift->start_time);
                        $isPicket = true;
                    } else {
                        $startTime = Carbon::parse($date . ' ' . $lateThreshold);
                    }

                    $lateMinutes = 0;
                    $status = 'present';

                    if ($checkIn) {
                        if ($checkIn->gt($startTime->copy()->addMinutes(1))) {
                            $lateMinutes = $checkIn->diffInMinutes($startTime);
                            $status = 'late';
                        } else {
                            $status = $isPicket ? 'picket' : 'present';
                        }
                    } else {
                        $status = 'absent';
                    }

                    $rate = $emp->rank_relation->meal_allowance ?? 0;

                    $upsertData[] = [
                        'employee_id' => $emp->id,
                        'date' => $date,
                        'check_in' => $checkIn->format('H:i:s'),
                        'check_out' => $checkOut ? $checkOut->format('H:i:s') : null,
                        'status' => $status,
                        'late_minutes' => $lateMinutes,
                        'allowance_amount' => $rate,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $importedCount++;
                }
            }

            if (!empty($upsertData)) {
                foreach (array_chunk($upsertData, 500) as $chunk) {
                    Attendance::upsert($chunk, ['employee_id', 'date'], ['check_in', 'check_out', 'status', 'late_minutes', 'allowance_amount', 'updated_at']);
                }
            }

            return back()->with('success', "Berhasil memproses $importedCount data absensi.");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    private function calculateAttendanceMetrics($attendance, $employee)
    {
        $attendance->late_minutes = 0;
        $attendance->status = 'absent';

        $schedule = \App\Models\Schedule::where('employee_id', $employee->id)->where('date', $attendance->date)->first();
        if (!$schedule && $employee->squad_id) {
            $schedule = \App\Models\SquadSchedule::where('squad_id', $employee->squad_id)->where('date', $attendance->date)->first();
        }

        $startTime = null;
        $isPicket = false;

        if ($schedule && $schedule->shift) {
            $startTime = Carbon::parse($attendance->date . ' ' . $schedule->shift->start_time);
            $isPicket = true;
        } else {
            $threshold = Setting::getValue('office_late_threshold', '07:30');
            $startTime = Carbon::parse($attendance->date . ' ' . $threshold);
        }

        if ($attendance->check_in) {
            $checkIn = Carbon::parse($attendance->date . ' ' . $attendance->check_in);
            if ($checkIn->gt($startTime->copy()->addMinutes(1))) {
                $attendance->late_minutes = $checkIn->diffInMinutes($startTime);
                $attendance->status = 'late';
            } else {
                $attendance->status = $isPicket ? 'picket' : 'present';
            }
        } else {
            $attendance->status = 'absent';
        }

        $rate = $employee->rank_relation->meal_allowance ?? 0;
        $attendance->allowance_amount = $rate;
    }

    public function storeManual(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'check_in' => 'nullable',
            'check_out' => 'nullable',
            'status' => 'required|in:present,absent,late,on_leave,picket',
        ]);

        $employee = Employee::with('rank_relation')->find($request->employee_id);
        
        $attendance = Attendance::updateOrCreate(
            ['employee_id' => $request->employee_id, 'date' => $request->date],
            [
                'check_in' => $request->check_in,
                'check_out' => $request->check_out,
                'status' => $request->status,
                'late_minutes' => 0,
            ]
        );

        $this->calculateAttendanceMetrics($attendance, $employee);
        $attendance->save();

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'manual_attendance',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . " menginput absensi manual untuk " . $employee->full_name . " tanggal " . $request->date
        ]);

        return back()->with('success', 'Absensi manual berhasil disimpan.');
    }

    public function export(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $filter = $request->filter ?? 'range'; // range, daily, individual
        $type = $request->type ?? 'pdf';
        
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->endOfMonth()->format('Y-m-d');
        
        $query = Employee::with(['work_unit', 'squad', 'rank_relation'])->orderBy('full_name');

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
                    'allowance_amount' => $att ? $currentRate : 0,
                ];
            });
            $reportTitle = "LAPORAN ABSENSI HARIAN - " . strtoupper($exactDate->translatedFormat('d F Y'));
            
            if ($type === 'excel') {
                return $this->exportExcelDaily($data, $reportTitle, "absensi-harian-{$exactDate->format('Y-m-d')}.xlsx");
            }
            return Pdf::loadView('admin.attendance.pdf-daily', compact('data', 'reportTitle'))->setPaper('a4', 'landscape')->download("absensi-harian-{$exactDate->format('Y-m-d')}.pdf");

        } elseif ($filter === 'range' || $filter === 'weekly' || $filter === 'monthly') {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            
            $attendances = Attendance::whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->get()->groupBy('employee_id');
            $data = $employees->map(function($emp) use ($attendances) {
                $atts = $attendances->get($emp->id) ?? collect();
                $currentRate = $emp->rank_relation->meal_allowance ?? 0;
                $presentCount = $atts->where('status', '!=', 'absent')->count();
                return (object)[
                    'employee' => $emp,
                    'total_present' => $presentCount,
                    'total_late_minutes' => $atts->sum('late_minutes'),
                    'total_allowance' => $presentCount * $currentRate,
                ];
            });
            
            $reportTitle = "REKAPITULASI ABSENSI (" . $start->format('d/m/Y') . " - " . $end->format('d/m/Y') . ")";
            
            if ($type === 'excel') {
                return $this->exportExcelMonthly($data, $reportTitle, "rekap-absensi-{$start->format('Ymd')}-{$end->format('Ymd')}.xlsx");
            }
            return Pdf::loadView('admin.attendance.pdf-monthly', compact('data', 'reportTitle'))->setPaper('a4', 'landscape')->download("rekap-absensi-{$start->format('Ymd')}-{$end->format('Ymd')}.pdf");

        } elseif ($filter === 'individual') {
            $emp = $employees->first();
            if (!$emp) return back()->with('error', 'Pegawai tidak ditemukan.');
            
            $logs = Attendance::where('employee_id', $emp->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'asc')
                ->get();
                
            $currentRate = $emp->rank_relation->meal_allowance ?? 0;
            $logs = $logs->map(function($log) use ($currentRate) {
                $log->allowance_amount = $log->status !== 'absent' ? $currentRate : 0;
                return $log;
            });

            $reportTitle = "LAPORAN INDIVIDU - " . strtoupper($emp->full_name) . " (" . Carbon::parse($startDate)->format('d/m/Y') . " - " . Carbon::parse($endDate)->format('d/m/Y') . ")";
            
            if ($type === 'excel') {
                return $this->exportExcelIndividual($emp, $logs, $reportTitle, "laporan-individu-{$emp->nip}.xlsx");
            }
            return Pdf::loadView('admin.attendance.pdf-individual', compact('emp', 'logs', 'reportTitle'))->setPaper('a4', 'portrait')->download("laporan-individu-{$emp->nip}.pdf");
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
                // KOP
                $sheet->mergeCells('B1:G1'); $sheet->setCellValue('B1', Setting::getValue('kop_line_1'));
                $sheet->mergeCells('B2:G2'); $sheet->setCellValue('B2', Setting::getValue('kop_line_2'));
                $sheet->mergeCells('B3:G3'); $sheet->setCellValue('B3', Setting::getValue('kop_address'));
                $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(11);
                $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E40AF'));
                
                $sheet->mergeCells('A5:G5'); $sheet->setCellValue('A5', $this->title);
                $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(13)->setUnderline(true);
                $sheet->getStyle('A5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Table Header
                $sheet->getStyle('A7:G7')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F172A']],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
                ]);

                $lastRow = $sheet->getHighestRow();
                if ($lastRow >= 7) {
                    $sheet->getStyle("A7:G$lastRow")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    // Zebra
                    for ($row = 8; $row <= $lastRow; $row++) {
                        if ($row % 2 == 0) $sheet->getStyle("A$row:G$row")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
                    }
                }
                
                foreach (range('A', 'G') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
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
                    "'" . $item->employee->nip, 
                    $item->check_in ? Carbon::parse($item->check_in)->format('H:i') : '--:--', 
                    $item->check_out && $item->check_out != $item->check_in ? Carbon::parse($item->check_out)->format('H:i') : '--:--', 
                    strtoupper($item->status), 
                    $item->allowance_amount
                ]);
            }
            public function headings(): array { return ['NO', 'NAMA PEGAWAI', 'NIP', 'MASUK', 'PULANG', 'STATUS', 'UANG MAKAN']; }
            public function startCell(): string { return 'A7'; }
            public function drawings() {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setPath(public_path('logo1.png'))->setHeight(80)->setCoordinates('A1');
                return $drawing;
            }
            public function styles($sheet) {
                // KOP
                $sheet->mergeCells('B1:H1'); $sheet->setCellValue('B1', Setting::getValue('kop_line_1'));
                $sheet->mergeCells('B2:H2'); $sheet->setCellValue('B2', Setting::getValue('kop_line_2'));
                $sheet->mergeCells('B3:H3'); $sheet->setCellValue('B3', Setting::getValue('kop_address'));
                $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(11);
                $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E40AF'));

                $sheet->mergeCells('A5:H5'); $sheet->setCellValue('A5', $this->title);
                $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(13)->setUnderline(true);
                $sheet->getStyle('A5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Table Header
                $sheet->getStyle('A7:H7')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F172A']],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
                ]);

                $lastRow = $sheet->getHighestRow();
                if ($lastRow >= 7) {
                    $sheet->getStyle("A7:H$lastRow")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    for ($row = 8; $row <= $lastRow; $row++) {
                        if ($row % 2 == 0) $sheet->getStyle("A$row:H$row")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
                    }
                }
                foreach (range('A', 'H') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
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
                    "'" . $item->employee->nip, 
                    $item->total_present, 
                    $item->total_late_minutes, 
                    $item->total_allowance
                ]);
            }
            public function headings(): array { return ['NO', 'NAMA PEGAWAI', 'NIP', 'TOTAL HADIR (HARI)', 'TOTAL TELAT (MENIT)', 'TOTAL UANG MAKAN']; }
            public function startCell(): string { return 'A7'; }
            public function drawings() {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setPath(public_path('logo1.png'))->setHeight(80)->setCoordinates('A1');
                return $drawing;
            }
            public function styles($sheet) {
                // KOP
                $sheet->mergeCells('B1:G1'); $sheet->setCellValue('B1', Setting::getValue('kop_line_1'));
                $sheet->mergeCells('B2:G2'); $sheet->setCellValue('B2', Setting::getValue('kop_line_2'));
                $sheet->mergeCells('B3:G3'); $sheet->setCellValue('B3', Setting::getValue('kop_address'));
                $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(11);
                $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E40AF'));

                $sheet->mergeCells('A5:G5'); $sheet->setCellValue('A5', $this->title);
                $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(13)->setUnderline(true);
                $sheet->getStyle('A5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Table Header
                $sheet->getStyle('A7:G7')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F172A']],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
                ]);

                $lastRow = $sheet->getHighestRow();
                if ($lastRow >= 7) {
                    $sheet->getStyle("A7:G$lastRow")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    for ($row = 8; $row <= $lastRow; $row++) {
                        if ($row % 2 == 0) $sheet->getStyle("A$row:G$row")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
                    }
                }
                foreach (range('A', 'G') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
                return [];
            }
        }, $filename);
    }
}
