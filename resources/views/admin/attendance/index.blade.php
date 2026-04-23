@extends('layouts.app')

@section('title', 'Manajemen Kehadiran')
@section('header-title', 'Absensi & Uang Makan')

@section('content')
@inject('scheduleService', 'App\Services\ScheduleService')

<!-- Custom Loading Overlay for Import -->
<div id="importLoading" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/60 backdrop-blur-md">
    <div class="bg-white rounded-[32px] p-10 shadow-2xl max-w-sm w-full text-center animate-in zoom-in duration-300">
        <div class="relative w-24 h-24 mx-auto mb-6">
            <div class="absolute inset-0 border-4 border-slate-100 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-blue-600 rounded-full border-t-transparent animate-spin"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                <i data-lucide="fingerprint" class="w-10 h-10 text-blue-600 animate-pulse"></i>
            </div>
        </div>
        <h3 class="text-xl font-bold text-slate-900 mb-2">Sinkronisasi Data</h3>
        <p class="text-sm text-slate-500 font-medium leading-relaxed">Mohon tunggu sebentar, sistem sedang memproses data absensi & validasi jadwal...</p>
    </div>
</div>

<div class="space-y-8 page-fade">
    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm card-3d flex items-center gap-5">
            <div class="w-14 h-14 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                <i data-lucide="user-check" class="w-7 h-7"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Kehadiran Valid (Uang Makan)</p>
                <h3 class="text-2xl font-bold text-slate-900">{{ number_format($employees->sum(fn($e) => $e->attendances->where('allowance_amount', '>', 0)->count())) }} Hari</h3>
            </div>
        </div>
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm card-3d flex items-center gap-5 border-l-4 border-l-amber-500">
            <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <i data-lucide="clock-alert" class="w-7 h-7"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Keterlambatan</p>
                <h3 class="text-2xl font-bold text-slate-900">{{ number_format($summary->total_late) }} Kali</h3>
            </div>
        </div>
        <div class="bg-slate-900 rounded-3xl p-6 text-white shadow-xl card-3d flex items-center gap-5 relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 opacity-10">
                <i data-lucide="banknote" class="w-20 h-20"></i>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-white/10 text-amber-400 flex items-center justify-center shrink-0 backdrop-blur-sm border border-white/10">
                <i data-lucide="wallet" class="w-7 h-7"></i>
            </div>
            <div class="relative z-10">
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Uang Makan Dibayarkan</p>
                <h3 class="text-2xl font-bold text-white">Rp {{ number_format($employees->sum(fn($e) => $e->attendances->sum('allowance_amount')), 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <!-- Actions & Filters -->
    <div class="bg-white p-6 md:p-8 rounded-[40px] border border-slate-200 shadow-sm card-3d relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-slate-50 rounded-full -mr-32 -mt-32 opacity-40"></div>
        
        <form action="{{ route('admin.attendance.index') }}" method="GET" class="relative z-10 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-end">
                <div class="lg:col-span-5">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Pencarian Pegawai</label>
                    <div class="relative group">
                        <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NIP atau Nama..." class="w-full pl-11 pr-4 py-3.5 rounded-2xl border-2 border-slate-50 bg-slate-50 text-sm font-bold text-slate-700 outline-none focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all">
                    </div>
                </div>

                <div class="lg:col-span-7">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Rentang Tanggal Absensi</label>
                    <div class="flex items-center gap-3">
                        <div class="relative flex-1 group">
                            <i data-lucide="calendar" class="absolute left-4 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400"></i>
                            <input type="date" name="start_date" value="{{ $startDate }}" class="w-full pl-10 pr-4 py-3.5 rounded-2xl border-2 border-slate-50 bg-slate-50 text-xs font-bold text-slate-700 outline-none focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all">
                        </div>
                        <span class="text-slate-300 font-bold text-sm">s/d</span>
                        <div class="relative flex-1 group">
                            <i data-lucide="calendar" class="absolute left-4 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400"></i>
                            <input type="date" name="end_date" value="{{ $endDate }}" class="w-full pl-10 pr-4 py-3.5 rounded-2xl border-2 border-slate-50 bg-slate-50 text-xs font-bold text-slate-700 outline-none focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-slate-100">
                <div class="flex gap-2">
                    <button type="submit" class="px-8 py-3.5 rounded-2xl bg-slate-900 text-white font-bold text-xs uppercase tracking-widest hover:bg-blue-600 transition-all shadow-lg active:scale-95 flex items-center gap-2 group">
                        <i data-lucide="filter" class="w-4 h-4 group-hover:rotate-12 transition-transform"></i> Terapkan Filter
                    </button>
                    @if(request()->anyFilled(['search', 'start_date', 'end_date']))
                        <a href="{{ route('admin.attendance.index') }}" class="px-5 py-3.5 rounded-2xl bg-red-50 text-red-500 font-bold text-xs uppercase tracking-widest hover:bg-red-500 hover:text-white transition-all shadow-sm flex items-center gap-2 group">
                            <i data-lucide="rotate-ccw" class="w-4 h-4 group-hover:rotate-[-45deg] transition-transform"></i> Reset
                        </a>
                    @endif
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="button" onclick="openExportModal()" class="px-5 py-3.5 rounded-2xl bg-blue-50 text-blue-600 font-bold text-[10px] uppercase tracking-widest hover:bg-blue-600 hover:text-white transition-all flex items-center gap-2 border border-blue-100 shadow-sm">
                        <i data-lucide="download-cloud" class="w-4 h-4"></i> Ekspor Laporan
                    </button>
                    <button type="button" onclick="document.getElementById('importModal').classList.remove('hidden')" class="px-6 py-3.5 rounded-2xl bg-slate-900 text-white font-bold text-[10px] uppercase tracking-widest hover:bg-blue-600 transition-all shadow-lg flex items-center gap-2">
                        <i data-lucide="upload-cloud" class="w-4 h-4 text-amber-400"></i> Import Finger
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tab Navigation -->
    <div class="flex gap-2 p-1.5 bg-slate-100 rounded-[24px] w-fit border border-slate-200 shadow-inner">
        <button onclick="switchTab('recap')" id="btn-recap" class="tab-btn active px-8 py-3.5 rounded-2xl text-[11px] font-black uppercase tracking-widest transition-all duration-300 flex items-center gap-3">
            <i data-lucide="calculator" class="w-4 h-4"></i> Rekapitulasi Bulanan
        </button>
        <button onclick="switchTab('logs')" id="btn-logs" class="tab-btn px-8 py-3.5 rounded-2xl text-[11px] font-black uppercase tracking-widest transition-all duration-300 flex items-center gap-3">
            <i data-lucide="list-checks" class="w-4 h-4"></i> Log Absensi Detail
        </button>
    </div>

    <!-- Tab Content: Recap -->
    <div id="tab-recap" class="tab-content">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden card-3d">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pegawai</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Unit / Regu</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Hadir Valid</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Uang Makan</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($employees as $emp)
                        @php
                            $totalUangMakan = $emp->attendances->sum('allowance_amount');
                            $validDays = $emp->attendances->where('allowance_amount', '>', 0)->count();
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 font-bold border border-slate-200 shrink-0 overflow-hidden">
                                        @if($emp->photo)
                                            <img src="{{ $emp->photo }}" class="w-full h-full object-cover">
                                        @else
                                            {{ substr($emp->full_name, 0, 1) }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors truncate">{{ $emp->full_name }}</p>
                                        <p class="text-[10px] font-mono font-bold text-slate-400">NIP. {{ $nip = $emp->nip }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <p class="text-[10px] font-black text-slate-900 uppercase tracking-tight">{{ $emp->work_unit->name ?? 'NON-UNIT' }}</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">
                                    {{ $emp->squad ? 'Regu ' . $emp->squad->name : 'Staff Kantor' }}
                                </p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-lg bg-emerald-50 text-emerald-600 text-xs font-black border border-emerald-100">
                                    {{ $validDays }} Hari
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <p class="text-sm font-bold text-slate-900">Rp {{ number_format($totalUangMakan, 0, ',', '.') }}</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Gol. {{ $emp->rank_relation->name ?? '-' }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button onclick="openIndividualExportModal({{ $emp->id }}, '{{ $emp->full_name }}')" class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-[10px] font-bold uppercase hover:bg-blue-600 hover:text-white transition-all">
                                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i> Laporan
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest italic text-slate-300">Belum ada data untuk periode ini</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($employees->hasPages())
            <div class="p-6 border-t border-slate-100 bg-slate-50/30">
                {{ $employees->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Tab Content: Logs -->
    <div id="tab-logs" class="tab-content hidden">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden card-3d">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tanggal / Pegawai</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Scan Masuk / Pulang</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Jadwal Aktif</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Status Kehadiran</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Uang Makan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($attendanceLogs as $log)
                        @php
                            $effectiveSched = $scheduleService->getEffectiveSchedule($log->employee, $log->date);
                            $isNightReturn = false;
                            
                            // Deteksi jika ini adalah kepulangan Shift Malam
                            if (!$effectiveSched || (isset($effectiveSched['shift']) && !str_contains(strtoupper($effectiveSched['shift']->name), 'MALAM'))) {
                                $yesterday = \Carbon\Carbon::parse($log->date)->subDay()->format('Y-m-d');
                                $yesterdaySched = $scheduleService->getEffectiveSchedule($log->employee, $yesterday);
                                if ($yesterdaySched && str_contains(strtoupper($yesterdaySched['shift']->name ?? ''), 'MALAM')) {
                                    $effectiveSched = $yesterdaySched;
                                    $isNightReturn = true;
                                }
                            }
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="shrink-0 text-center bg-slate-100 rounded-xl px-2 py-1.5 border border-slate-200 min-w-[50px]">
                                        <p class="text-xs font-black text-slate-900 leading-none">{{ \Carbon\Carbon::parse($log->date)->format('d') }}</p>
                                        <p class="text-[8px] font-bold text-slate-400 uppercase mt-0.5">{{ \Carbon\Carbon::parse($log->date)->translatedFormat('M') }}</p>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-slate-900 truncate">{{ $log->employee->full_name }}</p>
                                        <p class="text-[10px] font-mono text-slate-400">NIP. {{ $log->employee->nip }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <span class="px-2.5 py-1 bg-blue-50 text-blue-600 rounded-lg text-[11px] font-black border border-blue-100">
                                        {{ $log->check_in ? \Carbon\Carbon::parse($log->check_in)->format('H:i') : '--:--' }}
                                    </span>
                                    <i data-lucide="arrow-right" class="w-3 h-3 text-slate-300"></i>
                                    <span class="px-2.5 py-1 bg-slate-50 text-slate-600 rounded-lg text-[11px] font-black border border-slate-100">
                                        {{ $log->check_out && $log->check_out != $log->check_in ? \Carbon\Carbon::parse($log->check_out)->format('H:i') : '--:--' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($effectiveSched)
                                    <div class="flex flex-col items-center gap-1">
                                        @if(isset($effectiveSched['status']) && in_array($effectiveSched['status'], ['leave', 'sick']))
                                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider bg-slate-100 text-slate-600 border border-slate-200 italic">
                                                {{ $effectiveSched['status'] === 'leave' ? 'Sedang Cuti' : 'Izin Sakit' }}
                                            </span>
                                        @elseif($effectiveSched['shift'])
                                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider 
                                                {{ $effectiveSched['type'] === 'office' ? 'bg-slate-100 text-slate-600 border-slate-200' : 'bg-blue-100 text-blue-700 border-blue-200' }} border">
                                                {{ $effectiveSched['shift']->name }}
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 bg-red-50 text-red-400 rounded-lg text-[9px] font-black uppercase tracking-wider border border-red-100 italic">No Shift</span>
                                        @endif

                                        @if($isNightReturn)
                                            <span class="text-[8px] font-bold text-blue-400 uppercase italic">(Kepulangan H-1)</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="px-2.5 py-1 bg-red-50 text-red-400 rounded-lg text-[9px] font-black uppercase tracking-wider border border-red-100 italic">OFF / Libur</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($log->status === 'present')
                                    <span class="px-2.5 py-1 bg-green-50 text-green-600 rounded-lg text-[9px] font-black uppercase border border-green-100 italic">Tepat Waktu</span>
                                @elseif($log->status === 'picket')
                                    <span class="px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[9px] font-black uppercase border border-indigo-100 italic">Dinas Piket</span>
                                @elseif($log->status === 'late')
                                    <span class="px-2.5 py-1 bg-amber-50 text-amber-600 rounded-lg text-[9px] font-black uppercase border border-amber-100 italic">Terlambat ({{ $log->late_minutes }}m)</span>
                                @elseif($log->status === 'on_leave')
                                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-[9px] font-black uppercase border border-emerald-100 italic">Sedang Cuti</span>
                                @elseif($log->status === 'sick')
                                    <span class="px-2.5 py-1 bg-rose-50 text-rose-600 rounded-lg text-[9px] font-black uppercase border border-rose-100 italic">Izin Sakit</span>
                                @else
                                    <span class="px-2.5 py-1 bg-red-50 text-red-600 rounded-lg text-[9px] font-black uppercase border border-red-100 italic">{{ strtoupper($log->status === 'absent' ? 'Alpa / Libur' : $log->status) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($log->allowance_amount > 0)
                                    <div class="flex flex-col items-end">
                                        <p class="text-sm font-black text-emerald-600">Rp {{ number_format($log->allowance_amount, 0, ',', '.') }}</p>
                                        <div class="flex items-center gap-1 text-[8px] font-bold text-emerald-400 uppercase">
                                            <i data-lucide="check-circle-2" class="w-3 h-3"></i> Valid
                                        </div>
                                    </div>
                                @else
                                    <div class="flex flex-col items-end opacity-50">
                                        <p class="text-sm font-black text-slate-400 italic">Rp 0</p>
                                        <p class="text-[8px] font-bold text-red-400 uppercase italic">Luar Jadwal</p>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest italic text-slate-300">Belum ada aktivitas absensi tercatat</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($attendanceLogs->hasPages())
            <div class="p-6 border-t border-slate-100 bg-slate-50/30">
                {{ $attendanceLogs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Import Modal -->
<div id="importModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-md rounded-[32px] p-10 shadow-2xl animate-in zoom-in duration-200 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-full -mr-16 -mt-16 opacity-50"></div>
        <div class="relative z-10">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h3 class="text-2xl font-bold text-slate-900 tracking-tight italic">Impor Fingerprint</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Sinkronisasi Data Mesin</p>
                </div>
                <button onclick="document.getElementById('importModal').classList.add('hidden')" class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 hover:text-red-500 transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <div class="mb-8 p-5 bg-blue-50 rounded-2xl border border-blue-100">
                <h4 class="text-[10px] font-bold text-blue-800 uppercase tracking-widest mb-2">Validasi Pintar:</h4>
                <p class="text-[10px] font-semibold text-blue-700 leading-relaxed italic">
                    Sistem akan otomatis mencocokkan scan dengan jadwal Regu Jaga & Piket Individu. Uang makan hanya dihitung untuk scan yang valid sesuai jadwal.
                </p>
            </div>

            <form id="importForm" action="{{ route('admin.attendance.import') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div class="p-8 rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 text-center group hover:bg-white hover:border-blue-400 transition-all cursor-pointer relative">
                    <input type="file" name="file" required class="absolute inset-0 opacity-0 cursor-pointer" onchange="updateFileName(this)">
                    <i data-lucide="file-spreadsheet" class="w-10 h-10 text-slate-300 mx-auto mb-3 group-hover:text-blue-500 group-hover:scale-110 transition-all"></i>
                    <p id="fileName" class="text-xs font-bold text-slate-500 group-hover:text-blue-600 uppercase">Klik untuk pilih file Excel</p>
                </div>
                <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-bold text-sm uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl btn-3d">
                    Mulai Sinkronisasi
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Export Modal (Simplified for user) -->
<div id="exportModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-md rounded-[32px] p-10 shadow-2xl animate-in zoom-in duration-200">
        <h3 class="text-2xl font-black text-slate-900 mb-6 italic uppercase tracking-tight">Export Laporan</h3>
        <form id="exportForm" class="space-y-6">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Jenis Laporan</label>
                <select name="filter" id="export_filter" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-2 border-transparent focus:bg-white focus:border-blue-500 outline-none transition-all font-bold text-sm text-slate-700">
                    <option value="range">Rekap Rentang Waktu (Sesuai Filter)</option>
                    <option value="monthly">Rekap Bulanan (Seluruh Pegawai)</option>
                    <option value="daily">Laporan Harian (Satu Hari)</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Format File</label>
                <select name="type" id="export_type" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-2 border-transparent focus:bg-white focus:border-blue-500 outline-none transition-all font-bold text-sm text-slate-700">
                    <option value="pdf">Dokumen PDF Resmi</option>
                    <option value="excel">Microsoft Excel (.xlsx)</option>
                </select>
            </div>
            <button type="button" onclick="submitGlobalExport()" class="w-full py-4 bg-blue-600 text-white rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg btn-3d">
                Download Laporan
            </button>
            <button type="button" onclick="document.getElementById('exportModal').classList.add('hidden')" class="w-full text-slate-400 font-bold text-[10px] uppercase tracking-widest mt-2">Batal</button>
        </form>
    </div>
</div>

<!-- Individual Export Modal -->
<div id="individualExportModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-md rounded-[32px] p-10 shadow-2xl animate-in zoom-in duration-200">
        <h3 class="text-2xl font-black text-slate-900 mb-2 italic uppercase tracking-tight">Export Individu</h3>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-8" id="ind_emp_name"></p>
        
        <input type="hidden" id="ind_emp_id">
        <form id="individualExportForm" class="space-y-6">
            <div class="p-4 bg-blue-50 rounded-2xl border border-blue-100 mb-6">
                <p class="text-[10px] font-bold text-blue-600 uppercase leading-relaxed">Laporan akan diekspor sesuai rentang tanggal filter saat ini: <br><span class="text-blue-800">{{ $rangeTitle }}</span></p>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Format File</label>
                <select id="ind_export_type" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-2 border-transparent focus:bg-white focus:border-blue-500 outline-none transition-all font-bold text-sm text-slate-700">
                    <option value="pdf">Dokumen PDF Resmi</option>
                    <option value="excel">Microsoft Excel (.xlsx)</option>
                </select>
            </div>
            <button type="button" onclick="submitIndividualExport()" class="w-full py-4 bg-blue-600 text-white rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg btn-3d">
                Download Laporan Individu
            </button>
            <button type="button" onclick="document.getElementById('individualExportModal').classList.add('hidden')" class="w-full text-slate-400 font-bold text-[10px] uppercase tracking-widest mt-2">Batal</button>
        </form>
    </div>
</div>

<script>
    function openExportModal() { document.getElementById('exportModal').classList.remove('hidden'); }
    
    function submitGlobalExport() {
        const filter = document.getElementById('export_filter').value;
        const type = document.getElementById('export_type').value;
        const url = `/admin/attendance/export?filter=${filter}&type=${type}&start_date={{ $startDate }}&end_date={{ $endDate }}`;
        window.location.href = url;
        document.getElementById('exportModal').classList.add('hidden');
    }

    function openIndividualExportModal(id, name) {
        document.getElementById('ind_emp_id').value = id;
        document.getElementById('ind_emp_name').innerText = name;
        document.getElementById('individualExportModal').classList.remove('hidden');
    }

    function submitIndividualExport() {
        const id = document.getElementById('ind_emp_id').value;
        const type = document.getElementById('ind_export_type').value;
        const url = `/admin/attendance/export?filter=individual&employee_id=${id}&type=${type}&start_date={{ $startDate }}&end_date={{ $endDate }}`;
        window.location.href = url;
        document.getElementById('individualExportModal').classList.add('hidden');
    }

    function switchTab(tabName) {
        document.getElementById('tab-recap').classList.toggle('hidden', tabName !== 'recap');
        document.getElementById('tab-logs').classList.toggle('hidden', tabName !== 'logs');
        document.getElementById('btn-recap').classList.toggle('active', tabName === 'recap');
        document.getElementById('btn-logs').classList.toggle('active', tabName === 'logs');
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.pushState({}, '', url);
    }

    function updateFileName(input) {
        if (input.files && input.files[0]) {
            document.getElementById('fileName').textContent = input.files[0].name;
            document.getElementById('fileName').classList.add('text-blue-600');
        }
    }

    document.getElementById('importForm').addEventListener('submit', function() {
        document.getElementById('importModal').classList.add('hidden');
        document.getElementById('importLoading').classList.remove('hidden');
        document.getElementById('importLoading').classList.add('flex');
    });

    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        switchTab(urlParams.get('tab') || 'recap');
        lucide.createIcons();
    });
</script>

<style>
    .tab-btn.active { background-color: white; color: #0F172A; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
    .tab-btn:not(.active) { color: #64748B; }
    .card-3d { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .card-3d:hover { transform: translateY(-4px); }
    .btn-3d { transition: all 0.2s; border-bottom: 4px solid rgba(0,0,0,0.2); }
    .btn-3d:active { transform: translateY(2px); border-bottom-width: 0; }
</style>
@endsection
