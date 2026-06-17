<?php
// pages/ruang_belajar.php

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

// Ambil ID Mata Kuliah dari URL
$matkul_id = isset($_GET['matkul_id']) ? intval($_GET['matkul_id']) : 0;

// Query mengambil informasi dasar mata kuliah (Tanpa Inisial/Alias)
$query_info_matkul = "SELECT courses.nama_matkul, courses.kode_matkul FROM courses WHERE courses.id = ?";
$stmt_info = $koneksi->prepare($query_info_matkul);
$stmt_info->bind_param("i", $matkul_id);
$stmt_info->execute();
$info_matkul = $stmt_info->get_result()->fetch_assoc();
$stmt_info->close();

// Jika mata kuliah tidak ditemukan, kembalikan ke dashboard utama
if (!$info_matkul) {
    header("Location: /php/ppw/UAS-V2/lms-sukses/pages/matkul.php");
    exit;
}

// Ambil ID Materi spesifik yang sedang dipilih/dibaca oleh mahasiswa (jika ada)
$materi_aktif_id = isset($_GET['materi_id']) ? intval($_GET['materi_id']) : 0;

// Query mengambil seluruh daftar modul materi yang tersedia di mata kuliah ini
$query_list_materi = "SELECT course_contents.id, course_contents.judul_materi, course_contents.tipe_konten FROM course_contents WHERE course_contents.matkul_id = ? ORDER BY course_contents.id ASC";
$stmt_list = $koneksi->prepare($query_list_materi);
$stmt_list->bind_param("i", $matkul_id);
$stmt_list->execute();
$daftar_materi = $stmt_list->get_result();
$stmt_list->close();

// Detail konten materi aktif yang sedang dibuka untuk dibaca
$konten_aktif = null;
if ($materi_aktif_id > 0) {
    $query_konten = "SELECT course_contents.id, course_contents.judul_materi, course_contents.tipe_konten FROM course_contents WHERE course_contents.id = ? AND course_contents.matkul_id = ?";
    $stmt_konten = $koneksi->prepare($query_konten);
    $stmt_konten->bind_param("ii", $materi_aktif_id, $matkul_id);
    $stmt_konten->execute();
    $konten_aktif = $stmt_konten->get_result()->fetch_assoc();
    $stmt_konten->close();
}

require_once '../includes/header.php';
?>

<div class="academic-section">
    <div class="sub-navigation" style="margin-bottom: 20px;">
        <div>
            <span style="font-size: 12px; font-weight: 600; background-color: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 6px; font-monospace: true;">
                <?php echo htmlspecialchars($info_matkul['kode_matkul']); ?>
            </span>
            <h2 style="font-size: 24px; font-weight: 700; color: #0f172a; letter-spacing: -0.5px; margin-top: 6px; text-transform: uppercase;">
                <?php echo htmlspecialchars($info_matkul['nama_matkul']); ?>
            </h2>
            <p style="font-size: 14px; color: #64748b; font-weight: 400; margin-top: 2px;">Silakan pilih daftar modul pertemuan di sebelah kiri untuk mulai mempelajari materi.</p>
        </div>
        <div>
            <a href="/php/ppw/UAS-V2/lms-sukses/pages/matkul.php" class="semester-btn" style="border-radius: 8px; font-size: 13px;">&larr; Kembali ke Dashboard</a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; padding: 0 40px; align-items: start;">
        
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <h4 style="font-size: 15px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Daftar Modul Pembelajaran</h4>
            
            <?php if ($daftar_materi->num_rows > 0): ?>
                <?php while ($materi = $daftar_materi->fetch_assoc()): ?>
                    <?php 
                    // Penanda apakah item ini sedang aktif dibuka
                    $is_aktif = ($materi['id'] == $materi_aktif_id);
                    ?>
                    <a href="ruang_belajar.php?matkul_id=<?php echo $matkul_id; ?>&materi_id=<?php echo $materi['id']; ?>" 
                       style="text-decoration: none; display: block; color: inherit;">
                        
                        <div class="academic-card" style="height: auto; padding: 16px 20px; border-radius: 12px; background-color: <?php echo $is_aktif ? '#f8fafc' : '#ffffff'; ?>; border-color: <?php echo $is_aktif ? '#2563eb' : '#e2e8f0'; ?>; box-shadow: none;">
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 15px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <?php if ($materi['tipe_konten'] === 'teks'): ?>
                                        <span style="font-size: 18px;">📄</span>
                                    <?php elseif ($materi['tipe_konten'] === 'video'): ?>
                                        <span style="font-size: 18px;">🎥</span>
                                    <?php else: ?>
                                        <span style="font-size: 18px;">📂</span>
                                    <?php endif; ?>
                                    
                                    <span style="font-size: 14px; font-weight: <?php echo $is_aktif ? '600' : '500'; ?>; color: <?php echo $is_aktif ? '#0f172a' : '#334155'; ?>; text-transform: uppercase;">
                                        <?php echo htmlspecialchars($materi['judul_materi']); ?>
                                    </span>
                                </div>
                                
                                <span style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8;">
                                    <?php echo htmlspecialchars($materi['tipe_konten']); ?>
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="background-color: #ffffff; border: 1px dashed #cbd5e1; border-radius: 12px; padding: 30px; text-align: center; color: #64748b; font-size: 13px;">
                    📭 Belum ada modul materi kuliah yang diunggah untuk kelas ini.
                </div>
            <?php endif; ?>
        </div>

        <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 40px; min-height: 400px; box-shadow: 0 4px 12px rgba(0,0,0,0.01);">
            <?php if ($konten_aktif): ?>
                
                <h3 style="font-weight: 700; font-size: 20px; color: #0f172a; letter-spacing: -0.3px; margin-bottom: 8px; text-transform: uppercase;">
                    <?php echo htmlspecialchars($konten_aktif['judul_materi']); ?>
                </h3>
                <div style="font-size: 12px; color: #94a3b8; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 25px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
                    Media Pembelajaran: <span style="color: #475569; font-weight: 600;"><?php echo htmlspecialchars($konten_aktif['tipe_konten']); ?></span>
                </div>

                <div style="font-size: 15px; color: #334155; line-height: 1.7; font-weight: 400;">
                    <?php if ($konten_aktif['tipe_konten'] === 'teks'): ?>
                        <p style="margin-bottom: 16px; text-align: justify;">Selamat datang di modul rangkuman perkuliahan formal. Silakan cermati poin-poin capaian instruksional pokok pembelajaran pada bab pertemuan ini dengan saksama.</p>
                        <div style="background-color: #f8fafc; border-left: 4px solid #2563eb; padding: 15px 20px; border-radius: 0 8px 8px 0; margin: 24px 0; font-style: italic; color: #475569;">
                            "Peer-learning mandiri yang terstruktur secara kolektif merupakan kunci utama akselerasi pemahaman kurikulum akademik tingkat tinggi."
                        </div>
                        <p style="text-align: justify;">Lakukan tinjauan pustaka secara berulang dan manfaatkan ruang diskusi komunitas untuk memperdalam implementasi studi kasus nyata di luar jam operasional kelas.</p>

                    <?php elseif ($konten_aktif['tipe_konten'] === 'video'): ?>
                        <div style="background-color: #0f172a; width: 100%; height: 260px; border-radius: 12px; display: flex; flex-direction: column; justify-content: center; align-items: center; color: #ffffff; padding: 20px; text-align: center;">
                            <span style="font-size: 40px; margin-bottom: 10px;">📺</span>
                            <h5 style="font-size: 14px; font-weight: 600;">Media Player Video Terintegrasi</h5>
                            <p style="font-size: 12px; color: #94a3b8; max-width: 320px; margin-top: 4px; font-weight: 300;">[Simulasi: Sistem siap menyematkan tautan API pemutar video YouTube eksternal atau lokal secara aman]</p>
                        </div>
                        <p style="font-size: 13px; color: #64748b; margin-top: 15px; line-height: 1.5;">💡 <em>Tips: Tonton materi pemaparan video kuliah ini secara utuh untuk mempermudah pengerjaan simulasi evaluasi mingguan.</em></p>

                    <?php else: ?>
                        <div style="border: 1px dashed #cbd5e1; border-radius: 12px; padding: 40px 20px; text-align: center; background-color: #f8fafc;">
                            <span style="font-size: 44px; display: block; margin-bottom: 12px;">📂</span>
                            <h5 style="font-size: 15px; font-weight: 600; color: #0f172a;">Berkas Lampiran PDF Akademik</h5>
                            <p style="font-size: 13px; color: #64748b; max-width: 280px; margin: 4px auto 20px auto; font-weight: 400;">Dokumen cetak buku ajar resmi, lembar panduan praktikum, atau silabus materi kuliah.</p>
                            <a href="#" onclick="alert('Simulasi: Berkas PDF siap diunduh secara aman ke penyimpanan lokal.'); return false;" 
                               style="background-color: #0f172a; color: #ffffff; text-decoration: none; padding: 8px 24px; border-radius: 6px; font-size: 13px; font-weight: 500; display: inline-block;">
                                Unduh Dokumen (.pdf)
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <div style="height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; color: #64748b; padding-top: 60px;">
                    <span style="font-size: 40px; margin-bottom: 15px;">📖</span>
                    <h5 style="font-size: 16px; font-weight: 600; color: #0f172a;">Jendela Pembaca Modul</h5>
                    <p style="font-size: 13px; max-width: 280px; margin-top: 4px; font-weight: 400; line-height: 1.5;">Silakan klik salah satu topik materi di panel sebelah kiri untuk memuat isi konten perkuliahan.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php 
require_once '../includes/footer.php'; 
?>