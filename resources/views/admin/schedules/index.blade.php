@extends('layouts.app')

@section('title', 'Jadwal Shift')
@section('header-title', 'Kelola Jadwal Regu')

@section('content')
<div class="space-y-8 page-fade">
    <!-- Header & Tools -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
        <div class="flex flex-col gap-1">
            <h2 class="text-2xl font-black text-slate-900 italic tracking-tight uppercase">Penjadwalan Bulanan</h2>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.3em]">Manajemen Rotasi Regu Jaga A-E</p>
        </div>

        <div class="flex flex-wrap items-center gap-4">
            <form action="{{ route('admin.schedules.index') }}" method="GET" class="flex items-center gap-3 bg-white p-2 rounded-2xl border border-slate-200 shadow-sm">
                <input type="month" name="month" value="{{ $monthStr }}" onchange="this.form.submit()" class="px-4 py-2 rounded-xl bg-slate-50 border-none text-sm font-black text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none">
            </form>

            <a href="{{ route('admin.schedules.export', ['month' => $monthStr]) }}" class="px-6 py-3.5 rounded-2xl bg-slate-900 text-white font-bold text-[10px] uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl btn-3d flex items-center gap-3 no-loader">
                <i data-lucide="file-text" class="w-4 h-4"></i> Ekspor PDF
            </a>
        </div>
    </div>

    <!-- Schedule Grid -->
    <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden card-3d">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse min-w-[1200px]">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest sticky left-0 bg-slate-50 z-10 border-r border-slate-100">Sesi / Tanggal</th>
                        @for($d = 1; $d <= $daysInMonth; $d++)
                            <th class="px-4 py-6 text-center border-r border-slate-100 min-w-[80px] {{ $month->copy()->day($d)->isWeekend() ? 'bg-red-50/30' : '' }}">
                                <span class="block text-xs font-black text-slate-900">{{ $d }}</span>
                                <span class="text-[8px] font-bold text-slate-400 uppercase tracking-tighter">{{ $month->copy()->day($d)->translatedFormat('D') }}</span>
                            </th>
                        @endfor
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($shifts as $shift)
                    <tr class="group hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-5 sticky left-0 bg-white group-hover:bg-slate-50 z-10 border-r border-slate-100 shadow-[5px_0_15px_rgba(0,0,0,0.02)]">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center 
                                    {{ $shift->name === 'Pagi' ? 'bg-amber-50 text-amber-600' : '' }}
                                    {{ $shift->name === 'Siang' ? 'bg-blue-50 text-blue-600' : '' }}
                                    {{ $shift->name === 'Malam' ? 'bg-slate-900 text-white' : '' }}
                                    shadow-sm">
                                    <i data-lucide="{{ $shift->name === 'Pagi' ? 'sun' : ($shift->name === 'Siang' ? 'cloud-sun' : 'moon') }}" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <span class="text-sm font-black text-slate-900 uppercase tracking-tight">{{ $shift->name }}</span>
                                    <p class="text-[8px] font-bold text-slate-400 uppercase leading-none mt-0.5">{{ substr($shift->start_time, 0, 5) }} - {{ substr($shift->end_time, 0, 5) }}</p>
                                </div>
                            </div>
                        </td>
                        @for($d = 1; $d <= $daysInMonth; $d++)
                            @php 
                                $dateStr = $month->copy()->day($d)->format('Y-m-d');
                                $currentSchedule = $schedules->get($dateStr . '_' . $shift->id)?->first();
                            @endphp
                            <td class="p-2 border-r border-slate-50 text-center {{ $month->copy()->day($d)->isWeekend() ? 'bg-red-50/10' : '' }}">
                                <select 
                                    onchange="updateSquadSchedule('{{ $dateStr }}', {{ $shift->id }}, this.value)"
                                    class="w-full px-2 py-2 rounded-xl border-2 border-transparent bg-slate-50 text-xs font-black text-center appearance-none cursor-pointer hover:bg-white hover:border-blue-200 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 outline-none transition-all
                                    {{ $currentSchedule ? 'text-blue-600 bg-blue-50/50 border-blue-100' : 'text-slate-400' }}">
                                    <option value="">-</option>
                                    @foreach($squads as $squad)
                                        <option value="{{ $squad->id }}" {{ $currentSchedule && $currentSchedule->squad_id == $squad->id ? 'selected' : '' }}>
                                            Regu {{ $squad->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        @endfor
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Legend & Info -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-12">
        <!-- Individual Scheduling Form -->
        <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm p-8 card-3d">
            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight italic mb-6 flex items-center gap-3">
                <i data-lucide="user-plus" class="w-6 h-6 text-blue-600"></i> Penjadwalan Individu / Piket
            </h3>
            
            <form action="{{ route('admin.schedules.store-individual') }}" method="POST" class="space-y-6">
                @csrf
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Pilih Pegawai (Staf/Regu)</label>
                    <select name="employee_id" required class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-2 border-transparent focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 outline-none transition-all font-bold text-sm text-slate-700">
                        <option value="">-- Pilih Pegawai --</option>
                        @foreach($employees as $emp)
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
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Pilih Shift</label>
                        <select name="shift_id" required class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-2 border-transparent focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 outline-none transition-all font-bold text-sm text-slate-700">
                            @foreach($shifts as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ substr($s->start_time, 0, 5) }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-bold text-sm uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl btn-3d">
                    Simpan Jadwal Individu
                </button>
            </form>
        </div>

        <!-- List of Individual Schedules -->
        <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm p-8 card-3d overflow-hidden">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight italic">Jadwal Khusus Bulan Ini</h3>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="toggleSelectAllIndividual()" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest hover:text-blue-600 transition-colors">
                        Pilih Semua
                    </button>
                    <button type="button" onclick="confirmBulkDeleteIndividual()" id="btnBulkDeleteIndividual" class="hidden px-4 py-2 bg-red-50 text-red-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-600 hover:text-white transition-all">
                        Hapus Terpilih (<span id="selectedIndividualCount">0</span>)
                    </button>
                    <button type="button" onclick="confirmDeleteAllIndividual()" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-600 transition-all">
                        Hapus Semua
                    </button>
                </div>
            </div>

            <div class="max-h-[400px] overflow-y-auto custom-scrollbar pr-2">
                <div class="space-y-4">
                    @forelse($individualSchedules as $is)
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex justify-between items-center group hover:bg-white hover:border-blue-200 transition-all">
                            <div class="flex items-center gap-4">
                                <input type="checkbox" name="selected_schedules[]" value="{{ $is->id }}" onchange="updateIndividualSelection()" class="individual-checkbox w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-0 cursor-pointer">
                                <div>
                                    <p class="text-sm font-bold text-slate-900">{{ $is->employee->full_name }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="px-2 py-0.5 rounded-lg bg-blue-100 text-blue-700 text-[8px] font-black uppercase tracking-widest">{{ $is->shift->name }}</span>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter italic">{{ \Carbon\Carbon::parse($is->date)->translatedFormat('d F Y') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="w-8 h-8 rounded-xl bg-white text-slate-300 flex items-center justify-center group-hover:text-blue-500 transition-colors">
                                <i data-lucide="calendar-check" class="w-4 h-4"></i>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <i data-lucide="calendar-range" class="w-12 h-12 text-slate-200 mx-auto mb-3"></i>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Belum ada jadwal khusus</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Bulk Delete Form -->
    <form id="bulkDeleteForm" action="{{ route('admin.schedules.bulk-delete') }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
        <input type="hidden" name="month" value="{{ $monthStr }}">
        <input type="hidden" name="all" id="deleteAllInput" value="0">
        <div id="bulkDeleteIdsContainer"></div>
    </form>

    <!-- Info Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
        <div class="bg-white p-6 rounded-[32px] border border-slate-200 shadow-sm flex items-center gap-4 card-3d">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <i data-lucide="info" class="w-6 h-6"></i>
            </div>
            <div>
                <h4 class="text-xs font-black text-slate-900 uppercase">Simpan Otomatis</h4>
                <p class="text-[10px] text-slate-400 font-bold uppercase mt-1">Setiap perubahan regu akan langsung tersimpan ke sistem.</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-[32px] border border-slate-200 shadow-sm flex items-center gap-4 card-3d">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <div>
                <h4 class="text-xs font-black text-slate-900 uppercase">Integrasi Personel</h4>
                <p class="text-[10px] text-slate-400 font-bold uppercase mt-1">Pegawai dalam regu otomatis mengikuti jadwal ini.</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-[32px] border border-slate-200 shadow-sm flex items-center gap-4 card-3d">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <i data-lucide="file-check" class="w-6 h-6"></i>
            </div>
            <div>
                <h4 class="text-xs font-black text-slate-900 uppercase">Validasi Absensi</h4>
                <p class="text-[10px] text-slate-400 font-bold uppercase mt-1">Jadwal ini digunakan sebagai acuan keterlambatan harian.</p>
            </div>
        </div>
    </div>
</div>

<script>
    async function updateSquadSchedule(date, shiftId, squadId) {
        try {
            const response = await fetch("{{ route('admin.schedules.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    date: date,
                    shift_id: shiftId,
                    squad_id: squadId || null
                })
            });

            const data = await response.json();
            if (data.success) {
                showToast('Jadwal regu berhasil diperbarui', 'success');
            }
        } catch (error) {
            console.error('Update failed:', error);
            showToast('Gagal memperbarui jadwal', 'error');
        }
    }

    function toggleSelectAllIndividual() {
        const checkboxes = document.querySelectorAll('.individual-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
        updateIndividualSelection();
    }

    function updateIndividualSelection() {
        const selected = document.querySelectorAll('.individual-checkbox:checked');
        const btn = document.getElementById('btnBulkDeleteIndividual');
        const countSpan = document.getElementById('selectedIndividualCount');
        
        if (selected.length > 0) {
            btn.classList.remove('hidden');
            countSpan.textContent = selected.length;
        } else {
            btn.classList.add('hidden');
        }
    }

    function confirmBulkDeleteIndividual() {
        const selected = document.querySelectorAll('.individual-checkbox:checked');
        const container = document.getElementById('bulkDeleteIdsContainer');
        container.innerHTML = '';
        
        selected.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = cb.value;
            container.appendChild(input);
        });

        document.getElementById('deleteAllInput').value = '0';

        Swal.fire({
            title: 'Hapus Jadwal Terpilih?',
            text: `Anda akan menghapus ${selected.length} jadwal khusus yang dipilih.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#1e293b',
            cancelButtonColor: '#f1f5f9',
            confirmButtonText: 'YA, HAPUS',
            cancelButtonText: 'BATAL',
            customClass: {
                confirmButton: 'font-bold rounded-xl px-6 py-3',
                cancelButton: 'font-bold rounded-xl px-6 py-3 text-slate-500'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('bulkDeleteForm').submit();
            }
        });
    }

    function confirmDeleteAllIndividual() {
        document.getElementById('deleteAllInput').value = '1';
        document.getElementById('bulkDeleteIdsContainer').innerHTML = '';

        Swal.fire({
            title: 'Hapus SEMUA Jadwal?',
            text: "Seluruh jadwal khusus untuk bulan ini akan dihapus secara permanen!",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#f1f5f9',
            confirmButtonText: 'YA, HAPUS SEMUA',
            cancelButtonText: 'BATAL',
            customClass: {
                confirmButton: 'font-bold rounded-xl px-6 py-3',
                cancelButton: 'font-bold rounded-xl px-6 py-3 text-slate-500'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('bulkDeleteForm').submit();
            }
        });
    }
</script>
@endsection
