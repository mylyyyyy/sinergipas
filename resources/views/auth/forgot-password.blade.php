<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sinergi PAS - Lupa Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="antialiased bg-[#FCFBF9] flex items-center justify-center min-h-screen p-6 font-['Plus_Jakarta_Sans']">
    <div class="w-full max-w-md">
        <div class="text-center mb-10">
            <div class="w-12 h-12 bg-[#E85A4F] rounded-xl mx-auto mb-4 shadow-lg shadow-red-200"></div>
            <h1 class="text-2xl font-bold text-[#1E2432]">RESET PASSWORD</h1>
        </div>

        <div class="bg-white p-10 rounded-3xl border border-[#EFEFEF] shadow-xl shadow-gray-100">
            <form action="{{ route('password.update') }}" method="POST" class="space-y-6">
                @csrf
                <div class="space-y-2">
                    <label class="text-xs font-bold text-[#1E2432] uppercase tracking-wider">Email Terdaftar</label>
                    <input type="email" name="email" required class="w-full px-5 py-3.5 rounded-xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-[#1E2432] uppercase tracking-wider">Password Baru</label>
                    <input type="password" name="password" required class="w-full px-5 py-3.5 rounded-xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-[#1E2432] uppercase tracking-wider">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" required class="w-full px-5 py-3.5 rounded-xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm">
                </div>
                <button type="submit" class="w-full bg-[#E85A4F] text-white py-4 rounded-2xl font-bold hover:bg-[#d44d42] transition-all">
                    Reset Password
                </button>
                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-[#8A8A8A] hover:text-[#E85A4F]">Kembali ke Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
