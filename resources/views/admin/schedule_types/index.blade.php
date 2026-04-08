@extends('layouts.app')

@section('title', 'Master Tipe Piket')
@section('header-title', 'Master Data Tipe Piket')

@section('content')
<div class="space-y-8 page-fade">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="flex items-center gap-4">
            <div class="p-4 bg-indigo-600 rounded-[24px] shadow-lg shadow-indigo-200">
                <i data-lucide="layers" class="w-6 h-6 text-white text-3d"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black text-slate-900 italic tracking-tight uppercase">Master Tipe Piket</h2>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Manajemen Konfigurasi Jadwal & Piket</p>
            </div>
        </div>
        <button onclick="openModal('addTypeModal')" class="group px-6 py-3.5 bg-slate-900 border border-slate-800 rounded-2xl shadow-xl hover:shadow-2xl hover:bg-indigo-600 hover:border-indigo-500 transition-all active:scale-95 flex items-center gap-3">
            <span class="p-2 bg-slate-800 group-hover:bg-indigo-500 rounded-xl transition-colors">
                <i data-lucide="plus" class="w-4 h-4 text-white"></i>
            </span>
            <span class="text-sm font-black text-white uppercase tracking-widest italic">Tambah Tipe Piket</span>
        </button>
    </div>

    @if($types->isEmpty())
        <div class="bg-white rounded-[40px] border-2 border-dashed border-slate-200 p-20 text-center animate-pulse">
            <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-lucide="layers" class="w-12 h-12 text-slate-300"></i>
            </div>
            <h3 class="text-lg font-black text-slate-900 uppercase italic">Belum Ada Tipe Piket</h3>
            <p class="text-xs font-bold text-slate-400 mt-2 uppercase tracking-widest">Silakan tambahkan tipe piket baru atau jalankan seeder</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($types as $type)
                <div class="group bg-white rounded-[40px] border border-slate-200 shadow-sm hover:shadow-2xl hover:shadow-indigo-100 hover:-translate-y-2 transition-all duration-500 overflow-hidden flex flex-col card-3d">
                    <div class="p-8 flex-1">
                        <div class="flex justify-between items-start mb-6">
                            <div class="p-4 rounded-3xl {{ $type->is_active ? 'bg-indigo-50' : 'bg-slate-100' }} group-hover:bg-indigo-600 transition-colors duration-500">
                                <i data-lucide="layers" class="w-6 h-6 {{ $type->is_active ? 'text-indigo-600' : 'text-slate-400' }} group-hover:text-white"></i>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <span class="px-3 py-1 rounded-full {{ $type->is_active ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-slate-50 text-slate-400' }} text-[9px] font-black uppercase tracking-widest">
                                    {{ $type->is_active ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                                @if($type->uses_squads)
                                    <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-600 border border-blue-100 text-[9px] font-black uppercase tracking-widest">
                                        Sistem Regu
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <h3 class="text-xl font-black text-slate-900 group-hover:text-indigo-600 transition-colors italic leading-tight uppercase">{{ $type->name }}</h3>
                        <p class="text-[9px] font-mono font-bold text-slate-300 mt-1 uppercase tracking-tighter">Code: {{ $type->code }}</p>
                        <p class="text-xs font-medium text-slate-500 mt-4 leading-relaxed line-clamp-2">
                            {{ $type->description ?: 'Tidak ada deskripsi.' }}
                        </p>
                    </div>

                    <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="users" class="w-4 h-4 text-slate-400"></i>
                            <span class="text-[10px] font-black text-slate-600 uppercase tracking-widest">{{ $type->squads_count ?? 0 }} Regu Terhubung</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="editType({{ $type }})" class="p-2 hover:bg-blue-50 text-blue-600 rounded-xl transition-colors" title="Edit">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </button>
                            <form action="{{ route('admin.schedule-types.destroy', $type) }}" method="POST" onsubmit="return confirm('Hapus tipe piket ini?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 hover:bg-red-50 text-red-500 rounded-xl transition-colors" title="Hapus">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Add Modal -->
<div id="addTypeModal" class="fixed inset-0 z-100 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in duration-300">
    <div class="bg-white rounded-[40px] shadow-2xl w-full max-w-lg overflow-hidden card-3d border border-slate-100 transform transition-all duration-300 scale-95 opacity-0 modal-content">
        <div class="p-8 border-b border-slate-50 bg-slate-50/50 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-slate-900 rounded-2xl shadow-lg shadow-slate-200">
                    <i data-lucide="layers" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900 italic uppercase">Tambah Tipe Piket</h3>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-0.5">Definisi Konfigurasi Piket Baru</p>
                </div>
            </div>
            <button onclick="closeModal('addTypeModal')" class="p-2 hover:bg-slate-100 rounded-xl transition-all">
                <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
            </button>
        </div>
        <form action="{{ route('admin.schedule-types.store') }}" method="POST" class="p-8 space-y-6">
            @csrf
            <div class="space-y-1.5">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Nama Tipe Piket</label>
                <input type="text" name="name" required placeholder="Contoh: RUPAM, P2U, dll" class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-indigo-500 outline-none transition-all">
            </div>
            <div class="space-y-1.5">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Deskripsi</label>
                <textarea name="description" rows="3" placeholder="Jelaskan peran atau aturan umum tipe piket ini..." class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-indigo-500 outline-none transition-all resize-none"></textarea>
            </div>
            <div class="flex gap-6 mt-4">
                <label class="flex items-center gap-3 cursor-pointer group">
                    <div class="relative w-12 h-6 rounded-full bg-slate-100 border border-slate-200 transition-colors group-hover:border-indigo-300">
                        <input type="checkbox" name="uses_squads" class="peer sr-only" checked>
                        <div class="absolute left-1 top-1 w-4 h-4 rounded-full bg-white shadow-sm transition-all peer-checked:left-7 peer-checked:bg-indigo-600"></div>
                    </div>
                    <span class="text-[10px] font-black text-slate-700 uppercase tracking-widest">Sistem Regu</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer group">
                    <div class="relative w-12 h-6 rounded-full bg-slate-100 border border-slate-200 transition-colors group-hover:border-emerald-300">
                        <input type="checkbox" name="is_active" class="peer sr-only" checked>
                        <div class="absolute left-1 top-1 w-4 h-4 rounded-full bg-white shadow-sm transition-all peer-checked:left-7 peer-checked:bg-emerald-500"></div>
                    </div>
                    <span class="text-[10px] font-black text-slate-700 uppercase tracking-widest">Status Aktif</span>
                </label>
            </div>
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeModal('addTypeModal')" class="flex-1 px-6 py-4 rounded-2xl border border-slate-100 text-sm font-black text-slate-400 uppercase tracking-widest hover:bg-slate-50 transition-all italic">Batal</button>
                <button type="submit" class="flex-2 px-6 py-4 bg-slate-900 rounded-2xl text-sm font-black text-white uppercase tracking-widest hover:bg-indigo-600 transition-all shadow-xl shadow-slate-200 italic">Simpan Tipe</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editTypeModal" class="fixed inset-0 z-100 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in duration-300">
    <div class="bg-white rounded-[40px] shadow-2xl w-full max-w-lg overflow-hidden card-3d border border-slate-100 transform transition-all duration-300 scale-95 opacity-0 modal-content">
        <div class="p-8 border-b border-slate-50 bg-slate-50/50 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-blue-600 rounded-2xl shadow-lg shadow-blue-200">
                    <i data-lucide="edit-3" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900 italic uppercase">Edit Tipe Piket</h3>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-0.5">Ubah Konfigurasi Tipe Piket</p>
                </div>
            </div>
            <button onclick="closeModal('editTypeModal')" class="p-2 hover:bg-slate-100 rounded-xl transition-all">
                <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
            </button>
        </div>
        <form id="editTypeForm" method="POST" class="p-8 space-y-6">
            @csrf
            @method('PUT')
            <div class="space-y-1.5">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Nama Tipe Piket</label>
                <input type="text" name="name" id="edit_name" required class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none transition-all">
            </div>
            <div class="space-y-1.5">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Deskripsi</label>
                <textarea name="description" id="edit_description" rows="3" class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none transition-all resize-none"></textarea>
            </div>
            <div class="flex gap-6 mt-4">
                <label class="flex items-center gap-3 cursor-pointer group">
                    <div class="relative w-12 h-6 rounded-full bg-slate-100 border border-slate-200 transition-colors group-hover:border-blue-300">
                        <input type="checkbox" name="uses_squads" id="edit_uses_squads" class="peer sr-only">
                        <div class="absolute left-1 top-1 w-4 h-4 rounded-full bg-white shadow-sm transition-all peer-checked:left-7 peer-checked:bg-blue-600"></div>
                    </div>
                    <span class="text-[10px] font-black text-slate-700 uppercase tracking-widest">Sistem Regu</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer group">
                    <div class="relative w-12 h-6 rounded-full bg-slate-100 border border-slate-200 transition-colors group-hover:border-emerald-300">
                        <input type="checkbox" name="is_active" id="edit_is_active" class="peer sr-only">
                        <div class="absolute left-1 top-1 w-4 h-4 rounded-full bg-white shadow-sm transition-all peer-checked:left-7 peer-checked:bg-emerald-500"></div>
                    </div>
                    <span class="text-[10px] font-black text-slate-700 uppercase tracking-widest">Status Aktif</span>
                </label>
            </div>
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeModal('editTypeModal')" class="flex-1 px-6 py-4 rounded-2xl border border-slate-100 text-sm font-black text-slate-400 uppercase tracking-widest hover:bg-slate-50 transition-all italic">Batal</button>
                <button type="submit" class="flex-2 px-6 py-4 bg-blue-600 rounded-2xl text-sm font-black text-white uppercase tracking-widest hover:bg-blue-700 transition-all shadow-xl shadow-blue-200 italic">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        const content = modal.querySelector('.modal-content');
        modal.classList.remove('hidden');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
        }, 10);
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        const content = modal.querySelector('.modal-content');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    function editType(type) {
        const form = document.getElementById('editTypeForm');
        form.action = `/admin/schedule-types/${type.id}`;
        
        document.getElementById('edit_name').value = type.name;
        document.getElementById('edit_description').value = type.description || '';
        document.getElementById('edit_uses_squads').checked = type.uses_squads;
        document.getElementById('edit_is_active').checked = type.is_active;

        openModal('editTypeModal');
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('bg-slate-900/60')) {
            closeModal('addTypeModal');
            closeModal('editTypeModal');
        }
    }
</script>
@endsection
