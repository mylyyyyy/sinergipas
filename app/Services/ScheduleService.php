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
        $date = Carbon::parse($date)->format('Y-m-d');

        // 1. Cek Jadwal Individu (Piket/Override)
        $individual = Schedule::with('shift')
            ->where('employee_id', $employee->id)
            ->where('date', $date)
            ->first();

        if ($individual) {
            return [
                'type' => 'individual',
                'status' => $individual->status,
                'shift' => $individual->shift,
                'is_picket' => $individual->status === 'picket'
            ];
        }

        // 2. Cek Jadwal Regu (Jika pegawai punya squad_id)
        if ($employee->squad_id) {
            $squad = SquadSchedule::with('shift')
                ->where('squad_id', $employee->squad_id)
                ->where('date', $date)
                ->first();

            if ($squad) {
                $st = $squad->shift->start_time ?? '06:00:00';
                
                // Force 06:00 for Morning Guard Shifts to ensure consistency across the system
                if ($squad->shift && str_contains(strtoupper($squad->shift->name ?? ''), 'PAGI')) {
                    $st = '06:00:00';
                }

                return [
                    'type' => 'squad',
                    'shift' => (object)[
                        'name' => $squad->shift->name ?? 'Regu Jaga',
                        'start_time' => $st,
                        'end_time' => $squad->shift->end_time ?? '00:00:00',
                    ],
                    'is_picket' => true
                ];
            }
        }

        // 3. Jadwal Default Staff (Senin-Jumat, dan Sabtu opsional)
        $dateObj = Carbon::parse($date);
        $dayOfWeek = $dateObj->dayOfWeek;
        $staffSatEnabled = Setting::getValue('payroll_staff_saturday_enabled', 'off');

        if (($dayOfWeek >= Carbon::MONDAY && $dayOfWeek <= Carbon::FRIDAY) || ($dayOfWeek === Carbon::SATURDAY && $staffSatEnabled === 'on')) {
            $inTime = Setting::getValue('payroll_staff_in', '07:30');
            $outTime = ($dayOfWeek === Carbon::FRIDAY) 
                ? Setting::getValue('payroll_staff_out_fri', '16:30')
                : Setting::getValue('payroll_staff_out_mon_thu', '16:00');
            
            if ($dayOfWeek === Carbon::SATURDAY) {
                $inTime = Setting::getValue('payroll_staff_saturday_in', '07:30');
                $outTime = Setting::getValue('payroll_staff_saturday_out', '12:00');
            }

            return [
                'type' => 'office',
                'shift' => (object)[
                    'name' => ($dayOfWeek === Carbon::SATURDAY) ? 'Staff Kantor (Sabtu)' : 'Staff Kantor',
                    'start_time' => $inTime . ':00',
                    'end_time' => $outTime . ':00',
                ],
                'is_picket' => false
            ];
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
