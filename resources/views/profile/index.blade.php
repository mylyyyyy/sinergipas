@extends('layouts.app')

@section('title', 'Profil Saya')
@section('header-title', 'Pusat Kendali Profil')

@section('content')
<div class="space-y-8 page-fade">
    <!-- Hero Profile Section -->
    <div class="relative overflow-hidden rounded-[48px] bg-slate-900 p-1 font-sans shadow-2xl card-3d mb-8 border border-white/5">
        <div class="absolute -left-20 -top-20 h-96 w-96 rounded-full bg-blue-600/20 blur-[120px] animate-pulse"></div>
        <div class="absolute -right-20 -bottom-20 h-96 w-96 rounded-full bg-indigo-500/10 blur-[100px]"></div>
        
        <div class="relative z-10 bg-slate-900/40 backdrop-blur-3xl rounded-[44px] px-8 py-12 lg:px-12 flex flex-col lg:flex-row items-center gap-12">
            <!-- Photo Identity -->
            <div class="relative group">
                <div class="absolute inset-0 bg-blue-500 blur-2xl opacity-20 group-hover:opacity-40 transition-opacity duration-700 animate-pulse"></div>
                <div class="relative">
                    <div class="w-48 h-48 lg:w-56 lg:h-56 rounded-[40px] p-1 bg-linear-to-br from-blue-500 via-indigo-500 to-purple-600 shadow-2xl transform group-hover:rotate-3 transition-transform duration-700">
                        <div class="w-full h-full rounded-[38px] bg-slate-900 overflow-hidden ring-4 ring-slate-900">
                            <img id="main-avatar" src="{{ $employee->photo ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=0F172A&color=fff&size=512' }}" 
                                 class="w-full h-full object-cover transition-all duration-700 group-hover:scale-110" alt="Profile">
                        </div>
                    </div>
                    <!-- Photo Action Badge -->
                    <button onclick="document.getElementById('photo-input').click()" class="absolute -bottom-4 -right-4 w-14 h-14 bg-white text-slate-900 rounded-2xl flex items-center justify-center shadow-2xl hover:bg-blue-600 hover:text-white transition-all duration-300 btn-3d active:scale-90 group/btn">
                        <i data-lucide="camera" class="w-6 h-6 group-hover/btn:scale-110 transition-transform"></i>
                    </button>
                </div>
            </div>

            <!-- Identity Info -->
            <div class="flex-1 text-center lg:text-left space-y-6">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-blue-500/10 border border-blue-500/20 rounded-full backdrop-blur-md">
                        <span class="w-2 h-2 rounded-full bg-blue-500 animate-ping"></span>
                        <span class="text-[10px] font-black text-blue-400 uppercase tracking-[0.2em] italic">{{ $employee->employee_type_label ?? 'Administrator' }}</span>
                    </div>
                    <h1 class="text-4xl lg:text-5xl font-black text-white italic tracking-tight leading-tight">
                        {{ $user->name }}
                    </h1>
                    <div class="flex flex-wrap justify-center lg:justify-start items-center gap-4 text-slate-400 font-bold">
                        <div class="flex items-center gap-2">
                            <i data-lucide="fingerprint" class="w-4 h-4 text-blue-500"></i>
                            <span class="text-sm font-mono tracking-tighter">{{ $employee->nip ?? 'ADMIN-'.$user->id }}</span>
                        </div>
                        <div class="w-1.5 h-1.5 rounded-full bg-slate-700"></div>
                        <div class="flex items-center gap-2 text-sm italic">
                            <i data-lucide="building-2" class="w-4 h-4 text-indigo-500"></i>
                            {{ $employee->work_unit->name ?? 'Lembaga Pemasyarakatan Jombang' }}
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div class="p-4 bg-white/5 border border-white/10 rounded-3xl backdrop-blur-sm group hover:bg-white/10 transition-all duration-500">
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Pangkat/Gol</p>
                        <p class="text-sm font-black text-amber-400 italic">{{ $employee->rank_relation->name ?? '-' }}</p>
                    </div>
                    <div class="p-4 bg-white/5 border border-white/10 rounded-3xl backdrop-blur-sm group hover:bg-white/10 transition-all duration-500">
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Jabatan</p>
                        <p class="text-sm font-black text-blue-400 italic truncate" title="{{ $employee->position ?? 'Admin' }}">{{ $employee->position ?? 'Admin' }}</p>
                    </div>
                    <div class="hidden sm:block p-4 bg-white/5 border border-white/10 rounded-3xl backdrop-blur-sm group hover:bg-white/10 transition-all duration-500">
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Email Aktif</p>
                        <p class="text-sm font-black text-indigo-400 italic truncate">{{ $user->email }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-12">
        <!-- Left Col: Form Edit -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden card-3d">
                <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-200">
                            <i data-lucide="user-cog" class="w-5 h-5 text-white"></i>
                        </div>
                        <h3 class="text-lg font-black text-slate-900 italic uppercase tracking-tighter">Pengaturan Akun</h3>
                    </div>
                </div>
                
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="p-8 space-y-8">
                    @csrf @method('PUT')
                    
                    <!-- Hidden Photo Input -->
                    <input type="file" name="photo" id="photo-input" class="hidden" accept="image/*" onchange="previewImage(this)">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Nama Lengkap Anda</label>
                            <div class="relative group">
                                <i data-lucide="user" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-300 group-focus-within:text-blue-500 transition-colors"></i>
                                <input type="text" name="name" value="{{ $user->name }}" required 
                                    class="w-full pl-12 pr-6 py-4 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-bold focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-50 outline-none transition-all">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Email Instansi</label>
                            <div class="relative group">
                                <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-300 group-focus-within:text-blue-500 transition-colors"></i>
                                <input type="email" value="{{ $user->email }}" disabled 
                                    class="w-full pl-12 pr-6 py-4 rounded-2xl border border-slate-100 bg-slate-100/50 text-slate-400 text-sm font-bold cursor-not-allowed">
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-amber-50/50 border border-amber-100 rounded-3xl space-y-6">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-amber-500 text-white rounded-lg flex items-center justify-center shadow-md">
                                <i data-lucide="key-round" class="w-4 h-4"></i>
                            </div>
                            <h4 class="text-xs font-black text-amber-800 uppercase tracking-widest">Ubah Kata Sandi (Opsional)</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Password Baru</label>
                                <input type="password" name="password" placeholder="Minimal 8 karakter..." 
                                    class="w-full px-6 py-4 rounded-2xl border border-slate-200 bg-white text-sm font-bold focus:border-amber-500 focus:ring-4 focus:ring-amber-50 outline-none transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" placeholder="Ulangi password..." 
                                    class="w-full px-6 py-4 rounded-2xl border border-slate-200 bg-white text-sm font-bold focus:border-amber-500 focus:ring-4 focus:ring-amber-50 outline-none transition-all">
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-end gap-4 pt-4">
                        @if($employee->getRawOriginal('photo'))
                        <button type="button" onclick="confirmDeletePhoto()" class="px-6 py-4 text-xs font-black text-red-500 uppercase tracking-widest hover:bg-red-50 rounded-2xl transition-all">
                            Hapus Foto Profil
                        </button>
                        @endif
                        <button type="submit" class="px-10 py-4 bg-slate-900 text-white text-xs font-black uppercase tracking-[0.2em] rounded-2xl shadow-2xl shadow-slate-200 hover:bg-blue-600 transition-all transform active:scale-95 btn-3d">
                            Perbarui Data Profil
                        </button>
                    </div>
                </form>
            </div>

            <!-- Report Issues Section -->
            <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden card-3d">
                <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-red-600 rounded-xl flex items-center justify-center shadow-lg shadow-red-200">
                            <i data-lucide="alert-triangle" class="w-5 h-5 text-white"></i>
                        </div>
                        <h3 class="text-lg font-black text-slate-900 italic uppercase tracking-tighter">Laporan Kendala</h3>
                    </div>
                </div>
                
                <div class="p-8 space-y-8">
                    <form action="{{ route('profile.report') }}" method="POST" class="space-y-6">
                        @csrf
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Subjek Laporan</label>
                                <input type="text" name="subject" required placeholder="Contoh: Kesalahan Slip Gaji, Masalah Absensi..." 
                                    class="w-full px-6 py-4 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-bold focus:bg-white focus:border-red-500 focus:ring-4 focus:ring-red-50 outline-none transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Detail Masalah</label>
                                <textarea name="message" required rows="4" placeholder="Jelaskan secara detail kendala yang Anda alami..." 
                                    class="w-full px-6 py-4 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-bold focus:bg-white focus:border-red-500 focus:ring-4 focus:ring-red-50 outline-none transition-all resize-none"></textarea>
                            </div>
                        </div>
                        <button type="submit" class="w-full py-4 bg-red-600 text-white text-xs font-black uppercase tracking-[0.2em] rounded-2xl shadow-xl shadow-red-100 hover:bg-slate-900 transition-all transform active:scale-95 btn-3d">
                            Kirim Laporan ke Administrator
                        </button>
                    </form>

                    <!-- User's Issues History -->
                    @if($myIssues->count() > 0)
                    <div class="space-y-4 pt-4 border-t border-slate-100">
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Riwayat Laporan Anda</h4>
                        <div class="space-y-3">
                            @foreach($myIssues as $issue)
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between group">
                                <div class="min-w-0">
                                    <p class="text-sm font-black text-slate-900 group-hover:text-red-600 transition-colors truncate">{{ $issue->subject }}</p>
                                    <p class="text-[10px] font-bold text-slate-400">{{ $issue->created_at->translatedFormat('d F Y') }}</p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border
                                    @if($issue->status === 'open') bg-amber-50 text-amber-600 border-amber-100
                                    @elseif($issue->status === 'in_progress') bg-blue-50 text-blue-600 border-blue-100
                                    @else bg-green-50 text-green-600 border-green-100 @endif">
                                    {{ $issue->status }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Col: Activity Log -->
        <div class="space-y-8">
            <div class="bg-slate-900 rounded-[40px] p-1 shadow-2xl overflow-hidden card-3d">
                <div class="bg-slate-900/50 backdrop-blur-xl p-8 space-y-8">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-indigo-500 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20">
                            <i data-lucide="activity" class="w-5 h-5 text-white"></i>
                        </div>
                        <h3 class="text-lg font-black text-white italic uppercase tracking-tighter">Aktivitas Terakhir</h3>
                    </div>

                    <div class="space-y-6 relative before:absolute before:left-[19px] before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-800">
                        @forelse($logs as $log)
                        <div class="relative pl-12 group">
                            <div class="absolute left-0 top-1 w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center z-10 group-hover:bg-blue-600 group-hover:border-blue-500 transition-all">
                                <i data-lucide="{{ $log->activity === 'login' ? 'log-in' : ($log->activity === 'download' ? 'download' : 'edit-3') }}" class="w-4 h-4 text-slate-400 group-hover:text-white transition-colors"></i>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-slate-300 group-hover:text-white transition-colors">{{ $log->details }}</p>
                                <p class="text-[10px] font-bold text-slate-500 italic">{{ $log->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        @empty
                        <div class="py-12 text-center space-y-4">
                            <i data-lucide="coffee" class="w-12 h-12 text-slate-800 mx-auto"></i>
                            <p class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Belum ada aktivitas tercatat</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Security Info Card -->
            <div class="bg-indigo-600 rounded-[40px] p-8 text-white relative overflow-hidden group shadow-2xl shadow-indigo-200">
                <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-110 transition-transform duration-700">
                    <i data-lucide="shield-check" class="w-32 h-32 text-white"></i>
                </div>
                <div class="relative z-10 space-y-4">
                    <h4 class="text-sm font-black uppercase tracking-widest italic flex items-center gap-3">
                        <i data-lucide="lock" class="w-4 h-4"></i> Tips Keamanan
                    </h4>
                    <p class="text-xs font-medium leading-relaxed text-indigo-100">
                        Gunakan password yang kuat dengan kombinasi huruf, angka, dan simbol. Jangan berikan akses akun Anda kepada siapapun.
                    </p>
                    <div class="pt-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 rounded-lg backdrop-blur-md">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                            <span class="text-[9px] font-black uppercase tracking-tighter">Verified Device</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Only Logout Button -->
            <div class="bg-white rounded-[40px] p-8 border-2 border-red-50 shadow-sm space-y-6 card-3d">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-red-50 text-red-600 rounded-xl flex items-center justify-center">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-black text-slate-900 italic uppercase tracking-tighter">Sesi Aplikasi</h3>
                </div>
                <p class="text-xs font-medium text-slate-500 leading-relaxed">
                    Pastikan Anda keluar jika menggunakan perangkat bersama untuk menjaga keamanan data kepegawaian Anda.
                </p>
                <form action="{{ route('logout') }}" method="POST" class="no-loader">
                    @csrf
                    <button type="submit" class="w-full py-4 bg-slate-900 text-white text-xs font-black uppercase tracking-[0.2em] rounded-2xl shadow-xl hover:bg-red-600 transition-all transform active:scale-95 btn-3d flex items-center justify-center gap-3">
                        <i data-lucide="power" class="w-4 h-4"></i>
                        Keluar dari Aplikasi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Photo Form -->
<form id="delete-photo-form" action="{{ route('profile.delete-photo') }}" method="POST" class="hidden no-loader">
    @csrf @method('DELETE')
</form>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('main-avatar').src = e.target.result;
                document.getElementById('main-avatar').classList.add('opacity-80');
                
                Swal.fire({
                    icon: 'info',
                    title: 'Preview Foto',
                    text: 'Foto telah dimuat. Klik "Perbarui Data Profil" untuk menyimpan perubahan.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                    customClass: { popup: 'rounded-2xl' }
                });
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function confirmDeletePhoto() {
        Swal.fire({
            title: 'Hapus Foto Profil?',
            text: "Avatar Anda akan dikembalikan ke identitas default.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-3xl' }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-photo-form').submit();
            }
        });
    }
</script>

@if(session('success'))
<script>
    window.addEventListener('DOMContentLoaded', () => {
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", confirmButtonColor: '#0F172A', customClass: { popup: 'rounded-2xl' } });
    });
</script>
@endif

@if($errors->any())
<script>
    window.addEventListener('DOMContentLoaded', () => {
        Swal.fire({ icon: 'error', title: 'Gagal!', text: "{{ $errors->first() }}", confirmButtonColor: '#EF4444', customClass: { popup: 'rounded-2xl' } });
    });
</script>
@endif
@endsection
