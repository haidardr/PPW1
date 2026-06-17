<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$file_aktif = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS Sukses - Platform Peer-Learning</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="/php/ppw/UAS-V2/lms-sukses/assets/css/style.css">
</head>
<body>

    <header>
        <div class="logo-area">
            <a href="/php/ppw/UAS-V2/lms-sukses/index.php">LMS<span>Sukses</span></a>
        </div>
        
        <nav>
            <a href="/php/ppw/UAS-V2/lms-sukses/index.php">Beranda</a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/php/ppw/UAS-V2/lms-sukses/pages/matkul.php">Dashboard</a>
                
                <?php if ($_SESSION['peran'] === 'admin'): ?>
                    <a href="/php/ppw/UAS-V2/lms-sukses/pages/admin_manage.php" style="color: red; font-weight: 600;">Kelola Ruang</a>
                <?php endif; ?>
                
                <a href="/php/ppw/UAS-V2/lms-sukses/pages/logout.php" class="login">
                    <div class="dot"></div> Keluar
                </a>
            <?php else: ?>
                <a href="/php/ppw/UAS-V2/lms-sukses/pages/login.php" class="login">
                    <div class="dot"></div> Masuk Akun
                </a>
            <?php endif; ?>
        </nav>
    </header>