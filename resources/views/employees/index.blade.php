@extends('layouts.app')

@section('title', 'Data Pegawai')
@section('header-title', 'Manajemen Database Pegawai')

@section('content')
<style>
    .glass-card { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); border: 1px solid rgba(239, 239, 239, 0.5); }
    .table-row-hover:hover { background-color: #FCFBF9; transform: scale(1.002); }
    .action-btn { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .action-btn:hover { transform: translateY(-3px); }
</style>

@php
    $filteredCount = $employees->total();
    $unitLabel = $workUnits->firstWhere('id', request('work_unit_id'))?->name ?? 'Semua Unit Kerja';
@endphp

<div class="relative overflow-hidden rounded-[56px] bg-[#1E2432] px-8 py-8 text-white shadow-2xl shadow-slate-900/15 sm:px-10 sm:py-10 mb-12">
    <div class="absolute -left-10 top-8 h-44 w-44 rounded-full bg-white/5 blur-3xl"></div>
    <div class="absolute right-0 top-0 h-60 w-60 rounded-full bg-[#E85A4F]/20 blur-3xl"></div>

    <div class="relative z-10 flex flex-col gap-8 xl:flex-row xl:items-end xl:justify-between">
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-[10px] font-black uppercase tracking-[0.28em] text-white/80">
                <span class="h-2 w-2 rounded-full bg-[#E85A4F]"></span>
                Data Pegawai
            </div>
            <h2 class="mt-5 text-3xl font-black tracking-tight sm:text-4xl">Pusat pengelolaan pegawai yang lebih rapi untuk pencarian, ekspor, dan aksi massal.</h2>
            <p class="mt-4 max-w-2xl text-sm font-medium leading-relaxed text-white/65">
                Halaman ini dipoles agar pencarian lebih cepat dipahami, status filter lebih jelas, dan kontrol tindakan pada setiap baris tetap nyaman dipakai di desktop maupun mobile.
            </p>
        </div>

        <div class="rounded-[28px] border border-white/10 bg-white/5 px-6 py-5 backdrop-blur">
            <p class="text-[10px] font-black uppercase tracking-[0.24em] text-white/50">Filter Unit Aktif</p>
            <p class="mt-3 text-xl font-black tracking-tight">{{ $unitLabel }}</p>
        </div>
    </div>

    <div class="relative z-10 mt-8 grid gap-4 md:grid-cols-3">
        <div class="rounded-[24px] border border-white/10 bg-white/5 p-5">
            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-white/45">Data Tersaring</p>
            <p class="mt-3 text-3xl font-black">{{ $filteredCount }}</p>
        </div>
        <div class="rounded-[24px] border border-white/10 bg-white/5 p-5">
            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-white/45">Master Jabatan</p>
            <p class="mt-3 text-3xl font-black">{{ $positions->count() }}</p>
        </div>
        <div class="rounded-[24px] border border-white/10 bg-white/5 p-5">
            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-white/45">Master Unit Kerja</p>
            <p class="mt-3 text-3xl font-black">{{ $workUnits->count() }}</p>
        </div>
    </div>
</div>

<div class="flex flex-col lg:flex-row gap-8 justify-between items-start lg:items-center mb-12">
    <!-- Search Bar & Filter -->
    <div class="flex flex-col md:flex-row gap-4 w-full lg:w-auto">
        <form action="{{ route('employees.index') }}" method="GET" class="relative w-full lg:w-[420px] group no-loader">
            <i data-lucide="search" class="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-[#8A8A8A] group-focus-within:text-[#E85A4F] transition-all"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama, NIP, atau Jabatan..." 
                class="w-full pl-16 pr-12 py-5 rounded-[32px] border border-[#EFEFEF] bg-white text-sm font-bold text-[#1E2432] outline-none focus:ring-8 focus:ring-red-500/5 focus:border-[#E85A4F] transition-all shadow-sm group-hover:shadow-xl">
            @if(request('search'))
                <a href="{{ route('employees.index', request()->except('search')) }}" class="absolute right-6 top-1/2 -translate-y-1/2 text-[#8A8A8A] hover:text-red-500 transition-all">
                    <i data-lucide="x-circle" class="w-5 h-5"></i>
                </a>
            @endif
        </form>

        <form action="{{ route('employees.index') }}" method="GET" class="no-loader group">
            @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
            <div class="relative">
                <select name="work_unit_id" onchange="this.form.submit()" class="w-full md:w-[240px] px-8 py-5 rounded-[32px] border border-[#EFEFEF] bg-white text-[10px] font-black uppercase tracking-widest text-[#1E2432] outline-none focus:ring-8 focus:ring-red-500/5 focus:border-[#E85A4F] transition-all shadow-sm cursor-pointer appearance-none">
                    <option value="">Seluruh Unit Kerja</option>
                    @foreach($workUnits as $unit)
                        <option value="{{ $unit->id }}" {{ request('work_unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                    @endforeach
                </select>
                <i data-lucide="chevron-down" class="absolute right-6 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A8A8A] pointer-events-none"></i>
            </div>
        </form>
    </div>

    <div class="flex flex-wrap gap-4 w-full lg:w-auto">
        <button type="button" id="bulkDeleteBtn" class="hidden bg-red-50 text-red-600 px-8 py-5 rounded-[24px] font-black text-[10px] uppercase tracking-widest hover:bg-red-600 hover:text-white transition-all items-center justify-center gap-3 shadow-lg shadow-red-100 border border-red-100">
            <i data-lucide="trash-2" class="w-4 h-4"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
        </button>
        <button type="button" onclick="document.getElementById('importModal').classList.remove('hidden')" class="flex-1 lg:flex-none bg-white border border-[#EFEFEF] text-[#1E2432] px-8 py-5 rounded-[24px] font-black text-[10px] uppercase tracking-widest hover:bg-[#1E2432] hover:text-white transition-all flex items-center justify-center gap-3 shadow-sm">
            <i data-lucide="file-up" class="w-4 h-4 text-blue-600"></i> Impor Excel
        </button>
        <button type="button" onclick="document.getElementById('addModal').classList.remove('hidden')" class="flex-1 lg:flex-none bg-[#E85A4F] text-white px-10 py-5 rounded-[24px] font-black text-[10px] uppercase tracking-widest hover:bg-[#d44d42] transition-all flex items-center justify-center gap-3 shadow-xl shadow-red-200">
            <i data-lucide="user-plus" class="w-4 h-4"></i> Registrasi Pegawai
        </button>
    </div>
</div>

<form id="bulkForm" action="{{ route('employees.bulk-destroy') }}" method="POST">
    @csrf
    @method('DELETE')
    
    <div class="bg-white rounded-[64px] border border-[#EFEFEF] shadow-sm overflow-hidden transition-all hover:shadow-2xl hover:shadow-gray-100/50">
        <div class="p-12 border-b border-[#EFEFEF] flex flex-col md:flex-row justify-between items-center gap-8 bg-[#FCFBF9]/30">
            <div>
                <h3 class="text-2xl font-black text-[#1E2432] tracking-tight italic">Daftar Inventaris Pegawai</h3>
                <p class="text-[10px] font-bold text-[#8A8A8A] uppercase tracking-[0.3em] mt-2">Sinkronisasi Database: {{ now()->format('d M Y') }}</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('employees.export.excel') }}" class="w-14 h-14 bg-white border border-[#EFEFEF] text-[#8A8A8A] hover:text-green-600 hover:border-green-200 hover:bg-green-50 rounded-2xl flex items-center justify-center transition-all shadow-sm no-loader group" title="Ekspor Excel">
                    <i data-lucide="file-spreadsheet" class="w-6 h-6 group-hover:scale-110 transition-transform"></i>
                </a>
                <a href="{{ route('employees.export.pdf') }}" class="w-14 h-14 bg-white border border-[#EFEFEF] text-[#8A8A8A] hover:text-orange-600 hover:border-orange-200 hover:bg-orange-50 rounded-2xl flex items-center justify-center transition-all shadow-sm no-loader group" title="Ekspor PDF">
                    <i data-lucide="file-type-2" class="w-6 h-6 group-hover:scale-110 transition-transform"></i>
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#FCFBF9]/50">
                        <th class="px-12 py-6 w-10">
                            <input type="checkbox" id="selectAll" class="w-6 h-6 rounded-xl border-[#EFEFEF] text-[#E85A4F] focus:ring-0 cursor-pointer">
                        </th>
                        <th class="px-6 py-6 text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.3em]">Informasi Profil</th>
                        <th class="px-12 py-6 text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.3em]">Identitas NIP</th>
                        <th class="px-12 py-6 text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.3em]">Jabatan & Penempatan</th>
                        <th class="px-12 py-6 text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.3em] text-center">Kendali Sistem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EFEFEF]">
                    @foreach($employees as $employee)
                    <tr class="transition-all group table-row-hover">
                        <td class="px-12 py-8">
                            <input type="checkbox" name="ids[]" value="{{ $employee->id }}" class="row-checkbox w-6 h-6 rounded-xl border-[#EFEFEF] text-[#E85A4F] focus:ring-0 cursor-pointer">
                        </td>
                        <td class="px-6 py-8">
                            <a href="{{ route('employees.show', $employee->id) }}" class="flex items-center gap-6 group/item transition-all">
                                <div class="w-16 h-16 bg-gray-100 rounded-[24px] flex items-center justify-center text-[#8A8A8A] group-hover/item:bg-[#E85A4F] group-hover/item:text-white transition-all overflow-hidden text-xs shadow-inner relative">
                                    @if($employee->photo)
                                        <img src="{{ $employee->photo }}" class="w-full h-full object-cover">
                                    @else
                                        <i data-lucide="user" class="w-8 h-8 opacity-40"></i>
                                    @endif
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover/item:opacity-100 transition-opacity flex items-center justify-center">
                                        <i data-lucide="external-link" class="w-5 h-5 text-white"></i>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-base font-black text-[#1E2432] group-hover/item:text-[#E85A4F] transition-all">{{ $employee->full_name }}</p>
                                    <p class="text-[9px] text-[#ABABAB] font-bold uppercase tracking-[0.2em] mt-1 flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> Pegawai Aktif
                                    </p>
                                </div>
                            </a>
                        </td>
                        <td class="px-12 py-8">
                            <span class="text-xs font-mono font-bold text-[#1E2432] bg-[#FCFBF9] px-4 py-2 rounded-xl border border-[#EFEFEF] shadow-sm italic">{{ $employee->nip }}</span>
                        </td>
                        <td class="px-12 py-8">
                            <p class="text-sm font-black text-[#1E2432]">{{ $employee->position }}</p>
                            <div class="flex items-center gap-2 mt-1.5">
                                <i data-lucide="building-2" class="w-3 h-3 text-[#E85A4F]"></i>
                                <p class="text-[10px] text-[#8A8A8A] font-bold uppercase tracking-tight">{{ $employee->work_unit->name ?? 'Tanpa Unit Kerja' }}</p>
                            </div>
                        </td>
                        <td class="px-12 py-8 text-sm text-center">
                            <div class="flex justify-center items-center gap-3 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-all transform md:scale-90 md:group-hover:scale-100">
                                @php
                                    $waMessage = "Halo " . $employee->full_name . ", mohon segera unggah dokumen wajib Anda di sistem Sinergi PAS Jombang. Terima kasih.";
                                    $waLink = "https://wa.me/" . preg_replace('/[^0-9]/', '', '628123456789') . "?text=" . urlencode($waMessage);
                                @endphp
                                <a href="{{ $waLink }}" target="_blank" class="w-12 h-12 bg-green-50 text-green-600 border border-green-100 rounded-2xl flex items-center justify-center hover:bg-green-600 hover:text-white transition-all shadow-lg shadow-green-100 action-btn" title="Kirim Pengingat WA">
                                    <i data-lucide="message-circle" class="w-5 h-5"></i>
                                </a>
                                <button type="button" onclick="openEditModal({{ json_encode([
                                    'id' => $employee->id,
                                    'full_name' => $employee->full_name,
                                    'nip' => $employee->nip,
                                    'position_id' => $employee->position_id,
                                    'work_unit_id' => $employee->work_unit_id,
                                    'photo' => $employee->photo,
                                ]) }}, '{{ $employee->user->email }}')" class="w-12 h-12 bg-blue-50 text-blue-600 border border-blue-100 rounded-2xl flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all shadow-lg shadow-blue-100 action-btn">
                                    <i data-lucide="pencil" class="w-5 h-5"></i>
                                </button>
                                <form id="deleteForm-{{ $employee->id }}" action="{{ route('employees.destroy', $employee->id) }}" method="POST" class="m-0 p-0 no-loader">
                                    @csrf @method('DELETE')
                                    <button type="button" onclick="confirmIndividualDelete({{ $employee->id }})" class="w-12 h-12 bg-red-50 text-red-600 border border-red-100 rounded-2xl flex items-center justify-center hover:bg-red-600 hover:text-white transition-all shadow-lg shadow-red-100 action-btn">
                                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @if($employees->isEmpty())
                    <tr>
                        <td colspan="5" class="px-12 py-40 text-center">
                            <div class="w-32 h-32 bg-[#FCFBF9] rounded-full flex items-center justify-center mx-auto mb-8 text-gray-200 shadow-inner">
                                <i data-lucide="users" class="w-16 h-16 opacity-30"></i>
                            </div>
                            <p class="text-sm font-black text-[#ABABAB] uppercase tracking-[0.4em] italic">Database Kosong / Data Tidak Ditemukan</p>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="p-12 bg-[#FCFBF9]/30 border-t border-[#EFEFEF]">
            {{ $employees->links() }}
        </div>
    </div>
</form>

<!-- Add Modal (Tetap Kompak) -->
<div id="addModal" class="fixed inset-0 bg-black/70 hidden flex items-center justify-center z-50 p-6 backdrop-blur-xl">
    <div class="bg-white w-full max-w-2xl rounded-[64px] p-14 shadow-2xl animate-in zoom-in duration-300 overflow-hidden border border-[#EFEFEF]">
        <div class="flex justify-between items-center mb-12">
            <div>
                <h3 class="text-3xl font-black text-[#1E2432] tracking-tight italic">Registrasi Pegawai</h3>
                <p class="text-[10px] font-bold text-[#8A8A8A] uppercase tracking-[0.3em] mt-2">Entri Data ke Infrastruktur Digital</p>
            </div>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="bg-[#FCFBF9] w-14 h-14 rounded-2xl text-[#8A8A8A] hover:text-red-500 transition-all border border-[#EFEFEF] flex items-center justify-center shadow-sm">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <form action="{{ route('employees.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-10">
            @csrf
            <div class="space-y-3">
                <label class="text-[10px] font-black text-[#1E2432] uppercase tracking-[0.2em] ml-2">Nama Lengkap Sesuai SK</label>
                <input type="text" name="full_name" required placeholder="Contoh: Budi Santoso, S.H." class="w-full px-8 py-5 rounded-[28px] border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold outline-none focus:ring-12 focus:ring-red-500/5 focus:border-[#E85A4F] transition-all shadow-inner">
            </div>
            <div class="space-y-3">
                <label class="text-[10px] font-black text-[#1E2432] uppercase tracking-[0.2em] ml-2">Nomor Induk Pegawai</label>
                <input type="text" name="nip" required placeholder="18 Digit Angka" class="w-full px-8 py-5 rounded-[28px] border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold outline-none focus:ring-12 focus:ring-red-500/5 focus:border-[#E85A4F] transition-all shadow-inner">
            </div>
            <div class="space-y-3">
                <label class="text-[10px] font-black text-[#1E2432] uppercase tracking-[0.2em] ml-2">Email Kedinasan</label>
                <input type="email" name="email" required placeholder="email@pas.id" class="w-full px-8 py-5 rounded-[28px] border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold outline-none focus:ring-12 focus:ring-red-500/5 focus:border-[#E85A4F] transition-all shadow-inner">
            </div>
            <div class="space-y-3">
                <label class="text-[10px] font-black text-[#1E2432] uppercase tracking-[0.2em] ml-2">Jabatan Fungsional</label>
                <div class="relative">
                    <select name="position_id" required class="w-full px-8 py-5 rounded-[28px] border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold outline-none focus:ring-12 focus:ring-red-500/5 focus:border-[#E85A4F] transition-all appearance-none cursor-pointer">
                        <option value="">Pilih Jabatan</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                        @endforeach
                    </select>
                    <i data-lucide="chevron-down" class="absolute right-6 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A8A8A]"></i>
                </div>
            </div>
            <div class="space-y-3">
                <label class="text-[10px] font-black text-[#1E2432] uppercase tracking-[0.2em] ml-2">Unit Kerja / Divisi</label>
                <div class="relative">
                    <select name="work_unit_id" required class="w-full px-8 py-5 rounded-[28px] border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold outline-none focus:ring-12 focus:ring-red-500/5 focus:border-[#E85A4F] transition-all appearance-none cursor-pointer">
                        <option value="">Pilih Unit</option>
                        @foreach($workUnits as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                        @endforeach
                    </select>
                    <i data-lucide="chevron-down" class="absolute right-6 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A8A8A]"></i>
                </div>
            </div>
            <div class="space-y-3">
                <label class="text-[10px] font-black text-[#1E2432] uppercase tracking-[0.2em] ml-2">Kata Sandi Default</label>
                <input type="password" name="password" required placeholder="Keamanan Minimum 8 Karakter" class="w-full px-8 py-5 rounded-[28px] border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold outline-none focus:ring-12 focus:ring-red-500/5 focus:border-[#E85A4F] transition-all shadow-inner">
            </div>
            <div class="md:col-span-2 pt-6">
                <button type="submit" class="w-full bg-[#1E2432] text-white py-6 rounded-[32px] font-black text-lg hover:bg-[#E85A4F] transition-all shadow-2xl active:scale-[0.98] flex items-center justify-center gap-4 group">
                    Sinkronisasi & Simpan Pegawai
                    <i data-lucide="zap" class="w-6 h-6 group-hover:rotate-12 transition-transform"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Import Modal -->
<div id="importModal" class="fixed inset-0 bg-black/70 hidden flex items-center justify-center z-50 p-6 backdrop-blur-xl">
    <div class="bg-white w-full max-w-md rounded-[64px] p-14 shadow-2xl animate-in zoom-in duration-300 border border-[#EFEFEF]">
        <div class="flex justify-between items-center mb-12">
            <div>
                <h3 class="text-2xl font-black text-[#1E2432] tracking-tight italic">Batch Import</h3>
                <p class="text-[10px] font-bold text-[#8A8A8A] uppercase tracking-[0.3em] mt-2">Unggah File MS Excel (.xlsx)</p>
            </div>
            <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="bg-[#FCFBF9] w-12 h-12 rounded-2xl text-[#8A8A8A] hover:text-red-500 transition-all border border-[#EFEFEF] flex items-center justify-center">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('employees.import.excel') }}" method="POST" enctype="multipart/form-data" class="space-y-10">
            @csrf
            <div class="p-8 bg-blue-50 rounded-[40px] border border-blue-100 text-blue-700 text-[11px] font-bold leading-relaxed shadow-inner">
                <p class="font-black uppercase mb-4 flex items-center gap-3"><i data-lucide="info" class="w-5 h-5"></i> Struktur Header:</p>
                <p class="opacity-80 uppercase tracking-tighter italic border-l-2 border-blue-200 pl-4">NIP, Nama Lengkap, Jabatan, Unit Kerja, Email</p>
            </div>
            <div class="relative group">
                <input type="file" name="file" required class="w-full px-8 py-14 rounded-[40px] border-4 border-dashed border-[#EFEFEF] bg-[#FCFBF9] text-[10px] font-black uppercase text-[#8A8A8A] file:hidden cursor-pointer hover:border-blue-500 hover:bg-blue-50/30 transition-all text-center">
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none opacity-40 group-hover:opacity-100 transition-all">
                    <i data-lucide="file-up" class="w-12 h-12 text-blue-600 mb-4"></i>
                    <span class="text-[10px] uppercase font-black tracking-widest">Klik / Drag File Excel Disini</span>
                </div>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-6 rounded-[32px] font-black text-lg hover:bg-blue-700 transition-all shadow-xl shadow-blue-100 active:scale-95 flex items-center justify-center gap-4 group">
                Proses Import Massal
                <i data-lucide="refresh-cw" class="w-6 h-6 group-hover:rotate-180 transition-transform duration-700"></i>
            </button>
        </form>
    </div>
</div>

<!-- Edit Modal (Compact & Clean UI) -->
<div id="editModal" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-md">
    <div class="bg-white w-full max-w-2xl rounded-[48px] shadow-2xl animate-in zoom-in duration-300 overflow-hidden border border-[#EFEFEF]">
        <!-- Compact Header -->
        <div class="bg-[#1E2432] px-10 py-8 text-white flex justify-between items-center relative">
            <div class="absolute inset-0 opacity-5 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
            <div class="relative flex items-center gap-5">
                <div class="w-12 h-12 bg-[#E85A4F] rounded-2xl flex items-center justify-center shadow-lg">
                    <i data-lucide="user-cog" class="w-6 h-6 text-white"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black italic tracking-tight">Edit Profil Pegawai</h3>
                    <p class="text-[9px] font-bold opacity-60 uppercase tracking-widest mt-0.5">Modifikasi data entitas sistem</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="relative w-10 h-10 flex items-center justify-center rounded-xl bg-white/10 hover:bg-red-500 transition-all border border-white/10">
                <i data-lucide="x" class="w-5 h-5 text-white"></i>
            </button>
        </div>

        <form id="editForm" method="POST" enctype="multipart/form-data" class="p-10">
            @csrf @method('PUT')
            
            <div class="flex flex-col md:flex-row gap-10">
                <!-- Side Photo & Minimal Info -->
                <div class="md:w-1/3 flex flex-col items-center">
                    <div class="relative group mb-6">
                        <div class="w-32 h-32 rounded-[32px] border-4 border-[#FCFBF9] bg-[#F5F4F2] overflow-hidden shadow-xl flex items-center justify-center text-[#8A8A8A]">
                            <img id="edit_avatar_preview" src="" class="hidden w-full h-full object-cover">
                            <i id="edit_avatar_placeholder" data-lucide="user" class="w-12 h-12 opacity-20"></i>
                        </div>
                        <label for="edit_photo_input" class="absolute -bottom-2 -right-2 bg-[#1E2432] p-2.5 rounded-xl shadow-lg cursor-pointer hover:bg-[#E85A4F] transition-all border-4 border-white">
                            <i data-lucide="camera" class="w-4 h-4 text-white"></i>
                            <input type="file" id="edit_photo_input" name="photo" class="hidden" onchange="previewEditImage(this)">
                        </label>
                    </div>
                    <button type="button" id="btnDeleteEmployeePhoto" onclick="confirmDeleteEmployeePhoto()" class="hidden text-[10px] font-black uppercase text-red-500 hover:underline">Hapus Foto</button>
                    
                    <div class="mt-8 w-full p-5 bg-[#FCFBF9] rounded-3xl border border-[#EFEFEF]">
                        <p class="text-[8px] font-black text-[#ABABAB] uppercase tracking-widest mb-1">Status Verifikasi</p>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                            <span class="text-[10px] font-bold text-[#1E2432]">Aktif & Sah</span>
                        </div>
                    </div>
                </div>

                <!-- Fields Grid -->
                <div class="flex-1 grid grid-cols-1 gap-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest ml-1">Nama Lengkap</label>
                            <input type="text" name="full_name" id="edit_full_name" required class="w-full px-5 py-3.5 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold text-[#1E2432] focus:border-[#E85A4F] outline-none transition-all shadow-sm">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest ml-1">NIP</label>
                            <input type="text" name="nip" id="edit_nip" required class="w-full px-5 py-3.5 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold text-[#1E2432] focus:border-[#E85A4F] outline-none transition-all shadow-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest ml-1">Jabatan</label>
                            <select name="position_id" id="edit_position_id" required class="w-full px-5 py-3.5 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold text-[#1E2432] focus:border-[#E85A4F] outline-none appearance-none cursor-pointer">
                                @foreach($positions as $pos)
                                    <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest ml-1">Unit Kerja</label>
                            <select name="work_unit_id" id="edit_work_unit_id" required class="w-full px-5 py-3.5 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold text-[#1E2432] focus:border-[#E85A4F] outline-none appearance-none cursor-pointer">
                                @foreach($workUnits as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest ml-1">Email Aktif</label>
                        <input type="email" name="email" id="edit_email" required class="w-full px-5 py-3.5 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold text-[#1E2432] focus:border-[#E85A4F] outline-none shadow-sm">
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest ml-1">Password (Kosongkan jika tidak diganti)</label>
                        <input type="password" name="password" placeholder="••••••••" class="w-full px-5 py-3.5 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold text-[#1E2432] focus:border-[#E85A4F] outline-none shadow-sm">
                    </div>
                </div>
            </div>

            <div class="mt-10 flex gap-4">
                <button type="submit" class="flex-1 bg-[#1E2432] text-white py-4 rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-[#E85A4F] transition-all shadow-xl active:scale-[0.98] flex items-center justify-center gap-3">
                    Simpan Perubahan <i data-lucide="check-circle" class="w-4 h-4"></i>
                </button>
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="px-8 py-4 rounded-2xl border border-[#EFEFEF] text-[#8A8A8A] font-black text-[10px] uppercase tracking-widest hover:bg-[#FCFBF9]">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const selectAll = document.getElementById('selectAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const selectedCountSpan = document.getElementById('selectedCount');

    function updateBulkUI() {
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
        selectedCountSpan.innerText = checkedCount;
        if (checkedCount > 0) {
            bulkDeleteBtn.classList.remove('hidden');
            bulkDeleteBtn.classList.add('flex');
        } else {
            bulkDeleteBtn.classList.add('hidden');
            bulkDeleteBtn.classList.remove('flex');
        }
    }

    selectAll.addEventListener('change', function() {
        rowCheckboxes.forEach(cb => { cb.checked = selectAll.checked; });
        updateBulkUI();
    });

    rowCheckboxes.forEach(cb => { cb.addEventListener('change', updateBulkUI); });

    bulkDeleteBtn.addEventListener('click', function() {
        Swal.fire({
            title: 'Konfirmasi Penghapusan?',
            text: "Anda akan menghapus " + document.querySelectorAll('.row-checkbox:checked').length + " entitas pegawai secara permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#E85A4F',
            cancelButtonColor: '#1E2432',
            confirmButtonText: 'Ya, Eksekusi!',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-[48px]' }
        }).then((result) => {
            if (result.isConfirmed) { document.getElementById('bulkForm').submit(); }
        });
    });

    let currentEmployeeId = null;
    function openEditModal(employee, email) {
        currentEmployeeId = employee.id;
        const modal = document.getElementById('editModal');
        const form = document.getElementById('editForm');
        form.action = `/employees/${employee.id}`;
        
        document.getElementById('edit_full_name').value = employee.full_name;
        document.getElementById('edit_nip').value = employee.nip;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_position_id').value = employee.position_id;
        document.getElementById('edit_work_unit_id').value = employee.work_unit_id;
        
        const preview = document.getElementById('edit_avatar_preview');
        const placeholder = document.getElementById('edit_avatar_placeholder');
        const btnDelete = document.getElementById('btnDeleteEmployeePhoto');
        
        if (employee.photo) {
            preview.src = employee.photo;
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
            btnDelete.classList.remove('hidden');
        } else {
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
            btnDelete.classList.add('hidden');
        }
        
        modal.classList.remove('hidden');
        lucide.createIcons();
    }

    function previewEditImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('edit_avatar_preview');
                const placeholder = document.getElementById('edit_avatar_placeholder');
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function confirmDeleteEmployeePhoto() {
        if (!currentEmployeeId) return;
        Swal.fire({
            title: 'Hapus Foto Pegawai?',
            text: "Media profil akan dihapus dari server.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#E85A4F',
            cancelButtonColor: '#8A8A8A',
            confirmButtonText: 'Hapus!',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-[40px]' }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/employees/${currentEmployeeId}/photo`;
                form.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function confirmIndividualDelete(id) {
        Swal.fire({
            title: 'Hapus Data Pegawai?',
            text: "Seluruh data dan dokumen pegawai ini akan dimusnahkan secara permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#E85A4F',
            cancelButtonColor: '#1E2432',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-[48px]' }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteForm-' + id).submit();
            }
        });
    }
</script>

@if(session('success'))
<script>
    Swal.fire({ icon: 'success', title: 'Berhasil Terproses', text: "{{ session('success') }}", confirmButtonColor: '#1E2432', customClass: { popup: 'rounded-[48px]' } });
</script>
@endif
@if(session('error'))
<script>
    Swal.fire({ icon: 'error', title: 'Operasi Gagal', text: "{{ session('error') }}", confirmButtonColor: '#E85A4F', customClass: { popup: 'rounded-[48px]' } });
</script>
@endif
@endsection
