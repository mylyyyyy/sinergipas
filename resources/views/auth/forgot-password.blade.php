<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sinergi PAS - Lupa Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="antialiased bg-[#F1F5F9] flex items-center justify-center min-h-screen p-6 font-['Plus_Jakarta_Sans']">
    <div class="w-full max-w-md">
        <div class="text-center mb-10">
            <img src="{{ asset('logo1.png') }}" class="w-16 h-16 mx-auto mb-4 drop-shadow-xl">
            <h1 class="text-2xl font-bold text-[#0F172A]">RESET PASSWORD</h1>
        </div>

        <div class="bg-white p-10 rounded-3xl border border-[#EFEFEF] shadow-xl shadow-gray-100">
            <form action="{{ route('password.update') }}" method="POST" class="space-y-6">
                @csrf
                <div class="space-y-2">
                    <label class="text-xs font-bold text-[#0F172A] uppercase tracking-wider">Email Terdaftar</label>
                    <input type="email" name="email" required class="w-full px-5 py-3.5 rounded-xl border border-[#EFEFEF] bg-[#F1F5F9] text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-[#0F172A] uppercase tracking-wider">Password Baru</label>
                    <input type="password" name="password" required class="w-full px-5 py-3.5 rounded-xl border border-[#EFEFEF] bg-[#F1F5F9] text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-[#0F172A] uppercase tracking-wider">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" required class="w-full px-5 py-3.5 rounded-xl border border-[#EFEFEF] bg-[#F1F5F9] text-sm">
                </div>
                <button type="submit" class="w-full bg-[#EAB308] text-white py-4 rounded-2xl font-bold hover:bg-[#CA8A04] transition-all">
                    Reset Password
                </button>
                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-[#8A8A8A] hover:text-[#EAB308]">Kembali ke Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
