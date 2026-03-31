<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\Exports\EmployeesExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeeController extends Controller
{
    public function exportExcel()
    {
        return Excel::download(new EmployeesExport, 'daftar-pegawai.xlsx');
    }

    public function exportPdf()
    {
        $employees = Employee::all();
        $pdf = Pdf::loadView('employees.pdf', compact('employees'));
        return $pdf->download('daftar-pegawai.pdf');
    }

    public function index()
    {
        $employees = Employee::with('user')->latest()->get();
        return view('employees.index', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nip' => 'required|unique:employees,nip',
            'full_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ]);

        // Create User first
        $user = User::create([
            'name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make('password'), // Default password
            'role' => 'pegawai',
        ]);

        // Create Employee
        Employee::create([
            'user_id' => $user->id,
            'nip' => $request->nip,
            'full_name' => $request->full_name,
            'position' => $request->position,
        ]);

        return back()->with('success', 'Pegawai berhasil ditambahkan.');
    }

    public function destroy(Employee $employee)
    {
        $employee->user->delete(); // Cascades to employee
        return back()->with('success', 'Pegawai berhasil dihapus.');
    }
}
