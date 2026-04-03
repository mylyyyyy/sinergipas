@extends('layouts.app')

@section('title', 'Admin Control Center')
@section('header-title', 'Control Center Analytics')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .bento-card {
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .bento-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 30px 60px -12px rgba(30, 36, 50, 0.15);
    }
    .stat-card-gradient {
        background: linear-gradient(135deg, #1E2432 0%, #323d54 100%);
    }
    .accent-gradient {
        background: linear-gradient(135deg, #E85A4F 0%, #ff7b71 100%);
    }
</style>

<!-- Filter & Storage Info Row -->
<div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8 mb-12">
    <div class="flex-1">
        <div class="flex items-center gap-4 mb-2">
            <div class="w-1 h-8 bg-[#E85A4F] rounded-full"></div>
            <h2 class="text-3xl font-black text-[#1E2432] tracking-tight italic">Status Operasional</h2>
        </div>
        <div class="flex flex-wrap items-center gap-4">
            @if($storagePercent > 90)
                <span class="px-4 py-1.5 bg-red-50 text-red-600 text-[9px] font-black uppercase tracking-[0.2em] rounded-full border border-red-100 animate-pulse flex items-center gap-2">
                    <span class="w-2 h-2 bg-red-600 rounded-full"></span> Peringatan: Penyimpanan Penuh
                </span>
            @else
                <span class="px-4 py-1.5 bg-green-50 text-green-600 text-[9px] font-black uppercase tracking-[0.2em] rounded-full border border-green-100 flex items-center gap-2">
                    <span class="w-2 h-2 bg-green-600 rounded-full animate-ping"></span> Sistem Normal & Aman
                </span>
            @endif
            <div class="flex items-center gap-3 bg-white px-4 py-1.5 rounded-full border border-[#EFEFEF] shadow-sm">
                <div class="w-24 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="bg-[#1E2432] h-full transition-all duration-1000" style="width: {{ $storagePercent }}%"></div>
                </div>
                <span class="text-[9px] font-black text-[#8A8A8A] uppercase tracking-widest">{{ $storageUsed }} MB / {{ number_format($storagePercent, 1) }}% TERPAKAI</span>
            </div>
        </div>
    </div>
    
    <div class="flex flex-col sm:flex-row items-center gap-4 w-full lg:w-auto">
        <!-- Work Unit Filter -->
        <form action="{{ route('dashboard') }}" method="GET" class="w-full sm:w-auto no-loader group">
            <div class="relative">
                <i data-lucide="filter" class="absolute left-5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A8A8A] group-hover:text-[#E85A4F] transition-all"></i>
                <select name="work_unit_id" onchange="this.form.submit()" class="w-full sm:w-[240px] pl-14 pr-8 py-4 rounded-2xl border border-[#EFEFEF] bg-white text-[10px] font-black uppercase tracking-widest text-[#1E2432] outline-none focus:ring-4 focus:ring-red-500/5 focus:border-[#E85A4F] transition-all shadow-sm cursor-pointer appearance-none hover:shadow-md">
                    <option value="">Seluruh Unit Kerja</option>
                    @foreach($workUnits as $unit)
                        <option value="{{ $unit->id }}" {{ request('work_unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                    @endforeach
                </select>
                <i data-lucide="chevron-down" class="absolute right-5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A8A8A] pointer-events-none"></i>
            </div>
        </form>

        <div class="flex gap-3 w-full sm:w-auto">
            <a href="{{ route('dashboard.export.excel') }}" class="flex-1 sm:flex-none bg-white border border-[#EFEFEF] text-[#1E2432] px-8 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-[#1E2432] hover:text-white transition-all shadow-sm flex items-center justify-center gap-2 no-loader group">
                <i data-lucide="file-spreadsheet" class="w-4 h-4 text-green-600 group-hover:text-white"></i> Excel
            </a>
            <a href="{{ route('dashboard.export.pdf') }}" class="flex-1 sm:flex-none bg-[#1E2432] text-white px-8 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-[#E85A4F] transition-all shadow-xl shadow-gray-200 flex items-center justify-center gap-2 no-loader">
                <i data-lucide="file-text" class="w-4 h-4"></i> PDF
            </a>
        </div>
    </div>
</div>

<!-- Bento Grid: Smart Action Center -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
    <!-- Urgent Task Hub -->
    @if(($widgets['widget_stats'] ?? 'on') == 'on')
    <div class="md:col-span-2 bg-white p-12 rounded-[56px] border border-[#EFEFEF] shadow-sm relative overflow-hidden bento-card">
        <div class="absolute top-0 right-0 p-12 opacity-[0.03] rotate-12">
            <i data-lucide="zap" class="w-64 h-64 text-[#E85A4F]"></i>
        </div>
        <div class="relative flex flex-col h-full">
            <div class="flex justify-between items-start mb-12">
                <div>
                    <h3 class="text-2xl font-black text-[#1E2432] tracking-tight italic">Smart Action Center</h3>
                    <p class="text-[10px] font-bold text-[#8A8A8A] uppercase tracking-[0.3em] mt-1">Otomasi Alur Kerja & Verifikasi</p>
                </div>
                @if($pendingDocs > 0 || $openIssues > 0)
                    <div class="flex items-center gap-2 bg-red-50 px-4 py-2 rounded-xl border border-red-100">
                        <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                        <span class="text-[10px] font-black text-red-600 uppercase tracking-widest">Tindakan Diperlukan</span>
                    </div>
                @endif
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-auto">
                <div class="p-8 bg-[#FCFBF9] rounded-[40px] border border-[#EFEFEF] hover:border-[#E85A4F] transition-all group relative overflow-hidden">
                    <div class="relative z-10">
                        <p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest">Verifikasi Pending</p>
                        <h4 class="text-4xl font-black text-[#1E2432] mt-2 group-hover:text-[#E85A4F] transition-all">{{ $pendingDocs }} <span class="text-xs font-bold text-[#8A8A8A]">Berkas</span></h4>
                        <a href="{{ route('documents.index', ['status' => 'pending']) }}" class="mt-6 inline-flex items-center gap-3 bg-white px-6 py-3 rounded-2xl text-[9px] font-black uppercase tracking-widest border border-[#EFEFEF] hover:bg-[#1E2432] hover:text-white transition-all shadow-sm">
                            Tinjau Sekarang <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                </div>
                
                <div class="p-8 bg-[#FCFBF9] rounded-[40px] border border-[#EFEFEF] hover:border-[#1E2432] transition-all group relative overflow-hidden">
                    <div class="relative z-10">
                        <p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest">Laporan Masalah</p>
                        <h4 class="text-4xl font-black text-[#1E2432] mt-2 group-hover:scale-110 origin-left transition-all">{{ $openIssues }} <span class="text-xs font-bold text-[#8A8A8A]">Pesan</span></h4>
                        <a href="{{ route('admin.report-issues.index') }}" class="mt-6 inline-flex items-center gap-3 bg-white px-6 py-3 rounded-2xl text-[9px] font-black uppercase tracking-widest border border-[#EFEFEF] hover:bg-[#E85A4F] hover:text-white transition-all shadow-sm">
                            Balas Pegawai <i data-lucide="message-square" class="w-3 h-3"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Analytics Small -->
    @if(($widgets['widget_employees'] ?? 'on') == 'on')
    <div class="stat-card-gradient p-12 rounded-[56px] text-white flex flex-col justify-between shadow-2xl shadow-gray-400 bento-card relative overflow-hidden">
        <div class="absolute top-0 right-0 p-8 opacity-10">
            <i data-lucide="users" class="w-32 h-32 text-white"></i>
        </div>
        <div class="relative">
            <p class="text-[10px] font-black opacity-60 uppercase tracking-[0.4em]">Performa Unit</p>
            <h4 class="text-xl font-bold mt-6 leading-relaxed italic">
                @php $selectedUnit = $workUnits->where('id', request('work_unit_id'))->first(); @endphp
                Unit "{{ $selectedUnit->name ?? 'Global' }}" terpantau aktif.
            </h4>
        </div>
        <div class="pt-10 border-t border-white/10 relative">
            <div class="flex justify-between items-end">
                <div>
                    <p class="text-5xl font-black tracking-tighter">{{ $totalEmployees }}</p>
                    <p class="text-[10px] font-black opacity-60 uppercase tracking-[0.2em] mt-2">Pegawai Terdaftar</p>
                </div>
                <div class="w-16 h-16 accent-gradient rounded-[24px] flex items-center justify-center shadow-lg shadow-red-500/20">
                    <i data-lucide="trending-up" class="w-8 h-8 text-white"></i>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-10">
    <!-- Compliance Tracking List -->
    @if(($widgets['widget_compliance'] ?? 'on') == 'on')
    <div class="md:col-span-2 bg-white p-12 rounded-[56px] border border-[#EFEFEF] shadow-sm overflow-hidden flex flex-col transition-all hover:shadow-2xl hover:shadow-gray-100/50 bento-card">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h3 class="text-xl font-black text-[#1E2432] tracking-tight italic">Analitik Kepatuhan Pegawai</h3>
                <p class="text-[9px] font-bold text-[#8A8A8A] uppercase tracking-widest mt-1">Data sinkronisasi terakhir: {{ now()->format('H:i') }} WIB</p>
            </div>
            <div class="flex items-center gap-3 bg-[#FCFBF9] px-4 py-2 rounded-2xl border border-[#EFEFEF]">
                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                <span class="text-[9px] font-black text-[#1E2432] uppercase tracking-widest">LIVE SYNC</span>
            </div>
        </div>
        <div class="overflow-y-auto flex-1 pr-4 custom-scrollbar" style="max-height: 440px;">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.2em] border-b border-[#EFEFEF]">
                        <th class="pb-6">Nama Pegawai</th>
                        <th class="pb-6">Status Dokumen</th>
                        <th class="pb-6 text-center">Interaksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EFEFEF]">
                    @php $hasMandatory = \App\Models\DocumentCategory::where('is_mandatory', true)->exists(); @endphp
                    
                    @if(!$hasMandatory)
                        <tr>
                            <td colspan="3" class="py-20 text-center">
                                <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6 text-[#E85A4F]">
                                    <i data-lucide="alert-circle" class="w-10 h-10"></i>
                                </div>
                                <p class="text-sm font-black text-[#1E2432] uppercase tracking-widest mb-3">Aturan Kepatuhan Kosong</p>
                                <p class="text-[11px] text-[#8A8A8A] font-medium leading-relaxed max-w-xs mx-auto">Tandai kategori dokumen sebagai <span class="font-black text-[#1E2432]">"Wajib"</span> untuk memulai pelacakan.</p>
                                <a href="{{ route('documents.index') }}" class="mt-8 inline-flex items-center gap-3 bg-[#1E2432] text-white px-8 py-4 rounded-[20px] text-[10px] font-black uppercase tracking-widest hover:bg-[#E85A4F] transition-all shadow-lg">Buka Pengaturan Dokumen <i data-lucide="settings" class="w-4 h-4"></i></a>
                            </td>
                        </tr>
                    @else
                        @foreach($nonCompliantEmployees as $emp)
                        <tr class="group hover:bg-[#FCFBF9]/80 transition-all">
                            <td class="py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center text-[10px] font-black text-[#1E2432] group-hover:bg-[#E85A4F] group-hover:text-white transition-all">
                                        {{ substr($emp->full_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-[#1E2432] group-hover:translate-x-1 transition-all">{{ $emp->full_name }}</p>
                                        <p class="text-[10px] text-[#8A8A8A] font-bold uppercase tracking-widest mt-0.5">NIP. {{ $emp->nip }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-6">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                                    <span class="text-[10px] font-black text-red-600 uppercase tracking-widest italic">Belum Terpenuhi</span>
                                </div>
                            </td>
                            <td class="py-6 text-center">
                                @php
                                    $waMsg = "Halo " . $emp->full_name . ", kelengkapan dokumen administrasi Anda di Sinergi PAS belum terpenuhi. Mohon segera lengkapi dokumen wajib Anda. Terima kasih.";
                                    $waUrl = "https://wa.me/" . preg_replace('/[^0-9]/', '', '628123456789') . "?text=" . urlencode($waMsg);
                                @endphp
                                <a href="{{ $waUrl }}" target="_blank" class="inline-flex items-center gap-3 bg-white border border-[#EFEFEF] text-[#1E2432] px-6 py-3 rounded-2xl text-[9px] font-black uppercase tracking-widest hover:bg-green-600 hover:text-white hover:border-green-600 transition-all shadow-sm">
                                    <i data-lucide="message-circle" class="w-4 h-4 text-green-600 group-hover:text-white"></i> WhatsApp Blast
                                </a>
                            </td>
                        </tr>
                        @endforeach
                        
                        @if($nonCompliantEmployees->isEmpty())
                            <tr>
                                <td colspan="3" class="py-24 text-center">
                                    <div class="w-24 h-24 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-6 text-green-600 shadow-inner">
                                        <i data-lucide="check-circle" class="w-12 h-12"></i>
                                    </div>
                                    <p class="text-lg font-black text-[#1E2432] italic">Sempurna!</p>
                                    <p class="text-[10px] text-[#8A8A8A] font-black uppercase tracking-[0.3em] mt-2">Seluruh pegawai telah patuh administrasi.</p>
                                </td>
                            </tr>
                        @endif
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Activity Feed -->
    <div class="flex flex-col gap-10 {{ ($widgets['widget_compliance'] ?? 'on') == 'off' ? 'md:col-span-3' : '' }}">
        @if(($widgets['widget_activity'] ?? 'on') == 'on')
        <div class="bg-white p-12 rounded-[56px] border border-[#EFEFEF] shadow-sm flex flex-col bento-card flex-1">
            <h3 class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.4em] mb-12 flex items-center gap-3">
                <i data-lucide="activity" class="w-4 h-4 text-[#E85A4F]"></i> Aktivitas Terakhir
            </h3>
            <div class="space-y-10 flex-1 relative">
                <div class="absolute left-[19px] top-0 bottom-0 w-px bg-gray-100"></div>
                @php $recentLogs = \App\Models\AuditLog::with(['user', 'document'])->latest()->take(5)->get(); @endphp
                @foreach($recentLogs as $log)
                <div class="flex gap-6 group relative">
                    <div class="w-10 h-10 bg-white border-2 border-[#EFEFEF] group-hover:border-[#E85A4F] transition-all rounded-full flex items-center justify-center z-10 shadow-sm">
                        <i data-lucide="{{ $log->activity == 'login' ? 'log-in' : 'zap' }}" class="w-4 h-4 text-[#8A8A8A] group-hover:text-[#E85A4F] transition-all"></i>
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-sm font-black text-[#1E2432] group-hover:text-[#E85A4F] transition-all">{{ $log->user->name }}</p>
                        <p class="text-[11px] text-[#1E2432] font-medium leading-relaxed mt-1 opacity-80 truncate">{{ $log->details }}</p>
                        <p class="text-[9px] text-[#ABABAB] font-bold mt-2 uppercase tracking-widest italic">{{ $log->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            <a href="{{ route('audit.index') }}" class="mt-12 text-center text-[10px] font-black text-[#ABABAB] hover:text-[#1E2432] uppercase tracking-[0.4em] transition-all border-t border-[#FCFBF9] pt-8 group">
                Buka Arsip Audit <i data-lucide="external-link" class="w-3 h-3 inline ml-2 group-hover:translate-x-1 group-hover:-translate-y-1 transition-all"></i>
            </a>
        </div>
        @endif

        <!-- Incoming Document Feed Widget -->
        @if(($widgets['widget_feed'] ?? 'on') == 'on')
        <div class="bg-white p-12 rounded-[56px] border border-[#EFEFEF] shadow-sm flex flex-col bento-card flex-1 overflow-hidden relative">
            <div class="absolute top-0 right-0 p-8 opacity-5">
                <i data-lucide="zap" class="w-20 h-20 text-[#E85A4F]"></i>
            </div>
            <div class="flex justify-between items-center mb-10 relative">
                <h3 class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.4em]">Antrean Verifikasi</h3>
                <span class="bg-[#1E2432] text-white text-[8px] font-black px-3 py-1 rounded-full tracking-widest">FEED</span>
            </div>
            <div class="space-y-6 flex-1 relative">
                @php 
                    $latestPending = \App\Models\Document::where('status', 'pending')->with('employee')->latest()->take(3)->get();
                @endphp
                @foreach($latestPending as $pDoc)
                <div class="p-6 bg-[#FCFBF9] rounded-[32px] border border-[#EFEFEF] hover:border-[#E85A4F] transition-all group/feed relative overflow-hidden">
                    <div class="flex justify-between items-start mb-3 relative z-10">
                        <div class="flex-1 overflow-hidden mr-4">
                            <p class="text-xs font-black text-[#1E2432] truncate group-hover:text-[#E85A4F] transition-all">{{ $pDoc->title }}</p>
                            <p class="text-[9px] text-[#8A8A8A] font-bold uppercase tracking-widest mt-1">{{ $pDoc->employee->full_name }}</p>
                        </div>
                        <form action="{{ route('documents.verify', $pDoc->id) }}" method="POST" class="no-loader">
                            @csrf
                            <button type="submit" class="w-10 h-10 bg-white border border-[#EFEFEF] rounded-xl flex items-center justify-center text-green-600 hover:bg-green-600 hover:text-white transition-all shadow-sm">
                                <i data-lucide="check" class="w-5 h-5"></i>
                            </button>
                        </form>
                    </div>
                    <div class="flex items-center justify-between relative z-10 border-t border-gray-100 pt-4">
                        <span class="text-[8px] font-black text-[#ABABAB] uppercase tracking-tighter">{{ $pDoc->created_at->diffForHumans() }}</span>
                        <a href="{{ route('documents.employee', $pDoc->employee_id) }}" class="text-[8px] font-black text-[#E85A4F] uppercase tracking-[0.2em] hover:underline flex items-center gap-1">
                            Tinjau File <i data-lucide="chevron-right" class="w-2 h-2"></i>
                        </a>
                    </div>
                </div>
                @endforeach
                @if($latestPending->isEmpty())
                    <div class="text-center py-12 flex flex-col items-center">
                        <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mb-4 text-green-200">
                            <i data-lucide="check-circle-2" class="w-8 h-8"></i>
                        </div>
                        <p class="text-[10px] font-black text-[#ABABAB] uppercase tracking-[0.3em]">Antrean Bersih</p>
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

@if(($widgets['widget_chart'] ?? 'on') == 'on')
<div class="grid grid-cols-1 mt-12">
    <!-- Chart: Sebaran (Customized) -->
    <div class="bg-white p-14 rounded-[64px] border border-[#EFEFEF] shadow-sm bento-card relative overflow-hidden">
        <div class="absolute top-0 right-0 p-14 opacity-5">
            <i data-lucide="pie-chart" class="w-48 h-48 text-[#1E2432]"></i>
        </div>
        <div class="relative">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-14">
                <div>
                    <h3 class="text-2xl font-black text-[#1E2432] tracking-tight italic">Distribusi Arsip Digital</h3>
                    <p class="text-[10px] font-bold text-[#8A8A8A] uppercase tracking-[0.3em] mt-1">Kepadatan Data per Kategori Dokumen</p>
                </div>
                <div class="flex items-center gap-4 bg-[#FCFBF9] px-6 py-3 rounded-2xl border border-[#EFEFEF]">
                    <span class="text-[10px] font-black text-[#1E2432] uppercase tracking-widest">Total: {{ $totalDocuments }} Dokumen</span>
                </div>
            </div>
            <div class="h-[400px]">
                <canvas id="docChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endif

<script>
    const ctx = document.getElementById('docChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(232, 90, 79, 1)');
    gradient.addColorStop(1, 'rgba(232, 90, 79, 0.6)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartData->pluck('name')) !!},
            datasets: [{
                data: {!! json_encode($chartData->pluck('documents_count')) !!},
                backgroundColor: gradient,
                hoverBackgroundColor: '#1E2432',
                borderRadius: 24,
                barThickness: 40,
                borderSkipped: false,
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1E2432',
                    titleFont: { family: 'Plus Jakarta Sans', size: 12, weight: 'bold' },
                    bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
                    padding: 16,
                    cornerRadius: 16,
                    displayColors: false
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: '#F5F4F2', drawBorder: false }, 
                    border: { display: false }, 
                    ticks: { font: { family: 'Plus Jakarta Sans', weight: 'bold', size: 10 }, color: '#8A8A8A', padding: 10 } 
                },
                x: { 
                    grid: { display: false }, 
                    border: { display: false }, 
                    ticks: { font: { family: 'Plus Jakarta Sans', weight: 'bold', size: 10 }, color: '#1E2432', padding: 10 } 
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeOutQuart'
            }
        }
    });
</script>
@endsection
