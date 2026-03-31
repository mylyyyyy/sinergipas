@extends('layouts.app')

@section('title', 'Dashboard Premium')
@section('header-title', 'Overview Analytics')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Greeting Section -->
<div class="mb-12 flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
    <div>
        @php
            $hour = date('H');
            $greeting = $hour < 12 ? 'Selamat Pagi' : ($hour < 17 ? 'Selamat Siang' : 'Selamat Sore');
        @endphp
        <h2 class="text-3xl font-black text-[#1E2432] tracking-tight">{{ $greeting }}, {{ auth()->user()->name }}!</h2>
        <p class="text-[#8A8A8A] font-bold mt-1 uppercase tracking-[0.2em] text-xs">Pantau aktivitas dan data kepegawaian Anda hari ini.</p>
    </div>

    @if(auth()->user()->role === 'superadmin')
    <form action="{{ route('dashboard') }}" method="GET" class="w-full md:w-auto no-loader">
        <select name="work_unit_id" onchange="this.form.submit()" 
            class="w-full md:w-64 px-6 py-3.5 rounded-2xl border border-[#EFEFEF] bg-white text-sm font-bold text-[#1E2432] outline-none focus:ring-4 focus:ring-red-500/5 transition-all shadow-sm cursor-pointer appearance-none">
            <option value="">Seluruh Unit Kerja</option>
            @foreach($workUnits as $unit)
                <option value="{{ $unit->id }}" {{ request('work_unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
            @endforeach
        </select>
    </form>
    @endif
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
    <!-- Stats with Glassmorphism -->
    <div class="group bg-gradient-to-br from-[#E85A4F] to-[#d44d42] p-8 rounded-[48px] shadow-2xl shadow-red-100 flex flex-col justify-between h-56 transform hover:-translate-y-2 transition-all duration-500">
        <div class="flex justify-between items-center">
            <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-md">
                <i data-lucide="users" class="w-6 h-6 text-white"></i>
            </div>
            <span class="text-xs font-black text-white/60 uppercase tracking-widest">Pegawai</span>
        </div>
        <div>
            <h3 class="text-5xl font-black text-white">{{ $totalEmployees }}</h3>
            <p class="text-xs font-bold text-white/80 mt-2 uppercase tracking-widest">Total Terdaftar</p>
        </div>
    </div>

    <div class="group bg-[#1E2432] p-8 rounded-[48px] shadow-2xl shadow-gray-300 flex flex-col justify-between h-56 transform hover:-translate-y-2 transition-all duration-500">
        <div class="flex justify-between items-center">
            <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center backdrop-blur-md">
                <i data-lucide="file-text" class="w-6 h-6 text-white"></i>
            </div>
            <span class="text-xs font-black text-white/40 uppercase tracking-widest">Arsip</span>
        </div>
        <div>
            <h3 class="text-5xl font-black text-white">{{ $totalDocuments }}</h3>
            <p class="text-xs font-bold text-white/60 mt-2 uppercase tracking-widest">File Digital</p>
        </div>
    </div>

    <div class="bg-white p-8 rounded-[48px] border border-[#EFEFEF] shadow-sm flex flex-col justify-between h-56 transform hover:-translate-y-2 transition-all duration-500 text-[#1E2432]">
        <div class="flex justify-between items-start">
            <div class="w-12 h-12 bg-orange-50 rounded-2xl flex items-center justify-center">
                <i data-lucide="upload-cloud" class="w-6 h-6 text-orange-600"></i>
            </div>
            <span class="text-xs font-black text-[#8A8A8A] uppercase tracking-widest">Hari Ini</span>
        </div>
        <div>
            <h3 class="text-4xl font-black">{{ $docsToday }}</h3>
            <p class="text-[10px] font-black text-[#8A8A8A] mt-1 uppercase tracking-widest leading-relaxed">Unggahan Dokumen Baru</p>
        </div>
    </div>

    <div class="bg-white p-8 rounded-[48px] border border-[#EFEFEF] shadow-sm flex flex-col justify-between h-56 transform hover:-translate-y-2 transition-all duration-500 text-[#1E2432]">
        <div class="flex justify-between items-start">
            <div class="w-12 h-12 bg-red-50 rounded-2xl flex items-center justify-center">
                <i data-lucide="alert-circle" class="w-6 h-6 text-red-600"></i>
            </div>
            <span class="text-xs font-black text-[#8A8A8A] uppercase tracking-widest">Atensi</span>
        </div>
        <div>
            <h3 class="text-4xl font-black text-red-600">{{ $employeesWithoutSkp }}</h3>
            <p class="text-[10px] font-black text-[#8A8A8A] mt-1 uppercase tracking-widest leading-relaxed">Pegawai Belum Upload SKP</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-10">
    <!-- Chart Section -->
    @if(\App\Models\Setting::getValue('widget_chart', 'on') === 'on')
    <div class="md:col-span-2 bg-white p-12 rounded-[56px] border border-[#EFEFEF] shadow-sm">
        <div class="flex justify-between items-center mb-10">
            <h3 class="text-xl font-black text-[#1E2432] tracking-tight">Sebaran Dokumen Kepegawaian</h3>
            <div class="flex gap-2">
                <div class="w-3 h-3 bg-[#E85A4F] rounded-full"></div>
                <div class="w-3 h-3 bg-[#1E2432] rounded-full"></div>
            </div>
        </div>
        <canvas id="docChart" height="120"></canvas>
    </div>
    @endif

    <!-- Quick Actions / Notifications -->
    @if(\App\Models\Setting::getValue('widget_activity', 'on') === 'on')
    <div class="bg-[#FCFBF9] p-10 rounded-[56px] border border-[#EFEFEF] shadow-inner">
        <h3 class="text-lg font-black text-[#1E2432] mb-8 uppercase tracking-widest">Pengumuman</h3>
        <div class="bg-white p-8 rounded-[32px] border-l-8 border-[#E85A4F] shadow-sm mb-8">
            <p class="text-sm font-bold text-[#1E2432] leading-relaxed">
                {{ \App\Models\Setting::getValue('announcement', 'Belum ada pengumuman terbaru hari ini.') }}
            </p>
            <p class="text-[10px] font-black text-[#ABABAB] uppercase tracking-widest mt-4">Pesan Admin</p>
        </div>

        <h3 class="text-lg font-black text-[#1E2432] mb-8 uppercase tracking-widest">Aktivitas Terbaru</h3>
        <div class="space-y-6">
            @php
                $recentLogs = \App\Models\AuditLog::with(['user', 'document'])->latest()->take(3)->get();
            @endphp
            @foreach($recentLogs as $log)
            <div class="flex items-start gap-4">
                <div class="w-2 h-2 bg-[#E85A4F] rounded-full mt-1.5"></div>
                <div>
                    <p class="text-xs font-bold text-[#1E2432]">{{ $log->user->name }} mengunduh {{ $log->document->title ?? 'file' }}</p>
                    <p class="text-[10px] text-[#ABABAB] font-bold mt-0.5">{{ $log->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('docChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartData->pluck('name')) !!},
            datasets: [{
                label: 'Jumlah File',
                data: {!! json_encode($chartData->pluck('documents_count')) !!},
                backgroundColor: '#E85A4F',
                borderRadius: 16,
                barThickness: 40,
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { display: false }, ticks: { font: { family: 'Plus Jakarta Sans', weight: 'bold' } } },
                x: { grid: { display: false }, ticks: { font: { family: 'Plus Jakarta Sans', weight: 'bold' } } }
            }
        }
    });
</script>
@endsection
