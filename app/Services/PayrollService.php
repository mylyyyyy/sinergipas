<?php
// VERSION 4.0 - FINAL REFINED TL LOGIC (ROBUST CARBON COMPARISON)

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
            
        $squadSchedules = [];
        if ($employee->squad_id) {
            $squadSchedules = SquadSchedule::with('shift')
                ->where('squad_id', $employee->squad_id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get()
                ->keyBy(fn($s) => Carbon::parse($s->date)->format('Y-m-d'));
        }

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

            if ($individualSchedules->has($currentDate)) {
                $indiv = $individualSchedules->get($currentDate);
                $specialStatus = $indiv->status ?? 'picket';
                $isScheduled = !in_array($specialStatus, ['off', 'leave', 'sick']);
                $scheduledInTime = $indiv->shift->start_time ?? null;
                $scheduledOutTime = $indiv->shift->end_time ?? null;
            } elseif ($employee->squad_id && isset($squadSchedules[$currentDate])) {
                $isScheduled = true;
                $squadSched = $squadSchedules[$currentDate];
                $scheduledInTime = $squadSched->shift->start_time ?? null;
                $scheduledOutTime = $squadSched->shift->end_time ?? null;
                if (str_contains(strtoupper($squadSched->shift->name ?? ''), 'PAGI')) {
                    $scheduledInTime = '06:00:00';
                }
            } elseif (($dayOfWeek >= Carbon::MONDAY && $dayOfWeek <= Carbon::FRIDAY) || ($dayOfWeek === Carbon::SATURDAY && $rules['staff_saturday_enabled'] === 'on')) {
                $isScheduled = true;
                $isDefaultOffice = true;
                if ($dayOfWeek === Carbon::SATURDAY) {
                    $scheduledInTime = $rules['staff_saturday_in'];
                    $scheduledOutTime = $rules['staff_saturday_out'];
                } else {
                    $scheduledInTime = $rules['staff_in'];
                    $scheduledOutTime = ($dayOfWeek === Carbon::FRIDAY) ? $rules['staff_out_fri'] : $rules['staff_out_mon_thu'];
                }
            }

            if ($employee->squad_id && $isScheduled && empty($scheduledInTime)) {
                $scheduledInTime = '06:00:00'; 
            }

            if ($specialStatus && in_array($specialStatus, ['duty_full', 'duty_half', 'tubel'])) {
                $isEligibleMeal = ($specialStatus === 'duty_half');
                $stats['processed_logs'][] = ['date' => $currentDate, 'status' => $specialStatus, 'check_in' => 'DINAS', 'check_out' => 'LUAR', 'is_scheduled' => true, 'meal_amount' => $isEligibleMeal ? $mealRate : 0];
                if ($isEligibleMeal) { $stats['meal_allowance_days']++; $stats['total_present']++; }
                if ($specialStatus === 'tubel') { $stats['deduction_percentage'] += 100; }
                continue;
            }

            if ($attendance) {
                $status = $attendance->status;
                $isEligibleMeal = in_array($status, ['present', 'late', 'duty_half', 'picket']) && $isScheduled;
                $checkInDisplay = $attendance->check_in ? date('H:i', strtotime($attendance->check_in)) : '--:--';
                $checkOutDisplay = $attendance->check_out ? date('H:i', strtotime($attendance->check_out)) : '--:--';
                if ($checkInDisplay !== '--:--' && $checkInDisplay === $checkOutDisplay) $checkOutDisplay = '--:--';

                $stats['processed_logs'][] = ['date' => $currentDate, 'status' => $status, 'check_in' => $checkInDisplay, 'check_out' => $checkOutDisplay, 'is_scheduled' => $isScheduled, 'meal_amount' => $isEligibleMeal ? $mealRate : 0];
                if ($isEligibleMeal) { $stats['meal_allowance_days']++; $stats['total_present']++; }

                if ($isScheduled) {
                    $lateMin = abs((int)($attendance->late_minutes ?? 0));
                    $checkInStr = $attendance->check_in ? (is_string($attendance->check_in) ? $attendance->check_in : $attendance->check_in->format('H:i:s')) : null;
                    
                    if ($checkInStr && $scheduledInTime) {
                        $actualTimestamp = strtotime($currentDate . ' ' . $checkInStr);
                        $targetTimestamp = strtotime($currentDate . ' ' . $scheduledInTime);

                        // LOGIKA TL (Denda Apel di sini sudah dihapus total)
                        if ($actualTimestamp > $targetTimestamp) {
                            $diff = (int) ceil(($actualTimestamp - $targetTimestamp) / 60);
                            $lateMin = max($lateMin, $diff);
                        }
                    }

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
                            $stats['details'][] = ['type' => $label, 'info' => "{$lateMin}m (Jadwal: {$targetDisplay})", 'date' => $currentDate, 'percent' => $p, 'rupiah' => ($p / 100) * $baseTunkin];
                        }
                    }

                    $earlyMin = abs((int)($attendance->early_minutes ?? 0));
                    if ($earlyMin > 0) {
                        $p = $this->getLatePSWPercentage($earlyMin, $rules);
                        $stats['deduction_percentage'] += $p;
                        $stats['details'][] = ['type' => 'Pulang Cepat (PSW)', 'info' => "{$earlyMin}m", 'date' => $currentDate, 'percent' => $p, 'rupiah' => ($p / 100) * $baseTunkin];
                    }

                    if (in_array($status, ['present', 'late']) && (empty($attendance->check_in) || empty($attendance->check_out) || $attendance->check_in == $attendance->check_out)) {
                        $stats['deduction_percentage'] += $rules['lupa_absen'];
                        $stats['details'][] = ['type' => 'Lupa Absen', 'info' => (empty($attendance->check_in) ? "Tanpa Masuk" : "Tanpa Pulang"), 'date' => $currentDate, 'percent' => $rules['lupa_absen'], 'rupiah' => ($rules['lupa_absen'] / 100) * $baseTunkin];
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
