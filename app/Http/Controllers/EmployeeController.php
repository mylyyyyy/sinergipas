<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Models\Document;
use App\Models\Position;
use App\Models\WorkUnit;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EmployeesImport;
use App\Exports\EmployeesExport;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['user', 'work_unit', 'position_relation']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('nip', 'like', "%$search%");
            });
        }

        if ($request->filled('work_unit_id')) {
            $query->where('work_unit_id', $request->work_unit_id);
        }

        $employees = $query->latest()->paginate(10)->withQueryString();
        $positions = Position::orderBy('name')->get();
        $workUnits = WorkUnit::orderBy('name')->get();

        return view('employees.index', compact('employees', 'positions', 'workUnits'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'nip' => 'required|string|unique:employees,nip',
            'email' => 'required|email|unique:users,email',
            'position_id' => 'required|exists:positions,id',
            'work_unit_id' => 'required|exists:work_units,id',
            'password' => 'required|min:8',
            'rank_class' => 'nullable|string',
            'employee_type' => 'required|in:regu_jaga,non_regu_jaga',
            'picket_regu' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'pegawai',
        ]);

        $position = Position::find($request->position_id);

        Employee::create([
            'user_id' => $user->id,
            'nip' => $request->nip,
            'full_name' => $request->full_name,
            'position' => $position->name,
            'position_id' => $request->position_id,
            'work_unit_id' => $request->work_unit_id,
            'rank_class' => $request->rank_class,
            'employee_type' => $request->employee_type,
            'picket_regu' => $request->picket_regu,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'create_employee',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . ' mendaftarkan pegawai baru: ' . $request->full_name
        ]);

        return back()->with('success', 'Pegawai berhasil didaftarkan.');
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'nip' => 'required|string|unique:employees,nip,' . $employee->id,
            'email' => 'required|email|unique:users,email,' . $employee->user_id,
            'position_id' => 'required|exists:positions,id',
            'work_unit_id' => 'required|exists:work_units,id',
            'password' => 'nullable|min:8',
            'rank_class' => 'nullable|string',
            'employee_type' => 'required|in:regu_jaga,non_regu_jaga',
            'picket_regu' => 'nullable|string',
        ]);

        $employee->user->update([
            'name' => $request->full_name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $employee->user->update(['password' => Hash::make($request->password)]);
        }

        $position = Position::find($request->position_id);

        $employee->update([
            'nip' => $request->nip,
            'full_name' => $request->full_name,
            'position' => $position->name,
            'position_id' => $request->position_id,
            'work_unit_id' => $request->work_unit_id,
            'rank_class' => $request->rank_class,
            'employee_type' => $request->employee_type,
            'picket_regu' => $request->picket_regu,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'update_employee',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . ' memperbarui data pegawai: ' . $request->full_name
        ]);

        return back()->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function destroy(Employee $employee)
    {
        $name = $employee->full_name;
        $user = $employee->user;
        
        if ($employee->getRawOriginal('photo')) {
            Storage::disk('public')->delete($employee->getRawOriginal('photo'));
        }

        $employee->delete();
        if ($user) $user->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'delete_employee',
            'ip_address' => request()->ip(),
            'details' => auth()->user()->name . ' menghapus data pegawai: ' . $name
        ]);

        return back()->with('success', 'Data pegawai berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->ids;
        if (!$ids) return back()->with('error', 'Pilih data yang ingin dihapus.');

        $employees = Employee::whereIn('id', $ids)->get();
        foreach ($employees as $emp) {
            if ($emp->getRawOriginal('photo')) {
                Storage::disk('public')->delete($emp->getRawOriginal('photo'));
            }
            if ($emp->user) $emp->user->delete();
            $emp->delete();
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'bulk_delete_employee',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . ' menghapus ' . count($ids) . ' data pegawai secara massal'
        ]);

        return back()->with('success', count($ids) . ' data pegawai berhasil dihapus.');
    }

    public function importExcel(Request $request)
    {
        set_time_limit(0);
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        
        try {
            Excel::import(new EmployeesImport, $request->file('file'));
            
            AuditLog::create([
                'user_id' => auth()->id(),
                'activity' => 'import_employees',
                'ip_address' => $request->ip(),
                'details' => auth()->user()->name . ' mengimpor data pegawai via Excel'
            ]);

            return back()->with('success', 'Data pegawai berhasil diimpor.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal impor: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'export_employees_excel',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . ' mengekspor data pegawai ke Excel'
        ]);

        return Excel::download(new EmployeesExport, 'daftar-pegawai-jombang.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $employees = Employee::with('work_unit')->get();
        
        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'export_employees_pdf',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . ' mengekspor data pegawai ke PDF'
        ]);

        $pdf = Pdf::loadView('employees.pdf', compact('employees'));
        return $pdf->download('daftar-pegawai-jombang.pdf');
    }
}
