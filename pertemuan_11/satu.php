<?php
// Deklarasi variabel profil
$nama = "Haidar Daffa";
$nim = "2509102001"; // Silakan sesuaikan dengan NIM asli Anda
$prodi = "Teknik Informatika";
$asal_kota = "Surabaya"; // Silakan sesuaikan dengan kota asal Anda
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Mahasiswa</title>
    <style>
        table {
            border-collapse: collapse;
            width: 50%;
            margin: 20px 0;
            font-family: Arial, sans-serif;
        }
        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>
<body>

<h2>Profil Diri</h2>
<table>
    <tr>
        <th>Data</th>
        <th>Informasi</th>
    </tr>
    <tr>
        <td>Nama</td>
        <td><?php echo $nama; ?></td>
    </tr>
    <tr>
        <td>NIM</td>
        <td><?php echo $nim; ?></td>
    </tr>
    <tr>
        <td>Program Studi</td>
        <td><?php echo $prodi; ?></td>
    </tr>
    <tr>
        <td>Asal Kota</td>
        <td><?php echo $asal_kota; ?></td>
    </tr>
</table>

</body>
</html>