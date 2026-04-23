@extends('layouts.pdf_export')

@section('title', 'Jadwal Regu Jaga')

@section('report_title', $title)
@section('report_meta', 'Periode: ' . strtoupper($date->translatedFormat('F Y')))

@section('content')
    <style>
        /* Speed optimization for high column density */
        @page { margin: 0.5cm 0.5cm; }
        .main-table th, .main-table td { padding: 2px; font-size: 6px; text-align: center; border: 1px solid #000; }
        .weekend { background-color: #fdd !important; }
        .shift-name { font-weight: bold; text-align: left !important; padding-left: 3px !important; }
        .regu-box { font-weight: bold; color: #000; }
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

    <div style="margin-top: 25px;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 60%; vertical-align: top; text-align: left; border: none; padding: 0;">
                    <div style="font-size: 8px; font-weight: bold; color: #0f172a; margin-bottom: 5px; text-decoration: underline;">KETERANGAN JADWAL:</div>
                    <table style="font-size: 7px; color: #475569; border: none;">
                        <tr>
                            <td style="border: none; padding: 1px 0; text-align: left; width: 80px;">1. Pola Rotasi</td>
                            <td style="border: none; padding: 1px 0; text-align: left;">: 4 Regu (Pagi - Siang & Malam - Libur - Libur)</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 1px 0; text-align: left;">2. Sesi Pagi</td>
                            <td style="border: none; padding: 1px 0; text-align: left;">: Sesuai jam dinas yang ditetapkan di sistem</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 1px 0; text-align: left;">3. Sesi Siang/Malam</td>
                            <td style="border: none; padding: 1px 0; text-align: left;">: Sesuai jam dinas pengamanan yang berlaku</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 1px 0; text-align: left;">4. Validitas</td>
                            <td style="border: none; padding: 1px 0; text-align: left;">: Dokumen resmi hasil sistem Sinergi PAS Jombang</td>
                        </tr>
                    </table>
                </td>
                <td style="border: none;"></td>
            </tr>
        </table>
    </div>
@endsection

@section('footer_left')
    <!-- Empty left side for this specific layout -->
@endsection

@section('signer_title', 'Kepala Kesatuan Pengamanan')
