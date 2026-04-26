<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Models\Document;
use App\Models\Position;
use App\Models\WorkUnit;
use App\Models\AuditLog;
use App\Models\Rank;
use App\Models\Tunkin;
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
        $query = Employee::with(['user', 'work_unit', 'position_relation', 'rank_relation', 'category', 'squad']);

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

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $employees = $query->orderBy('full_name')->get();
        $positions = Position::orderBy('name')->get();
        $workUnits = WorkUnit::orderBy('name')->get();
        $ranks = Rank::orderBy('name')->get();
        $categories = \App\Models\Category::orderBy('name')->get();
        $tunkins = Tunkin::orderBy('grade', 'desc')->get();
        $squads = \App\Models\Squad::orderBy('type')->orderBy('name')->get();

        return view('employees.index', compact('employees', 'positions', 'workUnits', 'ranks', 'categories', 'tunkins', 'squads'));
    }

    public function show(Employee $employee)
    {
        $employee->load(['user', 'work_unit', 'position_relation', 'rank_relation', 'audit_logs.user', 'squad']);
        $history = $employee->audit_logs()->with('user')->latest()->get();
        
        return view('employees.show', compact('employee', 'history'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'nip' => 'required|string|unique:employees,nip',
            'email' => 'required|email|unique:users,email',
            'position_id' => 'required|exists:positions,id',
            'work_unit_id' => 'required|exists:work_units,id',
            'rank_id' => 'nullable|exists:ranks,id',
            'tunkin_id' => 'nullable|exists:tunkins,id',
            'is_cpns' => 'nullable|boolean',
            'is_tubel' => 'nullable|boolean',
            'acting_tunkin_id' => 'nullable|exists:tunkins,id',
            'acting_start_date' => 'nullable|date',
            'category_id' => 'nullable|exists:categories,id',
            'password' => 'required|min:8',
            'squad_id' => 'nullable|exists:squads,id',
            'role_in_squad' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'pegawai',
        ]);

        $position = Position::find($request->position_id);
        $rank = $request->rank_id ? Rank::find($request->rank_id) : null;

        // Auto-correct employee_type based on position or squad
        $jNameUpper = strtoupper($position->name);
        $isJaga = str_contains($jNameUpper, 'JAGA') || str_contains($jNameUpper, 'PENGAMANAN') || str_contains($jNameUpper, 'PENJAGA') || $request->filled('squad_id');
        $employeeType = $isJaga ? 'regu_jaga' : 'non_regu_jaga';

        $employee = Employee::create([
            'user_id' => $user->id,
            'nip' => $request->nip,
            'full_name' => $request->full_name,
            'position' => $position->name,
            'position_id' => $request->position_id,
            'work_unit_id' => $request->work_unit_id,
            'rank_id' => $request->rank_id,
            'tunkin_id' => $request->tunkin_id,
            'is_cpns' => $request->has('is_cpns'),
            'is_tubel' => $request->has('is_tubel'),
            'acting_tunkin_id' => $request->acting_tunkin_id,
            'acting_start_date' => $request->acting_start_date,
            'rank_class' => $rank?->name,
            'category_id' => $request->category_id,
            'employee_type' => $employeeType,
            'squad_id' => $request->squad_id,
            'role_in_squad' => $request->role_in_squad,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'create_employee',
            'auditable_id' => $employee->id,
            'auditable_type' => Employee::class,
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . ' mendaftarkan pegawai baru: ' . $request->full_name,
            'new_values' => $employee->toArray()
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
            'rank_id' => 'nullable|exists:ranks,id',
            'tunkin_id' => 'nullable|exists:tunkins,id',
            'is_cpns' => 'nullable|boolean',
            'is_tubel' => 'nullable|boolean',
            'acting_tunkin_id' => 'nullable|exists:tunkins,id',
            'acting_start_date' => 'nullable|date',
            'category_id' => 'nullable|exists:categories,id',
            'password' => 'nullable|min:8',
            'squad_id' => 'nullable|exists:squads,id',
            'role_in_squad' => 'nullable|string',
        ]);

        $oldValues = $employee->toArray();
        $oldUserValues = $employee->user->only(['name', 'email']);

        $employee->user->update([
            'name' => $request->full_name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $employee->user->update(['password' => Hash::make($request->password)]);
        }

        $position = Position::find($request->position_id);
        $rank = $request->rank_id ? Rank::find($request->rank_id) : null;

        $jNameUpper = strtoupper($position->name);
        $isJaga = str_contains($jNameUpper, 'JAGA') || str_contains($jNameUpper, 'PENGAMANAN') || str_contains($jNameUpper, 'PENJAGA') || $request->filled('squad_id');
        $employeeType = $isJaga ? 'regu_jaga' : 'non_regu_jaga';

        $employee->update([
            'nip' => $request->nip,
            'full_name' => $request->full_name,
            'position' => $position->name,
            'position_id' => $request->position_id,
            'work_unit_id' => $request->work_unit_id,
            'rank_id' => $request->rank_id,
            'tunkin_id' => $request->tunkin_id,
            'is_cpns' => $request->has('is_cpns'),
            'is_tubel' => $request->has('is_tubel'),
            'acting_tunkin_id' => $request->acting_tunkin_id,
            'acting_start_date' => $request->acting_start_date,
            'rank_class' => $rank?->name,
            'category_id' => $request->category_id,
            'employee_type' => $employeeType,
            'squad_id' => $request->squad_id,
            'role_in_squad' => $request->role_in_squad,
        ]);

        // Real-time Sync: Update attendance for this employee in the current month if rank changed
        if ($rank) {
            \App\Models\Attendance::where('employee_id', $employee->id)
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->update(['allowance_amount' => $rank->meal_allowance]);
        }

        $newValues = $employee->fresh()->toArray();
        
        // Only log if something changed
        $changedFields = array_diff_assoc($newValues, $oldValues);
        
        // Remove timestamps from comparison
        unset($changedFields['updated_at']);

        if (!empty($changedFields) || $employee->user->wasChanged()) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'activity' => 'update_employee',
                'auditable_id' => $employee->id,
                'auditable_type' => Employee::class,
                'ip_address' => $request->ip(),
                'details' => auth()->user()->name . ' memperbarui data pegawai: ' . $request->full_name,
                'old_values' => $oldValues,
                'new_values' => $newValues
            ]);
        }

        return back()->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function destroy(Employee $employee)
    {
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $name = $employee->full_name;
            $user = $employee->user;
            
            if ($employee->getRawOriginal('photo')) {
                Storage::disk('public')->delete($employee->getRawOriginal('photo'));
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'activity' => 'delete_employee',
                'ip_address' => request()->ip(),
                'details' => auth()->user()->name . ' menghapus data pegawai: ' . $name,
                'old_values' => $employee->toArray()
            ]);

            $employee->delete();
            if ($user) $user->delete();

            \Illuminate\Support\Facades\DB::commit();
            return back()->with('success', 'Data pegawai berhasil dihapus.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Gagal menghapus pegawai: ' . $e->getMessage());
        }
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->ids;
        if (!$ids) return back()->with('error', 'Pilih data yang ingin dihapus.');

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $employees = Employee::whereIn('id', $ids)->get();
            foreach ($employees as $emp) {
                if ($emp->getRawOriginal('photo')) {
                    Storage::disk('public')->delete($emp->getRawOriginal('photo'));
                }
                
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'activity' => 'delete_employee',
                    'ip_address' => $request->ip(),
                    'details' => auth()->user()->name . ' menghapus data pegawai: ' . $emp->full_name,
                    'old_values' => $emp->toArray()
                ]);

                if ($emp->user) $emp->user->delete();
                $emp->delete();
            }

            \Illuminate\Support\Facades\DB::commit();
            return back()->with('success', count($ids) . ' data pegawai berhasil dihapus.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Gagal menghapus data masal: ' . $e->getMessage());
        }
    }

    public function destroyAll(Request $request)
    {
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $currentUserId = auth()->id();
            $employees = Employee::where('user_id', '!=', $currentUserId)->get();
            $count = $employees->count();

            foreach ($employees as $emp) {
                if ($emp->getRawOriginal('photo')) {
                    Storage::disk('public')->delete($emp->getRawOriginal('photo'));
                }
                if ($emp->user) {
                    $emp->user->delete();
                }
                $emp->delete();
            }

            AuditLog::create([
                'user_id' => $currentUserId,
                'activity' => 'destroy_all_employees',
                'ip_address' => $request->ip(),
                'details' => auth()->user()->name . ' menghapus SELURUH data pegawai (' . $count . ' data)'
            ]);

            \Illuminate\Support\Facades\DB::commit();
            return back()->with('success', 'Seluruh data pegawai berhasil dihapus.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function importExcel(Request $request)
    {
        set_time_limit(0);
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        
        try {
            $import = new EmployeesImport;
            Excel::import($import, $request->file('file'));
            
            AuditLog::create([
                'user_id' => auth()->id(),
                'activity' => 'import_employees',
                'ip_address' => $request->ip(),
                'details' => auth()->user()->name . ' mengimpor ' . $import->importedCount . ' data pegawai via Excel'
            ]);

            return back()->with('success', $import->importedCount . ' data pegawai berhasil diimpor.');
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

        if (ob_get_length()) ob_end_clean();
        $pdf = Pdf::loadView('employees.pdf', compact('employees'));
        return $pdf->download('daftar-pegawai-jombang.pdf');
    }
}
