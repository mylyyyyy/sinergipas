@extends('layouts.app')

@section('title', 'Manajemen Kehadiran')
@section('header-title', 'Absensi & Uang Makan')

@section('content')
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
        <p class="text-sm text-slate-500 font-medium leading-relaxed">Mohon tunggu sebentar, sistem sedang memproses data absensi dari mesin...</p>
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
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Kehadiran ({{ \Carbon\Carbon::parse($monthStr)->translatedFormat('F') }})</p>
                <h3 class="text-2xl font-bold text-slate-900">{{ number_format($summary->total_present) }}</h3>
            </div>
        </div>
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm card-3d flex items-center gap-5 border-l-4 border-l-amber-500">
            <div class="w-14 h-14 rounded-2xl bg-amber text-amber-600 flex items-center justify-center shrink-0">
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
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Estimasi Uang Makan</p>
                <h3 class="text-2xl font-bold text-white">Rp {{ number_format($summary->total_allowance, 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <!-- Actions & Filters -->
    <div class="bg-white p-6 md:p-8 rounded-[40px] border border-slate-200 shadow-sm card-3d relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-slate-50 rounded-full -mr-32 -mt-32 opacity-40"></div>
        
        <form action="{{ route('admin.attendance.index') }}" method="GET" class="relative z-10 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-end">
                <!-- Search Box -->
                <div class="lg:col-span-5">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Pencarian Pegawai</label>
                    <div class="relative group">
                        <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                        <input type="text" name="search" id="main_search" value="{{ request('search') }}" placeholder="Cari NIP atau Nama..." class="w-full pl-11 pr-4 py-3.5 rounded-2xl border-2 border-slate-50 bg-slate-50 text-sm font-bold text-slate-700 outline-none focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all">
                    </div>
                </div>

                <!-- Date Range -->
                <div class="lg:col-span-7">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Rentang Tanggal Absensi</label>
                    <div class="flex items-center gap-3">
                        <div class="relative flex-1 group">
                            <i data-lucide="calendar" class="absolute left-4 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400"></i>
                            <input type="date" name="start_date" id="main_start_date" value="{{ $startDate }}" class="w-full pl-10 pr-4 py-3.5 rounded-2xl border-2 border-slate-50 bg-slate-50 text-xs font-bold text-slate-700 outline-none focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all">
                        </div>
                        <span class="text-slate-300 font-bold text-sm">s/d</span>
                        <div class="relative flex-1 group">
                            <i data-lucide="calendar" class="absolute left-4 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400"></i>
                            <input type="date" name="end_date" id="main_end_date" value="{{ $endDate }}" class="w-full pl-10 pr-4 py-3.5 rounded-2xl border-2 border-slate-50 bg-slate-50 text-xs font-bold text-slate-700 outline-none focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
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
                    <button type="button" onclick="document.getElementById('manualModal').classList.remove('hidden')" class="px-6 py-3.5 rounded-2xl bg-emerald-600 text-white font-bold text-[10px] uppercase tracking-widest hover:bg-emerald-700 transition-all shadow-lg flex items-center gap-2">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i> Input Manual
                    </button>
                    <button type="button" onclick="document.getElementById('importModal').classList.remove('hidden')" class="px-6 py-3.5 rounded-2xl bg-slate-900 text-white font-bold text-[10px] uppercase tracking-widest hover:bg-blue-600 transition-all shadow-lg flex items-center gap-2">
                        <i data-lucide="upload-cloud" class="w-4 h-4 text-amber-400"></i> Import Finger
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Info Banner -->
    <div class="flex items-center gap-4 p-4 bg-blue-50/50 rounded-2xl border border-blue-100/50 backdrop-blur-sm animate-in fade-in slide-in-from-top-4 duration-500">
        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
            <i data-lucide="info" class="w-5 h-5"></i>
        </div>
        <div>
            <p class="text-[10px] font-bold text-blue-400 uppercase tracking-widest leading-none mb-1">Status View</p>
            <p class="text-xs font-bold text-blue-700 uppercase tracking-tight">Menampilkan data periode: <span class="text-blue-900">{{ $rangeTitle }}</span></p>
        </div>
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
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Hadir (Hari)</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Terlambat (Total)</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Uang Makan</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($employees as $emp)
                        @php
                            $totalHadir = $emp->attendances->where('status', '!=', 'absent')->count();
                            $totalTelat = $emp->attendances->sum('late_minutes');
                            $currentRate = $emp->rank_relation->meal_allowance ?? 0;
                            $totalUangMakan = $totalHadir * $currentRate;
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
                                        <p class="text-[10px] font-mono font-bold text-slate-400">NIP. {{ $emp->nip }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm font-bold text-slate-900">{{ $totalHadir }} Hari</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($totalTelat > 0)
                                    <span class="text-sm font-bold text-red-500">{{ $totalTelat }} Menit</span>
                                @else
                                    <span class="px-3 py-1 rounded-lg bg-emerald-50 text-emerald-600 text-[11px] font-bold border border-emerald-100 italic">
                                        0 Menit
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <p class="text-sm font-bold text-emerald-600">Rp {{ number_format($totalUangMakan, 0, ',', '.') }}</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Gol. {{ $emp->rank_class ?? '-' }}</p>
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
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest italic">Belum ada data untuk periode ini</p>
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
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tanggal</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pegawai</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Jam Masuk</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Jam Pulang</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($attendanceLogs as $log)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-slate-900">{{ \Carbon\Carbon::parse($log->date)->translatedFormat('d F Y') }}</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase">{{ \Carbon\Carbon::parse($log->date)->translatedFormat('l') }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-slate-900 truncate max-w-[200px]">{{ $log->employee->full_name }}</p>
                                <p class="text-[10px] font-mono text-slate-400">NIP. {{ $log->employee->nip }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1.5 bg-blue-50 text-blue-600 rounded-xl text-xs font-black border border-blue-100">
                                    {{ $log->check_in ? \Carbon\Carbon::parse($log->check_in)->format('H:i') : '--:--' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1.5 bg-slate-50 text-slate-600 rounded-xl text-xs font-black border border-slate-100">
                                    {{ $log->check_out && $log->check_out != $log->check_in ? \Carbon\Carbon::parse($log->check_out)->format('H:i') : '--:--' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($log->status === 'present')
                                    <span class="px-2.5 py-1 bg-green-50 text-green-600 rounded-lg text-[9px] font-black uppercase tracking-wider border border-green-100">Hadir Tepat Waktu</span>
                                @elseif($log->status === 'late')
                                    <div class="flex flex-col items-center">
                                        <span class="px-2.5 py-1 bg-amber-50 text-amber-600 rounded-lg text-[9px] font-black uppercase tracking-wider border border-amber-100">Terlambat</span>
                                        <span class="text-[8px] font-bold text-red-400 mt-1">{{ $log->late_minutes }} Menit</span>
                                    </div>
                                @else
                                    <span class="px-2.5 py-1 bg-red-50 text-red-600 rounded-lg text-[9px] font-black uppercase tracking-wider border border-red-100">{{ strtoupper($log->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest italic">Belum ada aktivitas absensi tercatat</p>
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
                    <h3 class="text-2xl font-bold text-slate-900 tracking-tight">Impor Data Absensi</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Sinkronisasi Fingerprint Mesin</p>
                </div>
                <button onclick="document.getElementById('importModal').classList.add('hidden')" class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 hover:text-red-500 transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <div class="mb-8 p-5 bg-amber-50 rounded-2xl border border-amber-100">
                <h4 class="text-[10px] font-bold text-amber-800 uppercase tracking-widest mb-2">Petunjuk Format:</h4>
                <p class="text-[10px] font-semibold text-amber-700 leading-relaxed italic">
                    Sistem mendukung file .xlsx / .xls. Pastikan kolom NIP dan Waktu Scan tersedia sesuai template mesin absensi Anda.
                </p>
            </div>

            <form id="importForm" action="{{ route('admin.attendance.import') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div class="p-8 rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 text-center group hover:bg-white hover:border-blue-400 transition-all cursor-pointer relative">
                    <input type="file" name="file" required class="absolute inset-0 opacity-0 cursor-pointer" onchange="updateFileName(this)">
                    <i data-lucide="file-spreadsheet" class="w-10 h-10 text-slate-300 mx-auto mb-3 group-hover:text-blue-500 group-hover:scale-110 transition-all"></i>
                    <p id="fileName" class="text-xs font-bold text-slate-500 group-hover:text-blue-600">Klik untuk pilih file Excel</p>
                </div>
                <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-bold text-sm uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl btn-3d">
                    Mulai Sinkronisasi
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div id="exportModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-md rounded-[32px] p-10 shadow-2xl animate-in zoom-in duration-200">
        <h3 class="text-2xl font-bold text-slate-900 mb-6 italic">Export Laporan Kehadiran</h3>
        <form id="exportForm" class="space-y-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Filter Jenis Laporan</label>
                    <select name="filter" id="export_filter" onchange="updateExportUI()" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm font-bold outline-none focus:border-blue-500">
                        <option value="monthly">Rekapitulasi Bulanan (Seluruh Pegawai)</option>
                        <option value="weekly">Rekapitulasi Mingguan (Rentang Tanggal)</option>
                        <option value="daily">Laporan Harian (Satu Hari)</option>
                    </select>
                </div>

                <!-- Range Container (Weekly) -->
                <div id="range_container" class="hidden grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Dari</label>
                        <input type="date" name="start_date" id="export_start_date" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm font-bold outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Sampai</label>
                        <input type="date" name="end_date" id="export_end_date" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm font-bold outline-none focus:border-blue-500">
                    </div>
                </div>

                <!-- Single Date Container (Daily) -->
                <div id="date_input_container" class="hidden">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Pilih Tanggal</label>
                    <input type="date" name="exact_date" id="export_exact_date" value="{{ date('Y-m-d') }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm font-bold outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Format File</label>
                    <select name="type" id="export_type" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm font-bold outline-none focus:border-blue-500">
                        <option value="pdf">Dokumen PDF Resmi (KOP & Logo)</option>
                        <option value="excel">Microsoft Excel (.xlsx)</option>
                    </select>
                </div>
                <input type="hidden" name="month" id="export_month" value="{{ $monthStr }}">
            </div>
            <button type="button" onclick="submitGlobalExport()" class="w-full py-4 bg-blue-600 text-white rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg btn-3d">
                Download Laporan Sekarang
            </button>
            <button type="button" onclick="document.getElementById('exportModal').classList.add('hidden')" class="w-full text-slate-400 font-bold text-[10px] uppercase tracking-widest mt-2">Batal</button>
        </form>
    </div>
</div>

<!-- Individual Export Modal -->
<div id="individualExportModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-sm rounded-[32px] p-10 shadow-2xl animate-in zoom-in duration-200">
        <h3 class="text-xl font-bold text-slate-900 mb-2 italic">Export Laporan Individu</h3>
        <p id="individual_name" class="text-sm font-bold text-blue-600 mb-6"></p>
        
        <form id="individualExportForm" class="space-y-6">
            <input type="hidden" name="filter" value="individual">
            <input type="hidden" name="employee_id" id="individual_emp_id">
            <input type="hidden" name="month" id="individual_month" value="{{ $monthStr }}">
            
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Format Laporan</label>
                <select name="type" id="individual_type" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm font-bold outline-none focus:border-blue-500">
                    <option value="pdf">Dokumen PDF Resmi</option>
                    <option value="excel">Microsoft Excel (.xlsx)</option>
                </select>
            </div>

            <button type="button" onclick="submitIndividualExport()" class="w-full py-4 bg-slate-900 text-white rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-blue-600 transition-all shadow-lg">
                Download Laporan Individu
            </button>
            <button type="button" onclick="document.getElementById('individualExportModal').classList.add('hidden')" class="w-full text-slate-400 font-bold text-[10px] uppercase tracking-widest mt-2">Batal</button>
        </form>
    </div>
</div>

<style>
    .tab-btn.active {
        background-color: white;
        color: #0F172A;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    }
    .tab-btn:not(.active) {
        color: #64748B;
    }
    .tab-btn:not(.active):hover {
        color: #0F172A;
        background-color: rgba(255, 255, 255, 0.5);
    }
</style>

    <!-- Manual Attendance Modal -->
    <div id="manualModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4 animate-in fade-in duration-300">
        <div class="bg-white rounded-[40px] shadow-2xl w-full max-w-lg overflow-hidden card-3d">
            <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <div>
                    <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight italic">Input Pengecualian</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Input absensi manual (Cuti, Sakit, DL, dll)</p>
                </div>
                <button onclick="document.getElementById('manualModal').classList.add('hidden')" class="w-10 h-10 rounded-2xl hover:bg-slate-100 flex items-center justify-center transition-colors">
                    <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                </button>
            </div>

            <form action="{{ route('admin.attendance.store-manual') }}" method="POST" class="p-8 space-y-6">
                @csrf
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Pilih Pegawai</label>
                    <select name="employee_id" required class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-2 border-transparent focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 outline-none transition-all font-bold text-sm text-slate-700">
                        <option value="">-- Pilih Pegawai --</option>
                        @foreach($allEmployees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->nip }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Tanggal</label>
                        <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-2 border-transparent focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 outline-none transition-all font-bold text-sm text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Status</label>
                        <select name="status" required class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-2 border-transparent focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 outline-none transition-all font-bold text-sm text-slate-700">
                            <option value="present">Hadir (Kantor)</option>
                            <option value="picket">Hadir (Piket)</option>
                            <option value="on_leave">Cuti / Izin</option>
                            <option value="absent">Tanpa Keterangan</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Jam Masuk</label>
                        <input type="time" name="check_in" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-2 border-transparent focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 outline-none transition-all font-bold text-sm text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Jam Keluar</label>
                        <input type="time" name="check_out" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-2 border-transparent focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 outline-none transition-all font-bold text-sm text-slate-700">
                    </div>
                </div>

                <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-bold text-sm uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl btn-3d">
                    Simpan Absensi
                </button>
            </form>
        </div>
    </div>

<script>
    // Global handleDownload function
    function handleDownload(url, filename) {
        // For standard GET requests, window.location.href is usually enough
        // but if we want to ensure filename (optional in standard browser behavior)
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function openIndividualExportModal(id, name) {
        document.getElementById('individual_emp_id').value = id;
        document.getElementById('individual_name').innerText = name;
        document.getElementById('individualExportModal').classList.remove('hidden');
    }

    function updateExportUI() {
        const filter = document.getElementById('export_filter').value;
        const dateContainer = document.getElementById('date_input_container');
        const rangeContainer = document.getElementById('range_container');
        
        dateContainer.classList.toggle('hidden', filter !== 'daily');
        rangeContainer.classList.toggle('hidden', filter !== 'weekly');
    }

    function submitGlobalExport() {
        const filter = document.getElementById('export_filter').value;
        const type = document.getElementById('export_type').value;
        const month = document.getElementById('export_month').value;
        const startDate = document.getElementById('export_start_date').value;
        const endDate = document.getElementById('export_end_date').value;
        const exactDate = document.getElementById('export_exact_date').value;

        let url = `/admin/attendance/export?filter=${filter}&type=${type}&month=${month}`;
        if (filter === 'weekly') url += `&start_date=${startDate}&end_date=${endDate}`;
        if (filter === 'daily') url += `&exact_date=${exactDate}`;

        document.getElementById('exportModal').classList.add('hidden');
        handleDownload(url, `laporan-kehadiran-${filter}.${type === 'pdf' ? 'pdf' : 'xlsx'}`);
    }

    function submitIndividualExport() {
        const empId = document.getElementById('individual_emp_id').value;
        const type = document.getElementById('individual_type').value;
        const month = document.getElementById('individual_month').value;
        const name = document.getElementById('individual_name').innerText;

        const url = `/admin/attendance/export?filter=individual&employee_id=${empId}&type=${type}&month=${month}`;
        
        document.getElementById('individualExportModal').classList.add('hidden');
        handleDownload(url, `laporan-${name}.${type === 'pdf' ? 'pdf' : 'xlsx'}`);
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

    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab') || 'recap';
        switchTab(activeTab);
    });

    function updateFileName(input) {
        if (input.files && input.files[0]) {
            document.getElementById('fileName').textContent = input.files[0].name;
            document.getElementById('fileName').classList.add('text-blue-600');
        }
    }

    function openExportModal() {
        document.getElementById('exportModal').classList.remove('hidden');
    }

    document.getElementById('importForm').addEventListener('submit', function() {
        document.getElementById('importModal').classList.add('hidden');
        document.getElementById('importLoading').classList.remove('hidden');
        document.getElementById('importLoading').classList.add('flex');
    });
</script>

@if(session('success'))
<script>
    window.addEventListener('DOMContentLoaded', () => {
        Swal.fire({ icon: 'success', title: 'Berhasil', text: "{{ session('success') }}", confirmButtonColor: '#0F172A', customClass: { popup: 'rounded-2xl' } });
    });
</script>
@endif

@if(session('error'))
<script>
    window.addEventListener('DOMContentLoaded', () => {
        Swal.fire({ icon: 'error', title: 'Gagal', text: "{{ session('error') }}", confirmButtonColor: '#EF4444', customClass: { popup: 'rounded-2xl' } });
    });
</script>
@endif
@endsection
