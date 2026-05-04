<?php
// VERSION 5.0 - ADDED RAMADAN SATURDAY LOGIC

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Schedule;
use App\Models\SquadSchedule;
use App\Models\Setting;
use Carbon\Carbon;

class PayrollService
{
    /**
     * Menghitung rincian Tukin dan Uang Makan untuk satu pegawai dalam satu bulan.
     * Menggunakan kalkulasi Real-time yang disinkronkan dengan data absensi.
     */
    public function calculateMonthlyPayroll(Employee $employee, $monthStr)
    {
        $date = Carbon::parse($monthStr . '-01');
        $startDate = $date->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $date->copy()->endOfMonth()->format('Y-m-d');
        $daysInMonth = $date->daysInMonth;

        // Ambil semua setting sekaligus
        $allSettings = Setting::where('key', 'like', 'payroll_%')->get()->pluck('value', 'key');
        
        $rules = [
            'tl_1' => (float)($allSettings['payroll_tl_1_percent'] ?? 0.5),
            'tl_2' => (float)($allSettings['payroll_tl_2_percent'] ?? 1.0),
            'tl_3' => (float)($allSettings['payroll_tl_3_percent'] ?? 1.25),
            'tl_4' => (float)($allSettings['payroll_tl_4_percent'] ?? 1.5),
            'max_late' => (int)($allSettings['payroll_max_late_count'] ?? 8),
            'mangkir' => (float)($allSettings['payroll_mangkir_percent'] ?? 5.0),
            'lupa_absen' => (float)($allSettings['payroll_lupa_absen_percent'] ?? 1.5),
            'sakit_3_6' => (float)($allSettings['payroll_sakit_3_6_percent'] ?? 2.5),
            'sakit_7' => (float)($allSettings['payroll_sakit_7_plus_percent'] ?? 10.0),
            'staff_in' => $allSettings['payroll_staff_in'] ?? '07:30',
            'staff_out_mon_thu' => $allSettings['payroll_staff_out_mon_thu'] ?? '16:00',
            'staff_out_fri' => $allSettings['payroll_staff_out_fri'] ?? '16:30',
            'staff_saturday_enabled' => $allSettings['payroll_staff_saturday_enabled'] ?? 'off',
            'staff_saturday_in' => $allSettings['payroll_staff_saturday_in'] ?? '07:30',
            'staff_saturday_out' => $allSettings['payroll_staff_saturday_out'] ?? '12:00',
            
            // Jam Kerja Staff (Bulan Puasa)
            'ramadan_enabled' => $allSettings['payroll_ramadan_enabled'] ?? 'off',
            'ramadan_start' => $allSettings['payroll_ramadan_start'] ?? date('Y-m-d'),
            'ramadan_end' => $allSettings['payroll_ramadan_end'] ?? date('Y-m-d'),
            'ramadan_staff_in' => $allSettings['payroll_ramadan_staff_in'] ?? '08:00',
            'ramadan_staff_out_mon_thu' => $allSettings['payroll_ramadan_staff_out_mon_thu'] ?? '15:00',
            'ramadan_staff_out_fri' => $allSettings['payroll_ramadan_staff_out_fri'] ?? '15:30',
            'ramadan_saturday_enabled' => $allSettings['payroll_ramadan_saturday_enabled'] ?? 'off',
            'ramadan_saturday_in' => $allSettings['payroll_ramadan_saturday_in'] ?? '08:00',
            'ramadan_saturday_out' => $allSettings['payroll_ramadan_saturday_out'] ?? '12:00',
        ];
        
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy(fn($item) => Carbon::parse($item->date)->format('Y-m-d'));

        $individualSchedules = Schedule::with('shift')
            ->where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy(fn($i) => Carbon::parse($i->date)->format('Y-m-d'));
            
        // Ambil semua jadwal regu untuk bulan ini
        $squadSchedules = [];
        if ($employee->squad_id) {
            $squadSchedules = SquadSchedule::with('shift')
                ->where('squad_id', $employee->squad_id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get()
                ->groupBy(fn($s) => Carbon::parse($s->date)->format('Y-m-d'));
        }
        
        $holidays = \App\Models\Holiday::whereBetween('date', [$startDate, $endDate])
            ->pluck('date')->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))->toArray();

        $hasAnySquadSchedule = $employee->squad_id && $squadSchedules->count() > 0;

        $stats = [
            'total_present' => 0,
            'late_count' => 0,
            'compensation_count' => 0,
            'deduction_percentage' => 0.0,
            'meal_allowance_days' => 0,
            'details' => [],
            'processed_logs' => [],
            'violation_note' => null,
            'is_tubel' => (bool)$employee->is_tubel,
            'is_cpns' => (bool)$employee->is_cpns,
            'is_acting' => false
        ];
        
        $sickCounter = 0;
        $baseTunkin = (float)($employee->tunkin->nominal ?? 0);
        if ($employee->is_cpns) $baseTunkin = 0.8 * $baseTunkin;

        $actingBonus = 0;
        if ($employee->acting_tunkin_id && $employee->acting_start_date) {
            $startDateObj = Carbon::parse($employee->acting_start_date);
            if ($date->diffInMonths($startDateObj) >= 1) {
                $actingTunkin = $employee->actingTunkin->nominal ?? 0;
                $actingBonus = 0.2 * $actingTunkin;
                $stats['is_acting'] = true;
            }
        }

        if ($employee->is_tubel) {
            $stats['deduction_percentage'] = 100;
            $stats['details'][] = ['type' => 'Tugas Belajar', 'info' => 'Potong 100%', 'date' => null, 'percent' => 100, 'rupiah' => $baseTunkin];
            $baseTunkin = 0;
            $actingBonus = 0;
        }

        $mealRate = (float)($employee->rank_relation->meal_allowance ?? 0);
        $today = now()->startOfDay();

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $currentDateObj = $date->copy()->day($d);
            $currentDate = $currentDateObj->format('Y-m-d');
            $dayOfWeek = $currentDateObj->dayOfWeek;
            $isFuture = $currentDateObj->isAfter($today);
            
            $attendance = $attendances->get($currentDate);
            
            $isScheduled = false;
            $scheduledInTime = null;
            $scheduledOutTime = null;
            $specialStatus = null; 
            $isDefaultOffice = false;
            $isDoubleShift = false;
            $isNightShift = false;

            if ($individualSchedules->has($currentDate)) {
                $indivs = $individualSchedules->get($currentDate); // Wait, individualSchedules was grouped?
                // PayrollService v5 had keyBy, but I need to support multiple?
                // Actually individuals are rarely multiple, but let's be safe.
                $indiv = is_iterable($indivs) ? $indivs->first() : $indivs;
                
                $specialStatus = $indiv->status ?? 'picket';
                $isScheduled = !in_array($specialStatus, ['off', 'leave', 'sick']);
                $scheduledInTime = $indiv->shift->start_time ?? null;
                $scheduledOutTime = $indiv->shift->end_time ?? null;
                
                foreach((is_iterable($indivs) ? $indivs : [$indivs]) as $indivSched) {
                    if ($indivSched->shift && str_contains(strtoupper($indivSched->shift->name ?? ''), 'MALAM')) {
                        $isNightShift = true;
                    }
                }
            } elseif ($employee->squad_id && isset($squadSchedules[$currentDate])) {
                $isScheduled = true;
                $dayScheds = $squadSchedules[$currentDate];
                
                $minIn = null; $maxOut = null; $hasPagi = false; $hasMalam = false;
                foreach($dayScheds as $s) {
                    $st = $s->shift->start_time ?? '06:00:00';
                    if ($s->shift && str_contains(strtoupper($s->shift->name), 'PAGI')) { $st = '06:00:00'; $hasPagi = true; }
                    if ($s->shift && str_contains(strtoupper($s->shift->name), 'MALAM')) { $hasMalam = true; $isNightShift = true; }
                    
                    if (!$minIn || $st < $minIn) $minIn = $st;
                    if (!$maxOut || ($s->shift->end_time ?? '00:00:00') > $maxOut) $maxOut = $s->shift->end_time;
                }
                
                $scheduledInTime = $minIn;
                $scheduledOutTime = $maxOut;
                $isDoubleShift = ($hasPagi && $hasMalam) || ($dayScheds->count() > 1);

            } elseif (!$employee->squad_id || !$hasAnySquadSchedule) {
                // Fallback Staff (Office) logic ...
                $isRamadan = false;
                if ($rules['ramadan_enabled'] === 'on') {
                    $ramadanStart = Carbon::parse($rules['ramadan_start'])->startOfDay();
                    $ramadanEnd = Carbon::parse($rules['ramadan_end'])->endOfDay();
                    if ($currentDateObj->between($ramadanStart, $ramadanEnd)) $isRamadan = true;
                }

                if (($dayOfWeek >= Carbon::MONDAY && $dayOfWeek <= Carbon::FRIDAY) || ($dayOfWeek === Carbon::SATURDAY && $rules['staff_saturday_enabled'] === 'on')) {
                    $isScheduled = true;
                    $isDefaultOffice = true;
                    if ($dayOfWeek === Carbon::SATURDAY) {
                        if ($isRamadan && $rules['ramadan_saturday_enabled'] === 'on') {
                            $scheduledInTime = $rules['ramadan_saturday_in'];
                            $scheduledOutTime = $rules['ramadan_saturday_out'];
                        } else if (!$isRamadan) {
                            $scheduledInTime = $rules['staff_saturday_in'];
                            $scheduledOutTime = $rules['staff_saturday_out'];
                        } else { $isScheduled = false; $isDefaultOffice = false; }
                    } else {
                        if ($isRamadan) {
                            $scheduledInTime = $rules['ramadan_staff_in'];
                            $scheduledOutTime = ($dayOfWeek === Carbon::FRIDAY) ? $rules['ramadan_staff_out_fri'] : $rules['ramadan_staff_out_mon_thu'];
                        } else {
                            $scheduledInTime = $rules['staff_in'];
                            $scheduledOutTime = ($dayOfWeek === Carbon::FRIDAY) ? $rules['staff_out_fri'] : $rules['staff_out_mon_thu'];
                        }
                    }
                }
            }

            // ... (Special status processing duty_full etc) ...

            if ($attendance) {
                $status = $attendance->status;
                $canReevaluate = in_array($status, ['absent', 'present', 'late']);
                
                $checkInStr = $attendance->check_in ? (is_string($attendance->check_in) ? $attendance->check_in : $attendance->check_in->format('H:i:s')) : null;

                // Re-evaluate logic (Sync with AttendanceController)
                if ($checkInStr && $isScheduled && $canReevaluate) {
                    if ($scheduledInTime) {
                        $actualTimestamp = strtotime($currentDate . ' ' . $checkInStr);
                        $targetTimestamp = strtotime($currentDate . ' ' . $scheduledInTime);
                        $diffMin = (int) ceil(($actualTimestamp - $targetTimestamp) / 60);

                        if ($diffMin >= -180) {
                            if ($diffMin > 0) {
                                $status = 'late';
                            } else {
                                $status = $employee->squad_id ? 'picket' : 'present';
                            }
                        } else {
                            $status = 'absent'; // Sangat pagi = salah shift = mangkir
                        }
                    }
                } elseif ($checkInStr && !$isScheduled && $canReevaluate) {
                    $status = 'present'; // Hari libur tapi absen
                }

                $isEligibleMeal = in_array($status, ['present', 'late', 'duty_half', 'picket']) && $isScheduled;
                
                $dailyMealAmount = $isEligibleMeal ? ($isDoubleShift ? $mealRate * 2 : $mealRate) : 0;

                $stats['processed_logs'][] = [
                    'date' => $currentDate, 
                    'status' => $status, 
                    'check_in' => $checkInStr ?: '--:--', 
                    'check_out' => $attendance->check_out ? (is_string($attendance->check_out) ? $attendance->check_out : $attendance->check_out->format('H:i:s')) : '--:--', 
                    'is_scheduled' => $isScheduled, 
                    'meal_amount' => $dailyMealAmount
                ];

                if ($isEligibleMeal) { 
                    $stats['meal_allowance_days'] += ($isDoubleShift ? 2 : 1); 
                    $stats['total_present']++; 
                }
                
                // ... (TL/PSW calculation unchanged but now uses correct scheduledInTime) ...

                if ($isScheduled) {
                    $lateMin = 0;
                    
                    if ($checkInStr && $scheduledInTime) {
                        $actualTimestamp = strtotime($currentDate . ' ' . $checkInStr);
                        $targetTimestamp = strtotime($currentDate . ' ' . $scheduledInTime);
                        $diffMin = (int) ceil(($actualTimestamp - $targetTimestamp) / 60);

                        // Tolerance: Max 3 hours early (180 mins)
                        if ($diffMin >= -180) {
                            if ($diffMin > 0) $lateMin = $diffMin;
                        }
                    }

                    // --- Pencatatan TL yang Persisten ---
                    if ($lateMin > 0) {
                        $p = $this->getLatePSWPercentage($lateMin, $rules);
                        $canCompensate = false;
                        
                        if ($lateMin <= 30 && $stats['compensation_count'] < 8 && $attendance->check_out && $scheduledOutTime) {
                            $actualOut = strtotime($currentDate . ' ' . date('H:i:s', strtotime($attendance->check_out)));
                            $requiredOut = strtotime($currentDate . ' ' . $scheduledOutTime) + 1800; // +30 mins
                            if ($actualOut >= $requiredOut) $canCompensate = true;
                        }

                        if ($canCompensate) {
                            $stats['compensation_count']++;
                            $stats['details'][] = ['type' => 'TL Diganti (Kompensasi)', 'info' => "Telat {$lateMin}m, Ganti Pulang", 'date' => $currentDate, 'percent' => 0, 'rupiah' => 0];
                        } else {
                            $stats['late_count']++;
                            $stats['deduction_percentage'] += $p;
                            $label = $isDefaultOffice ? 'Terlambat (TL)' : 'Terlambat (TL Shift)';
                            $targetDisplay = $scheduledInTime ? date('H:i', strtotime($scheduledInTime)) : '--:--';
                            $stats['details'][] = [
                                'type' => $label, 
                                'info' => "{$lateMin}m (Jadwal: {$targetDisplay})", 
                                'date' => $currentDate, 
                                'percent' => $p, 
                                'rupiah' => ($p / 100) * $baseTunkin
                            ];
                        }
                    }

                    // 3. PSW & Lupa Absen
                    $earlyMin = abs((int)($attendance->early_minutes ?? 0));
                    if ($earlyMin > 0) {
                        $p = $this->getLatePSWPercentage($earlyMin, $rules);
                        $stats['deduction_percentage'] += $p;
                        $stats['details'][] = ['type' => 'Pulang Cepat (PSW)', 'info' => "{$earlyMin}m", 'date' => $currentDate, 'percent' => $p, 'rupiah' => ($p / 100) * $baseTunkin];
                    }

                    if (in_array($status, ['present', 'late']) && (empty($attendance->check_in) || empty($attendance->check_out) || $attendance->check_in == $attendance->check_out)) {
                        // Skip "Lupa Absen Pulang" deduction if this is a night shift
                        if ($isNightShift && !empty($attendance->check_in) && empty($attendance->check_out)) {
                            // Do nothing, it's expected for night shifts
                        } else {
                            $stats['deduction_percentage'] += $rules['lupa_absen'];
                            $stats['details'][] = ['type' => 'Lupa Absen', 'info' => (empty($attendance->check_in) ? "Tanpa Masuk" : "Tanpa Pulang"), 'date' => $currentDate, 'percent' => $rules['lupa_absen'], 'rupiah' => ($rules['lupa_absen'] / 100) * $baseTunkin];
                        }
                    }
                } else if ($attendance && in_array($attendance->status, ['present', 'late'])) {
                    $lateMinFallback = abs((int)($attendance->late_minutes ?? 0));
                    if ($lateMinFallback > 0) {
                        $stats['late_count']++;
                        $p = $this->getLatePSWPercentage($lateMinFallback, $rules);
                        $stats['deduction_percentage'] += $p;
                        $stats['details'][] = ['type' => 'Terlambat (TL)', 'info' => "{$lateMinFallback}m (Tercatat)", 'date' => $currentDate, 'percent' => $p, 'rupiah' => ($p / 100) * $baseTunkin];
                    }
                }

                if ($status === 'sick') {
                    $sickCounter++;
                    $p = ($sickCounter >= 3 && $sickCounter <= 6) ? $rules['sakit_3_6'] : (($sickCounter >= 7) ? $rules['sakit_7'] : 0);
                    if ($p > 0) { $stats['deduction_percentage'] += $p; $stats['details'][] = ['type' => 'Sakit Progresif', 'info' => "Hari ke-{$sickCounter}", 'date' => $currentDate, 'percent' => $p, 'rupiah' => ($p / 100) * $baseTunkin]; }
                }

                if ($status === 'absent') {
                    $p = $rules['mangkir'];
                    $stats['deduction_percentage'] += $p;
                    $stats['details'][] = ['type' => 'Mangkir', 'info' => "Tanpa Keterangan", 'date' => $currentDate, 'percent' => $p, 'rupiah' => ($p / 100) * $baseTunkin];
                }
            } else {
                if ($isScheduled && !$isFuture) {
                    $p = $rules['mangkir'];
                    $stats['deduction_percentage'] += $p;
                    $stats['details'][] = ['type' => 'Mangkir (Otomatis)', 'info' => "Bolos Jadwal", 'date' => $currentDate, 'percent' => $p, 'rupiah' => ($p / 100) * $baseTunkin];
                }
            }
        }

        if ($stats['late_count'] > $rules['max_late']) $stats['violation_note'] = "PELANGGARAN: Telat {$stats['late_count']}x";
        $finalPercent = min(100, $stats['deduction_percentage']);
        $stats['total_potongan_rupiah'] = ($finalPercent / 100) * $baseTunkin;
        $stats['tunkin_final'] = max(0, ($baseTunkin + $actingBonus) - $stats['total_potongan_rupiah']);
        $stats['total_meal_allowance'] = $stats['meal_allowance_days'] * $mealRate;
        $stats['grand_total'] = $stats['tunkin_final'] + $stats['total_meal_allowance'];
        $stats['base_tunkin'] = $baseTunkin;

        return $stats;
    }

    private function getLatePSWPercentage($minutes, $rules)
    {
        if ($minutes <= 0) return 0;
        if ($minutes <= 30) return $rules['tl_1'];
        if ($minutes <= 60) return $rules['tl_2'];
        if ($minutes <= 90) return $rules['tl_3'];
        return $rules['tl_4'];
    }
}
