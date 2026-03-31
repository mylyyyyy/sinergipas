<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sinergi PAS - @yield('title')</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #FCFBF9; }
        .sidebar-item:hover { background-color: #F5F4F2; }
        .sidebar-item.active { background-color: #E85A4F; color: white; }
    </style>
</head>
<body class="antialiased">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-[#EFEFEF] flex flex-col px-6 py-8 fixed h-full">
            <div class="flex items-center gap-3 mb-12">
                <div class="w-8 h-8 bg-[#E85A4F] rounded-lg"></div>
                <h1 class="text-lg font-bold text-[#1E2432]">SINERGI PAS</h1>
            </div>

            <nav class="flex-1 space-y-2">
                <a href="{{ route('dashboard') }}" class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : 'text-[#8A8A8A]' }} flex items-center gap-3 px-4 py-3 rounded-xl transition-all">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    <span class="text-sm font-semibold">Dashboard</span>
                </a>
                <a href="{{ route('employees.index') }}" class="sidebar-item {{ request()->routeIs('employees.*') ? 'active' : 'text-[#8A8A8A]' }} flex items-center gap-3 px-4 py-3 rounded-xl transition-all">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    <span class="text-sm font-medium">Data Pegawai</span>
                </a>
                <a href="{{ route('documents.index') }}" class="sidebar-item {{ request()->routeIs('documents.*') ? 'active' : 'text-[#8A8A8A]' }} flex items-center gap-3 px-4 py-3 rounded-xl transition-all">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                    <span class="text-sm font-medium">Dokumen & Slip Gaji</span>
                </a>
                <a href="{{ route('employees.index') }}" class="sidebar-item {{ request()->routeIs('employees.*') ? 'active' : 'text-[#8A8A8A]' }} flex items-center gap-3 px-4 py-3 rounded-xl transition-all">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    <span class="text-sm font-medium">Data Pegawai</span>
                </a>
                <div class="hidden">
                    <i data-lucide="clipboard-list" class="w-5 h-5"></i>
                </div>
            </nav>

            <div class="pt-6 border-t border-[#EFEFEF]">
                <div class="flex items-center gap-3 px-2 mb-6">
                    <div class="w-10 h-10 bg-[#E85A4F] rounded-full flex items-center justify-center text-white font-bold">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-[#1E2432]">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-[#8A8A8A]">{{ auth()->user()->email }}</p>
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
                    <button class="p-2 text-[#8A8A8A] hover:bg-[#F5F4F2] rounded-lg transition-all">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                    </button>
                    <button class="p-2 text-[#8A8A8A] hover:bg-[#F5F4F2] rounded-lg transition-all">
                        <i data-lucide="settings" class="w-5 h-5"></i>
                    </button>
                </div>
            </header>

            <div class="p-10">
                @yield('content')
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
