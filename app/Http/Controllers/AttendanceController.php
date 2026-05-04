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
            ->map(fn($g) => $g->keyBy(fn($i) => Carbon::parse($i->date)->format('Y-m-d')));

        $individualSchedules = Schedule::with('shift')->whereBetween('date', [$startDate, $endDate])
            ->get()->groupBy('employee_id')
            ->map(fn($g) => $g->keyBy(fn($i) => Carbon::parse($i->date)->format('Y-m-d')));

        $staffInTime = \App\Models\Setting::getValue('payroll_staff_in', '07:30');
        $staffSatEnabled = \App\Models\Setting::getValue('payroll_staff_saturday_enabled', 'off');
        $staffSatIn = \App\Models\Setting::getValue('payroll_staff_saturday_in', '07:30');
        
        $ramadanEnabled = \App\Models\Setting::getValue('payroll_ramadan_enabled', 'off');
        $ramadanStart = Carbon::parse(\App\Models\Setting::getValue('payroll_ramadan_start', date('Y-m-d')))->startOfDay();
        $ramadanEnd = Carbon::parse(\App\Models\Setting::getValue('payroll_ramadan_end', date('Y-m-d')))->endOfDay();
        $ramadanIn = \App\Models\Setting::getValue('payroll_ramadan_staff_in', '08:00');
        $ramadanSatEnabled = \App\Models\Setting::getValue('payroll_ramadan_saturday_enabled', 'off');
        $ramadanSatIn = \App\Models\Setting::getValue('payroll_ramadan_saturday_in', '08:00');

        // Helper to get effective shift start time for a date
        $getScheduledStartTime = function($emp, $date) use ($squadSchedules, $individualSchedules, $staffInTime, $staffSatEnabled, $staffSatIn, $ramadanEnabled, $ramadanStart, $ramadanEnd, $ramadanIn, $ramadanSatEnabled, $ramadanSatIn) {
            $dateStr = Carbon::parse($date)->format('Y-m-d');
            $dateObj = Carbon::parse($date);
            
            // 1. Individual Priority
            if (isset($individualSchedules[$emp->id][$dateStr])) {
                $sched = $individualSchedules[$emp->id][$dateStr];
                if (in_array($sched->status, ['off', 'leave', 'sick'])) return null;
                return $sched->shift->start_time ?? null;
            }
            
            // 2. Squad Priority (Regu/P2U)
            if ($emp->squad_id && isset($squadSchedules[$emp->squad_id][$dateStr])) {
                $st = $squadSchedules[$emp->squad_id][$dateStr]->shift->start_time ?? null;
                
                // Force 06:00 for Morning Guard Shifts to ensure consistency
                if ($st && str_contains(strtoupper($squadSchedules[$emp->squad_id][$dateStr]->shift->name ?? ''), 'PAGI')) {
                    return '06:00:00';
                }
                
                // Fallback jika shift_id ada tapi data waktu kosong
                return $st ?? '06:00:00';
            }
            
            // 3. Default Staff Fallback (Office Hours)
            // BERLAKU UNTUK SEMUA yang tidak punya jadwal regu/individu pada hari kerja
            $dayNum = $dateObj->dayOfWeek;
            $isRamadan = ($ramadanEnabled === 'on' && $dateObj->between($ramadanStart, $ramadanEnd));

            if ($dayNum >= Carbon::MONDAY && $dayNum <= Carbon::FRIDAY) {
                return $isRamadan ? $ramadanIn : $staffInTime;
            }
            
            // Saturday Logic
            if ($dayNum === Carbon::SATURDAY) {
                if ($isRamadan && $ramadanSatEnabled === 'on') {
                    return $ramadanSatIn;
                } else if (!$isRamadan && $staffSatEnabled === 'on') {
                    return $staffSatIn;
                }
            }
            
            return null;
        };

        $checkIsScheduled = function($emp, $date) use ($getScheduledStartTime) {
            return !is_null($getScheduledStartTime($emp, $date));
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
                    
                    $effectiveStart = $getScheduledStartTime($emp, $att->date);

                    if ($effectiveStart) {
                        // LOGIKA TELAT DINAMIS UNTUK SUMMARY
                        $isLate = ($att->status === 'late');
                        if (!$isLate && $att->check_in) {
                            $checkInOnly = date('H:i', strtotime($att->check_in));
                            $targetStart = date('H:i', strtotime($effectiveStart));
                            if ($checkInOnly > $targetStart) $isLate = true;
                        }

                        if ($isLate) {
                            $totalLate++;
                        }
                        $empValidDays++;
                        
                        if ($att->status !== 'duty_full' && $att->status !== 'tubel') {
                            $empTotalAllowance += $empRate;
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

        $attendanceLogs->getCollection()->transform(function($log) use ($getScheduledStartTime) {
            $emp = $log->employee;
            $effectiveStart = $getScheduledStartTime($emp, $log->date);
            $isScheduled = !is_null($effectiveStart);
            
            $hasMeal = $isScheduled && !in_array($log->status, ['absent', 'duty_full', 'tubel', 'on_leave', 'sick']);
            $log->allowance_amount = $hasMeal ? ($emp->rank_relation->meal_allowance ?? 0) : 0;

            if ($log->check_in && $isScheduled && !in_array($log->status, ['absent', 'duty_full', 'tubel', 'on_leave', 'sick'])) {
                $checkInTime = date('H:i', strtotime($log->check_in));
                $targetInTime = date('H:i', strtotime($effectiveStart));

                if ($checkInTime > $targetInTime) {
                    $log->status = 'late';
                    $dateOnly = Carbon::parse($log->date)->format('Y-m-d');
                    $startTime = Carbon::parse($dateOnly . ' ' . $targetInTime);
                    $actualIn = Carbon::parse($dateOnly . ' ' . date('H:i:s', strtotime($log->check_in)));
                    
                    // Hitung selisih mutlak agar selalu positif
                    $log->late_minutes = (int)$actualIn->diffInMinutes($startTime);
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

            $scansByNip = [];
            $allDates = [];
            
            // Default mapping berdasarkan contoh user
            $map = ['nip' => 4, 'date' => 1, 'time' => 2, 'datetime' => 0];
            $dataStarted = false;

            foreach ($data as $index => $row) {
                // 1. Deteksi Header (Cari baris yang mengandung NIP/Tanggal)
                if (!$dataStarted) {
                    $rowNormalized = array_map(fn($v) => strtoupper(str_replace(' ', '', trim((string)$v))), $row);
                    
                    if (in_array('NIP', $rowNormalized) || in_array('TANGGALSCAN', $rowNormalized)) {
                        foreach ($rowNormalized as $colIdx => $h) {
                            if ($h === 'NIP') $map['nip'] = $colIdx;
                            if ($h === 'TANGGAL' || $h === 'TANGGALSCAN') $map['date'] = $colIdx;
                            if ($h === 'JAM') $map['time'] = $colIdx;
                            if ($h === 'TANGGALSCAN') $map['datetime'] = $colIdx;
                        }
                        \Log::info("Header detected at row $index", ['map' => $map]);
                        $dataStarted = true;
                        continue;
                    }
                    
                    if ($index >= 10) $dataStarted = true;
                    else continue;
                }

                $nipRaw = trim((string)($row[$map['nip']] ?? ''));
                if (empty($nipRaw)) continue;

                // Bersihkan NIP: Hanya ambil angka
                $nip = preg_replace('/[^0-9]/', '', $nipRaw);
                if (empty($nip)) continue;

                try {
                    $d = trim((string)($row[$map['date']] ?? ''));
                    $t = trim((string)($row[$map['time']] ?? ''));
                    $dt = trim((string)($row[$map['datetime']] ?? ''));
                    
                    if (!empty($d) && !empty($t)) {
                        $scanTime = Carbon::parse($d . ' ' . $t);
                    } elseif (!empty($dt)) {
                        $scanTime = Carbon::parse($dt);
                    } elseif (!empty($d)) {
                        $scanTime = Carbon::parse($d);
                    } else {
                        continue;
                    }
                    
                    $scansByNip[$nip][] = $scanTime;
                    $allDates[] = $scanTime->format('Y-m-d');
                } catch (\Exception $e) {
                    \Log::warning("Failed to parse date/time at row $index: " . $e->getMessage());
                    continue;
                }
            }

            if (empty($scansByNip)) {
                \Log::error("Import failed: No scans found in file.");
                return back()->with('error', 'Gagal membaca data scan. Pastikan file sesuai format.');
            }

            \Log::info("Found " . count($scansByNip) . " unique NIPs in Excel.");

            // Ambil data pegawai
            $excelNips = array_keys($scansByNip);
            $employees = Employee::with(['rank_relation', 'squad'])->whereIn('nip', $excelNips)->get()->keyBy('nip');
            
            \Log::info("Matched " . $employees->count() . " employees by direct NIP.");

            if ($employees->count() < count($scansByNip)) {
                $missingNips = array_diff($excelNips, $employees->keys()->toArray());
                \Log::info("NIPs not matched yet: " . implode(', ', $missingNips));
                
                $moreEmps = Employee::with(['rank_relation', 'squad'])->where(function($q) use ($missingNips) {
                    foreach($missingNips as $n) { $q->orWhere('nip', 'like', "%$n"); }
                })->get();

                foreach($moreEmps as $me) {
                    foreach($missingNips as $mn) {
                        if (str_ends_with($me->nip, $mn) || str_ends_with($mn, $me->nip)) {
                            $employees->put($mn, $me);
                            break;
                        }
                    }
                }
            }

            \Log::info("Total employees matched after fallback: " . $employees->count());

            if ($employees->isEmpty()) return back()->with('error', 'NIP di Excel tidak ada yang cocok dengan Database Pegawai.');

            $minDate = collect($allDates)->min();
            $maxDate = collect($allDates)->max();
            $empIds = $employees->pluck('id')->toArray();
            $squadIds = $employees->pluck('squad_id')->filter()->unique()->toArray();

            // --- OPTIMASI: Pre-fetch jadwal untuk menghindari ribuan query dalam loop ---
            $individualSchedules = Schedule::with('shift')
                ->whereIn('employee_id', $empIds)
                ->whereBetween('date', [$minDate, $maxDate])
                ->get()
                ->groupBy('employee_id')
                ->map(fn($g) => $g->keyBy(fn($i) => \Carbon\Carbon::parse($i->date)->format('Y-m-d')));

            $squadSchedules = SquadSchedule::with('shift')
                ->whereIn('squad_id', $squadIds)
                ->whereBetween('date', [$minDate, $maxDate])
                ->get()
                ->groupBy('squad_id')
                ->map(fn($g) => $g->keyBy(fn($i) => \Carbon\Carbon::parse($i->date)->format('Y-m-d')));
            
            $staffInTime = Setting::getValue('payroll_staff_in', '07:30');
            $staffOutMonThu = Setting::getValue('payroll_staff_out_mon_thu', '16:00');
            $staffOutFri = Setting::getValue('payroll_staff_out_fri', '16:30');
            $staffSatEnabled = Setting::getValue('payroll_staff_saturday_enabled', 'off');
            $staffSatIn = Setting::getValue('payroll_staff_saturday_in', '07:30');
            $staffOutSat = Setting::getValue('payroll_staff_saturday_out', '12:00');
            
            $ramadanEnabled = Setting::getValue('payroll_ramadan_enabled', 'off');
            $ramadanStart = Carbon::parse(Setting::getValue('payroll_ramadan_start', date('Y-m-d')))->startOfDay();
            $ramadanEnd = Carbon::parse(Setting::getValue('payroll_ramadan_end', date('Y-m-d')))->endOfDay();
            $ramadanIn = Setting::getValue('payroll_ramadan_staff_in', '08:00');
            $ramadanOutMonThu = Setting::getValue('payroll_ramadan_staff_out_mon_thu', '15:00');
            $ramadanOutFri = Setting::getValue('payroll_ramadan_staff_out_fri', '15:30');
            $ramadanSatEnabled = Setting::getValue('payroll_ramadan_saturday_enabled', 'off');
            $ramadanSatIn = Setting::getValue('payroll_ramadan_saturday_in', '08:00');
            $ramadanSatOut = Setting::getValue('payroll_ramadan_saturday_out', '12:00');
            // -------------------------------------------------------------------------

            // REPLACE: Hapus data lama sebelum insert
            Attendance::whereIn('employee_id', $empIds)
                ->whereBetween('date', [$minDate, $maxDate])
                ->delete();

            $now = now();
            $insertData = [];
            $importedCount = 0;

            foreach ($employees as $dbNip => $emp) {
                $excelKey = null;
                foreach (array_keys($scansByNip) as $k) {
                    if (str_ends_with($dbNip, $k) || str_ends_with($k, $dbNip)) { $excelKey = $k; break; }
                }
                if (!$excelKey) continue;

                $empScans = collect($scansByNip[$excelKey])->sort();
                foreach ($empScans->groupBy(fn($s) => $s->format('Y-m-d')) as $date => $dayScans) {
                    $checkIn = $dayScans->min();
                    $checkOut = $dayScans->max();
                    if ($checkIn->equalTo($checkOut)) $checkOut = null;

                    // --- LOGIKA VALIDASI OPTIMIZED (Tanpa Query di dalam Loop) ---
                    $effectiveSched = null;
                    $status = 'absent';
                    $isPicket = false;

                    // 1. Cek Individu
                    if (isset($individualSchedules[$emp->id][$date])) {
                        $sched = $individualSchedules[$emp->id][$date];
                        $effectiveSched = ['shift' => $sched->shift, 'status' => $sched->status];
                        $isPicket = ($sched->status === 'picket');
                        if (in_array($sched->status, ['leave', 'sick'])) $status = ($sched->status === 'leave' ? 'on_leave' : 'sick');
                        elseif ($sched->status === 'off') $status = 'absent';
                    } 
                    // 2. Cek Regu
                    elseif ($emp->squad_id && isset($squadSchedules[$emp->squad_id][$date])) {
                        $sched = $squadSchedules[$emp->squad_id][$date];
                        $effectiveSched = ['shift' => $sched->shift];
                        $isPicket = true;
                    }
                    
                    // 3. Default Staff Fallback (Office Hours)
                    // Jika tidak ada jadwal regu/individu, gunakan jam kantor jika hari kerja
                    if (!$effectiveSched && $status === 'absent') {
                        $dateObj = Carbon::parse($date);
                        $dayNum = $dateObj->dayOfWeek;
                        $isRamadan = ($ramadanEnabled === 'on' && $dateObj->between($ramadanStart, $ramadanEnd));

                        if (($dayNum >= Carbon::MONDAY && $dayNum <= Carbon::FRIDAY) || ($dayNum === Carbon::SATURDAY && $staffSatEnabled === 'on') || ($isRamadan && $dayNum === Carbon::SATURDAY && $ramadanSatEnabled === 'on')) {
                            
                            if ($dayNum === Carbon::SATURDAY) {
                                if ($isRamadan && $ramadanSatEnabled === 'on') {
                                    $inT = $ramadanSatIn;
                                    $outT = $ramadanSatOut;
                                } else if (!$isRamadan && $staffSatEnabled === 'on') {
                                    $inT = $staffSatIn;
                                    $outT = $staffOutSat;
                                } else {
                                    $inT = null;
                                }
                            } else {
                                if ($isRamadan) {
                                    $inT = $ramadanIn;
                                    $outT = ($dayNum === Carbon::FRIDAY) ? $ramadanOutFri : $ramadanOutMonThu;
                                } else {
                                    $inT = $staffInTime;
                                    $outT = ($dayNum === Carbon::FRIDAY) ? $staffOutFri : $staffOutMonThu;
                                }
                            }

                            if ($inT) {
                                $effectiveSched = ['shift' => (object)[
                                    'start_time' => $inT . ':00',
                                    'end_time' => $outT . ':00'
                                ]];
                            }
                        }
                    }

                    $late = 0; $early = 0; $allowance = 0;
                    
                    if ($effectiveSched && !in_array($status, ['on_leave', 'sick'])) {
                        $shift = $effectiveSched['shift'];
                        if ($shift) {
                            $st = Carbon::parse($date . ' ' . $shift->start_time);
                            // Validasi jam masuk (toleransi 2 jam sebelum mulai)
                            if ($checkIn->diffInHours($st, false) <= 2) {
                                if ($checkIn->gt($st)) {
                                    $late = $checkIn->diffInMinutes($st);
                                    $status = 'late';
                                } else {
                                    $status = $isPicket ? 'picket' : 'present';
                                }
                                
                                if ($checkOut) {
                                    $et = Carbon::parse($date . ' ' . $shift->end_time);
                                    if ($checkOut->lt($et)) $early = $et->diffInMinutes($checkOut);
                                }
                                $allowance = $emp->rank_relation->meal_allowance ?? 0;
                            }
                        }
                    }
                    // -------------------------------------------------------------

                    $insertData[] = [
                        'employee_id' => $emp->id,
                        'date' => $date,
                        'check_in' => $checkIn->format('H:i:s'),
                        'check_out' => $checkOut ? $checkOut->format('H:i:s') : null,
                        'status' => $status,
                        'late_minutes' => $late,
                        'early_minutes' => $early,
                        'allowance_amount' => $allowance,
                        'created_at' => $now, 'updated_at' => $now,
                    ];
                    $importedCount++;
                }
            }

            if (!empty($insertData)) {
                foreach (array_chunk($insertData, 500) as $chunk) { Attendance::insert($chunk); }
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'activity' => 'import_attendance',
                'ip_address' => $request->ip(),
                'details' => "Import Replace periode $minDate - $maxDate. Total $importedCount data."
            ]);

            return back()->with('success', "Berhasil mereplace $importedCount data absensi ($minDate s/d $maxDate).");
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

        $staffSatEnabled = Setting::getValue('payroll_staff_saturday_enabled', 'off');
        $ramadanEnabled = Setting::getValue('payroll_ramadan_enabled', 'off');
        $ramadanStart = Carbon::parse(Setting::getValue('payroll_ramadan_start', date('Y-m-d')))->startOfDay();
        $ramadanEnd = Carbon::parse(Setting::getValue('payroll_ramadan_end', date('Y-m-d')))->endOfDay();
        $ramadanSatEnabled = Setting::getValue('payroll_ramadan_saturday_enabled', 'off');

        // Helper logic to determine if an employee is scheduled on a specific date
        $checkIsScheduled = function($emp, $date) use ($squadSchedules, $individualSchedules, $staffSatEnabled, $ramadanEnabled, $ramadanStart, $ramadanEnd, $ramadanSatEnabled) {
            $dateStr = Carbon::parse($date)->format('Y-m-d');
            $dateObj = Carbon::parse($date);
            
            // 1. Individual Override (Highest Priority)
            if (isset($individualSchedules[$emp->id][$dateStr])) {
                return !in_array($individualSchedules[$emp->id][$dateStr]->status, ['off', 'leave', 'sick']);
            }
            
            // 2. Squad Schedule
            if ($emp->squad_id && isset($squadSchedules[$emp->squad_id]) && in_array($dateStr, $squadSchedules[$emp->squad_id])) {
                return true;
            }
            
            // 3. Default Office Fallback (Staff hours)
            // Berlaku jika tidak ada jadwal regu/individu pada hari kerja
            $dayNum = $dateObj->dayOfWeek;
            $isRamadan = ($ramadanEnabled === 'on' && $dateObj->between($ramadanStart, $ramadanEnd));

            if ($dayNum >= Carbon::MONDAY && $dayNum <= Carbon::FRIDAY) return true;
            
            if ($dayNum === Carbon::SATURDAY) {
                if ($isRamadan && $ramadanSatEnabled === 'on') return true;
                if (!$isRamadan && $staffSatEnabled === 'on') return true;
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
                    'late_count' => $atts->filter(fn($a) => $a->status === 'late' || $a->late_minutes > 0)->count(),
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
