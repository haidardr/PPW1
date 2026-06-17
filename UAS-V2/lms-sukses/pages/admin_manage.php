<?php
// pages/admin_manage.php
require_once '../includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteksi Halaman: Hanya Admin yang boleh masuk
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] !== 'admin') {
    header("Location: /php/ppw/UAS-V2/lms-sukses/pages/login.php");
    exit;
}

// =========================================================================
// API INTERNAL (AJAX): MENANGGAPI PERMINTAAN QUICK LOOK DARI DROPDOWN
// =========================================================================
if (isset($_GET['get_quick_look_semester'])) {
    header('Content-Type: application/json');
    $id_sem = intval($_GET['get_quick_look_semester']);
    
    // Query murni tanpa alias/inisial untuk menghitung total modul per matkul di semester aktif
    $query_ajax = "
        SELECT courses.nama_matkul, COUNT(course_contents.id) AS jumlah_materi_dibuat
        FROM courses
        JOIN course_semester ON courses.id = course_semester.course_id
        LEFT JOIN course_contents ON courses.id = course_contents.matkul_id
        WHERE course_semester.semester_id = ?
        GROUP BY courses.id, courses.nama_matkul
        ORDER BY courses.is_pilihan ASC, courses.id ASC";
        
    $stmt_ajax = $koneksi->prepare($query_ajax);
    $stmt_ajax->bind_param("i", $id_sem);
    $stmt_ajax->execute();
    $hasil_ajax = $stmt_ajax->get_result();
    
    $stat_data = [];
    while ($row = $hasil_ajax->fetch_assoc()) {
        $stat_data[] = $row;
    }
    $stmt_ajax->close();
    
    echo json_encode($stat_data);
    exit;
}

// =========================================================================
// PROSES CRUD (DELETE): MENANGGAPI PENGHAPUSAN MODUL KONTEN
// =========================================================================
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus' && isset($_GET['id'])) {
    $id_hapus = intval($_GET['id']);
    $query_hapus = "DELETE FROM course_contents WHERE course_contents.id = ?";
    $stmt = $koneksi->prepare($query_hapus);
    $stmt->bind_param("i", $id_hapus);
    if ($stmt->execute()) { 
        header("Location: admin_manage.php?status=deleted"); 
        exit; 
    }
    $stmt->close();
}

// Ambil data master master semester untuk Nav-Tabs & Dropdown
$query_all_semester = "SELECT semesters.id, semesters.nama_semester FROM semesters ORDER BY semesters.id ASC";
$hasil_all_semester = $koneksi->query($query_all_semester);

require_once '../includes/header.php';
?>

<div class="academic-section">
    <div class="sub-navigation" style="flex-wrap: wrap; gap: 20px; margin-bottom: 20px;">
        <div>
            <h2 style="font-size: 24px; font-weight: 700; color: #0f172a; letter-spacing: -0.5px;">Panel Kendali Akademik</h2>
            <p style="font-size: 14px; color: #64748b; font-weight: 400; margin-top: 2px;">Kelola modul mata kuliah dan tinjau hak akses operasional kurikulum komunitas.</p>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
            <a href="admin_acc.php" class="semester-btn" style="border-radius: 8px; font-size: 13px;">📋 Tinjau Pengajuan</a>
            <a href="admin_add_matkul.php" class="semester-btn" style="border-radius: 8px; font-size: 13px;">📚 Kelola Mata Kuliah</a>
            <a href="admin_add.php" class="semester-btn selected" style="border-radius: 8px; font-size: 13px; background-color: #0f172a; border-color: #0f172a;">+ Tambah Materi</a>
        </div>
    </div>

    <div class="sub-navigation" style="padding-top: 0; padding-bottom: 0; align-items: center;">
        <h4 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 0;">📊 Ringkasan Statistik Modul</h4>
        <div style="min-width: 240px;">
            <select class="form-select" id="quickLookFilter" style="padding: 6px 12px; font-size: 13px;">
                <option value="">-- Pilih Semester Terpilih --</option>
                <?php 
                $hasil_all_semester->data_seek(0);
                while ($sem = $hasil_all_semester->fetch_assoc()): 
                ?>
                    <option value="<?php echo $sem['id']; ?>"><?php echo htmlspecialchars($sem['nama_semester']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
    </div>

    <div class="grid-container" id="quickLookContainer" style="padding-top: 15px; padding-bottom: 30px; gap: 20px;">
        <div style="grid-column: 1 / -1; background-color: #ffffff; border: 1px dashed #e2e8f0; border-radius: 12px; padding: 30px; text-align: center; color: #64748b; font-size: 13px;">
            💡 Silakan pilih tingkatan semester pada menu dropdown di atas untuk mengintip jumlah materi secara ringkas.
        </div>
    </div>

    <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 10px 40px 30px 40px;">

    <div class="sub-navigation" style="padding-top: 0; padding-bottom: 15px;">
        <h4 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 0;">🗂️ Manajemen Modul Konten</h4>
    </div>

    <div class="sub-navigation" style="padding-top: 0; padding-bottom: 25px;">
        <div class="semester-pill-container" style="width: 100%; -webkit-overflow-scrolling: touch; gap: 6px;">
            <?php 
            $aktif_pertama = true;
            $hasil_all_semester->data_seek(0);
            while ($sem = $hasil_all_semester->fetch_assoc()): 
            ?>
                <button class="semester-btn tab-trigger <?php echo $aktif_pertama ? 'selected' : ''; ?>" 
                        data-target-panel="panel-sem-<?php echo $sem['id']; ?>" 
                        style="cursor: pointer; padding: 6px 16px; font-size: 13px; border-radius: 20px;">
                    <?php echo htmlspecialchars($sem['nama_semester']); ?>
                </button>
            <?php $aktif_pertama = false; endwhile; ?>
        </div>
    </div>

    <div style="padding: 0 40px;" id="tabsContentWrapper">
        <?php 
        $aktif_panel_pertama = true;
        $hasil_all_semester->data_seek(0);
        while ($sem = $hasil_all_semester->fetch_assoc()): 
            $id_semester_loop = $sem['id'];
            
            // Query mengambil seluruh konten materi terikat murni pada semester looping aktif
            $query_konten = "
                SELECT course_contents.id, course_contents.judul_materi, course_contents.tipe_konten, courses.nama_matkul 
                FROM course_contents 
                JOIN courses ON course_contents.matkul_id = courses.id 
                JOIN course_semester ON courses.id = course_semester.course_id 
                WHERE course_semester.semester_id = ? 
                ORDER BY courses.is_pilihan ASC, courses.nama_matkul ASC, course_contents.id DESC";
                
            $stmt_konten = $koneksi->prepare($query_konten);
            $stmt_konten->bind_param("i", $id_semester_loop);
            $stmt_konten->execute();
            $hasil_konten = $stmt_konten->get_result();
        ?>
            <div class="semester-tab-panel" 
                 id="panel-sem-<?php echo $id_semester_loop; ?>" 
                 style="display: <?php echo $aktif_panel_pertama ? 'block' : 'none'; ?>;">
                
                <div style="width: 100%; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.01);">
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                            <thead>
                                <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #475569; font-weight: 600;">
                                    <th style="padding: 14px 20px; width: 30%;">Mata Kuliah</th>
                                    <th style="padding: 14px 20px; width: 45%;">Judul Modul</th>
                                    <th style="padding: 14px 20px; width: 10%;">Tipe</th>
                                    <th style="padding: 14px 20px; width: 15%; text-align: right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody style="color: #334155;">
                                <?php if ($hasil_konten->num_rows > 0): while ($k = $hasil_konten->fetch_assoc()): ?>
                                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f8fafc'" onmouseout="this.style.backgroundColor='transparent'">
                                        <td style="padding: 14px 20px; font-weight: 600; color: #0f172a; text-transform: uppercase; font-size: 13px;"><?php echo htmlspecialchars($k['nama_matkul']); ?></td>
                                        <td style="padding: 14px 20px; text-transform: uppercase; font-size: 13px; color: #475569;"><?php echo htmlspecialchars($k['judul_materi']); ?></td>
                                        <td style="padding: 14px 20px;">
                                            <?php if ($k['tipe_konten'] === 'teks'): ?>
                                                <span style="font-size: 11px; font-weight: 600; background-color: #f1f5f9; color: #475569; padding: 3px 8px; border-radius: 4px; border: 1px solid #e2e8f0;">TEKS</span>
                                            <?php elseif ($k['tipe_konten'] === 'video'): ?>
                                                <span style="font-size: 11px; font-weight: 600; background-color: #fef2f2; color: #b91c1c; padding: 3px 8px; border-radius: 4px; border: 1px solid #fee2e2;">VIDEO</span>
                                            <?php else: ?>
                                                <span style="font-size: 11px; font-weight: 600; background-color: #eff6ff; color: #1d4ed8; padding: 3px 8px; border-radius: 4px; border: 1px solid #dbeafe;">PDF</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 14px 20px; text-align: right;">
                                            <div style="display: flex; justify-content: flex-end; gap: 6px;">
                                                <a href="admin_edit.php?id=<?php echo $k['id']; ?>" class="semester-btn" style="padding: 4px 12px; font-size: 12px; border-radius: 6px; text-decoration: none;">Edit</a>
                                                <a href="admin_manage.php?id=<?php echo $k['id']; ?>&aksi=hapus" class="semester-btn del-btn" style="padding: 4px 12px; font-size: 12px; border-radius: 6px; text-decoration: none; color: #b91c1c; border-color: #fee2e2; background-color: #fef2f2;">Hapus</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; else: ?>
                                    <tr>
                                        <td colspan="4" style="padding: 50px; text-align: center; color: #94a3b8; font-size: 14px;">
                                            📭 Belum ada modul materi kuliah yang diterbitkan untuk tingkat semester ini.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        <?php 
            $stmt_konten->close();
            $aktif_panel_pertama = false; 
        endwhile; 
        ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterQuickLook = document.getElementById('quickLookFilter');
    const containerQuickLook = document.getElementById('quickLookContainer');

    // 1. MEKANISME FETCH API/AJAX PADA FILTER QUICK LOOK DROPDOWN
    filterQuickLook.addEventListener('change', function() {
        const semesterId = this.value;

        if (semesterId === '') {
            containerQuickLook.innerHTML = `
                <div style="grid-column: 1 / -1; background-color: #ffffff; border: 1px dashed #e2e8f0; border-radius: 12px; padding: 30px; text-align: center; color: #64748b; font-size: 13px;">
                    💡 Silakan pilih tingkatan semester pada menu dropdown di atas untuk mengintip jumlah materi secara ringkas.
                </div>`;
            return;
        }

        containerQuickLook.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; color: #64748b; font-size: 13px; py-2;">🔄 Sedang memuat rekapan statistik kurikulum...</div>';

        fetch(`admin_manage.php?get_quick_look_semester=${semesterId}`)
            .then(response => response.json())
            .then(data => {
                containerQuickLook.innerHTML = '';

                if (data.length === 0) {
                    containerQuickLook.innerHTML = `
                        <div style="grid-column: 1 / -1; background-color: #ffffff; border: 1px dashed #e2e8f0; border-radius: 12px; padding: 30px; text-align: center; color: #64748b; font-size: 13px;">
                            📭 Tidak ditemukan mata kuliah terdaftar pada tingkatan semester ini.
                        </div>`;
                } else {
                    data.forEach(st => {
                        const colDiv = document.createElement('div');
                        colDiv.style.width = '100%';
                        
                        colDiv.innerHTML = `
                            <div class="academic-card" style="height: 120px; border-radius: 12px; padding: 20px; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.01);">
                                <h6 style="font-weight: 700; font-size: 12px; color: #0f172a; margin-bottom: 6px; text-transform: uppercase; letter-spacing: -0.2px; line-height: 1.3;">
                                    ${st.nama_matkul}
                                </h6>
                                <div style="font-size: 13px; color: #64748b; font-weight: 400;">
                                    Total Modul: <span style="color: #0f172a; font-weight: 700;">${st.jumlah_materi_dibuat}</span>
                                </div>
                            </div>`;
                        containerQuickLook.appendChild(colDiv);
                    });
                }
            })
            .catch(error => {
                console.error('Gagal mengambil data Quick Look:', error);
                containerQuickLook.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; color: #b91c1c; font-size: 13px;">⚠️ Terjadi gangguan saat mengambil data.</div>';
            });
    });

    // 2. LOGIKA SWITCHING PANEL TAB PER SEMESTER (CLIENT SIDE)
    const tabTriggers = document.querySelectorAll('.tab-trigger');
    const tabPanels = document.querySelectorAll('.semester-tab-panel');

    tabTriggers.forEach(btn => {
        btn.addEventListener('click', function() {
            // Matikan tombol lama, nyalakan yang sedang diklik
            tabTriggers.forEach(t => t.classList.remove('selected'));
            this.classList.add('selected');

            // Sembunyikan seluruh panel data tabel
            tabPanels.forEach(p => p.style.display = 'none');

            // Tampilkan panel target yang sesuai
            const targetId = this.getAttribute('data-target-panel');
            document.getElementById(targetId).style.display = 'block';
        });
    });
});
</script>

<?php 
require_once '../includes/footer.php'; 
?>