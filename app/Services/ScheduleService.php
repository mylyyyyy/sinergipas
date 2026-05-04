<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Schedule;
use App\Models\SquadSchedule;
use App\Models\Shift;
use App\Models\Setting;
use Carbon\Carbon;

class ScheduleService
{
    /**
     * Mendapatkan jadwal kerja pegawai pada tanggal tertentu.
     * Hirarki: Jadwal Individu > Jadwal Regu > Jadwal Kantor (Staff)
     */
    public function getEffectiveSchedule(Employee $employee, $date)
    {
        $dateStr = Carbon::parse($date)->format('Y-m-d');
        $month = Carbon::parse($date)->month;
        $year = Carbon::parse($date)->year;

        // 1. Cek Jadwal Individu (Piket/Override)
        $individuals = Schedule::with('shift')
            ->where('employee_id', $employee->id)
            ->where('date', $dateStr)
            ->get();

        if ($individuals->isNotEmpty()) {
            $isOff = false; $isPicket = false; $st = null; $et = null; $status = 'present'; $shiftName = 'Piket Individu';
            foreach($individuals as $indiv) {
                if (in_array($indiv->status, ['off', 'leave', 'sick'])) { $isOff = true; $status = $indiv->status; }
                if ($indiv->status === 'picket') $isPicket = true;
                
                $start = $indiv->shift->start_time ?? null;
                $end = $indiv->shift->end_time ?? null;
                if ($start && (!$st || $start < $st)) $st = $start;
                if ($end && (!$et || $end > $et)) $et = $end;
                if ($indiv->shift) $shiftName = $indiv->shift->name;
            }

            return [
                'type' => 'individual',
                'status' => $status,
                'shift' => (object)[
                    'name' => $shiftName,
                    'start_time' => $st,
                    'end_time' => $et,
                ],
                'is_picket' => $isPicket,
                'is_double_shift' => false // Individu biasanya single
            ];
        }

        // 2. Cek Jadwal Regu (Jika pegawai punya squad_id)
        if ($employee->squad_id) {
            $squads = SquadSchedule::with('shift')
                ->where('squad_id', $employee->squad_id)
                ->where('date', $dateStr)
                ->get();

            if ($squads->isNotEmpty()) {
                $st = null; $et = null; $hasPagi = false; $hasMalam = false;
                foreach($squads as $sq) {
                    $start = $sq->shift->start_time ?? '06:00:00';
                    if ($sq->shift && str_contains(strtoupper($sq->shift->name), 'PAGI')) {
                        $start = '06:00:00'; $hasPagi = true;
                    }
                    if ($sq->shift && str_contains(strtoupper($sq->shift->name), 'MALAM')) $hasMalam = true;

                    $end = $sq->shift->end_time ?? '00:00:00';
                    if (!$st || $start < $st) $st = $start;
                    if (!$et || $end > $et) $et = $end;
                }

                return [
                    'type' => 'squad',
                    'shift' => (object)[
                        'name' => $squads->count() > 1 ? 'Double Shift' : $squads->first()->shift->name,
                        'start_time' => $st,
                        'end_time' => $et,
                    ],
                    'is_picket' => true,
                    'is_double_shift' => ($hasPagi && $hasMalam) || ($squads->count() > 1)
                ];
            }
        }

        // 3. Jadwal Default Staff (Senin-Jumat, dan Sabtu opsional)
        // PERBAIKAN: Hanya fallback jika squad TIDAK ADA jadwal sama sekali di bulan ini
        $shouldFallback = false;
        if (!$employee->squad_id) {
            $shouldFallback = true;
        } else {
            // Cek apakah squad ini punya jadwal APAPUN di bulan ini
            $anySquadSchedule = SquadSchedule::where('squad_id', $employee->squad_id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->exists();
            if (!$anySquadSchedule) $shouldFallback = true;
        }

        if ($shouldFallback) {
            $dateObj = Carbon::parse($dateStr);
            $dayOfWeek = $dateObj->dayOfWeek;
            $staffSatEnabled = Setting::getValue('payroll_staff_saturday_enabled', 'off');

            $ramadanEnabled = Setting::getValue('payroll_ramadan_enabled', 'off');
            $ramadanStart = Carbon::parse(Setting::getValue('payroll_ramadan_start', date('Y-m-d')))->startOfDay();
            $ramadanEnd = Carbon::parse(Setting::getValue('payroll_ramadan_end', date('Y-m-d')))->endOfDay();
            $ramadanSatEnabled = Setting::getValue('payroll_ramadan_saturday_enabled', 'off');
            
            $isRamadan = ($ramadanEnabled === 'on' && $dateObj->between($ramadanStart, $ramadanEnd));

            if (($dayOfWeek >= Carbon::MONDAY && $dayOfWeek <= Carbon::FRIDAY) || 
                ($dayOfWeek === Carbon::SATURDAY && ($staffSatEnabled === 'on' || ($isRamadan && $ramadanSatEnabled === 'on')))) {
                
                if ($dayOfWeek === Carbon::SATURDAY) {
                    if ($isRamadan && $ramadanSatEnabled === 'on') {
                        $inTime = Setting::getValue('payroll_ramadan_saturday_in', '08:00');
                        $outTime = Setting::getValue('payroll_ramadan_saturday_out', '12:00');
                        $shiftName = 'Staff Kantor (Sabtu Ramadhan)';
                    } else if (!$isRamadan && $staffSatEnabled === 'on') {
                        $inTime = Setting::getValue('payroll_staff_saturday_in', '07:30');
                        $outTime = Setting::getValue('payroll_staff_saturday_out', '12:00');
                        $shiftName = 'Staff Kantor (Sabtu)';
                    } else {
                        return null;
                    }
                } else {
                    if ($isRamadan) {
                        $inTime = Setting::getValue('payroll_ramadan_staff_in', '08:00');
                        $outTime = ($dayOfWeek === Carbon::FRIDAY) 
                            ? Setting::getValue('payroll_ramadan_staff_out_fri', '15:30')
                            : Setting::getValue('payroll_ramadan_staff_out_mon_thu', '15:00');
                        $shiftName = 'Staff Kantor (Ramadhan)';
                    } else {
                        $inTime = Setting::getValue('payroll_staff_in', '07:30');
                        $outTime = ($dayOfWeek === Carbon::FRIDAY) 
                            ? Setting::getValue('payroll_staff_out_fri', '16:30')
                            : Setting::getValue('payroll_staff_out_mon_thu', '16:00');
                        $shiftName = 'Staff Kantor';
                    }
                }

                return [
                    'type' => 'office',
                    'shift' => (object)[
                        'name' => $shiftName,
                        'start_time' => $inTime . ':00',
                        'end_time' => $outTime . ':00',
                    ],
                    'is_picket' => false,
                    'is_double_shift' => false
                ];
            }
        }

        // Tidak ada jadwal (Libur/Weekend tanpa piket)
        return null;
    }

    /**
     * Mengecek apakah absensi valid untuk mendapatkan uang makan.
     * Khusus Shift M (Malam), data diproses ke hari berikutnya.
     */
    public function validateAttendanceForAllowance(Employee $employee, $date, $checkInTime)
    {
        // Cek jadwal efektif (Hirarki: Individu > Regu > Staff)
        $schedule = $this->getEffectiveSchedule($employee, $date);
        
        // 1. Tangani Status Khusus dari Jadwal Individu (Cuti, Sakit, Libur)
        if ($schedule && isset($schedule['type']) && $schedule['type'] === 'individual') {
            if ($schedule['status'] === 'leave') {
                return ['is_valid' => true, 'reason' => 'Cuti', 'status' => 'on_leave', 'schedule' => $schedule, 'is_night_shift' => false];
            }
            if ($schedule['status'] === 'sick') {
                return ['is_valid' => true, 'reason' => 'Izin Sakit', 'status' => 'sick', 'schedule' => $schedule, 'is_night_shift' => false];
            }
            if ($schedule['status'] === 'off') {
                return ['is_valid' => false, 'reason' => 'Libur (Override)', 'status' => 'off', 'schedule' => $schedule, 'is_night_shift' => false];
            }
        }

        // Jika Shift Malam (M), jadwal aslinya adalah H-1
        // Tapi uang makan dihitung di hari H (kepulangan)
        $checkIn = Carbon::parse($date . ' ' . $checkInTime);
        
        // Jika tidak ada jadwal di hari H, cek apakah dia pulang dari Shift Malam H-1
        if (!$schedule || (isset($schedule['shift']) && !str_contains(strtoupper($schedule['shift']->name), 'MALAM'))) {
            $yesterday = Carbon::parse($date)->subDay()->format('Y-m-d');
            $yesterdaySchedule = $this->getEffectiveSchedule($employee, $yesterday);
            
            if ($yesterdaySchedule && isset($yesterdaySchedule['shift']) && str_contains(strtoupper($yesterdaySchedule['shift']->name), 'MALAM')) {
                return [
                    'is_valid' => true,
                    'reason' => 'Shift Malam (Kepulangan)',
                    'status' => 'picket',
                    'schedule' => $yesterdaySchedule,
                    'is_night_shift' => true
                ];
            }
        }

        // Jika ada jadwal di hari H, pastikan jam masuknya masuk akal
        if ($schedule) {
            $shiftStart = Carbon::parse($date . ' ' . $schedule['shift']->start_time);
            
            // Beri toleransi masuk (misal 2 jam sebelum shift mulai masih dianggap valid)
            if ($checkIn->diffInHours($shiftStart, false) <= 2) {
                // Tentukan status: Jika jam masuk > jam mulai, maka 'late', jika tidak maka 'present' atau 'picket'
                $isLate = $checkIn->gt($shiftStart);
                $status = $isLate ? 'late' : ($schedule['is_picket'] ? 'picket' : 'present');

                return [
                    'is_valid' => true,
                    'reason' => 'Sesuai Jadwal: ' . $schedule['shift']->name,
                    'status' => $status,
                    'schedule' => $schedule,
                    'is_night_shift' => str_contains(strtoupper($schedule['shift']->name), 'MALAM')
                ];
            }
        }

        return [
            'is_valid' => false,
            'reason' => 'Di luar jadwal resmi',
            'status' => 'absent',
            'schedule' => null,
            'is_night_shift' => false
        ];
    }
}
