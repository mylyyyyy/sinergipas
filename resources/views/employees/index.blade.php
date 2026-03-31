@extends('layouts.app')

@section('title', 'Data Pegawai')
@section('header-title', 'Manajemen Data Pegawai')

@section('content')
<div class="flex flex-col md:flex-row gap-6 justify-between items-start md:items-center mb-10">
    <!-- Search Bar -->
    <form action="{{ route('employees.index') }}" method="GET" class="relative w-full md:w-96 group">
        <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-[#8A8A8A] group-focus-within:text-[#E85A4F] transition-all"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama, NIP, atau Jabatan..." 
            class="w-full pl-12 pr-4 py-3.5 rounded-2xl border border-[#EFEFEF] bg-white text-sm outline-none focus:ring-2 focus:ring-[#E85A4F] focus:border-transparent transition-all shadow-sm group-hover:shadow-md">
    </form>

    <div class="flex gap-3 w-full md:w-auto">
        <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="flex-1 md:flex-none bg-blue-600 text-white px-6 py-3 rounded-2xl font-bold hover:bg-blue-700 transition-all flex items-center justify-center gap-2 shadow-lg shadow-blue-100">
            <i data-lucide="file-up" class="w-5 h-5"></i> Impor Excel
        </button>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="flex-1 md:flex-none bg-[#E85A4F] text-white px-6 py-3 rounded-2xl font-bold hover:bg-[#d44d42] transition-all flex items-center justify-center gap-2 shadow-lg shadow-red-100">
            <i data-lucide="plus" class="w-5 h-5"></i> Tambah Pegawai
        </button>
    </div>
</div>

<!-- Table Section -->
<div class="bg-white rounded-[40px] border border-[#EFEFEF] shadow-sm overflow-hidden">
    <div class="p-8 border-b border-[#EFEFEF] flex justify-between items-center bg-[#FCFBF9]/50">
        <h3 class="text-lg font-bold text-[#1E2432]">Daftar Seluruh Pegawai</h3>
        <div class="flex gap-2">
            <a href="{{ route('employees.export.excel') }}" class="p-2 text-[#8A8A8A] hover:text-green-600 hover:bg-green-50 rounded-xl transition-all" title="Ekspor Excel"><i data-lucide="file-spreadsheet" class="w-5 h-5"></i></a>
            <a href="{{ route('employees.export.pdf') }}" class="p-2 text-[#8A8A8A] hover:text-orange-600 hover:bg-orange-50 rounded-xl transition-all" title="Ekspor PDF"><i data-lucide="file-type-2" class="w-5 h-5"></i></a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#FCFBF9]">
                    <th class="px-8 py-5 text-xs font-bold text-[#8A8A8A] uppercase tracking-widest">Nama Pegawai</th>
                    <th class="px-8 py-5 text-xs font-bold text-[#8A8A8A] uppercase tracking-widest">NIP</th>
                    <th class="px-8 py-5 text-xs font-bold text-[#8A8A8A] uppercase tracking-widest">Jabatan</th>
                    <th class="px-8 py-5 text-xs font-bold text-[#8A8A8A] uppercase tracking-widest text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#EFEFEF]">
                @foreach($employees as $employee)
                <tr class="hover:bg-[#FCFBF9] transition-all group">
                    <td class="px-8 py-6">
                        <a href="{{ route('employees.show', $employee->id) }}" class="flex items-center gap-4 group/item text-sm font-bold text-[#1E2432] hover:text-[#E85A4F] transition-all">
                            <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center text-[#8A8A8A] group-hover/item:bg-[#E85A4F] group-hover/item:text-white transition-all overflow-hidden">
                                @if($employee->photo)
                                    <img src="{{ Storage::url($employee->photo) }}" class="w-full h-full object-cover">
                                @else
                                    <i data-lucide="user" class="w-5 h-5"></i>
                                @endif
                            </div>
                            {{ $employee->full_name }}
                        </a>
                    </td>
                    <td class="px-8 py-6 text-sm text-[#8A8A8A]">{{ $employee->nip }}</td>
                    <td class="px-8 py-6 text-sm text-[#8A8A8A] font-medium">{{ $employee->position }}</td>
                    <td class="px-8 py-6 text-sm text-center">
                        <div class="flex justify-center gap-2 opacity-0 group-hover:opacity-100 transition-all">
                            <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" onsubmit="return confirm('Hapus pegawai ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2.5 text-[#E85A4F] hover:bg-red-50 rounded-xl transition-all"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
                @if($employees->isEmpty())
                <tr>
                    <td colspan="4" class="px-8 py-20 text-center text-[#8A8A8A] italic">Data tidak ditemukan.</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-2xl rounded-[40px] p-10 shadow-2xl animate-in zoom-in duration-300">
        <div class="flex justify-between items-center mb-10">
            <h3 class="text-2xl font-bold text-[#1E2432]">Tambah Pegawai Baru</h3>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-[#8A8A8A] hover:text-[#1E2432]">
                <i data-lucide="x" class="w-8 h-8"></i>
            </button>
        </div>
        
        <form action="{{ route('employees.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @csrf
            <div class="space-y-2">
                <label class="text-xs font-bold text-[#1E2432] uppercase tracking-wider pl-1">Nama Lengkap</label>
                <input type="text" name="full_name" required placeholder="Nama Lengkap" class="w-full px-5 py-4 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm outline-none focus:ring-2 focus:ring-[#E85A4F]">
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold text-[#1E2432] uppercase tracking-wider pl-1">NIP</label>
                <input type="text" name="nip" required placeholder="NIP Pegawai" class="w-full px-5 py-4 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm outline-none focus:ring-2 focus:ring-[#E85A4F]">
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold text-[#1E2432] uppercase tracking-wider pl-1">Email Akun</label>
                <input type="email" name="email" required placeholder="email@pas.id" class="w-full px-5 py-4 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm outline-none focus:ring-2 focus:ring-[#E85A4F]">
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold text-[#1E2432] uppercase tracking-wider pl-1">Jabatan</label>
                <input type="text" name="position" required placeholder="Jabatan" class="w-full px-5 py-4 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm outline-none focus:ring-2 focus:ring-[#E85A4F]">
            </div>
            <div class="md:col-span-2 pt-4">
                <button type="submit" class="w-full bg-[#E85A4F] text-white py-5 rounded-[24px] font-bold hover:bg-[#d44d42] transition-all shadow-xl shadow-red-200 active:scale-[0.98]">
                    Daftarkan Pegawai Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Import Modal (Tetap Ada) -->
<div id="importModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-md rounded-[40px] p-10 shadow-2xl animate-in zoom-in duration-300">
        <div class="flex justify-between items-center mb-8 text-[#1E2432]">
            <h3 class="text-xl font-bold">Impor Massal</h3>
            <button onclick="document.getElementById('importModal').classList.add('hidden')">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <form action="{{ route('employees.import.excel') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="p-6 bg-blue-50 rounded-2xl border border-blue-100 text-blue-700 text-xs leading-relaxed">
                <p class="font-bold mb-2">📌 Panduan Kolom Excel:</p>
                <p>NIP, Nama Lengkap, Jabatan, Unit Kerja, Email, NIK, No. WhatsApp</p>
            </div>
            <input type="file" name="file" required class="w-full px-5 py-4 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm">
            <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-100">
                Mulai Proses Impor
            </button>
        </form>
    </div>
</div>

@if(session('success'))
<script>
    Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", confirmButtonColor: '#E85A4F' });
</script>
@endif
@endsection
