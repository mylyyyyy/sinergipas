<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sinergi PAS - Login Premium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: radial-gradient(circle at top right, #fff5f4, #ffffff);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
    </style>
</head>
<body class="antialiased min-h-screen flex items-center justify-center p-6 overflow-hidden">
    <!-- Abstract Shapes -->
    <div class="absolute top-[-10%] -right-[10%] w-96 h-96 bg-[#E85A4F] opacity-10 rounded-full blur-3xl animate-float"></div>
    <div class="absolute bottom-[-10%] -left-[10%] w-80 h-80 bg-[#1E2432] opacity-5 rounded-full blur-3xl animate-float" style="animation-delay: 2s"></div>

    <div class="w-full max-w-[480px] relative z-10">
        <!-- Logo -->
        <div class="text-center mb-12">
            <div class="w-16 h-16 bg-[#E85A4F] rounded-[24px] mx-auto mb-6 flex items-center justify-center shadow-2xl shadow-red-200 rotate-12 hover:rotate-0 transition-transform duration-500">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
            </div>
            <h1 class="text-3xl font-extrabold text-[#1E2432] tracking-tight">SINERGI PAS</h1>
            <p class="text-[#8A8A8A] font-medium mt-2">Sistem Database Kepegawaian Lapas Jombang</p>
        </div>

        <!-- Login Card -->
        <div class="glass-card p-12 rounded-[48px] shadow-2xl shadow-gray-200/50">
            <form action="{{ route('login.post') }}" method="POST" class="space-y-8">
                @csrf
                @if ($errors->any())
                    <div class="bg-red-50 border border-red-100 text-red-600 px-6 py-4 rounded-2xl text-sm font-bold flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="space-y-3">
                    <label class="text-xs font-extrabold text-[#1E2432] uppercase tracking-[0.2em] ml-1">Email Kantor</label>
                    <input type="email" name="email" required 
                        class="w-full px-6 py-4 rounded-[20px] border border-[#EFEFEF] bg-white/50 text-[#1E2432] focus:ring-4 focus:ring-red-500/10 focus:border-[#E85A4F] outline-none transition-all placeholder:text-[#ABABAB] font-medium"
                        placeholder="username@pas.id">
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between items-center ml-1">
                        <label class="text-xs font-extrabold text-[#1E2432] uppercase tracking-[0.2em]">Password</label>
                        <a href="{{ route('password.request') }}" class="text-xs font-bold text-[#E85A4F] hover:text-[#d44d42] transition-colors">Lupa Password?</a>
                    </div>
                    <input type="password" name="password" required 
                        class="w-full px-6 py-4 rounded-[20px] border border-[#EFEFEF] bg-white/50 text-[#1E2432] focus:ring-4 focus:ring-red-500/10 focus:border-[#E85A4F] outline-none transition-all placeholder:text-[#ABABAB] font-medium"
                        placeholder="••••••••••••">
                </div>

                <button type="submit" 
                    class="w-full bg-[#E85A4F] text-white py-5 rounded-[24px] font-extrabold text-lg hover:bg-[#d44d42] transition-all shadow-xl shadow-red-200 active:scale-[0.98] flex items-center justify-center gap-3">
                    Masuk ke Sistem
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                </button>
            </form>
        </div>

        <p class="text-center text-sm font-bold text-[#ABABAB] mt-12 uppercase tracking-widest">
            &copy; 2026 Lapas Jombang
        </p>
    </div>
</body>
</html>
