<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\Squad;
use App\Models\SquadSchedule;
use App\Models\Schedule;
use App\Models\Employee;
use App\Models\Setting;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $monthStr = $request->month ?? now()->format('Y-m');
        $month = Carbon::parse($monthStr . '-01');
        $daysInMonth = $month->daysInMonth;

        $shifts = Shift::whereIn('name', ['Pagi', 'Siang', 'Malam'])->orderBy('id')->get();
        
        $reguSquads = Squad::where('type', 'regu')->orderBy('name')->get();
        $p2uSquads = Squad::where('type', 'p2u')->orderBy('name')->get();

        // Get schedules for Regu and P2U
        $allSquadSchedules = SquadSchedule::with('squad')
            ->whereMonth('date', $month->month)
            ->whereYear('date', $month->year)
            ->get()
            ->groupBy(function($item) {
                return Carbon::parse($item->date)->format('Y-m-d') . '_' . $item->shift_id;
            });

        $reguSchedules = $allSquadSchedules->map(function($group) {
            return $group->where('squad.type', 'regu');
        });
        
        $p2uSchedules = $allSquadSchedules->map(function($group) {
            return $group->where('squad.type', 'p2u');
        });

        $individualSchedulesList = Schedule::with(['employee', 'shift'])
            ->whereMonth('date', $month->month)
            ->whereYear('date', $month->year)
            ->orderBy('date')
            ->get();

        $individualSchedules = $individualSchedulesList->groupBy(function($item) {
                return Carbon::parse($item->date)->format('Y-m-d') . '_' . $item->shift_id;
            });

        // Group Employees for Individual Picket Selection
        $allEmployees = Employee::with('squad')->orderBy('full_name')->get();
        $employeesByGroup = [
            'Regu Jaga' => $allEmployees->filter(fn($e) => $e->squad && $e->squad->type === 'regu')->groupBy('squad.name'),
            'Unit P2U' => $allEmployees->filter(fn($e) => $e->squad && $e->squad->type === 'p2u')->groupBy('squad.name'),
            'Staff Kantor' => $allEmployees->filter(fn($e) => !$e->squad)
        ];

        $hasData = SquadSchedule::whereMonth('date', $month->month)->whereYear('date', $month->year)->exists();

        return view('admin.schedules.index', compact(
            'month', 'monthStr', 'daysInMonth', 'shifts', 
            'reguSquads', 'p2uSquads', 'reguSchedules', 'p2uSchedules',
            'individualSchedules', 'individualSchedulesList', 'employeesByGroup', 'hasData'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'shift_id' => 'required|exists:shifts,id',
            'squad_id' => 'nullable',
            'type' => 'required|in:regu,p2u'
        ]);

        $cleanDate = Carbon::parse($request->date)->format('Y-m-d');

        SquadSchedule::where('date', $cleanDate)
            ->where('shift_id', $request->shift_id)
            ->whereHas('squad', function($q) use ($request) {
                $q->where('type', $request->type);
            })
            ->delete();

        if ($request->squad_id) {
            SquadSchedule::create([
                'date' => $cleanDate,
                'shift_id' => $request->shift_id,
                'squad_id' => $request->squad_id
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function storeIndividual(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
            'date' => 'required|date',
            'status' => 'required|in:picket,leave,sick,off,duty_full,duty_half,tubel',
            'shift_id' => 'required_if:status,picket,duty_half|nullable|exists:shifts,id',
        ]);

        foreach ($request->employee_ids as $employee_id) {
            $schedule = Schedule::updateOrCreate(
                ['employee_id' => $employee_id, 'date' => $request->date],
                [
                    'status' => $request->status,
                    'shift_id' => in_array($request->status, ['picket', 'duty_half']) ? $request->shift_id : null
                ]
            );

            $cleanDate = \Carbon\Carbon::parse($request->date)->format('Y-m-d');
            $attendanceStatus = match($request->status) {
                'leave' => 'on_leave',
                'sick' => 'sick',
                'duty_full' => 'duty_full',
                'duty_half' => 'duty_half',
                'tubel' => 'tubel',
                default => null
            };

            if ($attendanceStatus) {
                \App\Models\Attendance::updateOrCreate(
                    ['employee_id' => $employee_id, 'date' => $cleanDate],
                    ['status' => $attendanceStatus, 'check_in' => null, 'check_out' => null, 'late_minutes' => 0, 'allowance_amount' => 0]
                );
            } else {
                \App\Models\Attendance::where('employee_id', $employee_id)->where('date', $cleanDate)
                    ->whereIn('status', ['on_leave', 'sick', 'duty_full', 'duty_half', 'tubel'])->delete();
            }
        }
        return back()->with('success', count($request->employee_ids) . ' penugasan individu berhasil disimpan.');
    }

    public function destroyIndividual($id)
    {
        $schedule = Schedule::find($id);
        if ($schedule) {
            $cleanDate = \Carbon\Carbon::parse($schedule->date)->format('Y-m-d');
            \App\Models\Attendance::where('employee_id', $schedule->employee_id)->where('date', $cleanDate)
                ->whereIn('status', ['on_leave', 'sick', 'duty_full', 'duty_half', 'tubel'])->delete();
            $schedule->delete();
        }
        return back()->with('success', 'Jadwal penugasan berhasil dihapus.');
    }

    public function generate(Request $request)
    {
        $request->validate(['type' => 'required|in:regu,p2u', 'month' => 'required']);
        
        $month = Carbon::parse($request->month . '-01');
        $squads = Squad::where('type', $request->type)->orderBy('name')->pluck('id')->toArray();
        if (empty($squads)) return back()->with('error', 'Belum ada unit ' . strtoupper($request->type));

        $shifts = Shift::whereIn('name', ['Pagi', 'Siang', 'Malam'])->orderBy('id')->get();
        $squadCount = count($squads);
        $currentSquadIndex = 0;

        $lastMonth = $month->copy()->subMonth();
        $lastSchedule = SquadSchedule::whereMonth('date', $lastMonth->month)->whereYear('date', $lastMonth->year)
            ->whereHas('squad', fn($q) => $q->where('type', $request->type))
            ->orderBy('date', 'desc')->orderBy('shift_id', 'desc')->first();

        if ($lastSchedule) {
            $lastIdx = array_search($lastSchedule->squad_id, $squads);
            if ($lastIdx !== false) $currentSquadIndex = ($lastIdx + 1) % $squadCount;
        }

        SquadSchedule::whereMonth('date', $month->month)->whereYear('date', $month->year)
            ->whereHas('squad', fn($q) => $q->where('type', $request->type))->delete();

        for ($d = 1; $d <= $month->daysInMonth; $d++) {
            $date = $month->copy()->day($d)->format('Y-m-d');
            
            // Logika Rotasi: Pagi (Base), Siang (Base+1), Malam (Base)
            // Berlaku untuk Regu Jaga dan P2U
            foreach ($shifts as $shift) {
                $shiftName = strtolower($shift->name);
                $assignedIndex = $currentSquadIndex;
                
                if (str_contains($shiftName, 'siang')) {
                    $assignedIndex = ($currentSquadIndex + 1) % $squadCount;
                }
                
                SquadSchedule::create([
                    'date' => $date, 
                    'shift_id' => $shift->id, 
                    'squad_id' => $squads[$assignedIndex]
                ]);
            }
            // Maju ke squad berikutnya (Base baru) untuk hari selanjutnya
            $currentSquadIndex = ($currentSquadIndex + 1) % $squadCount;
        }

        return back()->with('success', 'Jadwal ' . strtoupper($request->type) . ' berhasil di-generate.');
    }

    public function clear(Request $request)
    {
        $request->validate(['type' => 'required|in:regu,p2u,individual', 'month' => 'required']);
        $month = Carbon::parse($request->month . '-01');
        if ($request->type === 'individual') {
            Schedule::whereMonth('date', $month->month)->whereYear('date', $month->year)->delete();
        } else {
            SquadSchedule::whereMonth('date', $month->month)
                ->whereYear('date', $month->year)
                ->whereHas('squad', fn($q) => $q->where('type', $request->type))
                ->delete();
        }
        return back()->with('success', 'Jadwal berhasil dibersihkan.');
    }

    public function copyLastMonth(Request $request)
    {
        $month = Carbon::parse($request->month . '-01');
        $lastMonth = $month->copy()->subMonth();
        $schedules = SquadSchedule::whereMonth('date', $lastMonth->month)->whereYear('date', $lastMonth->year)->get();
        foreach ($schedules as $s) {
            $newDate = Carbon::parse($s->date)->addMonth();
            if ($newDate->month == $month->month) {
                SquadSchedule::updateOrCreate(['date' => $newDate->format('Y-m-d'), 'shift_id' => $s->shift_id, 'squad_id' => $s->squad_id], ['updated_at' => now()]);
            }
        }
        return back()->with('success', 'Jadwal berhasil disalin dari bulan lalu.');
    }

    public function exportPdf(Request $request)
    {
        set_time_limit(300);
        $monthStr = $request->month ?? now()->format('Y-m');
        $type = $request->type ?? 'regu';
        $date = \Carbon\Carbon::parse($monthStr . '-01');
        $daysInMonth = $date->daysInMonth;
        $shifts = Shift::whereIn('name', ['Pagi', 'Siang', 'Malam'])->orderBy('id')->get();
        
        if ($type === 'individual') {
            $schedules = Schedule::with(['employee', 'shift'])->whereMonth('date', $date->month)->whereYear('date', $date->year)->orderBy('date')->get();
            if (ob_get_length()) ob_end_clean();
            return Pdf::loadView('admin.schedules.pdf-individual', compact('schedules', 'date', 'monthStr'))->setPaper('a4', 'portrait')->download("jadwal-piket-individu-{$monthStr}.pdf");
        }

        $schedules = SquadSchedule::with('squad')->whereMonth('date', $date->month)->whereYear('date', $date->year)
            ->whereHas('squad', fn($q) => $q->where('type', $type))->get()->groupBy(fn($item) => Carbon::parse($item->date)->format('Y-m-d') . '_' . $item->shift_id);
        $title = ($type === 'p2u') ? 'JADWAL UNIT P2U' : 'JADWAL REGU JAGA';
        if (ob_get_length()) ob_end_clean();
        return Pdf::loadView('admin.schedules.pdf-squad', compact('shifts', 'schedules', 'date', 'daysInMonth', 'title'))->setPaper('a4', 'landscape')->download("jadwal-{$type}-{$monthStr}.pdf");
    }
}
