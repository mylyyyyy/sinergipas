<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SinergiPAS">
    <link rel="apple-touch-icon" href="/logo1.png">

    <!-- SweetAlert & Progress Bar -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
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
            transform: translateX(6px); 
            }
            .sidebar-item.active { 
            background: var(--color-primary); 
            color: white !important; 
            box-shadow: 0 10px 20px -5px rgba(15, 23, 42, 0.3);
            transform: scale(1.02);
            }
            .sidebar-item.active i { color: white !important; }

            /* Component Refinement */
            .rounded-premium { border-radius: 1.5rem; }
            .rounded-button { border-radius: 0.875rem; }

            /* Smooth Scrollbar */
            .custom-scrollbar::-webkit-scrollbar { width: 5px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 10px; }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94A3B8; }

            .page-fade { animation: pageIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1); }
            @keyframes pageIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

    </style>
</head>
<body class="antialiased">
    <div id="global-loading">
        <span class="loader-ring"></span>
        <p class="mt-4 text-[11px] font-bold uppercase tracking-widest text-slate-500">Memuat Data...</p>
    </div>

    <div id="mobileSidebarBackdrop" class="fixed inset-0 z-30 bg-slate-900/40 backdrop-blur-sm lg:hidden hidden" onclick="closeSidebar()"></div>

    <div class="flex min-h-screen">
        <aside id="appSidebar" class="fixed inset-y-0 left-0 z-40 flex h-full w-64 -translate-x-full flex-col border-r border-slate-200 bg-white px-6 py-8 transition-transform duration-300 lg:translate-x-0 shrink-0">
            <div class="flex items-center gap-3 mb-10 px-2">
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

                <a href="{{ route('admin.attendance.index') }}" class="sidebar-item {{ request()->routeIs('admin.attendance.*') ? 'active' : 'text-slate-500' }} flex items-center gap-3 px-4 py-3 rounded-xl">
                    <i data-lucide="fingerprint" class="w-5 h-5"></i>
                    <span class="text-sm font-semibold">Absensi</span>
                </a>

                <a href="{{ route('admin.schedules.index') }}" class="sidebar-item {{ request()->routeIs('admin.schedules.*') ? 'active' : 'text-slate-500' }} flex items-center gap-3 px-4 py-3 rounded-xl">
                    <i data-lucide="calendar-days" class="w-5 h-5"></i>
                    <span class="text-sm font-semibold">Jadwal Shift</span>
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
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-red-500 hover:bg-red-50 transition-colors text-sm font-bold">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <main class="relative min-h-screen flex-1 lg:ml-64 bg-slate-50">
            @php 
                $activeBanner = \App\Models\Announcement::active()->where('type', 'banner')->latest()->first();
                $bannerBg = \App\Models\Setting::getValue('running_text_bg', '#0F172A');
                $bannerColor = \App\Models\Setting::getValue('running_text_color', '#FFFFFF');
                $bannerSpeed = \App\Models\Setting::getValue('running_text_speed', '20');
            @endphp

            @if($activeBanner)
            <div class="py-2.5 overflow-hidden relative shadow-sm" style="background-color: {{ $bannerBg }}; color: {{ $bannerColor }};">
                <div class="whitespace-nowrap animate-marquee inline-block text-[11px] font-semibold">
                    <span class="mx-12"><i data-lucide="megaphone" class="w-4 h-4 inline mr-2 align-middle"></i> {{ $activeBanner->message }}</span>
                    <span class="mx-12"><i data-lucide="megaphone" class="w-4 h-4 inline mr-2 align-middle"></i> {{ $activeBanner->message }}</span>
                </div>
            </div>
            <style>
                @keyframes marquee { 0% { transform: translateX(100%); } 100% { transform: translateX(-100%); } }
                .animate-marquee { animation: marquee {{ $bannerSpeed }}s linear infinite; }
            </style>
            @endif

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

                    <a href="{{ route('profile.index') }}" class="flex items-center gap-3 pl-1 group">
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
                    </a>
                </div>
            </header>
            
            <div class="page-fade p-6 lg:p-10">@yield('content')</div>
        </main>
    </div>

    <script>
        // PWA Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('SW Registered'))
                    .catch(err => console.log('SW Error', err));
            });
        }

        // Global Toast Notification Helper
        window.showToast = function(message, icon = 'info') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
            Toast.fire({ icon: icon, title: message });
        };

        window.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            NProgress.done();
        });

        window.addEventListener('beforeunload', () => {
            NProgress.start();
        });

        function toggleNotifications() {
            document.getElementById('notificationDropdown').classList.toggle('hidden');
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
            }
        });

        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                if(!this.classList.contains('no-loader')) {
                    document.getElementById('global-loading').style.display = 'flex';
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
