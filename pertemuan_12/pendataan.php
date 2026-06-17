<?php
require 'koneksi.php';

$error = '';
$sukses = '';

// Inisialisasi variabel untuk sticky form
$nama = $nim = $prodi = $ipk = $semester = '';
$predikat = '';

// Fungsi untuk menghitung predikat kelulusan berdasarkan IPK
function hitungPredikat($ipk) {
    if ($ipk >= 3.51 && $ipk <= 4.00) return 'Dengan Pujian (Cum Laude)';
    if ($ipk >= 3.01 && $ipk < 3.51) return 'Sangat Memuaskan';
    if ($ipk >= 2.76 && $ipk < 3.01) return 'Memuaskan';
    if ($ipk >= 2.00 && $ipk < 2.76) return 'Cukup';
    return 'Kurang / Mengulang';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Mengambil dan membersihkan input data
    $nama     = trim($_POST['nama']);
    $nim      = trim($_POST['nim']);
    $prodi    = $_POST['prodi'] ?? '';
    $ipk      = trim($_POST['ipk']);
    $semester = trim($_POST['semester']);

    // Validasi Sisi Server
    if (empty($nama) || empty($nim) || empty($prodi) || empty($ipk) || empty($semester)) {
        $error .= "Semua field wajib diisi.<br>";
    }
    if (!preg_match('/^\d{2}\/\d{6}\/[A-Z]{2}\/\d{5}$/', $nim)) {
        $error .= "Format NIM tidak valid. Contoh: 25/123456/SV/12345<br>";
    }
    if (!is_numeric($ipk) || $ipk < 0.00 || $ipk > 4.00) {
        $error .= "IPK harus berupa angka di antara rentang 0.00 - 4.00.<br>";
    }
    if (!is_numeric($semester) || $semester < 1 || $semester > 14) {
        $error .= "Semester harus berupa angka di antara rentang 1 - 14.<br>";
    }

    // Jika validasi lolos, hitung predikat kelulusan dan simpan ke database
    if (empty($error)) {
        $predikat = hitungPredikat($ipk);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO pendataan (nama, nim, prodi, ipk, semester) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nama, $nim, $prodi, $ipk, $semester]);
            $sukses = "Data mahasiswa sukses divalidasi dan berhasil disimpan secara permanen!";
        } catch (\PDOException $e) {
            $error = "Gagal menyimpan data ke database: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Pendataan Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container my-5" style="max-width: 650px;">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white text-center fw-bold">
            Form Pendataan Mahasiswa
        </div>
        <div class="card-body">
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <?php if (!empty($sukses)): ?>
                <div class="alert alert-success"><?= $sukses ?></div>
            <?php endif; ?>

            <form action="pendataan.php" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($nama) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">NIM (Format UGM)</label>
                    <input type="text" name="nim" class="form-control" placeholder="Contoh: 25/123456/SV/12345" value="<?= htmlspecialchars($nim) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Program Studi</label>
                    <select name="prodi" class="form-select" required>
                        <option value="">-- Pilih Program Studi --</option>
                        <option value="Teknologi Rekayasa Perangkat Lunak" <?= $prodi == 'Teknologi Rekayasa Perangkat Lunak' ? 'selected' : '' ?>>TRPL</option>
                        <option value="Sistem Informasi Geografis" <?= $prodi == 'Sistem Informasi Geografis' ? 'selected' : '' ?>>SIG</option>
                        <option value="Teknik Elektro" <?= $prodi == 'Teknik Elektro' ? 'selected' : '' ?>>Teknik Elektro</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">IPK</label>
                        <input type="number" name="ipk" class="form-control" step="0.01" min="0" max="4" value="<?= htmlspecialchars($ipk) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Semester</label>
                        <input type="number" name="semester" class="form-control" min="1" max="14" value="<?= htmlspecialchars($semester) ?>" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-dark w-100 fw-bold">Validasi & Kirim Data</button>
            </form>

            <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($error)): ?>
                <div class="mt-4 p-4 border rounded bg-white shadow-sm">
                    <h5 class="text-center text-primary mb-3 fw-bold border-bottom pb-2">Resume Ringkasan Data</h5>
                    <table class="table table-bordered mb-0 align-middle">
                        <tr><th width="35%">Nama</th><td><?= htmlspecialchars($nama) ?></td></tr>
                        <tr><th>NIM</th><td><?= htmlspecialchars($nim) ?></td></tr>
                        <tr><th>Program Studi</th><td><?= htmlspecialchars($prodi) ?></td></tr>
                        <tr><th>IPK</th><td><?= htmlspecialchars($ipk) ?></td></tr>
                        <tr><th>Semester</th><td><?= htmlspecialchars($semester) ?></td></tr>
                        <tr class="table-info fw-bold"><th>Predikat Kelulusan</th><td><?= htmlspecialchars($predikat) ?></td></tr>
                    </table>
                </div>
            <?php endif; ?>
            
        </div>
    </div>
</div>
</body>
</html>