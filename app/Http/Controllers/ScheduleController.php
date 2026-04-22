<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\Squad;
use App\Models\SquadSchedule;
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
        $date = Carbon::parse($monthStr . '-01');
        
        $shifts = Shift::whereIn('name', ['Pagi', 'Siang', 'Malam'])->orderBy('id')->get();
        $squads = Squad::orderBy('name')->get();
        
        $schedules = SquadSchedule::with('squad', 'shift')
            ->whereMonth('date', $date->month)
            ->whereYear('date', $date->year)
            ->get()
            ->groupBy(function($item) {
                return $item->date . '_' . $item->shift_id;
            });

        $daysInMonth = $date->daysInMonth;

        return view('admin.schedules.index', compact('shifts', 'squads', 'schedules', 'date', 'monthStr', 'daysInMonth'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'shift_id' => 'required|exists:shifts,id',
            'squad_id' => 'required|exists:squads,id',
        ]);

        SquadSchedule::updateOrCreate(
            ['date' => $request->date, 'shift_id' => $request->shift_id],
            ['squad_id' => $request->squad_id]
        );

        return response()->json(['success' => true]);
    }

    public function generate(Request $request)
    {
        $monthStr = $request->month ?? now()->format('Y-m');
        $date = Carbon::parse($monthStr . '-01');
        $daysInMonth = $date->daysInMonth;
        
        $shifts = Shift::whereIn('name', ['Pagi', 'Siang', 'Malam'])->orderBy('id')->get();
        $squads = Squad::orderBy('name')->get()->pluck('id')->toArray();
        
        if (empty($squads)) return back()->with('error', 'Belum ada regu jaga.');

        $squadCount = count($squads);
        $currentSquadIndex = 0;

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $currentDate = $date->copy()->day($d)->format('Y-m-d');
            
            foreach ($shifts as $shift) {
                SquadSchedule::updateOrCreate(
                    ['date' => $currentDate, 'shift_id' => $shift->id],
                    ['squad_id' => $squads[$currentSquadIndex]]
                );
                
                $currentSquadIndex = ($currentSquadIndex + 1) % $squadCount;
            }
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'generate_schedule',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . " men-generate otomatis jadwal regu jaga untuk " . $date->translatedFormat('F Y')
        ]);

        return back()->with('success', 'Jadwal berhasil di-generate otomatis.');
    }

    public function clear(Request $request)
    {
        $monthStr = $request->month ?? now()->format('Y-m');
        $date = Carbon::parse($monthStr . '-01');
        
        SquadSchedule::whereMonth('date', $date->month)
            ->whereYear('date', $date->year)
            ->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'clear_schedule',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . " menghapus seluruh jadwal regu jaga periode " . $date->translatedFormat('F Y')
        ]);

        return back()->with('success', 'Seluruh jadwal bulan ini berhasil dihapus.');
    }

    public function exportPdf(Request $request)
    {
        $monthStr = $request->month ?? now()->format('Y-m');
        $date = Carbon::parse($monthStr . '-01');
        
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

        if (ob_get_length()) ob_end_clean();
        $pdf = Pdf::loadView('admin.schedules.pdf-squad', compact(
            'shifts', 'schedules', 'date', 'daysInMonth', 'kop1', 'kop2'
        ))->setPaper('a4', 'landscape');

        return $pdf->download("jadwal-regu-{$monthStr}.pdf");
    }
}
