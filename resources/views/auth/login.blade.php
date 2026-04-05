<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sinergi PAS - Login Premium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Open+Sans:wght@400;500;600;700;800&family=Montserrat:wght@500;600;700;800&family=Poppins:wght@500;600;700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --font-display: 'Montserrat', sans-serif;
            --font-body: 'Open Sans', sans-serif;
            --font-ui: 'Poppins', sans-serif;
            --font-data: 'Roboto', sans-serif;
            --font-caption: 'Lato', sans-serif;
        }
        body { 
            font-family: var(--font-body);
            background: radial-gradient(circle at top right, #fff5f4, #ffffff);
        }
        h1, h2, h3, h4, h5, h6, .font-extrabold, .font-black {
            font-family: var(--font-display);
        }
        button { font-family: var(--font-ui); }
        input, textarea { font-family: var(--font-data); }
        label, .text-xs { font-family: var(--font-caption); }
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
            <img src="{{ asset('logo1.png') }}" class="w-20 h-20 mx-auto mb-6 drop-shadow-2xl animate-float">
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
                    <div class="relative group">
                        <input type="password" name="password" id="password" required 
                            class="w-full px-6 py-4 rounded-[20px] border border-[#EFEFEF] bg-white/50 text-[#1E2432] focus:ring-4 focus:ring-red-500/10 focus:border-[#E85A4F] outline-none transition-all placeholder:text-[#ABABAB] font-medium pr-14"
                            placeholder="••••••••••••">
                        <button type="button" onclick="togglePassword()" class="absolute right-5 top-1/2 -translate-y-1/2 text-[#ABABAB] hover:text-[#E85A4F] transition-colors focus:outline-none">
                            <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path id="eye-open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path id="eye-open-outer" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                <path id="eye-closed" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <script>
                    function togglePassword() {
                        const passwordInput = document.getElementById('password');
                        const eyeOpen = document.getElementById('eye-open');
                        const eyeOpenOuter = document.getElementById('eye-open-outer');
                        const eyeClosed = document.getElementById('eye-closed');
                        
                        if (passwordInput.type === 'password') {
                            passwordInput.type = 'text';
                            eyeOpen.classList.add('hidden');
                            eyeOpenOuter.classList.add('hidden');
                            eyeClosed.classList.remove('hidden');
                        } else {
                            passwordInput.type = 'password';
                            eyeOpen.classList.remove('hidden');
                            eyeOpenOuter.classList.remove('hidden');
                            eyeClosed.classList.add('hidden');
                        }
                    }
                </script>

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
