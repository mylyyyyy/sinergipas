<!DOCTYPE html>
<html>
<head>
    <title>Daftar Pegawai Lapas Jombang</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f8f9fa; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #E85A4F; padding-bottom: 10px; }
        .footer { margin-top: 50px; }
        .signature { float: right; text-align: center; width: 200px; }
        .qr { margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="color: #E85A4F; margin: 0;">SINERGI PAS</h1>
        <p style="margin: 5px 0;">Lembaga Pemasyarakatan Jombang</p>
        <h2 style="margin: 10px 0;">DAFTAR PEGAWAI RESMI</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>NIP</th>
                <th>Nama Lengkap</th>
                <th>Jabatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $e)
            <tr>
                <td>{{ $e->nip }}</td>
                <td>{{ $e->full_name }}</td>
                <td>{{ $e->position }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="signature">
            <p>Dokumen Digital Sah</p>
            <div class="qr">
                <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(100)->generate('Verified by Sinergi PAS - ' . date('d M Y H:i:s'))) !!} ">
            </div>
            <p><strong>Sinergi PAS System</strong></p>
            <p style="font-size: 8px; color: #888;">ID: {{ md5(time()) }}</p>
        </div>
    </div>
</body>
</html>
