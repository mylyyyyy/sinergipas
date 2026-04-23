@extends('layouts.app')

@section('title', 'Jadwal Shift')
@section('header-title', 'Pusat Kendali Operasional')

@section('content')
<div class="space-y-8 page-fade">
    <!-- Header & global Tools -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
        <div class="flex flex-col gap-1">
            <h2 class="text-2xl font-black text-slate-900 italic tracking-tight uppercase">Penjadwalan Terpadu</h2>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.3em]">Regu Jaga • P2U • Piket Individu</p>
        </div>

        <div class="flex flex-wrap items-center gap-4">
            <form action="{{ route('admin.schedules.index') }}" method="GET" class="flex items-center gap-3 bg-white p-2 rounded-2xl border border-slate-200 shadow-sm">
                <input type="month" name="month" value="{{ $monthStr }}" onchange="this.form.submit()" class="px-4 py-2 rounded-xl bg-slate-50 border-none text-sm font-black text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none">
            </form>

            <a href="{{ route('admin.schedules.export', ['month' => $monthStr]) }}" id="globalExportBtn" class="px-6 py-3.5 rounded-2xl bg-slate-900 text-white font-bold text-[10px] uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl btn-3d flex items-center gap-3 no-loader">
                <i data-lucide="file-text" class="w-4 h-4"></i> Ekspor PDF (<span id="exportLabel">Regu</span>)
            </a>
        </div>
    </div>

    <!-- Main Navigation Tabs -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex gap-2 p-1.5 bg-slate-100 rounded-[28px] w-fit border border-slate-200 shadow-inner">
            <button onclick="switchSchedTab('regu')" id="btn-regu" class="sched-tab-btn active px-8 py-4 rounded-[22px] text-[11px] font-black uppercase tracking-widest transition-all duration-300 flex items-center gap-3">
                <i data-lucide="shield" class="w-4 h-4"></i> Regu Jaga
            </button>
            <button onclick="switchSchedTab('p2u')" id="btn-p2u" class="sched-tab-btn px-8 py-4 rounded-[22px] text-[11px] font-black uppercase tracking-widest transition-all duration-300 flex items-center gap-3">
                <i data-lucide="door-closed" class="w-4 h-4"></i> Unit P2U
            </button>
            <button onclick="switchSchedTab('individual')" id="btn-individual" class="sched-tab-btn px-8 py-4 rounded-[22px] text-[11px] font-black uppercase tracking-widest transition-all duration-300 flex items-center gap-3">
                <i data-lucide="user-check" class="w-4 h-4"></i> Piket Individu
            </button>
        </div>

        <div id="tab-actions" class="flex gap-3">
            <!-- Copy Last Month (Simplified) -->
            <form action="{{ route('admin.schedules.copy-last-month') }}" method="POST" class="no-loader">
                @csrf
                <input type="hidden" name="month" value="{{ $monthStr }}">
                <button type="submit" class="px-5 py-3 rounded-xl bg-white border border-slate-200 text-slate-600 font-black text-[9px] uppercase tracking-widest hover:bg-blue-50 hover:text-blue-600 transition-all shadow-sm flex items-center gap-2">
                    <i data-lucide="copy" class="w-3.5 h-3.5"></i> Salin Bulan Lalu
                </button>
            </form>
        </div>
    </div>

    <!-- Tab Content: Regu Jaga -->
    <div id="tab-regu" class="sched-tab-content space-y-6">
        <div class="flex justify-end gap-3 mb-2">
            <form action="{{ route('admin.schedules.clear') }}" method="POST" class="no-loader">
                @csrf @method('DELETE')
                <input type="hidden" name="type" value="regu">
                <input type="hidden" name="month" value="{{ $monthStr }}">
                <button type="button" onclick="confirmClear(this.form, 'Regu Jaga')" class="px-4 py-2 text-red-500 font-bold text-[9px] uppercase tracking-widest hover:underline">Bersihkan Jadwal</button>
            </form>
            <form action="{{ route('admin.schedules.generate') }}" method="POST" class="no-loader">
                @csrf
                <input type="hidden" name="type" value="regu">
                <input type="hidden" name="month" value="{{ $monthStr }}">
                <button type="submit" class="px-5 py-2 bg-slate-900 text-white rounded-xl font-bold text-[9px] uppercase tracking-widest hover:bg-blue-600 shadow-lg">Generate Regu</button>
            </form>
        </div>
        @include('admin.schedules.partials.calendar-grid', ['type' => 'regu', 'squads' => $reguSquads, 'currentSchedules' => $reguSchedules])
    </div>

    <!-- Tab Content: P2U -->
    <div id="tab-p2u" class="sched-tab-content hidden space-y-6">
        <div class="flex justify-end gap-3 mb-2">
            <form action="{{ route('admin.schedules.clear') }}" method="POST" class="no-loader">
                @csrf @method('DELETE')
                <input type="hidden" name="type" value="p2u">
                <input type="hidden" name="month" value="{{ $monthStr }}">
                <button type="button" onclick="confirmClear(this.form, 'Unit P2U')" class="px-4 py-2 text-red-500 font-bold text-[9px] uppercase tracking-widest hover:underline">Bersihkan Jadwal</button>
            </form>
            <form action="{{ route('admin.schedules.generate') }}" method="POST" class="no-loader">
                @csrf
                <input type="hidden" name="type" value="p2u">
                <input type="hidden" name="month" value="{{ $monthStr }}">
                <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-xl font-bold text-[9px] uppercase tracking-widest hover:bg-indigo-700 shadow-lg">Generate P2U</button>
            </form>
        </div>
        @include('admin.schedules.partials.calendar-grid', ['type' => 'p2u', 'squads' => $p2uSquads, 'currentSchedules' => $p2uSchedules])
    </div>

    <!-- Tab Content: Individual -->
    <div id="tab-individual" class="sched-tab-content hidden space-y-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Form Plot -->
            <div class="lg:col-span-4 sticky top-8">
                <div class="bg-slate-900 rounded-[32px] p-8 shadow-2xl relative overflow-hidden border border-white/5 group">
                    <div class="absolute -right-16 -top-16 w-64 h-64 bg-blue-600/10 blur-[80px] transition-all duration-700"></div>
                    
                    <div class="relative z-10">
                        <div class="flex items-center gap-4 mb-8">
                            <div class="w-12 h-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center">
                                <i data-lucide="user-plus" class="w-6 h-6 text-blue-400"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-white uppercase tracking-tight italic">Plot Personel</h3>
                                <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Tugas Khusus & Izin</p>
                            </div>
                        </div>
                        
                        <form action="{{ route('admin.schedules.store-individual') }}" method="POST" class="space-y-6">
                            @csrf
                            <div class="space-y-3">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Nama Pegawai</label>
                                <div class="relative group/input mb-2">
                                    <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-600 group-focus-within/input:text-blue-400"></i>
                                    <input type="text" placeholder="Ketik untuk filter..." onkeyup="filterEmployeeSelect(this.value)" class="w-full pl-10 pr-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-[11px] font-bold outline-none focus:border-blue-500/50 transition-all placeholder:text-slate-700">
                                </div>
                                <div class="relative group/input">
                                    <i data-lucide="users" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 group-focus-within/input:text-blue-400 transition-colors"></i>
                                    <select name="employee_id" id="employeeSelect" required class="w-full pl-11 pr-10 py-4 rounded-xl bg-white/5 border border-white/10 text-white focus:bg-white/10 focus:border-blue-500 outline-none transition-all font-bold text-sm appearance-none cursor-pointer">
                                        <option value="" class="bg-slate-900 text-slate-400">-- Pilih Pegawai --</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->id }}" class="bg-slate-900 text-white employee-option" data-name="{{ strtolower($emp->full_name) }}">{{ strtoupper($emp->full_name) }}</option>
                                        @endforeach
                                    </select>
                                    <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-600 pointer-events-none"></i>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-3">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Tanggal</label>
                                    <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="w-full px-4 py-4 rounded-xl bg-white/5 border border-white/10 text-white focus:bg-white/10 focus:border-blue-500 outline-none transition-all font-bold text-sm [color-scheme:dark]">
                                </div>
                                <div class="space-y-3">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Status</label>
                                    <div class="relative">
                                        <select name="status" id="statusSelect" required onchange="toggleShiftSelect(this.value)" class="w-full pl-4 pr-10 py-4 rounded-xl bg-white/5 border border-white/10 text-white focus:bg-white/10 focus:border-blue-500 outline-none transition-all font-bold text-sm appearance-none cursor-pointer">
                                            <option value="picket" class="bg-slate-900">PIKET</option>
                                            <option value="leave" class="bg-slate-900">CUTI</option>
                                            <option value="sick" class="bg-slate-900">SAKIT</option>
                                            <option value="off" class="bg-slate-900">LIBUR</option>
                                        </select>
                                        <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-600 pointer-events-none"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3 transition-all duration-300" id="shiftContainer">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Shift Kerja</label>
                                <div class="relative">
                                    <select name="shift_id" id="shiftSelect" class="w-full pl-4 pr-10 py-4 rounded-xl bg-white/5 border border-white/10 text-white focus:bg-white/10 focus:border-blue-500 outline-none transition-all font-bold text-sm appearance-none cursor-pointer">
                                        @foreach($shifts as $s)
                                            <option value="{{ $s->id }}" class="bg-slate-900">{{ strtoupper($s->name) }}</option>
                                        @endforeach
                                    </select>
                                    <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-600 pointer-events-none"></i>
                                </div>
                            </div>

                            <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-xl font-black text-[10px] uppercase tracking-[0.2em] hover:bg-blue-500 transition-all shadow-xl flex items-center justify-center gap-3 active:scale-95">
                                <i data-lucide="check-circle" class="w-4 h-4"></i> Simpan Penugasan
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- List Piket -->
            <div class="lg:col-span-8">
                <div class="bg-white rounded-[32px] border border-slate-200 shadow-sm overflow-hidden flex flex-col min-h-[600px] card-3d">
                    <!-- Search Header -->
                    <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row gap-6 justify-between items-center">
                        <div class="flex items-center gap-4">
                            <div class="w-1 h-8 bg-blue-600 rounded-full"></div>
                            <div>
                                <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight italic">Daftar Penugasan</h3>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Sinkronisasi Aktif • {{ $individualSchedulesList->count() }} Personel</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 w-full md:w-auto">
                            <div class="relative flex-1 md:w-64 group">
                                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                                <input type="text" id="individualSearch" placeholder="Cari nama..." onkeyup="filterIndividual()" class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 outline-none transition-all text-xs font-bold text-slate-700">
                            </div>
                            
                            <form action="{{ route('admin.schedules.clear') }}" method="POST" class="no-loader shrink-0">
                                @csrf @method('DELETE')
                                <input type="hidden" name="type" value="individual">
                                <input type="hidden" name="month" value="{{ $monthStr }}">
                                <button type="button" onclick="confirmClear(this.form, 'Piket')" class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all shadow-sm" title="Hapus Semua">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Scrollable List -->
                    <div class="p-6 overflow-y-auto custom-scrollbar" id="individualList">
                        @if($individualSchedulesList->isEmpty())
                            <div class="py-20 flex flex-col items-center justify-center text-center opacity-30">
                                <i data-lucide="calendar-off" class="w-16 h-16 text-slate-300 mb-4"></i>
                                <h4 class="text-sm font-black text-slate-900 uppercase italic">Belum Ada Data</h4>
                            </div>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="individualGrid">
                                @foreach($individualSchedulesList as $is)
                                    <div class="individual-card group p-5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between hover:bg-white hover:border-blue-200 hover:shadow-lg transition-all duration-300" data-name="{{ strtolower($is->employee->full_name) }}">
                                        <div class="flex items-center gap-4 min-w-0">
                                            <div class="w-12 h-12 rounded-xl bg-white border border-slate-200 flex flex-col items-center justify-center shrink-0 shadow-sm group-hover:bg-blue-600 transition-colors duration-500">
                                                <span class="text-sm font-black text-slate-900 group-hover:text-white">{{ date('d', strtotime($is->date)) }}</span>
                                                <span class="text-[8px] font-black text-slate-400 uppercase group-hover:text-blue-100">{{ \Carbon\Carbon::parse($is->date)->translatedFormat('D') }}</span>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-xs font-black text-slate-900 truncate group-hover:text-blue-600 transition-colors">{{ strtoupper($is->employee->full_name) }}</p>
                                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">NIP. {{ $is->employee->nip }}</p>
                                                
                                                <div class="flex items-center gap-2 mt-2">
                                                    @php
                                                        $status = $is->status ?? 'picket';
                                                        $label = $is->shift ? $is->shift->name : 'N/A';
                                                        $colorClass = 'bg-slate-200 text-slate-600 border-slate-300';
                                                        $icon = 'clock';
                                                        
                                                        if ($status === 'leave') {
                                                            $label = 'CUTI';
                                                            $colorClass = 'bg-emerald-100 text-emerald-600 border-emerald-200';
                                                            $icon = 'palm-tree';
                                                        } elseif ($status === 'sick') {
                                                            $label = 'SAKIT';
                                                            $colorClass = 'bg-rose-100 text-rose-600 border-rose-200';
                                                            $icon = 'thermometer';
                                                        } elseif ($status === 'off') {
                                                            $label = 'LIBUR';
                                                            $colorClass = 'bg-slate-100 text-slate-400 border-slate-200';
                                                            $icon = 'home';
                                                        } elseif (str_contains(strtoupper($label), 'PAGI')) {
                                                            $colorClass = 'bg-amber-100 text-amber-600 border-amber-200';
                                                            $icon = 'sun';
                                                        } elseif (str_contains(strtoupper($label), 'SIANG')) {
                                                            $colorClass = 'bg-blue-100 text-blue-600 border-blue-200';
                                                            $icon = 'cloud-sun';
                                                        } elseif (str_contains(strtoupper($label), 'MALAM')) {
                                                            $colorClass = 'bg-slate-800 text-slate-200 border-slate-700';
                                                            $icon = 'moon';
                                                        }
                                                    @endphp
                                                    <span class="flex items-center gap-1 px-2 py-0.5 rounded-lg border {{ $colorClass }} text-[8px] font-black uppercase tracking-widest shadow-sm">
                                                        <i data-lucide="{{ $icon }}" class="w-3 h-3"></i>
                                                        {{ $label }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <form action="{{ route('admin.schedules.destroy-individual', $is->id) }}" method="POST" class="no-loader opacity-0 group-hover:opacity-100 transition-all">
                                            @csrf @method('DELETE')
                                            <button type="button" onclick="confirmDelete(this.form)" class="p-2 text-slate-300 hover:text-red-500 transition-colors">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleShiftSelect(status) {
        const container = document.getElementById('shiftContainer');
        const select = document.getElementById('shiftSelect');
        if (status === 'picket') {
            container.classList.remove('hidden');
            select.setAttribute('required', 'required');
        } else {
            container.classList.add('hidden');
            select.removeAttribute('required');
            select.value = '';
        }
    }

    function filterEmployeeSelect(query) {
        const q = query.toLowerCase();
        document.querySelectorAll('.employee-option').forEach(opt => {
            if (opt.getAttribute('data-name').includes(q)) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
            }
        });
    }

    function filterIndividual() {
        const query = document.getElementById('individualSearch').value.toLowerCase();
        document.querySelectorAll('.individual-card').forEach(card => {
            const name = card.getAttribute('data-name');
            if (name.includes(query)) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }

    function confirmDelete(form) {
        Swal.fire({
            title: 'Hapus Penugasan?',
            text: "Data piket personel ini akan dihapus permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-[32px]' }
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    }
    function switchSchedTab(tab) {
        document.querySelectorAll('.sched-tab-content').forEach(c => c.classList.add('hidden'));
        document.querySelectorAll('.sched-tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('tab-' + tab).classList.remove('hidden');
        document.getElementById('btn-' + tab).classList.add('active');
        
        const url = new URL(window.location);
        url.searchParams.set('tab', tab);
        window.history.pushState({}, '', url);

        // Update Global Export Button
        const exportBtn = document.getElementById('globalExportBtn');
        const exportLabel = document.getElementById('exportLabel');
        const baseUrl = "{{ route('admin.schedules.export') }}";
        const month = "{{ $monthStr }}";
        
        exportBtn.href = `${baseUrl}?month=${month}&type=${tab}`;
        
        if(tab === 'regu') exportLabel.innerText = 'Regu';
        else if(tab === 'p2u') exportLabel.innerText = 'P2U';
        else exportLabel.innerText = 'Individu';
    }

    async function updateSchedule(date, shiftId, squadId, type) {
        try {
            const response = await fetch("{{ route('admin.schedules.store') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ date, shift_id: shiftId, squad_id: squadId || null, type })
            });
            const data = await response.json();
            if (data.success) showToast('Jadwal diperbarui', 'success');
        } catch (error) { showToast('Gagal menyimpan', 'error'); }
    }

    function confirmClear(form, name) {
        Swal.fire({ title: 'Hapus Jadwal?', text: `Seluruh jadwal ${name} akan dihapus!`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#EF4444', confirmButtonText: 'Ya, Hapus Semua', customClass: { popup: 'rounded-[32px]' } }).then((result) => { if (result.isConfirmed) form.submit(); });
    }

    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        switchSchedTab(urlParams.get('tab') || 'regu');
        lucide.createIcons();
    });
</script>

<style>
    .sched-tab-btn.active { background-color: white; color: #0F172A; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
    .sched-tab-btn:not(.active) { color: #64748B; }
    .card-3d { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .card-3d:hover { transform: translateY(-4px); }
    .btn-3d { transition: all 0.2s; border-bottom: 4px solid rgba(0,0,0,0.2); }
    .btn-3d:active { transform: translateY(2px); border-bottom-width: 0; }
</style>
@endsection
