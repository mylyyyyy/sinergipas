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
        <div class="p-8 border-b border-[#EFEFEF] bg-[#FCFBF9]/50 flex justify-between items-center">
            <h3 class="text-lg font-black text-[#1E2432]">Riwayat Akses Terbaru</h3>
            <form action="{{ route('audit.clear') }}" method="POST" onsubmit="return confirm('Peringatan: Seluruh riwayat akan dihapus permanen. Lanjutkan?')">
                @csrf @method('DELETE')
                <button type="submit" class="bg-red-50 text-red-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-600 hover:text-white transition-all">
                    Bersihkan Seluruh Log
                </button>
            </form>
        </div>
        <div class="flex-1 overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#FCFBF9]">
                        <th class="px-8 py-4 text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.2em]">User</th>
                        <th class="px-8 py-4 text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.2em]">Dokumen</th>
                        <th class="px-8 py-4 text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.2em]">Waktu</th>
                        <th class="px-8 py-4 text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.2em]">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EFEFEF]">
                    @foreach($logs as $log)
                    <tr class="hover:bg-[#FCFBF9] transition-all">
                        <td class="px-8 py-4 text-xs font-bold text-[#1E2432]">{{ $log->user->name }}</td>
                        <td class="px-8 py-4 text-xs text-[#8A8A8A]">{{ $log->document->title ?? 'N/A' }}</td>
                        <td class="px-8 py-4 text-[10px] font-black text-[#ABABAB]">{{ $log->created_at->diffForHumans() }}</td>
                        <td class="px-8 py-4 text-[10px] font-mono text-[#E85A4F]">{{ $log->ip_address }}</td>
                    </tr>
                    @endforeach
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
