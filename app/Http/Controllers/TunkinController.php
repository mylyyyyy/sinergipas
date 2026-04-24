<?php

namespace App\Http\Controllers;

use App\Models\Tunkin;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Shift;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TunkinRecapExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\PayrollService;

class TunkinController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    private function getRecapData($request)
    {
        $monthStr = $request->month ?? now()->format('Y-m');
        $date = Carbon::parse($monthStr . '-01');
        
        $query = Employee::with(['tunkin', 'rank_relation', 'position_relation', 'work_unit']);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('nip', 'like', "%$search%");
            });
        }
        
        $employees = $query->orderBy('full_name')->get();

        foreach ($employees as $emp) {
            $payroll = $this->payrollService->calculateMonthlyPayroll($emp, $monthStr);
            
            $emp->total_attendance = $payroll['meal_allowance_days'];
            $emp->meal_allowance = $payroll['total_meal_allowance'];
            $emp->base_tunkin = $emp->tunkin->nominal ?? 0;
            $emp->potongan = $payroll['total_potongan_rupiah'];
            $emp->deduction_percentage = $payroll['deduction_percentage'];
            $emp->total_tunkin = $payroll['tunkin_final'];
            $emp->grand_total = $payroll['grand_total'];
            $emp->violation_note = $payroll['violation_note'];
        }

        return [$employees, $monthStr, $date];
    }

    public function index(Request $request)
    {
        $tab = $request->tab ?? 'nominal';
        
        if ($tab === 'recap') {
            list($employees, $monthStr, $date) = $this->getRecapData($request);
            return view('admin.tunkins.index', compact('tab', 'employees', 'monthStr'));
        }

        $tunkins = Tunkin::withCount('employees')->orderBy('grade', 'desc')->get();
        return view('admin.tunkins.index', compact('tab', 'tunkins'));
    }

    public function exportRecapExcel(Request $request)
    {
        set_time_limit(300);
        list($employees, $monthStr) = $this->getRecapData($request);
        return Excel::download(new TunkinRecapExport($employees, $monthStr), "rekap-tunkin-{$monthStr}.xlsx");
    }

    public function exportRecapPdf(Request $request)
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');
        
        list($employees, $monthStr, $date) = $this->getRecapData($request);
        
        // Pre-convert logo to base64 for faster rendering
        $logoPath = public_path('logo1.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoBase64 = 'data:image/png;base64,' . $logoData;
        }

        $pdf = Pdf::loadView('admin.tunkins.pdf-recap', compact('employees', 'monthStr', 'date', 'logoBase64'))
                  ->setPaper('a4', 'landscape');
        
        return $pdf->download("rekap-tunkin-{$monthStr}.pdf");
    }

    public function exportIndividualPdf(Request $request, Employee $employee)
    {
        set_time_limit(120);
        $monthStr = $request->month ?? now()->format('Y-m');
        $date = Carbon::parse($monthStr . '-01');
        
        $employee->load(['tunkin', 'rank_relation', 'position_relation', 'work_unit']);
        $payroll = $this->payrollService->calculateMonthlyPayroll($employee, $monthStr);
        
        $logoPath = public_path('logo1.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoBase64 = 'data:image/png;base64,' . $logoData;
        }

        $pdf = Pdf::loadView('admin.tunkins.pdf-individual', array_merge([
            'employee' => $employee,
            'monthStr' => $monthStr,
            'date' => $date,
            'logoBase64' => $logoBase64,
            'attendances' => $payroll['meal_allowance_days'],
            'mealAllowancePerDay' => $employee->rank_relation->meal_allowance ?? 0,
            'totalMealAllowance' => $payroll['total_meal_allowance'],
            'baseTunkin' => $employee->tunkin->nominal ?? 0,
            'potongan' => $payroll['total_potongan_rupiah'],
            'totalTerima' => $payroll['grand_total'],
            'deduction_percentage' => $payroll['deduction_percentage'],
        ], $payroll));
        
        return $pdf->download("slip-tunkin-{$employee->nip}-{$monthStr}.pdf");
    }

    public function showEmployee(Request $request, Employee $employee)
    {
        $monthStr = $request->month ?? now()->format('Y-m');
        $date = Carbon::parse($monthStr . '-01');
        
        $employee->load(['tunkin', 'rank_relation', 'position_relation', 'work_unit']);
        $payroll = $this->payrollService->calculateMonthlyPayroll($employee, $monthStr);
        
        // Detailed attendance for the month for preview table
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereMonth('date', $date->month)
            ->whereYear('date', $date->year)
            ->orderBy('date', 'asc')
            ->get();
            
        return view('admin.tunkins.show-employee', array_merge([
            'employee' => $employee,
            'attendances' => $attendances,
            'monthStr' => $monthStr,
            'date' => $date,
            'mealAllowancePerDay' => $employee->rank_relation->meal_allowance ?? 0,
            'totalMealAllowance' => $payroll['total_meal_allowance'],
            'baseTunkin' => $employee->tunkin->nominal ?? 0,
            'potongan' => $payroll['total_potongan_rupiah'],
            'deduction_percentage' => $payroll['deduction_percentage'],
        ], $payroll));
    }

    public function update(Request $request, Tunkin $tunkin)
    {
        $request->validate([
            'nominal' => 'required|numeric|min:0',
        ]);

        $tunkin->update([
            'nominal' => $request->nominal,
        ]);

        return response()->json(['success' => true, 'message' => 'Besaran Tunkin berhasil diperbarui.']);
    }
}
