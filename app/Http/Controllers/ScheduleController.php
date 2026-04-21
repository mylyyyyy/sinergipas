<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\Squad;
use App\Models\SquadSchedule;
use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Employee;
use App\Models\Schedule;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        // Get shifts (Pagi, Siang, Malam, Kantor)
        $shifts = Shift::orderBy('id')->get();
        if ($shifts->isEmpty()) {
            return back()->with('error', 'Silakan inisialisasi data Shift terlebih dahulu.');
        }

        $squads = Squad::orderBy('name')->get();
        $employees = Employee::orderBy('full_name')->get();
        
        $monthStr = $request->input('month', now()->format('Y-m'));
        $month = Carbon::parse($monthStr);
        $daysInMonth = $month->daysInMonth;
        
        // Get all squad schedules for this month
        $schedules = SquadSchedule::with('squad', 'shift')
            ->whereMonth('date', $month->month)
            ->whereYear('date', $month->year)
            ->get()
            ->groupBy(function($item) {
                return $item->date . '_' . $item->shift_id;
            });

        // Get all individual schedules for this month
        $individualSchedules = Schedule::with('employee', 'shift')
            ->whereMonth('date', $month->month)
            ->whereYear('date', $month->year)
            ->get();

        return view('admin.schedules.index', compact(
            'shifts', 'month', 'daysInMonth', 'schedules', 'squads', 'employees', 'monthStr', 'individualSchedules'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'squad_id' => 'nullable|exists:squads,id',
            'shift_id' => 'required|exists:shifts,id',
            'date' => 'required|date',
        ]);

        if (!$request->squad_id) {
            SquadSchedule::where('date', $request->date)
                ->where('shift_id', $request->shift_id)
                ->delete();
        } else {
            SquadSchedule::updateOrCreate(
                [
                    'date' => $request->date,
                    'shift_id' => $request->shift_id
                ],
                ['squad_id' => $request->squad_id]
            );
        }

        return response()->json(['success' => true]);
    }

    public function storeIndividual(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'shift_id' => 'required|exists:shifts,id',
            'date' => 'required|date',
        ]);

        Schedule::updateOrCreate(
            [
                'employee_id' => $request->employee_id,
                'date' => $request->date
            ],
            ['shift_id' => $request->shift_id]
        );

        return back()->with('success', 'Jadwal individu berhasil disimpan.');
    }

    public function reset(Request $request)
    {
        $request->validate(['month' => 'required']);
        $date = Carbon::parse($request->month);
        
        SquadSchedule::whereMonth('date', $date->month)->whereYear('date', $date->year)->delete();
        Schedule::whereMonth('date', $date->month)->whereYear('date', $date->year)->delete();

        return back()->with('success', 'Jadwal bulan ini berhasil dikosongkan.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'nullable|array',
            'ids.*' => 'exists:schedules,id',
            'month' => 'nullable|string',
            'all' => 'nullable|boolean'
        ]);

        if ($request->all && $request->month) {
            $date = Carbon::parse($request->month);
            Schedule::whereMonth('date', $date->month)
                ->whereYear('date', $date->year)
                ->delete();
            return back()->with('success', 'Semua jadwal khusus bulan ini berhasil dihapus.');
        }

        if ($request->ids) {
            Schedule::whereIn('id', $request->ids)->delete();
            return back()->with('success', count($request->ids) . ' jadwal berhasil dihapus.');
        }

        return back()->with('error', 'Tidak ada jadwal yang dipilih.');
    }

    public function export(Request $request)
    {
        set_time_limit(0);
        $monthStr = $request->month ?? now()->format('Y-m');
        $date = Carbon::parse($monthStr);

        $shifts = Shift::whereIn('name', ['Pagi', 'Siang', 'Malam'])->orderBy('id')->get();
        $daysInMonth = $date->daysInMonth;
        
        $schedules = SquadSchedule::with('squad', 'shift')
            ->whereMonth('date', $date->month)
            ->whereYear('date', $date->year)
            ->get()
            ->groupBy(function($item) {
                return $item->date . '_' . $item->shift_id;
            });

        $kop1 = Setting::getValue('kop_line_1', 'KEMENTERIAN HUKUM DAN HAM RI');
        $kop2 = Setting::getValue('kop_line_2', 'LAPAS KELAS IIB JOMBANG');

        $pdf = Pdf::loadView('admin.schedules.pdf-squad', compact(
            'shifts', 'schedules', 'date', 'daysInMonth', 'kop1', 'kop2'
        ))->setPaper('a4', 'landscape');

        return $pdf->download("jadwal-regu-{$monthStr}.pdf");
    }
}
