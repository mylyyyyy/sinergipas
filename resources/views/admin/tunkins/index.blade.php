@extends('layouts.app')

@section('title', 'Manajemen Tunjangan Kinerja')
@section('header-title', 'Tunjangan Kinerja (Tunkin)')

@section('content')
<div class="space-y-8 page-fade">
    <!-- Header & Tabs -->
    <div class="bg-white p-8 md:p-10 rounded-[40px] border border-slate-200 shadow-sm card-3d relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-slate-50 rounded-full -mr-32 -mt-32 opacity-40"></div>
        <div class="relative z-10">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-[10px] font-bold uppercase tracking-widest border border-blue-100">
                        <i data-lucide="coins" class="w-3 h-3"></i> Payroll Management
                    </div>
                    <h2 class="text-2xl md:text-3xl font-black text-slate-900 leading-tight">Sinergi Tunkin</h2>
                    <p class="text-sm text-slate-500 font-medium max-w-xl">
                        Kelola besaran tunjangan per kelas jabatan dan monitor rekapitulasi tunkin seluruh pegawai secara real-time.
                    </p>
                </div>
                
                <div class="flex items-center p-1.5 bg-slate-100 rounded-2xl w-fit">
                    <a href="{{ route('admin.tunkins.index', ['tab' => 'nominal']) }}" 
                       class="px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 {{ $tab === 'nominal' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                        Daftar Nominal
                    </a>
                    <a href="{{ route('admin.tunkins.index', ['tab' => 'recap']) }}" 
                       class="px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 {{ $tab === 'recap' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                        Rekap Pegawai
                    </a>
                </div>
            </div>

            @if($tab === 'recap')
            <form action="{{ route('admin.tunkins.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <input type="hidden" name="tab" value="recap">
                <div class="md:col-span-5">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Pencarian Pegawai</label>
                    <div class="relative group">
                        <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="NIP atau Nama..." class="w-full pl-11 pr-4 py-3.5 rounded-2xl border-2 border-slate-50 bg-slate-50 text-sm font-bold text-slate-700 outline-none focus:bg-white focus:border-blue-500 transition-all">
                    </div>
                </div>
                <div class="md:col-span-4">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Periode</label>
                    <div class="relative group">
                        <i data-lucide="calendar" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                        <input type="month" name="month" value="{{ $monthStr }}" class="w-full pl-11 pr-4 py-3.5 rounded-2xl border-2 border-slate-50 bg-slate-50 text-sm font-bold text-slate-700 outline-none focus:bg-white focus:border-blue-500 transition-all">
                    </div>
                </div>
                <div class="md:col-span-3 flex gap-2">
                    <button type="submit" class="flex-1 py-4 rounded-2xl bg-slate-900 text-white font-bold text-[10px] uppercase tracking-widest hover:bg-blue-600 transition-all shadow-lg shadow-slate-200">
                        Filter Data
                    </button>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.tunkins.export.excel', ['month' => $monthStr, 'search' => request('search')]) }}" class="p-4 rounded-2xl bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all shadow-sm border border-emerald-100" title="Export Excel">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                        </a>
                        <a href="{{ route('admin.tunkins.export.pdf', ['month' => $monthStr, 'search' => request('search')]) }}" class="p-4 rounded-2xl bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all shadow-sm border border-red-100" title="Export PDF">
                            <i data-lucide="file-text" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            </form>
            @endif
        </div>
    </div>

    @if($tab === 'nominal')
        <!-- Nominal List Tab -->
        <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden card-3d">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">No</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">Kelas Jabatan</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">Pegawai Terdaftar</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100 text-right">Tunjangan Kinerja</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($tunkins as $index => $tunkin)
                        <tr class="hover:bg-slate-50/30 transition-colors group">
                            <td class="px-8 py-6 text-xs font-bold text-slate-400">#{{ $index + 1 }}</td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700 font-black text-sm group-hover:bg-blue-600 group-hover:text-white transition-all">
                                        {{ $tunkin->grade }}
                                    </div>
                                    <span class="text-sm font-bold text-slate-700">Kelas {{ $tunkin->grade }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="users" class="w-4 h-4 text-slate-400"></i>
                                    <span class="text-sm font-bold text-slate-600">{{ $tunkin->employees_count }} Orang</span>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-right font-black text-slate-900" id="nominal-display-{{ $tunkin->id }}">
                                Rp {{ number_format($tunkin->nominal, 0, ',', '.') }}
                            </td>
                            <td class="px-8 py-6 text-center">
                                <button onclick="editTunkin({{ $tunkin->id }}, {{ $tunkin->grade }}, {{ $tunkin->nominal }})" class="p-2.5 rounded-xl bg-slate-50 text-slate-400 hover:bg-blue-50 hover:text-blue-600 transition-all border border-transparent hover:border-blue-100">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <!-- Employee Recap Tab -->
        <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden card-3d">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">Pegawai</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100 text-center">Kelas</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100 text-right">Uang Makan</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100 text-center">% Potong</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100 text-right">Potongan (Rp)</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100 text-right">Total Terima</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100 text-center">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($employees as $emp)
                        <tr class="hover:bg-slate-50/30 transition-all group {{ $emp->violation_note ? 'bg-red-50/30' : '' }}">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0">
                                        <img src="{{ $emp->photo }}" class="w-full h-full object-cover" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($emp->full_name) }}&background=F1F5F9&color=64748B'">
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-900 leading-none mb-1">{{ $emp->full_name }}</p>
                                        <p class="text-[10px] font-bold text-slate-400 font-mono tracking-tight">NIP. {{ $emp->nip }}</p>
                                        @if($emp->violation_note)
                                            <p class="text-[9px] font-black text-red-500 uppercase mt-1 animate-pulse">{{ $emp->violation_note }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <span class="px-3 py-1 bg-slate-100 text-slate-700 text-[10px] font-black rounded-lg border border-slate-200">
                                    {{ $emp->tunkin->grade ?? '-' }}
                                </span>
                            </td>
                            <td class="px-8 py-6 text-right font-bold text-slate-600">
                                <span class="text-[10px] text-slate-400 mr-1">({{ $emp->total_attendance }}d)</span>
                                Rp {{ number_format($emp->meal_allowance, 0, ',', '.') }}
                            </td>
                            <td class="px-8 py-6 text-center">
                                <span class="text-xs font-black {{ $emp->deduction_percentage > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                    {{ number_format($emp->deduction_percentage, 1) }}%
                                </span>
                            </td>
                            <td class="px-8 py-6 text-right font-bold text-red-600">
                                Rp {{ number_format($emp->potongan, 0, ',', '.') }}
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="text-base font-black text-slate-900">
                                    Rp {{ number_format($emp->grand_total, 0, ',', '.') }}
                                </div>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <a href="{{ route('admin.tunkins.employee', ['employee' => $emp->id, 'month' => $monthStr]) }}" class="inline-flex p-2.5 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-200 hover:shadow-sm transition-all active:scale-95">
                                    <i data-lucide="external-link" class="w-4 h-4"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-8 py-20 text-center">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i data-lucide="user-minus" class="w-8 h-8 text-slate-300"></i>
                                </div>
                                <h4 class="font-bold text-slate-900">Pegawai Tidak Ditemukan</h4>
                                <p class="text-xs text-slate-400 uppercase tracking-widest mt-1">Coba sesuaikan kata kunci atau filter anda</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

<!-- Modal Edit (Hanya untuk Tab Nominal) -->
@if($tab === 'nominal')
<div id="editModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/60 backdrop-blur-md px-4">
    <div class="bg-white rounded-[40px] p-10 shadow-2xl max-w-md w-full animate-in zoom-in duration-300">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="coins" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-900">Edit Besaran Tunkin</h3>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-widest" id="modalGradeLabel">Kelas Jabatan</p>
                </div>
            </div>
            <button onclick="closeModal()" class="w-10 h-10 rounded-xl hover:bg-slate-100 flex items-center justify-center transition-colors">
                <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
            </button>
        </div>

        <form id="editForm" onsubmit="handleUpdate(event)" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_id">
            <div class="space-y-3">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Besaran Nominal (Rp)</label>
                <div class="relative group">
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-black text-sm">Rp</div>
                    <input type="number" id="edit_nominal" required step="0.01" min="0" class="w-full pl-12 pr-6 py-4 rounded-2xl border-2 border-slate-50 bg-slate-50 text-sm font-black text-slate-700 outline-none focus:bg-white focus:border-blue-500 transition-all">
                </div>
            </div>
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeModal()" class="flex-1 py-4 rounded-2xl bg-slate-100 text-slate-500 font-bold text-xs uppercase tracking-widest hover:bg-slate-200">Batal</button>
                <button type="submit" class="flex-2 py-4 px-8 rounded-2xl bg-slate-900 text-white font-bold text-xs uppercase tracking-widest hover:bg-blue-600 shadow-lg active:scale-95">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editTunkin(id, grade, nominal) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nominal').value = nominal;
        document.getElementById('modalGradeLabel').innerText = 'Kelas Jabatan ' + grade;
        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('editModal').classList.add('flex');
    }

    function closeModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.getElementById('editModal').classList.remove('flex');
    }

    async function handleUpdate(event) {
        event.preventDefault();
        const id = document.getElementById('edit_id').value;
        const nominal = document.getElementById('edit_nominal').value;
        const submitBtn = event.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerText;

        submitBtn.disabled = true;
        submitBtn.innerText = 'Menyimpan...';

        try {
            const response = await fetch(`/admin/tunkins/${id}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ _method: 'PUT', nominal: nominal })
            });
            const result = await response.json();
            if (result.success) {
                const display = document.getElementById(`nominal-display-${id}`);
                display.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(nominal);
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: result.message, timer: 1500, showConfirmButton: false, customClass: { popup: 'rounded-[32px]' } });
                closeModal();
            } else { throw new Error(result.message); }
        } catch (error) {
            Swal.fire({ icon: 'error', title: 'Error!', text: error.message, customClass: { popup: 'rounded-[32px]' } });
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerText = originalText;
        }
    }
</script>
@endif
@endsection
