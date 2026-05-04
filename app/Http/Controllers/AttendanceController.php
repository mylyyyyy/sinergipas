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
        $squadSchedules = SquadSchedule::with('shift')->whereBetween('date', [$startDate, $endDate])
            ->get()->groupBy('squad_id')
            ->map(fn($g) => $g->groupBy(fn($i) => Carbon::parse($i->date)->format('Y-m-d')));

        $individualSchedules = Schedule::with('shift')->whereBetween('date', [$startDate, $endDate])
            ->get()->groupBy('employee_id')
            ->map(fn($g) => $g->groupBy(fn($i) => Carbon::parse($i->date)->format('Y-m-d')));

        $staffInTime = \App\Models\Setting::getValue('payroll_staff_in', '07:30');
        $staffSatEnabled = \App\Models\Setting::getValue('payroll_staff_saturday_enabled', 'off');
        $staffSatIn = \App\Models\Setting::getValue('payroll_staff_saturday_in', '07:30');
        
        $ramadanEnabled = \App\Models\Setting::getValue('payroll_ramadan_enabled', 'off');
        $ramadanStart = Carbon::parse(\App\Models\Setting::getValue('payroll_ramadan_start', date('Y-m-d')))->startOfDay();
        $ramadanEnd = Carbon::parse(\App\Models\Setting::getValue('payroll_ramadan_end', date('Y-m-d')))->endOfDay();
        $ramadanIn = \App\Models\Setting::getValue('payroll_ramadan_staff_in', '08:00');
        $ramadanSatEnabled = \App\Models\Setting::getValue('payroll_ramadan_saturday_enabled', 'off');
        $ramadanSatIn = \App\Models\Setting::getValue('payroll_ramadan_saturday_in', '08:00');

        // Helper to get effective shift info (including double shift check)
        $getEffectiveShiftInfo = function($emp, $date) use ($squadSchedules, $individualSchedules, $staffInTime, $staffSatEnabled, $staffSatIn, $ramadanEnabled, $ramadanStart, $ramadanEnd, $ramadanIn, $ramadanSatEnabled, $ramadanSatIn) {
            $dateStr = Carbon::parse($date)->format('Y-m-d');
            $dateObj = Carbon::parse($date);
            
            // 1. Individual
            if (isset($individualSchedules[$emp->id][$dateStr])) {
                $scheds = $individualSchedules[$emp->id][$dateStr];
                $minIn = null; $isDouble = false;
                foreach($scheds as $s) {
                    if (in_array($s->status, ['off', 'leave', 'sick'])) return null;
                    $st = $s->shift->start_time ?? null;
                    if ($st && (!$minIn || $st < $minIn)) $minIn = $st;
                }
                return ['start_time' => $minIn, 'is_double' => $scheds->count() > 1];
            }
            
            // 2. Squad
            if ($emp->squad_id && isset($squadSchedules[$emp->squad_id][$dateStr])) {
                $scheds = $squadSchedules[$emp->squad_id][$dateStr];
                $minIn = null; $hasPagi = false; $hasMalam = false;
                foreach($scheds as $s) {
                    $st = $s->shift->start_time ?? '06:00:00';
                    if ($s->shift && str_contains(strtoupper($s->shift->name), 'PAGI')) { $st = '06:00:00'; $hasPagi = true; }
                    if ($s->shift && str_contains(strtoupper($s->shift->name), 'MALAM')) $hasMalam = true;
                    if (!$minIn || $st < $minIn) $minIn = $st;
                }
                return ['start_time' => $minIn, 'is_double' => ($hasPagi && $hasMalam) || $scheds->count() > 1];
            }
            
            // 3. Office Fallback
            $hasSquadScheduleAtAll = $emp->squad_id && isset($squadSchedules[$emp->squad_id]) && $squadSchedules[$emp->squad_id]->count() > 0;
            if (!$emp->squad_id || !$hasSquadScheduleAtAll) {
                $dayNum = $dateObj->dayOfWeek;
                $isRamadan = ($ramadanEnabled === 'on' && $dateObj->between($ramadanStart, $ramadanEnd));
                if ($dayNum >= Carbon::MONDAY && $dayNum <= Carbon::FRIDAY) {
                    return ['start_time' => ($isRamadan ? $ramadanIn : $staffInTime), 'is_double' => false];
                }
                if ($dayNum === Carbon::SATURDAY) {
                    if ($isRamadan && $ramadanSatEnabled === 'on') return ['start_time' => $ramadanSatIn, 'is_double' => false];
                    if (!$isRamadan && $staffSatEnabled === 'on') return ['start_time' => $staffSatIn, 'is_double' => false];
                }
            }
            return null;
        };

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

        // --- CALCULATE SUMMARY ---
        $allFilteredEmployees = Employee::whereHas('user')
            ->when($search, function($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('nip', 'like', "%$search%");
            })
            ->with(['attendances' => function($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            }])
            ->get();

        $totalPresent = 0; $totalValidDays = 0; $totalLate = 0; $totalAllowance = 0;

        foreach ($allFilteredEmployees as $emp) {
            $empRate = (float)($emp->rank_relation->meal_allowance ?? 0);
            $empValidDays = 0; $empTotalAllowance = 0;

            foreach ($emp->attendances as $att) {
                if ($att->status !== 'absent') {
                    $totalPresent++;
                    $info = $getEffectiveShiftInfo($emp, $att->date);
                    if ($info) {
                        $effectiveStart = $info['start_time'];
                        $isLate = ($att->status === 'late');
                        if (!$isLate && $att->check_in && $effectiveStart) {
                            if (date('H:i', strtotime($att->check_in)) > date('H:i', strtotime($effectiveStart))) $isLate = true;
                        }
                        if ($isLate) $totalLate++;
                        
                        $count = $info['is_double'] ? 2 : 1;
                        $empValidDays += $count;
                        if ($att->status !== 'duty_full' && $att->status !== 'tubel') {
                            $empTotalAllowance += ($empRate * $count);
                        }
                    }
                }
            }
            $totalValidDays += $empValidDays;
            $totalAllowance += $empTotalAllowance;
            $paginatedEmp = $employees->getCollection()->where('id', $emp->id)->first();
            if ($paginatedEmp) {
                $paginatedEmp->setAttribute('valid_attendance_count', $empValidDays);
                $paginatedEmp->setAttribute('corrected_total_allowance', $empTotalAllowance);
            }
        }

        $summary = (object)['total_present' => $totalPresent, 'total_valid_days' => $totalValidDays, 'total_late' => $totalLate, 'total_allowance' => $totalAllowance];

        $allEmployees = Employee::whereHas('user')->orderBy('full_name')->get();

        $attendanceLogs = Attendance::whereHas('employee')->with(['employee.rank_relation'])
            ->whereBetween('date', [$startDate, $endDate])
            ->when($search, function($q) use ($search) {
                $q->whereHas('employee', fn($eq) => $eq->where('full_name', 'like', "%$search%")->orWhere('nip', 'like', "%$search%"));
            })
            ->orderBy('date', 'desc')->orderBy('check_in', 'asc')
            ->paginate(50, ['*'], 'log_page')->withQueryString();

        $attendanceLogs->getCollection()->transform(function($log) use ($getEffectiveShiftInfo) {
            $emp = $log->employee;
            $info = $getEffectiveShiftInfo($emp, $log->date);
            $effectiveStart = $info['start_time'] ?? null;
            $isScheduled = !is_null($effectiveStart);
            $empRate = (float)($emp->rank_relation->meal_allowance ?? 0);
            
            $hasMeal = $isScheduled && !in_array($log->status, ['absent', 'duty_full', 'tubel', 'on_leave', 'sick']);
            $log->allowance_amount = $hasMeal ? ($info['is_double'] ? $empRate * 2 : $empRate) : 0;

            if ($log->check_in && $isScheduled && !in_array($log->status, ['absent', 'duty_full', 'tubel', 'on_leave', 'sick'])) {
                $checkInTime = date('H:i', strtotime($log->check_in));
                $targetInTime = date('H:i', strtotime($effectiveStart));
                if ($checkInTime > $targetInTime) {
                    $log->status = 'late';
                    $log->late_minutes = (int)Carbon::parse($log->date.' '.$checkInTime)->diffInMinutes(Carbon::parse($log->date.' '.$targetInTime));
                } else {
                    if ($log->status === 'late') $log->status = 'present';
                    $log->late_minutes = 0;
                }
            }
            return $log;
        });

        $maxLateCount = (int)\App\Models\Setting::getValue('payroll_max_late_count', 8);
        $rangeTitle = Carbon::parse($startDate)->translatedFormat('d M') . ' - ' . Carbon::parse($endDate)->translatedFormat('d M Y');

        return view('admin.attendance.index', compact('employees', 'allEmployees', 'attendanceLogs', 'summary', 'startDate', 'endDate', 'rangeTitle', 'monthStr', 'maxLateCount'));
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

            $scansByNip = []; $allDates = [];
            $map = ['nip' => 4, 'date' => 1, 'time' => 2, 'datetime' => 0];
            $dataStarted = false;

            foreach ($data as $index => $row) {
                if (!$dataStarted) {
                    $rowNormalized = array_map(fn($v) => strtoupper(str_replace(' ', '', trim((string)$v))), $row);
                    if (in_array('NIP', $rowNormalized) || in_array('TANGGALSCAN', $rowNormalized)) {
                        foreach ($rowNormalized as $colIdx => $h) {
                            if ($h === 'NIP') $map['nip'] = $colIdx;
                            if ($h === 'TANGGAL' || $h === 'TANGGALSCAN') $map['date'] = $colIdx;
                            if ($h === 'JAM') $map['time'] = $colIdx;
                            if ($h === 'TANGGALSCAN') $map['datetime'] = $colIdx;
                        }
                        $dataStarted = true; continue;
                    }
                    if ($index >= 10) $dataStarted = true; else continue;
                }
                $nipRaw = trim((string)($row[$map['nip']] ?? ''));
                if (empty($nipRaw)) continue;
                $nip = preg_replace('/[^0-9]/', '', $nipRaw);
                if (empty($nip)) continue;

                try {
                    $d = trim((string)($row[$map['date']] ?? ''));
                    $t = trim((string)($row[$map['time']] ?? ''));
                    $dt = trim((string)($row[$map['datetime']] ?? ''));
                    if (!empty($d) && !empty($t)) $scanTime = Carbon::parse($d . ' ' . $t);
                    elseif (!empty($dt)) $scanTime = Carbon::parse($dt);
                    elseif (!empty($d)) $scanTime = Carbon::parse($d);
                    else continue;
                    
                    $scansByNip[$nip][] = $scanTime;
                    $allDates[] = $scanTime->format('Y-m-d');
                } catch (\Exception $e) { continue; }
            }

            if (empty($scansByNip)) return back()->with('error', 'Gagal membaca data scan.');

            $excelNips = array_keys($scansByNip);
            $employees = Employee::with(['rank_relation', 'squad'])->whereIn('nip', $excelNips)->get()->keyBy('nip');
            
            if ($employees->isEmpty()) return back()->with('error', 'NIP di Excel tidak cocok dengan Database.');

            $minDate = collect($allDates)->min(); $maxDate = collect($allDates)->max();
            $empIds = $employees->pluck('id')->toArray();
            $squadIds = $employees->pluck('squad_id')->filter()->unique()->toArray();

            $individualSchedules = Schedule::with('shift')->whereIn('employee_id', $empIds)->whereBetween('date', [$minDate, $maxDate])->get()->groupBy('employee_id')->map(fn($g) => $g->groupBy(fn($i) => Carbon::parse($i->date)->format('Y-m-d')));
            $squadSchedules = SquadSchedule::with('shift')->whereIn('squad_id', $squadIds)->whereBetween('date', [$minDate, $maxDate])->get()->groupBy('squad_id')->map(fn($g) => $g->groupBy(fn($i) => Carbon::parse($i->date)->format('Y-m-d')));
            
            $settings = Setting::where('key', 'like', 'payroll_%')->get()->pluck('value', 'key');
            $staffIn = $settings['payroll_staff_in'] ?? '07:30';
            $ramadanEnabled = $settings['payroll_ramadan_enabled'] ?? 'off';
            $ramadanStart = Carbon::parse($settings['payroll_ramadan_start'] ?? date('Y-m-d'))->startOfDay();
            $ramadanEnd = Carbon::parse($settings['payroll_ramadan_end'] ?? date('Y-m-d'))->endOfDay();

            Attendance::whereIn('employee_id', $empIds)->whereBetween('date', [$minDate, $maxDate])->delete();

            $now = now(); $insertData = []; $importedCount = 0;

            foreach ($employees as $dbNip => $emp) {
                $excelKey = null;
                foreach (array_keys($scansByNip) as $k) { if (str_ends_with($dbNip, $k) || str_ends_with($k, $dbNip)) { $excelKey = $k; break; } }
                if (!$excelKey) continue;

                foreach (collect($scansByNip[$excelKey])->sort()->groupBy(fn($s) => $s->format('Y-m-d')) as $date => $dayScans) {
                    $checkIn = $dayScans->min(); $checkOut = $dayScans->max();
                    if ($checkIn->equalTo($checkOut)) $checkOut = null;

                    $effectiveSched = null; $status = 'absent'; $isPicket = false; $isDouble = false;

                    if (isset($individualSchedules[$emp->id][$date])) {
                        $scheds = $individualSchedules[$emp->id][$date]; $sched = $scheds->first();
                        if (in_array($sched->status, ['leave', 'sick'])) $status = ($sched->status === 'leave' ? 'on_leave' : 'sick');
                        elseif ($sched->status === 'off') $status = 'absent';
                        else {
                            $minStart = null; $maxEnd = null;
                            foreach($scheds as $s) {
                                if ($s->shift) {
                                    if (!$minStart || $s->shift->start_time < $minStart) $minStart = $s->shift->start_time;
                                    if (!$maxEnd || $s->shift->end_time > $maxEnd) $maxEnd = $s->shift->end_time;
                                }
                            }
                            $effectiveSched = (object)['start_time' => $minStart, 'end_time' => $maxEnd, 'name' => 'Individual'];
                            $isPicket = ($sched->status === 'picket');
                            $isDouble = $scheds->count() > 1;
                        }
                    } elseif ($emp->squad_id && isset($squadSchedules[$emp->squad_id][$date])) {
                        $scheds = $squadSchedules[$emp->squad_id][$date];
                        $minStart = null; $maxEnd = null; $hasPagi = false; $hasMalam = false;
                        foreach($scheds as $s) {
                            $st = $s->shift->start_time;
                            if ($s->shift && str_contains(strtoupper($s->shift->name), 'PAGI')) { $st = '06:00:00'; $hasPagi = true; }
                            if ($s->shift && str_contains(strtoupper($s->shift->name), 'MALAM')) $hasMalam = true;
                            if (!$minStart || $st < $minStart) $minStart = $st;
                            if (!$maxEnd || ($s->shift->end_time ?? '00:00:00') > $maxEnd) $maxEnd = $s->shift->end_time;
                        }
                        $effectiveSched = (object)['start_time' => $minStart, 'end_time' => $maxEnd, 'name' => 'Squad'];
                        $isPicket = true; $isDouble = ($hasPagi && $hasMalam) || $scheds->count() > 1;
                    } elseif (!$emp->squad_id || !isset($squadSchedules[$emp->squad_id]) || $squadSchedules[$emp->squad_id]->count() == 0) {
                        $dateObj = Carbon::parse($date); $dayNum = $dateObj->dayOfWeek;
                        if ($dayNum >= Carbon::MONDAY && $dayNum <= Carbon::FRIDAY) {
                            $inT = ($ramadanEnabled === 'on' && $dateObj->between($ramadanStart, $ramadanEnd)) ? ($settings['payroll_ramadan_staff_in'] ?? '08:00') : $staffIn;
                            $effectiveSched = (object)['start_time' => $inT.':00', 'end_time' => '16:00:00', 'name' => 'Office'];
                        }
                    }

                    $late = 0; $early = 0; $allowance = 0;
                    if ($effectiveSched && !in_array($status, ['on_leave', 'sick'])) {
                        $st = Carbon::parse($date . ' ' . $effectiveSched->start_time);
                        if ($checkIn->diffInHours($st, false) <= 2) {
                            if ($checkIn->gt($st)) { $late = $checkIn->diffInMinutes($st); $status = 'late'; }
                            else { $status = $isPicket ? 'picket' : 'present'; }
                            $allowance = (float)($emp->rank_relation->meal_allowance ?? 0) * ($isDouble ? 2 : 1);
                        }
                    }

                    $insertData[] = ['employee_id' => $emp->id, 'date' => $date, 'check_in' => $checkIn->format('H:i:s'), 'check_out' => $checkOut ? $checkOut->format('H:i:s') : null, 'status' => $status, 'late_minutes' => $late, 'early_minutes' => $early, 'allowance_amount' => $allowance, 'created_at' => $now, 'updated_at' => $now];
                    $importedCount++;
                }
            }
            if (!empty($insertData)) { foreach (array_chunk($insertData, 500) as $chunk) { Attendance::insert($chunk); } }
            AuditLog::create(['user_id' => auth()->id(), 'activity' => 'import_attendance', 'ip_address' => $request->ip(), 'details' => "Import Replace periode $minDate - $maxDate. Total $importedCount data."]);
            return back()->with('success', "Berhasil mereplace $importedCount data absensi.");
        } catch (\Exception $e) { return back()->with('error', 'Gagal: ' . $e->getMessage()); }
    }

    public function export(Request $request)
    {
        set_time_limit(600);
        ini_set('memory_limit', '2048M');
        $filter = $request->filter ?? 'range'; $type = $request->type ?? 'pdf';
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->endOfMonth()->format('Y-m-d');
        
        $query = Employee::query()->orderBy('full_name');
        if ($request->filled('employee_id')) $query->where('id', $request->employee_id);
        if ($request->filled('work_unit_id')) $query->where('work_unit_id', $request->work_unit_id);

        $squadSchedules = SquadSchedule::whereBetween('date', [$startDate, $endDate])->get()->groupBy('squad_id')->map(fn($g) => $g->groupBy(fn($i) => Carbon::parse($i->date)->format('Y-m-d')));
        $individualSchedules = Schedule::whereBetween('date', [$startDate, $endDate])->get()->groupBy('employee_id')->map(fn($g) => $g->groupBy(fn($i) => Carbon::parse($i->date)->format('Y-m-d')));
        
        $settings = Setting::where('key', 'like', 'payroll_%')->get()->pluck('value', 'key');

        $getMealMultiplier = function($emp, $date) use ($squadSchedules, $individualSchedules) {
            $dateStr = Carbon::parse($date)->format('Y-m-d');
            if (isset($individualSchedules[$emp->id][$dateStr])) return $individualSchedules[$emp->id][$dateStr]->count() > 1 ? 2 : 1;
            if ($emp->squad_id && isset($squadSchedules[$emp->squad_id][$dateStr])) {
                $scheds = $squadSchedules[$emp->squad_id][$dateStr];
                $hasPagi = false; $hasMalam = false;
                foreach($scheds as $s) {
                    if (str_contains(strtoupper($s->shift->name ?? ''), 'PAGI')) $hasPagi = true;
                    if (str_contains(strtoupper($s->shift->name ?? ''), 'MALAM')) $hasMalam = true;
                }
                return ($hasPagi && $hasMalam) || $scheds->count() > 1 ? 2 : 1;
            }
            return 1;
        };

        if ($filter === 'daily') {
            $exactDate = Carbon::parse($request->exact_date ?? now()); $dateStr = $exactDate->format('Y-m-d');
            $employees = $query->with('rank_relation')->get();
            $attendances = Attendance::whereDate('date', $exactDate)->get()->keyBy('employee_id');
            $data = $employees->map(function($emp) use ($attendances, $dateStr, $getMealMultiplier) {
                $att = $attendances->get($emp->id);
                return (object)['employee' => $emp, 'check_in' => $att?->check_in, 'check_out' => $att?->check_out, 'status' => $att?->status ?? 'absent', 'late_minutes' => $att?->late_minutes ?? 0, 'allowance_amount' => $att ? $att->allowance_amount : 0];
            });
            $reportTitle = "ABSENSI HARIAN - " . strtoupper($exactDate->translatedFormat('d F Y'));
            if ($type === 'excel') return $this->exportExcelDaily($data, $reportTitle, "absensi-harian-{$dateStr}.xlsx");
            if (ob_get_length()) ob_end_clean();
            return Pdf::loadView('admin.attendance.pdf-daily', ['logs' => $data, 'reportTitle' => $reportTitle, 'date' => $dateStr])->setPaper('a4', 'landscape')->download("absensi-harian-{$dateStr}.pdf");
        } elseif ($filter === 'range' || $filter === 'weekly' || $filter === 'monthly') {
            $start = Carbon::parse($startDate); $end = Carbon::parse($endDate);
            $employees = $query->with('rank_relation')->get();
            $attendances = Attendance::whereBetween('date', [$startDate, $endDate])->get()->groupBy('employee_id');
            $data = $employees->map(function($emp) use ($attendances) {
                $atts = $attendances->get($emp->id) ?? collect();
                return (object)['full_name' => strtoupper($emp->full_name), 'nip' => $emp->nip, 'present_count' => $atts->whereNotIn('status', ['absent'])->count(), 'late_count' => $atts->where('status', 'late')->count(), 'total_allowance' => $atts->sum('allowance_amount')];
            });
            $reportTitle = "REKAP ABSENSI (" . $start->format('d/m/Y') . " - " . $end->format('d/m/Y') . ")";
            if ($type === 'excel') return $this->exportExcelMonthly($data, $reportTitle, "rekap-absensi.xlsx");
            if (ob_get_length()) ob_end_clean();
            return Pdf::loadView('admin.attendance.pdf-monthly', ['logs' => $data, 'reportTitle' => $reportTitle, 'startDate' => $startDate, 'endDate' => $endDate])->setPaper('a4', 'landscape')->download("rekap-absensi.pdf");
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
