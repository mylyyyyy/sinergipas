<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Employee;
use App\Models\DocumentCategory;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $watermarkEnabled = \App\Models\Setting::getValue('watermark_enabled', 'on') === 'on';
        $watermarkText = \App\Models\Setting::getValue('watermark_text', 'SINERGI PAS JOMBANG');

        if ($user->role === 'superadmin') {
            $categories = DocumentCategory::withCount('documents')->get();
            $query = Employee::withCount(['documents' => function($q) use ($request) {
                if ($request->filled('category_id')) { $q->where('document_category_id', $request->category_id); }
                if ($request->status === 'pending') { $q->where('status', 'pending'); }
            }]);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('full_name', 'like', "%$search%")->orWhere('nip', 'like', "%$search%");
            }

            if ($request->status === 'pending') {
                $query->whereHas('documents', function($q) { $q->where('status', 'pending'); });
            }

            $employees = $query->get();
            return view('documents.index', compact('employees', 'categories', 'watermarkEnabled', 'watermarkText'));
        } else {
            $employee = Employee::where('user_id', $user->id)->first();
            $documents = Document::where('employee_id', $employee?->id)->with('category')->latest()->get();
            $categories = DocumentCategory::all();
            return view('documents.pegawai-index', compact('documents', 'categories', 'employee', 'watermarkEnabled', 'watermarkText'));
        }
    }

    public function showEmployeeFolders(Employee $employee, Request $request)
    {
        $query = Document::where('employee_id', $employee->id)->with('category');
        $watermarkEnabled = \App\Models\Setting::getValue('watermark_enabled', 'on') === 'on';
        $watermarkText = \App\Models\Setting::getValue('watermark_text', 'SINERGI PAS JOMBANG');
        
        if ($request->filled('category_id')) {
            $query->where('document_category_id', $request->category_id);
        }
        
        $documents = $query->latest()->get();
        $categories = DocumentCategory::all();
        return view('documents.show-folder', compact('employee', 'documents', 'categories', 'watermarkEnabled', 'watermarkText'));
    }

    public function verify(Document $document)
    {
        $document->status = 'verified';
        $document->verified_at = now();
        $document->is_locked = true;
        $document->save();

        AuditLog::create([
            'user_id' => auth()->id(),
            'document_id' => $document->id,
            'activity' => 'verify_document',
            'ip_address' => request()->ip(),
            'details' => auth()->user()->name . ' memverifikasi dokumen: ' . $document->title
        ]);

        return back()->with('success', 'Dokumen berhasil diverifikasi.');
    }

    public function preview(Document $document)
    {
        $user = auth()->user();
        if ($user->role !== 'superadmin') {
            $employee = Employee::where('user_id', $user->id)->first();
            if ($document->employee_id !== $employee?->id) abort(403);
        }
        $path = storage_path('app/private/' . $document->file_path);
        if (!file_exists($path)) abort(404);
        return response()->file($path, ['Content-Disposition' => 'inline']);
    }

    public function reject(Request $request, Document $document)
    {
        $request->validate(['rejection_reason' => 'required|string|max:500']);
        
        $document->status = 'rejected';
        $document->rejection_reason = $request->rejection_reason;
        $document->is_locked = false; // Allow re-upload
        $document->save();

        AuditLog::create([
            'user_id' => auth()->id(),
            'document_id' => $document->id,
            'activity' => 'reject_document',
            'ip_address' => request()->ip(),
            'details' => auth()->user()->name . ' menolak dokumen: ' . $document->title . ' Alasan: ' . $request->rejection_reason
        ]);

        return back()->with('success', 'Dokumen ditolak dengan alasan.');
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'document_category_id' => 'required|exists:document_categories,id',
            'title' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,xls,xlsx,csv,doc,docx|max:10240' // Expanded validation
        ]);
        
        $employeeId = $user->role === 'superadmin' ? $request->employee_id : Employee::where('user_id', $user->id)->first()->id;
        $path = $request->file('file')->store('documents', 'private');

        $doc = Document::create([
            'employee_id' => $employeeId,
            'document_category_id' => $request->document_category_id,
            'title' => $request->title,
            'file_path' => $path,
            'status' => 'pending',
        ]);

        AuditLog::create(['user_id' => $user->id, 'document_id' => $doc->id, 'activity' => 'upload', 'ip_address' => $request->ip(), 'details' => 'Unggah dokumen: ' . $doc->title]);
        return back()->with('success', 'Dokumen berhasil diunggah.');
    }

    public function toggleLock(Document $document)
    {
        $document->is_locked = !$document->is_locked;
        $document->save();
        return back()->with('success', 'Status kunci diperbarui.');
    }

    public function download(Document $document)
    {
        $extension = pathinfo($document->file_path, PATHINFO_EXTENSION);
        $filename = $document->title . '.' . $extension;
        return Storage::disk('private')->download($document->file_path, $filename);
    }

    public function destroy(Document $document)
    {
        Storage::disk('private')->delete($document->file_path);
        $document->delete();
        return back()->with('success', 'Terhapus.');
    }

    public function storeCategory(Request $request)
    {
        DocumentCategory::create([
            'name' => $request->name, 
            'slug' => \Illuminate\Support\Str::slug($request->name),
            'is_mandatory' => $request->has('is_mandatory')
        ]);
        return back()->with('success', 'Kategori baru ditambahkan.');
    }

    public function bulkAction(Request $request)
    {
        $ids = $request->ids;
        if ($request->action === 'delete') Document::whereIn('id', $ids)->delete();
        elseif ($request->action === 'lock') Document::whereIn('id', $ids)->update(['is_locked' => true]);
        elseif ($request->action === 'unlock') Document::whereIn('id', $ids)->update(['is_locked' => false]);
        return back()->with('success', 'Aksi massal berhasil.');
    }
}
