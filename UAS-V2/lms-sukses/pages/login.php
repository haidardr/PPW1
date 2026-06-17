<?php
// pages/login.php

// 1. Hubungkan koneksi database (Gunakan relative path keluar folder pages)
require_once '../includes/config.php';

// Pastikan session sudah berjalan secara aman
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika user sudah login, langsung lempar ke halaman dashboard matkul
if (isset($_SESSION['user_id'])) {
    header("Location: /php/ppw/UAS-V2/lms-sukses/pages/matkul.php");
    exit;
}

$pesan_error = "";

// 2. MEMPROSES FORM KETIKA DISUBMIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Bersihkan input teks untuk keamanan dasar
    $username = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        // Menggunakan Prepared Statement untuk mencari user berdasarkan username (Tanpa Alias/Inisial)
        $query_login = "SELECT users.id, users.username, users.password, users.peran FROM users WHERE users.username = ?";
        
        if ($stmt = $koneksi->prepare($query_login)) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $hasil_query = $stmt->get_result();

            if ($hasil_query->num_rows === 1) {
                $user = $hasil_query->fetch_assoc();
                
                // Verifikasi password (Bisa menggunakan password_verify jika di-hash, atau string match murni untuk demo uas)
                if ($password === $user['password']) {
                    // Regenerasi session ID untuk mencegah serangan Session Fixation
                    session_regenerate_id(true);

                    // Daftarkan data user ke global session
                    $_SESSION['user_id']  = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['peran']    = $user['peran'];

                    // Alihkan halaman berdasarkan peran hak akses (Role-based Redirect)
                    if ($user['peran'] === 'admin') {
                        header("Location: /php/ppw/UAS-V2/lms-sukses/pages/admin_manage.php");
                    } else {
                        header("Location: /php/ppw/UAS-V2/lms-sukses/pages/matkul.php");
                    }
                    exit;
                } else {
                    $pesan_error = "Kata sandi yang Anda masukkan salah.";
                }
            } else {
                $pesan_error = "Username tidak terdaftar dalam sistem.";
            }
            $stmt->close();
        }
    } else {
        $pesan_error = "Harap isi seluruh kolom username dan password.";
    }
}

// 3. Render komponen visual atas (Navbar otomatis mendeteksi status belum login)
require_once '../includes/header.php';
?>

<div class="container my-5 py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-4">
            
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white border">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-dark mb-1">Selamat Datang</h4>
                        <p class="text-muted small">Silakan masuk menggunakan akun institusi Anda.</p>
                    </div>

                    <?php if (!empty($pesan_error)): ?>
                        <div class="alert alert-danger border-0 small rounded-3 py-2 mb-3" role="alert">
                            ⚠️ <?php echo $pesan_error; ?>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="POST" id="formLogin">
                        
                        <div class="mb-3">
                            <label for="username" class="form-label small fw-semibold text-secondary">Username / NIM</label>
                            <input type="text" class="form-control rounded-3" id="username" name="username" placeholder="E.g. 230101001" required>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label small fw-semibold text-secondary">Kata Sandi</label>
                            <input type="password" class="form-control rounded-3" id="password" name="password" placeholder="••••••••" required>
                        </div>

                        <button type="submit" class="btn btn-dark w-100 rounded-pill py-2 fw-medium shadow-sm mb-2">
                            Masuk ke Ruang Belajar
                        </button>
                        
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="/php/ppw/UAS-V2/lms-sukses/index.php" class="text-decoration-none small text-secondary">&larr; Kembali ke Beranda</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php 
// 4. Render komponen visual bawah
require_once '../includes/footer.php'; 
?>