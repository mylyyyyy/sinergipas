<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sinergi PAS - @yield('title')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="icon" type="image/png" href="{{ asset('logo1.png') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        #global-loading {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(8px);
            z-index: 9999;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .loader-ring {
            width: 48px;
            height: 48px;
            border: 5px solid #F5F4F2;
            border-bottom-color: #E85A4F;
            border-radius: 50%;
            animation: rotation 0.6s linear infinite;
        }
        @keyframes rotation { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="antialiased">
    <div id="global-loading">
        <span class="loader-ring"></span>
        <p class="mt-4 text-xs font-black uppercase tracking-widest text-[#E85A4F]">Sedang Memproses...</p>
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
                <a href="{{ route('documents.index') }}" class="sidebar-item {{ request()->routeIs('documents.*') ? 'active shadow-lg shadow-red-100' : 'text-[#8A8A8A]' }} flex items-center gap-3 px-4 py-3 rounded-xl transition-all">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                    <span class="text-sm font-bold">Pusat Dokumen</span>
                </a>
                @if(auth()->user()->role === 'superadmin')
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

        <main class="flex-1 ml-64 min-h-screen">
            <header class="h-20 bg-white border-b border-[#EFEFEF] flex items-center justify-between px-10 sticky top-0 z-10">
                <h2 class="text-xl font-black text-[#1E2432] tracking-tight">@yield('header-title')</h2>
                <div class="flex items-center gap-4">
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
            <div class="p-10">@yield('content')</div>
        </main>
    </div>

    <script>
        lucide.createIcons();
        
        // Instant speed: only show loading on actual heavy form submissions
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                if(!this.classList.contains('no-loader')) {
                    document.getElementById('global-loading').style.display = 'flex';
                }
            });
        });

        function showLoading() {
            document.getElementById('global-loading').style.display = 'flex';
        }
    </script>
</body>
</html>
