<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Sinergi PAS - @yield('title')</title>
    
    <!-- Scripts & Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Optimized Icon Loading -->
    <script src="https://unpkg.com/lucide@latest" defer></script>
    
    <link rel="icon" type="image/png" href="{{ asset('logo1.png') }}">
    
    <!-- PWA Support -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0F172A">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SinergiPAS">
    <link rel="apple-touch-icon" href="{{ asset('logo1.png') }}">
    
    <!-- iOS Splash Screens -->
    <link rel="apple-touch-startup-image" href="{{ asset('logo1.png') }}">

    <!-- SweetAlert & Progress Bar -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>

    <style>
        :root {
            --font-custom: 'Plus Jakarta Sans', sans-serif;
            --font-body: var(--font-custom);
            --font-display: var(--font-custom);
            --font-ui: var(--font-custom);
            --font-data: var(--font-custom);
            --font-caption: var(--font-custom);
            
            /* Premium Palette */
            --color-primary: #0F172A;    /* Deep Navy */
            --color-secondary: #1E293B;  /* Slate Blue */
            --color-accent: #B45309;     /* Refined Gold/Amber */
            --color-accent-light: #FDE68A;
            --color-surface: #F8FAFC;    /* Clean Background */
            --color-card: #FFFFFF;
            --color-border: #E2E8F0;
        }

        body {
            font-family: var(--font-body);
            background: var(--color-surface);
            color: var(--color-primary);
            font-size: 14px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
            width: 100%;
        }

        h1, h2, h3, h4, h5, h6 { 
            font-family: var(--font-display); 
            letter-spacing: -0.02em; 
            font-weight: 700;
        }

        /* 3D Animations & Hover Effects */
        .card-3d {
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            perspective: 1000px;
        }
        .card-3d:hover {
            transform: translateY(-8px) rotateX(2deg) rotateY(1deg);
            box-shadow: 0 20px 40px -12px rgba(15, 23, 42, 0.12);
        }

        .btn-3d {
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
        }
        .btn-3d:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -6px rgba(15, 23, 42, 0.3);
        }
        .btn-3d:active {
            transform: translateY(0);
        }

        .glass-premium {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Refined Sidebar */
        .sidebar-item { 
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); 
        }
        .sidebar-item:hover { 
            background-color: rgba(15, 23, 42, 0.05); 
        }
        .sidebar-item.active { 
            background: var(--color-primary); 
            color: white !important; 
            box-shadow: 0 10px 20px -5px rgba(15, 23, 42, 0.3);
        }
        .sidebar-item.active i { color: white !important; }

        .bottom-nav-item.active {
            color: var(--color-primary) !important;
        }
        .bottom-nav-item.active i {
            color: var(--color-primary) !important;
            transform: translateY(-2px);
        }

        /* Component Refinement */
        .rounded-premium { border-radius: 1.5rem; }
        .rounded-button { border-radius: 0.875rem; }

        /* Smooth Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94A3B8; }

        .page-fade { animation: pageIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1); }
        @keyframes pageIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

        /* Marquee Animation */
        @keyframes marquee-js {
            0% { transform: translateX(100vw); }
            100% { transform: translateX(-100%); }
        }
        .animate-marquee-js {
            display: inline-block;
            white-space: nowrap;
            animation: marquee-js linear infinite;
            will-change: transform;
        }

        /* Mobile Optimizations */
        @media (max-width: 1024px) {
            body { padding-bottom: 90px !important; }
            .mobile-bottom-nav {
                position: fixed !important;
                bottom: 0 !important;
                left: 0 !important;
                right: 0 !important;
                z-index: 9999 !important;
                transform: translateZ(0); /* Force GPU acceleration */
                -webkit-transform: translateZ(0);
            }
        }

        #global-loading {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: opacity 0.5s ease;
        }
        .loader-ring {
            width: 48px;
            height: 48px;
            border: 4px solid #F1F5F9;
            border-top-color: #0F172A;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
    @stack('styles')
</head>
<body class="antialiased">
    <div id="global-loading">
        <span class="loader-ring"></span>
        <p class="mt-4 text-[11px] font-bold uppercase tracking-widest text-slate-500">Memuat Data...</p>
    </div>

    <div id="mobileSidebarBackdrop" class="fixed inset-0 z-30 bg-slate-900/40 backdrop-blur-sm lg:hidden hidden" onclick="closeSidebar()"></div>

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside id="appSidebar" class="fixed inset-y-0 left-0 z-40 flex h-full w-64 -translate-x-full flex-col border-r border-slate-200 bg-white px-6 py-8 transition-transform duration-300 lg:translate-x-0 shrink-0">
            <div class="flex items-center gap-3 mb-10 px-2" onclick="window.location.href='{{ route('dashboard') }}'" style="cursor: pointer;">
                <img src="{{ asset('logo1.png') }}" class="w-9 h-9 object-contain">
                <div>
                    <h1 class="text-base font-bold text-slate-900 leading-tight uppercase tracking-tight">SINERGI PAS</h1>
                    <p class="text-[10px] text-slate-400 font-medium">Lapas Jombang</p>
                </div>
            </div>
            
            <nav class="flex-1 space-y-1 custom-scrollbar overflow-y-auto">
                <a href="{{ route('dashboard') }}" class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : 'text-slate-500' }} flex items-center gap-3 px-4 py-3 rounded-xl">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    <span class="text-sm font-semibold">Dashboard</span>
                </a>
                
                @if(auth()->user()->role === 'superadmin')
                <div class="pt-4 pb-2 px-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Kepegawaian</p>
                </div>

                <a href="{{ route('employees.index') }}" class="sidebar-item {{ request()->routeIs('employees.*') ? 'active' : 'text-slate-500' }} flex items-center gap-3 px-4 py-3 rounded-xl">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    <span class="text-sm font-semibold">Data Pegawai</span>
                </a>

                <a href="{{ route('admin.ranks.index') }}" class="sidebar-item {{ request()->routeIs('admin.ranks.*') ? 'active' : 'text-slate-500' }} flex items-center gap-3 px-4 py-3 rounded-xl">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                    <span class="text-sm font-semibold">Golongan</span>
                </a>

                <a href="{{ route('admin.attendance.index') }}" class="sidebar-item {{ request()->routeIs('admin.attendance.*') ? 'active' : 'text-slate-500' }} flex items-center gap-3 px-4 py-3 rounded-xl">
                    <i data-lucide="fingerprint" class="w-5 h-5"></i>
                    <span class="text-sm font-semibold">Absensi</span>
                </a>

                <a href="{{ route('admin.schedules.index') }}" class="sidebar-item {{ request()->routeIs('admin.schedules.*') ? 'active' : 'text-slate-500' }} flex items-center gap-3 px-4 py-3 rounded-xl">
                    <i data-lucide="calendar" class="w-5 h-5"></i>
                    <span class="text-sm font-semibold">Jadwal Shift</span>
                </a>

                <a href="{{ route('admin.tunkins.index') }}" class="sidebar-item {{ request()->routeIs('admin.tunkins.*') ? 'active' : 'text-slate-500' }} flex items-center gap-3 px-4 py-3 rounded-xl">
                    <i data-lucide="coins" class="w-5 h-5"></i>
                    <span class="text-sm font-semibold">Besaran Tunkin</span>
                </a>

                <a href="{{ route('admin.payroll-settings.index') }}" class="sidebar-item {{ request()->routeIs('admin.payroll-settings.*') ? 'active' : 'text-slate-500' }} flex items-center gap-3 px-4 py-3 rounded-xl">
                    <i data-lucide="settings-2" class="w-5 h-5"></i>
                    <span class="text-sm font-semibold">Master Aturan</span>
                </a>

                <a href="{{ route('admin.squads.index') }}" class="sidebar-item {{ request()->routeIs('admin.squads.*') ? 'active' : 'text-slate-500' }} flex items-center gap-3 px-4 py-3 rounded-xl">
                    <i data-lucide="users-round" class="w-5 h-5"></i>
                    <span class="text-sm font-semibold">Manajemen Regu</span>
                </a>
                @endif

                <a href="{{ route('documents.index') }}" class="sidebar-item {{ request()->routeIs('documents.*') ? 'active' : 'text-slate-500' }} flex items-center justify-between px-4 py-3 rounded-xl">
                    <div class="flex items-center gap-3">
                        <i data-lucide="folder-open" class="w-5 h-5"></i>
                        <span class="text-sm font-semibold">Arsip Digital</span>
                    </div>
                    @if(auth()->user()->role === 'superadmin')
                        @php $pendingCount = \App\Models\Document::where('status', 'pending')->count(); @endphp
                        @if($pendingCount > 0)
                            <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $pendingCount }}</span>
                        @endif
                    @endif
                </a>

                @if(auth()->user()->role === 'superadmin')
                <div class="pt-4 pb-2 px-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Sistem</p>
                </div>
                
                <a href="{{ route('admin.report-issues.index') }}" class="sidebar-item {{ request()->routeIs('admin.report-issues.*') ? 'active' : 'text-slate-500' }} flex items-center justify-between px-4 py-3 rounded-xl">
                    <div class="flex items-center gap-3">
                        <i data-lucide="help-circle" class="w-5 h-5"></i>
                        <span class="text-sm font-semibold">Laporan</span>
                    </div>
                    @php $openIssuesCount = \App\Models\ReportIssue::where('status', 'open')->count(); @endphp
                    @if($openIssuesCount > 0)
                        <span class="bg-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $openIssuesCount }}</span>
                    @endif
                </a>

                <a href="{{ route('audit.index') }}" class="sidebar-item {{ request()->routeIs('audit.*') ? 'active' : 'text-slate-500' }} flex items-center gap-3 px-4 py-3 rounded-xl">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                    <span class="text-sm font-semibold">Audit Log</span>
                </a>

                <a href="{{ route('settings.index') }}" class="sidebar-item {{ request()->routeIs('settings.*') ? 'active' : 'text-slate-500' }} flex items-center gap-3 px-4 py-3 rounded-xl">
                    <i data-lucide="settings" class="w-5 h-5"></i>
                    <span class="text-sm font-semibold">Pengaturan</span>
                </a>
                @endif
            </nav>

            <div class="pt-6 mt-auto border-t border-slate-100">
                <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" class="no-loader">
                    @csrf
                    <button type="button" onclick="confirmLogout('logout-form-sidebar')" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-red-500 hover:bg-red-50 transition-colors text-sm font-bold">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="relative min-h-screen flex-1 min-w-0 lg:ml-64 bg-slate-50">
            <!-- Real-time Broadcast Container -->
            <div id="broadcast-container" class="z-30 relative hidden">
                <div id="marquee-wrapper" class="w-full overflow-hidden whitespace-nowrap py-3 shadow-md">
                    <div id="marquee-content" class="animate-marquee-js inline-block font-black uppercase tracking-widest italic">
                        <!-- Content will be injected by JS -->
                    </div>
                </div>
            </div>

            <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white/80 backdrop-blur-md px-6 lg:px-10">
                <div class="flex items-center gap-4">
                    <button type="button" onclick="toggleSidebar()" class="lg:hidden text-slate-500 hover:text-slate-900">
                        <i data-lucide="menu" class="h-6 w-6"></i>
                    </button>
                    <h2 class="text-lg font-bold text-slate-900 tracking-tight">@yield('header-title')</h2>
                </div>

                <div class="flex items-center gap-4">
                    <!-- Notification -->
                    <div class="relative">
                        @php $unreadCount = auth()->user()->unreadNotifications->count(); @endphp
                        <button onclick="toggleNotifications()" class="w-9 h-9 flex items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 transition-colors">
                            <i data-lucide="bell" class="w-5 h-5"></i>
                            @if($unreadCount > 0)
                                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
                            @endif
                        </button>

                        <div id="notificationDropdown" class="hidden absolute right-0 mt-3 w-80 bg-white rounded-2xl border border-slate-200 shadow-xl z-50 overflow-hidden">
                            <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest">Notifikasi</h3>
                                @if($unreadCount > 0)
                                    <form action="{{ route('notifications.mark-read') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-[10px] font-bold text-blue-600 hover:underline">Tandai Baca</button>
                                    </form>
                                @endif
                            </div>
                            <div class="max-h-80 overflow-y-auto custom-scrollbar">
                                @forelse(auth()->user()->unreadNotifications as $notification)
                                    <div class="p-4 border-b border-slate-50 hover:bg-slate-50 transition-colors cursor-pointer">
                                        <p class="text-xs font-semibold text-slate-900 mb-1 leading-relaxed">{{ $notification->data['message'] }}</p>
                                        <p class="text-[10px] text-slate-400 font-medium">{{ $notification->created_at->diffForHumans() }}</p>
                                    </div>
                                @empty
                                    <div class="p-8 text-center">
                                        <p class="text-xs font-medium text-slate-400">Tidak ada notifikasi baru</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="h-8 w-px bg-slate-200"></div>

                    <!-- Profile Dropdown -->
                    <div class="relative">
                        <button onclick="toggleProfileDropdown()" class="flex items-center gap-3 pl-1 group no-loader">
                            <div class="flex flex-col items-end hidden sm:flex">
                                <span class="text-xs font-bold text-slate-900 leading-none group-hover:text-blue-600 transition-colors">{{ auth()->user()->name }}</span>
                                <span class="text-[10px] text-slate-400 font-medium capitalize">{{ auth()->user()->role }}</span>
                            </div>
                            @php $sidebarEmployee = \App\Models\Employee::where('user_id', auth()->id())->first(); @endphp
                            <div class="w-9 h-9 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center text-slate-400 font-bold group-hover:border-blue-200 transition-colors">
                                @if($sidebarEmployee && $sidebarEmployee->photo)
                                    <img src="{{ $sidebarEmployee->photo }}" class="w-full h-full object-cover">
                                @else
                                    <i data-lucide="user" class="w-5 h-5"></i>
                                @endif
                            </div>
                        </button>

                        <div id="profileDropdown" class="hidden absolute right-0 mt-3 w-56 bg-white rounded-2xl border border-slate-200 shadow-xl z-50 overflow-hidden">
                            <div class="p-4 border-b border-slate-50 bg-slate-50/50 sm:hidden">
                                <p class="text-xs font-black text-slate-900 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ auth()->user()->role }}</p>
                            </div>
                            <div class="p-2">
                                <a href="{{ route('profile.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition-all">
                                    <i data-lucide="user-circle" class="w-4 h-4"></i>
                                    <span>Profil Saya</span>
                                </a>
                                <form id="logout-form-header" action="{{ route('logout') }}" method="POST" class="no-loader">
                                    @csrf
                                    <button type="button" onclick="confirmLogout('logout-form-header')" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold text-red-500 hover:bg-red-50 transition-all">
                                        <i data-lucide="log-out" class="w-4 h-4"></i>
                                        <span>Keluar Aplikasi</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <div class="page-fade p-6 lg:p-10 pb-32 lg:pb-10">@yield('content')</div>
        </main>
    </div>

    <!-- Mobile Bottom Navigation -->
    <nav class="mobile-bottom-nav lg:hidden bg-white/95 backdrop-blur-2xl border-t border-slate-200 flex items-center justify-around px-4 pt-3 pb-safe shadow-[0_-10px_40px_rgba(15,23,42,0.1)]">
        <a href="{{ route('dashboard') }}" class="bottom-nav-item {{ request()->routeIs('dashboard') ? 'active' : 'text-slate-400' }} flex flex-col items-center gap-1 group transition-all">
            <i data-lucide="layout-dashboard" class="w-6 h-6 transition-transform group-active:scale-90"></i>
            <span class="text-[9px] font-bold uppercase tracking-widest">Beranda</span>
        </a>
        
        @if(auth()->user()->role === 'superadmin')
        <a href="{{ route('employees.index') }}" class="bottom-nav-item {{ request()->routeIs('employees.*') ? 'active' : 'text-slate-400' }} flex flex-col items-center gap-1 group transition-all">
            <i data-lucide="users" class="w-6 h-6 transition-transform group-active:scale-90"></i>
            <span class="text-[9px] font-bold uppercase tracking-widest">Pegawai</span>
        </a>
        @else
        <a href="{{ route('documents.index') }}" class="bottom-nav-item {{ request()->routeIs('documents.*') ? 'active' : 'text-slate-400' }} flex flex-col items-center gap-1 group transition-all">
            <i data-lucide="folder-open" class="w-6 h-6 transition-transform group-active:scale-90"></i>
            <span class="text-[9px] font-bold uppercase tracking-widest">Arsip</span>
        </a>
        @endif

        <a href="{{ auth()->user()->role === 'superadmin' ? route('admin.attendance.index') : route('dashboard') }}" class="bottom-nav-item {{ request()->routeIs('admin.attendance.*') ? 'active' : 'text-slate-400' }} flex flex-col items-center gap-1 group transition-all">
            <i data-lucide="fingerprint" class="w-6 h-6 transition-transform group-active:scale-90"></i>
            <span class="text-[9px] font-bold uppercase tracking-widest">Absensi</span>
        </a>

        <a href="{{ route('profile.index') }}" class="bottom-nav-item {{ request()->routeIs('profile.index') ? 'active' : 'text-slate-400' }} flex flex-col items-center gap-1 group transition-all">
            <i data-lucide="user-circle" class="w-6 h-6 transition-transform group-active:scale-90"></i>
            <span class="text-[9px] font-bold uppercase tracking-widest">Profil</span>
        </a>

        <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" class="no-loader">
            @csrf
            <button type="button" onclick="confirmLogout('logout-form-mobile')" class="flex flex-col items-center gap-1 group text-red-500/70 active:text-red-600 transition-all">
                <i data-lucide="log-out" class="w-6 h-6 transition-transform group-active:scale-90"></i>
                <span class="text-[9px] font-bold uppercase tracking-widest">Keluar</span>
            </button>
        </form>
    </nav>

    <script>
        let lastMessage = null;

        // Global handleDownload function
        function handleDownload(url, filename) {
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', filename);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Global confirmLogout function
        function confirmLogout(formId) {
            Swal.fire({
                title: 'Konfirmasi Keluar',
                text: "Apakah Anda yakin ingin mengakhiri sesi ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#64748B',
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'rounded-[32px]',
                    confirmButton: 'rounded-xl px-6 py-3 font-bold',
                    cancelButton: 'rounded-xl px-6 py-3 font-bold'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        }

        async function refreshBroadcast() {
            try {
                const response = await fetch("{{ route('settings.running-text') }}");
                const data = await response.json();

                if (!data.message) {
                    document.getElementById('broadcast-container').classList.add('hidden');
                    return;
                }

                if (data.mode === 'running_text') {
                    const container = document.getElementById('broadcast-container');
                    const wrapper = document.getElementById('marquee-wrapper');
                    const content = document.getElementById('marquee-content');

                    container.classList.remove('hidden');
                    wrapper.style.backgroundColor = data.bg;
                    wrapper.style.color = data.color;
                    content.style.fontSize = data.size + 'px';
                    content.style.animationDuration = data.speed + 's';
                    
                    const item = `<i data-lucide="megaphone" class="w-4 h-4 inline-block mr-2 align-middle"></i> ${data.message} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`;
                    content.innerHTML = item + item + item + item;
                    lucide.createIcons();
                } else if (data.mode === 'popup') {
                    document.getElementById('broadcast-container').classList.add('hidden');
                    
                    // Only show popup if message changed or first time
                    if (lastMessage !== data.message) {
                        Swal.fire({
                            title: '<div class="flex items-center justify-center gap-3 text-blue-600"><i data-lucide="megaphone" class="w-8 h-8"></i> PENGUMUMAN</div>',
                            html: `<div class="text-slate-700 font-bold leading-relaxed py-4" style="font-size: ${data.size}px;">${data.message}</div>`,
                            confirmButtonText: 'SAYA MENGERTI',
                            confirmButtonColor: '#0F172A',
                            customClass: { popup: 'rounded-[40px] p-10 border-4 border-blue-50' },
                            didOpen: () => { lucide.createIcons(); }
                        });
                        lastMessage = data.message;
                    }
                }
            } catch (error) {
                console.error('Failed to fetch broadcast:', error);
            }
        }

        window.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            const loader = document.getElementById('global-loading');
            loader.style.opacity = '0';
            setTimeout(() => loader.style.display = 'none', 500);
            
            // Initial fetch
            refreshBroadcast();
            
            // Poll every 60 seconds
            setInterval(refreshBroadcast, 60000);
        });

        function toggleNotifications() {
            document.getElementById('profileDropdown').classList.add('hidden');
            document.getElementById('notificationDropdown').classList.toggle('hidden');
        }

        function toggleProfileDropdown() {
            document.getElementById('notificationDropdown').classList.add('hidden');
            document.getElementById('profileDropdown').classList.toggle('hidden');
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('appSidebar');
            const backdrop = document.getElementById('mobileSidebarBackdrop');
            sidebar.classList.toggle('-translate-x-full');
            backdrop.classList.toggle('hidden');
        }

        function closeSidebar() {
            const sidebar = document.getElementById('appSidebar');
            const backdrop = document.getElementById('mobileSidebarBackdrop');
            sidebar.classList.add('-translate-x-full');
            backdrop.classList.add('hidden');
        }

        window.addEventListener('click', function(e) {
            if (!e.target.closest('.relative')) {
                document.getElementById('notificationDropdown').classList.add('hidden');
                document.getElementById('profileDropdown').classList.add('hidden');
            }
        });

        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                if(!this.classList.contains('no-loader')) {
                    document.getElementById('global-loading').style.display = 'flex';
                    document.getElementById('global-loading').style.opacity = '1';
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
