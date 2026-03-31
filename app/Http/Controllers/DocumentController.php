<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Employee;
use App\Models\DocumentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->role === 'superadmin') {
            $documents = Document::with(['employee', 'category'])->latest()->get();
            $employees = Employee::all();
            $categories = DocumentCategory::all();
            return view('documents.index', compact('documents', 'employees', 'categories'));
        } else {
            // Regular employee only sees their own documents
            $employee = Employee::where('user_id', $user->id)->first();
            $documents = Document::where('employee_id', $employee->id)->with('category')->latest()->get();
            return view('documents.pegawai-index', compact('documents'));
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'document_category_id' => 'required|exists:document_categories,id',
            'title' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,xls,xlsx,doc,docx,csv|max:5120', // Max 5MB
        ]);

        $path = $request->file('file')->store('documents');

        Document::create([
            'employee_id' => $request->employee_id,
            'document_category_id' => $request->document_category_id,
            'title' => $request->title,
            'file_path' => $path,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Dokumen berhasil diunggah.');
    }

    public function download(Document $document)
    {
        // Security check
        $user = auth()->user();
        if ($user->role !== 'superadmin') {
            $employee = Employee::where('user_id', $user->id)->first();
            if ($document->employee_id !== $employee->id) {
                abort(403, 'Akses ditolak.');
            }
        }

        return Storage::download($document->file_path, $document->title . '.' . pathinfo($document->file_path, PATHINFO_EXTENSION));
    }

    public function destroy(Document $document)
    {
        Storage::delete($document->file_path);
        $document->delete();
        return back()->with('success', 'Dokumen berhasil dihapus.');
    }
}
