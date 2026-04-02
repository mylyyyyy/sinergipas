@extends('layouts.app')

@section('title', 'Dokumen Saya')
@section('header-title', 'Pusat Dokumen Pribadi')

@section('content')
<div class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
    <div class="text-sm">
        <p class="text-[#8A8A8A]">Halo, <span class="text-[#1E2432] font-black">{{ auth()->user()->name }}</span>. Berikut adalah dokumen Anda.</p>
    </div>
    
    <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" 
        class="bg-[#E85A4F] text-white px-8 py-3.5 rounded-2xl font-black hover:bg-[#d44d42] transition-all flex items-center gap-2 shadow-lg shadow-red-100 active:scale-95">
        <i data-lucide="upload-cloud" class="w-5 h-5 text-white"></i>
        Unggah Dokumen Baru
    </button>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-8">
    @foreach($documents as $doc)
    <div class="group bg-white p-8 rounded-[40px] border border-[#EFEFEF] hover:border-[#E85A4F] hover:shadow-2xl hover:shadow-red-100/50 transition-all duration-500 transform hover:-translate-y-2 flex flex-col justify-between h-[280px]">
        <div class="flex justify-between items-start">
            <div class="w-14 h-14 bg-[#F5F4F2] rounded-2xl flex items-center justify-center text-[#8A8A8A] group-hover:bg-[#E85A4F] group-hover:text-white transition-all duration-500">
                @if(str_contains($doc->file_path, '.pdf'))
                    <i data-lucide="file-text" class="w-7 h-7 text-red-500 group-hover:text-white"></i>
                @elseif(str_contains($doc->file_path, '.xls') || str_contains($doc->file_path, '.xlsx'))
                    <i data-lucide="file-spreadsheet" class="w-7 h-7 text-green-600 group-hover:text-white"></i>
                @elseif(str_contains($doc->file_path, '.csv'))
                    <i data-lucide="file-type" class="w-7 h-7 text-blue-400 group-hover:text-white"></i>
                @elseif(str_contains($doc->file_path, '.doc') || str_contains($doc->file_path, '.docx'))
                    <i data-lucide="file-text" class="w-7 h-7 text-blue-600 group-hover:text-white"></i>
                @elseif(str_contains($doc->file_path, '.jpg') || str_contains($doc->file_path, '.jpeg') || str_contains($doc->file_path, '.png'))
                    <i data-lucide="image" class="w-7 h-7 text-blue-500 group-hover:text-white"></i>
                @else
                    <i data-lucide="file" class="w-7 h-7"></i>
                @endif
            </div>
            <div class="flex gap-2">
                <button onclick="openPreview('{{ route('documents.preview', $doc->id) }}', '{{ $doc->title }}', '{{ $doc->file_path }}', {{ json_encode($doc->versions) }})" class="bg-green-50 p-3 rounded-xl text-green-600 hover:bg-green-600 hover:text-white transition-all">
                    <i data-lucide="eye" class="w-4 h-4"></i>
                </button>
                <button onclick="openRevisionModal({{ $doc->id }}, '{{ $doc->title }}')" class="bg-blue-50 p-3 rounded-xl text-blue-600 hover:bg-blue-600 hover:text-white transition-all" title="Unggah Revisi">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                </button>
                @if(!$doc->is_locked)
                <a href="{{ route('documents.download', $doc->id) }}" target="_blank" class="bg-[#FCFBF9] p-3 rounded-xl text-[#E85A4F] hover:bg-[#E85A4F] hover:text-white transition-all no-loader">
                    <i data-lucide="download" class="w-4 h-4"></i>
                </a>
                @else
                <div class="bg-gray-50 p-3 rounded-xl text-gray-300 cursor-not-allowed border border-gray-100" title="Unduhan dikunci Admin">
                    <i data-lucide="download" class="w-4 h-4 opacity-50"></i>
                </div>
                @endif
                @if(!$doc->is_locked)
                <form action="{{ route('documents.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Hapus dokumen ini?')" class="no-loader">
                    @csrf @method('DELETE')
                    <button type="submit" class="bg-red-50 p-3 rounded-xl text-red-500 hover:bg-red-500 hover:text-white transition-all">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </form>
                @else
                <div class="bg-gray-100 p-3 rounded-xl text-gray-400 cursor-not-allowed" title="Dokumen dikunci Admin">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                </div>
                @endif
            </div>
        </div>
        <div>
            <div class="mb-2">
                @if($doc->status === 'verified')
                    <span class="px-3 py-1 bg-green-50 text-green-600 text-[9px] font-black rounded-lg uppercase tracking-widest border border-green-100">Verified</span>
                @elseif($doc->status === 'rejected')
                    <span class="px-3 py-1 bg-red-50 text-red-600 text-[9px] font-black rounded-lg uppercase tracking-widest border border-red-100 cursor-pointer" onclick="Swal.fire({title: 'Alasan Penolakan', text: '{{ $doc->rejection_reason }}', icon: 'info', confirmButtonColor: '#E85A4F', customClass: {popup: 'rounded-[32px]'}})">Rejected ?</span>
                @else
                    <span class="px-3 py-1 bg-blue-50 text-blue-600 text-[9px] font-black rounded-lg uppercase tracking-widest border border-blue-100">Pending Review</span>
                @endif
            </div>
            <h3 class="text-lg font-black text-[#1E2432] truncate group-hover:text-[#E85A4F] transition-all" title="{{ $doc->title }}">{{ $doc->title }}</h3>
            <div class="flex items-center gap-3 mt-2">
                <span class="px-3 py-1 bg-gray-100 text-[#1E2432] text-[10px] font-black rounded-lg uppercase tracking-widest">{{ $doc->category->name }}</span>
                <span class="text-[10px] font-bold text-[#ABABAB]">{{ $doc->created_at->format('d M Y') }}</span>
            </div>
        </div>
    </div>
    @endforeach

    @if($documents->isEmpty())
    <div class="col-span-4 py-24 text-center bg-white rounded-[56px] border-4 border-dashed border-[#F5F4F2]">
        <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6 text-gray-200">
            <i data-lucide="folder-open" class="w-12 h-12"></i>
        </div>
        <p class="text-[#8A8A8A] font-bold uppercase tracking-widest text-xs">Belum ada dokumen yang diunggah.</p>
    </div>
    @endif
</div>

<!-- Multi-Upload Modal -->
<div id="uploadModal" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-md">
    <div class="bg-white w-full max-w-4xl rounded-[48px] p-12 shadow-2xl animate-in zoom-in duration-300 flex flex-col max-h-[90vh]">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h3 class="text-2xl font-black text-[#1E2432] tracking-tight">Pusat Unggah Dokumen</h3>
                <p class="text-[10px] font-bold text-[#8A8A8A] uppercase tracking-widest mt-1">Seret & lepas banyak file sekaligus</p>
            </div>
            <button onclick="document.getElementById('uploadModal').classList.add('hidden')" class="bg-[#FCFBF9] p-3 rounded-2xl text-[#8A8A8A] hover:text-red-500 transition-all border border-[#EFEFEF]">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        
        <div id="dropzone" class="w-full py-12 border-4 border-dashed border-[#EFEFEF] rounded-[40px] bg-[#FCFBF9] flex flex-col items-center justify-center cursor-pointer hover:border-[#E85A4F] transition-all group mb-8">
            <i data-lucide="upload-cloud" class="w-16 h-16 text-[#8A8A8A] group-hover:text-[#E85A4F] transition-all mb-4"></i>
            <p class="text-sm font-black text-[#1E2432] uppercase tracking-widest">Tarik file ke sini atau klik untuk memilih</p>
            <p class="text-[10px] font-bold text-[#8A8A8A] uppercase mt-2">Mendukung PDF, Image, Word, Excel, CSV (Max 10MB/file)</p>
            <input type="file" id="multiFileInput" multiple class="hidden" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.csv">
        </div>

        <form action="{{ route('documents.bulk-store') }}" method="POST" enctype="multipart/form-data" class="flex-1 overflow-hidden flex flex-col">
            @csrf
            <div id="fileList" class="flex-1 overflow-y-auto space-y-4 pr-4 custom-scrollbar mb-8">
                <!-- File items will be injected here -->
            </div>

            <div id="uploadActions" class="hidden">
                <button type="submit" class="w-full bg-[#E85A4F] text-white py-5 rounded-[28px] font-black text-lg hover:bg-[#d44d42] transition-all shadow-xl shadow-red-200 active:scale-95 flex items-center justify-center gap-3">
                    Unggah Semua Dokumen (<span id="fileCount">0</span>)
                    <i data-lucide="zap" class="w-5 h-5"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('multiFileInput');
    const fileList = document.getElementById('fileList');
    const uploadActions = document.getElementById('uploadActions');
    const fileCountSpan = document.getElementById('fileCount');
    let selectedFiles = [];

    dropzone.onclick = () => fileInput.click();

    fileInput.onchange = (e) => handleFiles(e.target.files);

    dropzone.ondragover = (e) => { e.preventDefault(); dropzone.classList.add('border-[#E85A4F]', 'bg-red-50/30'); };
    dropzone.ondragleave = () => { dropzone.classList.remove('border-[#E85A4F]', 'bg-red-50/30'); };
    dropzone.ondrop = (e) => { e.preventDefault(); dropzone.classList.remove('border-[#E85A4F]', 'bg-red-50/30'); handleFiles(e.dataTransfer.files); };

    function handleFiles(files) {
        Array.from(files).forEach(file => {
            const id = Math.random().toString(36).substr(2, 9);
            const item = `
                <div id="file-${id}" class="p-6 bg-white rounded-[32px] border border-[#EFEFEF] flex flex-col md:flex-row gap-6 items-center animate-in slide-in-from-bottom-4 duration-300">
                    <div class="w-12 h-12 bg-gray-50 rounded-xl flex items-center justify-center text-[#8A8A8A]"><i data-lucide="file" class="w-6 h-6"></i></div>
                    <div class="flex-1 space-y-4">
                        <div class="flex justify-between items-start">
                            <div class="overflow-hidden"><p class="text-xs font-black text-[#1E2432] truncate">${file.name}</p><p class="text-[9px] text-[#ABABAB] font-bold uppercase">${(file.size / 1024 / 1024).toFixed(2)} MB</p></div>
                            <button type="button" onclick="removeFile('${id}')" class="text-red-500 hover:text-red-700 transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[8px] font-black text-[#8A8A8A] uppercase tracking-widest">Judul Arsip</label>
                                <input type="text" name="titles[]" value="${file.name.split('.').slice(0, -1).join('.')}" required class="w-full px-4 py-2 rounded-xl border border-[#EFEFEF] bg-[#FCFBF9] text-[10px] font-bold outline-none">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[8px] font-black text-[#8A8A8A] uppercase tracking-widest">Kategori</label>
                                <select name="categories[]" required class="w-full px-4 py-2 rounded-xl border border-[#EFEFEF] bg-[#FCFBF9] text-[10px] font-bold outline-none">
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <input type="file" name="files[]" class="hidden" id="input-${id}">
                    </div>
                </div>
            `;
            fileList.insertAdjacentHTML('beforeend', item);
            
            // Re-inject file to a specific hidden input
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            document.getElementById(`input-${id}`).files = dataTransfer.files;
        });
        
        updateUI();
        lucide.createIcons();
    }

    function removeFile(id) {
        document.getElementById(`file-${id}`).remove();
        updateUI();
    }

    function updateUI() {
        const count = fileList.children.length;
        fileCountSpan.innerText = count;
        if (count > 0) uploadActions.classList.remove('hidden');
        else uploadActions.classList.add('hidden');
    }
</script>

<!-- Revision Modal -->
<div id="revisionModal" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center z-[110] p-6 backdrop-blur-md">
    <div class="bg-white w-full max-w-lg rounded-[48px] p-12 shadow-2xl animate-in zoom-in duration-300">
        <div class="flex justify-between items-center mb-10">
            <h3 class="text-2xl font-black text-[#1E2432]">Unggah Revisi</h3>
            <button onclick="document.getElementById('revisionModal').classList.add('hidden')" class="text-[#8A8A8A] hover:text-red-500 transition-colors">
                <i data-lucide="x" class="w-8 h-8"></i>
            </button>
        </div>
        <p class="text-xs font-bold text-[#8A8A8A] mb-8 uppercase tracking-widest">Revisi untuk: <span id="revisionDocTitle" class="text-[#E85A4F]"></span></p>
        <form id="revisionForm" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            <div class="space-y-3">
                <label class="text-[10px] font-black text-[#1E2432] uppercase tracking-[0.2em] ml-1">Pilih File Baru</label>
                <div class="relative group">
                    <input type="file" name="file" required class="w-full px-6 py-10 rounded-3xl border-2 border-dashed border-[#EFEFEF] bg-[#FCFBF9] text-xs font-bold text-[#8A8A8A] file:hidden cursor-pointer hover:border-[#E85A4F] transition-all text-center">
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none opacity-60 group-hover:opacity-100 transition-opacity">
                        <i data-lucide="upload-cloud" class="w-10 h-10 text-[#E85A4F] mb-3"></i>
                        <span class="text-[10px] uppercase font-black tracking-tighter">Versi lama akan dipindahkan ke riwayat</span>
                    </div>
                </div>
            </div>
            <button type="submit" class="w-full bg-[#1E2432] text-white py-5 rounded-[28px] font-black text-lg hover:bg-[#E85A4F] transition-all shadow-xl active:scale-95 flex items-center justify-center gap-3">
                Simpan Sebagai Versi Terbaru
                <i data-lucide="zap" class="w-5 h-5"></i>
            </button>
        </form>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="fixed inset-0 bg-black/80 hidden flex items-center justify-center z-[100] p-10 backdrop-blur-xl">
    <div class="bg-white w-full h-full max-w-7xl rounded-[48px] overflow-hidden flex flex-col shadow-2xl">
        <div class="p-8 border-b border-[#EFEFEF] flex justify-between items-center bg-[#FCFBF9]/50">
            <h3 id="previewTitle" class="text-xl font-black text-[#1E2432]">Pratinjau Dokumen</h3>
            <button onclick="document.getElementById('previewModal').classList.add('hidden')" class="bg-white p-3 rounded-2xl shadow-sm border border-[#EFEFEF] hover:bg-red-50 hover:text-red-500 transition-all">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <div class="flex-1 flex overflow-hidden">
            <div class="flex-1 bg-gray-100 overflow-auto flex items-center justify-center p-10 relative" id="previewContent">
                <!-- Content will be injected here -->
                @if($watermarkEnabled)
                <div id="watermarkOverlay" class="absolute inset-0 pointer-events-none hidden flex-wrap gap-20 p-20 opacity-[0.03] overflow-hidden content-center justify-center select-none">
                    @for($i=0; $i<20; $i++)
                        <div class="text-4xl font-black -rotate-45 uppercase tracking-[0.5em] whitespace-nowrap">{{ $watermarkText }}</div>
                    @endfor
                </div>
                @endif
            </div>
            <!-- Version History Sidebar -->
            <div id="versionSidebar" class="w-80 border-l border-[#EFEFEF] bg-white p-8 overflow-y-auto hidden">
                <h4 class="text-xs font-black text-[#8A8A8A] uppercase tracking-[0.3em] mb-8">Riwayat Versi</h4>
                <div id="versionList" class="space-y-6">
                    <!-- Versions injected here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openRevisionModal(id, title) {
        document.getElementById('revisionDocTitle').innerText = title;
        document.getElementById('revisionForm').action = `/documents/${id}/revision`;
        document.getElementById('revisionModal').classList.remove('hidden');
    }

    function openPreview(url, title, filePath, versions = []) {
        document.getElementById('previewTitle').innerText = title;
        const container = document.getElementById('previewContent');
        const watermark = document.getElementById('watermarkOverlay');
        const sidebar = document.getElementById('versionSidebar');
        const list = document.getElementById('versionList');
        
        // Handle Sidebar
        if (versions && versions.length > 0) {
            sidebar.classList.remove('hidden');
            list.innerHTML = versions.map(v => `
                <div class="p-5 bg-[#FCFBF9] rounded-2xl border border-[#EFEFEF]">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-[10px] font-black text-[#1E2432] uppercase">Versi ${v.version_number}</span>
                        <a href="/documents/preview-version/${v.id}" target="_blank" class="text-[9px] font-black text-[#E85A4F] uppercase hover:underline">Lihat</a>
                    </div>
                    <p class="text-[9px] text-[#ABABAB] font-bold">${new Date(v.created_at).toLocaleDateString('id-ID', {day:'numeric', month:'short', year:'numeric'})}</p>
                </div>
            `).join('');
        } else {
            sidebar.classList.add('hidden');
        }

        // Remove existing items except watermark
        Array.from(container.children).forEach(child => {
            if(child.id !== 'watermarkOverlay') child.remove();
        });

        if (filePath.toLowerCase().endsWith('.pdf')) {
            const iframe = document.createElement('iframe');
            iframe.src = url;
            iframe.className = 'w-full h-full border-none relative z-0';
            container.appendChild(iframe);
        } else {
            const img = document.createElement('img');
            img.src = url;
            img.className = 'max-w-full max-h-full object-contain shadow-2xl rounded-2xl border-8 border-white relative z-0';
            container.appendChild(img);
        }
        
        if (watermark) {
            watermark.classList.remove('hidden');
            watermark.classList.add('flex');
        }
        document.getElementById('previewModal').classList.remove('hidden');
    }
</script>

@if(session('success'))
<script>
    Swal.fire({ 
        icon: 'success', 
        title: 'Berhasil!', 
        text: "{{ session('success') }}", 
        confirmButtonColor: '#E85A4F',
        customClass: { popup: 'rounded-[40px]' }
    });
</script>
@endif
@endsection
