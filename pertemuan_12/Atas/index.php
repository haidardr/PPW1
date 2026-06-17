<?php
require '../koneksi.php';

$error = '';
$sukses = '';

// 1. DELETE
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM mahasiswa WHERE id = ?");
    if ($stmt->execute([$id])) {
        $sukses = "Data mahasiswa berhasil dihapus!";
    } else {
        $error = "Gagal menghapus data.";
    }
}

// 2. CREATE & UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id       = $_POST['id'] ?? '';
    $nim      = trim($_POST['nim']);
    $nama     = trim($_POST['nama']);
    $email    = trim($_POST['email']);
    $jurusan  = $_POST['jurusan'];
    $alamat   = trim($_POST['alamat']);

    // Validasi Regex NIM UGM dan format Email
    if (!preg_match('/^\d{2}\/\d{6}\/[A-Z]{2}\/\d{5}$/', $nim)) {
        $error .= "Format NIM tidak valid. Contoh: 25/123456/SV/12345<br>";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error .= "Format email tidak valid.<br>";
    }

    if (empty($error)) {
        if (!empty($id)) {
            // Jika ID ada, lakukan UPDATE
            $stmt = $pdo->prepare("UPDATE mahasiswa SET nim = ?, nama = ?, jurusan = ?, email = ?, alamat = ? WHERE id = ?");
            if ($stmt->execute([$nim, $nama, $jurusan, $email, $alamat, $id])) {
                $sukses = "Data mahasiswa berhasil diperbarui!";
            }
        } else {
            // Jika ID kosong, lakukan CREATE (TAMBAH DATA BARU)
            $stmt = $pdo->prepare("INSERT INTO mahasiswa (nim, nama, jurusan, email, alamat) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$nim, $nama, $jurusan, $email, $alamat])) {
                $sukses = "Data mahasiswa baru berhasil ditambahkan!";
            }
        }
    }
}

// 3. EDIT
$edit_data = ['id' => '', 'nim' => '', 'nama' => '', 'email' => '', 'jurusan' => '', 'alamat' => ''];
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $fetch = $stmt->fetch();
    if ($fetch) {
        $edit_data = $fetch;
    }
}

// 4. READ
$stmt = $pdo->query("SELECT * FROM mahasiswa ORDER BY id DESC");
$daftar_mahasiswa = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Mahasiswa - PPW 12</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-5">
    <h2 class="text-center mb-4">Sistem Pendataan Mahasiswa</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($sukses)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $sukses ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white text-center fw-bold">
                    <?= !empty($edit_data['id']) ? 'Edit Data Mahasiswa' : 'Tambah Mahasiswa Baru' ?>
                </div>
                <div class="card-body">
                    <form action="index.php" method="POST">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($edit_data['id']) ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">NIM</label>
                            <input type="text" name="nim" class="form-placeholder form-control" placeholder="Contoh: 25/123456/SV/12345" value="<?= htmlspecialchars($edit_data['nim']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($edit_data['nama']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($edit_data['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Jurusan</label>
                            <select name="jurusan" class="form-select" required>
                                <option value="">-- Pilih Jurusan --</option>
                                <option value="Teknologi Rekayasa Perangkat Lunak" <?= $edit_data['jurusan'] == 'Teknologi Rekayasa Perangkat Lunak' ? 'selected' : '' ?>>TRPL</option>
                                <option value="Sistem Informasi Geografis" <?= $edit_data['jurusan'] == 'Sistem Informasi Geografis' ? 'selected' : '' ?>>SIG</option>
                                <option value="Teknik Elektro" <?= $edit_data['jurusan'] == 'Teknik Elektro' ? 'selected' : '' ?>>Teknik Elektro</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="3" required><?= htmlspecialchars($edit_data['alamat']) ?></textarea>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">Simpan Data</button>
                            <?php if (!empty($edit_data['id'])): ?>
                                <a href="index.php" class="btn btn-secondary">Batal Edit</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white fw-bold">
                    Daftar Mahasiswa Terdaftar
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">NIM</th>
                                    <th>Nama</th>
                                    <th>Jurusan</th>
                                    <th>Email</th>
                                    <th class="text-center" style="min-width: 130px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($daftar_mahasiswa) > 0): ?>
                                    <?php foreach ($daftar_mahasiswa as $row): ?>
                                        <tr>
                                            <td class="ps-3 fw-medium"><?= htmlspecialchars($row['nim']) ?></td>
                                            <td><?= htmlspecialchars($row['nama']) ?></td>
                                            <td><?= htmlspecialchars($row['jurusan']) ?></td>
                                            <td><?= htmlspecialchars($row['email']) ?></td>
                                            <td class="text-center pe-3">
                                                <a href="index.php?action=edit&id=<?= $row['id'] ?>" class="btn btn-warning btn-sm me-1">Edit</a>
                                                <a href="index.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data mahasiswa.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>