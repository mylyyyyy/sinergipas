<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Document;
use App\Models\WorkUnit;
use App\Models\DocumentCategory;
use App\Models\ReportIssue;
use App\Models\Setting;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller

{
    public function index(Request $request)
    {
        $user = auth()->user();
        $workUnitId = $request->work_unit_id;

        // --- ADMIN VIEW DATA ---
        if ($user->role === 'superadmin') {
            $employeeQuery = Employee::query();
            if ($workUnitId) { $employeeQuery->where('work_unit_id', $workUnitId); }

            $totalEmployees = (clone $employeeQuery)->count();
            $totalDocuments = Document::count();
            $docsToday = Document::whereDate('created_at', now())->count();
            $pendingDocs = Document::where('status', 'pending')->count();
            $openIssues = ReportIssue::where('status', 'open')->count();

            // Storage Analytics
            // Calculate actual storage used in MB
            $totalSizeBytes = 0;
            $disk = Storage::disk('private');
            if ($disk->exists('documents')) {
                $files = $disk->allFiles('documents');
                foreach ($files as $file) {
                    $totalSizeBytes += $disk->size($file);
                }
            }
            $storageUsed = round($totalSizeBytes / (1024 * 1024), 2); // MB

            // Compliance Tracking: Find employees missing mandatory documents
            $mandatoryCategories = DocumentCategory::where('is_mandatory', true)->get(['id', 'name']);
            
            // Auto-fix: If no mandatory categories exist, mark 'Gaji' and 'SK' as mandatory for demo
            if ($mandatoryCategories->isEmpty()) {
                DocumentCategory::where('slug', 'like', '%gaji%')
                    ->orWhere('slug', 'like', '%sk-%')
                    ->update(['is_mandatory' => true]);
                $mandatoryCategories = DocumentCategory::where('is_mandatory', true)->get();
            }

            $mandatoryCategoryIds = $mandatoryCategories->pluck('id');
            $totalMandatoryCategories = $mandatoryCategoryIds->count();

            $nonCompliantEmployees = collect();
            if ($totalMandatoryCategories > 0) {
                $complianceQuery = Employee::with([
                    'user',
                    'documents' => function ($query) use ($mandatoryCategoryIds) {
                        $query->select('id', 'employee_id', 'document_category_id')
                            ->whereIn('document_category_id', $mandatoryCategoryIds)
                            ->where('status', 'verified');
                    },
                ])->whereHas('user', function($q) {
                    $q->where('role', 'pegawai');
                });

                if ($workUnitId) {
                    $complianceQuery->where('work_unit_id', $workUnitId);
                }

                $whatsAppNumber = $this->normalizeWhatsAppNumber(
                    Setting::getValue('compliance_whatsapp_number', '628123456789')
                );

                $nonCompliantEmployees = $complianceQuery
                    ->get()
                    ->map(function ($employee) use ($totalMandatoryCategories, $whatsAppNumber) {
                        $uploadedCount = $employee->documents->pluck('document_category_id')->unique()->count();

                        if ($uploadedCount >= $totalMandatoryCategories) {
                            return null;
                        }

                        $employee->setAttribute('uploaded_mandatory_count', $uploadedCount);
                        $employee->setAttribute('total_mandatory_count', $totalMandatoryCategories);
                        $employee->setAttribute('compliance_percent', (int) round(($uploadedCount / $totalMandatoryCategories) * 100));
                        $employee->setAttribute('whatsapp_link', $this->buildWhatsAppReminderLink($whatsAppNumber, $employee->full_name));

                        return $employee;
                    })
                    ->filter()
                    ->values();
            }

            $nonCompliantEmployeesTotal = $nonCompliantEmployees->count();
            $nonCompliantPreviewLimit = 10;
            $nonCompliantEmployees = $nonCompliantEmployees
                ->take($nonCompliantPreviewLimit)
                ->values();

            // Unit Performance
            $unitPerformance = WorkUnit::withCount('employees')->get();

            $latestEmployees = (clone $employeeQuery)->with(['user', 'work_unit'])->latest()->take(5)->get();
            $recentLogs = AuditLog::with('user')->latest()->take(5)->get();
            $chartData = DocumentCategory::withCount('documents')->get();
            $workUnits = WorkUnit::all();
            
            // Widget Settings
            $widgets = Setting::where('key', 'like', 'widget_%')->pluck('value', 'key');

            return view('dashboard', compact(
                'totalEmployees', 'totalDocuments', 'docsToday', 'pendingDocs', 
                'openIssues', 'storageUsed', 'unitPerformance', 
                'latestEmployees', 'chartData', 'workUnits', 'nonCompliantEmployees',
                'nonCompliantEmployeesTotal', 'nonCompliantPreviewLimit',
                'widgets', 'recentLogs', 'totalMandatoryCategories'
            ));
        } 
        
        // --- PEGAWAI VIEW DATA ---
        else {
            $employee = Employee::with(['work_unit', 'position_relation'])->where('user_id', $user->id)->first();
            $myDocs = Document::where('employee_id', $employee?->id)->with('category')->get();
            
            $myDocumentsCount = $myDocs->count();
            $verifiedDocs = $myDocs->where('status', 'verified')->count();
            $recentDocuments = Document::where('employee_id', $employee?->id)
                ->with('category')
                ->latest()
                ->take(5)
                ->get();
            
            // Career Progress: Percentage of mandatory categories uploaded
            $mandatoryCats = DocumentCategory::where('is_mandatory', true)->get();
            $totalMandatory = $mandatoryCats->count();
            
            if ($totalMandatory > 0) {
                $uploadedMandatory = Document::where('employee_id', $employee?->id)
                    ->whereIn('document_category_id', $mandatoryCats->pluck('id'))
                    ->where('status', 'verified')
                    ->distinct('document_category_id')
                    ->count();
                $careerProgress = ($uploadedMandatory / $totalMandatory) * 100;
            } else {
                $careerProgress = 100;
            }

            // Latest Salary
            $latestSalary = Document::where('employee_id', $employee?->id)
                ->whereHas('category', function($q) { $q->where('slug', 'like', '%gaji%'); })
                ->latest()
                ->first();

            return view('dashboard-pegawai', compact(
                'myDocumentsCount', 'verifiedDocs', 'careerProgress', 'latestSalary', 
                'employee', 'recentDocuments'
            ));
        }
    }

    public function exportPdf(Request $request)
    {
        $data = $this->getDashboardData();

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'export_dashboard_pdf',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . ' mengekspor ringkasan dashboard ke PDF',
        ]);

        $pdf = Pdf::loadView('reports.dashboard', $data);
        return $pdf->download('laporan-sinergi-pas.pdf');
    }

    public function exportExcel(Request $request)
    {
        $data = $this->getDashboardData();

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'export_dashboard_excel',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . ' mengekspor ringkasan dashboard ke Excel',
        ]);

        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            protected $data;
            public function __construct($data) { $this->data = $data; }
            public function collection() {
                return collect([
                    ['Total Pegawai', $this->data['totalEmployees']],
                    ['Total Dokumen', $this->data['totalDocuments']],
                    ['Dokumen Baru Hari Ini', $this->data['docsToday']],
                    ['Menunggu Verifikasi', $this->data['pendingDocs']],
                    ['Laporan Masalah Terbuka', $this->data['openIssues']],
                    ['Penyimpanan Digunakan (MB)', $this->data['storageUsed']],
                ]);
            }
            public function headings(): array { return ['Metrik', 'Nilai']; }
        }, 'laporan-sinergi-pas.xlsx');
    }

    private function getDashboardData()
    {
        $totalEmployees = Employee::count();
        $totalDocuments = Document::count();
        $docsToday = Document::whereDate('created_at', now())->count();
        $pendingDocs = Document::where('status', 'pending')->count();
        $openIssues = ReportIssue::where('status', 'open')->count();

        $totalSizeBytes = 0;
        $disk = Storage::disk('private');
        if ($disk->exists('documents')) {
            $files = $disk->allFiles('documents');
            foreach ($files as $file) {
                $totalSizeBytes += $disk->size($file);
            }
        }
        $storageUsed = round($totalSizeBytes / (1024 * 1024), 2);

        return compact('totalEmployees', 'totalDocuments', 'docsToday', 'pendingDocs', 'openIssues', 'storageUsed');
    }

    private function normalizeWhatsAppNumber(?string $number): string
    {
        return preg_replace('/[^0-9]/', '', (string) $number);
    }

    private function buildWhatsAppReminderLink(string $number, string $employeeName): string
    {
        $message = 'Halo ' . $employeeName . ', mohon segera lengkapi dokumen wajib Anda di portal Sinergi PAS. Terima kasih.';

        return 'https://wa.me/' . $number . '?text=' . urlencode($message);
    }
}
