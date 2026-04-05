@extends('layouts.app')

@section('title', 'Data Pegawai')
@section('header-title', 'Database Pegawai')

@section('content')
<div class="space-y-8 page-fade">
    <!-- Hero Section -->
    <div class="relative overflow-hidden rounded-3xl bg-slate-900 px-8 py-10 text-white shadow-xl card-3d">
        <div class="absolute -left-10 top-8 h-44 w-44 rounded-full bg-blue-500/10 blur-3xl"></div>
        <div class="absolute right-0 top-0 h-64 w-64 rounded-full bg-amber-500/5 blur-3xl"></div>
        
        <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-3xl font-bold tracking-tight">Manajemen Pegawai</h2>
                <p class="mt-2 text-slate-400 font-medium max-w-xl">Kelola informasi profil, akses, dan unit kerja seluruh pegawai Lapas Jombang secara terpusat.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <div class="flex bg-white/5 p-1 rounded-2xl border border-white/10 backdrop-blur-sm">
                    <button type="button" onclick="document.getElementById('importModal').classList.remove('hidden')" class="px-5 py-2.5 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-white/10 transition-all flex items-center gap-2">
                        <i data-lucide="file-up" class="w-4 h-4 text-amber-400"></i> Impor Excel
                    </button>
                    <a href="{{ route('employees.export.excel') }}" class="px-5 py-2.5 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-white/10 transition-all flex items-center gap-2 no-loader">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4 text-green-400"></i> Ekspor
                    </a>
                </div>
                <button type="button" onclick="document.getElementById('addModal').classList.remove('hidden')" class="px-6 py-2.5 rounded-xl bg-blue-600 text-white text-[10px] font-bold uppercase tracking-widest hover:bg-blue-700 transition-all flex items-center gap-2 shadow-lg shadow-blue-900/20 btn-3d">
                    <i data-lucide="user-plus" class="w-4 h-4"></i> Registrasi Baru
                </button>
            </div>
        </div>
    </div>

    <!-- Filters & Bulk Actions -->
    <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="w-full md:flex-1 bg-white p-2 rounded-2xl border border-slate-200 shadow-sm flex flex-col lg:flex-row gap-2">
            <form action="{{ route('employees.index') }}" method="GET" class="relative flex-1 group">
                <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIP..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-transparent bg-slate-50 text-sm font-semibold outline-none focus:bg-white focus:border-blue-500 transition-all">
            </form>
            
            <form action="{{ route('employees.index') }}" method="GET" class="w-full lg:w-64">
                <select name="work_unit_id" onchange="this.form.submit()" class="w-full px-4 py-2.5 rounded-xl border border-transparent bg-slate-50 text-sm font-semibold text-slate-700 outline-none focus:bg-white focus:border-blue-500 appearance-none cursor-pointer">
                    <option value="">Seluruh Unit Kerja</option>
                    @foreach($workUnits as $unit)
                        <option value="{{ $unit->id }}" {{ request('work_unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div id="bulkActionBar" class="hidden flex items-center gap-3 bg-amber-50 px-4 py-2 rounded-2xl border border-amber-100 animate-in slide-in-from-right-4">
            <p class="text-[10px] font-bold text-amber-700 uppercase tracking-widest"><span id="selectedCount">0</span> Pegawai Terpilih</p>
            <button type="button" onclick="confirmBulkDelete()" class="p-2 bg-white text-red-500 rounded-xl hover:bg-red-500 hover:text-white transition-all shadow-sm border border-amber-200">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden card-3d">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-6 py-4 w-10">
                            <input type="checkbox" id="selectAll" class="w-5 h-5 rounded-lg border-slate-300 text-blue-600 focus:ring-0 cursor-pointer">
                        </th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Identitas Pegawai</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Unit & Jabatan</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Status Akses</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($employees as $employee)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-6 py-4">
                            <input type="checkbox" name="ids[]" value="{{ $employee->id }}" class="emp-checkbox w-5 h-5 rounded-lg border-slate-300 text-blue-600 focus:ring-0 cursor-pointer">
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center text-slate-400 font-bold group-hover:scale-105 transition-transform shrink-0">
                                    @if($employee->photo)
                                        <img src="{{ $employee->photo }}" class="w-full h-full object-cover">
                                    @else
                                        <i data-lucide="user" class="w-6 h-6 opacity-30"></i>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors truncate">{{ $employee->full_name }}</p>
                                    <p class="text-[10px] font-mono font-bold text-slate-400 mt-0.5 tracking-tight">NIP. {{ $employee->nip }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-3 py-1 bg-blue-50 text-blue-600 text-[9px] font-bold uppercase rounded-lg border border-blue-100 inline-block mb-1">{{ $employee->work_unit->name ?? '-' }}</span>
                            <p class="text-[10px] font-semibold text-slate-500 truncate max-w-[150px] mx-auto">{{ $employee->position }}</p>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex flex-col items-center">
                                <span class="text-[10px] font-bold text-slate-700">{{ $employee->user->email }}</span>
                                <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-1">Role: {{ $employee->user->role }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex justify-center items-center gap-2">
                                <button onclick="openEditModal({{ json_encode($employee) }}, '{{ $employee->user->email }}')" class="w-9 h-9 rounded-xl border border-slate-200 text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all btn-3d bg-white shadow-sm flex items-center justify-center">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </button>
                                <button type="button" onclick="confirmDelete({{ $employee->id }})" class="w-9 h-9 rounded-xl border border-slate-200 text-slate-400 hover:text-red-600 hover:bg-red-50 transition-all btn-3d bg-white shadow-sm flex items-center justify-center">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                                <form id="deleteForm-{{ $employee->id }}" action="{{ route('employees.destroy', $employee->id) }}" method="POST" class="hidden no-loader">
                                    @csrf @method('DELETE')
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-20 text-center">
                            <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-dashed border-slate-200">
                                <i data-lucide="users" class="w-8 h-8 text-slate-300"></i>
                            </div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest italic">Belum ada data pegawai</p>
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

<!-- Modal Add -->
<div id="addModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-xl rounded-[32px] p-10 shadow-2xl animate-in zoom-in duration-200 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-full -mr-16 -mt-16 opacity-50"></div>
        <div class="relative z-10">
            <div class="flex justify-between items-center mb-10">
                <div>
                    <h3 class="text-2xl font-bold text-slate-900 tracking-tight">Registrasi Pegawai</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Penambahan Entitas Kepegawaian Baru</p>
                </div>
                <button onclick="document.getElementById('addModal').classList.add('hidden')" class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 hover:text-red-500 transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <form action="{{ route('employees.store') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Nama Lengkap</label>
                        <input type="text" name="full_name" required placeholder="Nama sesuai SK..." class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none transition-all">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">NIP (Username)</label>
                        <input type="text" name="nip" required placeholder="Nomor Induk Pegawai..." class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none transition-all">
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Email Kedinasan</label>
                    <input type="email" name="email" required placeholder="pegawai@sinergipas.id" class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none transition-all">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Jabatan Struktural</label>
                        <select name="position_id" required class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none appearance-none cursor-pointer">
                            @foreach($positions as $pos)
                                <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Unit Kerja</label>
                        <select name="work_unit_id" required class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none appearance-none cursor-pointer">
                            @foreach($workUnits as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Kata Sandi Awal</label>
                    <input type="password" name="password" required placeholder="Minimal 8 karakter..." class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none transition-all">
                </div>
                <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-bold text-sm uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl btn-3d mt-4">
                    Simpan Data Pegawai
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-xl rounded-[32px] p-10 shadow-2xl animate-in zoom-in duration-200 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-amber-50 rounded-full -mr-16 -mt-16 opacity-50"></div>
        <div class="relative z-10">
            <div class="flex justify-between items-center mb-10">
                <div>
                    <h3 class="text-2xl font-bold text-slate-900 italic tracking-tight">Edit Profil Pegawai</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Pembaruan Informasi Entitas</p>
                </div>
                <button onclick="document.getElementById('editModal').classList.add('hidden')" class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 hover:text-red-500 transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <form id="editForm" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Nama Lengkap</label>
                        <input type="text" name="full_name" id="edit_full_name" required class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none transition-all">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">NIP Pegawai</label>
                        <input type="text" name="nip" id="edit_nip" required class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none transition-all">
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Email</label>
                    <input type="email" name="email" id="edit_email" required class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none transition-all">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Jabatan</label>
                        <select name="position_id" id="edit_position_id" required class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none appearance-none cursor-pointer">
                            @foreach($positions as $pos)
                                <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Unit Kerja</label>
                        <select name="work_unit_id" id="edit_work_unit_id" required class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none appearance-none cursor-pointer">
                            @foreach($workUnits as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="p-4 bg-blue-50 rounded-2xl border border-blue-100">
                    <p class="text-[9px] font-bold text-blue-600 uppercase tracking-widest mb-1 flex items-center gap-2">
                        <i data-lucide="info" class="w-3.5 h-3.5"></i> Informasi Keamanan
                    </p>
                    <p class="text-[10px] text-blue-500 font-medium italic">Biarkan kosong jika tidak ingin mengubah kata sandi pegawai.</p>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Password Baru (Opsional)</label>
                    <input type="password" name="password" placeholder="Minimal 8 karakter..." class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none transition-all">
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-bold text-sm uppercase tracking-widest hover:bg-blue-700 transition-all shadow-xl btn-3d">
                    Update Informasi Pegawai
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div id="importModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-md rounded-[32px] p-10 shadow-2xl animate-in zoom-in duration-200">
        <div class="flex justify-between items-center mb-8">
            <h3 class="text-xl font-bold text-slate-900">Import Batch Pegawai</h3>
            <button onclick="document.getElementById('importModal').classList.add('hidden')" class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 hover:text-red-500">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        
        <div class="mb-8 p-6 bg-slate-900 rounded-3xl border border-white/5 text-white shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 opacity-10 group-hover:scale-110 transition-transform duration-700">
                <i data-lucide="file-spreadsheet" class="w-24 h-24 text-amber-400"></i>
            </div>
            <h4 class="text-[10px] font-bold text-amber-400 uppercase tracking-widest mb-4 flex items-center gap-2 relative z-10">
                <i data-lucide="help-circle" class="w-4 h-4"></i> Struktur File Excel
            </h4>
            <div class="space-y-3 relative z-10">
                <p class="text-[11px] font-medium text-slate-300">Gunakan format kolom berikut pada baris pertama:</p>
                <div class="bg-white/5 p-3 rounded-xl border border-white/10 font-mono text-[10px] text-amber-200 text-center select-all">
                    nip, full_name, position, work_unit, email
                </div>
                <ul class="text-[9px] text-slate-400 space-y-1 pl-4 list-disc">
                    <li>NIP unik untuk setiap pegawai.</li>
                    <li>Unit & Jabatan harus sesuai data master.</li>
                    <li>Password default diatur sama dengan NIP.</li>
                </ul>
            </div>
        </div>

        <form action="{{ route('employees.import.excel') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="p-8 rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 text-center group hover:bg-white hover:border-blue-400 transition-all cursor-pointer relative">
                <input type="file" name="file" required class="absolute inset-0 opacity-0 cursor-pointer" onchange="updateFileName(this)">
                <i data-lucide="upload-cloud" class="w-10 h-10 text-slate-300 mx-auto mb-3 group-hover:text-blue-500 group-hover:scale-110 transition-all"></i>
                <p id="fileName" class="text-xs font-bold text-slate-500 group-hover:text-blue-600">Klik atau seret file .xlsx</p>
            </div>
            <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl btn-3d">
                Proses Impor Data
            </button>
        </form>
    </div>
</div>

<form id="bulkDeleteForm" action="{{ route('employees.bulk-destroy') }}" method="POST" class="hidden no-loader">
    @csrf @method('DELETE')
</form>

<script>
    const selectAll = document.getElementById('selectAll');
    const checkboxes = Array.from(document.querySelectorAll('.emp-checkbox'));
    const bulkActionBar = document.getElementById('bulkActionBar');
    const selectedCount = document.getElementById('selectedCount');

    function syncSelection() {
        const checked = checkboxes.filter(c => c.checked);
        selectedCount.textContent = checked.length;
        bulkActionBar.classList.toggle('hidden', checked.length === 0);
        selectAll.checked = checkboxes.length > 0 && checked.length === checkboxes.length;
        selectAll.indeterminate = checked.length > 0 && checked.length < checkboxes.length;
    }

    selectAll.addEventListener('change', function() {
        checkboxes.forEach(c => c.checked = this.checked);
        syncSelection();
    });

    checkboxes.forEach(c => c.addEventListener('change', syncSelection));

    function confirmBulkDelete() {
        Swal.fire({
            title: 'Hapus Massal?',
            text: `${checkboxes.filter(c => c.checked).length} data pegawai akan dimusnahkan secara permanen.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            confirmButtonText: 'Ya, Hapus Semua!',
            customClass: { popup: 'rounded-3xl' }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('bulkDeleteForm');
                form.querySelectorAll('input[name="ids[]"]').forEach(i => i.remove());
                checkboxes.filter(c => c.checked).forEach(c => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = c.value;
                    form.appendChild(input);
                });
                form.submit();
            }
        });
    }

    function openEditModal(employee, email) {
        const modal = document.getElementById('editModal');
        const form = document.getElementById('editForm');
        form.action = `/employees/${employee.id}`;
        document.getElementById('edit_full_name').value = employee.full_name;
        document.getElementById('edit_nip').value = employee.nip;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_position_id').value = employee.position_id;
        document.getElementById('edit_work_unit_id').value = employee.work_unit_id;
        modal.classList.remove('hidden');
        lucide.createIcons();
    }

    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus Pegawai?',
            text: "Seluruh data dan akses pengguna ini akan dimusnahkan.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            confirmButtonText: 'Ya, Hapus!',
            customClass: { popup: 'rounded-3xl' }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteForm-' + id).submit();
            }
        });
    }

    function updateFileName(input) {
        if (input.files && input.files[0]) {
            document.getElementById('fileName').textContent = input.files[0].name;
            document.getElementById('fileName').classList.add('text-blue-600');
        }
    }
</script>

@if(session('success'))
<script>
    Swal.fire({ icon: 'success', title: 'Berhasil', text: "{{ session('success') }}", confirmButtonColor: '#0F172A', customClass: { popup: 'rounded-2xl' } });
</script>
@endif
@endsection
