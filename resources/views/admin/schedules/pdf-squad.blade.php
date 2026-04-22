@extends('layouts.pdf_export')

@section('title', 'Jadwal Regu Jaga')

@section('report_title', 'JADWAL TUGAS REGU JAGA')
@section('report_meta', 'Periode: ' . strtoupper($date->translatedFormat('F Y')))

@section('content')
    <style>
        /* Override for high column density */
        @page { margin: 0.8cm 1cm; }
        .main-table th, .main-table td { padding: 3px 1px; font-size: 7px; text-align: center; border: 1px solid #94a3b8; }
        .weekend { background-color: #ffe4e6 !important; }
        .shift-name { font-weight: bold; text-align: left !important; padding-left: 5px !important; background-color: #f8fafc; }
        .regu-box { font-weight: 800; color: #1e40af; }
    </style>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 50px; background-color: #0f172a; color: white;">SESI</th>
                @for($d = 1; $d <= $daysInMonth; $d++)
                    @php $isWeekend = $date->copy()->day($d)->isWeekend(); @endphp
                    <th class="{{ $isWeekend ? 'weekend' : '' }}" style="{{ $isWeekend ? 'color: #b91c1c;' : '' }}">
                        {{ $d }}<br>
                        <span style="font-size: 6px;">{{ strtoupper($date->copy()->day($d)->translatedFormat('D')) }}</span>
                    </th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach($shifts as $shift)
            <tr>
                <td class="shift-name">{{ $shift->name }}</td>
                @for($d = 1; $d <= $daysInMonth; $d++)
                    @php 
                        $dateStr = $date->copy()->day($d)->format('Y-m-d');
                        $current = $schedules->get($dateStr . '_' . $shift->id)?->first();
                        $isWeekend = $date->copy()->day($d)->isWeekend();
                    @endphp
                    <td class="{{ $isWeekend ? 'weekend' : '' }}">
                        <span class="regu-box">{{ $current ? $current->squad->name : '-' }}</span>
                    </td>
                @endfor
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 15px; font-size: 7px; color: #64748b;">
        <strong>Keterangan:</strong> Sesi Jaga menyesuaikan dengan jadwal shift yang telah ditetapkan di sistem Sinergi PAS.
    </div>
@endsection

@section('footer_left')
    <!-- Empty left side for this specific layout -->
@endsection

@section('signer_title', 'Kepala Kesatuan Pengamanan')
