<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sinergi PAS - @yield('title')</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="icon" type="image/png" href="{{ asset('logo1.png') }}">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#E85A4F">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --bg-primary: #FCFBF9;
            --bg-secondary: #FFFFFF;
            --text-primary: #1E2432;
            --text-secondary: #8A8A8A;
            --border-primary: #EFEFEF;
        }

        .dark {
            --bg-primary: #0B0E14;
            --bg-secondary: #12151C;
            --text-primary: #F7FAFC;
            --text-secondary: #94A3B8;
            --border-primary: #1E293B;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg-primary); 
            color: var(--text-primary);
            transition: all 0.3s ease; 
        }
        
        /* Direct Tag Overrides */
        .dark body { background-color: #0B0E14 !important; }
        .dark aside, .dark header, .dark .bg-white { background-color: #12151C !important; border-color: #1E293B !important; }
        .dark .bg-[#FCFBF9], .dark .bg-gray-50 { background-color: #0B0E14 !important; }
        .dark .text-[#1E2432], .dark h1, .dark h2, .dark h3, .dark h4, .dark td { color: #F7FAFC !important; }
        .dark .text-[#8A8A8A], .dark .text-[#ABABAB], .dark th { color: #94A3B8 !important; }
        .dark border-[#EFEFEF], .dark .border-r, .dark .border-b, .dark .border-t, .dark .border { border-color: #1E293B !important; }
        
        /* Input & Component Overrides */
        .dark input, .dark select, .dark textarea { 
            background-color: #1A1F2B !important; 
            color: #F7FAFC !important; 
            border-color: #2D3748 !important; 
        }
        .dark .sidebar-item:not(.active):hover { background-color: #1A1F2B; }
        .sidebar-item.active { background-color: #E85A4F !important; color: white !important; }
        
        /* Utility Class Wrappers */
        .folder-3d { transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
        .folder-3d:hover { transform: translateY(-8px) scale(1.02); }

        #global-loading {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            z-index: 9999;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .loader-ring {
            width: 48px;
            height: 48px;
            border: 5px solid #EFEFEF;
            border-bottom-color: #E85A4F;
            border-radius: 50%;
            display: inline-block;
            box-sizing: border-box;
            animation: rotation 1s linear infinite;
        }
        @keyframes rotation {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="antialiased">
    <!-- Loading Overlay -->
    <div id="global-loading">
        <span class="loader-ring"></span>
        <p class="mt-4 text-[#1E2432] font-bold animate-pulse">Memproses Data...</p>
    </div>

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-[#EFEFEF] flex flex-col px-6 py-8 fixed h-full">
            <div class="flex items-center gap-3 mb-12">
                <img src="{{ asset('logo1.png') }}" class="w-10 h-10 object-contain">
                <h1 class="text-lg font-black text-[#1E2432] tracking-tighter">SINERGI PAS</h1>
            </div>

            <nav class="flex-1 space-y-2">
                <a href="{{ route('dashboard') }}" class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : 'text-[#8A8A8A]' }} flex items-center gap-3 px-4 py-3 rounded-xl transition-all">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    <span class="text-sm font-semibold">Dashboard</span>
                </a>
                
                @if(auth()->user()->role === 'superadmin')
                <a href="{{ route('employees.index') }}" class="sidebar-item {{ request()->routeIs('employees.*') ? 'active' : 'text-[#8A8A8A]' }} flex items-center gap-3 px-4 py-3 rounded-xl transition-all">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    <span class="text-sm font-medium">Data Pegawai</span>
                </a>
                @endif

                <a href="{{ route('documents.index') }}" class="sidebar-item {{ request()->routeIs('documents.*') ? 'active' : 'text-[#8A8A8A]' }} flex items-center gap-3 px-4 py-3 rounded-xl transition-all">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                    <span class="text-sm font-medium">Pusat Dokumen</span>
                </a>

                @if(auth()->user()->role === 'superadmin')
                <a href="{{ route('audit.index') }}" class="sidebar-item {{ request()->routeIs('audit.*') ? 'active' : 'text-[#8A8A8A]' }} flex items-center gap-3 px-4 py-3 rounded-xl transition-all">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                    <span class="text-sm font-medium">Laporan Audit</span>
                </a>
                <a href="{{ route('settings.index') }}" class="sidebar-item {{ request()->routeIs('settings.*') ? 'active' : 'text-[#8A8A8A]' }} flex items-center gap-3 px-4 py-3 rounded-xl transition-all">
                    <i data-lucide="settings-2" class="w-5 h-5"></i>
                    <span class="text-sm font-medium">Pengaturan</span>
                </a>
                @endif
            </nav>

            <div class="pt-6 border-t border-[#EFEFEF]">
                <div class="flex items-center gap-3 px-2 mb-6">
                    @php
                        $sidebarEmployee = \App\Models\Employee::where('user_id', auth()->id())->first();
                    @endphp
                    <div class="w-10 h-10 bg-[#E85A4F] rounded-xl flex items-center justify-center text-white font-bold overflow-hidden text-xs">
                        @if($sidebarEmployee && $sidebarEmployee->photo)
                            <img src="{{ Storage::url($sidebarEmployee->photo) }}" class="w-full h-full object-cover">
                        @else
                            {{ substr(auth()->user()->name, 0, 1) }}
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-[#1E2432] truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-[#8A8A8A] truncate">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full sidebar-item flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-red-500 hover:bg-red-50">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                        <span class="text-sm font-semibold">Keluar Aplikasi</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 ml-64">
            <!-- Header -->
            <header class="h-20 bg-white border-b border-[#EFEFEF] flex items-center justify-between px-10 sticky top-0 z-10">
                <h2 class="text-xl font-semibold text-[#1E2432]">@yield('header-title')</h2>
                <div class="flex items-center gap-4">
                    <!-- Dark Mode Toggle -->
                    <button onclick="toggleDarkMode()" class="p-2 text-[#8A8A8A] hover:bg-[#F5F4F2] rounded-lg transition-all" id="dark-mode-toggle">
                        <i data-lucide="moon" class="w-5 h-5 block dark:hidden"></i>
                        <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
                    </button>

                    <!-- Notifications Dropdown (Simplified) -->
                    <div class="relative group">
                        <button class="p-2 text-[#8A8A8A] hover:bg-[#F5F4F2] rounded-lg transition-all relative">
                            <i data-lucide="bell" class="w-5 h-5"></i>
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="absolute top-1 right-1 w-2 h-2 bg-[#E85A4F] rounded-full ring-2 ring-white"></span>
                            @endif
                        </button>
                        <!-- Dropdown Content (Hidden by default) -->
                        <div class="absolute right-0 mt-2 w-80 bg-white rounded-3xl shadow-2xl border border-[#EFEFEF] hidden group-hover:block z-50 p-4">
                            <h4 class="text-xs font-black uppercase tracking-widest text-[#8A8A8A] mb-4 p-2">Notifikasi Terbaru</h4>
                            <div class="space-y-2 max-h-96 overflow-y-auto">
                                @forelse(auth()->user()->notifications->take(5) as $notification)
                                    <div class="p-4 bg-[#FCFBF9] rounded-2xl border border-[#EFEFEF]">
                                        <p class="text-xs font-bold text-[#1E2432]">{{ $notification->data['title'] }}</p>
                                        <p class="text-[10px] text-[#8A8A8A] mt-1">{{ $notification->data['message'] }}</p>
                                    </div>
                                @empty
                                    <p class="text-xs text-center py-10 text-[#ABABAB]">Tidak ada notifikasi.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    
                    <a href="{{ route('profile.index') }}" class="p-2 text-[#8A8A8A] hover:bg-[#F5F4F2] rounded-lg transition-all">
                        <i data-lucide="settings" class="w-5 h-5"></i>
                    </a>
                </div>
            </header>

            <div class="p-10">
                @yield('content')
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();

        // Dark Mode Logic
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        function toggleDarkMode() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
            lucide.createIcons();
        }

        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                if(!this.classList.contains('no-loader')) {
                    document.getElementById('global-loading').style.display = 'flex';
                }
            });
        });
    </script>
</body>
</html>
