<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Services\PayrollService;
use App\Services\ScheduleService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class SelfServiceController extends Controller
{
    protected $payrollService;
    protected $scheduleService;

    public function __construct(PayrollService $payrollService, ScheduleService $scheduleService)
    {
        $this->payrollService = $payrollService;
        $this->scheduleService = $scheduleService;
    }

    public function myPayroll(Request $request)
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            return back()->with('error', 'Data pegawai tidak ditemukan.');
        }

        $monthStr = $request->month ?? now()->format('Y-m');
        $date = Carbon::parse($monthStr . '-01');
        
        $payroll = $this->payrollService->calculateMonthlyPayroll($employee, $monthStr);
        
        return view('employees.self-service.payroll', array_merge([
            'employee' => $employee,
            'monthStr' => $monthStr,
            'date' => $date,
            'base_tunkin' => $payroll['base_tunkin'],
            'grand_total' => $payroll['grand_total'],
            'total_potongan_rupiah' => $payroll['total_potongan_rupiah'],
            'total_meal_allowance' => $payroll['total_meal_allowance'],
        ], $payroll));
    }

    public function downloadMySlip(Request $request)
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');
        
        $user = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            return back()->with('error', 'Data pegawai tidak ditemukan.');
        }

        $monthStr = $request->month ?? now()->format('Y-m');
        $date = Carbon::parse($monthStr . '-01');

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
            'baseTunkin' => $payroll['base_tunkin'],
            'potongan' => $payroll['total_potongan_rupiah'],
            'totalTerima' => $payroll['grand_total'],
            'deduction_percentage' => $payroll['deduction_percentage'],
        ], $payroll));

        return $pdf->download("Slip-Gaji-{$employee->nip}-{$monthStr}.pdf");
    }

    public function myAttendance(Request $request)
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            return back()->with('error', 'Data pegawai tidak ditemukan.');
        }

        $monthStr = $request->month ?? now()->format('Y-m');
        
        $payroll = $this->payrollService->calculateMonthlyPayroll($employee, $monthStr);
        
        return view('employees.self-service.attendance', [
            'employee' => $employee,
            'monthStr' => $monthStr,
            'logs' => $payroll['processed_logs']
        ]);
    }
}
