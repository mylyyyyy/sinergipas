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
     * Logika ini diselaraskan 100% dengan AttendanceController.
     */
    public function calculateMonthlyPayroll(Employee $employee, $monthStr)
    {
        $date = Carbon::parse($monthStr . '-01');
        $startDate = $date->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $date->copy()->endOfMonth()->format('Y-m-d');
        $daysInMonth = $date->daysInMonth;

        // Fetch Dynamic Rules from Settings
        $rules = [
            'tl_1' => Setting::getValue('payroll_tl_1_percent', 0.5),
            'tl_2' => Setting::getValue('payroll_tl_2_percent', 1.0),
            'tl_3' => Setting::getValue('payroll_tl_3_percent', 1.25),
            'tl_4' => Setting::getValue('payroll_tl_4_percent', 1.5),
            'max_late' => Setting::getValue('payroll_max_late_count', 8),
            'mangkir' => Setting::getValue('payroll_mangkir_percent', 5.0),
            'lupa_absen' => Setting::getValue('payroll_lupa_absen_percent', 1.5),
            'sakit_3_6' => Setting::getValue('payroll_sakit_3_6_percent', 2.5),
            'sakit_7' => Setting::getValue('payroll_sakit_7_plus_percent', 10.0),
            'apel' => Setting::getValue('payroll_apel_percent', 0.5),
        ];
        
        // 1. Ambil data Absensi
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

        // 2. Ambil data Jadwal (Unified Logic from AttendanceController)
        $squadSchedules = SquadSchedule::where('squad_id', $employee->squad_id)
            ->whereBetween('date', [$startDate, $endDate])
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        $individualSchedules = Schedule::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy(fn($i) => Carbon::parse($i->date)->format('Y-m-d'));

        // Helper checkIsScheduled (Sama persis dengan AttendanceController)
        $checkIsScheduled = function($emp, $dateStr) use ($squadSchedules, $individualSchedules) {
            // 1. Individual Override
            if (isset($individualSchedules[$dateStr])) {
                return !in_array($individualSchedules[$dateStr]->status, ['off', 'leave', 'sick']);
            }
            // 2. Squad Schedule
            if ($emp->squad_id && in_array($dateStr, $squadSchedules)) {
                return true;
            }
            // 3. Default Office (Staff only, Mon-Fri)
            if (!$emp->squad_id) {
                $dayNum = Carbon::parse($dateStr)->dayOfWeek;
                return ($dayNum >= Carbon::MONDAY && $dayNum <= Carbon::FRIDAY);
            }
            return false;
        };

        $stats = [
            'total_present' => 0,
            'total_alpha' => 0,
            'total_sick' => 0,
            'total_leave' => 0,
            'late_count' => 0,
            'early_leave_count' => 0,
            'deduction_percentage' => 0.0,
            'meal_allowance_days' => 0,
            'details' => [],
            'processed_logs' => [], // Tambahkan ini untuk rincian tabel
            'violation_note' => null
        ];

        $sickCounter = 0;
        $baseTunkin = $employee->tunkin->nominal ?? 0;
        $mealRate = $employee->rank_relation->meal_allowance ?? 0;

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $currentDate = $date->copy()->day($d)->format('Y-m-d');
            $attendance = $attendances->get($currentDate);
            $isScheduled = $checkIsScheduled($employee, $currentDate);

            if ($attendance) {
                $status = $attendance->status;
                $isEligibleMeal = in_array($status, ['present', 'late', 'duty_half', 'picket']) && $isScheduled;

                // Tambahkan ke processed_logs untuk tampilan tabel
                $stats['processed_logs'][] = [
                    'date' => $currentDate,
                    'status' => $status,
                    'check_in' => $attendance->check_in ? (is_string($attendance->check_in) ? $attendance->check_in : $attendance->check_in->format('H:i')) : '--:--',
                    'check_out' => $attendance->check_out ? (is_string($attendance->check_out) ? $attendance->check_out : $attendance->check_out->format('H:i')) : '--:--',
                    'is_scheduled' => $isScheduled,
                    'meal_amount' => $isEligibleMeal ? $mealRate : 0
                ];

                // --- UANG MAKAN ---
                if ($isEligibleMeal) {
                    $stats['meal_allowance_days']++;
                    $stats['total_present']++;
                }

                // --- POTONGAN TUKIN ---
                if ($isScheduled) {
                    if ($status === 'late' || $attendance->late_minutes > 0) {
                        $stats['late_count']++;
                        
                        $p = $this->getLatePSWPercentage($attendance->late_minutes, $rules);
                        
                        $stats['deduction_percentage'] += $p;
                        $stats['details'][] = [
                            'type' => 'Terlambat (TL)',
                            'info' => $attendance->late_minutes > 0 ? "{$attendance->late_minutes}m" : "Status Terlambat",
                            'date' => $currentDate,
                            'percent' => $p,
                            'rupiah' => ($p / 100) * $baseTunkin
                        ];
                    }
                    
                    if ($attendance->early_minutes > 0) {
                        $stats['early_leave_count']++;
                        $p = $this->getLatePSWPercentage($attendance->early_minutes, $rules);
                        $stats['deduction_percentage'] += $p;
                        $stats['details'][] = [
                            'type' => 'Pulang Cepat (PSW)',
                            'info' => "{$attendance->early_minutes}m",
                            'date' => $currentDate,
                            'percent' => $p,
                            'rupiah' => ($p / 100) * $baseTunkin
                        ];
                    }

                    if (in_array($status, ['present', 'late']) && (!$attendance->check_in || !$attendance->check_out)) {
                        $p = $rules['lupa_absen'];
                        $stats['deduction_percentage'] += $p;
                        $stats['details'][] = [
                            'type' => 'Lupa Absen',
                            'info' => "Data tidak lengkap",
                            'date' => $currentDate,
                            'percent' => $p,
                            'rupiah' => ($p / 100) * $baseTunkin
                        ];
                    }
                }

                if ($status === 'sick') {
                    $stats['total_sick']++;
                    $sickCounter++;
                    $p = ($sickCounter >= 3 && $sickCounter <= 6) ? $rules['sakit_3_6'] : (($sickCounter >= 7) ? $rules['sakit_7'] : 0);
                    if ($p > 0) {
                        $stats['deduction_percentage'] += $p;
                        $stats['details'][] = ['type' => 'Sakit Progresif', 'info' => "Hari ke-{$sickCounter}", 'date' => $currentDate, 'percent' => $p, 'rupiah' => ($p / 100) * $baseTunkin];
                    }
                }

                if ($status === 'absent') {
                    $stats['total_alpha']++;
                    $p = $rules['mangkir'];
                    $stats['deduction_percentage'] += $p;
                    $stats['details'][] = ['type' => 'Mangkir', 'info' => "Tanpa Keterangan", 'date' => $currentDate, 'percent' => $p, 'rupiah' => ($p / 100) * $baseTunkin];
                }

            } else {
                // TIDAK ABSEN
                if ($isScheduled) {
                    $stats['total_alpha']++;
                    $p = $rules['mangkir'];
                    $stats['deduction_percentage'] += $p;
                    $stats['details'][] = ['type' => 'Mangkir (Jadwal)', 'info' => "Bolos Shift", 'date' => $currentDate, 'percent' => $p, 'rupiah' => ($p / 100) * $baseTunkin];
                }
            }
        }

        if ($stats['late_count'] > $rules['max_late']) $stats['violation_note'] = "PELANGGARAN: Telat {$stats['late_count']}x";

        $stats['total_potongan_rupiah'] = ($stats['deduction_percentage'] / 100) * $baseTunkin;
        $stats['tunkin_final'] = max(0, $baseTunkin - $stats['total_potongan_rupiah']);
        $stats['total_meal_allowance'] = $stats['meal_allowance_days'] * $mealRate;
        $stats['grand_total'] = $stats['tunkin_final'] + $stats['total_meal_allowance'];

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

