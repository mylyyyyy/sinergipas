<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sinergi PAS - Login</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #FCFBF9; }
    </style>
</head>
<body class="antialiased bg-[#FCFBF9] flex items-center justify-center min-h-screen p-6">
    <div class="w-full max-w-md">
        <!-- Logo & Title -->
        <div class="text-center mb-10">
            <div class="w-12 h-12 bg-[#E85A4F] rounded-xl mx-auto mb-4 shadow-lg shadow-red-200"></div>
            <h1 class="text-2xl font-bold text-[#1E2432]">SINERGI PAS</h1>
            <p class="text-[#8A8A8A] text-sm mt-1">Sistem Informasi Kepegawaian Lapas Jombang</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white p-10 rounded-3xl border border-[#EFEFEF] shadow-xl shadow-gray-100">
            <h2 class="text-xl font-bold text-[#1E2432] mb-2 text-center">Selamat Datang</h2>
            <p class="text-[#8A8A8A] text-sm mb-8 text-center">Silakan masuk untuk mengakses akun Anda</p>

            <form action="{{ route('login.post') }}" method="POST" class="space-y-6">
                @csrf
                @if ($errors->any())
                    <div class="bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-xl text-sm mb-4">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="space-y-2">
                    <label for="email" class="text-xs font-bold text-[#1E2432] uppercase tracking-wider">Email Address</label>
                    <input type="email" id="email" name="email" required 
                        class="w-full px-5 py-3.5 rounded-xl border border-[#EFEFEF] bg-[#FCFBF9] text-[#1E2432] focus:ring-2 focus:ring-[#E85A4F] focus:border-transparent outline-none transition-all placeholder:text-[#ABABAB]"
                        placeholder="nama@email.com">
                </div>

                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <label for="password" class="text-xs font-bold text-[#1E2432] uppercase tracking-wider">Password</label>
                        <a href="{{ route('password.request') }}" class="text-xs font-semibold text-[#E85A4F] hover:underline">Lupa Password?</a>
                    </div>
                    <input type="password" id="password" name="password" required 
                        class="w-full px-5 py-3.5 rounded-xl border border-[#EFEFEF] bg-[#FCFBF9] text-[#1E2432] focus:ring-2 focus:ring-[#E85A4F] focus:border-transparent outline-none transition-all placeholder:text-[#ABABAB]"
                        placeholder="••••••••">
                </div>

                <div class="flex items-center gap-2 py-1">
                    <input type="checkbox" id="remember" name="remember" class="w-4 h-4 text-[#E85A4F] rounded border-[#EFEFEF] focus:ring-0">
                    <label for="remember" class="text-sm text-[#8A8A8A] font-medium">Ingat saya</label>
                </div>

                <button type="submit" 
                    class="w-full bg-[#E85A4F] text-white py-4 rounded-2xl font-bold hover:bg-[#d44d42] transition-all shadow-lg shadow-red-200 active:scale-[0.98]">
                    Masuk Sekarang
                </button>
            </form>
        </div>

        <!-- Footer -->
        <p class="text-center text-xs text-[#8A8A8A] mt-8">
            &copy; 2026 Sinergi PAS Jombang. Seluruh Hak Cipta Dilindungi.
        </p>
    </div>
</body>
</html>
