<?php
function hitungIMT($berat, $tinggi) {
    // Formula IMT: Berat (kg) / (Tinggi (m) * Tinggi (m))
    $imt = $berat / ($tinggi * $tinggi);
    
    // Penentuan kategori berdasarkan nilai IMT
    if ($imt < 18.5) {
        return "Kurus";
    } elseif ($imt >= 18.5 && $imt < 25) {
        return "Normal";
    } elseif ($imt >= 25 && $imt < 30) {
        return "Gemuk";
    } else {
        return "Obesitas";
    }
}

// Contoh penggunaan fungsi
$berat_badan = 65; // dalam kg
$tinggi_badan = 1.70; // dalam meter

$kategori = hitungIMT($berat_badan, $tinggi_badan);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kalkulator IMT PHP</title>
</head>
<body>
    <h3>Hasil Pengukuran IMT</h3>
    <p>Berat Badan: <?php echo $berat_badan; ?> kg</p>
    <p>Tinggi Badan: <?php echo $tinggi_badan; ?> m</p>
    <p>Kategori Kesehatan: <strong><?php echo $kategori; ?></strong></p>
</body>
</html>