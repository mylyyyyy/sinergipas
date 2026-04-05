<!DOCTYPE html>
<html>
<head>
    <title>Daftar Pegawai Lapas Jombang</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .kop { text-align: center; margin-bottom: 30px; border-bottom: 3px double #0F172A; padding-bottom: 15px; position: relative; }
        .kop img { width: 80px; margin-bottom: 10px; }
        .kop h1 { margin: 0; font-size: 18px; font-weight: bold; color: #0F172A; }
        .kop h2 { margin: 2px 0; font-size: 14px; font-weight: normal; }
        .kop p { margin: 2px 0; font-size: 10px; font-style: italic; color: #666; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f9f9f9; font-weight: bold; text-transform: uppercase; font-size: 9px; }
        
        .footer { margin-top: 40px; position: relative; }
        .signature { float: right; text-align: center; width: 200px; }
        .qr-box { margin: 10px 0; }
    </style>
</head>
<body>
    <div class="kop">
        <img src="{{ public_path('logo1.png') }}">
        <h1>{{ \App\Models\Setting::getValue('kop_line_1', 'LEMBAGA PEMASYARAKATAN JOMBANG') }}</h1>
        <h2>{{ \App\Models\Setting::getValue('kop_line_2', 'KANTOR WILAYAH KEMENTERIAN HUKUM DAN HAM JAWA TIMUR') }}</h2>
        <p>{{ \App\Models\Setting::getValue('kop_address', 'Jl. KH. Wahid Hasyim No. 123, Jombang') }}</p>
    </div>

    <h3 style="text-align: center; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 20px;">Daftar Pegawai Resmi</h3>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>NIP</th>
                <th>Nama Lengkap</th>
                <th>Jabatan</th>
                <th>Unit Kerja</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $index => $e)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td style="font-family: monospace;">{{ $e->nip }}</td>
                <td style="font-weight: bold;">{{ $e->full_name }}</td>
                <td>{{ $e->position }}</td>
                <td>{{ $e->work_unit->name ?? 'N/A' }}</td>
                <td>{{ $e->user->email ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="signature">
            <p style="font-size: 10px; margin-bottom: 10px;">Dicetak secara digital pada: {{ date('d/m/Y H:i') }}</p>
            <div class="qr-box">
                {!! QrCode::size(80)->generate('DocID: ' . md5(time()) . ' - Sinergi PAS Jombang') !!}
            </div>
            <p><strong>ADMINISTRASI KEPEGAWAIAN</strong></p>
            <p style="font-size: 9px; color: #888; margin-top: 5px;">Sinergi PAS Digital Verification</p>
        </div>
    </div>
</body>
</html>
