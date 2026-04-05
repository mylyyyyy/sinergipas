@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('header-title', 'Pusat Analitik & Kontrol')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@php
    $selectedUnitName = $workUnits->firstWhere('id', request('work_unit_id'))?->name;
    $unitLabel = $selectedUnitName ?: 'Seluruh Unit Kerja';
    $displayedNonCompliantEmployees = $nonCompliantEmployees->count();
    $hasMandatory = $totalMandatoryCategories > 0;
@endphp

<div class="space-y-8">
    <!-- Hero Stats Section -->
    <div class="relative overflow-hidden rounded-3xl bg-slate-900 px-8 py-10 text-white shadow-xl">
        <div class="absolute -left-12 top-8 h-44 w-44 rounded-full bg-blue-500/10 blur-3xl"></div>
        <div class="absolute right-0 top-0 h-64 w-64 rounded-full bg-amber-500/10 blur-3xl"></div>

        <div class="relative z-10 flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-amber-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                    Ringkasan Sistem
                </div>
                <h2 class="mt-4 text-3xl font-bold tracking-tight">Selamat Datang di Sinergi PAS</h2>
                <p class="mt-2 text-slate-400 font-medium">Monitoring operasional harian Lapas Jombang secara real-time.</p>
            </div>

            <div class="flex items-center gap-4">
                <div class="px-5 py-3 rounded-2xl border border-white/10 bg-white/5 backdrop-blur-sm">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-1">Unit Aktif</p>
                    <p class="text-lg font-bold">{{ $unitLabel }}</p>
                </div>
            </div>
        </div>

        <div class="relative z-10 mt-10 grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="p-4 rounded-2xl bg-white/5 border border-white/5">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Dokumen Baru</p>
                <p class="mt-2 text-2xl font-bold">{{ $docsToday }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-white/5 border border-white/5">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Menunggu Review</p>
                <p class="mt-2 text-2xl font-bold text-amber-400">{{ $pendingDocs }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-white/5 border border-white/5">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Laporan Masalah</p>
                <p class="mt-2 text-2xl font-bold text-red-400">{{ $openIssues }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-white/5 border border-white/5">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Total Pegawai</p>
                <p class="mt-2 text-2xl font-bold">{{ $totalEmployees }}</p>
            </div>
        </div>
    </div>

    <!-- Actions & Filter Row -->
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form action="{{ route('dashboard') }}" method="GET" class="w-full md:w-auto flex items-center gap-3">
            <div class="relative flex-1 md:w-64">
                <i data-lucide="filter" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <select name="work_unit_id" onchange="this.form.submit()" class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 transition-all appearance-none cursor-pointer">
                    <option value="">Seluruh Unit Kerja</option>
                    @foreach($workUnits as $unit)
                        <option value="{{ $unit->id }}" {{ request('work_unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                    @endforeach
                </select>
                <i data-lucide="chevron-down" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
            </div>
        </form>

        <div class="flex items-center gap-3 w-full md:w-auto">
            <a href="{{ route('dashboard.export.excel') }}" class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-white border border-slate-200 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-all">
                <i data-lucide="file-spreadsheet" class="w-4 h-4 text-green-600"></i>
                Excel
            </a>
            <a href="{{ route('dashboard.export.pdf') }}" class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-slate-900 text-white text-sm font-bold hover:bg-slate-800 transition-all shadow-lg shadow-slate-200">
                <i data-lucide="file-text" class="w-4 h-4 text-amber-400"></i>
                PDF Report
            </a>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left: Compliance & Storage -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Compliance Tracker -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <i data-lucide="shield-alert" class="w-4 h-4 text-red-500"></i>
                            Monitoring Kepatuhan Dokumen
                        </h3>
                    </div>
                    <a href="{{ route('employees.index') }}" class="text-xs font-bold text-blue-600 hover:underline">Lihat Semua</a>
                </div>
                
                <div class="p-6">
                    @if(!$hasMandatory)
                        <div class="py-12 text-center">
                            <i data-lucide="settings" class="w-12 h-12 text-slate-200 mx-auto mb-4"></i>
                            <p class="text-sm font-bold text-slate-400">Kategori wajib belum dikonfigurasi.</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @forelse($nonCompliantEmployees as $emp)
                            <div class="flex items-center justify-between p-4 rounded-2xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-blue-100 hover:shadow-md transition-all group">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-slate-200 overflow-hidden flex items-center justify-center text-slate-500 font-bold">
                                        @if($emp->photo)
                                            <img src="{{ $emp->photo }}" class="w-full h-full object-cover">
                                        @else
                                            {{ substr($emp->full_name, 0, 1) }}
                                        @endif
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-slate-900">{{ $emp->full_name }}</h4>
                                        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">{{ $emp->nip }}</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-6">
                                    <div class="hidden sm:flex flex-col items-end">
                                        <div class="w-24 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                            <div class="h-full bg-blue-600 rounded-full" style="width: {{ $emp->compliance_percent }}%"></div>
                                        </div>
                                        <p class="text-[10px] font-bold text-slate-500 mt-1">{{ $emp->uploaded_mandatory_count }}/{{ $emp->total_mandatory_count }} Dokumen</p>
                                    </div>
                                    <a href="{{ $emp->whatsapp_link }}" target="_blank" class="p-2.5 rounded-xl bg-green-50 text-green-600 border border-green-100 hover:bg-green-600 hover:text-white transition-all" title="WhatsApp Blast">
                                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </div>
                            @empty
                            <div class="py-12 text-center">
                                <i data-lucide="check-circle" class="w-12 h-12 text-green-100 mx-auto mb-4"></i>
                                <p class="text-sm font-bold text-slate-400 italic">Seluruh pegawai telah patuh.</p>
                            </div>
                            @endforelse
                        </div>
                    @endif
                </div>
            </div>

            <!-- Storage Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-slate-900 rounded-3xl p-8 text-white relative overflow-hidden shadow-lg group">
                    <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
                        <i data-lucide="database" class="w-32 h-32"></i>
                    </div>
                    <h4 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-4">Penyimpanan Server</h4>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-bold">{{ $storageUsed }}</span>
                        <span class="text-sm font-bold text-slate-500">MB Terpakai</span>
                    </div>
                    <div class="mt-6 flex items-center gap-2 text-[10px] font-bold text-green-400 bg-green-400/10 w-fit px-3 py-1 rounded-full uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                        Status Optimal
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4">Total Entitas</h4>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-slate-600">Arsip Digital</span>
                                <span class="text-lg font-bold text-slate-900">{{ $totalDocuments }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-slate-600">Unit Kerja</span>
                                <span class="text-lg font-bold text-slate-900">{{ $workUnits->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Charts & Logs -->
        <div class="space-y-8">
            <!-- Distribution Chart -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 flex flex-col">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6">Distribusi Dokumen</h3>
                <div class="relative h-64">
                    <canvas id="docChart"></canvas>
                </div>
            </div>

            <!-- Activity Log -->
            <div class="bg-slate-900 rounded-3xl border border-slate-800 shadow-xl overflow-hidden flex flex-col h-[480px]">
                <div class="p-5 border-b border-slate-800 bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Audit Trail</h3>
                    <i data-lucide="history" class="w-4 h-4 text-slate-500"></i>
                </div>
                <div class="p-6 overflow-y-auto custom-scrollbar space-y-6 flex-1">
                    @foreach($recentLogs as $log)
                    <div class="flex gap-4 group">
                        <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center shrink-0 group-hover:bg-blue-600 transition-colors">
                            <i data-lucide="{{ str_contains($log->activity, 'upload') ? 'upload-cloud' : 'activity' }}" class="w-4 h-4 text-slate-400 group-hover:text-white"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-white leading-tight">{{ $log->user->name ?? 'Sistem' }}</p>
                            <p class="text-[10px] text-slate-400 mt-1 line-clamp-2">{{ $log->details }}</p>
                            <p class="text-[9px] font-medium text-slate-500 mt-1.5">{{ $log->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="p-4 border-t border-slate-800 text-center">
                    <a href="{{ route('audit.index') }}" class="text-[10px] font-bold text-slate-500 uppercase tracking-widest hover:text-white transition-colors">Lihat Seluruh Log</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const chartDataValues = {!! json_encode($chartData->pluck('documents_count')) !!};
    const chartLabels = {!! json_encode($chartData->pluck('name')) !!};
    const totalDocs = chartDataValues.reduce((a, b) => a + b, 0);
    
    const ctx = document.getElementById('docChart').getContext('2d');

    const centerTextPlugin = {
        id: 'centerText',
        beforeDraw: function(chart) {
            if (chart.config.type !== 'doughnut') return;
            var width = chart.chartArea.right - chart.chartArea.left,
                height = chart.chartArea.bottom - chart.chartArea.top,
                ctx = chart.ctx;
            ctx.restore();
            ctx.font = "bold 24px 'Plus Jakarta Sans'";
            ctx.textBaseline = "middle";
            ctx.fillStyle = "#0F172A";
            var text = totalDocs.toString(),
                textX = chart.chartArea.left + Math.round((width - ctx.measureText(text).width) / 2),
                textY = chart.chartArea.top + (height / 2) - 10;
            ctx.fillText(text, textX, textY);
            ctx.font = "bold 10px 'Plus Jakarta Sans'";
            ctx.fillStyle = "#94A3B8";
            var label = "DOKUMEN";
            var labelX = chart.chartArea.left + Math.round((width - ctx.measureText(label).width) / 2);
            ctx.fillText(label, labelX, textY + 25);
            ctx.save();
        }
    };

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: chartLabels,
            datasets: [{
                data: chartDataValues,
                backgroundColor: ['#0F172A', '#B45309', '#1D4ED8', '#64748B', '#94A3B8', '#CBD5E1'],
                borderWidth: 4,
                borderColor: '#ffffff',
                hoverOffset: 8
            }]
        },
        options: {
            maintainAspectRatio: false,
            cutout: '80%',
            plugins: { 
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0F172A',
                    padding: 12,
                    cornerRadius: 12,
                    titleFont: { family: "'Plus Jakarta Sans'", weight: 'bold', size: 12 },
                    bodyFont: { family: "'Plus Jakarta Sans'", size: 12 }
                }
            }
        },
        plugins: [centerTextPlugin]
    });
</script>
@endsection
