@extends('layouts.app')

@section('title', 'Folder ' . $employee->full_name)
@section('header-title', 'Dokumen ' . $employee->full_name)

@section('content')
<form id="bulkDocForm" action="{{ route('documents.bulk-action') }}" method="POST">
    @csrf
    <input type="hidden" name="action" id="bulkActionInput" value="">

    <!-- Sub Header & Tabs -->
    <div class="mb-12 flex flex-col md:flex-row items-center justify-between gap-8">
        <div class="flex items-center gap-3 text-sm">
            <a href="{{ route('documents.index') }}" class="text-[#8A8A8A] hover:text-[#E85A4F] transition-all font-bold uppercase tracking-widest text-[10px]">Pusat Dokumen</a>
            <span class="text-[#8A8A8A]">/</span>
            <span class="text-[#E85A4F] font-black italic">{{ $employee->full_name }}</span>
        </div>
        
        <div class="flex gap-3 items-center">
            <!-- Dynamic Bulk Actions -->
            <div id="bulkActions" class="hidden gap-3 animate-in fade-in zoom-in duration-300">
                <button type="button" onclick="submitBulk('unlock')" class="bg-gray-100 text-[#1E2432] px-5 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-gray-200 transition-all border border-[#EFEFEF]">
                    Buka Kunci
                </button>
                <button type="button" onclick="submitBulk('lock')" class="bg-blue-600 text-white px-5 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg shadow-blue-100">
                    Kunci
                </button>
                <button type="button" onclick="submitBulk('delete')" class="bg-red-600 text-white px-5 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-red-700 transition-all shadow-lg shadow-red-100">
                    Hapus
                </button>
            </div>

            <button type="button" onclick="document.getElementById('uploadModal').classList.remove('hidden')" 
                class="bg-[#E85A4F] text-white px-8 py-3.5 rounded-2xl font-black hover:bg-[#d44d42] transition-all flex items-center gap-2 shadow-xl shadow-red-100 active:scale-90">
                <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                Unggah File
            </button>
        </div>
    </div>

    <!-- Category Tabs -->
    <div class="flex bg-white p-1.5 rounded-[24px] border border-[#EFEFEF] shadow-sm overflow-x-auto max-w-full mb-10 inline-flex">
        <a href="{{ route('documents.employee', $employee->id) }}" 
            class="px-8 py-3 rounded-[20px] text-[10px] font-black uppercase tracking-widest transition-all {{ !request('category_id') ? 'bg-[#1E2432] text-white shadow-lg' : 'text-[#8A8A8A] hover:bg-[#FCFBF9]' }}">
            Semua File
        </a>
        @foreach($categories as $cat)
        <a href="{{ route('documents.employee', ['employee' => $employee->id, 'category_id' => $cat->id]) }}" 
            class="px-8 py-3 rounded-[20px] text-[10px] font-black uppercase tracking-widest transition-all {{ request('category_id') == $cat->id ? 'bg-[#E85A4F] text-white shadow-lg' : 'text-[#8A8A8A] hover:bg-[#FCFBF9]' }}">
            {{ $cat->name }}
        </a>
        @endforeach
    </div>

    <!-- Files Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        @foreach($documents as $doc)
        <div class="group relative bg-white p-8 rounded-[40px] border border-[#EFEFEF] hover:border-[#E85A4F] hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 flex flex-col justify-between h-[280px]">
            <!-- Checkbox for Bulk (Always Visible for Easy Interaction) -->
            <div class="absolute top-6 left-6 z-10">
                <input type="checkbox" name="ids[]" value="{{ $doc->id }}" class="doc-checkbox w-6 h-6 rounded-xl border-2 border-[#EFEFEF] text-[#E85A4F] focus:ring-0 cursor-pointer transition-all checked:border-[#E85A4F]">
            </div>

            <div class="flex justify-end items-start mb-4">
                <div class="flex gap-1 flex-wrap justify-end opacity-0 group-hover:opacity-100 transition-all duration-300">
                    @if($doc->status === 'pending')
                    <button type="button" onclick="verifyDoc({{ $doc->id }})" class="p-2 text-green-600 bg-green-50 hover:bg-green-600 hover:text-white rounded-xl transition-all" title="Verifikasi">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                    </button>
                    @endif
                    
                    <button type="button" onclick="toggleLock({{ $doc->id }})" class="p-2 {{ $doc->is_locked ? 'text-red-600 bg-red-100' : 'text-gray-400 bg-gray-50' }} hover:bg-red-600 hover:text-white rounded-xl transition-all" title="Kunci/Buka">
                        <i data-lucide="{{ $doc->is_locked ? 'lock' : 'unlock' }}" class="w-4 h-4"></i>
                    </button>

                    <button type="button" onclick="openPreview('{{ route('documents.preview', $doc->id) }}', '{{ $doc->title }}')" class="p-2 text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-xl no-loader"><i data-lucide="eye" class="w-4 h-4"></i></button>
                    <a href="{{ route('documents.download', $doc->id) }}" target="_blank" class="p-2 text-purple-600 bg-purple-50 hover:bg-purple-600 hover:text-white rounded-xl no-loader"><i data-lucide="download" class="w-4 h-4"></i></a>
                </div>
            </div>

            <div class="flex flex-col items-center justify-center flex-1 mb-4">
                <div class="w-16 h-16 bg-[#F5F4F2] rounded-3xl flex items-center justify-center text-[#8A8A8A] group-hover:bg-[#E85A4F] group-hover:text-white transition-all duration-500 shadow-sm">
                    @if(str_contains($doc->file_path, '.pdf'))
                        <i data-lucide="file-text" class="w-8 h-8 text-red-500 group-hover:text-white"></i>
                    @elseif(str_contains($doc->file_path, '.xls'))
                        <i data-lucide="file-spreadsheet" class="w-8 h-8 text-green-600 group-hover:text-white"></i>
                    @else
                        <i data-lucide="file" class="w-8 h-8"></i>
                    @endif
                </div>
            </div>

            <div>
                <h4 class="text-sm font-black text-[#1E2432] truncate text-center mb-2" title="{{ $doc->title }}">{{ $doc->title }}</h4>
                <div class="flex items-center justify-between mt-4 pt-4 border-t border-[#F5F4F2]">
                    <span class="text-[10px] font-black {{ $doc->status === 'verified' ? 'text-blue-600' : 'text-[#8A8A8A]' }} uppercase tracking-widest">{{ $doc->status }}</span>
                    <span class="text-[10px] font-bold text-[#ABABAB]">{{ $doc->created_at->format('d/m/y') }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</form>

<script>
    const docCheckboxes = document.querySelectorAll('.doc-checkbox');
    const bulkActionsDiv = document.getElementById('bulkActions');

    docCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const checkedCount = document.querySelectorAll('.doc-checkbox:checked').length;
            if (checkedCount > 0) {
                bulkActionsDiv.classList.remove('hidden');
                bulkActionsDiv.classList.add('flex');
            } else {
                bulkActionsDiv.classList.add('hidden');
                bulkActionsDiv.classList.remove('flex');
            }
        });
    });

    function submitBulk(action) {
        Swal.fire({
            title: 'Konfirmasi Aksi Massal',
            text: "Anda akan menjalankan " + action + " pada dokumen terpilih.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#E85A4F',
            confirmButtonText: 'Ya, Jalankan!',
            customClass: { popup: 'rounded-[32px]' }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('bulkActionInput').value = action;
                document.getElementById('bulkDocForm').submit();
            }
        });
    }

    function verifyDoc(id) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/documents/${id}/verify`;
        form.innerHTML = `@csrf`;
        document.body.appendChild(form);
        form.submit();
    }

    function toggleLock(id) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/documents/${id}/toggle-lock`;
        form.innerHTML = `@csrf`;
        document.body.appendChild(form);
        form.submit();
    }
</script>

<!-- Upload & Preview Modals (Tetap Ada) -->
<div id="uploadModal" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-md">
    <div class="bg-white w-full max-w-lg rounded-[48px] p-12 shadow-2xl animate-in zoom-in duration-300">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h3 class="text-2xl font-black text-[#1E2432] tracking-tight">Unggah Arsip Baru</h3>
                <p class="text-[10px] font-bold text-[#8A8A8A] uppercase tracking-widest mt-1">Ke Akun: {{ $employee->full_name }}</p>
            </div>
            <button onclick="document.getElementById('uploadModal').classList.add('hidden')" class="bg-[#FCFBF9] p-3 rounded-2xl text-[#8A8A8A] hover:text-red-500 transition-all border border-[#EFEFEF]">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            <input type="hidden" name="employee_id" value="{{ $employee->id }}">
            <div class="space-y-3">
                <label class="text-[10px] font-black text-[#1E2432] uppercase tracking-[0.2em] ml-1">Jenis Dokumen</label>
                <select name="document_category_id" required class="w-full px-6 py-4 rounded-3xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold outline-none focus:ring-4 focus:ring-red-500/5 appearance-none cursor-pointer">
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-3">
                <label class="text-[10px] font-black text-[#1E2432] uppercase tracking-[0.2em] ml-1">Judul Dokumen</label>
                <input type="text" name="title" required placeholder="Contoh: SK Pengangkatan 2026" class="w-full px-6 py-4 rounded-3xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold outline-none focus:ring-4 focus:ring-red-500/5 transition-all">
            </div>
            <div class="space-y-3">
                <label class="text-[10px] font-black text-[#1E2432] uppercase tracking-[0.2em] ml-1">Pilih File</label>
                <div class="relative group">
                    <input type="file" name="file" required class="w-full px-6 py-10 rounded-3xl border-2 border-dashed border-[#EFEFEF] bg-[#FCFBF9] text-xs font-bold text-[#8A8A8A] file:hidden cursor-pointer hover:border-[#E85A4F] transition-all text-center">
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none opacity-60 group-hover:opacity-100 transition-opacity">
                        <i data-lucide="file-up" class="w-10 h-10 text-[#E85A4F] mb-3"></i>
                        <span class="text-[10px] uppercase font-black tracking-tighter">Klik atau Seret File Kesini</span>
                    </div>
                </div>
            </div>
            <button type="submit" class="w-full bg-[#E85A4F] text-white py-5 rounded-[28px] font-black text-lg hover:bg-[#d44d42] transition-all shadow-xl shadow-red-200 active:scale-95 flex items-center justify-center gap-3">
                Proses Sinkronisasi <i data-lucide="zap" class="w-5 h-5"></i>
            </button>
        </form>
    </div>
</div>

<div id="previewModal" class="fixed inset-0 bg-black/80 hidden flex items-center justify-center z-[100] p-10 backdrop-blur-xl">
    <div class="bg-white w-full h-full max-w-6xl rounded-[48px] overflow-hidden flex flex-col shadow-2xl">
        <div class="p-8 border-b border-[#EFEFEF] flex justify-between items-center bg-[#FCFBF9]/50">
            <h3 id="previewTitle" class="text-xl font-black text-[#1E2432]">Pratinjau Dokumen</h3>
            <button onclick="document.getElementById('previewModal').classList.add('hidden')" class="bg-white p-3 rounded-2xl shadow-sm border border-[#EFEFEF] hover:bg-red-50 hover:text-red-500 transition-all">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <div class="flex-1 bg-gray-100"><iframe id="previewFrame" src="" class="w-full h-full border-none"></iframe></div>
    </div>
</div>

<script>
    function openPreview(url, title) {
        document.getElementById('previewTitle').innerText = title;
        document.getElementById('previewFrame').src = url;
        document.getElementById('previewModal').classList.remove('hidden');
    }
</script>

@if(session('success'))
<script>
    Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", confirmButtonColor: '#E85A4F', customClass: { popup: 'rounded-[40px]' } });
</script>
@endif
@endsection
