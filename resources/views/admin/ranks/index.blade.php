@extends('layouts.app')

@section('title', 'Manajemen Golongan')
@section('header-title', 'Konfigurasi Golongan')

@section('content')
<div class="space-y-8 page-fade">
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
        <div>
            <h2 class="text-3xl font-black text-slate-900 italic">Daftar <span class="text-blue-600">Golongan</span></h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Kelola tingkatan golongan dan pangkat pegawai.</p>
        </div>
        <button onclick="document.getElementById('addRankModal').classList.remove('hidden')" class="px-8 py-4 rounded-2xl bg-slate-900 text-white font-bold text-[10px] uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl btn-3d flex items-center gap-3">
            <i data-lucide="plus-circle" class="w-5 h-5"></i> Tambah Golongan
        </button>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden card-3d">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nama Golongan</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Keterangan / Pangkat</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($ranks as $rank)
                <tr class="hover:bg-slate-50/50 transition-colors group">
                    <td class="px-6 py-4">
                        <span class="text-sm font-black text-slate-900">{{ $rank->name }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-medium text-slate-500">{{ $rank->description ?? '-' }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex justify-center items-center gap-2">
                            <button onclick="openEditRankModal({{ json_encode($rank) }})" class="w-9 h-9 rounded-xl border border-slate-200 text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all flex items-center justify-center">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </button>
                            <form action="{{ route('admin.ranks.destroy', $rank->id) }}" method="POST" onsubmit="return confirm('Hapus golongan ini?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-9 h-9 rounded-xl border border-slate-200 text-slate-400 hover:text-red-600 hover:bg-red-50 transition-all flex items-center justify-center">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-20 text-center">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest italic">Belum ada data golongan</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div id="addRankModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-sm rounded-[32px] p-10 shadow-2xl animate-in zoom-in duration-200">
        <h3 class="text-xl font-bold text-slate-900 mb-6">Tambah Golongan</h3>
        <form action="{{ route('admin.ranks.store') }}" method="POST" class="space-y-6">
            @csrf
            <div class="space-y-1.5">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Nama (Misal: II/A)</label>
                <input type="text" name="name" required class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-bold focus:border-blue-500 outline-none">
            </div>
            <div class="space-y-1.5">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Keterangan (Misal: Pengatur)</label>
                <input type="text" name="description" class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none">
            </div>
            <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-bold text-sm uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl">Simpan Golongan</button>
            <button type="button" onclick="document.getElementById('addRankModal').classList.add('hidden')" class="w-full text-slate-400 font-bold text-[10px] uppercase tracking-widest mt-2">Batal</button>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editRankModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-sm rounded-[32px] p-10 shadow-2xl animate-in zoom-in duration-200">
        <h3 class="text-xl font-bold text-slate-900 mb-6">Edit Golongan</h3>
        <form id="editRankForm" method="POST" class="space-y-6">
            @csrf @method('PUT')
            <div class="space-y-1.5">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Nama Golongan</label>
                <input type="text" name="name" id="edit_name" required class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-bold focus:border-blue-500 outline-none">
            </div>
            <div class="space-y-1.5">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Keterangan</label>
                <input type="text" name="description" id="edit_description" class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:border-blue-500 outline-none">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-bold text-sm uppercase tracking-widest hover:bg-blue-700 transition-all shadow-xl">Update Golongan</button>
            <button type="button" onclick="document.getElementById('editRankModal').classList.add('hidden')" class="w-full text-slate-400 font-bold text-[10px] uppercase tracking-widest mt-2">Batal</button>
        </form>
    </div>
</div>

<script>
    function openEditRankModal(rank) {
        const form = document.getElementById('editRankForm');
        form.action = `/admin/ranks/${rank.id}`;
        document.getElementById('edit_name').value = rank.name;
        document.getElementById('edit_description').value = rank.description || '';
        document.getElementById('editRankModal').classList.remove('hidden');
    }
</script>
@endsection
