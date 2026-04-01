@extends('layouts.app')

@section('title', 'Admin Control Center')
@section('header-title', 'Control Center Analytics')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Filter & Storage Info Row -->
<div class="flex flex-col md:flex-row justify-between items-center gap-8 mb-12">
    <div class="flex-1">
        <h2 class="text-3xl font-black text-[#1E2432] tracking-tight italic">Status Operasional</h2>
        <div class="flex items-center gap-4 mt-2">
            <span class="px-4 py-1 bg-green-50 text-green-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-green-100">Sistem Normal</span>
            <div class="flex items-center gap-2">
                <div class="w-24 h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="bg-[#E85A4F] h-full" style="width: {{ $storagePercent }}%"></div>
                </div>
                <span class="text-[10px] font-black text-[#8A8A8A]">PENYIMPANAN: {{ number_format($storagePercent, 1) }}%</span>
            </div>
        </div>
    </div>
    
    <form action="{{ route('dashboard') }}" method="GET" class="no-loader">
        <select name="work_unit_id" onchange="this.form.submit()" 
            class="bg-white border border-[#EFEFEF] px-8 py-4 rounded-[24px] font-black text-xs uppercase tracking-widest text-[#1E2432] shadow-xl shadow-gray-100 outline-none focus:ring-4 focus:ring-red-500/5 cursor-pointer">
            <option value="">Seluruh Unit Kerja</option>
            @foreach($workUnits as $unit)
                <option value="{{ $unit->id }}" {{ request('work_unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
            @endforeach
        </select>
    </form>
</div>

<!-- Bento Grid: Smart Action Center -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
    <!-- Urgent Task Hub -->
    <div class="md:col-span-2 bg-white p-10 rounded-[56px] border border-[#EFEFEF] shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 p-10 opacity-5">
            <i data-lucide="zap" class="w-32 h-32 text-[#E85A4F]"></i>
        </div>
        <h3 class="text-xl font-black text-[#1E2432] mb-8 flex items-center gap-3">
            Smart Action Center
            <span class="bg-red-500 text-white text-[10px] px-2 py-0.5 rounded-md animate-pulse">URGENT</span>
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="p-6 bg-[#FCFBF9] rounded-[32px] border border-[#EFEFEF] hover:border-[#E85A4F] transition-all">
                <p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest">Verifikasi Pending</p>
                <h4 class="text-2xl font-black text-[#1E2432] mt-1">{{ $pendingDocs }} <span class="text-xs font-bold text-[#8A8A8A]">Dokumen</span></h4>
                <a href="{{ route('documents.index') }}" class="mt-4 inline-flex items-center gap-2 text-[#E85A4F] text-[10px] font-black uppercase tracking-widest hover:underline">
                    Tinjau Sekarang <i data-lucide="arrow-right" class="w-3 h-3"></i>
                </a>
            </div>
            
            <div class="p-6 bg-[#FCFBF9] rounded-[32px] border border-[#EFEFEF] hover:border-[#1E2432] transition-all">
                <p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest">Laporan Masalah</p>
                <h4 class="text-2xl font-black text-[#1E2432] mt-1">{{ $openIssues }} <span class="text-xs font-bold text-[#8A8A8A]">Pesan</span></h4>
                <a href="#" class="mt-4 inline-flex items-center gap-2 text-[#1E2432] text-[10px] font-black uppercase tracking-widest hover:underline">
                    Balas Pegawai <i data-lucide="message-square" class="w-3 h-3"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Analytics Small -->
    <div class="bg-[#1E2432] p-10 rounded-[56px] text-white flex flex-col justify-between shadow-2xl shadow-gray-400">
        <div>
            <p class="text-[10px] font-black opacity-60 uppercase tracking-[0.3em]">Performa Unit</p>
            <h4 class="text-lg font-bold mt-4 leading-relaxed italic">Unit "{{ $workUnits->where('id', request('work_unit_id'))->first()->name ?? 'Global' }}" menunjukkan tren positif bulan ini.</h4>
        </div>
        <div class="pt-8 border-t border-white/10">
            <div class="flex justify-between items-end">
                <div>
                    <p class="text-3xl font-black">{{ $totalEmployees }}</p>
                    <p class="text-[10px] font-bold opacity-60 uppercase tracking-widest">Pegawai Aktif</p>
                </div>
                <i data-lucide="trending-up" class="w-10 h-10 text-[#E85A4F]"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-10">
    <!-- Chart: Sebaran (Customized) -->
    <div class="md:col-span-2 bg-white p-12 rounded-[56px] border border-[#EFEFEF] shadow-sm">
        <h3 class="text-xl font-black text-[#1E2432] mb-10">Distribusi Arsip Digital</h3>
        <canvas id="docChart" height="120"></canvas>
    </div>

    <!-- Latest Uploads / System Logs -->
    <div class="bg-white p-10 rounded-[56px] border border-[#EFEFEF] shadow-sm flex flex-col">
        <h3 class="text-xs font-black text-[#8A8A8A] uppercase tracking-[0.3em] mb-8">Aktivitas Terakhir</h3>
        <div class="space-y-8 flex-1">
            @php $recentLogs = \App\Models\AuditLog::with(['user', 'document'])->latest()->take(4)->get(); @endphp
            @foreach($recentLogs as $log)
            <div class="flex gap-4 group">
                <div class="w-1 h-10 bg-[#EFEFEF] group-hover:bg-[#E85A4F] transition-all rounded-full"></div>
                <div>
                    <p class="text-xs font-black text-[#1E2432]">{{ $log->user->name }}</p>
                    <p class="text-[10px] text-[#8A8A8A] font-bold mt-0.5 uppercase tracking-widest">{{ $log->activity }} - {{ $log->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @endforeach
        </div>
        <a href="{{ route('audit.index') }}" class="mt-8 text-center text-[10px] font-black text-[#ABABAB] hover:text-[#E85A4F] uppercase tracking-[0.3em] transition-all">Lihat Seluruh Log</a>
    </div>
</div>

<script>
    const ctx = document.getElementById('docChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartData->pluck('name')) !!},
            datasets: [{
                data: {!! json_encode($chartData->pluck('documents_count')) !!},
                backgroundColor: '#E85A4F',
                borderRadius: 20,
                barThickness: 35,
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { display: false }, border: { display: false }, ticks: { font: { family: 'Plus Jakarta Sans', weight: 'bold', size: 10 } } },
                x: { grid: { display: false }, border: { display: false }, ticks: { font: { family: 'Plus Jakarta Sans', weight: 'bold', size: 10 } } }
            }
        }
    });
</script>
@endsection
