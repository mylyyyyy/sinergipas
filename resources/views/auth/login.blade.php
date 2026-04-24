<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sinergi PAS - Login</title>
    
    <!-- Scripts & Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            --font-custom: 'Plus Jakarta Sans', sans-serif;
            --color-primary: #0F172A;
            --color-accent: #B45309;
        }
        body {
            font-family: var(--font-custom);
            background: #F8FAFC;
            background-image: 
                radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(234, 179, 8, 0.05) 0px, transparent 50%);
            overflow: hidden;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.1);
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }
        .btn-3d {
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .btn-3d:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -6px rgba(15, 23, 42, 0.3);
        }
    </style>
</head>
<body class="antialiased min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-[420px] space-y-8 relative z-10">
        <div class="text-center space-y-4 animate-float">
            <img src="{{ asset('logo1.png') }}" class="w-16 h-16 mx-auto drop-shadow-2xl">
            <div>
                <h1 class="text-2xl font-[800] text-slate-900 tracking-tight uppercase tracking-widest">SINERGI PAS</h1>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Lapas Kelas IIB Jombang</p>
                <p class="text-[10px] font-black text-blue-600 uppercase tracking-[0.2em] mt-3">Digitalisasi Data, Wujudkan SDM Prima</p>
            </div>
        </div>

        <div class="login-card p-10 rounded-3xl shadow-xl shadow-slate-200/50">
            <h2 class="text-lg font-bold text-slate-900 mb-8 text-center italic">Autentikasi Sistem</h2>
            
            <form action="{{ route('login.post') }}" method="POST" class="space-y-6">
                @csrf
                
                @if ($errors->any())
                    <div class="p-4 rounded-xl bg-red-50 border border-red-100 text-red-600 text-[11px] font-bold">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Email Kedinasan</label>
                    <input type="email" name="email" required placeholder="username@pas.id" 
                        class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-white text-sm font-semibold outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all">
                </div>

                <div class="space-y-2 relative">
                    <div class="flex justify-between items-center px-1">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Kata Sandi</label>
                        <a href="{{ route('password.request') }}" class="text-[10px] font-bold text-blue-600 hover:underline">Lupa Password?</a>
                    </div>
                    <div class="relative">
                        <input type="password" name="password" id="password" required placeholder="••••••••" 
                            class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 bg-white text-sm font-semibold outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all pr-12">
                        <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                            <i data-lucide="eye" id="eye-icon" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-bold text-sm uppercase tracking-widest hover:bg-slate-800 transition-all shadow-lg shadow-slate-200 active:scale-[0.98] btn-3d">
                    Masuk Sekarang
                </button>
            </form>
        </div>

        <p class="text-center text-[10px] font-bold text-slate-400 uppercase tracking-[0.3em]">
            &copy; 2026 Sinergi PAS - Versi 2.0
        </p>
    </div>

    <!-- Background Decoration -->
    <div class="fixed top-0 left-0 w-full h-full pointer-events-none -z-0">
        <div class="absolute top-[10%] right-[10%] w-64 h-64 bg-blue-500/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-[10%] left-[10%] w-64 h-64 bg-amber-500/5 rounded-full blur-3xl"></div>
    </div>

    <script>
        lucide.createIcons();
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                input.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }
    </script>
</body>
</html>
