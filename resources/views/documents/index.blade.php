@extends('layouts.app')

@section('title', 'Manajemen Dokumen')
@section('header-title', 'Manajemen Dokumen Pegawai')

@section('content')
<!-- Upload Document Section -->
<div class="bg-white p-8 rounded-2xl border border-[#EFEFEF] shadow-sm mb-10">
    <h3 class="text-lg font-bold text-[#1E2432] mb-6">Unggah Dokumen Baru</h3>
    <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
        @csrf
        <div class="space-y-2">
            <label class="text-xs font-bold text-[#1E2432] uppercase tracking-wider">Pilih Pegawai</label>
            <select name="employee_id" required class="w-full px-4 py-3 rounded-xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm outline-none">
                <option value="">Pilih Pegawai</option>
                @foreach($employees as $employee)
                <option value="{{ $employee->id }}">{{ $employee->full_name }} ({{ $employee->nip }})</option>
                @endforeach
            </select>
        </div>
        <div class="space-y-2">
            <label class="text-xs font-bold text-[#1E2432] uppercase tracking-wider">Kategori</label>
            <select name="document_category_id" required class="w-full px-4 py-3 rounded-xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm outline-none">
                <option value="">Pilih Kategori</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="space-y-2">
            <label class="text-xs font-bold text-[#1E2432] uppercase tracking-wider">Judul Dokumen</label>
            <input type="text" name="title" required placeholder="Contoh: Slip Gaji Maret 2026" class="w-full px-4 py-3 rounded-xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm">
        </div>
        <div class="space-y-2">
            <label class="text-xs font-bold text-[#1E2432] uppercase tracking-wider">File (PDF/Excel/Word)</label>
            <input type="file" name="file" required class="w-full px-4 py-2.5 rounded-xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm">
        </div>
        <div class="md:col-span-2">
            <button type="submit" class="bg-[#E85A4F] text-white px-10 py-3.5 rounded-2xl font-bold hover:bg-[#d44d42] transition-all shadow-lg shadow-red-100">
                Unggah Dokumen
            </button>
        </div>
    </form>
</div>

<!-- Documents List -->
<div class="bg-white rounded-2xl border border-[#EFEFEF] shadow-sm overflow-hidden">
    <div class="p-8 border-b border-[#EFEFEF]">
        <h3 class="text-lg font-bold text-[#1E2432]">Daftar Dokumen Terunggah</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#FCFBF9]">
                    <th class="px-8 py-4 text-xs font-bold text-[#8A8A8A] uppercase tracking-wider">Pegawai</th>
                    <th class="px-8 py-4 text-xs font-bold text-[#8A8A8A] uppercase tracking-wider">Kategori</th>
                    <th class="px-8 py-4 text-xs font-bold text-[#8A8A8A] uppercase tracking-wider">Judul Dokumen</th>
                    <th class="px-8 py-4 text-xs font-bold text-[#8A8A8A] uppercase tracking-wider text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#EFEFEF]">
                @foreach($documents as $doc)
                <tr class="hover:bg-[#FCFBF9] transition-all">
                    <td class="px-8 py-5 text-sm font-semibold text-[#1E2432]">{{ $doc->employee->full_name }}</td>
                    <td class="px-8 py-5">
                        <span class="px-3 py-1 bg-gray-100 text-[#1E2432] text-[10px] font-bold rounded-full uppercase">{{ $doc->category->name }}</span>
                    </td>
                    <td class="px-8 py-5 text-sm text-[#8A8A8A]">{{ $doc->title }}</td>
                    <td class="px-8 py-5 text-sm text-center">
                        <div class="flex justify-center gap-3">
                            <a href="{{ route('documents.download', $doc->id) }}" class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg"><i data-lucide="download" class="w-4 h-4"></i></a>
                            <form action="{{ route('documents.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Hapus dokumen ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-[#E85A4F] hover:bg-red-50 rounded-lg">
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
</div>

@if(session('success'))
<script>
    Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", confirmButtonColor: '#E85A4F' });
</script>
@endif
@endsection
