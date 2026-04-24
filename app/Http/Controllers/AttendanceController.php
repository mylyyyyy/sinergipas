<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Shift;
use App\Models\Schedule;
use App\Models\SquadSchedule;
use App\Models\Setting;
use App\Models\AuditLog;
use App\Models\WorkUnit;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

use App\Services\ScheduleService;

class AttendanceController extends Controller
{
    protected $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function index(Request $request)
    {
        $search = $request->search;
        
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->endOfMonth()->format('Y-m-d');
        $monthStr = Carbon::parse($startDate)->format('Y-m');

        // --- PRE-FETCH SCHEDULES FOR ALL CALCULATIONS ---
        $squadSchedules = SquadSchedule::whereBetween('date', [$startDate, $endDate])
            ->get()->groupBy('squad_id')
            ->map(fn($g) => $g->pluck('date')->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))->toArray());

        $individualSchedules = Schedule::whereBetween('date', [$startDate, $endDate])
            ->get()->groupBy('employee_id')
            ->map(fn($g) => $g->keyBy(fn($i) => Carbon::parse($i->date)->format('Y-m-d')));

        $checkIsScheduled = function($emp, $date) use ($squadSchedules, $individualSchedules) {
            $dateStr = Carbon::parse($date)->format('Y-m-d');
            if (isset($individualSchedules[$emp->id][$dateStr])) {
                return !in_array($individualSchedules[$emp->id][$dateStr]->status, ['off', 'leave', 'sick']);
            }
            if ($emp->squad_id && isset($squadSchedules[$emp->squad_id])) {
                return in_array($dateStr, $squadSchedules[$emp->squad_id]);
            }
            if (!$emp->squad_id) {
                $dayNum = Carbon::parse($date)->dayOfWeek;
                return ($dayNum >= Carbon::MONDAY && $dayNum <= Carbon::FRIDAY);
            }
            return false;
        };
        // ------------------------------------------------

        $employees = Employee::with(['work_unit', 'squad', 'rank_relation'])
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

        // --- CALCULATE SUMMARY FROM THE FILTERED EMPLOYEES ---
        // Note: For accurate global summary (not just current page), we need the full filtered set
        $allFilteredEmployees = Employee::whereHas('user')
            ->when($search, function($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('nip', 'like', "%$search%");
            })
            ->with(['attendances' => function($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            }])
            ->get();

        $totalPresent = 0;
        $totalValidDays = 0;
        $totalLate = 0;
        $totalAllowance = 0;

        foreach ($allFilteredEmployees as $emp) {
            $empRate = $emp->rank_relation->meal_allowance ?? 0;
            $empValidDays = 0;
            $empTotalAllowance = 0;

            foreach ($emp->attendances as $att) {
                if ($att->status !== 'absent') {
                    $totalPresent++;
                    if ($att->late_minutes > 0) $totalLate++;

                    if ($checkIsScheduled($emp, $att->date)) {
                        $empValidDays++;
                        $empTotalAllowance += $empRate;
                    }
                }
            }
            
            $totalValidDays += $empValidDays;
            $totalAllowance += $empTotalAllowance;

            // Also update the paginated collection attributes if they are on the current page
            $paginatedEmp = $employees->getCollection()->where('id', $emp->id)->first();
            if ($paginatedEmp) {
                $paginatedEmp->setAttribute('valid_attendance_count', $empValidDays);
                $paginatedEmp->setAttribute('corrected_total_allowance', $empTotalAllowance);
            }
        }

        $summary = (object)[
            'total_present' => $totalPresent,
            'total_valid_days' => $totalValidDays,
            'total_late' => $totalLate,
            'total_allowance' => $totalAllowance
        ];
        // -------------------------------------------------------

        $allEmployees = Employee::whereHas('user')->orderBy('full_name')->get();

        $attendanceLogs = Attendance::whereHas('employee')
            ->with(['employee.rank_relation'])
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

        $attendanceLogs->getCollection()->transform(function($log) use ($checkIsScheduled) {
            $emp = $log->employee;
            $isScheduled = $checkIsScheduled($emp, $log->date);
            $log->allowance_amount = ($isScheduled && $log->status !== 'absent') ? ($emp->rank_relation->meal_allowance ?? 0) : 0;
            return $log;
        });

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
            foreach ($data as $index => $row) {
                if ($index === 0 || !isset($row[4]) || !is_numeric($row[4])) continue;
                $nip = trim((string)$row[4]);
                $scansByNip[$nip][] = $row[1] . ' ' . $row[2];
            }

            $employees = Employee::with(['rank_relation', 'squad'])->whereIn('nip', array_keys($scansByNip))->get()->keyBy('nip');
            $now = now();
            $upsertData = [];
            $importedCount = 0;

            foreach ($employees as $nip => $emp) {
                $scans = collect($scansByNip[$nip])->map(fn($s) => Carbon::parse($s))->sort();
                $empDates = $scans->groupBy(fn($s) => $s->format('Y-m-d'));

                foreach ($empDates as $date => $dayScans) {
                    $checkIn = $dayScans->min();
                    $checkOut = $dayScans->max();
                    if ($checkIn == $checkOut) $checkOut = null;

                    $validation = $this->scheduleService->validateAttendanceForAllowance($emp, $date, $checkIn->format('H:i:s'));
                    $status = 'present'; $lateMinutes = 0; $allowance = 0;

                    if ($validation['is_valid']) {
                        if ($validation['is_night_shift'] && !str_contains($validation['reason'], 'Kepulangan')) continue; 
                        
                        $status = $validation['status'] ?? 'present';
                        $shift = $validation['schedule']['shift'] ?? null;
                        if ($shift && !in_array($status, ['on_leave', 'sick'])) {
                            $startTime = Carbon::parse($date . ' ' . $shift->start_time);
                            if ($checkIn->gt($startTime->copy()->addMinutes(1))) {
                                $lateMinutes = $checkIn->diffInMinutes($startTime);
                                $status = 'late';
                            }
                        }
                        $allowance = $emp->rank_relation->meal_allowance ?? 0;
                    }

                    $upsertData[] = [
                        'employee_id' => $emp->id,
                        'date' => $date,
                        'check_in' => $checkIn->format('H:i:s'),
                        'check_out' => $checkOut ? $checkOut->format('H:i:s') : null,
                        'status' => $status,
                        'late_minutes' => $lateMinutes,
                        'allowance_amount' => $allowance,
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

    public function export(Request $request)
    {
        set_time_limit(600);
        ini_set('memory_limit', '2048M');

        $filter = $request->filter ?? 'range';
        $type = $request->type ?? 'pdf';
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->endOfMonth()->format('Y-m-d');
        
        $query = Employee::query()->orderBy('full_name');
        if ($request->filled('employee_id')) $query->where('id', $request->employee_id);
        if ($request->filled('work_unit_id')) $query->where('work_unit_id', $request->work_unit_id);
        $workUnit = $request->filled('work_unit_id') ? WorkUnit::find($request->work_unit_id) : null;

        // --- UNIFIED SCHEDULE DATA FETCHING ---
        $squadSchedules = SquadSchedule::whereBetween('date', [$startDate, $endDate])
            ->get()->groupBy('squad_id')
            ->map(fn($g) => $g->pluck('date')->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))->toArray());

        $individualSchedules = Schedule::whereBetween('date', [$startDate, $endDate])
            ->get()->groupBy('employee_id')
            ->map(fn($g) => $g->keyBy(fn($i) => Carbon::parse($i->date)->format('Y-m-d')));

        // Helper logic to determine if an employee is scheduled on a specific date
        $checkIsScheduled = function($emp, $date) use ($squadSchedules, $individualSchedules) {
            $dateStr = Carbon::parse($date)->format('Y-m-d');
            
            // 1. Individual Override (Highest Priority)
            if (isset($individualSchedules[$emp->id][$dateStr])) {
                return !in_array($individualSchedules[$emp->id][$dateStr]->status, ['off', 'leave', 'sick']);
            }
            
            // 2. Squad Schedule
            if ($emp->squad_id && isset($squadSchedules[$emp->squad_id])) {
                return in_array($dateStr, $squadSchedules[$emp->squad_id]);
            }
            
            // 3. Default Office (Staff only, Mon-Fri)
            if (!$emp->squad_id) {
                $dayNum = Carbon::parse($date)->dayOfWeek;
                return ($dayNum >= Carbon::MONDAY && $dayNum <= Carbon::FRIDAY);
            }
            
            return false;
        };
        // --------------------------------------

        if ($filter === 'daily') {
            $exactDate = $request->filled('exact_date') ? Carbon::parse($request->exact_date) : now();
            $dateStr = $exactDate->format('Y-m-d');
            $employees = $query->with('rank_relation')->get();
            $attendances = Attendance::whereDate('date', $exactDate)->get()->keyBy('employee_id');

            $data = $employees->map(function($emp) use ($attendances, $dateStr, $checkIsScheduled) {
                $att = $attendances->get($emp->id);
                $isScheduled = $checkIsScheduled($emp, $dateStr);

                return (object)[
                    'employee' => $emp,
                    'check_in' => $att ? $att->check_in : null,
                    'check_out' => $att ? $att->check_out : null,
                    'status' => $att ? $att->status : 'absent',
                    'late_minutes' => $att ? $att->late_minutes : 0,
                    'allowance_amount' => ($isScheduled && $att && $att->status !== 'absent') ? ($emp->rank_relation->meal_allowance ?? 0) : 0,
                ];
            });

            $reportTitle = "ABSENSI HARIAN - " . strtoupper($exactDate->translatedFormat('d F Y'));
            if ($type === 'excel') return $this->exportExcelDaily($data, $reportTitle, "absensi-harian-{$dateStr}.xlsx");
            
            if (ob_get_length()) ob_end_clean();
            return Pdf::loadView('admin.attendance.pdf-daily', ['logs' => $data, 'reportTitle' => $reportTitle, 'date' => $dateStr, 'workUnit' => $workUnit])
                ->setPaper('a4', 'landscape')->setOptions(['isHtml5ParserEnabled' => true])->download("absensi-harian-{$dateStr}.pdf");

        } elseif ($filter === 'range' || $filter === 'weekly' || $filter === 'monthly') {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            $totalDays = $start->diffInDays($end) + 1;
            $employees = $query->with('rank_relation')->get();
            $attendances = Attendance::whereBetween('date', [$startDate, $endDate])->get()->groupBy('employee_id');

            $data = $employees->map(function($emp) use ($attendances, $totalDays, $checkIsScheduled) {
                $atts = $attendances->get($emp->id) ?? collect();
                $presentCount = $atts->whereNotIn('status', ['absent'])->count();

                // Calculate allowance only for days where they were both present AND scheduled
                $eligiblePresence = $atts->filter(function($att) use ($emp, $checkIsScheduled) {
                    return $att->status !== 'absent' && $checkIsScheduled($emp, $att->date);
                })->count();

                return (object)[
                    'full_name' => strtoupper($emp->full_name),
                    'nip' => $emp->nip,
                    'present_count' => $presentCount,
                    'late_count' => $atts->where('status', 'late')->count(),
                    'absent_count' => max(0, $totalDays - $presentCount),
                    'total_allowance' => $eligiblePresence * ($emp->rank_relation->meal_allowance ?? 0),
                ];
            });
            
            $reportTitle = "REKAP ABSENSI (" . $start->format('d/m/Y') . " - " . $end->format('d/m/Y') . ")";
            if ($type === 'excel') return $this->exportExcelMonthly($data, $reportTitle, "rekap-absensi.xlsx");

            if (ob_get_length()) ob_end_clean();
            return Pdf::loadView('admin.attendance.pdf-monthly', ['logs' => $data, 'reportTitle' => $reportTitle, 'startDate' => $startDate, 'endDate' => $endDate, 'workUnit' => $workUnit])
                ->setPaper('a4', 'landscape')->setOptions(['isHtml5ParserEnabled' => true])->download("rekap-absensi.pdf");

        } elseif ($filter === 'individual') {
            $emp = $query->with('rank_relation')->first();
            if (!$emp) return back()->with('error', 'Pegawai tidak ditemukan.');
            
            $logs = Attendance::where('employee_id', $emp->id)->whereBetween('date', [$startDate, $endDate])->orderBy('date', 'asc')->get();
            $rate = $emp->rank_relation->meal_allowance ?? 0;

            foreach($logs as $l) {
                $isScheduled = $checkIsScheduled($emp, $l->date);
                $l->allowance_amount = ($isScheduled && $l->status !== 'absent') ? $rate : 0;
            }

            $reportTitle = "LAPORAN INDIVIDU - " . strtoupper($emp->full_name);
            if ($type === 'excel') return $this->exportExcelIndividual($emp, $logs, $reportTitle, "laporan-individu-{$emp->nip}.xlsx");

            if (ob_get_length()) ob_end_clean();
            return Pdf::loadView('admin.attendance.pdf-individual', compact('emp', 'logs', 'reportTitle'))
                ->setPaper('a4', 'portrait')->setOptions(['isHtml5ParserEnabled' => true])->download("laporan-individu-{$emp->nip}.pdf");
        }
    }

    private function exportExcelIndividual($emp, $logs, $title, $filename)
    {
        return Excel::download(new class($emp, $logs, $title) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles, \Maatwebsite\Excel\Concerns\WithDrawings, \Maatwebsite\Excel\Concerns\WithCustomStartCell {
            protected $emp, $logs, $title;
            public function __construct($e, $l, $t) { $this->emp = $e; $this->logs = $l; $this->title = $t; }
            public function collection() {
                return $this->logs->map(fn($log, $i) => [
                    $i+1, Carbon::parse($log->date)->format('d/m/Y'),
                    $log->check_in ?? '--:--', $log->check_out ?? '--:--',
                    strtoupper($log->status), $log->late_minutes . 'm', $log->allowance_amount
                ]);
            }
            public function headings(): array { return ['NO', 'TANGGAL', 'MASUK', 'PULANG', 'STATUS', 'TELAT', 'UANG MAKAN']; }
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
                $sheet->getStyle('A7:G7')->applyFromArray(['font'=>['bold'=>true,'color'=>['rgb'=>'FFFFFF']],'fill'=>['fillType'=>\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'startColor'=>['rgb'=>'0F172A']]]);
                $lastRow = $sheet->getHighestRow();
                if ($lastRow >= 7) $sheet->getStyle("A7:G$lastRow")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
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
                return $this->data->map(fn($item, $i) => [$i+1, $item->employee->full_name, "'" . $item->employee->nip, $item->check_in, $item->check_out, strtoupper($item->status), $item->allowance_amount]);
            }
            public function headings(): array { return ['NO', 'NAMA PEGAWAI', 'NIP', 'MASUK', 'PULANG', 'STATUS', 'UANG MAKAN']; }
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
                $sheet->getStyle('A7:H7')->applyFromArray(['font'=>['bold'=>true,'color'=>['rgb'=>'FFFFFF']],'fill'=>['fillType'=>\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'startColor'=>['rgb'=>'0F172A']]]);
                $lastRow = $sheet->getHighestRow();
                if ($lastRow >= 7) $sheet->getStyle("A7:H$lastRow")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
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
                return $this->data->map(fn($item, $i) => [$i+1, $item->full_name, "'" . $item->nip, $item->present_count, $item->late_count, $item->total_allowance]);
            }
            public function headings(): array { return ['NO', 'NAMA PEGAWAI', 'NIP', 'HADIR', 'TELAT', 'TOTAL UANG MAKAN']; }
            public function startCell(): string { return 'A7'; }
            public function drawings() {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setPath(public_path('logo1.png'))->setHeight(80)->setCoordinates('A1');
                return $drawing;
            }
            public function styles($sheet) {
                $sheet->mergeCells('B1:F1'); $sheet->setCellValue('B1', Setting::getValue('kop_line_1'));
                $sheet->mergeCells('B2:F2'); $sheet->setCellValue('B2', Setting::getValue('kop_line_2'));
                $sheet->mergeCells('A5:F5'); $sheet->setCellValue('A5', $this->title);
                $sheet->getStyle('A7:F7')->applyFromArray(['font'=>['bold'=>true,'color'=>['rgb'=>'FFFFFF']],'fill'=>['fillType'=>\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'startColor'=>['rgb'=>'0F172A']]]);
                $lastRow = $sheet->getHighestRow();
                if ($lastRow >= 7) $sheet->getStyle("A7:F$lastRow")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                foreach (range('A', 'F') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
                return [];
            }
        }, $filename);
    }
}
