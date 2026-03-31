<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Imports\EmployeesImport;
use App\Exports\EmployeesExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('nip', 'like', "%$search%")
                  ->orWhere('position', 'like', "%$search%");
            });
        }

        $employees = $query->latest()->get();
        return view('employees.index', compact('employees'));
    }

    public function show(Employee $employee)
    {
        // Redirect to employee folder in documents
        return redirect()->route('documents.employee', $employee->id);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nip' => 'required|unique:employees,nip',
            'full_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ]);

        $user = User::create([
            'name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make('password'),
            'role' => 'pegawai',
        ]);

        Employee::create([
            'user_id' => $user->id,
            'nip' => $request->nip,
            'full_name' => $request->full_name,
            'position' => $request->position,
        ]);

        return back()->with('success', 'Pegawai berhasil ditambahkan.');
    }

    public function importExcel(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        Excel::import(new EmployeesImport, $request->file('file'));
        return back()->with('success', 'Data pegawai berhasil diimpor.');
    }

    public function exportExcel() { return Excel::download(new EmployeesExport, 'daftar-pegawai.xlsx'); }
    
    public function exportPdf() {
        $employees = Employee::all();
        $pdf = Pdf::loadView('employees.pdf', compact('employees'));
        return $pdf->download('daftar-pegawai.pdf');
    }

    public function destroy(Employee $employee)
    {
        $employee->user->delete();
        return back()->with('success', 'Pegawai berhasil dihapus.');
    }
}
