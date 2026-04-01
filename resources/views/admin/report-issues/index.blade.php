@extends('layouts.app')

@section('title', 'Laporan Masalah')
@section('header-title', 'Manajemen Laporan Masalah')

@section('content')
<div class="bg-white rounded-[40px] border border-[#EFEFEF] shadow-sm overflow-hidden">
    <div class="p-8 border-b border-[#EFEFEF] bg-[#FCFBF9]/50">
        <h3 class="text-lg font-bold text-[#1E2432]">Daftar Laporan dari Pegawai</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#FCFBF9]">
                    <th class="px-8 py-5 text-xs font-bold text-[#8A8A8A] uppercase tracking-widest">Pegawai</th>
                    <th class="px-8 py-5 text-xs font-bold text-[#8A8A8A] uppercase tracking-widest">Subjek</th>
                    <th class="px-8 py-5 text-xs font-bold text-[#8A8A8A] uppercase tracking-widest">Status</th>
                    <th class="px-8 py-5 text-xs font-bold text-[#8A8A8A] uppercase tracking-widest">Tanggal</th>
                    <th class="px-8 py-5 text-xs font-bold text-[#8A8A8A] uppercase tracking-widest text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#EFEFEF]">
                @foreach($issues as $issue)
                <tr class="hover:bg-[#FCFBF9] transition-all group">
                    <td class="px-8 py-6">
                        <p class="text-sm font-bold text-[#1E2432]">{{ $issue->user->name }}</p>
                        <p class="text-[10px] text-[#8A8A8A] font-medium">{{ $issue->user->email }}</p>
                    </td>
                    <td class="px-8 py-6">
                        <p class="text-sm font-medium text-[#1E2432]">{{ $issue->subject }}</p>
                    </td>
                    <td class="px-8 py-6 text-sm">
                        @if($issue->status === 'open')
                            <span class="px-3 py-1 bg-red-50 text-red-600 text-[10px] font-black uppercase rounded-full border border-red-100 italic">Open</span>
                        @elseif($issue->status === 'resolved')
                            <span class="px-3 py-1 bg-green-50 text-green-600 text-[10px] font-black uppercase rounded-full border border-green-100 italic">Resolved</span>
                        @else
                            <span class="px-3 py-1 bg-gray-50 text-gray-600 text-[10px] font-black uppercase rounded-full border border-gray-100 italic">Closed</span>
                        @endif
                    </td>
                    <td class="px-8 py-6 text-sm text-[#8A8A8A] font-medium">
                        {{ $issue->created_at->format('d M Y, H:i') }}
                    </td>
                    <td class="px-8 py-6 text-sm text-center">
                        <div class="flex justify-center items-center gap-2">
                            @php
                                $employee = \App\Models\Employee::where('user_id', $issue->user_id)->first();
                                $issueData = array_merge($issue->toArray(), [
                                    'user' => $issue->user,
                                    'employee' => $employee
                                ]);
                            @endphp
                            <button onclick="openDetailModal({{ json_encode($issueData) }})" class="w-9 h-9 flex items-center justify-center text-blue-500 hover:bg-blue-50 rounded-xl transition-all">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                            <form action="{{ route('admin.report-issues.destroy', $issue->id) }}" method="POST" onsubmit="return confirm('Hapus laporan ini?')">
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
        {{ $issues->links() }}
    </div>
</div>

<!-- Detail Modal -->
<div id="detailModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 p-6 backdrop-blur-md">
    <div class="bg-white w-full max-w-3xl rounded-[56px] p-0 shadow-2xl animate-in zoom-in duration-300 overflow-hidden border border-[#EFEFEF]">
        <div class="bg-[#1E2432] p-10 text-white flex justify-between items-center relative">
            <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
            <div class="relative">
                <h3 class="text-2xl font-black italic tracking-tight">Detail Laporan</h3>
                <p class="text-[10px] font-black opacity-60 uppercase tracking-[0.3em] mt-1">Sinergi PAS Management System</p>
            </div>
            <button onclick="document.getElementById('detailModal').classList.add('hidden')" class="relative w-12 h-12 flex items-center justify-center rounded-2xl bg-white/10 hover:bg-white/20 transition-all border border-white/20">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        
        <form id="updateForm" method="POST" class="p-10">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <!-- User Profile Info -->
                <div class="md:col-span-1 space-y-8">
                    <div class="p-6 bg-[#FCFBF9] rounded-[32px] border border-[#EFEFEF] text-center">
                        <div class="w-20 h-20 bg-gray-100 rounded-[24px] mx-auto mb-4 flex items-center justify-center overflow-hidden border-2 border-white shadow-lg" id="detail_photo_container">
                            <i data-lucide="user" class="w-10 h-10 text-gray-300"></i>
                        </div>
                        <h4 class="text-sm font-black text-[#1E2432]" id="detail_name"></h4>
                        <p class="text-[10px] font-bold text-[#8A8A8A] uppercase tracking-widest mt-1" id="detail_nip"></p>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600"><i data-lucide="mail" class="w-4 h-4"></i></div>
                            <div class="flex-1 overflow-hidden"><p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest">Email</p><p class="text-xs font-bold text-[#1E2432] truncate" id="detail_email"></p></div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-green-50 rounded-xl flex items-center justify-center text-green-600"><i data-lucide="briefcase" class="w-4 h-4"></i></div>
                            <div class="flex-1"><p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest">Jabatan</p><p class="text-xs font-bold text-[#1E2432]" id="detail_position"></p></div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-purple-50 rounded-xl flex items-center justify-center text-purple-600"><i data-lucide="calendar" class="w-4 h-4"></i></div>
                            <div class="flex-1"><p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest">Dikirim</p><p class="text-xs font-bold text-[#1E2432]" id="detail_date"></p></div>
                        </div>
                    </div>
                </div>

                <!-- Issue Content & Action -->
                <div class="md:col-span-2 space-y-8">
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <span class="w-2 h-2 rounded-full bg-[#E85A4F]"></span>
                            <h4 class="text-xs font-black text-[#1E2432] uppercase tracking-[0.2em]" id="detail_subject"></h4>
                        </div>
                        <div class="p-8 bg-[#FCFBF9] rounded-[32px] border border-[#EFEFEF] text-sm text-[#1E2432] leading-relaxed shadow-inner italic" id="detail_message"></div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-6">
                        <div class="col-span-1">
                            <label class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest block mb-2 ml-1">Update Status</label>
                            <select name="status" id="detail_status" class="w-full px-5 py-4 rounded-[20px] border border-[#EFEFEF] bg-white text-sm font-bold outline-none focus:ring-4 focus:ring-red-500/5 focus:border-[#E85A4F] transition-all">
                                <option value="open">Open</option>
                                <option value="resolved">Resolved</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest block mb-2 ml-1">Catatan Admin / Tanggapan</label>
                        <textarea name="admin_note" id="detail_note" rows="3" class="w-full px-6 py-5 rounded-[24px] border border-[#EFEFEF] bg-white text-sm font-bold outline-none focus:ring-4 focus:ring-red-500/5 focus:border-[#E85A4F] transition-all" placeholder="Berikan tanggapan untuk laporan ini..."></textarea>
                    </div>

                    <button type="submit" class="w-full bg-[#E85A4F] text-white py-5 rounded-[24px] font-black hover:bg-[#d44d42] transition-all shadow-xl shadow-red-200 active:scale-[0.98] flex items-center justify-center gap-3">
                        Perbarui & Simpan Perubahan
                        <i data-lucide="save" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function openDetailModal(data) {
        document.getElementById('detailModal').classList.remove('hidden');
        document.getElementById('updateForm').action = `/admin/report-issues/${data.id}`;
        
        // User Info
        document.getElementById('detail_name').innerText = data.user.name;
        document.getElementById('detail_email').innerText = data.user.email;
        document.getElementById('detail_nip').innerText = data.employee ? 'NIP. ' + data.employee.nip : 'Admin';
        document.getElementById('detail_position').innerText = data.employee ? data.employee.position : 'N/A';
        document.getElementById('detail_date').innerText = new Date(data.created_at).toLocaleString('id-ID', {
            day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
        });

        // Photo
        const photoContainer = document.getElementById('detail_photo_container');
        if (data.employee && data.employee.photo) {
            photoContainer.innerHTML = `<img src="${data.employee.photo}" class="w-full h-full object-cover">`;
        } else {
            photoContainer.innerHTML = `<i data-lucide="user" class="w-10 h-10 text-gray-300"></i>`;
            lucide.createIcons();
        }

        // Issue Content
        document.getElementById('detail_subject').innerText = data.subject;
        document.getElementById('detail_message').innerText = data.message;
        document.getElementById('detail_status').value = data.status;
        document.getElementById('detail_note').value = data.admin_note || '';
        
        lucide.createIcons();
    }
</script>
@endsection
