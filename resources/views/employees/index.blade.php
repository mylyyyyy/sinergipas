@extends('layouts.app')

@section('title', 'Data Pegawai')
@section('header-title', 'Database Pegawai')

@section('content')
<div class="space-y-8 page-fade">
    <!-- Hero Section -->
    <div class="relative overflow-hidden rounded-3xl bg-slate-900 px-8 py-10 text-white shadow-xl card-3d">
        <div class="absolute -left-10 top-8 h-44 w-44 rounded-full bg-blue-500/10 blur-3xl"></div>
        <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-3xl font-bold tracking-tight">Manajemen Pegawai</h2>
                <p class="mt-2 text-slate-400 font-medium">Kelola informasi profil dan akses kepegawaian Lapas Jombang.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="button" onclick="document.getElementById('importModal').classList.remove('hidden')" class="px-5 py-2.5 rounded-xl bg-white/10 border border-white/10 text-[10px] font-bold uppercase tracking-widest hover:bg-white/20 transition-all flex items-center gap-2 btn-3d">
                    <i data-lucide="file-up" class="w-4 h-4"></i> Impor Excel
                </button>
                <button type="button" onclick="document.getElementById('addModal').classList.remove('hidden')" class="px-5 py-2.5 rounded-xl bg-blue-600 text-white text-[10px] font-bold uppercase tracking-widest hover:bg-blue-700 transition-all flex items-center gap-2 shadow-lg shadow-blue-900/20 btn-3d">
                    <i data-lucide="user-plus" class="w-4 h-4"></i> Registrasi
                </button>
            </div>
        </div>
    </div>

    <!-- Filters & Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-3 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row gap-4">
            <form action="{{ route('employees.index') }}" method="GET" class="relative flex-1 group">
                <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIP..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-100 bg-slate-50 text-sm font-semibold outline-none focus:border-blue-500 transition-all">
            </form>
            
            <form action="{{ route('employees.index') }}" method="GET" class="w-full md:w-64">
                <select name="work_unit_id" onchange="this.form.submit()" class="w-full px-4 py-2.5 rounded-xl border border-slate-100 bg-slate-50 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 appearance-none cursor-pointer">
                    <option value="">Semua Unit Kerja</option>
                    @foreach($workUnits as $unit)
                        <option value="{{ $unit->id }}" {{ request('work_unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between px-6 hover-lift">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Data</p>
                <p class="text-xl font-bold text-slate-900">{{ $employees->total() }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pegawai</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">NIP</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Jabatan & Unit</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($employees as $employee)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center text-slate-400 font-bold group-hover:scale-105 transition-transform">
                                    @if($employee->photo)
                                        <img src="{{ $employee->photo }}" class="w-full h-full object-cover">
                                    @else
                                        <i data-lucide="user" class="w-6 h-6 opacity-30"></i>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors">{{ $employee->full_name }}</p>
                                    <p class="text-[10px] font-medium text-slate-400">{{ $employee->user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-mono font-semibold text-slate-600 bg-slate-100 px-3 py-1 rounded-lg border border-slate-200">{{ $employee->nip }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-slate-700">{{ $employee->position }}</p>
                            <p class="text-[10px] font-bold text-blue-600 uppercase tracking-tight mt-1">{{ $employee->work_unit->name ?? '-' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex justify-center items-center gap-2">
                                <button onclick="openEditModal({{ json_encode($employee) }}, '{{ $employee->user->email }}')" class="p-2 rounded-lg border border-slate-200 text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all btn-3d">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </button>
                                <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" class="no-loader">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="return confirm('Hapus data pegawai ini?')" class="p-2 rounded-lg border border-slate-200 text-slate-400 hover:text-red-600 hover:bg-red-50 transition-all btn-3d">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
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
    <div class="bg-white w-full max-w-xl rounded-3xl p-8 shadow-2xl animate-in zoom-in duration-200 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-full -mr-16 -mt-16 opacity-50"></div>
        <div class="relative z-10">
            <div class="flex justify-between items-center mb-8">
                <h3 class="text-xl font-bold text-slate-900">Registrasi Pegawai</h3>
                <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <form action="{{ route('employees.store') }}" method="POST" class="space-y-5">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Nama Lengkap</label>
                        <input type="text" name="full_name" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">NIP</label>
                        <input type="text" name="nip" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none">
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Email</label>
                    <input type="email" name="email" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Jabatan</label>
                        <select name="position_id" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none">
                            @foreach($positions as $pos)
                                <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Unit Kerja</label>
                        <select name="work_unit_id" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none">
                            @foreach($workUnits as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Password Default</label>
                    <input type="password" name="password" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none">
                </div>
                <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-xl font-bold text-sm uppercase tracking-widest hover:bg-slate-800 transition-all mt-4 btn-3d">
                    Simpan Pegawai
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-xl rounded-3xl p-8 shadow-2xl animate-in zoom-in duration-200 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-amber-50 rounded-full -mr-16 -mt-16 opacity-50"></div>
        <div class="relative z-10">
            <div class="flex justify-between items-center mb-8">
                <h3 class="text-xl font-bold text-slate-900 italic">Edit Informasi Pegawai</h3>
                <button onclick="document.getElementById('editModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <form id="editForm" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Nama Lengkap</label>
                        <input type="text" name="full_name" id="edit_full_name" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none transition-all">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">NIP</label>
                        <input type="text" name="nip" id="edit_nip" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none transition-all">
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Email</label>
                    <input type="email" name="email" id="edit_email" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none transition-all">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Jabatan</label>
                        <select name="position_id" id="edit_position_id" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none">
                            @foreach($positions as $pos)
                                <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Unit Kerja</label>
                        <select name="work_unit_id" id="edit_work_unit_id" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none">
                            @foreach($workUnits as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-xl font-bold text-sm uppercase tracking-widest hover:bg-blue-700 transition-all mt-4 shadow-lg shadow-blue-900/20 btn-3d">
                    Update Data Pegawai
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div id="edit_password_container"></div> <!-- Placeholder for consistency -->
<div id="importModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-md rounded-3xl p-8 shadow-2xl animate-in zoom-in duration-200">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-slate-900">Import Batch Pegawai</h3>
            <button onclick="document.getElementById('importModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        
        <!-- File Structure Instructions -->
        <div class="mb-8 p-5 bg-amber-50 rounded-2xl border border-amber-100">
            <h4 class="text-[10px] font-bold text-amber-800 uppercase tracking-widest mb-3 flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4"></i> Aturan Struktur File Excel
            </h4>
            <ul class="space-y-2">
                <li class="text-xs font-semibold text-amber-700 flex items-start gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mt-1.5 shrink-0"></span>
                    Baris pertama (Header) harus: <span class="font-bold underline italic">nip, full_name, position, work_unit, email</span>
                </li>
                <li class="text-xs font-semibold text-amber-700 flex items-start gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mt-1.5 shrink-0"></span>
                    Password default akan diatur sama dengan NIP pegawai.
                </li>
                <li class="text-xs font-semibold text-amber-700 flex items-start gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mt-1.5 shrink-0"></span>
                    Pastikan unit kerja & jabatan sesuai dengan data master.
                </li>
            </ul>
        </div>

        <form action="{{ route('employees.import.excel') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="p-8 rounded-2xl bg-slate-50 border border-slate-200 border-dashed text-center group hover:bg-white hover:border-blue-300 transition-all cursor-pointer relative">
                <input type="file" name="file" required class="hidden" id="fileInput" onchange="updateFileName(this)">
                <label for="fileInput" class="cursor-pointer">
                    <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="upload-cloud" class="w-6 h-6 text-slate-400 group-hover:text-blue-500 transition-colors"></i>
                    </div>
                    <p id="fileName" class="text-sm font-semibold text-slate-500">Klik atau seret file .xlsx ke sini</p>
                    <p class="text-[10px] text-slate-400 mt-1">Maksimum ukuran file 5MB</p>
                </label>
            </div>
            <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-xl font-bold text-sm uppercase tracking-widest hover:bg-slate-800 transition-all shadow-lg btn-3d">
                Mulai Proses Import
            </button>
        </form>
    </div>
</div>

<script>
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

    function updateFileName(input) {
        if (input.files && input.files[0]) {
            const fileName = input.files[0].name;
            document.getElementById('fileName').innerHTML = `<span class="text-blue-600 font-bold">${fileName}</span>`;
        }
    }
</script>

@if(session('success'))
<script>
    window.addEventListener('DOMContentLoaded', () => {
        Swal.fire({ 
            icon: 'success', 
            title: 'Berhasil', 
            text: "{{ session('success') }}", 
            confirmButtonColor: '#0F172A', 
            customClass: { popup: 'rounded-2xl' } 
        });
    });
</script>
@endif
@endsection
