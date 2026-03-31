<!DOCTYPE html>
<html>
<head>
    <title>Daftar Pegawai Lapas Jombang</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        h2 { text-align: center; }
    </style>
</head>
<body>
    <h2>DAFTAR PEGAWAI LAPAS JOMBANG</h2>
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
</body>
</html>
