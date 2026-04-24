<!DOCTYPE html>
<html>
<head>
    <title>Rekap Tunkin - {{ $monthStr }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #334155; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { position: absolute; left: 0; top: 0; width: 60px; }
        .kop-1 { font-size: 12px; font-weight: bold; margin-bottom: 2px; }
        .kop-2 { font-size: 16px; font-weight: bold; color: #1e40af; margin-bottom: 2px; }
        .kop-3 { font-size: 9px; font-style: italic; }
        .title { text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 20px; text-decoration: underline; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 8px; font-weight: bold; text-align: left; text-transform: uppercase; font-size: 8px; }
        td { border: 1px solid #e2e8f0; padding: 8px; vertical-align: top; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .footer { margin-top: 30px; }
        .signature { float: right; width: 200px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" class="logo">
        @endif
        <div class="kop-1">KEMENTERIAN IMIGRASI DAN PEMASYARAKATAN RI</div>
        <div class="kop-2">LEMBAGA PEMASYARAKATAN KELAS IIB JOMBANG</div>
        <div class="kop-3">Jl. KH. Wahid Hasyim No. 151, Jombang | Telp: (0321) 861114</div>
    </div>

    <div class="title">REKAPITULASI TUNJANGAN KINERJA & UANG MAKAN PERIODE {{ strtoupper($date->translatedFormat('F Y')) }}</div>

    <table>
        <thead>
            <tr>
                <th width="30">NO</th>
                <th>NAMA PEGAWAI / NIP</th>
                <th width="40">GRADE</th>
                <th>JABATAN</th>
                <th width="40" class="text-center">HADIR</th>
                <th class="text-right">UANG MAKAN</th>
                <th class="text-right">TUNKIN</th>
                <th class="text-right">POTONGAN</th>
                <th class="text-right">TOTAL TERIMA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $index => $emp)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <div class="font-bold">{{ $emp->full_name }}</div>
                    <div style="color: #64748b; font-size: 8px;">NIP. {{ $emp->nip }}</div>
                </td>
                <td class="text-center">{{ $emp->tunkin->grade ?? '-' }}</td>
                <td>{{ $emp->position }}</td>
                <td class="text-center">{{ $emp->total_attendance }}d</td>
                <td class="text-right">Rp {{ number_format($emp->meal_allowance, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($emp->base_tunkin, 0, ',', '.') }}</td>
                <td class="text-right" style="color: #ef4444;">Rp {{ number_format($emp->potongan, 0, ',', '.') }}</td>
                <td class="text-right font-bold">Rp {{ number_format($emp->grand_total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div style="float: left; font-size: 8px; color: #94a3b8;">
            Dicetak secara otomatis oleh Sistem Sinergi PAS pada {{ now()->format('d/m/Y H:i') }}
        </div>
        <div class="signature">
            Jombang, {{ now()->translatedFormat('d F Y') }}<br>
            Bendahara Pengeluaran,<br><br><br><br>
            <strong>__________________________</strong><br>
            NIP. .........................
        </div>
    </div>
</body>
</html>
