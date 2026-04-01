@extends('layouts.app')

@section('title', 'Profil Saya')
@section('header-title', 'Pengaturan Akun')

@section('content')
<div class="max-w-5xl mx-auto">
    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="bg-white rounded-[56px] border border-[#EFEFEF] shadow-xl shadow-gray-100/50 overflow-hidden mb-10 transition-all duration-500 hover:shadow-2xl">
            <!-- Decorative Header -->
            <div class="h-48 bg-gradient-to-r from-[#1E2432] to-[#E85A4F] relative">
                <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
            </div>
            
            <div class="px-12 pb-12">
                <div class="relative -mt-20 mb-10 flex flex-col md:flex-row items-end gap-8">
                    <!-- Photo Upload with Preview -->
                    <div class="relative group">
                        <div class="w-40 h-40 rounded-[40px] border-[6px] border-white bg-[#F5F4F2] overflow-hidden shadow-2xl flex items-center justify-center text-[#8A8A8A]">
                            @if($employee && $employee->photo)
                                <img id="avatar-preview" src="{{ $employee->photo }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            @else
                                <div id="avatar-placeholder" class="text-center">
                                    <i data-lucide="user" class="w-16 h-16 mx-auto opacity-20"></i>
                                </div>
                                <img id="avatar-preview" class="hidden w-full h-full object-cover">
                            @endif
                        </div>

                        <label for="photoInput" class="absolute -bottom-2 -right-2 bg-[#E85A4F] p-3 rounded-2xl shadow-xl cursor-pointer hover:bg-[#d44d42] transition-all hover:scale-110 active:scale-95 border-4 border-white">
                            <i data-lucide="camera" class="w-5 h-5 text-white"></i>
                            <input type="file" id="photoInput" name="photo" class="hidden" onchange="previewImage(this)">
                        </label>
                    </div>

                    <div class="flex-1 pb-2">
                        <h2 class="text-3xl font-black text-[#1E2432] tracking-tight">{{ $user->name }}</h2>
                        <div class="flex items-center gap-3 mt-2">
                            <span class="px-4 py-1.5 bg-red-50 text-[#E85A4F] text-[10px] font-black uppercase tracking-widest rounded-full border border-red-100">
                                {{ strtoupper($user->role) }}
                            </span>
                            <span class="text-sm font-bold text-[#8A8A8A] flex items-center gap-1">
                                <i data-lucide="mail" class="w-4 h-4"></i> {{ $user->email }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <!-- Section: Personal Info -->
                    <div class="space-y-6">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-8 h-8 bg-[#FCFBF9] rounded-xl flex items-center justify-center border border-[#EFEFEF]">
                                <i data-lucide="info" class="w-4 h-4 text-[#1E2432]"></i>
                            </div>
                            <h4 class="text-sm font-black text-[#1E2432] uppercase tracking-widest">Informasi Dasar</h4>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.2em] ml-1">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ $user->name }}" required 
                                class="w-full px-6 py-4 rounded-3xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold text-[#1E2432] focus:ring-4 focus:ring-red-500/5 focus:border-[#E85A4F] outline-none transition-all">
                        </div>

                        @if($employee)
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.2em] ml-1">NIP</label>
                                <input type="text" value="{{ $employee->nip }}" readonly class="w-full px-6 py-4 rounded-3xl border border-[#EFEFEF] bg-gray-50 text-[#8A8A8A] text-sm font-bold outline-none cursor-not-allowed">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.2em] ml-1">Jabatan</label>
                                <input type="text" value="{{ $employee->position }}" readonly class="w-full px-6 py-4 rounded-3xl border border-[#EFEFEF] bg-gray-50 text-[#8A8A8A] text-sm font-bold outline-none cursor-not-allowed">
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Section: Security -->
                    <div class="space-y-6">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-8 h-8 bg-[#FCFBF9] rounded-xl flex items-center justify-center border border-[#EFEFEF]">
                                <i data-lucide="lock" class="w-4 h-4 text-[#1E2432]"></i>
                            </div>
                            <h4 class="text-sm font-black text-[#1E2432] uppercase tracking-widest">Keamanan Akun</h4>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.2em] ml-1">Password Baru</label>
                            <input type="password" name="password" placeholder="••••••••••••"
                                class="w-full px-6 py-4 rounded-3xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold text-[#1E2432] focus:ring-4 focus:ring-red-500/5 focus:border-[#E85A4F] outline-none transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.2em] ml-1">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" placeholder="••••••••••••"
                                class="w-full px-6 py-4 rounded-3xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold text-[#1E2432] focus:ring-4 focus:ring-red-500/5 focus:border-[#E85A4F] outline-none transition-all">
                        </div>
                        
                        <p class="text-[10px] text-[#8A8A8A] font-medium leading-relaxed bg-blue-50 p-4 rounded-2xl border border-blue-100">
                            <i data-lucide="shield-check" class="w-3 h-3 inline mr-1 text-blue-600"></i>
                            Kosongkan kolom password jika Anda tidak ingin mengubah password saat ini.
                        </p>
                    </div>
                </div>

                <!-- Section: My Activity -->
                <div class="mt-12 pt-12 border-t border-[#EFEFEF]">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-8 h-8 bg-[#FCFBF9] rounded-xl flex items-center justify-center border border-[#EFEFEF]">
                            <i data-lucide="history" class="w-4 h-4 text-[#1E2432]"></i>
                        </div>
                        <h4 class="text-sm font-black text-[#1E2432] uppercase tracking-widest">Aktivitas Saya (10 Terakhir)</h4>
                    </div>

                    <div class="bg-[#FCFBF9] rounded-[32px] border border-[#EFEFEF] overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-white/50">
                                    <th class="px-8 py-4 text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest">Aktivitas</th>
                                    <th class="px-8 py-4 text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest">Dokumen</th>
                                    <th class="px-8 py-4 text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest">Waktu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#EFEFEF]">
                                @forelse($logs as $log)
                                <tr>
                                    <td class="px-8 py-4 text-xs font-bold text-[#1E2432] uppercase tracking-tighter">{{ $log->activity }}</td>
                                    <td class="px-8 py-4 text-xs text-[#8A8A8A]">{{ $log->document->title ?? 'N/A' }}</td>
                                    <td class="px-8 py-4 text-xs font-bold text-[#ABABAB]">{{ $log->created_at->diffForHumans() }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="px-8 py-10 text-center text-[#ABABAB] italic text-xs">Belum ada riwayat aktivitas.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-12 pt-8 border-t border-[#EFEFEF] flex flex-col md:flex-row justify-between items-center gap-6">
                    <p class="text-xs text-[#8A8A8A] font-bold uppercase tracking-widest">Terakhir diperbarui: {{ $user->updated_at->format('d M Y, H:i') }}</p>
                    <button type="submit" class="w-full md:w-auto bg-[#1E2432] text-white px-12 py-5 rounded-[24px] font-black hover:bg-[#E85A4F] transition-all shadow-2xl hover:shadow-red-200 active:scale-[0.98] flex items-center justify-center gap-3">
                        Perbarui Profil & Keamanan
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Report Issue Section -->
    <div class="bg-white rounded-[56px] border border-[#EFEFEF] shadow-sm p-12 mb-10">
        <div class="flex items-center gap-4 mb-10">
            <div class="w-14 h-14 bg-yellow-50 rounded-2xl flex items-center justify-center text-yellow-600 shadow-sm">
                <i data-lucide="message-circle" class="w-7 h-7"></i>
            </div>
            <div>
                <h3 class="text-2xl font-black text-[#1E2432]">Laporkan Masalah Data</h3>
                <p class="text-xs text-[#8A8A8A] font-bold uppercase tracking-widest mt-1">Koreksi NIP, Jabatan, atau Data Pribadi ke Admin</p>
            </div>
        </div>

        <form action="{{ route('profile.report') }}" method="POST" class="space-y-6">
            @csrf
            <div class="space-y-2">
                <label class="text-[10px] font-black text-[#1E2432] uppercase tracking-[0.2em] ml-1">Subjek Laporan</label>
                <input type="text" name="subject" required placeholder="Contoh: Kesalahan Penulisan NIP" class="w-full px-6 py-4 rounded-3xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold">
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black text-[#1E2432] uppercase tracking-[0.2em] ml-1">Detail Pesan</label>
                <textarea name="message" rows="4" required placeholder="Jelaskan detail kesalahan data Anda..." class="w-full px-6 py-4 rounded-3xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold"></textarea>
            </div>
            <button type="submit" class="bg-yellow-600 text-white px-10 py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-yellow-700 transition-all shadow-lg">
                Kirim Laporan
            </button>
        </form>
    </div>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('avatar-preview');
                const placeholder = document.getElementById('avatar-placeholder');
                
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                if(placeholder) placeholder.classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

@if(session('success'))
<script>
    Swal.fire({ 
        icon: 'success', 
        title: 'Profil Terupdate!', 
        text: "{{ session('success') }}", 
        confirmButtonColor: '#1E2432',
        customClass: { popup: 'rounded-[32px]' }
    });
</script>
@endif
@endsection
