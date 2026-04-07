@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('header-title', 'Pusat Analitik & Kontrol')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@php
    $selectedUnitName = $workUnits->firstWhere('id', request('work_unit_id'))?->name;
    $unitLabel = $selectedUnitName ?: 'Seluruh Unit Kerja';
    $hasMandatory = $totalMandatoryCategories > 0;
@endphp

<div class="space-y-8">
    <!-- Hero Stats Section -->
    <div class="relative overflow-hidden rounded-[48px] bg-slate-900 px-10 py-12 text-white shadow-2xl card-3d">
        <div class="absolute -left-12 top-8 h-44 w-44 rounded-full bg-blue-500/10 blur-3xl"></div>
        <div class="absolute right-0 top-0 h-64 w-64 rounded-full bg-amber-500/10 blur-3xl"></div>

        <div class="relative z-10 flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-[10px] font-bold uppercase tracking-widest text-amber-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                    Ringkasan Operasional - {{ date('d F Y') }}
                </div>
                <h2 class="mt-6 text-4xl font-black tracking-tight italic">Sinergi <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-300">Analytics</span></h2>
                <p class="mt-2 text-slate-400 font-medium text-lg">Pusat kendali data kepegawaian Lapas Jombang.</p>
            </div>

            <div class="flex items-center gap-4">
                <div class="px-6 py-4 rounded-3xl border border-white/10 bg-white/5 backdrop-blur-md">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500 mb-1">Unit Fokus</p>
                    <p class="text-xl font-black text-white italic">{{ $unitLabel }}</p>
                </div>
            </div>
        </div>

        <div class="relative z-10 mt-12 grid grid-cols-2 md:grid-cols-4 gap-8">
            <div class="p-6 rounded-3xl bg-white/5 border border-white/5 group hover:bg-white/10 transition-all">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Total Personel</p>
                <p class="mt-3 text-3xl font-black">{{ $totalEmployees }}</p>
            </div>
            <div class="p-6 rounded-3xl bg-white/5 border border-white/5 group hover:bg-white/10 transition-all">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Antrean Verifikasi</p>
                <p class="mt-3 text-3xl font-black text-amber-400">{{ $pendingDocs }}</p>
            </div>
            <div class="p-6 rounded-3xl bg-white/5 border border-white/5 group hover:bg-white/10 transition-all">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Laporan Isu</p>
                <p class="mt-3 text-3xl font-black text-red-400">{{ $openIssues }}</p>
            </div>
            <div class="p-6 rounded-3xl bg-white/5 border border-white/5 group hover:bg-white/10 transition-all">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Volume Berkas</p>
                <p class="mt-3 text-3xl font-black">{{ $totalDocuments }}</p>
            </div>
        </div>
    </div>

    <!-- Quick Access Section -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <a href="{{ route('employees.index') }}" class="p-6 rounded-[32px] bg-white border border-slate-200 shadow-sm hover:border-blue-500 hover:shadow-xl transition-all group flex flex-col items-center text-center gap-3 card-3d">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-all">
                <i data-lucide="user-plus" class="w-6 h-6"></i>
            </div>
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-600">Tambah Pegawai</span>
        </a>
        <a href="{{ route('admin.attendance.index') }}?tab=recap" class="p-6 rounded-[32px] bg-white border border-slate-200 shadow-sm hover:border-emerald-500 hover:shadow-xl transition-all group flex flex-col items-center text-center gap-3 card-3d">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-all">
                <i data-lucide="fingerprint" class="w-6 h-6"></i>
            </div>
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-600">Impor Absensi</span>
        </a>
        <a href="{{ route('documents.index', ['status' => 'pending']) }}" class="p-6 rounded-[32px] bg-white border border-slate-200 shadow-sm hover:border-amber-500 hover:shadow-xl transition-all group flex flex-col items-center text-center gap-3 card-3d">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-amber-600 group-hover:text-white transition-all">
                <i data-lucide="shield-check" class="w-6 h-6"></i>
            </div>
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-600">Verifikasi Dokumen</span>
        </a>
        <a href="{{ route('admin.schedules.index') }}" class="p-6 rounded-[32px] bg-white border border-slate-200 shadow-sm hover:border-indigo-500 hover:shadow-xl transition-all group flex flex-col items-center text-center gap-3 card-3d">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-all">
                <i data-lucide="calendar-range" class="w-6 h-6"></i>
            </div>
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-600">Atur Jadwal</span>
        </a>
        <button onclick="handleDownload('{{ route('dashboard.export.pdf') }}', 'laporan-operasional.pdf')" class="p-6 rounded-[32px] bg-white border border-slate-200 shadow-sm hover:border-red-500 hover:shadow-xl transition-all group flex flex-col items-center text-center gap-3 card-3d no-loader">
            <div class="w-12 h-12 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center group-hover:bg-red-600 group-hover:text-white transition-all">
                <i data-lucide="file-text" class="w-6 h-6"></i>
            </div>
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-600">Download PDF</span>
        </button>
        <a href="{{ route('settings.index') }}" class="p-6 rounded-[32px] bg-white border border-slate-200 shadow-sm hover:border-slate-900 hover:shadow-xl transition-all group flex flex-col items-center text-center gap-3 card-3d">
            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center group-hover:bg-slate-900 group-hover:text-white transition-all">
                <i data-lucide="settings-2" class="w-6 h-6"></i>
            </div>
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-600">Pengaturan</span>
        </a>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left: Compliance Tracker -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden flex flex-col card-3d">
                <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h3 class="text-sm font-black text-slate-900 flex items-center gap-3 uppercase tracking-widest">
                            <i data-lucide="shield-alert" class="w-5 h-5 text-red-500"></i>
                            Monitoring Kepatuhan Dokumen
                        </h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <form action="{{ route('dashboard') }}" method="GET" id="unitFilterForm" class="no-loader">
                            <select name="work_unit_id" onchange="this.form.submit()" class="bg-white border border-slate-200 rounded-xl px-4 py-2 text-xs font-bold text-slate-600 outline-none">
                                <option value="">Semua Unit</option>
                                @foreach($workUnits as $unit)
                                    <option value="{{ $unit->id }}" {{ request('work_unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </form>
                        <a href="{{ route('documents.index') }}" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest hover:bg-blue-600 transition-all shadow-lg">
                            Lihat Semua
                        </a>
                    </div>
                </div>
                
                <div class="p-8">
                    @if(!$hasMandatory)
                        <div class="py-12 text-center">
                            <i data-lucide="settings" class="w-12 h-12 text-slate-200 mx-auto mb-4"></i>
                            <p class="text-sm font-bold text-slate-400 italic text-[10px] uppercase tracking-widest">Konfigurasi kategori wajib belum tersedia.</p>
                        </div>
                    @else
                        <!-- Scrollable Container -->
                        <div class="space-y-4 max-h-[500px] overflow-y-auto pr-4 custom-scrollbar">
                            @forelse($nonCompliantEmployees as $emp)
                            <div class="flex items-center justify-between p-5 rounded-3xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-blue-200 hover:shadow-xl transition-all group">
                                <div class="flex items-center gap-5">
                                    <div class="w-14 h-14 rounded-2xl bg-white border border-slate-200 overflow-hidden flex items-center justify-center text-slate-400 font-bold shadow-sm">
                                        @if($emp->photo)
                                            <img src="{{ $emp->photo }}" class="w-full h-full object-cover">
                                        @else
                                            {{ substr($emp->full_name, 0, 1) }}
                                        @endif
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-black text-slate-900 group-hover:text-blue-600 transition-colors">{{ $emp->full_name }}</h4>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">{{ $emp->nip }}</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-8">
                                    <div class="hidden md:flex flex-col items-end min-w-[120px]">
                                        <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                                            <div class="h-full bg-gradient-to-r from-blue-600 to-indigo-500 rounded-full" style="width: {{ $emp->compliance_percent }}%"></div>
                                        </div>
                                        <p class="text-[10px] font-black text-slate-500 mt-2 uppercase tracking-tighter">{{ $emp->uploaded_mandatory_count }}/{{ $emp->total_mandatory_count }} Berkas Terverifikasi</p>
                                    </div>
                                    <a href="{{ $emp->whatsapp_link }}" target="_blank" class="w-12 h-12 rounded-2xl bg-green-50 text-green-600 border border-green-100 flex items-center justify-center hover:bg-green-600 hover:text-white transition-all shadow-sm active:scale-95 no-loader" title="WhatsApp Reminder">
                                        <i data-lucide="message-circle" class="w-5 h-5"></i>
                                    </a>
                                </div>
                            </div>
                            @empty
                            <div class="py-12 text-center">
                                <i data-lucide="check-circle" class="w-12 h-12 text-green-100 mx-auto mb-4"></i>
                                <p class="text-sm font-bold text-slate-400 italic">Database kepatuhan dokumen sinkron 100%.</p>
                            </div>
                            @endforelse
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right: Audit Trail -->
        <div class="space-y-8">
            <div class="bg-slate-900 rounded-[40px] border border-slate-800 shadow-2xl overflow-hidden flex flex-col h-[600px] card-3d">
                <div class="p-6 border-b border-slate-800 bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">System Audit Trail</h3>
                    <i data-lucide="terminal" class="w-4 h-4 text-blue-500"></i>
                </div>
                <div class="p-8 overflow-y-auto custom-scrollbar space-y-8 flex-1">
                    @foreach($recentLogs as $log)
                    <div class="flex gap-5 relative group">
                        @if(!$loop->last)
                            <div class="absolute left-4 top-10 bottom-0 w-px bg-slate-800 group-hover:bg-blue-900/50 transition-colors"></div>
                        @endif
                        <div class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center shrink-0 border border-slate-700 group-hover:border-blue-500/50 group-hover:bg-blue-600 transition-all">
                            <i data-lucide="{{ str_contains($log->activity, 'upload') ? 'upload-cloud' : 'activity' }}" class="w-3.5 h-3.5 text-slate-500 group-hover:text-white"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-black text-white leading-tight uppercase tracking-wide">{{ $log->user->name ?? 'SYSTEM' }}</p>
                            <p class="text-[10px] text-slate-400 mt-1.5 leading-relaxed italic line-clamp-3 group-hover:text-slate-300 transition-colors">"{{ $log->details }}"</p>
                            <div class="flex items-center gap-2 mt-3 text-[9px] font-bold text-slate-600 uppercase tracking-widest">
                                <i data-lucide="clock" class="w-3 h-3"></i>
                                {{ $log->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="p-6 border-t border-slate-800 text-center bg-slate-950/50">
                    <a href="{{ route('audit.index') }}" class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] hover:text-blue-400 transition-colors flex items-center justify-center gap-2">
                        View Full Logs <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
</script>
@endsection
