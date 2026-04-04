<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sinergi PAS - @yield('title')</title>
    
    <!-- Scripts & Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Optimized Icon Loading -->
    <script src="https://unpkg.com/lucide@latest" defer></script>
    
    <link rel="icon" type="image/png" href="{{ asset('logo1.png') }}">
    
    <!-- SweetAlert & Progress Bar -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #FCFBF9; color: #1E2432; }
        .sidebar-item:hover { background-color: #F5F4F2; }
        .sidebar-item.active { background-color: #E85A4F; color: white !important; }
        .sidebar-item.active i { color: white !important; }
        .rounded-[40px] { border-radius: 40px; }
        
        /* Glassmorphism SweetAlert Custom */
        .swal2-popup { border-radius: 32px !important; padding: 2rem !important; }
        .swal2-title { font-weight: 800 !important; color: #1E2432 !important; }
        .swal2-confirm { border-radius: 16px !important; padding: 12px 32px !important; font-weight: 700 !important; }

        /* NProgress customization */
        #nprogress .bar { background: #E85A4F !important; height: 4px !important; }
        #nprogress .spinner-icon { border-top-color: #E85A4F !important; border-left-color: #E85A4F !important; }

        #global-loading {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            z-index: 9999;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .loader-ring {
            width: 56px;
            height: 56px;
            border: 6px solid #F5F4F2;
            border-bottom-color: #E85A4F;
            border-radius: 50%;
            animation: rotation 0.6s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }
        @keyframes rotation { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* Smooth page transition */
        .page-fade { animation: fadeIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="antialiased">
    <div id="global-loading">
        <span class="loader-ring"></span>
        <p class="mt-6 text-[10px] font-black uppercase tracking-[0.3em] text-[#1E2432]">Sinkronisasi Sistem...</p>
    </div>

    <div class="flex min-h-screen">
        <aside class="w-64 bg-white border-r border-[#EFEFEF] flex flex-col px-6 py-8 fixed h-full z-20">
            <div class="flex items-center gap-3 mb-12">
                <img src="{{ asset('logo1.png') }}" class="w-10 h-10 object-contain">
                <h1 class="text-lg font-black text-[#1E2432] tracking-tighter uppercase">SINERGI PAS</h1>
            </div>
            <nav class="flex-1 space-y-2">
                <a href="{{ route('dashboard') }}" class="sidebar-item {{ request()->routeIs('dashboard') ? 'active shadow-lg shadow-red-100' : 'text-[#8A8A8A]' }} flex items-center gap-3 px-4 py-3 rounded-xl transition-all">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    <span class="text-sm font-bold">Dashboard</span>
                </a>
                @if(auth()->user()->role === 'superadmin')
                <a href="{{ route('employees.index') }}" class="sidebar-item {{ request()->routeIs('employees.*') ? 'active shadow-lg shadow-red-100' : 'text-[#8A8A8A]' }} flex items-center gap-3 px-4 py-3 rounded-xl transition-all">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    <span class="text-sm font-bold">Data Pegawai</span>
                </a>
                @endif
                <a href="{{ route('documents.index') }}" class="sidebar-item {{ request()->routeIs('documents.*') ? 'active shadow-lg shadow-red-100' : 'text-[#8A8A8A]' }} flex items-center justify-between px-4 py-3 rounded-xl transition-all">
                    <div class="flex items-center gap-3">
                        <i data-lucide="file-text" class="w-5 h-5"></i>
                        <span class="text-sm font-bold">Pusat Dokumen</span>
                    </div>
                    @if(auth()->user()->role === 'superadmin')
                        @php $pendingCount = \App\Models\Document::where('status', 'pending')->count(); @endphp
                        @if($pendingCount > 0)
                            <span class="bg-[#1E2432] text-white text-[9px] font-black px-2 py-0.5 rounded-lg transition-all">{{ $pendingCount }}</span>
                        @endif
                    @endif
                </a>
                @if(auth()->user()->role === 'superadmin')
                <a href="{{ route('admin.report-issues.index') }}" class="sidebar-item {{ request()->routeIs('admin.report-issues.*') ? 'active shadow-lg shadow-red-100' : 'text-[#8A8A8A]' }} flex items-center justify-between px-4 py-3 rounded-xl transition-all">
                    <div class="flex items-center gap-3">
                        <i data-lucide="message-square" class="w-5 h-5"></i>
                        <span class="text-sm font-bold">Laporan Masalah</span>
                    </div>
                    @php $openIssuesCount = \App\Models\ReportIssue::where('status', 'open')->count(); @endphp
                    @if($openIssuesCount > 0)
                        <span class="bg-white text-[#E85A4F] text-[9px] font-black px-2 py-0.5 rounded-lg shadow-sm">{{ $openIssuesCount }}</span>
                    @endif
                </a>
                <a href="{{ route('audit.index') }}" class="sidebar-item {{ request()->routeIs('audit.*') ? 'active shadow-lg shadow-red-100' : 'text-[#8A8A8A]' }} flex items-center gap-3 px-4 py-3 rounded-xl transition-all">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                    <span class="text-sm font-bold">Laporan Audit</span>
                </a>
                <a href="{{ route('settings.index') }}" class="sidebar-item {{ request()->routeIs('settings.*') ? 'active shadow-lg shadow-red-100' : 'text-[#8A8A8A]' }} flex items-center gap-3 px-4 py-3 rounded-xl transition-all">
                    <i data-lucide="settings-2" class="w-5 h-5"></i>
                    <span class="text-sm font-bold">Pengaturan</span>
                </a>
                @endif
            </nav>
            <div class="pt-6 border-t border-[#EFEFEF]">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full sidebar-item flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-red-500 hover:bg-red-50 font-black text-xs uppercase tracking-widest">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        <span>Keluar Aplikasi</span>
                    </button>
                </form>
            </div>
        </aside>

        <main class="flex-1 ml-64 min-h-screen relative">
            @php 
                $activeBanner = \App\Models\Announcement::active()->where('type', 'banner')->latest()->first();
                $activePopup = \App\Models\Announcement::active()->where('type', 'popup')->latest()->first();
                $bannerBg = \App\Models\Setting::getValue('running_text_bg', '#1E2432');
                $bannerColor = \App\Models\Setting::getValue('running_text_color', '#FFFFFF');
                $bannerSpeed = \App\Models\Setting::getValue('running_text_speed', '20');
            @endphp

            @if($activeBanner)
            <div class="py-2 overflow-hidden relative border-b border-white/10" style="background-color: {{ $bannerBg }}; color: {{ $bannerColor }};">
                <div class="whitespace-nowrap animate-marquee inline-block font-bold text-[10px] uppercase tracking-[0.2em]">
                    <span class="mx-10"><i data-lucide="megaphone" class="w-3 h-3 inline mr-2"></i> PENGUMUMAN: {{ $activeBanner->message }}</span>
                    <span class="mx-10"><i data-lucide="megaphone" class="w-3 h-3 inline mr-2"></i> PENGUMUMAN: {{ $activeBanner->message }}</span>
                </div>
            </div>
            <style>
                @keyframes marquee { 0% { transform: translateX(100%); } 100% { transform: translateX(-100%); } }
                .animate-marquee { animation: marquee {{ $bannerSpeed }}s linear infinite; }
            </style>
            @endif

            <header class="h-20 bg-white border-b border-[#EFEFEF] flex items-center justify-between px-10 sticky top-0 z-10">
                <h2 class="text-xl font-black text-[#1E2432] tracking-tight italic">@yield('header-title')</h2>
                <div class="flex items-center gap-4">
                    <!-- Notification Bell -->
                    <div class="relative">
                        @php $unreadCount = auth()->user()->unreadNotifications->count(); @endphp
                        <button onclick="toggleNotifications()" class="relative w-10 h-10 bg-[#FCFBF9] rounded-xl border border-[#EFEFEF] flex items-center justify-center text-[#8A8A8A] hover:text-[#E85A4F] hover:shadow-md transition-all">
                            <i data-lucide="bell" class="w-5 h-5"></i>
                            @if($unreadCount > 0)
                                <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-[10px] font-black rounded-full border-2 border-white flex items-center justify-center animate-bounce">{{ $unreadCount }}</span>
                            @endif
                        </button>

                        <div id="notificationDropdown" class="hidden absolute right-0 mt-4 w-80 bg-white rounded-[32px] border border-[#EFEFEF] shadow-2xl z-50 overflow-hidden animate-in fade-in slide-in-from-top-2 duration-300">
                            <div class="p-6 border-b border-[#EFEFEF] flex justify-between items-center bg-[#FCFBF9]/50">
                                <h3 class="text-xs font-black text-[#1E2432] uppercase tracking-widest">Notifikasi</h3>
                                @if($unreadCount > 0)
                                    <form action="{{ route('notifications.mark-read') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-[9px] font-black text-[#E85A4F] uppercase hover:underline">Tandai Baca</button>
                                    </form>
                                @endif
                            </div>
                            <div class="max-h-96 overflow-y-auto custom-scrollbar">
                                @forelse(auth()->user()->unreadNotifications as $notification)
                                    <div class="p-5 border-b border-[#EFEFEF] hover:bg-[#FCFBF9] transition-all cursor-pointer">
                                        <p class="text-xs font-bold text-[#1E2432] mb-1">{{ $notification->data['message'] }}</p>
                                        <p class="text-[9px] text-[#ABABAB] font-black uppercase">{{ $notification->created_at->diffForHumans() }}</p>
                                    </div>
                                @empty
                                    <div class="p-10 text-center">
                                        <i data-lucide="bell-off" class="w-10 h-10 text-gray-100 mx-auto mb-3"></i>
                                        <p class="text-[10px] font-bold text-[#ABABAB] uppercase tracking-widest">Tidak ada notifikasi baru</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('profile.index') }}" class="flex items-center gap-3 p-2 bg-[#FCFBF9] rounded-2xl border border-[#EFEFEF] hover:shadow-md transition-all">
                        @php $sidebarEmployee = \App\Models\Employee::where('user_id', auth()->id())->first(); @endphp
                        <div class="w-10 h-10 bg-[#E85A4F] rounded-xl flex items-center justify-center text-white font-black overflow-hidden text-xs shadow-lg shadow-red-100">
                            @if($sidebarEmployee && $sidebarEmployee->photo)
                                <img src="{{ $sidebarEmployee->photo }}" class="w-full h-full object-cover">
                            @else
                                {{ substr(auth()->user()->name, 0, 1) }}
                            @endif
                        </div>

                        <span class="text-xs font-black text-[#1E2432] pr-2">{{ auth()->user()->name }}</span>
                    </a>
                </div>
            </header>
            <div class="p-10 page-fade">@yield('content')</div>
        </main>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            NProgress.done();
        });

        window.addEventListener('beforeunload', () => {
            NProgress.start();
        });

        function toggleNotifications() {
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.classList.toggle('hidden');
        }

        window.addEventListener('click', function(e) {
            const dropdown = document.getElementById('notificationDropdown');
            if (!e.target.closest('.relative')) {
                dropdown.classList.add('hidden');
            }
        });

        // Instant Loader for Forms
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                if(!this.classList.contains('no-loader')) {
                    document.getElementById('global-loading').style.display = 'flex';
                }
            });
        });

        @if(isset($activePopup))
        window.addEventListener('load', () => {
            if (!sessionStorage.getItem('announcement_seen_{{ $activePopup->id }}')) {
                Swal.fire({
                    title: 'Informasi Penting!',
                    text: '{{ $activePopup->message }}',
                    icon: 'info',
                    confirmButtonColor: '#1E2432',
                    confirmButtonText: 'Saya Mengerti',
                    customClass: { popup: 'rounded-[40px]' }
                }).then(() => {
                    sessionStorage.setItem('announcement_seen_{{ $activePopup->id }}', 'true');
                });
            }
        });
        @endif
    </script>
    @stack('scripts')
</body>
</html>
