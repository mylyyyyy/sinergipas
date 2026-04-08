<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Shift;
use App\Models\Schedule;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\Squad;
use App\Models\ScheduleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $scheduleTypes = ScheduleType::where('is_active', true)->orderBy('sort_order')->get();
        if ($scheduleTypes->isEmpty()) {
            return back()->with('error', 'Silakan setup Schedule Types terlebih dahulu via Seeder.');
        }

        $activeTypeId = $request->input('type', $scheduleTypes->first()->id);
        $activeType = $scheduleTypes->firstWhere('id', $activeTypeId);

        $employeesQuery = Employee::with(['work_unit', 'squad'])
            ->whereHas('user') // Safety check: only active employees
            ->orderBy('full_name');

        if ($activeType->uses_squads) {
            $employeesQuery->whereHas('squad', function($q) use ($activeTypeId) {
                $q->where('schedule_type_id', $activeTypeId);
            });
            $squads = Squad::where('schedule_type_id', $activeTypeId)->get();
        } else {
            // For staffing / non-squad types
            $employeesQuery->whereDoesntHave('squad');
            $squads = collect();
        }

        $employees = $employeesQuery->get();
        $shifts = Shift::all();
        
        $month = $request->filled('month') ? Carbon::parse($request->month) : now();
        $daysInMonth = $month->daysInMonth;
        
        $schedules = Schedule::whereMonth('date', $month->month)
            ->whereYear('date', $month->year)
            ->where('schedule_type_id', $activeTypeId)
            ->get()
            ->groupBy('employee_id');

        return view('admin.schedules.index', compact(
            'employees', 'shifts', 'month', 'daysInMonth', 'schedules', 'squads', 'scheduleTypes', 'activeType'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'date' => 'required|date',
            'schedule_type_id' => 'required|exists:schedule_types,id',
        ]);

        if (!$request->shift_id) {
            Schedule::where('employee_id', $request->employee_id)
                ->where('date', $request->date)
                ->where('schedule_type_id', $request->schedule_type_id)
                ->delete();
        } else {
            Schedule::updateOrCreate(
                [
                    'employee_id' => $request->employee_id, 
                    'date' => $request->date,
                    'schedule_type_id' => $request->schedule_type_id
                ],
                ['shift_id' => $request->shift_id]
            );
        }

        return response()->json(['success' => true]);
    }

    public function generateRoster(Request $request)
    {
        $request->validate([
            'schedule_type_id' => 'required|exists:schedule_types,id',
            'month' => 'required|string',
            'start_date' => 'required|date',
            'squad_id' => 'nullable|exists:squads,id',
            'pattern' => 'nullable|array',
        ]);

        $baseMonth = Carbon::parse($request->month);
        $startDate = Carbon::parse($request->start_date);
        $type = ScheduleType::find($request->schedule_type_id);
        
        // Use pattern from request or from type model
        $pattern = $request->pattern ?: $type->pattern;
        
        if (empty($pattern)) {
            return back()->with('error', "Pola (pattern) tidak ditemukan untuk tipe ini. Silakan atur di Master Tipe Piket.");
        }

        $pattern = array_values($pattern); 
        $patternCount = count($pattern);

        // Fetch employees
        if ($request->squad_id) {
            $employees = Employee::where('squad_id', $request->squad_id)->get();
            $squad = Squad::find($request->squad_id);
            $logTag = "Regu " . $squad->name;
        } else {
            // If No Squad but for a specific type (e.g. CPNS Ramadan)
            $employees = Employee::whereHas('user') // Or specific criteria
                        ->whereDoesntHave('squad')
                        ->get();
            $logTag = $type->name;
        }

        if ($employees->isEmpty()) {
            return back()->with('error', "Tidak ada data pegawai untuk diproses.");
        }

        $upsertData = [];
        $now = now();

        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                // Different offset for each employee could be added here if needed
                // For now, we use a simple start_date diff
                for ($day = 1; $day <= $baseMonth->daysInMonth; $day++) {
                    $dateObj = $baseMonth->copy()->day($day);
                    $diffDays = $startDate->diffInDays($dateObj, false);
                    
                    $index = ($diffDays % $patternCount);
                    if ($index < 0) $index += $patternCount;

                    $shiftToken = $pattern[$index];
                    
                    if ($shiftToken && $shiftToken !== 'I') {
                        // Find shift by ID or Code/Name
                        $shift = is_numeric($shiftToken) 
                            ? Shift::find($shiftToken) 
                            : Shift::where('name', 'like', "%($shiftToken)%")->first();

                        if ($shift) {
                            $upsertData[] = [
                                'employee_id' => $employee->id,
                                'date' => $dateObj->format('Y-m-d'),
                                'shift_id' => $shift->id,
                                'schedule_type_id' => $type->id,
                                'created_at' => $now,
                                'updated_at' => $now
                            ];
                        }
                    }
                }
            }

            if (!empty($upsertData)) {
                $chunks = array_chunk($upsertData, 500);
                foreach ($chunks as $chunk) {
                    Schedule::upsert($chunk, ['employee_id', 'date', 'schedule_type_id'], ['shift_id', 'updated_at']);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "Terjadi kesalahan: " . $e->getMessage());
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'activity' => 'generate_roster',
            'ip_address' => $request->ip(),
            'details' => Auth::user()->name . " men-generate roster otomatis untuk $logTag pada bulan " . $baseMonth->translatedFormat('F Y')
        ]);

        return back()->with('success', "Roster berhasil di-generate secara otomatis.");
    }

    public function reset(Request $request)
    {
        $request->validate([
            'month' => 'required|string',
            'schedule_type_id' => 'required|exists:schedule_types,id',
        ]);
        $date = Carbon::parse($request->month);
        $typeId = $request->schedule_type_id;
        $type = ScheduleType::find($typeId);

        Schedule::whereMonth('date', $date->month)
            ->whereYear('date', $date->year)
            ->where('schedule_type_id', $typeId)
            ->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'reset_schedule',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . " mereset jadwal '{$type->name}' bulan " . $date->translatedFormat('F Y')
        ]);

        return back()->with('success', "Jadwal '{$type->name}' bulan " . $date->translatedFormat('F Y') . " telah dibersihkan.");
    }

    public function export(Request $request)
    {
        set_time_limit(0); // Prevent timeout for large exports
        $monthStr = $request->month ?? now()->format('Y-m');
        $date = Carbon::parse($monthStr);
        $type = $request->type ?? 'pdf';

        $employees = Employee::with('work_unit')->orderBy('full_name')->get();
        $daysInMonth = $date->daysInMonth;
        $schedules = Schedule::with('shift')
            ->whereMonth('date', $date->month)
            ->whereYear('date', $date->year)
            ->get()
            ->groupBy('employee_id');

        if ($type === 'excel') {
            return $this->exportExcel($employees, $schedules, $date, $daysInMonth);
        }

        $pdf = Pdf::loadView('admin.schedules.pdf', compact('employees', 'schedules', 'date', 'daysInMonth'))->setPaper('a4', 'landscape');
        return $pdf->download("jadwal-dinas-{$monthStr}.pdf");
    }

    private function exportExcel($employees, $schedules, $date, $daysInMonth)
    {
        return Excel::download(new class($employees, $schedules, $date, $daysInMonth) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles, \Maatwebsite\Excel\Concerns\WithDrawings, \Maatwebsite\Excel\Concerns\WithCustomStartCell {
            protected $employees, $schedules, $date, $days;
            public function __construct($e, $s, $d, $days) { 
                $this->employees = $e; $this->schedules = $s; $this->date = $d; $this->days = $days; 
            }
            public function collection() {
                return $this->employees->map(function($emp, $index) {
                    $row = [$index + 1, $emp->full_name, $emp->nip];
                    for($d = 1; $d <= $this->days; $d++) {
                        $dateStr = $this->date->copy()->day($d)->format('Y-m-d');
                        $sched = $this->schedules->get($emp->id)?->firstWhere('date', $dateStr);
                        $row[] = $sched?->shift?->name ? substr($sched->shift->name, 0, 1) : '-';
                    }
                    return $row;
                });
            }
            public function headings(): array {
                $h = ['NO', 'NAMA PEGAWAI', 'NIP'];
                for($d = 1; $d <= $this->days; $d++) $h[] = $d;
                return $h;
            }
            public function startCell(): string { return 'A7'; }
            public function drawings() {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('Logo');
                $drawing->setPath(public_path('logo1.png'));
                $drawing->setHeight(80);
                $drawing->setCoordinates('A1');
                return $drawing;
            }
            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet) {
                $kop1 = Setting::getValue('kop_line_1', 'KEMENTERIAN IMIGRASI DAN PEMASYARAKATAN RI');
                $kop2 = Setting::getValue('kop_line_2', 'KANTOR WILAYAH KEMENTERIAN IMIGRASI DAN PEMASYARAKATAN');
                $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(2 + $this->days + 1); // NO + NAMA + NIP + Days
                $sheet->mergeCells("B1:{$endCol}1"); $sheet->setCellValue('B1', $kop1);
                $sheet->mergeCells("B2:{$endCol}2"); $sheet->setCellValue('B2', $kop2);
                $sheet->getStyle("B1:{$endCol}2")->getFont()->setBold(true)->setSize(12);
                $sheet->mergeCells("A5:{$endCol}5");
                $sheet->setCellValue('A5', 'JADWAL DINAS PEGAWAI PERIODE ' . strtoupper($this->date->translatedFormat('F Y')));
                $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(14)->setUnderline(true);
                $sheet->getStyle('A5')->getAlignment()->setHorizontal('center');
                $sheet->getStyle("A7:{$endCol}7")->getFont()->setBold(true);
                $sheet->getStyle("A7:{$endCol}7")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('F1F5F9');
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle("A7:{$endCol}{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $sheet->getColumnDimension('C')->setAutoSize(true);
                return [];
            }
        }, "jadwal-dinas-{$date->format('Y-m')}.xlsx");
    }
}
