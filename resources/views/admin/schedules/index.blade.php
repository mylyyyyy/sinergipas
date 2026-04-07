@extends('layouts.app')

@section('title', 'Jadwal Pegawai')
@section('header-title', 'Roster & Penjadwalan')

@section('content')
<div class="space-y-8 page-fade">
    <!-- Header & Tools -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
        <div class="flex flex-wrap items-center gap-4 w-full lg:w-auto">
            <form action="{{ route('admin.schedules.index') }}" method="GET" class="w-full lg:w-auto">
                <div class="bg-white p-1 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-1">
                    <input type="month" name="month" value="{{ $month->format('Y-m') }}" onchange="this.form.submit()" class="px-4 py-2 rounded-xl text-sm font-bold text-slate-700 outline-none border-none bg-transparent">
                </div>
            </form>

            <div class="flex bg-white p-1 rounded-2xl border border-slate-200 shadow-sm">
                <a href="{{ route('admin.schedules.export', ['month' => $month->format('Y-m'), 'type' => 'pdf']) }}" class="px-5 py-2 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-slate-50 transition-all flex items-center gap-2 no-loader">
                    <i data-lucide="file-text" class="w-4 h-4 text-red-500"></i> PDF
                </a>
                <a href="{{ route('admin.schedules.export', ['month' => $month->format('Y-m'), 'type' => 'excel']) }}" class="px-5 py-2 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-slate-50 transition-all flex items-center gap-2 no-loader">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-green-500"></i> EXCEL
                </a>
            </div>
        </div>

        <div class="flex flex-wrap gap-3 w-full lg:w-auto">
            <a href="{{ route('admin.squads.index') }}" class="flex-1 lg:flex-none px-6 py-3 rounded-xl bg-slate-100 text-slate-600 font-bold text-[10px] uppercase tracking-wider hover:bg-slate-200 transition-all flex items-center justify-center gap-2">
                <i data-lucide="users" class="w-4 h-4"></i> Manajemen Regu
            </a>
            <button onclick="document.getElementById('rosterModal').classList.remove('hidden')" class="flex-1 lg:flex-none px-6 py-3 rounded-xl bg-amber-600 text-white font-bold text-[10px] uppercase tracking-wider hover:bg-amber-700 transition-all shadow-lg btn-3d flex items-center justify-center gap-2">
                <i data-lucide="wand-2" class="w-4 h-4"></i> Generate Roster Otomatis
            </button>
        </div>
    </div>

    <!-- Schedule Grid -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden card-3d">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="sticky left-0 z-10 bg-slate-50 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest min-w-[200px] border-r border-slate-100">Pegawai</th>
                        @for($d = 1; $d <= $daysInMonth; $d++)
                            @php $currentDate = $month->copy()->day($d); @endphp
                            <th class="px-3 py-4 text-center min-w-[45px] {{ $currentDate->isWeekend() ? 'bg-red-50 text-red-500' : 'text-slate-400' }}">
                                <p class="text-[9px] font-bold uppercase">{{ $currentDate->translatedFormat('D') }}</p>
                                <p class="text-xs font-extrabold">{{ $d }}</p>
                            </th>
                        @endfor
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($employees as $emp)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="sticky left-0 z-10 bg-white px-6 py-4 border-r border-slate-100 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-400 shrink-0">
                                    {{ substr($emp->full_name, 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-900 truncate">{{ $emp->full_name }}</p>
                                    <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">{{ $emp->is_regu ? 'Regu Jaga' : 'Staf' }}</p>
                                </div>
                            </div>
                        </td>
                        @for($d = 1; $d <= $daysInMonth; $d++)
                            @php 
                                $dateStr = $month->copy()->day($d)->format('Y-m-d');
                                $schedule = $schedules->get($emp->id)?->firstWhere('date', $dateStr);
                                $shiftName = $schedule?->shift?->name;
                                $colorClass = 'bg-slate-50 text-slate-300';
                                if($shiftName == 'Pagi') $colorClass = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                                elseif($shiftName == 'Siang') $colorClass = 'bg-amber-100 text-amber-700 border-amber-200';
                                elseif($shiftName == 'Malam') $colorClass = 'bg-indigo-100 text-indigo-700 border-indigo-200';
                                elseif($shiftName == 'Kantor') $colorClass = 'bg-blue-100 text-blue-700 border-blue-200';
                            @endphp
                            <td class="p-1 border-r border-slate-50">
                                <div class="w-full h-8 rounded-lg border {{ $colorClass }} flex items-center justify-center text-[9px] font-bold uppercase transition-transform hover:scale-110 cursor-pointer" 
                                     title="{{ $emp->full_name }} - {{ $dateStr }}"
                                     onclick="openManualAssign({{ $emp->id }}, '{{ $emp->full_name }}', '{{ $dateStr }}')">
                                    {{ $shiftName ? substr($shiftName, 0, 1) : '-' }}
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
    <div class="flex flex-wrap gap-6 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm card-3d">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest w-full border-b border-slate-50 pb-2 mb-2">Keterangan Shift:</p>
        <div class="flex items-center gap-2">
            <span class="w-4 h-4 rounded bg-emerald-100 border border-emerald-200"></span>
            <span class="text-xs font-bold text-slate-600">P = Pagi (07:30 - 13:00)</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-4 h-4 rounded bg-amber-100 border border-amber-200"></span>
            <span class="text-xs font-bold text-slate-600">S = Siang (13:00 - 19:00)</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-4 h-4 rounded bg-indigo-100 border border-indigo-200"></span>
            <span class="text-xs font-bold text-slate-600">M = Malam (19:00 - 07:30)</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-4 h-4 rounded bg-blue-100 border border-blue-200"></span>
            <span class="text-xs font-bold text-slate-600">K = Kantor (07:30 - 16:00)</span>
        </div>
    </div>
</div>

<!-- Roster Generator Modal -->
<div id="rosterModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-xl rounded-[32px] p-10 shadow-2xl animate-in zoom-in duration-200 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-amber-50 rounded-full -mr-16 -mt-16 opacity-50"></div>
        <div class="relative z-10">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h3 class="text-2xl font-bold text-slate-900 tracking-tight">Generate Roster Otomatis</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Penjadwalan Regu Jaga</p>
                </div>
                <button onclick="document.getElementById('rosterModal').classList.add('hidden')" class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 hover:text-red-500 transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form action="{{ route('admin.schedules.generate') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
                
                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Pilih Regu</label>
                        <select name="squad_id" required class="w-full px-5 py-3 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-bold focus:border-blue-500 outline-none appearance-none cursor-pointer">
                            @foreach($squads as $squad)
                                <option value="{{ $squad->id }}">{{ $squad->name }} ({{ $squad->employees_count ?? $squad->employees->count() }} Anggota)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Tanggal Mulai Pola</label>
                        <input type="date" name="start_date" required value="{{ $month->format('Y-m-01') }}" class="w-full px-5 py-3 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-bold outline-none focus:border-blue-500">
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Urutan Pola Berulang (Misal: P-S-M-L)</label>
                    <div class="grid grid-cols-4 gap-3">
                        @for($i = 0; $i < 4; $i++)
                        <select name="pattern[]" class="w-full px-3 py-3 rounded-xl border border-slate-200 bg-white text-[10px] font-bold outline-none focus:border-blue-500">
                            <option value="">LIBUR</option>
                            @foreach($shifts as $s)
                                <option value="{{ $s->id }}" {{ $i == $loop->index ? 'selected' : '' }}>{{ strtoupper($s->name) }}</option>
                            @endforeach
                        </select>
                        @endfor
                    </div>
                </div>

                <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-bold text-sm uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl btn-3d mt-4">
                    Proses Generate Jadwal
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Manual Assign Modal -->
<div id="manualModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-sm rounded-[32px] p-8 shadow-2xl animate-in zoom-in duration-200">
        <h3 class="text-xl font-bold text-slate-900 mb-2">Ubah Jadwal</h3>
        <p id="manual_info" class="text-xs text-slate-500 font-medium mb-6"></p>
        
        <form id="manualForm" class="space-y-6">
            @csrf
            <input type="hidden" id="manual_emp_id">
            <input type="hidden" id="manual_date">
            <div class="space-y-1.5">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Pilih Shift</label>
                <select id="manual_shift_id" class="w-full px-5 py-3 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-bold focus:border-blue-500 outline-none appearance-none cursor-pointer">
                    <option value="">LIBUR / KOSONG</option>
                    @foreach($shifts as $s)
                        <option value="{{ $s->id }}">{{ strtoupper($s->name) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="button" onclick="submitManual()" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-bold text-sm uppercase tracking-widest hover:bg-blue-700 transition-all shadow-xl">
                Simpan Perubahan
            </button>
            <button type="button" onclick="document.getElementById('manualModal').classList.add('hidden')" class="w-full text-slate-400 font-bold text-[10px] uppercase tracking-widest mt-2">Batal</button>
        </form>
    </div>
</div>

<script>
    function openManualAssign(empId, empName, date) {
        document.getElementById('manual_emp_id').value = empId;
        document.getElementById('manual_date').value = date;
        document.getElementById('manual_info').innerText = `${empName} - ${date}`;
        document.getElementById('manualModal').classList.remove('hidden');
    }

    async function submitManual() {
        const empId = document.getElementById('manual_emp_id').value;
        const shiftId = document.getElementById('manual_shift_id').value;
        const date = document.getElementById('manual_date').value;

        try {
            const response = await fetch("{{ route('admin.schedules.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ employee_id: empId, shift_id: shiftId, date: date })
            });
            if (response.ok) {
                location.reload();
            }
        } catch (error) {
            console.error(error);
        }
    }
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
