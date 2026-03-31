@extends('layouts.app')

@section('title', 'Data Pegawai')
@section('header-title', 'Manajemen Data Pegawai')

@section('content')
<!-- Add Employee Section -->
<div class="bg-white p-8 rounded-2xl border border-[#EFEFEF] shadow-sm mb-10">
    <h3 class="text-lg font-bold text-[#1E2432] mb-6">Tambah Pegawai Baru</h3>
    <form action="{{ route('employees.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
        @csrf
        <div class="space-y-2">
            <label class="text-xs font-bold text-[#1E2432] uppercase tracking-wider">Nama Lengkap</label>
            <input type="text" name="full_name" required placeholder="Nama Lengkap" class="w-full px-4 py-3 rounded-xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm">
        </div>
        <div class="space-y-2">
            <label class="text-xs font-bold text-[#1E2432] uppercase tracking-wider">NIP</label>
            <input type="text" name="nip" required placeholder="NIP Pegawai" class="w-full px-4 py-3 rounded-xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm">
        </div>
        <div class="space-y-2">
            <label class="text-xs font-bold text-[#1E2432] uppercase tracking-wider">Email Akun</label>
            <input type="email" name="email" required placeholder="email@pas.id" class="w-full px-4 py-3 rounded-xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm">
        </div>
        <div class="space-y-2">
            <label class="text-xs font-bold text-[#1E2432] uppercase tracking-wider">Jabatan</label>
            <input type="text" name="position" required placeholder="Jabatan" class="w-full px-4 py-3 rounded-xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm">
        </div>
        <div class="md:col-span-4">
            <button type="submit" class="bg-[#E85A4F] text-white px-8 py-3 rounded-xl font-bold hover:bg-[#d44d42] transition-all shadow-lg shadow-red-100">
                Simpan Pegawai
            </button>
        </div>
    </form>
</div>

<!-- Table Section -->
<div class="bg-white rounded-2xl border border-[#EFEFEF] shadow-sm overflow-hidden">
    <div class="p-8 border-b border-[#EFEFEF] flex justify-between items-center">
        <h3 class="text-lg font-bold text-[#1E2432]">Daftar Seluruh Pegawai</h3>
        <div class="flex gap-3">
            <a href="{{ route('employees.export.excel') }}" class="bg-green-600 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-green-700 transition-all flex items-center gap-2">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i> Ekspor Excel
            </a>
            <a href="{{ route('employees.export.pdf') }}" class="bg-orange-600 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-orange-700 transition-all flex items-center gap-2">
                <i data-lucide="file-type-2" class="w-4 h-4"></i> Ekspor PDF
            </a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#FCFBF9]">
                    <th class="px-8 py-4 text-xs font-bold text-[#8A8A8A] uppercase tracking-wider">Nama Pegawai</th>
                    <th class="px-8 py-4 text-xs font-bold text-[#8A8A8A] uppercase tracking-wider">NIP</th>
                    <th class="px-8 py-4 text-xs font-bold text-[#8A8A8A] uppercase tracking-wider">Jabatan</th>
                    <th class="px-8 py-4 text-xs font-bold text-[#8A8A8A] uppercase tracking-wider">Email</th>
                    <th class="px-8 py-4 text-xs font-bold text-[#8A8A8A] uppercase tracking-wider text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#EFEFEF]">
                @foreach($employees as $employee)
                <tr class="hover:bg-[#FCFBF9] transition-all">
                    <td class="px-8 py-5 text-sm font-semibold text-[#1E2432]">{{ $employee->full_name }}</td>
                    <td class="px-8 py-5 text-sm text-[#8A8A8A]">{{ $employee->nip }}</td>
                    <td class="px-8 py-5 text-sm text-[#8A8A8A]">{{ $employee->position }}</td>
                    <td class="px-8 py-5 text-sm text-[#8A8A8A]">{{ $employee->user->email }}</td>
                    <td class="px-8 py-5 text-sm text-center">
                        <div class="flex justify-center gap-3">
                            <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" onsubmit="return confirm('Hapus pegawai ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-[#E85A4F] hover:text-[#d44d42] transition-all">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
                @if($employees->isEmpty())
                <tr>
                    <td colspan="5" class="px-8 py-10 text-center text-[#8A8A8A] text-sm italic">Belum ada data pegawai.</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: "{{ session('success') }}",
        confirmButtonColor: '#E85A4F',
    });
</script>
@endif
@endsection
