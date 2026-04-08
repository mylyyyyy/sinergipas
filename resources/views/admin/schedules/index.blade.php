@extends('layouts.app')

@section('title', 'Jadwal Pegawai')
@section('header-title', 'Roster & Penjadwalan')

@section('content')
<style>
    /* Force scrollbar visibility and style */
    .schedule-container::-webkit-scrollbar {
        height: 10px !important;
        display: block !important;
    }
    .schedule-container::-webkit-scrollbar-track {
        background: #f8fafc !important;
        border-radius: 10px !important;
        border: 1px solid #e2e8f0;
    }
    .schedule-container::-webkit-scrollbar-thumb {
        background: #334155 !important;
        border-radius: 10px !important;
        border: 2px solid #f8fafc;
    }
    .schedule-container::-webkit-scrollbar-thumb:hover {
        background: #0f172a !important;
    }
    
    /* Ensure the container behaves correctly */
    .schedule-wrapper {
        width: 100%;
        max-width: 100%;
        position: relative;
    }

    /* Modal Height Fix */
    .modal-scrollable {
        max-height: 85vh;
        overflow-y: auto;
    }
</style>

<!-- Custom Loading Overlay for Roster Generation -->
<div id="rosterLoading" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/60 backdrop-blur-md">
    <div class="bg-white rounded-[32px] p-10 shadow-2xl max-w-sm w-full text-center animate-in zoom-in duration-300">
        <div class="relative w-24 h-24 mx-auto mb-6">
            <div class="absolute inset-0 border-4 border-slate-100 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-amber-500 rounded-full border-t-transparent animate-spin"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                <i data-lucide="wand-2" class="w-10 h-10 text-amber-500 animate-pulse"></i>
            </div>
        </div>
        <h3 class="text-xl font-bold text-slate-900 mb-2">Menyusun Roster</h3>
        <p class="text-sm text-slate-500 font-medium leading-relaxed">Sistem sedang menghitung pola shift dan menyinkronkan jadwal seluruh personel...</p>
    </div>
</div>

<div class="space-y-8 page-fade">
    <!-- Header & Tools -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 bg-white p-6 rounded-[32px] border border-slate-200 shadow-sm card-3d">
        <div class="flex flex-wrap items-center gap-4 w-full lg:w-auto">
            <form action="{{ route('admin.schedules.index') }}" method="GET" class="w-full lg:w-auto flex items-center gap-3">
                <div class="relative group">
                    <i data-lucide="calendar" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                    <input type="month" name="month" value="{{ $month->format('Y-m') }}" onchange="this.form.submit()" class="pl-11 pr-4 py-2.5 rounded-2xl text-sm font-bold text-slate-700 outline-none border border-slate-100 bg-slate-50 focus:bg-white focus:border-blue-500 transition-all">
                </div>
            </form>

            <div class="h-8 w-px bg-slate-100 hidden md:block"></div>

            <div class="flex bg-slate-50 p-1 rounded-2xl border border-slate-100">
                <button onclick="handleDownload('{{ route('admin.schedules.export', ['month' => $month->format('Y-m'), 'type' => 'pdf']) }}', 'jadwal-dinas-{{ $month->format('Y-m') }}.pdf')" class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-white hover:text-red-600 transition-all flex items-center gap-2 group">
                    <i data-lucide="file-text" class="w-4 h-4 text-slate-400 group-hover:text-red-500 transition-colors"></i> PDF
                </button>
                <button onclick="handleDownload('{{ route('admin.schedules.export', ['month' => $month->format('Y-m'), 'type' => 'excel']) }}', 'jadwal-dinas-{{ $month->format('Y-m') }}.xlsx')" class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-white hover:text-green-600 transition-all flex items-center gap-2 group">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-slate-400 group-hover:text-green-500 transition-colors"></i> EXCEL
                </button>
            </div>
        </div>

        <div class="flex flex-wrap gap-3 w-full lg:w-auto">
            <button onclick="confirmResetJadwal()" class="flex-1 lg:flex-none px-6 py-3.5 rounded-2xl bg-red-50 text-red-600 border border-red-100 font-bold text-[10px] uppercase tracking-widest hover:bg-red-600 hover:text-white transition-all flex items-center justify-center gap-3 group">
                <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Reset Jadwal
            </button>
            <a href="{{ route('admin.squads.index') }}" class="flex-1 lg:flex-none px-6 py-3.5 rounded-2xl bg-white border border-slate-200 text-slate-600 font-bold text-[10px] uppercase tracking-widest hover:border-blue-500 hover:text-blue-600 transition-all flex items-center justify-center gap-3 group">
                <i data-lucide="users" class="w-4 h-4 group-hover:scale-110 transition-transform"></i> Manajemen Regu
            </a>
            <button onclick="document.getElementById('rosterModal').classList.remove('hidden')" class="flex-1 lg:flex-none px-8 py-3.5 rounded-2xl bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl btn-3d flex items-center justify-center gap-3">
                <i data-lucide="wand-2" class="w-4 h-4 text-amber-400"></i> Generate Roster
            </button>
        </div>
    </div>

    <!-- Schedule Grid -->
    <div class="schedule-wrapper bg-white rounded-[40px] border border-slate-200 shadow-sm card-3d">
        <div class="overflow-x-auto schedule-container rounded-[40px] w-full">
            <table class="w-full border-collapse" style="min-width: 1800px;">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100">
                        <th class="sticky left-0 z-20 bg-slate-50 px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] min-w-[280px] border-r border-slate-100 text-left shadow-[4px_0_10px_-2px_rgba(0,0,0,0.1)]">
                            <div class="flex items-center gap-2">
                                <i data-lucide="user-cog" class="w-4 h-4"></i>
                                Nama Personel
                            </div>
                        </th>
                        @for($d = 1; $d <= $daysInMonth; $d++)
                            @php $currentDate = $month->copy()->day($d); @endphp
                            <th class="px-2 py-4 text-center min-w-[50px] {{ $currentDate->isWeekend() ? 'bg-red-50 text-red-500' : 'text-slate-400' }} border-r border-slate-50/50">
                                <p class="text-[8px] font-black uppercase tracking-tighter">{{ $currentDate->translatedFormat('D') }}</p>
                                <p class="text-sm font-black mt-0.5">{{ $d }}</p>
                            </th>
                        @endfor
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($employees as $emp)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="sticky left-0 z-10 bg-white px-8 py-5 border-r border-slate-100 shadow-[4px_0_10px_-4px_rgba(0,0,0,0.1)] group-hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 text-slate-500 flex items-center justify-center text-xs font-black shrink-0 border border-white shadow-sm group-hover:from-blue-500 group-hover:to-indigo-600 group-hover:text-white transition-all duration-500">
                                    {{ substr($emp->full_name, 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-black text-slate-900 group-hover:text-blue-600 transition-colors truncate">{{ $emp->full_name }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest truncate max-w-[120px]">{{ $emp->position }}</span>
                                        @if($emp->squad)
                                            <span class="px-1.5 py-0.5 rounded-md bg-indigo-50 text-indigo-600 text-[8px] font-black uppercase border border-indigo-100">Regu {{ $emp->squad->name }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        @for($d = 1; $d <= $daysInMonth; $d++)
                            @php 
                                $dateStr = $month->copy()->day($d)->format('Y-m-d');
                                $schedule = $schedules->get($emp->id)?->firstWhere('date', $dateStr);
                                $shiftName = $schedule?->shift?->name;
                                
                                $colorClass = 'bg-slate-50 text-slate-300 border-slate-100';
                                $indicator = '-';
                                
                                if($shiftName == 'Pagi') {
                                    $colorClass = 'bg-emerald-500 text-white border-emerald-600 shadow-md shadow-emerald-200';  
                                    $indicator = 'P';
                                } elseif($shiftName == 'Siang') {
                                    $colorClass = 'bg-amber-500 text-white border-amber-600 shadow-md shadow-amber-200';
                                    $indicator = 'S';
                                } elseif($shiftName == 'Malam') {
                                    $colorClass = 'bg-slate-800 text-white border-slate-900 shadow-md shadow-slate-400';        
                                    $indicator = 'M';
                                } elseif($shiftName == 'Kantor') {
                                    $colorClass = 'bg-blue-500 text-white border-blue-600 shadow-md shadow-blue-200';
                                    $indicator = 'K';
                                }
                            @endphp
                            <td class="p-1.5 border-r border-slate-50">
                                <div class="w-full h-10 rounded-xl border {{ $colorClass }} flex items-center justify-center text-xs font-black transition-all cursor-pointer select-none hover:scale-110" 
                                     title="{{ $emp->full_name }} - {{ $dateStr }} ({{ $shiftName ?? 'Libur' }})"
                                     onclick="openManualAssign({{ $emp->id }}, '{{ $emp->full_name }}', '{{ $dateStr }}', '{{ $schedule?->shift_id }}')">
                                    {{ $indicator }}
                                </div>
                            </td>
                        @endfor
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Legend -->
    <div class="bg-white p-8 rounded-[40px] border border-slate-200 shadow-sm card-3d">
        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-8 flex items-center gap-3">
            <i data-lucide="info" class="w-5 h-5 text-blue-500"></i> Informasi Kode Shift & Jam Dinas
        </h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="flex items-center gap-5 p-5 rounded-3xl bg-slate-50 border border-slate-100 group hover:bg-white hover:border-emerald-200 transition-all">
                <span class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center font-black text-lg shadow-xl shadow-emerald-200 group-hover:scale-110 transition-transform">P</span>
                <div>
                    <p class="text-sm font-black text-slate-900 uppercase tracking-tight">Dinas Pagi</p>
                    <p class="text-[11px] font-bold text-emerald-600 mt-0.5">07:30 - 13:00 WIB</p>
                </div>
            </div>
            <div class="flex items-center gap-5 p-5 rounded-3xl bg-slate-50 border border-slate-100 group hover:bg-white hover:border-amber-200 transition-all">
                <span class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center font-black text-lg shadow-xl shadow-amber-200 group-hover:scale-110 transition-transform">S</span>
                <div>
                    <p class="text-sm font-black text-slate-900 uppercase tracking-tight">Dinas Siang</p>
                    <p class="text-[11px] font-bold text-amber-600 mt-0.5">13:00 - 19:00 WIB</p>
                </div>
            </div>
            <div class="flex items-center gap-5 p-5 rounded-3xl bg-slate-50 border border-slate-100 group hover:bg-white hover:border-slate-400 transition-all">
                <span class="w-12 h-12 rounded-2xl bg-slate-800 text-white flex items-center justify-center font-black text-lg shadow-xl shadow-slate-300 group-hover:scale-110 transition-transform">M</span>
                <div>
                    <p class="text-sm font-black text-slate-900 uppercase tracking-tight">Dinas Malam</p>
                    <p class="text-[11px] font-bold text-slate-500 mt-0.5">19:00 - 07:30 WIB</p>
                </div>
            </div>
            <div class="flex items-center gap-5 p-5 rounded-3xl bg-slate-50 border border-slate-100 group hover:bg-white hover:border-blue-200 transition-all">
                <span class="w-12 h-12 rounded-2xl bg-blue-500 text-white flex items-center justify-center font-black text-lg shadow-xl shadow-blue-200 group-hover:scale-110 transition-transform">K</span>
                <div>
                    <p class="text-sm font-black text-slate-900 uppercase tracking-tight">Jam Kantor</p>
                    <p class="text-[11px] font-bold text-blue-600 mt-0.5">07:30 - 16:00 WIB</p>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="resetScheduleForm" action="{{ route('admin.schedules.reset') }}" method="POST" class="hidden no-loader">
    @csrf @method('DELETE')
    <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
</form>

<!-- Roster Generator Modal -->
<div id="rosterModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-xl rounded-[40px] p-10 shadow-2xl animate-in zoom-in duration-200 relative overflow-hidden modal-scrollable">
        <div class="absolute top-0 right-0 w-32 h-32 bg-amber-50 rounded-full -mr-16 -mt-16 opacity-50"></div>
        <div class="relative z-10">
            <div class="flex justify-between items-center mb-8 border-b border-slate-100 pb-6">
                <div>
                    <h3 class="text-2xl font-black text-slate-900 tracking-tight italic">Auto-Generate Roster</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Sinkronisasi Jadwal Regu & Staf</p>
                </div>
                <button onclick="document.getElementById('rosterModal').classList.add('hidden')" class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 hover:text-red-500 transition-colors border border-slate-100">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form id="rosterForm" action="{{ route('admin.schedules.generate') }}" method="POST" class="space-y-8">
                @csrf
                <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
                
                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Pilih Regu / Kelompok</label>
                        <select name="squad_id" required class="w-full px-5 py-4 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-black text-slate-700 focus:bg-white focus:border-blue-500 outline-none appearance-none cursor-pointer shadow-sm">
                            <option value="">-- Pilih Regu --</option>
                            @foreach($squads as $squad)
                                <option value="{{ $squad->id }}">{{ $squad->name }} ({{ $squad->employees_count ?? $squad->employees->count() }} Anggota)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Tanggal Mulai Pola</label>
                        <input type="date" name="start_date" required value="{{ $month->format('Y-m-01') }}" class="w-full px-5 py-4 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-black text-slate-700 outline-none focus:bg-white focus:border-blue-500 transition-all shadow-sm">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Durasi Penjadwalan</label>
                    <select name="duration_months" class="w-full px-5 py-4 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-black text-slate-700 focus:bg-white focus:border-blue-500 outline-none appearance-none cursor-pointer shadow-sm">
                        <option value="1">Hanya 1 Bulan (Bulan Terpilih)</option>
                        <option value="2">2 Bulan Sekaligus</option>
                        <option value="3">3 Bulan Sekaligus</option>
                        <option value="6">6 Bulan Sekaligus</option>
                        <option value="12">1 Tahun Sekaligus</option>
                    </select>
                </div>

                <div class="space-y-4">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Urutan Pola Berulang (Shift Jaga)</label>
                    <div class="grid grid-cols-4 gap-3">
                        @for($i = 0; $i < 4; $i++)
                        <div class="space-y-1.5">
                            <span class="text-[8px] font-black text-slate-400 uppercase ml-1">H-{{ $i + 1 }}</span>
                            <select name="pattern[]" class="w-full px-3 py-4 rounded-xl border border-slate-200 bg-white text-xs font-black outline-none focus:border-blue-500 shadow-sm text-center">
                                <option value="">OFF</option>
                                @foreach($shifts as $s)
                                    <option value="{{ $s->id }}" {{ $i == $loop->index ? 'selected' : '' }}>{{ substr(strtoupper($s->name), 0, 1) }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endfor
                    </div>
                </div>

                <div class="p-5 bg-blue-50 rounded-3xl border border-blue-100 flex gap-4">
                    <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shrink-0 shadow-sm">
                        <i data-lucide="info" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <p class="text-[10px] font-bold text-blue-600 leading-relaxed italic">
                        Klik eksekusi akan menimpa jadwal yang sudah ada pada periode tersebut. Seluruh Staf otomatis akan dijadwalkan Jam Kantor (Senin-Jumat).
                    </p>
                </div>

                <div class="flex gap-4">
                    <button type="button" onclick="document.getElementById('rosterModal').classList.add('hidden')" class="flex-1 py-5 bg-slate-100 text-slate-500 rounded-[24px] font-black text-sm uppercase tracking-widest hover:bg-slate-200 transition-all">Batal</button>
                    <button type="submit" class="flex-[2] bg-slate-900 text-white py-5 rounded-[24px] font-black text-sm uppercase tracking-[0.2em] hover:bg-blue-600 transition-all shadow-2xl btn-3d">
                        Eksekusi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Manual Assign Modal -->
<div id="manualModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-sm rounded-[40px] p-10 shadow-2xl animate-in zoom-in duration-200">
        <h3 class="text-xl font-black text-slate-900 mb-2 italic">Penyesuaian Jadwal</h3>
        <p id="manual_info" class="text-[10px] text-blue-600 font-black uppercase tracking-widest mb-8"></p>
        
        <form id="manualForm" class="space-y-8">
            @csrf
            <input type="hidden" id="manual_emp_id">
            <input type="hidden" id="manual_date">
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Pilih Jenis Shift</label>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" onclick="setManualShift(null)" class="manual-shift-btn p-4 rounded-2xl border-2 border-slate-100 text-[10px] font-black uppercase tracking-widest hover:border-slate-300 transition-all bg-slate-50 text-slate-400" id="btn-shift-none">LIBUR</button>
                    @foreach($shifts as $s)
                        <button type="button" onclick="setManualShift({{ $s->id }})" class="manual-shift-btn p-4 rounded-2xl border-2 border-slate-100 text-[10px] font-black uppercase tracking-widest hover:border-blue-500 transition-all" id="btn-shift-{{ $s->id }}">{{ strtoupper($s->name) }}</button>
                    @endforeach
                </div>
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('manualModal').classList.add('hidden')" class="flex-1 py-4 bg-slate-100 text-slate-500 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-200 transition-all">Batal</button>
                <button type="button" onclick="submitManual()" class="flex-[2] py-4 bg-blue-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-700 transition-all shadow-xl shadow-blue-100">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentSelectedShiftId = null;

    function openManualAssign(empId, empName, date, shiftId) {
        document.getElementById('manual_emp_id').value = empId;
        document.getElementById('manual_date').value = date;
        document.getElementById('manual_info').innerText = `${empName} • ${date}`;
        
        // Reset and highlight current
        document.querySelectorAll('.manual-shift-btn').forEach(btn => {
            btn.classList.remove('bg-blue-600', 'text-white', 'border-blue-600');
            btn.classList.add('bg-white', 'text-slate-600', 'border-slate-100');
        });

        currentSelectedShiftId = shiftId || null;
        const activeBtn = shiftId ? document.getElementById(`btn-shift-${shiftId}`) : document.getElementById('btn-shift-none');
        if (activeBtn) {
            activeBtn.classList.add('bg-blue-600', 'text-white', 'border-blue-600');
            activeBtn.classList.remove('bg-white', 'text-slate-600', 'border-slate-100');
        }

        document.getElementById('manualModal').classList.remove('hidden');
    }

    function setManualShift(id) {
        currentSelectedShiftId = id;
        document.querySelectorAll('.manual-shift-btn').forEach(btn => {
            btn.classList.remove('bg-blue-600', 'text-white', 'border-blue-600');
            btn.classList.add('bg-white', 'text-slate-600', 'border-slate-100');
        });
        const activeBtn = id ? document.getElementById(`btn-shift-${id}`) : document.getElementById('btn-shift-none');
        activeBtn.classList.add('bg-blue-600', 'text-white', 'border-blue-600');
    }

    async function submitManual() {
        const empId = document.getElementById('manual_emp_id').value;
        const date = document.getElementById('manual_date').value;

        try {
            const response = await fetch("{{ route('admin.schedules.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ employee_id: empId, shift_id: currentSelectedShiftId, date: date })
            });
            if (response.ok) {
                location.reload();
            }
        } catch (error) {
            console.error(error);
        }
    }

    function confirmResetJadwal() {
        Swal.fire({
            title: 'Bersihkan Jadwal?',
            text: "Seluruh data jadwal pada bulan ini akan dihapus permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            confirmButtonText: 'Ya, Bersihkan!',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-[32px]' }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('resetScheduleForm').submit();
            }
        });
    }

    // Roster Generation Loading
    document.getElementById('rosterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Konfirmasi Generate',
            text: "Jadwal yang sudah ada pada periode tersebut akan ditimpa. Lanjutkan?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#F59E0B',
            confirmButtonText: 'Ya, Jalankan!',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-[32px]' }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('rosterModal').classList.add('hidden');
                document.getElementById('rosterLoading').classList.remove('hidden');
                document.getElementById('rosterLoading').classList.add('flex');
                this.submit();
            }
        });
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
