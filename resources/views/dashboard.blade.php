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
    .glass-stat {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
</style>

@php
    $selectedUnitName = $workUnits->firstWhere('id', request('work_unit_id'))?->name;
    $unitLabel = $selectedUnitName ?: 'Seluruh Unit Kerja';
    $displayedNonCompliantEmployees = $nonCompliantEmployees->count();
    $hasMandatory = $totalMandatoryCategories > 0;
@endphp

<div class="relative overflow-hidden rounded-[56px] bg-[#1E2432] px-8 py-8 text-white shadow-2xl shadow-slate-900/15 sm:px-10 sm:py-10 mb-12">
    <div class="absolute -left-12 top-8 h-44 w-44 rounded-full bg-white/5 blur-3xl"></div>
    <div class="absolute right-0 top-0 h-64 w-64 rounded-full bg-[#E85A4F]/20 blur-3xl"></div>

    <div class="relative z-10 flex flex-col gap-8 xl:flex-row xl:items-end xl:justify-between">
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-[10px] font-black uppercase tracking-[0.28em] text-white/80">
                <span class="h-2 w-2 rounded-full bg-[#E85A4F]"></span>
                Admin Overview
            </div>
            <h2 class="mt-5 text-3xl font-black tracking-tight sm:text-4xl">Ringkasan operasional harian untuk memantau arsip, kepatuhan, dan antrean tindak lanjut.</h2>
            <p class="mt-4 max-w-2xl text-sm font-medium leading-relaxed text-white/65">
                Dashboard ini dipoles agar lebih cepat dipindai saat admin perlu melihat konteks unit, beban kerja hari ini, dan aktivitas yang perlu ditangani segera.
            </p>
        </div>

        <div class="rounded-[28px] border border-white/10 bg-white/5 px-6 py-5 backdrop-blur">
            <p class="text-[10px] font-black uppercase tracking-[0.24em] text-white/50">Filter Unit Aktif</p>
            <p class="mt-3 text-xl font-black tracking-tight">{{ $unitLabel }}</p>
        </div>
    </div>

    <div class="relative z-10 mt-8 grid gap-4 md:grid-cols-3">
        <div class="rounded-[24px] border border-white/10 bg-white/5 p-5">
            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-white/45">Dokumen Hari Ini</p>
            <p class="mt-3 text-3xl font-black">{{ $docsToday }}</p>
        </div>
        <div class="rounded-[24px] border border-white/10 bg-white/5 p-5">
            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-white/45">Pending Review</p>
            <p class="mt-3 text-3xl font-black">{{ $pendingDocs }}</p>
        </div>
        <div class="rounded-[24px] border border-white/10 bg-white/5 p-5">
            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-white/45">Laporan Terbuka</p>
            <p class="mt-3 text-3xl font-black">{{ $openIssues }}</p>
        </div>
    </div>
</div>

<!-- Top Status & Export Row -->
<div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8 mb-12">
    <div class="flex-1">
        <div class="flex items-center gap-4 mb-3">
            <div class="w-2 h-10 bg-[#E85A4F] rounded-full shadow-lg shadow-red-200"></div>
            <div>
                <h2 class="text-3xl font-black text-[#1E2432] tracking-tight italic">Dashboard Utama</h2>
                <p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.3em]">Ringkasan Operasional & Monitoring Sistem</p>
            </div>
        </div>
    </div>
    
    <div class="flex flex-col sm:flex-row items-center gap-4 w-full lg:w-auto">
        <form action="{{ route('dashboard') }}" method="GET" class="w-full sm:w-auto no-loader group">
            <div class="relative">
                <i data-lucide="filter" class="absolute left-5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A8A8A] group-hover:text-[#E85A4F] transition-all"></i>
                <select name="work_unit_id" onchange="this.form.submit()" class="w-full sm:w-[260px] pl-14 pr-10 py-4 rounded-[24px] border border-[#EFEFEF] bg-white text-[10px] font-black uppercase tracking-widest text-[#1E2432] outline-none focus:ring-8 focus:ring-red-500/5 focus:border-[#E85A4F] transition-all shadow-sm cursor-pointer appearance-none hover:shadow-md">
                    <option value="">Seluruh Unit Kerja</option>
                    @foreach($workUnits as $unit)
                        <option value="{{ $unit->id }}" {{ request('work_unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                    @endforeach
                </select>
                <i data-lucide="chevron-down" class="absolute right-5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A8A8A] pointer-events-none"></i>
            </div>
        </form>

        <div class="flex gap-3 w-full sm:w-auto">
            <a href="{{ route('dashboard.export.excel') }}" class="flex-1 sm:flex-none bg-white border border-[#EFEFEF] text-[#1E2432] px-8 py-4 rounded-[24px] font-black text-[10px] uppercase tracking-widest hover:bg-[#1E2432] hover:text-white transition-all shadow-sm flex items-center justify-center gap-2 no-loader group">
                <i data-lucide="file-spreadsheet" class="w-4 h-4 text-green-600 group-hover:text-white transition-transform group-hover:scale-110"></i> Excel
            </a>
            <a href="{{ route('dashboard.export.pdf') }}" class="flex-1 sm:flex-none bg-[#1E2432] text-white px-8 py-4 rounded-[24px] font-black text-[10px] uppercase tracking-widest hover:bg-[#E85A4F] transition-all shadow-xl shadow-gray-200 flex items-center justify-center gap-2 no-loader group">
                <i data-lucide="file-text" class="w-4 h-4 group-hover:rotate-6 transition-transform"></i> PDF
            </a>
        </div>
    </div>
</div>

<!-- Statistics Bento Grid -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
    <!-- Archive Size Widget -->
    <div class="md:col-span-2 bg-[#1E2432] rounded-[56px] p-12 text-white relative overflow-hidden bento-card shadow-2xl shadow-gray-900/20">
        <div class="absolute top-0 right-0 p-12 opacity-10 rotate-12">
            <i data-lucide="database" class="w-64 h-64"></i>
        </div>
        <div class="relative z-10 h-full flex flex-col justify-between">
            <div>
                <p class="text-[10px] font-black opacity-50 uppercase tracking-[0.4em]">Volume Data Digital</p>
                <h3 class="text-5xl font-black mt-4 tracking-tighter">{{ $storageUsed }} <span class="text-xl opacity-40">MB</span></h3>
                <p class="text-xs font-bold opacity-60 mt-4 leading-relaxed max-w-xs">Total akumulasi ukuran dokumen yang telah diunggah dan terarsip dalam server.</p>
            </div>
            <div class="mt-12 flex gap-4">
                <div class="glass-stat px-6 py-4 rounded-[24px]">
                    <p class="text-[8px] font-black opacity-40 uppercase mb-1">Status Server</p>
                    <p class="text-sm font-black italic flex items-center gap-2">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span> Sinkron & Optimal
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Column -->
    <div class="space-y-8">
        <div class="bg-white p-10 rounded-[48px] border border-[#EFEFEF] shadow-sm bento-card relative overflow-hidden">
            <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 mb-6">
                <i data-lucide="users" class="w-7 h-7"></i>
            </div>
            <p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest">Total Pegawai</p>
            <h4 class="text-4xl font-black text-[#1E2432] mt-2 tracking-tighter">{{ $totalEmployees }}</h4>
        </div>
        <div class="bg-white p-10 rounded-[48px] border border-[#EFEFEF] shadow-sm bento-card relative overflow-hidden">
            <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center text-green-600 mb-6">
                <i data-lucide="file-check" class="w-7 h-7"></i>
            </div>
            <p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest">Total Arsip</p>
            <h4 class="text-4xl font-black text-[#1E2432] mt-2 tracking-tighter">{{ $totalDocuments }}</h4>
        </div>
    </div>

    <!-- Urgent Alerts Column -->
    <div class="bg-[#E85A4F] p-10 rounded-[56px] text-white shadow-2xl shadow-red-200 bento-card flex flex-col justify-between group">
        <div>
            <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center mb-8 border border-white/20">
                <i data-lucide="bell-ring" class="w-7 h-7 animate-bounce"></i>
            </div>
            <h4 class="text-xl font-black leading-tight italic">Tindakan<br>Segera</h4>
            <p class="text-[10px] font-bold opacity-60 uppercase tracking-widest mt-4">Memerlukan Respon Admin</p>
        </div>
        <div class="space-y-4">
            <a href="{{ route('documents.index', ['status' => 'pending']) }}" class="flex items-center justify-between bg-white/10 hover:bg-white/20 border border-white/10 p-5 rounded-[24px] transition-all group/link">
                <div class="flex flex-col">
                    <span class="text-2xl font-black leading-none">{{ $pendingDocs }}</span>
                    <span class="text-[8px] font-black uppercase opacity-60 mt-1">Pending Verif</span>
                </div>
                <i data-lucide="arrow-right" class="w-4 h-4 group-hover/link:translate-x-1 transition-transform"></i>
            </a>
            <a href="{{ route('admin.report-issues.index') }}" class="flex items-center justify-between bg-white/10 hover:bg-white/20 border border-white/10 p-5 rounded-[24px] transition-all group/link">
                <div class="flex flex-col">
                    <span class="text-2xl font-black leading-none">{{ $openIssues }}</span>
                    <span class="text-[8px] font-black uppercase opacity-60 mt-1">Laporan Baru</span>
                </div>
                <i data-lucide="arrow-right" class="w-4 h-4 group-hover/link:translate-x-1 transition-transform"></i>
            </a>
        </div>
    </div>
</div>

<!-- Main Analytics Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-10 items-stretch">
    <!-- Compliance Tracking (Improved UI) -->
    <div class="lg:col-span-2 bg-white rounded-[56px] border border-[#EFEFEF] shadow-sm overflow-hidden flex flex-col bento-card h-full min-h-0">
        <div class="p-12 border-b border-[#F5F4F2] flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 bg-[#FCFBF9]/50">
            <div>
                <h3 class="text-2xl font-black text-[#1E2432] tracking-tight italic flex items-center gap-3">
                    Pelacakan Kepatuhan <span class="bg-red-500 text-white text-[8px] font-black px-2.5 py-1 rounded-lg not-italic uppercase tracking-widest">LIVE</span>
                </h3>
                <p class="text-[10px] font-bold text-[#8A8A8A] uppercase tracking-[0.2em] mt-2">Preview pegawai dengan dokumen tidak lengkap</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="rounded-2xl border border-[#EFEFEF] bg-white px-4 py-3 text-right shadow-sm">
                    <p class="text-[9px] font-black uppercase tracking-[0.24em] text-[#8A8A8A]">Ditampilkan</p>
                    <p class="mt-1 text-sm font-black text-[#1E2432]">{{ $displayedNonCompliantEmployees }}/{{ $nonCompliantEmployeesTotal }}</p>
                </div>
                <a href="{{ route('employees.index') }}" class="inline-flex items-center gap-2 rounded-2xl border border-[#EFEFEF] bg-white px-4 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-[#1E2432] shadow-sm transition-all hover:border-[#E85A4F] hover:text-[#E85A4F]">
                    <i data-lucide="arrow-up-right" class="h-4 w-4"></i>
                    Lihat Semua
                </a>
                <button onclick="window.location.reload()" class="bg-white border border-[#EFEFEF] p-4 rounded-2xl hover:bg-[#1E2432] hover:text-white transition-all shadow-sm active:scale-95 group">
                    <i data-lucide="refresh-cw" class="w-5 h-5 group-hover:rotate-180 transition-transform duration-700"></i>
                </button>
            </div>
        </div>
        
        <div class="p-8 flex-1 min-h-0 flex flex-col">
            <div class="mb-4 flex items-center justify-between gap-3 rounded-[24px] border border-[#F1EFEB] bg-[#FCFBF9] px-5 py-4">
                <div>
                    <p class="text-[9px] font-black uppercase tracking-[0.24em] text-[#8A8A8A]">Ringkasan Widget</p>
                    <p class="mt-1 text-sm font-bold text-[#1E2432]">Widget ini hanya menampilkan maksimal {{ $nonCompliantPreviewLimit }} pegawai agar dashboard tetap ringan dan fokus.</p>
                </div>
                <div class="shrink-0 rounded-2xl bg-white px-4 py-3 text-center shadow-sm">
                    <p class="text-[9px] font-black uppercase tracking-[0.22em] text-[#8A8A8A]">Total Isu</p>
                    <p class="mt-1 text-base font-black text-[#E85A4F]">{{ $nonCompliantEmployeesTotal }}</p>
                </div>
            </div>

            <div class="overflow-y-auto pr-2 custom-scrollbar flex-1 min-h-0">
                @if(!$hasMandatory)
                    <div class="py-20 text-center">
                        <div class="w-20 h-20 bg-orange-50 rounded-3xl flex items-center justify-center mx-auto mb-6 text-orange-500 rotate-3 border border-orange-100 shadow-inner">
                            <i data-lucide="alert-circle" class="w-10 h-10"></i>
                        </div>
                        <h4 class="text-lg font-black text-[#1E2432] uppercase tracking-widest">Aturan Belum Diatur</h4>
                        <p class="text-xs text-[#8A8A8A] mt-2 max-w-xs mx-auto leading-relaxed">Tandai minimal satu kategori sebagai <span class="font-black text-[#1E2432]">Wajib</span> untuk mengaktifkan sistem deteksi kepatuhan.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-4">
                        @foreach($nonCompliantEmployees as $emp)
                        <div class="group bg-[#FCFBF9] border border-[#EFEFEF] p-6 rounded-[32px] hover:bg-white hover:border-[#E85A4F] hover:shadow-xl transition-all duration-500 flex flex-col sm:flex-row items-center justify-between gap-6">
                            <div class="flex items-center gap-6 flex-1 w-full">
                                <div class="w-16 h-16 rounded-[24px] overflow-hidden border-4 border-white shadow-lg bg-[#E85A4F] flex items-center justify-center text-white text-xl font-black">
                                    @if($emp->photo)
                                        <img src="{{ $emp->photo }}" class="w-full h-full object-cover">
                                    @else
                                        {{ substr($emp->full_name, 0, 1) }}
                                    @endif
                                </div>
                                <div>
                                    <h5 class="text-base font-black text-[#1E2432] group-hover:text-[#E85A4F] transition-colors">{{ $emp->full_name }}</h5>
                                    <p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest mt-1">NIP. {{ $emp->nip }}</p>
                                </div>
                            </div>
                            
                            <div class="flex flex-col items-center sm:items-end gap-3 w-full sm:w-auto">
                                <div class="flex items-center gap-4">
                                    <div class="w-32 h-2 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-[#E85A4F] rounded-full transition-all duration-1000" style="width: {{ $emp->compliance_percent }}%"></div>
                                    </div>
                                    <span class="text-[10px] font-black text-red-600 uppercase">{{ $emp->uploaded_mandatory_count }}/{{ $emp->total_mandatory_count }}</span>
                                </div>
                                <a href="{{ $emp->whatsapp_link }}" target="_blank" class="flex items-center gap-2 bg-white px-6 py-2.5 rounded-xl border border-[#EFEFEF] text-[9px] font-black uppercase tracking-[0.2em] hover:bg-green-600 hover:text-white hover:border-green-600 transition-all shadow-sm">
                                    <i data-lucide="message-circle" class="w-3 h-3"></i> WhatsApp Blast
                                </a>
                            </div>
                        </div>
                        @endforeach
                        
                        @if($nonCompliantEmployees->isEmpty())
                            <div class="py-20 text-center">
                                <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-6 text-green-600 shadow-inner rotate-6">
                                    <i data-lucide="shield-check" class="w-10 h-10"></i>
                                </div>
                                <p class="text-base font-black text-[#1E2432] italic">Zero Compliance Issues</p>
                                <p class="text-[10px] text-[#8A8A8A] font-black uppercase tracking-[0.3em] mt-1">Seluruh pegawai telah mematuhi aturan administrasi.</p>
                            </div>
                        @endif

                        @if($nonCompliantEmployeesTotal > $displayedNonCompliantEmployees)
                            <div class="rounded-[28px] border border-dashed border-[#E2E0DC] bg-white px-6 py-5 text-center">
                                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#8A8A8A]">
                                    Menampilkan {{ $displayedNonCompliantEmployees }} dari {{ $nonCompliantEmployeesTotal }} pegawai yang perlu ditindaklanjuti.
                                </p>
                                <a href="{{ route('employees.index') }}" class="mt-4 inline-flex items-center gap-2 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] px-5 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-[#1E2432] transition-all hover:border-[#E85A4F] hover:text-[#E85A4F]">
                                    <i data-lucide="list" class="h-4 w-4"></i>
                                    Lihat Semua Data Pegawai
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Sidebar: Chart & Feed -->
    <div class="space-y-10">
        <div class="bg-white p-8 rounded-[40px] border border-[#EFEFEF] shadow-sm bento-card">
            <div class="flex items-center justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.3em]">Pegawai Baru Dipantau</h3>
                    <p class="text-sm font-bold text-[#1E2432] mt-2">Akses cepat ke entitas terbaru dalam sistem.</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-[#FCFBF9] border border-[#EFEFEF] flex items-center justify-center text-[#E85A4F]">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="space-y-3">
                @forelse($latestEmployees as $employee)
                    <div class="flex items-center justify-between gap-4 rounded-[24px] border border-[#EFEFEF] bg-[#FCFBF9] px-4 py-4">
                        <div class="min-w-0">
                            <p class="text-xs font-black text-[#1E2432] truncate">{{ $employee->full_name }}</p>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#8A8A8A] mt-1 truncate">{{ $employee->work_unit->name ?? 'Tanpa Unit Kerja' }}</p>
                        </div>
                        <span class="shrink-0 text-[10px] font-black uppercase tracking-[0.18em] text-[#E85A4F]">{{ $employee->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <div class="rounded-[24px] border border-dashed border-[#E2E0DC] bg-[#FCFBF9] px-4 py-8 text-center text-[10px] font-black uppercase tracking-[0.24em] text-[#ABABAB]">
                        Belum ada data pegawai terbaru
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Distribution Chart -->
        <div class="bg-white p-10 rounded-[56px] border border-[#EFEFEF] shadow-sm bento-card relative overflow-hidden">
            <h3 class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.4em] mb-10">Distribusi Data</h3>
            <div class="h-[280px]">
                <canvas id="docChart"></canvas>
            </div>
        </div>

        <!-- Activity Log Hub -->
        <div class="bg-[#1E2432] p-10 rounded-[56px] text-white shadow-2xl relative overflow-hidden bento-card h-[400px] flex flex-col">
            <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/5 rounded-full blur-3xl"></div>
            <div class="flex items-center justify-between mb-8 relative z-10">
                <h3 class="text-[10px] font-black opacity-50 uppercase tracking-[0.4em]">Audit Trail</h3>
                <span class="w-2 h-2 bg-[#E85A4F] rounded-full animate-ping"></span>
            </div>
            <div class="space-y-8 flex-1 overflow-y-auto custom-scrollbar pr-4 relative z-10">
                @foreach($recentLogs as $log)
                <div class="flex gap-5 group">
                    <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center group-hover:bg-[#E85A4F] transition-all flex-shrink-0">
                        <i data-lucide="{{ str_contains($log->activity, 'upload') ? 'upload-cloud' : 'activity' }}" class="w-4 h-4 text-white"></i>
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-[11px] font-black tracking-tight leading-tight">{{ $log->user->name ?? 'Sistem' }}</p>
                        <p class="text-[10px] opacity-50 mt-1 truncate">{{ $log->details }}</p>
                        <p class="text-[8px] font-bold opacity-30 uppercase mt-2 italic">{{ $log->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            <a href="{{ route('audit.index') }}" class="mt-8 text-center text-[9px] font-black uppercase tracking-[0.3em] opacity-40 hover:opacity-100 hover:text-[#E85A4F] transition-all pt-6 border-t border-white/5 relative z-10">
                Lihat Seluruh Log <i data-lucide="external-link" class="w-3 h-3 inline ml-1"></i>
            </a>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('docChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(232, 90, 79, 1)');
    gradient.addColorStop(1, 'rgba(232, 90, 79, 0.2)');

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($chartData->pluck('name')) !!},
            datasets: [{
                data: {!! json_encode($chartData->pluck('documents_count')) !!},
                backgroundColor: ['#E85A4F', '#1E2432', '#F5F4F2', '#EFEFEF', '#ABABAB'],
                borderWidth: 0,
                hoverOffset: 20
            }]
        },
        options: {
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: { 
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1E2432',
                    padding: 16,
                    cornerRadius: 16,
                    titleFont: { family: 'Roboto', weight: 'bold' },
                    bodyFont: { family: 'Roboto' }
                }
            },
            animation: { animateRotate: true, duration: 2000 }
        }
    });
</script>
@endsection
