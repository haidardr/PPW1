<?php
// pages/matkul.php

// 1. Hubungkan file konfigurasi database lokal
require_once '../includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteksi Akses: Jika belum login, kembalikan ke gerbang login
if (!isset($_SESSION['user_id'])) {
    header("Location: /php/ppw/UAS-V2/lms-sukses/pages/login.php");
    exit;
}

// Ambil parameter Semester aktif dari URL (Default: Semester 1)
$id_semester_terpilih = isset($_GET['semester_id']) ? intval($_GET['semester_id']) : 1;
if ($id_semester_terpilih < 1 || $id_semester_terpilih > 8) {
    $id_semester_terpilih = 1;
}

// Ambil parameter kata kunci pencarian
$kata_kunci = isset($_GET['cari']) ? trim($_GET['cari']) : '';

// =========================================================================
// LOGIKA SINKRONISASI PAGINATION (MAKSIMAL 5 DATA & PENGUNCI HALAMAN GAIB)
// =========================================================================
$jumlah_data_per_halaman = 5;

// Ambil nomor halaman aktif dari URL (Default: 1)
$halaman_aktif = isset($_GET['halaman']) ? intval($_GET['halaman']) : 1;
if ($halaman_aktif < 1) { 
    $halaman_aktif = 1; 
}

// HITUNG TOTAL DATA SECARA REAL-TIME DARI DATABASE (Tanpa Inisial/Alias)
if ($kata_kunci !== '') {
    $query_total = "SELECT COUNT(*) AS total FROM courses JOIN course_semester ON courses.id = course_semester.course_id WHERE course_semester.semester_id = ? AND courses.nama_matkul LIKE ?";
    $stmt_total = $koneksi->prepare($query_total);
    $cari_parameter = "%" . $kata_kunci . "%";
    $stmt_total->bind_param("is", $id_semester_terpilih, $cari_parameter);
} else {
    $query_total = "SELECT COUNT(*) AS total FROM courses JOIN course_semester ON courses.id = course_semester.course_id WHERE course_semester.semester_id = ?";
    $stmt_total = $koneksi->prepare($query_total);
    $stmt_total->bind_param("i", $id_semester_terpilih);
}
$stmt_total->execute();
$total_data = $stmt_total->get_result()->fetch_assoc()['total'];
$stmt_total->close();

// Hitung total plafon halaman maksimal
$total_halaman = ceil($total_data / $jumlah_data_per_halaman);

// PENGUNCI BATAS ATAS: Cegah user mengetik manual angka halaman melebihi kapasitas nyata
if ($total_halaman > 0 && $halaman_aktif > $total_halaman) {
    $halaman_aktif = $total_halaman;
}

// Hitung index titik awal penarikan data baris MySQL
$titik_awal_data = ($halaman_aktif - 1) * $jumlah_data_per_halaman;

// =========================================================================
// QUERY UTAMA: AMBIL DATA MATKUL (URUTAN: WAJIB DULU, BARU BERDASARKAN ID)
// =========================================================================
if ($kata_kunci !== '') {
    $query_matkul = "SELECT courses.id, courses.kode_matkul, courses.nama_matkul, courses.is_pilihan 
                     FROM courses 
                     JOIN course_semester ON courses.id = course_semester.course_id 
                     WHERE course_semester.semester_id = ? AND courses.nama_matkul LIKE ? 
                     ORDER BY courses.is_pilihan ASC, courses.id ASC 
                     LIMIT ?, ?";
    $stmt_matkul = $koneksi->prepare($query_matkul);
    $cari_parameter = "%" . $kata_kunci . "%";
    $stmt_matkul->bind_param("isii", $id_semester_terpilih, $cari_parameter, $titik_awal_data, $jumlah_data_per_halaman);
} else {
    $query_matkul = "SELECT courses.id, courses.kode_matkul, courses.nama_matkul, courses.is_pilihan 
                     FROM courses 
                     JOIN course_semester ON courses.id = course_semester.course_id 
                     WHERE course_semester.semester_id = ? 
                     ORDER BY courses.is_pilihan ASC, courses.id ASC 
                     LIMIT ?, ?";
    $stmt_matkul = $koneksi->prepare($query_matkul);
    $stmt_matkul->bind_param("iii", $id_semester_terpilih, $titik_awal_data, $jumlah_data_per_halaman);
}
$stmt_matkul->execute();
$hasil_matkul = $stmt_matkul->get_result();

// Ambil list nama semester untuk navigasi pill buttons
$list_semester = $koneksi->query("SELECT id, nama_semester FROM semesters ORDER BY id ASC");

// 3. Render komponen visual navigasi atas
require_once '../includes/header.php';
?>

<div class="academic-section">
    <div class="sub-navigation" style="flex-wrap: wrap; gap: 20px;">
        <div>
            <h2 style="font-size: 24px; font-weight: 700; color: #0f172a; letter-spacing: -0.5px;">Ruang Belajar</h2>
            <p style="font-size: 14px; color: #64748b; font-weight: 400; margin-top: 2px;">
                Selamat datang, <span style="font-weight: 600; color: #0f172a;"><?php echo htmlspecialchars($_SESSION['username']); ?></span>. Silakan pilih kurikulum aktifmu.
            </p>
        </div>
        
        <form action="matkul.php" method="GET" style="display: flex; gap: 8px; width: 100%; max-width: 320px;">
            <input type="hidden" name="semester_id" value="<?php echo $id_semester_terpilih; ?>">
            <input type="text" class="form-control" name="cari" value="<?php echo htmlspecialchars($kata_kunci); ?>" placeholder="Cari mata kuliah..." style="padding: 8px 14px;">
            <button type="submit" style="background-color: #0f172a; color: #ffffff; border: none; padding: 0 16px; border-radius: 8px; font-size: 14px; cursor: pointer; font-weight: 500;">Cari</button>
        </form>
    </div>

    <div class="sub-navigation" style="padding-top: 0; padding-bottom: 10px;">
        <div class="semester-pill-container" style="width: 100%; -webkit-overflow-scrolling: touch;">
            <?php while ($sem = $list_semester->fetch_assoc()): ?>
                <a href="matkul.php?semester_id=<?php echo $sem['id']; ?>" class="semester-btn <?php echo ($id_semester_terpilih == $sem['id']) ? 'selected' : ''; ?>">
                    <?php echo htmlspecialchars($sem['nama_semester']); ?>
                </a>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="grid-container">
        <?php if ($hasil_matkul->num_rows > 0): ?>
            <?php while ($matkul = $hasil_matkul->fetch_assoc()): ?>
                <div class="academic-card">
                    <div class="card-top-content">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                            <span style="font-size: 12px; font-weight: 600; font-monospace: true; background-color: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 6px;">
                                <?php echo htmlspecialchars($matkul['kode_matkul']); ?>
                            </span>
                            <?php if ($matkul['is_pilihan'] === 'ya'): ?>
                                <span style="font-size: 11px; font-weight: 600; background-color: #fef3c7; color: #d97706; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px;">Pilihan</span>
                            <?php else: ?>
                                <span style="font-size: 11px; font-weight: 600; background-color: #dcfce7; color: #15803d; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px;">Wajib</span>
                            <?php endif; ?>
                        </div>
                        <h4 style="font-weight: 700; font-size: 18px; color: #0f172a; line-height: 1.3; margin-bottom: 10px; text-transform: uppercase; letter-spacing: -0.3px;">
                            <?php echo htmlspecialchars($matkul['nama_matkul']); ?>
                        </h4>
                        <p style="font-size: 13px; color: #64748b; line-height: 1.5; font-weight: 400;">
                            Akses materi pembelajaran terstruktur, ruang unduh berkas dokumen PDF, serta tautan video pengayaan kelas.
                        </p>
                    </div>
                    <div class="card-bottom-bar">
                        <span style="font-size: 13px; font-weight: 500; color: #64748b;">Akses Terbuka</span>
                        <a href="/php/ppw/UAS-V2/lms-sukses/pages/ruang_belajar.php?matkul_id=<?php echo $matkul['id']; ?>" style="background-color: #0f172a; color: #ffffff; text-decoration: none; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 500; transition: background-color 0.2s;">
                            Masuk Kelas
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>

            <?php if ($total_halaman > 1): ?>
                <div style="display: flex; align-items: center; justify-content: center; background-color: #ffffff; border: 1px dashed #cbd5e1; border-radius: 16px; height: 360px; padding: 30px; text-align: center;">
                    <div>
                        <span style="font-size: 13px; color: #64748b; font-weight: 500; display: block; margin-bottom: 12px;">Navigasi Halaman</span>
                        
                        <div class="pagination-container" style="margin-top: 0; gap: 6px;">
                            <?php if ($halaman_aktif > 1): ?>
                                <a href="matkul.php?semester_id=<?php echo $id_semester_terpilih; ?>&cari=<?php echo urlencode($kata_kunci); ?>&halaman=<?php echo $halaman_aktif - 1; ?>" style="padding: 6px 12px; border-radius: 6px;">&larr;</a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                                <a href="matkul.php?semester_id=<?php echo $id_semester_terpilih; ?>&cari=<?php echo urlencode($kata_kunci); ?>&halaman=<?php echo $i; ?>" class="<?php echo ($halaman_aktif == $i) ? 'active' : ''; ?>" style="padding: 6px 12px; border-radius: 6px;">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($halaman_aktif < $total_halaman): ?>
                                <a href="matkul.php?semester_id=<?php echo $id_semester_terpilih; ?>&cari=<?php echo urlencode($kata_kunci); ?>&halaman=<?php echo $halaman_aktif + 1; ?>" style="padding: 6px 12px; border-radius: 6px;">&rarr;</a>
                            <?php endif; ?>
                        </div>
                        
                        <span style="font-size: 11px; color: #94a3b8; font-weight: 500; display: block; margin-top: 10px;">
                            Halaman <?php echo $halaman_aktif; ?> dari <?php echo $total_halaman; ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div style="grid-column: 1 / -1; background-color: #ffffff; border: 1px dashed #cbd5e1; border-radius: 16px; padding: 60px; text-align: center;">
                <span style="font-size: 32px; display: block; margin-bottom: 10px;">📭</span>
                <h3 style="font-size: 18px; font-weight: 600; color: #0f172a;">Mata Kuliah Tidak Ditemukan</h3>
                <p style="font-size: 14px; color: #64748b; margin-top: 4px;">Tidak ada modul mata kuliah aktif yang cocok dengan kriteria pencarian Anda.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
$stmt_matkul->close();
require_once '../includes/footer.php'; 
?>