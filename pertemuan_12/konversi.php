<?php
$nilai_input = '';
$hasil = null;

function konversiNilai($nilai) {
    if (!is_numeric($nilai) || $nilai < 0 || $nilai > 100) {
        return ['grade' => '-', 'desc' => 'Nilai harus berupa angka antara 0 - 100', 'color' => 'danger'];
    }
    if ($nilai >= 85) return ['grade' => 'A', 'desc' => 'Sangat Baik', 'color' => 'success'];
    if ($nilai >= 70) return ['grade' => 'B', 'desc' => 'Baik', 'color' => 'primary'];
    if ($nilai >= 55) return ['grade' => 'C', 'desc' => 'Cukup', 'color' => 'warning text-dark'];
    if ($nilai >= 40) return ['grade' => 'D', 'desc' => 'Kurang', 'color' => 'danger'];
    return ['grade' => 'E', 'desc' => 'Sangat Kurang', 'color' => 'dark'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nilai_input = trim($_POST['nilai']);
    $hasil = konversiNilai($nilai_input);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Konversi Nilai</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container my-5" style="max-width: 500px;">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white text-center fw-bold">
            Form Konversi Nilai
        </div>
        <div class="card-body">
            <form action="konversi.php" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Masukkan Nilai Angka (0-100)</label>
                    <input type="number" name="nilai" class="form-control" min="0" max="100" step="0.01" value="<?= htmlspecialchars($nilai_input) ?>" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Konversi Nilai</button>
            </form>

            <?php if ($hasil !== null): ?>
                <div class="mt-4 p-3 border rounded text-center bg-white">
                    <h5 class="mb-2">Hasil Konversi:</h5>
                    <span class="badge bg-<?= $hasil['color'] ?> fs-3 mb-2"><?= $hasil['grade'] ?></span>
                    <p class="mb-0 fw-medium"><?= $hasil['desc'] ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>