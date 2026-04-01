@extends('layouts.app')

@section('title', 'Data Pegawai')
@section('header-title', 'Manajemen Data Pegawai')

@section('content')
<form id="bulkForm" action="{{ route('employees.bulk-destroy') }}" method="POST">
    @csrf
    @method('DELETE')
    
    <div class="flex flex-col md:flex-row gap-6 justify-between items-start md:items-center mb-10">
        <!-- Search Bar -->
        <div class="relative w-full md:w-96 group">
            <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-[#8A8A8A] group-focus-within:text-[#E85A4F] transition-all"></i>
            <input type="text" id="searchInput" placeholder="Cari Nama, NIP, atau Jabatan..." 
                class="w-full pl-12 pr-4 py-3.5 rounded-2xl border border-[#EFEFEF] bg-white text-sm outline-none focus:ring-2 focus:ring-[#E85A4F] focus:border-transparent transition-all shadow-sm group-hover:shadow-md">
        </div>

        <div class="flex gap-3 w-full md:w-auto">
            <button type="button" id="bulkDeleteBtn" class="hidden bg-red-50 text-red-600 px-6 py-3 rounded-2xl font-bold hover:bg-red-600 hover:text-white transition-all items-center justify-center gap-2 shadow-lg shadow-red-100">
                <i data-lucide="trash-2" class="w-5 h-5"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
            </button>
            <button type="button" onclick="document.getElementById('importModal').classList.remove('hidden')" class="flex-1 md:flex-none bg-blue-600 text-white px-6 py-3 rounded-2xl font-bold hover:bg-blue-700 transition-all flex items-center justify-center gap-2 shadow-lg shadow-blue-100">
                <i data-lucide="file-up" class="w-5 h-5"></i> Impor Excel
            </button>
            <button type="button" onclick="document.getElementById('addModal').classList.remove('hidden')" class="flex-1 md:flex-none bg-[#E85A4F] text-white px-6 py-3 rounded-2xl font-bold hover:bg-[#d44d42] transition-all flex items-center justify-center gap-2 shadow-lg shadow-red-100">
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
                        <th class="px-8 py-5 w-10">
                            <input type="checkbox" id="selectAll" class="w-5 h-5 rounded-lg border-[#EFEFEF] text-[#E85A4F] focus:ring-0">
                        </th>
                        <th class="px-4 py-5 text-xs font-bold text-[#8A8A8A] uppercase tracking-widest">Nama Pegawai</th>
                        <th class="px-8 py-5 text-xs font-bold text-[#8A8A8A] uppercase tracking-widest">NIP</th>
                        <th class="px-8 py-5 text-xs font-bold text-[#8A8A8A] uppercase tracking-widest">Jabatan</th>
                        <th class="px-8 py-5 text-xs font-bold text-[#8A8A8A] uppercase tracking-widest text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EFEFEF]">
                    @foreach($employees as $employee)
                    <tr class="hover:bg-[#FCFBF9] transition-all group">
                        <td class="px-8 py-6">
                            <input type="checkbox" name="ids[]" value="{{ $employee->id }}" class="row-checkbox w-5 h-5 rounded-lg border-[#EFEFEF] text-[#E85A4F] focus:ring-0">
                        </td>
                        <td class="px-4 py-6">
                            <a href="{{ route('employees.show', $employee->id) }}" class="flex items-center gap-4 group/item text-sm font-bold text-[#1E2432] hover:text-[#E85A4F] transition-all">
                                <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center text-[#8A8A8A] group-hover/item:bg-[#E85A4F] group-hover/item:text-white transition-all overflow-hidden text-xs">
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
                            <div class="flex justify-center items-center gap-2 opacity-0 group-hover:opacity-100 transition-all">
                                @php
                                    $waMessage = "Halo " . $employee->full_name . ", mohon segera unggah dokumen SKP Anda di sistem Sinergi PAS Jombang. Terima kasih.";
                                    $waLink = "https://wa.me/" . preg_replace('/[^0-9]/', '', '628123456789') . "?text=" . urlencode($waMessage);
                                @endphp
                                <a href="{{ $waLink }}" target="_blank" class="w-9 h-9 flex items-center justify-center text-green-600 hover:bg-green-50 rounded-xl transition-all" title="Kirim Pengingat WA">
                                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                                </a>
                                <button type="button" onclick="openEditModal({{ $employee->toJson() }}, '{{ $employee->user->email }}')" class="w-9 h-9 flex items-center justify-center text-blue-500 hover:bg-blue-50 rounded-xl transition-all">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </button>
                                <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" onsubmit="return confirm('Hapus pegawai ini?')" class="m-0 p-0 no-loader">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-9 h-9 flex items-center justify-center text-[#E85A4F] hover:bg-red-50 rounded-xl transition-all">
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
        <div class="p-8 bg-[#FCFBF9]/50 border-t border-[#EFEFEF]">
            {{ $employees->links() }}
        </div>
    </div>
</form>

<!-- Modal scripts & HTML tetap sama -->
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
        rowCheckboxes.forEach(cb => {
            cb.checked = selectAll.checked;
        });
        updateBulkUI();
    });

    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkUI);
    });

    bulkDeleteBtn.addEventListener('click', function() {
        Swal.fire({
            title: 'Hapus Massal?',
            text: "Anda akan menghapus " + document.querySelectorAll('.row-checkbox:checked').length + " data pegawai terpilih secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#E85A4F',
            cancelButtonColor: '#8A8A8A',
            confirmButtonText: 'Ya, Hapus Semua!',
            customClass: { popup: 'rounded-[32px]' }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('bulkForm').submit();
            }
        });
    });
</script>

<!-- Add Modal, Import Modal, Edit Modal placeholder (Sesuai kode sebelumnya) -->
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
            <div class="space-y-2">
                <label class="text-xs font-bold text-[#1E2432] uppercase tracking-wider pl-1">Kata Sandi Akun</label>
                <input type="password" name="password" required placeholder="Minimal 8 Karakter" class="w-full px-5 py-4 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm outline-none focus:ring-2 focus:ring-[#E85A4F]">
            </div>
            <div class="md:col-span-2 pt-4">
                <button type="submit" class="w-full bg-[#E85A4F] text-white py-5 rounded-[24px] font-bold hover:bg-[#d44d42] transition-all shadow-xl shadow-red-200 active:scale-[0.98]">
                    Daftarkan Pegawai Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

<div id="importModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-md rounded-[40px] p-10 shadow-2xl animate-in zoom-in duration-300">
        <div class="flex justify-between items-center mb-8 text-[#1E2432]">
            <h3 class="text-xl font-bold">Impor Massal</h3>
            <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')">
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

<div id="editModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-2xl rounded-[40px] p-10 shadow-2xl animate-in zoom-in duration-300">
        <div class="flex justify-between items-center mb-10">
            <h3 class="text-2xl font-bold text-[#1E2432]">Edit Data Pegawai</h3>
            <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="text-[#8A8A8A] hover:text-[#1E2432]">
                <i data-lucide="x" class="w-8 h-8"></i>
            </button>
        </div>
        <form id="editForm" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @csrf @method('PUT')
            <div class="space-y-2">
                <label class="text-xs font-bold text-[#1E2432] uppercase tracking-wider pl-1">Nama Lengkap</label>
                <input type="text" name="full_name" id="edit_full_name" required class="w-full px-5 py-4 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm outline-none focus:ring-2 focus:ring-[#E85A4F]">
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold text-[#1E2432] uppercase tracking-wider pl-1">NIP</label>
                <input type="text" name="nip" id="edit_nip" required class="w-full px-5 py-4 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm outline-none focus:ring-2 focus:ring-[#E85A4F]">
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold text-[#1E2432] uppercase tracking-wider pl-1">Email Akun</label>
                <input type="email" name="email" id="edit_email" required class="w-full px-5 py-4 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm outline-none focus:ring-2 focus:ring-[#E85A4F]">
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold text-[#1E2432] uppercase tracking-wider pl-1">Jabatan</label>
                <input type="text" name="position" id="edit_position" required class="w-full px-5 py-4 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm outline-none focus:ring-2 focus:ring-[#E85A4F]">
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold text-[#1E2432] uppercase tracking-wider pl-1">Kata Sandi Baru</label>
                <input type="password" name="password" placeholder="Kosongkan jika tidak ingin ganti" class="w-full px-5 py-4 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm outline-none focus:ring-2 focus:ring-[#E85A4F]">
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold text-[#1E2432] uppercase tracking-wider pl-1">Foto Profil (JPEG/PNG)</label>
                <input type="file" name="photo" class="w-full px-5 py-3 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-xs font-bold">
            </div>
            <div class="md:col-span-2 pt-4">
                <button type="submit" class="w-full bg-[#1E2432] text-white py-5 rounded-[24px] font-bold hover:bg-[#343b4d] transition-all shadow-xl active:scale-[0.98]">
                    Perbarui Data Pegawai
                </button>
            </div>
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
        document.getElementById('edit_position').value = employee.position;
        modal.classList.remove('hidden');
    }
</script>

@if(session('success'))
<script>
    Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", confirmButtonColor: '#E85A4F', customClass: { popup: 'rounded-[40px]' } });
</script>
@endif
@if(session('error'))
<script>
    Swal.fire({ icon: 'error', title: 'Ups!', text: "{{ session('error') }}", confirmButtonColor: '#E85A4F', customClass: { popup: 'rounded-[40px]' } });
</script>
@endif
@endsection
