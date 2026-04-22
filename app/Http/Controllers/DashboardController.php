<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Document;
use App\Models\WorkUnit;
use App\Models\DocumentCategory;
use App\Models\ReportIssue;
use App\Models\Setting;
use App\Models\AuditLog;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $workUnitId = $request->work_unit_id;
        $today = Carbon::today();

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
            $totalSizeBytes = 0;
            $disk = Storage::disk('private');
            if ($disk->exists('documents')) {
                $files = $disk->allFiles('documents');
                foreach ($files as $file) {
                    $totalSizeBytes += $disk->size($file);
                }
            }
            $storageUsed = round($totalSizeBytes / (1024 * 1024), 2); // MB

            // Compliance Tracking
            $mandatoryCategories = DocumentCategory::where('is_mandatory', true)->get(['id', 'name']);
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

                if ($workUnitId) { $complianceQuery->where('work_unit_id', $workUnitId); }

                $whatsAppNumber = $this->normalizeWhatsAppNumber(
                    Setting::getValue('compliance_whatsapp_number', '628123456789')
                );

                $nonCompliantEmployees = $complianceQuery
                    ->get()
                    ->map(function ($employee) use ($totalMandatoryCategories, $whatsAppNumber) {
                        $uploadedCount = $employee->documents->pluck('document_category_id')->unique()->count();
                        if ($uploadedCount >= $totalMandatoryCategories) return null;

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
            // Show all for scrolling widget
            $nonCompliantEmployees = $nonCompliantEmployees->values();

            $unitPerformance = WorkUnit::withCount('employees')->get();
            $latestEmployees = (clone $employeeQuery)->with(['user', 'work_unit'])->latest()->take(5)->get();
            $recentLogs = AuditLog::with('user')->latest()->take(5)->get();
            $chartData = DocumentCategory::withCount('documents')->get();
            $workUnits = WorkUnit::all();
            $widgets = Setting::where('key', 'like', 'widget_%')->pluck('value', 'key');

            return view('dashboard', compact(
                'totalEmployees', 'totalDocuments', 'docsToday', 'pendingDocs', 
                'openIssues', 'storageUsed', 'unitPerformance', 
                'latestEmployees', 'chartData', 'workUnits', 'nonCompliantEmployees',
                'nonCompliantEmployeesTotal', 'widgets', 'recentLogs', 'totalMandatoryCategories'
            ));
        } 
        
        // --- PEGAWAI VIEW DATA ---
        else {
            $employee = Employee::with(['work_unit', 'position_relation'])->where('user_id', $user->id)->first();
            $myDocs = Document::where('employee_id', $employee?->id)->with('category')->get();
            
            $myDocumentsCount = $myDocs->count();
            $verifiedDocs = $myDocs->where('status', 'verified')->count();
            $rejectedDocsCount = $myDocs->where('status', 'rejected')->count();
            
            $recentDocuments = Document::where('employee_id', $employee?->id)
                ->with('category')
                ->latest()
                ->take(5)
                ->get();
            
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

            $latestSalary = Document::where('employee_id', $employee?->id)
                ->whereHas('category', function($q) { $q->where('slug', 'like', '%gaji%'); })
                ->latest()
                ->first();

            $myAttendanceToday = Attendance::where('employee_id', $employee?->id)
                ->whereDate('date', $today)
                ->first();

            return view('dashboard-pegawai', compact(
                'myDocumentsCount', 'verifiedDocs', 'rejectedDocsCount', 'careerProgress', 'latestSalary', 
                'employee', 'recentDocuments', 'myAttendanceToday'
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

        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles, \Maatwebsite\Excel\Concerns\WithDrawings, \Maatwebsite\Excel\Concerns\WithCustomStartCell {
            protected $data;
            public function __construct($data) { $this->data = $data; }
            public function collection() {
                return collect([
                    ['Total Pegawai Terdaftar', $this->data['totalEmployees'] . ' Orang'],
                    ['Volume Arsip Digital', $this->data['totalDocuments'] . ' Berkas'],
                    ['Aktivitas Dokumen Hari Ini', $this->data['docsToday'] . ' Berkas Baru'],
                    ['Antrean Verifikasi Admin', $this->data['pendingDocs'] . ' Berkas'],
                    ['Laporan Masalah Aktif', $this->data['openIssues'] . ' Laporan'],
                    ['Penyimpanan Digunakan', $this->data['storageUsed'] . ' MB'],
                ]);
            }
            public function headings(): array { return ['METRIK OPERASIONAL', 'NILAI STATISTIK']; }
            public function startCell(): string { return 'A7'; }
            public function drawings() {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('Logo');
                $drawing->setPath(public_path('logo1.png'));
                $drawing->setHeight(80);
                $drawing->setCoordinates('A1');
                return $drawing;
            }
            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet) {
                // KOP
                $kop1 = \App\Models\Setting::getValue('kop_line_1', 'KEMENTERIAN HUKUM DAN HAK ASASI MANUSIA RI');
                $kop2 = \App\Models\Setting::getValue('kop_line_2', 'LEMBAGA PEMASYARAKATAN KELAS IIB JOMBANG');
                $address = \App\Models\Setting::getValue('kop_address', 'Jl. KH. Wahid Hasyim No. 151, Jombang');
                
                $sheet->mergeCells('B1:C1'); $sheet->setCellValue('B1', $kop1);
                $sheet->mergeCells('B2:C2'); $sheet->setCellValue('B2', $kop2);
                $sheet->mergeCells('B3:C3'); $sheet->setCellValue('B3', $address);
                
                $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(11);
                $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E40AF'));
                $sheet->getStyle('B3')->getFont()->setItalic(true)->setSize(9);

                $sheet->mergeCells('A5:C5'); $sheet->setCellValue('A5', 'LAPORAN RINGKASAN EKSEKUTIF OPERASIONAL');
                $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(13)->setUnderline(true);
                $sheet->getStyle('A5')->getAlignment()->setHorizontal('center');
                
                // Header Table
                $sheet->getStyle('A7:B7')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F172A']],
                    'alignment' => ['horizontal' => 'center']
                ]);

                // Data Borders & Zebra
                $sheet->getStyle('A7:B13')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                for ($row = 8; $row <= 13; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle('A' . $row . ':B' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
                    }
                }

                $sheet->getColumnDimension('A')->setAutoSize(true);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                return [];
            }
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
