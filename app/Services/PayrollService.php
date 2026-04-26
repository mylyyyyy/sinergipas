<?php

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
     * Logika sangat dioptimalkan untuk kecepatan eksekusi tinggi (Bebas N+1 Query).
     */
    public function calculateMonthlyPayroll(Employee $employee, $monthStr)
    {
        $date = Carbon::parse($monthStr . '-01');
        $startDate = $date->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $date->copy()->endOfMonth()->format('Y-m-d');
        $daysInMonth = $date->daysInMonth;

        // 1. Pre-fetch All Settings in ONE Query
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
            'apel' => (float)($allSettings['payroll_apel_percent'] ?? 0.5),
            'staff_in' => $allSettings['payroll_staff_in'] ?? '07:30',
            'staff_out_mon_thu' => $allSettings['payroll_staff_out_mon_thu'] ?? '16:00',
            'staff_out_fri' => $allSettings['payroll_staff_out_fri'] ?? '16:30',
        ];
        
        // 2. Pre-fetch Attendances
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy(fn($item) => Carbon::parse($item->date)->format('Y-m-d'));

        // 3. Pre-fetch ALL Schedules (INDIVIDUAL & SQUAD) in single queries
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
            'is_tubel' => $employee->is_tubel,
            'is_cpns' => $employee->is_cpns,
            'is_acting' => false
        ];

        $sickCounter = 0;
        // 1. Dasar Tunkin & Penyesuaian CPNS (80%)
        $baseTunkin = (float)($employee->tunkin->nominal ?? 0);
        if ($employee->is_cpns) {
            $baseTunkin = 0.8 * $baseTunkin;
        }

        // 2. Bonus Plt / Plh (20% dari jabatan yang dirangkap jika > 1 bulan)
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

        // 5. Main Processing Loop (No DB queries inside this loop)
        $today = now()->startOfDay();

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $currentDateObj = $date->copy()->day($d);
            $currentDate = $currentDateObj->format('Y-m-d');
            $dayOfWeek = $currentDateObj->dayOfWeek;
            $isFuture = $currentDateObj->isAfter($today);
            
            $attendance = $attendances->get($currentDate);
            
            // Logic Prioritas Jadwal (Memory Based)
            $isScheduled = false;
            $scheduledOutTime = null;
            $specialStatus = null; 

            if ($individualSchedules->has($currentDate)) {
                $indiv = $individualSchedules->get($currentDate);
                $specialStatus = $indiv->status ?? 'picket';
                $isScheduled = !in_array($specialStatus, ['off', 'leave', 'sick']);
                $scheduledOutTime = $indiv->shift->end_time ?? null;
            } elseif ($employee->squad_id && isset($squadSchedules[$currentDate])) {
                $isScheduled = true;
                $scheduledOutTime = $squadSchedules[$currentDate]->shift->end_time ?? null;
            } elseif (!$employee->squad_id && $dayOfWeek >= Carbon::MONDAY && $dayOfWeek <= Carbon::FRIDAY) {
                $isScheduled = true;
                $scheduledOutTime = ($dayOfWeek === Carbon::FRIDAY) ? $rules['staff_out_fri'] : $rules['staff_out_mon_thu'];
            }

            // A. LOGIKA STATUS KHUSUS
            if ($specialStatus && in_array($specialStatus, ['duty_full', 'duty_half', 'tubel'])) {
                $isEligibleMeal = ($specialStatus === 'duty_half');
                $stats['processed_logs'][] = [
                    'date' => $currentDate, 'status' => $specialStatus, 'check_in' => 'DINAS', 'check_out' => 'LUAR', 'is_scheduled' => true, 'meal_amount' => $isEligibleMeal ? $mealRate : 0
                ];
                if ($isEligibleMeal) { $stats['meal_allowance_days']++; $stats['total_present']++; }
                if ($specialStatus === 'tubel') { $stats['deduction_percentage'] += 100; }
                continue;
            }

            if ($attendance) {
                $status = $attendance->status;
                $isEligibleMeal = in_array($status, ['present', 'late', 'duty_half', 'picket']) && $isScheduled;

                $stats['processed_logs'][] = [
                    'date' => $currentDate,
                    'status' => $status,
                    'check_in' => $attendance->check_in ? (is_string($attendance->check_in) ? substr($attendance->check_in, 0, 5) : $attendance->check_in->format('H:i')) : '--:--',
                    'check_out' => $attendance->check_out ? (is_string($attendance->check_out) ? substr($attendance->check_out, 0, 5) : $attendance->check_out->format('H:i')) : '--:--',
                    'is_scheduled' => $isScheduled,
                    'meal_amount' => $isEligibleMeal ? $mealRate : 0
                ];

                if ($isEligibleMeal) { $stats['meal_allowance_days']++; $stats['total_present']++; }

                if ($isScheduled) {
                    $lateMin = abs($attendance->late_minutes ?? 0);
                    $earlyMin = abs($attendance->early_minutes ?? 0);

                    if ($status === 'late' || $lateMin > 0) {
                        $stats['late_count']++;
                        $p = $this->getLatePSWPercentage($lateMin ?: 1, $rules);
                        $canCompensate = false;
                        if ($lateMin <= 30 && $stats['compensation_count'] < 8 && $attendance->check_out && $scheduledOutTime) {
                            $outTimeString = is_string($attendance->check_out) ? $attendance->check_out : $attendance->check_out->format('H:i:s');
                            $actualOut = Carbon::parse($currentDate . ' ' . $outTimeString);
                            $schedOutStr = is_string($scheduledOutTime) ? $scheduledOutTime : $scheduledOutTime;
                            $requiredOut = Carbon::parse($currentDate . ' ' . $schedOutStr)->addMinutes(30);
                            if ($actualOut->greaterThanOrEqualTo($requiredOut)) { $canCompensate = true; }
                        }

                        if ($canCompensate) {
                            $stats['compensation_count']++;
                            $outDisplay = is_string($attendance->check_out) ? substr($attendance->check_out, 0, 5) : $attendance->check_out->format('H:i');
                            $stats['details'][] = ['type' => 'TL Diganti (Kompensasi)', 'info' => "Telat {$lateMin}m, Pulang {$outDisplay}", 'date' => $currentDate, 'percent' => 0, 'rupiah' => 0];
                        } else {
                            $stats['deduction_percentage'] += $p;
                            $stats['details'][] = ['type' => 'Terlambat (TL)', 'info' => "{$lateMin}m", 'date' => $currentDate, 'percent' => $p, 'rupiah' => ($p / 100) * $baseTunkin];
                        }

                        if ($attendance->check_in && Carbon::parse($attendance->check_in)->format('H:i') > $rules['staff_in']) {
                            $stats['deduction_percentage'] += $rules['apel'];
                            $stats['details'][] = ['type' => 'Tidak Ikut Apel', 'info' => "Masuk > {$rules['staff_in']}", 'date' => $currentDate, 'percent' => $rules['apel'], 'rupiah' => ($rules['apel'] / 100) * $baseTunkin];
                        }
                    }

                    if ($earlyMin > 0) {
                        $p = $this->getLatePSWPercentage($earlyMin, $rules);
                        $stats['deduction_percentage'] += $p;
                        $stats['details'][] = ['type' => 'Pulang Cepat (PSW)', 'info' => "{$earlyMin}m", 'date' => $currentDate, 'percent' => $p, 'rupiah' => ($p / 100) * $baseTunkin];
                    }

                    $hasOnlyOneScan = (empty($attendance->check_in) || empty($attendance->check_out) || $attendance->check_in == $attendance->check_out);
                    if (in_array($status, ['present', 'late']) && $hasOnlyOneScan) {
                        $p = $rules['lupa_absen'];
                        $stats['deduction_percentage'] += $p;
                        $stats['details'][] = ['type' => 'Lupa Absen', 'info' => (empty($attendance->check_in) ? "Tanpa Masuk" : "Tanpa Pulang"), 'date' => $currentDate, 'percent' => $p, 'rupiah' => ($p / 100) * $baseTunkin];
                    }
                }

                if ($status === 'sick') {
                    $sickCounter++;
                    $p = ($sickCounter >= 3 && $sickCounter <= 6) ? $rules['sakit_3_6'] : (($sickCounter >= 7) ? $rules['sakit_7'] : 0);
                    if ($p > 0) {
                        $stats['deduction_percentage'] += $p;
                        $stats['details'][] = ['type' => 'Sakit Progresif', 'info' => "Hari ke-{$sickCounter}", 'date' => $currentDate, 'percent' => $p, 'rupiah' => ($p / 100) * $baseTunkin];
                    }
                }

                if ($status === 'absent') {
                    $p = $rules['mangkir'];
                    $stats['deduction_percentage'] += $p;
                    $stats['details'][] = ['type' => 'Mangkir', 'info' => "Tanpa Keterangan", 'date' => $currentDate, 'percent' => $p, 'rupiah' => ($p / 100) * $baseTunkin];
                }

            } else {
                // TIDAK ADA DATA ABSEN SAMA SEKALI
                // Hanya anggap Mangkir jika tanggal tersebut BUKAN masa depan (!isFuture)
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
