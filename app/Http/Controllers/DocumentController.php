<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Employee;
use App\Models\DocumentCategory;
use App\Models\DocumentVersion;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;
use Intervention\Image\ImageManagerStatic as Image;
use setasign\Fpdi\Fpdi;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $watermarkEnabled = Setting::getValue('watermark_enabled', 'on') === 'on';
        $watermarkText = Setting::getValue('watermark_text', 'SINERGI PAS JOMBANG');

        if ($user->role === 'superadmin') {
            $categories = DocumentCategory::withCount('documents')->get();
            
            // Advanced Employee Query with Filters
            $query = Employee::query();
            
            // Search by Name or NIP
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('full_name', 'like', "%$search%")
                      ->orWhere('nip', 'like', "%$search%");
                });
            }

            // Filter by Status or Year (via Document relation)
            if ($request->filled('status') || $request->filled('year') || $request->filled('category_id')) {
                $query->whereHas('documents', function($q) use ($request) {
                    if ($request->filled('status')) { $q->where('status', $request->status); }
                    if ($request->filled('year')) { $q->whereYear('created_at', $request->year); }
                    if ($request->filled('category_id')) { $q->where('document_category_id', $request->category_id); }
                });
            }

            $employees = $query->withCount(['documents' => function($q) use ($request) {
                if ($request->filled('category_id')) { $q->where('document_category_id', $request->category_id); }
                if ($request->filled('status')) { $q->where('status', $request->status); }
            }])->get();

            $years = Document::selectRaw('YEAR(created_at) as year')->distinct()->orderBy('year', 'desc')->pluck('year');

            return view('documents.index', compact('employees', 'categories', 'watermarkEnabled', 'watermarkText', 'years'));
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

    public function previewVersion(DocumentVersion $version)
    {
        $user = auth()->user();
        $document = $version->document;
        if ($user->role !== 'superadmin') {
            $employee = Employee::where('user_id', $user->id)->first();
            if ($document->employee_id !== $employee?->id) abort(403);
        }
        $path = storage_path('app/private/' . $version->file_path);
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
        $user = auth()->user();
        
        // Prevent download if locked and not superadmin
        if ($document->is_locked && $user->role !== 'superadmin') {
            return back()->with('error', 'Dokumen ini dikunci oleh Admin dan tidak dapat diunduh.');
        }

        $extension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
        $filename = $document->title . '.' . $extension;
        $fullPath = storage_path('app/private/' . $document->file_path);

        if (!file_exists($fullPath)) {
            return back()->with('error', 'File tidak ditemukan di server.');
        }

        $watermarkEnabled = Setting::getValue('watermark_enabled', 'on') === 'on';
        $watermarkText = Setting::getValue('watermark_text', 'SINERGI PAS JOMBANG');

        if ($watermarkEnabled) {
            // --- PDF WATERMARKING ---
            if ($extension === 'pdf') {
                try {
                    $pdf = new Fpdi();
                    $pageCount = $pdf->setSourceFile($fullPath);

                    for ($n = 1; $n <= $pageCount; $n++) {
                        $tplIdx = $pdf->importPage($n);
                        $specs = $pdf->getTemplateSize($tplIdx);
                        $pdf->AddPage($specs['orientation'], [$specs['width'], $specs['height']]);
                        $pdf->useTemplate($tplIdx);

                        // Set watermark font & color
                        $pdf->SetFont('Helvetica', 'B', 40);
                        $pdf->SetTextColor(200, 200, 200); // Light gray
                        
                        // Diagonal watermark
                        $pdf->SetAlpha(0.2); // Not supported directly in FPDI core without extension, but we'll use light color
                        $textWidth = $pdf->GetStringWidth($watermarkText);
                        
                        // Position it in the center (approx)
                        $pdf->SetXY($specs['width']/2 - $textWidth/2, $specs['height']/2);
                        // Rotation is tricky in basic FPDI, so we'll just put it center
                        $pdf->Write(0, $watermarkText);
                    }

                    $tempPdf = tempnam(sys_get_temp_dir(), 'pdf_wm');
                    $pdf->Output('F', $tempPdf);
                    return response()->download($tempPdf, $filename)->deleteFileAfterSend(true);
                } catch (\Exception $e) {
                    // Fallback to normal download if watermarking fails
                    return Storage::disk('private')->download($document->file_path, $filename);
                }
            }

            // --- IMAGE WATERMARKING ---
            if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                try {
                    $img = Image::make($fullPath);
                    $img->text($watermarkText, $img->width() / 2, $img->height() / 2, function($font) {
                        $font->file(public_path('fonts/PlusJakartaSans-ExtraBold.ttf')); // Ensure font exists or use default
                        $font->size(60);
                        $font->color([255, 255, 255, 0.2]); // White with 20% alpha
                        $font->align('center');
                        $font->valign('middle');
                        $font->angle(45);
                    });

                    return $img->response($extension);
                } catch (\Exception $e) {
                    // Fallback to normal download
                    return Storage::disk('private')->download($document->file_path, $filename);
                }
            }
        }

        return Storage::disk('private')->download($document->file_path, $filename);
    }

    public function destroy(Document $document)
    {
        $title = $document->title;
        Storage::disk('private')->delete($document->file_path);
        $document->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'delete_document',
            'ip_address' => request()->ip(),
            'details' => auth()->user()->name . ' menghapus dokumen: ' . $title
        ]);

        return back()->with('success', 'Terhapus.');
    }

    public function storeCategory(Request $request)
    {
        $cat = DocumentCategory::create([
            'name' => $request->name, 
            'slug' => \Illuminate\Support\Str::slug($request->name),
            'is_mandatory' => $request->has('is_mandatory')
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'create_category',
            'ip_address' => request()->ip(),
            'details' => auth()->user()->name . ' membuat kategori baru: ' . $cat->name
        ]);

        return back()->with('success', 'Kategori baru ditambahkan.');
    }

    public function bulkAction(Request $request)
    {
        $ids = $request->ids;
        if (empty($ids)) return back()->with('error', 'Tidak ada data terpilih.');

        if ($request->action === 'delete') {
            Document::whereIn('id', $ids)->delete();
            $activity = 'bulk_delete';
            $msg = 'menghapus ' . count($ids) . ' dokumen';
        } elseif ($request->action === 'lock') {
            Document::whereIn('id', $ids)->update(['is_locked' => true]);
            $activity = 'bulk_lock';
            $msg = 'mengunci ' . count($ids) . ' dokumen';
        } elseif ($request->action === 'unlock') {
            Document::whereIn('id', $ids)->update(['is_locked' => false]);
            $activity = 'bulk_unlock';
            $msg = 'membuka kunci ' . count($ids) . ' dokumen';
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => $activity,
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . ' ' . $msg
        ]);

        return back()->with('success', 'Aksi massal berhasil.');
    }

    public function destroyCategory(DocumentCategory $category)
    {
        if ($category->documents()->count() > 0) {
            return back()->with('error', 'Kategori ini tidak bisa dihapus karena masih memiliki dokumen terkait.');
        }
        $name = $category->name;
        $category->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'delete_category',
            'ip_address' => request()->ip(),
            'details' => auth()->user()->name . ' menghapus kategori: ' . $name
        ]);

        return back()->with('success', 'Kategori berhasil dihapus.');
    }

    public function storeRevision(Request $request, Document $document)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,xls,xlsx,csv,doc,docx|max:10240'
        ]);

        DB::beginTransaction();
        try {
            // Get current version number
            $currentVersionNum = DocumentVersion::where('document_id', $document->id)->max('version_number') ?? 0;
            $newVersionNum = $currentVersionNum + 1;

            // Save old file to versions
            DocumentVersion::create([
                'document_id' => $document->id,
                'file_path' => $document->file_path,
                'version_number' => $newVersionNum,
            ]);

            // Save new file
            $path = $request->file('file')->store('documents', 'private');
            $document->update([
                'file_path' => $path,
                'status' => 'pending', // Re-verify
            ]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'document_id' => $document->id,
                'activity' => 'revision',
                'ip_address' => $request->ip(),
                'details' => 'Unggah revisi untuk: ' . $document->title
            ]);

            DB::commit();
            return back()->with('success', 'Revisi berhasil diunggah sebagai versi terbaru.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengunggah revisi: ' . $e->getMessage());
        }
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|mimes:pdf,jpg,jpeg,png,xls,xlsx,csv,doc,docx|max:10240',
            'categories.*' => 'required|exists:document_categories,id',
            'titles.*' => 'required|string|max:255',
        ]);

        $user = auth()->user();
        $employeeId = $user->role === 'superadmin' ? $request->employee_id : Employee::where('user_id', $user->id)->first()->id;

        DB::beginTransaction();
        try {
            foreach ($request->file('files') as $index => $file) {
                $path = $file->store('documents', 'private');
                $doc = Document::create([
                    'employee_id' => $employeeId,
                    'document_category_id' => $request->categories[$index],
                    'title' => $request->titles[$index],
                    'file_path' => $path,
                    'status' => 'pending',
                ]);

                AuditLog::create([
                    'user_id' => $user->id,
                    'document_id' => $doc->id,
                    'activity' => 'upload',
                    'ip_address' => $request->ip(),
                    'details' => 'Bulk upload: ' . $doc->title
                ]);
            }
            DB::commit();
            return back()->with('success', count($request->file('files')) . ' dokumen berhasil diunggah.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal melakukan bulk upload: ' . $e->getMessage());
        }
    }
}
