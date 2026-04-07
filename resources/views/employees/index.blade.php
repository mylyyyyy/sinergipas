@extends('layouts.app')

@section('title', 'Data Pegawai')
@section('header-title', 'Database Pegawai')

@section('content')
<!-- Custom Loading Overlay for Import -->
<div id="importLoading" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/60 backdrop-blur-md">
    <div class="bg-white rounded-[32px] p-10 shadow-2xl max-w-sm w-full text-center animate-in zoom-in duration-300">
        <div class="relative w-24 h-24 mx-auto mb-6">
            <div class="absolute inset-0 border-4 border-slate-100 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-blue-600 rounded-full border-t-transparent animate-spin"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                <i data-lucide="user-cog" class="w-10 h-10 text-blue-600 animate-pulse"></i>
            </div>
        </div>
        <h3 class="text-xl font-bold text-slate-900 mb-2">Sinkronisasi Pegawai</h3>
        <p class="text-sm text-slate-500 font-medium leading-relaxed">Mohon tunggu, sistem sedang memproses database kepegawaian Anda secara instan...</p>
    </div>
</div>

<div class="space-y-8 page-fade">
    <!-- Hero Section -->
    <div class="relative overflow-hidden rounded-[48px] bg-slate-900 px-10 py-14 text-white shadow-2xl card-3d mb-12 border border-white/5">
        <div class="absolute -left-20 -top-20 h-96 w-96 rounded-full bg-blue-600/20 blur-[120px] animate-pulse"></div>
        <div class="absolute -right-20 -bottom-20 h-96 w-96 rounded-full bg-indigo-500/10 blur-[100px]"></div>
        
        <div class="relative z-10 flex flex-col gap-10 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-6">
                <div class="flex items-center gap-5">
                    <div class="relative">
                        <div class="absolute inset-0 bg-blue-600 blur-xl opacity-40 animate-pulse"></div>
                        <div class="relative w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-3xl flex items-center justify-center shadow-2xl shadow-blue-900/50 transform -rotate-3 group-hover:rotate-0 transition-transform duration-500">
                            <i data-lucide="users-round" class="w-8 h-8 text-white"></i>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-5xl font-black tracking-tight italic leading-none">
                            Data <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-300">Pegawai</span>
                        </h2>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="h-1 w-12 bg-blue-500 rounded-full"></div>
                            <p class="text-[11px] font-black uppercase tracking-[0.3em] text-blue-400/80">Human Resources Engine</p>
                        </div>
                    </div>
                </div>
                
                <p class="text-slate-400 font-medium max-w-2xl text-lg leading-relaxed">
                    Pusat kendali database kepegawaian modern. Kelola profil, akses sistem, dan penempatan unit kerja seluruh petugas secara terintegrasi dengan standar keamanan tinggi.
                </p>

                <div class="flex flex-wrap items-center gap-6">
                    <div class="flex items-center gap-3 px-5 py-2.5 bg-white/5 border border-white/10 rounded-2xl backdrop-blur-md">
                        <div class="flex -space-x-3">
                            <div class="w-8 h-8 rounded-full border-2 border-slate-900 bg-blue-500 flex items-center justify-center text-[10px] font-bold">LP</div>
                            <div class="w-8 h-8 rounded-full border-2 border-slate-900 bg-indigo-500 flex items-center justify-center text-[10px] font-bold">JB</div>
                            <div class="w-8 h-8 rounded-full border-2 border-slate-900 bg-slate-700 flex items-center justify-center text-[10px] font-bold text-slate-400">+</div>
                        </div>
                        <span class="text-sm font-bold text-white tracking-wide">
                            {{ $employees->total() }} <span class="text-slate-500 font-medium ml-1 text-xs uppercase tracking-widest">Personel Aktif</span>
                        </span>
                    </div>
                    <div class="h-10 w-px bg-white/10 hidden md:block"></div>
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Sinkronisasi Terakhir</span>
                        <span class="text-sm font-bold text-blue-400">{{ date('d F Y') }}</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-4 items-center">
                <div class="flex flex-col gap-2">
                    <div class="flex bg-white/5 p-2 rounded-[28px] border border-white/10 backdrop-blur-md shadow-inner">
                        <button type="button" onclick="document.getElementById('importModal').classList.remove('hidden')" class="px-6 py-4 rounded-2xl text-[11px] font-black uppercase tracking-widest hover:bg-white/10 transition-all flex items-center gap-3 group">
                            <i data-lucide="file-up" class="w-5 h-5 text-amber-400 group-hover:-translate-y-1 transition-transform"></i> Impor
                        </button>
                        <a href="{{ route('employees.export.excel') }}" class="px-6 py-4 rounded-2xl text-[11px] font-black uppercase tracking-widest hover:bg-white/10 transition-all flex items-center gap-3 group no-loader">
                            <i data-lucide="download-cloud" class="w-5 h-5 text-emerald-400 group-hover:translate-y-1 transition-transform"></i> Ekspor
                        </a>
                        <button type="button" onclick="confirmDestroyAll()" class="px-6 py-4 rounded-2xl text-[11px] font-black uppercase tracking-widest hover:bg-red-500/20 text-red-400 transition-all flex items-center gap-3 group">
                            <i data-lucide="trash-2" class="w-5 h-5 group-hover:scale-110 transition-transform"></i> Kosongkan
                        </button>
                    </div>
                    <div class="flex gap-2 justify-center">
                        <a href="{{ route('admin.ranks.index') }}" class="px-4 py-2 bg-slate-800 border border-slate-700 rounded-xl text-[9px] font-bold uppercase tracking-widest text-slate-400 hover:text-white transition-all flex items-center gap-2">
                            <i data-lucide="shield-check" class="w-3 h-3"></i> Atur Golongan
                        </a>
                        <button onclick="sortNames()" class="px-4 py-2 bg-slate-800 border border-slate-700 rounded-xl text-[9px] font-bold uppercase tracking-widest text-slate-400 hover:text-white transition-all flex items-center gap-2">
                            <i data-lucide="sort-asc" class="w-3 h-3"></i> Urut A-Z
                        </button>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('addModal').classList.remove('hidden')" class="px-10 py-5 rounded-[28px] bg-white text-slate-900 text-[11px] font-black uppercase tracking-[0.15em] hover:bg-blue-600 hover:text-white transition-all shadow-[0_20px_40px_rgba(0,0,0,0.3)] flex items-center gap-4 active:scale-95 group">
                    <div class="w-8 h-8 bg-blue-100 rounded-xl flex items-center justify-center group-hover:bg-white/20 transition-colors">
                        <i data-lucide="user-plus" class="w-5 h-5 text-blue-600 group-hover:text-white"></i>
                    </div>
                    Registrasi Baru
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
            <table class="w-full text-left border-collapse" id="employeeTable">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-6 py-4 w-10">
                            <input type="checkbox" id="selectAll" class="w-5 h-5 rounded-lg border-slate-300 text-blue-600 focus:ring-0 cursor-pointer">
                        </th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Identitas Pegawai</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Unit & Jabatan</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Tipe & Gol</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($employees as $employee)
                    <tr class="hover:bg-slate-50/50 transition-colors group employee-row" data-name="{{ $employee->full_name }}">
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
                                    <p class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors truncate name-field">{{ $employee->full_name }}</p>
                                    <p class="text-[10px] font-mono font-bold text-slate-400 mt-0.5 tracking-tight">NIP. {{ $employee->nip }} | NIK. {{ $employee->nik ?? '-' }}</p>
                                    <p class="text-[9px] font-bold text-green-600 mt-0.5 uppercase">WA: {{ $employee->phone_number ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-3 py-1 bg-blue-50 text-blue-600 text-[9px] font-bold uppercase rounded-lg border border-blue-100 inline-block mb-1">{{ $employee->work_unit->name ?? '-' }}</span>
                            <p class="text-[10px] font-semibold text-slate-500 truncate max-w-[150px] mx-auto">{{ $employee->position }}</p>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex flex-col items-center">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[9px] font-bold border border-slate-200 uppercase mb-1">{{ str_replace('_', ' ', $employee->employee_type) }}</span>
                                <span class="text-[9px] font-bold text-amber-600 uppercase">Gol. {{ $employee->rank_relation->name ?? $employee->rank_class ?? '-' }}</span>
                                @if($employee->picket_regu)
                                    <span class="text-[8px] font-extrabold text-blue-500 mt-1 uppercase">Regu {{ $employee->picket_regu }}</span>
                                @endif
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
    </div>
</div>

<!-- Modal Add -->
<div id="addModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-4 sm:p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-2xl rounded-[32px] shadow-2xl animate-in zoom-in duration-200 relative overflow-hidden flex flex-col max-h-[90vh]">
        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-full -mr-16 -mt-16 opacity-50"></div>
        
        <!-- Modal Header -->
        <div class="relative z-10 px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-white shrink-0">
            <div>
                <h3 class="text-xl font-bold text-slate-900 tracking-tight">Registrasi Pegawai</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Penambahan Entitas Kepegawaian Baru</p>
            </div>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 hover:text-red-500 transition-colors">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>

        <!-- Scrollable Form Area -->
        <div class="overflow-y-auto custom-scrollbar p-8">
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">NIK</label>
                        <input type="text" name="nik" placeholder="16 Digit NIK..." class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none transition-all">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">No. WhatsApp</label>
                        <input type="text" name="phone_number" placeholder="628..." class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none transition-all">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Email</label>
                        <input type="email" name="email" required placeholder="pegawai@sinergipas.id" class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none transition-all">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Golongan</label>
                        <select name="rank_id" required class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-bold focus:border-blue-500 outline-none appearance-none cursor-pointer">
                            <option value="">-- Pilih Golongan --</option>
                            @foreach($ranks as $rank)
                                <option value="{{ $rank->id }}">{{ $rank->name }} ({{ $rank->description }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Tipe Pegawai</label>
                        <select name="employee_type" required class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-bold focus:border-blue-500 outline-none appearance-none cursor-pointer" onchange="toggleRegu(this, 'add')">
                            <option value="non_regu_jaga">Non-Regu (Kantor)</option>
                            <option value="regu_jaga">Regu Jaga (Shift)</option>
                        </select>
                    </div>
                    <div id="regu_container_add" class="space-y-1.5 hidden">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Pilih Regu</label>
                        <select name="picket_regu" class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-bold focus:border-blue-500 outline-none appearance-none cursor-pointer">
                            <option value="">-- Tanpa Regu --</option>
                            <option value="A">Regu A</option>
                            <option value="B">Regu B</option>
                            <option value="C">Regu C</option>
                            <option value="D">Regu D</option>
                        </select>
                    </div>
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
                <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-bold text-sm uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl btn-3d mt-4 shrink-0">
                    Simpan Data Pegawai
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-4 sm:p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-2xl rounded-[32px] shadow-2xl animate-in zoom-in duration-200 relative overflow-hidden flex flex-col max-h-[90vh]">
        <div class="absolute top-0 right-0 w-32 h-32 bg-amber-50 rounded-full -mr-16 -mt-16 opacity-50"></div>
        
        <!-- Modal Header -->
        <div class="relative z-10 px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-white shrink-0">
            <div>
                <h3 class="text-2xl font-bold text-slate-900 italic tracking-tight">Edit Profil Pegawai</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Pembaruan Informasi Entitas</p>
            </div>
            <button onclick="document.getElementById('editModal').classList.add('hidden')" class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 hover:text-red-500 transition-colors">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>

        <!-- Scrollable Form Area -->
        <div class="overflow-y-auto custom-scrollbar p-8">
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">NIK</label>
                        <input type="text" name="nik" id="edit_nik" class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none transition-all">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">No. WhatsApp</label>
                        <input type="text" name="phone_number" id="edit_phone_number" class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none transition-all">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Email</label>
                        <input type="email" name="email" id="edit_email" required class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none transition-all">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Golongan</label>
                        <select name="rank_id" id="edit_rank_id" required class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-bold focus:border-blue-500 outline-none appearance-none cursor-pointer">
                            <option value="">-- Pilih Golongan --</option>
                            @foreach($ranks as $rank)
                                <option value="{{ $rank->id }}">{{ $rank->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Tipe Pegawai</label>
                        <select name="employee_type" id="edit_employee_type" required class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-bold focus:border-blue-500 outline-none" onchange="toggleRegu(this, 'edit')">
                            <option value="non_regu_jaga">Non-Regu (Kantor)</option>
                            <option value="regu_jaga">Regu Jaga (Shift)</option>
                        </select>
                    </div>
                    <div id="regu_container_edit" class="space-y-1.5 hidden">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Pilih Regu</label>
                        <select name="picket_regu" id="edit_picket_regu" class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-bold focus:border-blue-500 outline-none">
                            <option value="">-- Tanpa Regu --</option>
                            <option value="A">Regu A</option>
                            <option value="B">Regu B</option>
                            <option value="C">Regu C</option>
                            <option value="D">Regu D</option>
                        </select>
                    </div>
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
                <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-bold text-sm uppercase tracking-widest hover:bg-blue-700 transition-all shadow-xl btn-3d mt-4 shrink-0">
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
                <p class="text-[11px] font-medium text-slate-300">Gunakan format kolom berikut secara berurutan:</p>
                <div class="bg-white/5 p-3 rounded-xl border border-white/10 font-mono text-[9px] text-amber-200 text-center select-all leading-relaxed">
                    NIP, Nama Lengkap, Jabatan, Unit Kerja, Email, NIK, No. WhatsApp, Golongan, Regu
                </div>
                <ul class="text-[9px] text-slate-400 space-y-1 pl-4 list-disc">
                    <li>Jabatan & Unit otomatis tersinkron ke Master Data.</li>
                    <li>Jika 'Regu' diisi selain 'Staf', otomatis menjadi Regu Jaga.</li>
                    <li>Sistem akan melakukan Update data jika NIP sudah terdaftar.</li>
                </ul>
            </div>
        </div>

        <form id="importForm" action="{{ route('employees.import.excel') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="p-8 rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 text-center group hover:bg-white hover:border-blue-400 transition-all cursor-pointer relative">
                <input type="file" name="file" required class="absolute inset-0 opacity-0 cursor-pointer" onchange="updateFileName(this)">
                <i data-lucide="upload-cloud" class="w-10 h-10 text-slate-300 mx-auto mb-3 group-hover:text-blue-500 group-hover:scale-110 transition-all"></i>
                <p id="fileName" class="text-xs font-bold text-slate-500 group-hover:text-blue-600">Klik atau seret file .xlsx</p>
            </div>
            <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-bold text-sm uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl btn-3d">
                Proses Impor Data
            </button>
        </form>
    </div>
</div>

<form id="bulkDeleteForm" action="{{ route('employees.bulk-destroy') }}" method="POST" class="hidden no-loader">
    @csrf @method('DELETE')
</form>

<form id="destroyAllForm" action="{{ route('employees.destroy-all') }}" method="POST" class="hidden no-loader">
    @csrf @method('DELETE')
</form>

<script>
    let isAsc = true;
    function sortNames() {
        const table = document.getElementById('employeeTable');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('.employee-row'));

        rows.sort((a, b) => {
            const nameA = a.dataset.name.toLowerCase();
            const nameB = b.dataset.name.toLowerCase();
            return isAsc ? nameA.localeCompare(nameB) : nameB.localeCompare(nameA);
        });

        isAsc = !isAsc;
        
        // Update icons or visual indicator if needed
        rows.forEach(row => tbody.appendChild(row));
    }

    // Show Loading on Import Submit
    document.getElementById('importForm').addEventListener('submit', function() {
        document.getElementById('importModal').classList.add('hidden');
        document.getElementById('importLoading').classList.remove('hidden');
        document.getElementById('importLoading').classList.add('flex');
    });
    function toggleRegu(select, mode) {
        const container = document.getElementById(`regu_container_${mode}`);
        if (select.value === 'regu_jaga') {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }

    const selectAll = document.getElementById('selectAll');
    const checkboxes = Array.from(document.querySelectorAll('.emp-checkbox'));
    const bulkActionBar = document.getElementById('bulkActionBar');
    const selectedCount = document.getElementById('selectedCount');

    function syncSelection() {
        const checked = checkboxes.filter(c => c.checked);
        selectedCount.textContent = checked.length;
        bulkActionBar.classList.toggle('hidden', checked.length === 0);
        if(selectAll) {
            selectAll.checked = checkboxes.length > 0 && checked.length === checkboxes.length;
            selectAll.indeterminate = checked.length > 0 && checked.length < checkboxes.length;
        }
    }

    if(selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(c => c.checked = this.checked);
            syncSelection();
        });
    }

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

    function confirmDestroyAll() {
        Swal.fire({
            title: 'KOSONGKAN DATABASE?',
            text: "Tindakan ini akan menghapus SELURUH data pegawai secara permanen. Anda tidak dapat membatalkan ini!",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            confirmButtonText: 'YA, HAPUS SEMUA!',
            cancelButtonText: 'Batal',
            footer: '<span class="text-red-500 font-bold">Hanya Superadmin yang dapat melakukan ini.</span>',
            customClass: { popup: 'rounded-3xl' }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Konfirmasi Terakhir',
                    text: 'Ketik kata "HAPUS" untuk mengonfirmasi pembersihan total data pegawai:',
                    input: 'text',
                    inputAttributes: { autocapitalize: 'off' },
                    showCancelButton: true,
                    confirmButtonText: 'Eksekusi Sekarang!',
                    confirmButtonColor: '#EF4444',
                    customClass: { popup: 'rounded-3xl' },
                    preConfirm: (value) => {
                        if (value !== 'HAPUS') {
                            Swal.showValidationMessage('Kata konfirmasi salah!');
                        }
                    }
                }).then((res) => {
                    if (res.isConfirmed) {
                        document.getElementById('destroyAllForm').submit();
                    }
                });
            }
        });
    }

    function openEditModal(employee, email) {
        const modal = document.getElementById('editModal');
        const form = document.getElementById('editForm');
        form.action = `/employees/${employee.id}`;
        document.getElementById('edit_full_name').value = employee.full_name;
        document.getElementById('edit_nip').value = employee.nip;
        document.getElementById('edit_nik').value = employee.nik || '';
        document.getElementById('edit_phone_number').value = employee.phone_number || '';
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_rank_id').value = employee.rank_id || '';
        document.getElementById('edit_employee_type').value = employee.employee_type || 'non_regu_jaga';
        document.getElementById('edit_picket_regu').value = employee.picket_regu || '';
        document.getElementById('edit_position_id').value = employee.position_id;
        document.getElementById('edit_work_unit_id').value = employee.work_unit_id;
        
        toggleRegu(document.getElementById('edit_employee_type'), 'edit');
        
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
    window.addEventListener('DOMContentLoaded', () => {
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", confirmButtonColor: '#0F172A', customClass: { popup: 'rounded-2xl' } });
    });
</script>
@endif
@endsection
