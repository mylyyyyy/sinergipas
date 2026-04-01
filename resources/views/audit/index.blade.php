@extends('layouts.app')

@section('title', 'Laporan Audit Lanjutan')
@section('header-title', 'Keamanan & Log Aktivitas')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="grid grid-cols-1 md:grid-cols-3 gap-10 mb-12">
    <!-- Top Users Chart -->
    <div class="bg-white p-10 rounded-[56px] border border-[#EFEFEF] shadow-sm">
        <h3 class="text-lg font-black text-[#1E2432] mb-8 uppercase tracking-widest text-center">User Teraktif</h3>
        <canvas id="userChart" height="250"></canvas>
    </div>

    <!-- Log Table Area -->
    <div class="md:col-span-2 bg-white rounded-[56px] border border-[#EFEFEF] shadow-sm overflow-hidden flex flex-col">
        <div class="p-8 border-b border-[#EFEFEF] bg-[#FCFBF9]/50 flex flex-col md:flex-row justify-between items-center gap-4">
            <h3 class="text-lg font-black text-[#1E2432]">Riwayat Akses Terbaru</h3>
            <div class="flex items-center gap-2">
                <form action="{{ route('audit.clear') }}" method="POST" onsubmit="return confirm('Peringatan: Seluruh riwayat akan dihapus permanen. Lanjutkan?')" class="no-loader">
                    @csrf @method('DELETE')
                    <button type="submit" class="bg-red-50 text-red-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-600 hover:text-white transition-all">
                        Bersihkan Log
                    </button>
                </form>
            </div>
        </div>

        <div class="p-8 border-b border-[#EFEFEF] bg-white">
            <form action="{{ route('audit.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="relative flex-1 group">
                    <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A8A8A] group-focus-within:text-[#E85A4F] transition-all"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari User atau Aktivitas..." 
                        class="w-full pl-12 pr-4 py-3 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-xs outline-none focus:ring-2 focus:ring-[#E85A4F] transition-all">
                </div>
                <div class="relative group">
                    <i data-lucide="calendar" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A8A8A] group-focus-within:text-[#E85A4F] transition-all"></i>
                    <input type="date" name="date" value="{{ request('date') }}" 
                        class="w-full pl-12 pr-4 py-3 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-xs outline-none focus:ring-2 focus:ring-[#E85A4F] transition-all">
                </div>
                <button type="submit" class="bg-[#1E2432] text-white px-8 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-[#E85A4F] transition-all shadow-lg shadow-gray-100">
                    Filter Log
                </button>
                @if(request()->anyFilled(['search', 'date']))
                <a href="{{ route('audit.index') }}" class="flex items-center justify-center bg-gray-100 text-gray-600 px-4 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-gray-200 transition-all">
                    Reset
                </a>
                @endif
            </form>
        </div>

        <div class="flex-1 overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#FCFBF9]">
                        <th class="px-8 py-4 text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.2em]">User</th>
                        <th class="px-8 py-4 text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.2em]">Aktivitas</th>
                        <th class="px-8 py-4 text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.2em]">Waktu</th>
                        <th class="px-8 py-4 text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.2em]">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EFEFEF]">
                    @foreach($logs as $log)
                    <tr class="hover:bg-[#FCFBF9] transition-all group">
                        <td class="px-8 py-4">
                            <p class="text-xs font-bold text-[#1E2432]">{{ $log->user->name }}</p>
                            <p class="text-[9px] text-[#8A8A8A] font-medium uppercase tracking-tighter">{{ $log->user->role }}</p>
                        </td>
                        <td class="px-8 py-4">
                            <p class="text-xs text-[#1E2432] font-medium">{{ $log->details }}</p>
                            <p class="text-[9px] text-[#E85A4F] font-black uppercase">{{ str_replace('_', ' ', $log->activity) }}</p>
                        </td>
                        <td class="px-8 py-4">
                            <p class="text-[10px] font-black text-[#1E2432]">{{ $log->created_at->format('d M Y') }}</p>
                            <p class="text-[9px] font-bold text-[#ABABAB]">{{ $log->created_at->format('H:i') }} ({{ $log->created_at->diffForHumans() }})</p>
                        </td>
                        <td class="px-8 py-4 text-[10px] font-mono text-[#ABABAB] group-hover:text-[#E85A4F] transition-all">{{ $log->ip_address }}</td>
                    </tr>
                    @endforeach
                    @if($logs->isEmpty())
                    <tr>
                        <td colspan="4" class="px-8 py-12 text-center text-xs text-[#8A8A8A] italic">Tidak ada log aktivitas ditemukan.</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="p-6 bg-[#FCFBF9]/50">
            {{ $logs->links() }}
        </div>
    </div>
</div>

<script>
    const userCtx = document.getElementById('userChart').getContext('2d');
    new Chart(userCtx, {
        type: 'polarArea',
        data: {
            labels: {!! json_encode($topDownloaders->pluck('user.name')) !!},
            datasets: [{
                data: {!! json_encode($topDownloaders->pluck('total')) !!},
                backgroundColor: ['rgba(232, 90, 79, 0.8)', 'rgba(30, 36, 50, 0.8)', 'rgba(138, 138, 138, 0.8)', 'rgba(239, 239, 239, 0.8)']
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { r: { grid: { display: false }, ticks: { display: false } } }
        }
    });
</script>
@endsection
