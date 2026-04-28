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
                            <td style="border: none; padding: 1px 0; text-align: left; width: 80px;">Dinas Pagi</td>
                            <td style="border: none; padding: 1px 0; text-align: left;">: 06.00 WIB – 13.00 WIB</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 1px 0; text-align: left;">Dinas Siang</td>
                            <td style="border: none; padding: 1px 0; text-align: left;">: 13.00 WIB – 20.00 WIB</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 1px 0; text-align: left;">Dinas Malam</td>
                            <td style="border: none; padding: 1px 0; text-align: left;">: 20.00 WIB – 06.00 WIB</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 1px 0; text-align: left;">Pola Rotasi</td>
                            <td style="border: none; padding: 1px 0; text-align: left;">: Pagi - Siang - Malam - Libur</td>
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
