<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tugas PHP & Javascript</title>

    <style>
        body{
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }

        h2{
            color: #333;
        }

        table{
            border-collapse: collapse;
            width: 50%;
            background: white;
            margin-bottom: 30px;
        }

        table, th, td{
            border: 1px solid black;
            padding: 10px;
        }

        .card{
            background: white;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 0 5px rgba(0,0,0,0.2);
        }

        input{
            padding: 8px;
            margin: 5px;
        }

        button{
            padding: 10px 15px;
            margin: 5px;
            cursor: pointer;
        }

        #hasil{
            margin-top: 15px;
            font-weight: bold;
            color: blue;
        }
    </style>
</head>
<body>

    <!-- 1. Profil Diri -->
    <div class="card">
        <h2>1A. Profil Diri</h2>
        <?php
            $nama = "Haidar Daff Rasyiqin";
            $nim = "25/562020/SV/26719";
            $prodi = "Sofeng";
            $asalKota = "Yogyakarta";
        ?>
        <table>
            <tr><th>Data</th><th>Keterangan</th></tr>
            <tr><td>Nama</td><td><?php echo $nama; ?></td></tr>
            <tr><td>NIM</td><td><?php echo $nim; ?></td></tr>
            <tr><td>Prodi</td><td><?php echo $prodi; ?></td></tr>
            <tr><td>Asal Kota</td><td><?php echo $asalKota; ?></td></tr>
        </table>
    </div>


    <!-- 2. Fungsi Hitung IMT -->
    <div class="card">
        <h2>1B. Hitung Indeks Massa Tubuh (IMT)</h2>
        <?php
            function hitungIMT($berat, $tinggi){
                $imt = $berat / ($tinggi * $tinggi);
                if($imt < 18.5){
                    $kategori = "Kurus";
                } elseif($imt >= 18.5 && $imt < 25){
                    $kategori = "Normal";
                } elseif($imt >= 25 && $imt < 30){
                    $kategori = "Gemuk";
                } else {
                    $kategori = "Obesitas";
                }
                return [
                    "imt" => round($imt,2),
                    "kategori" => $kategori
                ];
            }
            $berat = 70;
            $tinggi = 1.70;
            $hasil = hitungIMT($berat, $tinggi);
        ?>
        <p>Berat Badan : <b><?php echo $berat; ?> kg</b></p>
        <p>Tinggi Badan : <b><?php echo $tinggi; ?> m</b></p>
        <p>Nilai IMT : <b><?php echo $hasil['imt']; ?></b></p>
        <p>Kategori : <b><?php echo $hasil['kategori']; ?></b></p>
    </div>


    <!-- 3. Nama Bulan dan Sisa Hari -->
    <div class="card">
        <h2>1C. Informasi Bulan Sekarang</h2>
        <?php
            date_default_timezone_set("Asia/Jakarta");
            $bulan = date("F");
            $hariIni = date("d");
            $jumlahHari = date("t");
            $sisaHari = $jumlahHari - $hariIni;
        ?>
        <p>Bulan Sekarang : <b><?php echo $bulan; ?></b></p>
        <p>Sisa Hari di Bulan Ini : <b><?php echo $sisaHari; ?> hari</b></p>
    </div>


    <!-- 4. Kalkulator Javascript -->
    <div class="card">
        <h2>2. Kalkulator Javascript</h2>
        <input type="number" id="bil1" placeholder="Bilangan Pertama">
        <input type="number" id="bil2" placeholder="Bilangan Kedua">
        <br>
        <button onclick="tambah()">Tambah</button>
        <button onclick="kurang()">Kurang</button>
        <button onclick="kali()">Kali</button>
        <button onclick="bagi()">Bagi</button>
        <div id="hasil"></div>
    </div>

    <script>
        function ambilNilai(){
            let bil1 = parseFloat(document.getElementById("bil1").value);
            let bil2 = parseFloat(document.getElementById("bil2").value);
            return {bil1, bil2};
        }
        function tambah(){
            let data = ambilNilai();
            let hasil = data.bil1 + data.bil2;
            document.getElementById("hasil").innerHTML =
                "Hasil Penjumlahan = " + hasil;
        }
        function kurang(){
            let data = ambilNilai();
            let hasil = data.bil1 - data.bil2;
            document.getElementById("hasil").innerHTML =
                "Hasil Pengurangan = " + hasil;
        }
        function kali(){
            let data = ambilNilai();
            let hasil = data.bil1 * data.bil2;
            document.getElementById("hasil").innerHTML =
                "Hasil Perkalian = " + hasil;
        }
        function bagi(){
            let data = ambilNilai();
            if(data.bil2 == 0){
                document.getElementById("hasil").innerHTML =
                    "Tidak bisa dibagi dengan 0";
            } else {
                let hasil = data.bil1 / data.bil2;
                document.getElementById("hasil").innerHTML =
                    "Hasil Pembagian = " + hasil;
            }
        }
    </script>

</body>
</html>