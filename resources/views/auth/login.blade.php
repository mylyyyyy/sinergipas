<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SINERGI PAS - Lapas Jombang</title>
    
    <!-- Scripts & Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .bg-navy {
            background-color: #0F172A;
        }
        .text-pas-orange {
            color: #F59E0B;
        }
        .glass-overlay {
            background: linear-gradient(to right, rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.3));
        }
        .login-card {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        /* Custom scrollbar for better aesthetics */
        ::-webkit-scrollbar {
            width: 5px;
        }
        ::-webkit-scrollbar-track {
            background: #0F172A;
        }
        ::-webkit-scrollbar-thumb {
            background: #1E293B;
            border-radius: 10px;
        }
    </style>
</head>
<body class="antialiased bg-navy selection:bg-blue-500 selection:text-white">

    <div class="min-h-screen flex flex-col md:flex-row overflow-y-auto">
        
        <!-- SISI KIRI: FORM LOGIN -->
        <div class="w-full md:w-[40%] bg-navy flex items-center justify-center p-6 lg:p-12 py-12 min-h-screen relative overflow-hidden">
            <!-- Decorative circle backgrounds -->
            <div class="absolute -top-24 -left-24 w-64 h-64 bg-blue-600/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -right-24 w-64 h-64 bg-amber-500/10 rounded-full blur-3xl"></div>

            <div class="w-full max-w-[380px] relative z-10 flex flex-col items-center">
                <!-- Logo Instansi -->
                <img src="{{ asset('logo1.png') }}" alt="Logo Lapas" class="w-20 h-20 mb-8 drop-shadow-2xl animate-bounce" style="animation-duration: 3s;">

                <!-- Card Login -->
                <div class="bg-white w-full rounded-[32px] p-8 lg:p-10 login-card border border-white/10">
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Silakan Login</h2>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-2">Akses Sistem Internal</p>
                    </div>

                    @if($errors->any())
                        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-xl">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-exclamation-circle text-red-500"></i>
                                <p class="text-xs font-bold text-red-700">{{ $errors->first() }}</p>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                        @csrf
                        
                        <!-- Input Email -->
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Alamat Email</label>
                            <div class="relative group">
                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition-colors">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <input type="email" name="email" value="{{ old('email') }}" required
                                    class="w-full pl-12 pr-6 py-4 rounded-2xl bg-slate-50 border-2 border-transparent focus:bg-white focus:border-blue-500 outline-none transition-all font-bold text-sm text-slate-700"
                                    placeholder="Masukkan Email">
                            </div>
                        </div>

                        <!-- Input Password -->
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Kata Sandi</label>
                            <div class="relative group">
                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition-colors">
                                    <i class="fas fa-lock"></i>
                                </div>
                                <input type="password" name="password" id="password" required
                                    class="w-full pl-12 pr-12 py-4 rounded-2xl bg-slate-50 border-2 border-transparent focus:bg-white focus:border-blue-500 outline-none transition-all font-bold text-sm text-slate-700"
                                    placeholder="••••••••">
                                <button type="button" onclick="togglePassword()"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                                    <i id="eyeIcon" class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" 
                            class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-xl shadow-blue-500/30 transition-all transform hover:-translate-y-1 active:scale-95 mt-4">
                            LOGIN KE SISTEM
                        </button>
                    </form>

                    <!-- Footer Card -->
                    <div class="mt-10 pt-8 border-t border-slate-100 text-center">
                        <div class="inline-flex items-center gap-2">
                            <span class="text-[10px] font-black text-slate-900 tracking-[0.2em] uppercase">SINERGI</span>
                            <span class="px-2 py-0.5 bg-amber-500 text-white text-[10px] font-black rounded-md tracking-[0.2em] uppercase">PAS</span>
                        </div>
                    </div>
                </div>

                <!-- Copyright mobile only -->
                <p class="mt-8 text-[10px] font-bold text-slate-500 uppercase tracking-[0.3em] md:hidden text-center">
                    &copy; 2026 Lapas Jombang
                </p>
            </div>
        </div>

        <!-- SISI KANAN: BRANDING -->
        <div class="hidden md:flex md:w-[60%] bg-cover bg-center relative items-center justify-center p-20" 
            style="background-image: url('https://images.unsplash.com/photo-1555421689-491a97ff2040?q=80&w=2070&auto=format&fit=crop');">
            
            <!-- Glass Overlay -->
            <div class="absolute inset-0 glass-overlay"></div>

            <div class="relative z-10 w-full">
                <div class="space-y-2 animate-in fade-in slide-in-from-right duration-1000">
                    <h3 class="text-xs md:text-sm font-bold text-white/90 uppercase tracking-widest mb-4">Sistem Informasi Kinerja Pegawai Pemasyarakatan</h3>
                    <h1 class="text-7xl lg:text-8xl font-black leading-none flex flex-col">
                        <span class="text-white drop-shadow-2xl">SINERGI</span>
                        <span class="text-pas-orange drop-shadow-2xl">PAS</span>
                    </h1>
                    <div class="h-1.5 w-32 bg-pas-orange rounded-full my-6"></div>
                    <h2 class="text-3xl font-extrabold text-white tracking-tight">LAPAS KELAS IIB JOMBANG</h2>
                    <p class="text-lg font-medium text-blue-100/90 tracking-[0.2em] mt-8 uppercase border-l-4 border-blue-500 pl-6 py-2">
                        Digitalisasi Data, Wujudkan SDM Prima
                    </p>
                </div>
            </div>

            <!-- Decorative corner bottom right -->
            <div class="absolute bottom-12 right-12 text-white/50 text-[10px] font-bold uppercase tracking-[0.5em]">
                Sinergi PAS v2.0
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
