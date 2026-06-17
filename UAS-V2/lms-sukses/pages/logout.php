<?php
// pages/logout.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Kosongkan semua data array session
$_SESSION = array();

// 2. Hancurkan cookie session jika ada di browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Hancurkan data session di server secara total
session_destroy();

// 4. Lempar kembali pengguna ke gerbang login utama dengan aman
header("Location: /php/ppw/UAS-V2/lms-sukses/pages/login.php");
exit;