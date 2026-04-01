@extends('layouts.app')

@section('title', 'Folder ' . $employee->full_name)
@section('header-title', 'Dokumen ' . $employee->full_name)

@section('content')
<div class="mb-8 flex items-center justify-between">
    <div class="flex items-center gap-3 text-sm">
        <a href="{{ route('documents.index') }}" class="text-[#8A8A8A] hover:text-[#E85A4F] transition-all">Semua Folder</a>
        <span class="text-[#8A8A8A]">/</span>
        <span class="text-[#E85A4F] font-semibold">{{ $employee->full_name }}</span>
    </div>
    
    <!-- Modal Trigger -->
    <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" 
        class="bg-[#E85A4F] text-white px-6 py-3 rounded-2xl font-bold hover:bg-[#d44d42] transition-all flex items-center gap-2 shadow-lg shadow-red-100">
        <i data-lucide="upload-cloud" class="w-5 h-5"></i>
        Unggah File
    </button>
</div>

<!-- Files Grid -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6">
    @foreach($documents as $doc)
    <div class="group bg-white p-6 rounded-3xl border border-[#EFEFEF] hover:shadow-xl transition-all duration-300">
        <div class="flex justify-between items-start mb-6">
            <div class="w-12 h-12 bg-[#F5F4F2] rounded-xl flex items-center justify-center text-[#8A8A8A]">
                @if(str_contains($doc->file_path, '.pdf'))
                    <i data-lucide="file-text" class="text-red-500 w-6 h-6"></i>
                @elseif(str_contains($doc->file_path, '.xls'))
                    <i data-lucide="file-spreadsheet" class="text-green-600 w-6 h-6"></i>
                @else
                    <i data-lucide="file" class="w-6 h-6"></i>
                @endif
            </div>
            <div class="flex gap-1">
                <button onclick="openPreview('{{ Storage::url($doc->file_path) }}', '{{ $doc->title }}')" class="p-2 text-green-600 hover:bg-green-50 rounded-lg no-loader" title="Pratinjau"><i data-lucide="eye" class="w-4 h-4"></i></button>
                <a href="{{ route('documents.download', $doc->id) }}" target="_blank" class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg no-loader" title="Unduh"><i data-lucide="download" class="w-4 h-4"></i></a>
                <form action="{{ route('documents.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Hapus file ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-2 text-[#E85A4F] hover:bg-red-50 rounded-lg"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                </form>
            </div>
        </div>
        <h4 class="text-sm font-bold text-[#1E2432] truncate" title="{{ $doc->title }}">{{ $doc->title }}</h4>
        <div class="flex items-center justify-between mt-4">
            <span class="text-[10px] font-bold text-[#8A8A8A] uppercase tracking-widest">{{ $doc->category->name }}</span>
            <span class="text-[10px] text-[#ABABAB]">{{ $doc->created_at->format('d M Y') }}</span>
        </div>
    </div>
    @endforeach
</div>

@if($documents->isEmpty())
<div class="py-20 text-center bg-white rounded-3xl border border-dashed border-[#EFEFEF]">
    <p class="text-[#8A8A8A]">Folder ini kosong.</p>
</div>
@endif

<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-lg rounded-[32px] p-10 shadow-2xl animate-in zoom-in duration-300">
        <div class="flex justify-between items-center mb-8">
            <h3 class="text-xl font-bold text-[#1E2432]">Unggah ke {{ $employee->full_name }}</h3>
            <button onclick="document.getElementById('uploadModal').classList.add('hidden')" class="text-[#8A8A8A] hover:text-[#1E2432]">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        
        <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <input type="hidden" name="employee_id" value="{{ $employee->id }}">
            
            <div class="space-y-2">
                <label class="text-xs font-bold text-[#1E2432] uppercase tracking-wider">Pilih Kategori</label>
                <select name="document_category_id" required class="w-full px-5 py-3.5 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm outline-none focus:ring-2 focus:ring-[#E85A4F]">
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="space-y-2">
                <label class="text-xs font-bold text-[#1E2432] uppercase tracking-wider">Judul File</label>
                <input type="text" name="title" required placeholder="Nama dokumen" class="w-full px-5 py-3.5 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm">
            </div>
            
            <div class="space-y-2">
                <label class="text-xs font-bold text-[#1E2432] uppercase tracking-wider">Pilih File</label>
                <input type="file" name="file" required class="w-full px-5 py-3 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm">
            </div>

            <button type="submit" class="w-full bg-[#E85A4F] text-white py-4 rounded-2xl font-bold hover:bg-[#d44d42] transition-all shadow-lg shadow-red-200">
                Mulai Unggah
            </button>
        </form>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="fixed inset-0 bg-black/80 hidden flex items-center justify-center z-[100] p-10 backdrop-blur-xl">
    <div class="bg-white w-full h-full max-w-6xl rounded-[48px] overflow-hidden flex flex-col shadow-2xl">
        <div class="p-8 border-b border-[#EFEFEF] flex justify-between items-center bg-[#FCFBF9]/50">
            <h3 id="previewTitle" class="text-xl font-black text-[#1E2432]">Pratinjau Dokumen</h3>
            <button onclick="document.getElementById('previewModal').classList.add('hidden')" class="bg-white p-3 rounded-2xl shadow-sm border border-[#EFEFEF] hover:bg-red-50 hover:text-red-500 transition-all">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <div class="flex-1 bg-gray-100">
            <iframe id="previewFrame" src="" class="w-full h-full border-none"></iframe>
        </div>
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
    Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", confirmButtonColor: '#E85A4F' });
</script>
@endif
@endsection
